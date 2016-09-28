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
class Astrio_News_Model_Resource_Category_Collection extends Astrio_News_Model_Resource_Abstract_Collection
{

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('astrio_news/category');
        $this->_initTables();
    }

    /**
     * Define news store table
     */
    protected function _initTables()
    {

    }

    /**
     * Get news count select
     *
     * @return Varien_Db_Select
     */
    protected function _getNewsCountSelect()
    {
        /**
         * @var $collection Astrio_News_Model_Resource_News_Collection
         * @var $category Astrio_News_Model_Category
         */
        $collection = Mage::getResourceModel('astrio_news/news_collection');
        $collection
            ->setStoreId($this->getStoreId())
            ->addStoreFilter()
            ->addIsActiveFilter();

        $select = $collection->getSelect();

        $select
            ->reset(Zend_Db_Select::COLUMNS)
            ->reset(Zend_Db_Select::ORDER)
            ->join(
                array('news_category' => $this->getTable('astrio_news/news_category')),
                implode(' AND ', array(
                    'news_category.news_id = ' . Astrio_News_Model_Resource_News_Collection::MAIN_TABLE_ALIAS . '.entity_id',
                )),
                array()
            )
            ->columns(array(
                'category_id'   => 'news_category.category_id',
                'count'         => new Zend_Db_Expr('COUNT(*)'),
            ))
            ->group('news_category.category_id');

        return $select;
    }

    /**
     * add news count and get only categories with active news.
     *
     * @return $this
     */
    public function addActiveNewsCount()
    {
        $select = $this->_getNewsCountSelect();

        $this->getSelect()->join(
            array('active_news_count' => $select),
            implode(' AND ', array(
                'active_news_count.category_id = ' . self::MAIN_TABLE_ALIAS . '.entity_id',
            )),
            array('active_news_count' => 'count')
        );

        return $this;
    }
}
