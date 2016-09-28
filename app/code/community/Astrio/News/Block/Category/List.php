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
class Astrio_News_Block_Category_List extends Mage_Core_Block_Template
{

    /**
     * @return Astrio_News_Model_Resource_News_Collection
     */
    public function getCollection()
    {
        if (!$this->hasData('collection')) {
            /**
             * @var $collection Astrio_News_Model_Resource_Category_Collection
             * @var $category Astrio_News_Model_Category
             */
            $collection = Mage::getResourceModel('astrio_news/category_collection');
            $collection
                ->setStoreId(Mage::app()->getStore()->getId())
                ->addStoreFilter()
                ->addIsActiveFilter()
                ->addCollectionAttributesToSelect()
                ->addAttributeToSort('position', Varien_Data_Collection::SORT_ORDER_ASC);

            $collection->addActiveNewsCount();

            $this->setData('collection', $collection);
        }

        return $this->_getData('collection');
    }

    /**
     * Before toHtml
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _beforeToHtml()
    {
        $return = parent::_beforeToHtml();

        $collection = $this->getCollection();

        Mage::dispatchEvent('astrio_news_category_list_block_collection_load_before', array('collection' => $collection));

        $collection->load();

        return $return;
    }
}
