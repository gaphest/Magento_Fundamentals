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
class Astrio_News_Model_Resource_News extends Astrio_News_Model_Resource_Abstract
{
    
    /**
     * News to store linkage table
     *
     * @var string
     */
    protected $_newsStoreTable;
    
    /**
     * News to category linkage table
     *
     * @var string
     */
    protected $_newsCategoryTable;
    
    /**
     * Initialize resource
     */
    public function __construct()
    {
        /**
         * @var $resource Mage_Core_Model_Resource
         */
        parent::__construct();
        $resource = Mage::getSingleton('core/resource');
        $this->setType(Astrio_News_Model_News::ENTITY)
            ->setConnection(
                $resource->getConnection(Mage_Core_Model_Resource::DEFAULT_READ_RESOURCE),
                $resource->getConnection(Mage_Core_Model_Resource::DEFAULT_WRITE_RESOURCE)
            );
        
        $this->_newsStoreTable      = $this->getTable('astrio_news/news_store');
        $this->_newsCategoryTable   = $this->getTable('astrio_news/news_category');
    }

    /**
     * Retrieve news store identifiers
     *
     * @param  Astrio_News_Model_News|int $news news model or id
     * @return array
     */
    public function getStoreIds($news)
    {
        $adapter = $this->_getReadAdapter();

        if ($news instanceof Astrio_News_Model_News) {
            $newsId = $news->getId();
        } else {
            $newsId = $news;
        }

        $select = $adapter->select()
            ->from($this->_newsStoreTable, 'store_id')
            ->where('news_id = ?', (int)$newsId);

        return $adapter->fetchCol($select);
    } 
    
    /**
     * Retrieve news category identifiers
     *
     * @param  Astrio_News_Model_News|int $news news model or id
     * @return array
     */
    public function getCategoryIds($news)
    {
        $adapter = $this->_getReadAdapter();

        if ($news instanceof Astrio_News_Model_News) {
            $newsId = $news->getId();
        } else {
            $newsId = $news;
        }

        $select = $adapter->select()
            ->from($this->_newsCategoryTable, 'category_id')
            ->where('news_id = ?', (int)$newsId);

        return $adapter->fetchCol($select);
    }

    /**
     * Save news store relations
     *
     * @param  Astrio_News_Model_News $news news model or id
     * @return $this
     */
    protected function _saveStoreIds($news)
    {
        $storeIds = $news->getStoreIds();
        if (in_array(Mage_Core_Model_App::ADMIN_STORE_ID, $storeIds)) {
            $storeIds = array(Mage_Core_Model_App::ADMIN_STORE_ID);
        }

        $news->setIsChangedStores(false);

        $adapter = $this->_getWriteAdapter();

        $oldStoreIds = $this->getStoreIds($news);

        $insert = array_diff($storeIds, $oldStoreIds);
        $delete = array_diff($oldStoreIds, $storeIds);

        if (!empty($insert)) {
            $data = array();
            foreach ($insert as $storeId) {
                $data[] = array(
                    'news_id' => (int)$news->getId(),
                    'store_id' => (int)$storeId
                );
            }
            $adapter->insertMultiple($this->_newsStoreTable, $data);
        }

        if (!empty($delete)) {
            foreach ($delete as $storeId) {
                $condition = array(
                    'news_id = ?' => (int)$news->getId(),
                    'store_id = ?' => (int)$storeId,
                );

                $adapter->delete($this->_newsStoreTable, $condition);
            }
        }

        if (!empty($insert) || !empty($delete)) {
            $news->setIsChangedStores(true);
        }

        return $this;
    }
    
    /**
     * Save news category relations
     *
     * @param  Astrio_News_Model_News $news news model
     * @return $this
     */
    protected function _saveCategoryIds($news)
    {
        $categoryIds = $news->getCategoryIds();

        $news->setIsChangedCategories(false);

        $adapter = $this->_getWriteAdapter();

        $oldCategoryIds = $this->getCategoryIds($news);

        $insert = array_diff($categoryIds, $oldCategoryIds);
        $delete = array_diff($oldCategoryIds, $categoryIds);

        if (!empty($insert)) {
            $data = array();
            foreach ($insert as $categoryId) {
                $data[] = array(
                    'news_id'       => (int)$news->getId(),
                    'category_id'   => (int)$categoryId
                );
            }
            $adapter->insertMultiple($this->_newsCategoryTable, $data);
        }

        if (!empty($delete)) {
            foreach ($delete as $categoryId) {
                $condition = array(
                    'news_id = ?'       => (int)$news->getId(),
                    'category_id = ?'   => (int)$categoryId,
                );

                $adapter->delete($this->_newsCategoryTable, $condition);
            }
        }

        if (!empty($insert) || !empty($delete)) {
            $news->setIsChangedCategories(true);
        }

        return $this;
    }

    /**
     * Save data related with news
     *
     * @param  Varien_Object $news news model
     * @return $this
     */
    protected function _afterSave(Varien_Object $news)
    {
        /**
         * @var $news Astrio_News_Model_News
         */
        $this->_saveStoreIds($news);
        $this->_saveCategoryIds($news);
        return parent::_afterSave($news);
    }
}
