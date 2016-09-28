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
 * @package    Astrio_Core
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Astrio
 *
 * @category Astrio
 * @package Astrio_Brand
 * @author Eldaniz Gasymov <e.gasymov@astrio.net>
 */
class Astrio_Brand_Model_Observer
{
    /**
     * event: admin_system_config_changed_section_astrio_brand
     *
     * @param Varien_Event_Observer $observer observer
     * @return $this
     */
    public function saveBrandsListUrlRewrite(Varien_Event_Observer $observer)
    {
        $website = $observer->getEvent()->getData('website');
        $store = $observer->getEvent()->getData('store');

        /**
         * @var $factory Astrio_Brand_Model_Factory
         */
        $factory = Mage::getSingleton('astrio_brand/factory');
        $urlRewriteHelper = $factory->getBrandUrlRewriteHelper();

        if (!$website && !$store) {
            $urlRewriteHelper->createBrandsListUrlRewrite();
        } elseif (!$store) {
            $storeIds = Mage::app()->getWebsite($website)->getStoreIds();
            foreach ($storeIds as $storeId) {
                $urlRewriteHelper->createBrandsListUrlRewrite($storeId);
            }
        } else {
            $storeId = Mage::app()->getStore($store)->getId();
            $urlRewriteHelper->createBrandsListUrlRewrite($storeId);
        }

        return $this;
    }

    /**
     * event: catalog_product_collection_load_after
     *
     * @param Varien_Event_Observer $observer observer
     * @return $this
     */
    public function addUrlRewriteToProductCollection(Varien_Event_Observer $observer)
    {
        /**
         * @var $collection Mage_Catalog_Model_Resource_Product_Collection
         */
        $collection = $observer->getEvent()->getData('collection');
        if ($brandId = $collection->getFlag('add_brand_url_rewrite')) {
            /**
             * @var $factory Astrio_Brand_Model_Factory
             */
            $factory = Mage::getSingleton('astrio_brand/factory');
            $factory->getBrandUrlRewriteHelper()
                ->addProductUrlRewrites($collection, $brandId);
        }

        return $this;
    }

    /**
     * event: catalog_controller_product_init_after
     *
     * @param Varien_Event_Observer $observer observer
     * @return $this
     */
    public function setCurrentBrandOnProductPage(Varien_Event_Observer $observer)
    {
        /**
         * @var $controller Mage_Core_Controller_Front_Action
         * @var $product Mage_Catalog_Model_Product
         */
        $controller = $observer->getEvent()->getData('controller_action');
        $product = $observer->getEvent()->getData('product');
        $brandId = $controller->getRequest()->getParam('brand');
        if (!$brandId && !Mage::registry('current_category')) {
            $brandId = Mage::getSingleton('catalog/session')->getLastViewedBrandId();
        }
        if ($brandId && $brandId == $product->getData(Astrio_Brand_Model_Brand::ATTRIBUTE_CODE)) {
            /**
             * @var $brandHelper Astrio_Brand_Helper_Brand
             * @var $brand Astrio_Brand_Model_Brand
             */
            $brandHelper = Mage::helper('astrio_brand/brand');
            if ($brandHelper->shouldUseBrandForProductUrl($product->getStoreId())) {
                $brand = Mage::getModel('astrio_brand/brand');
                $brand->setStoreId($product->getStoreId())
                    ->load($brandId);
                if ($brand->canShow()) {
                    Mage::register('brand', $brand);
                    Mage::register('current_brand', $brand);
                }
            }
        }

        return $this;
    }

