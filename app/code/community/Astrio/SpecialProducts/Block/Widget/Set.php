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
class Astrio_SpecialProducts_Block_Widget_Set
    extends Mage_Catalog_Block_Product_List
    implements Mage_Widget_Block_Interface
{

    /**
     * Get customer group id
     *
     * @return int
     */
    public function getCustomerGroupId()
    {
        if (!$this->hasData('customer_group_id')) {
            /**
             * @var $customerSession Mage_Customer_Model_Session
             */
            $customerSession = Mage::getSingleton('customer/session');
            $this->setData('customer_group_id', (int) $customerSession->getCustomerGroupId());
        }

        return $this->_getData('customer_group_id');
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->_getData('identifier');
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        if (!$this->hasData('title')) {
            $this->setData('title', $this->getSet()->getName());
        }
        return $this->_getData('title');
    }

    /**
     * Get if is random
     *
     * @return int
     */
    public function getIsRandom()
    {
        if (!$this->hasData('is_random')) {
            $this->setData('is_random', 0);
        }
        return $this->_getData('is_random');
    }

    /**
     * Get count
     *
     * @return int
     */
    public function getCount()
    {
        if (!$this->hasData('count')) {
            $this->setData('count', 0);
        }

        return $this->_getData('count');
    }

    /**
     * Get is active
     *
     * @return mixed
     */
    public function getIsActive()
    {
        return $this->_getData('is_active');
    }

    /**
     * Get special products set
     *
     * @return Astrio_SpecialProducts_Model_Set
     */
    public function getSet()
    {
        if (!$this->hasData('set')) {
            /**
             * @var $collection Astrio_SpecialProducts_Model_Resource_Set_Collection
             * @var $set Astrio_SpecialProducts_Model_Set
             */
            $collection = Mage::getResourceModel('astrio_specialproducts/set_collection');
            $collection
                ->addStoreFilter()
                ->addIsActiveFilter()
                ->addIdentifierFilter($this->getIdentifier())
                ->setCurPage(1)
                ->setPageSize(1);

            $set = $collection->getFirstItem();
            $this->setData('set', $set);
        }

        return $this->_getData('set');
    }

    /**
     * Retrieve loaded category collection
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _getProductCollection()
    {
        if (is_null($this->_productCollection)) {
            /**
             * @var $visibility Mage_Catalog_Model_Product_Visibility
             */
            $visibility     = Mage::getSingleton('catalog/product_visibility');
            $visibilityIds  = $visibility->getVisibleInCatalogIds();

            /**
             * @var $collection Mage_Catalog_Model_Resource_Product_Collection
             */
            $collection = Mage::getResourceModel('catalog/product_collection');

            $collection
                ->setStore(Mage::app()->getStore())
                ->setVisibility($visibilityIds)
                ->addStoreFilter()
                ->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                ->addUrlRewrite()
                ->setPageSize($this->getCount())
                ->setCurPage(1);

            $set = $this->getSet();
            $set->joinProductCollectionToSpecialProductsSetWithGroup($collection, $collection->getStoreId(), $this->getCustomerGroupId());

            $collection->getSelect()->reset(Varien_Db_Select::ORDER);
            if ($this->getIsRandom()) {
                $collection->getSelect()->order(new Zend_Db_Expr("RAND()"));
            } else {
                $collection->getSelect()->order(Astrio_SpecialProducts_Model_Resource_Set::SPECIAL_PRODUCTS_TABLE_ALIAS . ".position " . Varien_Db_Select::SQL_ASC);
            }

            $this->_productCollection = $collection;
        }

        return $this->_productCollection;
    }

    /**
     * Before toHtml
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        if (!$this->getIdentifier() || !$this->getSet()->getId()) {
            return $this;
        }

        Mage::dispatchEvent('catalog_block_product_list_collection', array(
            'collection' => $this->_getProductCollection()
        ));

        $this->_getProductCollection()->load();

        return Mage_Core_Block_Abstract::_beforeToHtml();
    }

    /**
     * Tabs before toHtml
     *
     * @return Astrio_SpecialProducts_Block_Widget_Set
     */
    public function tabsBeforeToHtml()
    {
        return $this->_beforeToHtml();
    }

    /**
     * To html
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->hasProducts()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Get if has products
     *
     * @return bool
     */
    public function hasProducts()
    {
        if (!$this->getIdentifier() || !$this->getSet()->getId()) {
            return false;
        }

        if (count($this->_getProductCollection()) <= 0) {
            return false;
        }

        return true;
    }

    /**
     * Get cache key info
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return parent::getCacheKeyInfo() + array(
            'identifier'        => $this->getIdentifier(),
            'is_random'         => (int) $this->getIsRandom(),
            'count'             => (int) $this->getCount(),
            'title'             => $this->getTitle(),
            'customer_group_id' => (int) $this->getCustomerGroupId(),
            'is_active'         => (int) $this->getIsActive(),
        );
    }

    /**
     * Get cache life time
     *
     * @return int
     */
    public function getCacheLifetime()
    {
        if (!$this->hasData('cache_lifetime')) {
            $this->setData('cache_lifetime', 3600);
        }

        return $this->getData('cache_lifetime');
    }

    /**
     * Replace form key for add to cart url. Added 30.03.2015
     *
     * @param  string $html html
     * @return string
     */
    protected function _afterToHtml($html)
    {
        $formkey = Mage::getSingleton('core/session')->getFormKey();
        $formkey = "/form_key/".$formkey."/";
        $html = preg_replace("/\/form_key\/[a-zA-Z0-9]+\//", $formkey, $html);
        return parent::_afterToHtml($html);
    }
}
