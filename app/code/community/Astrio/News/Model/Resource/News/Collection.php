<?php
/**
 * Astrio Agency
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0).
 * It is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you are unable to obtain it through the world-wide-web, please send
 * an email to info@astrio.net so we can send you a copy immediately.
 *
 * @category   Astrio
 * @package    Astrio_News
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** 
 * Astrio
 *
 * @category   Astrio
 * @package    Astrio_News
 * @author     Eldaniz Gasymov <e.gasymov@astrio.net>
 */
class Astrio_News_Model_Resource_News_Collection extends Astrio_News_Model_Resource_Abstract_Collection
{
    
    /**
     * News stores table name
     *
     * @var string
     */
    protected $_newsStoreTable;

    /**
     * News categories table name
     *
     * @var string
     */
    protected $_newsCategoryTable;

    /**
     * Is add Category to URL to collection flag
     *
     * @var null|Astrio_News_Model_Category
     */
    protected $_addCategoryToUrl = null;

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('astrio_news/news');
        $this->_initTables();
    }

    /**
     * Define news store table
     *
     */
    protected function _initTables()
    {
        $this->_newsStoreTable = $this->getResource()->getTable('astrio_news/news_store');
        $this->_newsCategoryTable = $this->getResource()->getTable('astrio_news/news_category');
    }

    /**
     * Adding news store names to result collection
     * Add for each news store information
     *
     * @return Astrio_News_Model_Resource_News_Collection
     */
    public function addStoreNamesToResult()
    {
        $newsStores = array();
        foreach ($this as $news) {
            $newsStores[$news->getId()] = array();
        }

        if (!empty($newsStores)) {
            $select = $this->getConnection()->select()
                ->from(array('news_store' => $this->_newsStoreTable))
                ->join(
                    array('store' => $this->getResource()->getTable('core/store')),
                    'store.store_id = news_store.store_id',
                    array('name'))
                ->where('news_store.news_id IN (?)', array_keys($newsStores));

            $data = $this->getConnection()->fetchAll($select);
            foreach ($data as $row) {
                $newsStores[$row['news_id']][] = $row['store_id'];
            }
        }

        foreach ($this as $news) {
            if (isset($newsStores[$news->getId()])) {
                $news->setData('store_ids', $newsStores[$news->getId()]);
            }
        }

        return $this;
    }    
    
    /**
     * Adding news categories to result collection
     * Add for each news categories
     *
     * @return Astrio_News_Model_Resource_News_Collection
     */
    public function addCategoriesToResult()
    {
        $newsCategories = array();
        foreach ($this as $news) {
            $newsCategories[$news->getId()] = array();
        }

        if (!empty($newsCategories)) {
            $select = $this->getConnection()->select()
                ->from(array('news_category' => $this->_newsCategoryTable))
                ->where('news_category.news_id IN (?)', array_keys($newsCategories));

            $data = $this->getConnection()->fetchAll($select);
            foreach ($data as $row) {
                $newsCategories[$row['news_id']][] = $row['category_id'];
            }
        }

        foreach ($this as $news) {
            if (isset($newsCategories[$news->getId()])) {
                $news->setData('category_ids', $newsCategories[$news->getId()]);
            }
        }

        return $this;
    }

    /**
     * Add store availability filter.
     *
     * @param  mixed $store store id
     * @return Astrio_News_Model_Resource_News_Collection
     */
    public function addStoreFilter($store = null)
    {
        if ($store === null) {
            $store = $this->getStoreId();
        }
        $store = Mage::app()->getStore($store);

        if (!$store->isAdmin()) {
            $storeIds = array(Mage_Core_Model_App::ADMIN_STORE_ID, (int) $store->getId());
            $this->getSelect()->join(
                array('news_store' => $this->_newsStoreTable),
                implode(' AND ', array(
                    'news_store.news_id = ' . self::MAIN_TABLE_ALIAS . '.entity_id',
                    $this->getConnection()->quoteInto('news_store.store_id IN(?)', $storeIds),
                )),
                array()
            );
        }

        return $this;
    }

    /**
     * Add category filter.
     *
     * @param  int|Astrio_News_Model_Category $category category
     * @return Astrio_News_Model_Resource_News_Collection
     */
    public function addCategoryFilter($category)
    {
        if ($category instanceof Astrio_News_Model_Category) {
            $categoryId = $category->getId();
        } else {
            $categoryId = (int) $category;
        }

        $this->getSelect()->join(
            array('news_category' => $this->_newsCategoryTable),
            implode(' AND ', array(
                'news_category.news_id = e.entity_id',
                $this->getConnection()->quoteInto('news_category.category_id = ?', $categoryId),
            )),
            array()
        );

        return $this;
    }

    /**
     * Add category to url
     *
     * @param  Astrio_News_Model_Category $category category
     * @return $this
     */
    public function addCategoryToUrl(Astrio_News_Model_Category $category)
    {
        $this->_addCategoryToUrl = $category;

        if ($this->isLoaded()) {
            $this->_addCategoryToUrl();
        }

        return $this;
    }

    /**
     * Add category to url
     *
     * @return $this
     */
    protected function _addCategoryToUrl()
    {
        if ($this->_addCategoryToUrl instanceof Astrio_News_Model_Category) {

            $catId = $this->_addCategoryToUrl->getId();
            $catUrlKey = $this->_addCategoryToUrl->getUrlKey();

            foreach ($this->getItems() as $item) {
                $item->setData('current_category_id', $catId);
                $item->setData('current_category_url_key', $catUrlKey);
            }
        }

        return $this;
    }

    /**
     * Processing collection items after loading
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        if ($this->_addCategoryToUrl) {
            $this->_addCategoryToUrl();
        }

        return parent::_afterLoad();
    }

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('entity_id', 'title');
    }

    /**
     * To option hash
     *
     * @return array
     */
    public function toOptionHash()
    {
        return $this->_toOptionHash('entity_id', 'title');
    }
}
