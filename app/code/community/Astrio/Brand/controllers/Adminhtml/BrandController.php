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
 * @package    Astrio_Brand
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Astrio
 *
 * @category Astrio
 * @package Astrio_Brand
 * @author Eldaniz Gasymov <e.gasymov@astrio.net>
 * @see Mage_Adminhtml_Catalog_ProductController
 */
class Astrio_Brand_Adminhtml_BrandController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Retrieve session model
     *
     * @return Mage_Adminhtml_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }

    /**
     * Initialize brand from request parameters
     *
     * @return Astrio_Brand_Model_Brand
     */
    protected function _initBrand()
    {
        $this->_title($this->__('Brands'))
            ->_title($this->__('Manage Brands'));

        $brandId  = (int) $this->getRequest()->getParam('id');
        $brand    = Mage::getModel('astrio_brand/brand')
            ->setStoreId($this->getRequest()->getParam('store', 0));

        $brand->setData('_edit_mode', true);
        if ($brandId) {
            try {
                $brand->load($brandId);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        Mage::register('brand', $brand);
        Mage::register('current_brand', $brand);
        Mage::getSingleton('cms/wysiwyg_config')->setStoreId($this->getRequest()->getParam('store'));
        return $brand;
    }

    /**
     * Product list page
     */
    public function indexAction()
    {
        $this->_title($this->__('Brands'))
            ->_title($this->__('Manage Brands'));

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Create new product page
     */
    public function newAction()
    {
        $brand = $this->_initBrand();

        $this->_title($this->__('New Brand'));

        Mage::dispatchEvent('astrio_brand_new_action', array('brand' => $brand));

        $this->loadLayout();

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $block = $this->getLayout()->getBlock('catalog.wysiwyg.js');
        if ($block) {
            $block->setStoreId($brand->getStoreId());
        }

        $this->renderLayout();
    }

    /**
     * Product edit form
     */
    public function editAction()
    {
        $brandId = (int) $this->getRequest()->getParam('id');
        $brand = $this->_initBrand();

        if ($brandId && !$brand->getId()) {
            $this->_getSession()->addError(Mage::helper('astrio_brand')->__('This brand no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }

        $this->_title($brand->getName());

        Mage::dispatchEvent('astrio_brand_edit_action', array('brand' => $brand));

        $this->loadLayout();

        if (!Mage::app()->isSingleStoreMode() && ($switchBlock = $this->getLayout()->getBlock('store_switcher'))) {
            $switchBlock->setDefaultStoreName($this->__('Default Values'))
                ->setWebsiteIds($brand->getWebsiteIds())
                ->setSwitchUrl(
                    $this->getUrl('*/*/*', array('_current' => true, 'active_tab' => null, 'tab' => null, 'store' => null))
                );
        }

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $block = $this->getLayout()->getBlock('catalog.wysiwyg.js');
        if ($block) {
            $block->setStoreId($brand->getStoreId());
        }

        $this->renderLayout();
    }

    /**
     * WYSIWYG editor action for ajax request
     *
     */
    public function wysiwygAction()
    {
        $elementId = $this->getRequest()->getParam('element_id', md5(microtime()));
        $storeId = $this->getRequest()->getParam('store_id', 0);
        $storeMediaUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);

        $content = $this->getLayout()->createBlock('adminhtml/catalog_helper_form_wysiwyg_content', '', array(
            'editor_element_id' => $elementId,
            'store_id'          => $storeId,
            'store_media_url'   => $storeMediaUrl,
        ));
        $this->getResponse()->setBody($content->toHtml());
    }

    /**
     * Product grid for AJAX request
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Validate product
     *
     */
    public function validateAction()
    {
        $response = new Varien_Object();
        $response->setError(false);

        try {
            $brandData = $this->getRequest()->getPost('brand');

            /* @var $brand Astrio_Brand_Model_Brand */
            $brand = Mage::getModel('astrio_brand/brand');
            $brand->setData('_edit_mode', true);
            if ($storeId = $this->getRequest()->getParam('store')) {
                $brand->setStoreId($storeId);
            }
            if ($brandId = $this->getRequest()->getParam('id')) {
                $brand->load($brandId);
            }

            $dateFields = array();
            $attributes = $brand->getAttributes();
            foreach ($attributes as $attrKey => $attribute) {
                if ($attribute->getBackend()->getType() == 'datetime') {
                    if (array_key_exists($attrKey, $brandData) && $brandData[$attrKey] != '') {
                        $dateFields[] = $attrKey;
                    }
                }
            }
            $brandData = $this->_filterDates($brandData, $dateFields);
            $brand->addData($brandData);

            $brand->validate();
        }
        catch (Mage_Eav_Model_Entity_Attribute_Exception $e) {
            $response->setError(true);
            $response->setAttribute($e->getAttributeCode());
            $response->setMessage($e->getMessage());
        } catch (Mage_Core_Exception $e) {
            $response->setError(true);
            $response->setMessage($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_initLayoutMessages('adminhtml/session');
            $response->setError(true);
            $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
        }

        $this->getResponse()->setBody($response->toJson());
    }

    /**
     * Initialize brand before saving
     */
    protected function _initBrandSave()
    {
        $brand     = $this->_initBrand();
        $brandData = $this->getRequest()->getPost('brand');

        /**
         * Websites
         */
        if (!isset($brandData['website_ids'])) {
            $brandData['website_ids'] = array();
        }

        /**
         * Init brand products
         */
        $products = $this->getRequest()->getPost('product_ids');
        if (isset($products)) {
            $brand->setProductsData(Mage::helper('adminhtml/js')->decodeGridSerializedInput($products));
        }

        $brand->addData($brandData);

        if (Mage::app()->isSingleStoreMode()) {
            $brand->setWebsiteIds(array(Mage::app()->getStore(true)->getWebsite()->getId()));
        }

        /**
         * Create Permanent Redirect for old URL key
         */
        if ($brand->getId() && isset($brandData['url_key_create_redirect'])) {
            $brand->setData('save_rewrites_history', (bool)$brandData['url_key_create_redirect']);
        }

        /**
         * Check "Use Default Value" checkboxes values
         */
        if ($useDefaults = $this->getRequest()->getPost('use_default')) {
            foreach ($useDefaults as $attributeCode) {
                $brand->setData($attributeCode, false);
            }
        }

        Mage::dispatchEvent(
            'astrio_brand_prepare_save',
            array('astrio_brand' => $brand, 'request' => $this->getRequest())
        );

        return $brand;
    }

    /**
     * Save brand action
     */
    public function saveAction()
    {
        $storeId        = $this->getRequest()->getParam('store');
        $redirectBack   = $this->getRequest()->getParam('back', false);
        $brandId      = $this->getRequest()->getParam('id');

        $data = $this->getRequest()->getPost();
        if ($data) {
            $brand = $this->_initBrandSave();

            try {
                $brand->save();
                $brandId = $brand->getId();

                if (isset($data['copy_to_stores'])) {
                    $this->_copyAttributesBetweenStores($data['copy_to_stores'], $brand);
                }

                $this->_getSession()->addSuccess($this->__('The brand has been saved.'));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage())
                    ->setBrandData($data);
                $redirectBack = true;
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage());
                $redirectBack = true;
            }
        }

        if ($redirectBack) {
            $this->_redirect('*/*/edit', array(
                'id'    => $brandId,
                '_current' => true
            ));
        } else {
            $this->_redirect('*/*/', array('store' => $storeId));
        }
    }

    /**
     * Delete product action
     */
    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $brand = Mage::getModel('astrio_brand/brand')
                ->load($id);
            try {
                $brand->delete();
                $this->_getSession()->addSuccess($this->__('The brand has been deleted.'));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->getResponse()
            ->setRedirect($this->getUrl('*/*/', array('store' => $this->getRequest()->getParam('store'))));
    }

    /**
     * Mass delete action
     */
    public function massDeleteAction()
    {
        $brandIds = $this->getRequest()->getParam('brand');
        if (!is_array($brandIds)) {
            $this->_getSession()->addError($this->__('Please select brand(s).'));
        } else {
            if (!empty($brandIds)) {
                try {
                    foreach ($brandIds as $brandId) {
                        $brand = Mage::getSingleton('astrio_brand/brand')->load($brandId);
                        Mage::dispatchEvent('astrio_brand_controller_brand_delete', array('brand' => $brand));
                        $brand->delete();
                    }
                    $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) have been deleted.', count($brandIds))
                    );
                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Products action
     */
    public function productsAction()
    {
        $this->_initBrand();
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Products grid action
     */
    public function productsGridAction()
    {
        $this->_initBrand();
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Clean image files cache
     */
    public function cleanImagesAction()
    {
        try {
            Mage::getModel('astrio_brand/brand_image')->clearCache();
            Mage::dispatchEvent('clean_astrio_brand_images_cache_after');
            $this->_getSession()->addSuccess(
                Mage::helper('adminhtml')->__('The image cache was cleaned.')
            );
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addException(
                $e,
                Mage::helper('adminhtml')->__('An error occurred while clearing the image cache.')
            );
        }
        $this->_redirect('*/cache');
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('astrio/brand');
    }

    /**
     * Widget chooser action
     */
    public function widgetChooserAction()
    {
        $uniqId = $this->getRequest()->getParam('uniq_id');
        $massAction = $this->getRequest()->getParam('use_massaction', false);
        $grid = $this->getLayout()->createBlock(
            'astrio_brand/adminhtml_brand_widget_chooser',
            'brand_widget_chooser',
            array(
                'id'                => $uniqId,
                'use_massaction'    => $massAction,
            )
        );
        $html = $grid->toHtml();
        $this->getResponse()->setBody($html);
    }
}