    /**
     * Gets admin session
     *
     * @return Mage_Adminhtml_Model_Session
     */
    protected function _getAdminSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }

    /**
     * Gets brand attribute
     *
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */
    protected function _getBrandAttribute()
    {
        /**
         * @var $eavConfig Mage_Eav_Model_Config
         */
        $eavConfig = Mage::getSingleton('eav/config');
        return $eavConfig->getAttribute(Mage_Catalog_Model_Product::ENTITY, Astrio_Brand_Model_Brand::ATTRIBUTE_CODE);
    }

    /**
     * Brand attribute edit restrict
     *
     * event: catalog_entity_attribute_save_before
     *
     * @param Varien_Event_Observer $observer observer
     * @return $this
     */
    public function brandAttributeEditRestrict(Varien_Event_Observer $observer)
    {
        /**
         * @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute
         */
        $attribute = $observer->getEvent()->getData('attribute');
        if ($attribute->getId() == $this->_getBrandAttribute()->getId()) {
            $helper = Mage::helper('astrio_brand');

            //do not change options
            $attribute->unsetData('option');

            $isAdminStore = Mage::app()->getStore()->isAdmin();

            if ($attribute->getAttributeCode() != Astrio_Brand_Model_Brand::ATTRIBUTE_CODE) {
                $attribute->setAttributeCode(Astrio_Brand_Model_Brand::ATTRIBUTE_CODE);
            }
            if ($attribute->getData('backend_model') != NULL) {
                $attribute->setData('backend_model', NULL);
            }
            if ($attribute->getData('backend_type') != 'int') {
                $attribute->setData('backend_type', 'int');
            }
            if ($attribute->getData('backend_table') != null) {
                $attribute->setData('backend_table', null);
            }
            if ($attribute->getData('frontend_input') != 'select') {
                $attribute->setData('frontend_input', 'select');
            }
            if ($attribute->getData('source_model') != 'eav/entity_attribute_source_table') {
                $attribute->setData('source_model', 'eav/entity_attribute_source_table');
            }

            if (!$attribute->isScopeGlobal()) {
                $attribute->setIsGlobal(Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL);
                if ($isAdminStore) {
                    $this->_getAdminSession()->addNotice($helper->__('Brand attribute should have global scope. Scope was not changed'));
                }
            }

            if ($attribute->getData('is_unique')) {
                $attribute->setData('is_unique', 0);
                if ($isAdminStore) {
                    $this->_getAdminSession()->addNotice($helper->__('Brand attribute could not be unique. This property was not changed.'));
                }
            }
        }

        return $this;
    }

    /**
     * Brand attribute delete restrict
     *
     * event: catalog_entity_attribute_delete_before
     *
     * @param Varien_Event_Observer $observer observer
     * @return $this
     */
    public function brandAttributeDeleteRestrict(Varien_Event_Observer $observer)
    {
        /**
         * @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute
         */
        $attribute = $observer->getEvent()->getData('attribute');
        if ($attribute->getId() == $this->_getBrandAttribute()->getId()) {
            Mage::throwException(Mage::helper('astrio_brand')->__('Can not delete attribute `' . Astrio_Brand_Model_Brand::ATTRIBUTE_CODE . '`. It is used by Astrio_Brand module.'));
        }

        return $this;
    }

    /**
     * Add handles to widget instance
     *
     * event: adminhtml_widget_instance_edit_tab_main_layout_get_display_on_options
     *
     * @param Varien_Event_Observer $observer observer
     * @return $this
     */
    public function addHandlesToWidgetInstance(Varien_Event_Observer $observer)
    {
        $transport = $observer->getEvent()->getData('transport');

        $layoutHandles                   = $transport->getData('layout_handles');
        $specificEntitiesLayoutHandles   = $transport->getData('specific_entities_layout_handles');

        $layoutHandles['brand']                 = 'astrio_brand_brand_view';
        $specificEntitiesLayoutHandles['brand'] = 'BRAND_{{ID}}';

        $transport->setData('layout_handles', $layoutHandles);
        $transport->setData('specific_entities_layout_handles', $specificEntitiesLayoutHandles);

        return $this;
    }

    /**
     * Add display on option to widget instance
     *
     * event: adminhtml_widget_instance_edit_tab_main_layout_get_display_on_options
     *
     * @param Varien_Event_Observer $observer observer
     * @return $this
     */
    public function addDisplayOnOptionsToWidgetInstance(Varien_Event_Observer $observer)
    {
        $transport = $observer->getEvent()->getData('transport');

        $options = $transport->getData('options');

        $options[] = array(
            'label' => Mage::helper('astrio_brand')->__('Brand'),
            'value' => array(
                array(
                    'value' => 'brand',
                    'label' => Mage::helper('core')->jsQuoteEscape(Mage::helper('astrio_brand')->__('Brand'))
                ),
            )
        );

        $transport->setData('options', $options);

        return $this;
    }

    /**
     * Add display on containers to widget instance
     *
     * event: adminhtml_widget_instance_edit_tab_main_layout_get_display_on_containers
     *
     * @param Varien_Event_Observer $observer observer
     * @return $this
     */
    public function addDisplayOnContainersToWidgetInstance(Varien_Event_Observer $observer)
    {
        $transport = $observer->getEvent()->getData('transport');

        $container = $transport->getData('container');

        $container['brand'] = array(
            'label' => 'Brand',
            'code' => 'brand',
            'name' => 'brand',
            'layout_handle' => 'default,astrio_brand_brand_view',
            'is_anchor_only' => 1,
            'product_type_id' => ''
        );

        $transport->setData('container', $container);

        return $this;
    }
}