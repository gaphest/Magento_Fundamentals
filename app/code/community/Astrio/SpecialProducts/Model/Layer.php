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
 * @package    Astrio_SpecialProducts
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Astrio
 *
 * @category   Astrio
 * @package    Astrio_SpecialProducts
 * @author     Eldaniz Gasymov <e.gasymov@astrio.net>
 */
class Astrio_SpecialProducts_Model_Layer extends Mage_Catalog_Model_Layer
{

    protected $_productCollection = null;

    /**
     * Initialize product collection
     *
     * @param  Mage_Catalog_Model_Resource_Product_Collection $collection collection
     * @return $this
     */
    public function prepareProductCollection($collection)
    {
        /**
         * @var $set Astrio_SpecialProducts_Model_Set
         */
        $set = Mage::registry('astrio_specialproducts_set');
        if (!$set instanceof Astrio_SpecialProducts_Model_Set || !$set->getId() || !$set->getIsActive() || !in_array(Mage::app()->getStore()->getId(), $set->getStoreIds())) {
            $collection->addIdFilter(0);
            return $this;
        }

        $collection
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addUrlRewrite();

        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);

        /**
         * @var $customerSession Mage_Customer_Model_Session
         */
        $customerSession = Mage::getSingleton('customer/session');
        $customerGroupId = (int) $customerSession->getCustomerGroupId();

        $set->joinProductCollectionToSpecialProductsSetWithGroup($collection, $collection->getStoreId(), $customerGroupId);

        return $this;
    }

    /**
     * Retrieve current layer product collection
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getProductCollection()
    {
        if ($this->_productCollection === null) {
            /**
             * @var $collection Mage_Catalog_Model_Resource_Product_Collection
             */
            $collection = Mage::getResourceModel('catalog/product_collection');
            $this->prepareProductCollection($collection);
            $this->_productCollection = $collection;
        }

        return $this->_productCollection;
    }
}
