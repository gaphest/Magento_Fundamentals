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
class Astrio_News_Adminhtml_NewsController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Initialize news from request parameters
     *
     * @return Astrio_News_Model_News
     */
    protected function _initNews()
    {
        /**
         * @var $news Astrio_News_Model_News
         */
        $this->_title($this->__('News'))
            ->_title($this->__('Manage News'));

        $newsId  = (int) $this->getRequest()->getParam('id');
        $news    = Mage::getModel('astrio_news/news')
            ->setStoreId($this->getRequest()->getParam('store', 0));

        $news->setData('_edit_mode', true);
        if ($newsId) {
            try {
                $news->load($newsId);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        //set default value for 'published_at' in default store view locale timezone
        if (!$news->getData('published_at')) {
            /**
             * @var $coreDate Mage_Core_Model_Date
             */
            $coreDate = Mage::getSingleton('core/date');
            $news->setData('published_at', $coreDate->date());
        }

        Mage::register('news', $news);
        Mage::register('current_news', $news);
        Mage::getSingleton('cms/wysiwyg_config')->setStoreId($this->getRequest()->getParam('store'));
        return $news;
    }

    /**
     * News list page
     */
    public function indexAction()
    {
        $this->_title($this->__('News'))
            ->_title($this->__('Manage News'));

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Create new news page
     */
    public function newAction()
    {
        $news = $this->_initNews();

        $this->_title($this->__('New News'));

        Mage::dispatchEvent('astrio_news_new_action', array('news' => $news));

        $this->loadLayout();

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $block = $this->getLayout()->getBlock('catalog.wysiwyg.js');
        if ($block) {
            $block->setStoreId($news->getStoreId());
        }

        $this->renderLayout();
    }

    /**
     * News edit form
     */
    public function editAction()
    {
        $newsId = (int) $this->getRequest()->getParam('id');
        $news = $this->_initNews();

        if ($newsId && !$news->getId()) {
            $this->_getSession()->addError(Mage::helper('astrio_news')->__('This news no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }

        $this->_title($news->getTitle());

        Mage::dispatchEvent('astrio_news_edit_action', array('news' => $news));

        $this->loadLayout();

        if (!Mage::app()->isSingleStoreMode() && ($switchBlock = $this->getLayout()->getBlock('store_switcher'))) {
            /**
             * @var $switchBlock Mage_Adminhtml_Block_Store_Switcher
             */
            $switchBlock->setDefaultStoreName($this->__('Default Values'))
                //->setStoreIds($news->getStoreIds())
                ->setSwitchUrl(
                    $this->getUrl('*/*/*', array('_current' => true, 'active_tab' => null, 'tab' => null, 'store' => null))
                );
        }

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $block = $this->getLayout()->getBlock('catalog.wysiwyg.js');
        if ($block) {
            $block->setStoreId($news->getStoreId());
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
     * News grid for AJAX request
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Validate news
     */
    public function validateAction()
    {
        $response = new Varien_Object();
        $response->setError(false);

        try {
            $newsData = $this->getRequest()->getPost('news');

            /* @var $news Astrio_News_Model_News */
            $news = Mage::getModel('astrio_news/news');
            $news->setData('_edit_mode', true);
            if ($storeId = $this->getRequest()->getParam('store')) {
                $news->setStoreId($storeId);
            }
            if ($newsId = $this->getRequest()->getParam('id')) {
                $news->load($newsId);
            }

            $dateFields = array();
            $attributes = $news->getAttributes();
            foreach ($attributes as $attrKey => $attribute) {
                if ($attribute->getBackend()->getType() == 'datetime') {
                    if (array_key_exists($attrKey, $newsData) && $newsData[$attrKey] != '') {
                        $dateFields[] = $attrKey;
                    }
                }
            }
            $newsData = $this->_filterDates($newsData, $dateFields);
            $news->addData($newsData);

            $news->validate();
        } catch (Mage_Eav_Model_Entity_Attribute_Exception $e) {
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
     * Initialize news before saving
     */
    protected function _initNewsSave()
    {
        $news     = $this->_initNews();
        $newsData = $this->getRequest()->getPost('news');

        /**
         * Stores
         */
        if (!isset($newsData['store_ids'])) {
            $newsData['store_ids'] = array();
        }

        /**
         * Categories
         */
        if (!isset($newsData['category_ids'])) {
            $newsData['category_ids'] = array();
        }

        $news->addData($newsData);

        if (Mage::app()->isSingleStoreMode()) {
            $news->setStoreIds(array(Mage::app()->getStore(true)->getId()));
        }

        /**
         * Check "Use Default Value" checkboxes values
         */
        if ($useDefaults = $this->getRequest()->getPost('use_default')) {
            foreach ($useDefaults as $attributeCode) {
                $news->setData($attributeCode, false);
            }
        }

        Mage::dispatchEvent(
            'astrio_news_prepare_save',
            array('news' => $news, 'request' => $this->getRequest())
        );

        return $news;
    }

    /**
     * Save news action
     */
    public function saveAction()
    {
        $storeId        = $this->getRequest()->getParam('store');
        $redirectBack   = $this->getRequest()->getParam('back', false);
        $newsId      = $this->getRequest()->getParam('id');

        $data = $this->getRequest()->getPost();
        if ($data) {
            $news = $this->_initNewsSave();

            try {
                $news->save();
                $newsId = $news->getId();

                if (isset($data['copy_to_stores'])) {
                    $this->_copyAttributesBetweenStores($data['copy_to_stores'], $news);
                }

                $this->_getSession()->addSuccess($this->__('The news has been saved.'));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage())
                    ->setNewsData($data);
                $redirectBack = true;
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage());
                $redirectBack = true;
            }
        }

        if ($redirectBack) {
            $this->_redirect('*/*/edit', array(
                'id'    => $newsId,
                '_current' => true
            ));
        } else {
            $this->_redirect('*/*/', array('store' => $storeId));
        }
    }

    /**
     * Duplicates news attributes between stores.
     *
     * @param array $stores list of store pairs: array(fromStore => toStore, fromStore => toStore,..)
     * @param Astrio_News_Model_News $news whose attributes should be copied
     * @return $this
     */
    protected function _copyAttributesBetweenStores(array $stores, Astrio_News_Model_News $news)
    {
        foreach ($stores as $storeTo => $storeFrom) {
            $newsInStore = Mage::getModel('astrio_news/news')
                ->setStoreId($storeFrom)
                ->load($news->getId());
            Mage::dispatchEvent('astrio_news_duplicate_attributes', array(
                'news'     => $newsInStore,
                'storeTo'   => $storeTo,
                'storeFrom' => $storeFrom,
            ));
            $newsInStore->setStoreId($storeTo)->save();
        }

        return $this;
    }

    /**
     * Delete news action
     */
    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $news = Mage::getModel('astrio_news/news')
                ->load($id);
            try {
                $news->delete();
                $this->_getSession()->addSuccess($this->__('The news has been deleted.'));
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
        $newsIds = $this->getRequest()->getParam('news');
        if (!is_array($newsIds)) {
            $this->_getSession()->addError($this->__('Please select news(s).'));
        } else {
            if (!empty($newsIds)) {
                try {
                    foreach ($newsIds as $newsId) {
                        $news = Mage::getSingleton('astrio_news/news')->load($newsId);
                        Mage::dispatchEvent('astrio_news_controller_news_delete', array('news' => $news));
                        $news->delete();
                    }

                    $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) have been deleted.', count($newsIds))
                    );
                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Clean image files cache
     */
    public function cleanImagesAction()
    {
        try {
            Mage::getModel('astrio_news/news_image')->clearCache();
            Mage::getModel('astrio_news/category_image')->clearCache();
            Mage::dispatchEvent('clean_astrio_news_images_cache_after');
            $this->_getSession()->addSuccess(
                Mage::helper('adminhtml')->__('The image cache was cleaned.')
            );
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
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
        return Mage::getSingleton('admin/session')->isAllowed('astrio/news/news');
    }

    /**
     * Widget choose action
     */
    public function widgetChooserAction()
    {
        $uniqId = $this->getRequest()->getParam('uniq_id');
        $massAction = $this->getRequest()->getParam('use_massaction', false);
        $grid = $this->getLayout()->createBlock(
            'astrio_news/adminhtml_news_widget_chooser',
            'news_widget_chooser',
            array(
                'id'                => $uniqId,
                'use_massaction'    => $massAction,
            )
        );

        $html = $grid->toHtml();
        $this->getResponse()->setBody($html);
    }
}
