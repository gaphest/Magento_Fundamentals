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
class Astrio_News_Adminhtml_News_CategoryController extends Mage_Adminhtml_Controller_Action
{
    
    /**
     * Initialize news category from request parameters
     *
     * @return Astrio_News_Model_Category
     */
    protected function _initNewsCategory()
    {
        /**
         * @var $category Astrio_News_Model_Category
         */
        $this->_title($this->__('News Category'))
            ->_title($this->__('Manage News Categories'));

        $categoryId  = (int) $this->getRequest()->getParam('id');
        $category    = Mage::getModel('astrio_news/category')
            ->setStoreId($this->getRequest()->getParam('store', 0));

        $category->setData('_edit_mode', true);
        if ($categoryId) {
            try {
                $category->load($categoryId);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        Mage::register('news_category', $category);
        Mage::register('current_news_category', $category);
        Mage::getSingleton('cms/wysiwyg_config')->setStoreId($this->getRequest()->getParam('store'));
        return $category;
    }

    /**
     * News categories list page
     */
    public function indexAction()
    {
        $this->_title($this->__('News Categories'))
            ->_title($this->__('Manage News Categories'));

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Create new news category page
     */
    public function newAction()
    {
        $category = $this->_initNewsCategory();

        $this->_title($this->__('New News Category'));

        Mage::dispatchEvent('astrio_news_category_new_action', array('category' => $category));

        $this->loadLayout();

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $block = $this->getLayout()->getBlock('catalog.wysiwyg.js');
        if ($block) {
            $block->setStoreId($category->getStoreId());
        }

        $this->renderLayout();
    }

    /**
     * News category edit form
     */
    public function editAction()
    {
        $categoryId = (int) $this->getRequest()->getParam('id');
        $category = $this->_initNewsCategory();

        if ($categoryId && !$category->getId()) {
            $this->_getSession()->addError(Mage::helper('astrio_news')->__('This news category no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }

        $this->_title($category->getTitle());

        Mage::dispatchEvent('astrio_news_category_edit_action', array('category' => $category));

        $this->loadLayout();

        if (!Mage::app()->isSingleStoreMode() && ($switchBlock = $this->getLayout()->getBlock('store_switcher'))) {
            /**
             * @var $switchBlock Mage_Adminhtml_Block_Store_Switcher
             */
            $switchBlock->setDefaultStoreName($this->__('Default Values'))
                ->setSwitchUrl(
                    $this->getUrl('*/*/*', array('_current' => true, 'active_tab' => null, 'tab' => null, 'store' => null))
                );
        }

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $block = $this->getLayout()->getBlock('catalog.wysiwyg.js');
        if ($block) {
            $block->setStoreId($category->getStoreId());
        }

        $this->renderLayout();
    }

    /**
     * WYSIWYG editor action for ajax request
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
     * News categories grid for AJAX request
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Validate news category
     */
    public function validateAction()
    {
        $response = new Varien_Object();
        $response->setError(false);

        try {
            $categoryData = $this->getRequest()->getPost('news_category');

            /* @var $category Astrio_News_Model_Category */
            $category = Mage::getModel('astrio_news/category');
            $category->setData('_edit_mode', true);
            if ($storeId = $this->getRequest()->getParam('store')) {
                $category->setStoreId($storeId);
            }

            if ($categoryId = $this->getRequest()->getParam('id')) {
                $category->load($categoryId);
            }

            $dateFields = array();
            $attributes = $category->getAttributes();
            foreach ($attributes as $attrKey => $attribute) {
                if ($attribute->getBackend()->getType() == 'datetime') {
                    if (array_key_exists($attrKey, $categoryData) && $categoryData[$attrKey] != '') {
                        $dateFields[] = $attrKey;
                    }
                }
            }

            $categoryData = $this->_filterDates($categoryData, $dateFields);
            $category->addData($categoryData);

            $category->validate();
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
     * Initialize news category before saving
     */
    protected function _initNewsCategorySave()
    {
        $category     = $this->_initNewsCategory();
        $categoryData = $this->getRequest()->getPost('news_category');       

        $category->addData($categoryData);

        /**
         * Check "Use Default Value" checkboxes values
         */
        if ($useDefaults = $this->getRequest()->getPost('use_default')) {
            foreach ($useDefaults as $attributeCode) {
                $category->setData($attributeCode, false);
            }
        }

        Mage::dispatchEvent(
            'astrio_news_category_prepare_save',
            array('news_category' => $category, 'request' => $this->getRequest())
        );

        return $category;
    }

    /**
     * Save news category action
     */
    public function saveAction()
    {
        $storeId        = $this->getRequest()->getParam('store');
        $redirectBack   = $this->getRequest()->getParam('back', false);
        $categoryId     = $this->getRequest()->getParam('id');

        $data = $this->getRequest()->getPost();
        if ($data) {
            $category = $this->_initNewsCategorySave();

            try {
                $category->save();
                $categoryId = $category->getId();

                if (isset($data['copy_to_stores'])) {
                    $this->_copyAttributesBetweenStores($data['copy_to_stores'], $category);
                }

                $this->_getSession()->addSuccess($this->__('The news category has been saved.'));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage())
                    ->setNewsCategoryData($data);
                $redirectBack = true;
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage());
                $redirectBack = true;
            }
        }

        if ($redirectBack) {
            $this->_redirect('*/*/edit', array(
                'id'    => $categoryId,
                '_current' => true
            ));
        } else {
            $this->_redirect('*/*/', array('store' => $storeId));
        }
    }

    /**
     * Duplicates news category attributes between stores.
     *
     * @param array $stores list of store pairs: array(fromStore => toStore, fromStore => toStore,..)
     * @param Astrio_News_Model_Category $category whose attributes should be copied
     * @return $this
     */
    protected function _copyAttributesBetweenStores(array $stores, Astrio_News_Model_Category $category)
    {
        foreach ($stores as $storeTo => $storeFrom) {
            $categoryInStore = Mage::getModel('astrio_news/category')
                ->setStoreId($storeFrom)
                ->load($category->getId());
            Mage::dispatchEvent('astrio_news_category_duplicate_attributes', array(
                'category'  => $categoryInStore,
                'storeTo'   => $storeTo,
                'storeFrom' => $storeFrom,
            ));
            $categoryInStore->setStoreId($storeTo)->save();
        }

        return $this;
    }

    /**
     * Delete news category action
     */
    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $category = Mage::getModel('astrio_news/category')
                ->load($id);
            try {
                $category->delete();
                $this->_getSession()->addSuccess($this->__('The news category has been deleted.'));
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
        $categoryIds = $this->getRequest()->getParam('categories');
        if (!is_array($categoryIds)) {
            $this->_getSession()->addError($this->__('Please select news categories.'));
        } else {
            if (!empty($categoryIds)) {
                try {
                    foreach ($categoryIds as $categoryId) {
                        $category = Mage::getSingleton('astrio_news/category')->load($categoryId);
                        Mage::dispatchEvent('astrio_news_category_controller_category_delete', array('news' => $category));
                        $category->delete();
                    }
                    $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) have been deleted.', count($categoryIds))
                    );
                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('astrio/news/category');
    }

    /**
     * Widget chooser action
     */
    public function widgetChooserAction()
    {
        $uniqId = $this->getRequest()->getParam('uniq_id');
        $massAction = $this->getRequest()->getParam('use_massaction', false);
        $grid = $this->getLayout()->createBlock(
            'astrio_news/adminhtml_news_category_widget_chooser',
            'news_category_widget_chooser',
            array(
                'id'                => $uniqId,
                'use_massaction'    => $massAction,
            )
        );

        $html = $grid->toHtml();
        $this->getResponse()->setBody($html);
    }
}
