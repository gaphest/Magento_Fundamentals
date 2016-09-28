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
class Astrio_SpecialProducts_Adminhtml_SpecialProducts_SetController extends Mage_Adminhtml_Controller_Action
{

    protected $_files = null;

    /**
     * @var Astrio_SpecialProducts_Helper_Data
     */
    protected $_helper = null;

    /**
     * Get Astrio_SpecialProducts helper
     *
     * @return Astrio_SpecialProducts_Helper_Data
     */
    protected function _getHelper()
    {
        if (null === $this->_helper) {
            $this->_helper = Mage::helper('astrio_specialproducts');
        }

        return $this->_helper;
    }

    /**
     * Add content
     *
     * @param  Mage_Core_Block_Abstract $block block
     * @return $this
     */
    protected function _addContent(Mage_Core_Block_Abstract $block)
    {
        $this->getLayout()->getBlock('content')->append($block);
        return $this;
    }

    /**
     * Init layout, menu and breadcrumb
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('astrio/specialproducts')
            ->_addBreadcrumb($this->_getHelper()->__('Special Products Sets Management'), $this->_getHelper()->__('Special Products Sets Management'));

        $this->_title($this->_getHelper()->__('Special Products Sets Management'));

        return $this;
    }

    /**
     * Grid page
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('astrio_specialproducts/adminhtml_specialProducts_set'));
        $this->renderLayout();
    }

    /**
     * Edit/create page
     */
    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        /**
         * @var $set Astrio_SpecialProducts_Model_Set
         */
        $set = Mage::getModel('astrio_specialproducts/set')->load($id);

        if ($set->getId() || $id == 0) {
            $data = $this->_getSession()->getFormData(true);
            if (!empty($data)) {
                $set->addData($data);
            }

            Mage::register('astrio_specialproducts_set', $set);

            $this->_initAction();
            $this->_title($this->_getHelper()->__($id == 0 ? 'New Special Products Set' : 'Edit Special Products Set'));
            $this->_addContent($this->getLayout()->createBlock('astrio_specialproducts/adminhtml_specialProducts_set_edit'));
            $this->renderLayout();
            return;
        }

        $this->_getSession()->addError($this->_getHelper()->__('Special Products Set does not exist'));
        $this->_redirect('*/*/');
    }

    /**
     * New action
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Get files
     *
     * @return array|null
     */
    public function getFiles()
    {
        if ($this->_files === null) {
            $files = array();
            $fix = function (&$files, $values, $prop) use (&$fix) {
                foreach ($values as $key => $value) {
                    if (is_array($value)) {
                        $fix($files[$key], $value, $prop);
                    } else {
                        $files[$key][$prop] = $value;
                    }
                }
            };
            foreach ($_FILES as $name => $props) {
                foreach ($props as $prop => $value) {
                    if (is_array($value)) {
                        $fix($files[$name], $value, $prop);
                    } else {
                        $files[$name][$prop] = $value;
                    }
                }
            }

            $this->_files = $files;
        }

        return $this->_files;
    }

    /**
     * Process image file
     *
     * @param  string $name file name
     * @param  string $path file path
     * @return null
     */
    public function processImageFile($name, $path)
    {
        $file = $this->getFiles();

        $keys = explode('/', $name);
        foreach ($keys as $key) {
            if (!isset($file[$key])) {
                $file = false;
                break;
            }

            $file = $file[$key];
        }

        if ($file && isset($file['name']) && $file['name'] != '') {
            try {
                $uploader = new Astrio_SpecialProducts_Model_Uploader($file);
                $uploader->setAllowCreateFolders(true);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);
                $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                $uploader->save($path, $uploader->getUploadedFileName());
                return $uploader->getUploadedFileName();
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }

        $post = $this->getRequest()->getPost();

        $imagePost = $post;
        foreach ($keys as $key) {
            if (!isset($imagePost[$key])) {
                $imagePost = false;
                break;
            }

            $imagePost = $imagePost[$key];
        }

        if (is_array($imagePost) && !empty($imagePost['delete'])) {
            return null;
        }

        return is_array($imagePost) && !empty($imagePost['value']) ? $imagePost['value'] : null;
    }

    /**
     * Menu Management save action
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost('set')) {
            try {
                if (isset($data['products'])) {
                    if (is_array($data['products'])) {
                        /**
                         * @var $jsHelper Mage_Adminhtml_Helper_Js
                         */
                        $jsHelper = Mage::helper('adminhtml/js');
                        foreach ($data['products'] as $websiteId => $products) {
                            $data['products'][$websiteId] = $jsHelper->decodeGridSerializedInput($products);
                        }
                    } else {
                        $data['products'] = array();
                    }
                }

                /**
                 * @var $set Astrio_SpecialProducts_Model_Set
                 */
                $set = Mage::getModel('astrio_specialproducts/set')->load($this->getRequest()->getParam('id'));

                $set->addData($data);

                $back = false;

                Mage::dispatchEvent('adminhtml_specialproducts_set_controller_save', array('controller' => $this, 'set' => $set));

                $labelsDir = Mage::getBaseDir('media') . DS . str_replace('/', DS, Astrio_SpecialProducts_Model_Set_Label::IMAGE_PATH);
                $labelImage = $this->processImageFile('set/label_data/image', $labelsDir);
                $labelData = $set->getData('label_data');
                $labelData['image'] = $labelImage;
                $set->setData('label_data', $labelData);

                // save and redirect
                $set->save();

                $this->_getSession()->addSuccess($this->_getHelper()->__('The Special Products Set has been saved.'));

                if ($this->getRequest()->getParam('back') || $back) {
                    $this->_redirect('*/*/edit', array('id' => $set->getId()));
                    return;
                }

                $this->_redirect('*/*/');
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_getSession()->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            } catch (Exception $e) {
                $this->_getSession()->addException($e, $this->_getHelper()->__('An error occurred while saving Special Products Set.'));
                $this->_getSession()->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }

            return;
        }
        $this->_getSession()->addError($this->_getHelper()->__('Unable to find Special Products Set to save'));
        $this->_redirect('*/*/');
    }

    /**
     * delete action
     */
    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                /**
                 * @var $model Astrio_SpecialProducts_Model_Set
                 */
                $model = Mage::getModel('astrio_specialproducts/set');
                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();
                $this->_getSession()->addSuccess($this->_getHelper()->__('The Special Products Set has been deleted.'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }

        $this->_redirect('*/*/');
    }

    /**
     * Grid action
     */
    public function gridAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('astrio_specialproducts/adminhtml_specialProducts_set_grid')->toHtml());
    }

    /**
     * Store products grid action
     */
    public function storeProductsGridAction()
    {
        $id = $this->getRequest()->getParam('id');
        /**
         * @var $set Astrio_SpecialProducts_Model_Set
         */
        $set = Mage::getModel('astrio_specialproducts/set')->load($id);
        Mage::register('astrio_specialproducts_set', $set);

        $storeId = $this->getRequest()->getParam('store_id');

        $gridId = 'specialproducts_edit_tab_products_' . $storeId;

        /**
         * @var $grid Astrio_SpecialProducts_Block_Adminhtml_SpecialProducts_Set_Edit_Tab_Products
         */
        $grid = $this->getLayout()->createBlock(
            'astrio_specialproducts/adminhtml_specialProducts_set_edit_tab_products',
            $gridId,
            array(
                'id'        => $gridId,
                'store_id'  => $storeId
            )
        );

        $this->getResponse()->setBody($grid->toHtml());
    }

    /**
     * Reindex action
     */
    public function reindexAction()
    {
        $id = $this->getRequest()->getParam('id');
        /**
         * @var $set Astrio_SpecialProducts_Model_Set
         */
        $set = Mage::getModel('astrio_specialproducts/set')->load($id);
        if ($set->getId()) {
            if ($set->getIsAuto()) {
                $set->reindex();
                $this->_getSession()->addSuccess($this->_getHelper()->__('Special Products Set was successfully reindexed'));
            } else {
                $this->_getSession()->addError($this->_getHelper()->__('Special Products Set is not auto'));
            }
            $this->_redirect('*/*/edit', array('id' => $set->getId()));
            return;
        }

        $this->_getSession()->addError($this->_getHelper()->__('Unable to find Special Products Set to reindex'));
        $this->_redirect('*/*/');
    }

    /**
     * Get if is allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        /**
         * @var $session Mage_Admin_Model_Session
         */
        $session = Mage::getSingleton('admin/session');
        return $session->isAllowed('astrio/specialproducts');
    }

    /**
     * Chooser Source action
     */
    public function widgetChooserAction()
    {
        $uniqId = $this->getRequest()->getParam('uniq_id');
        $massAction = $this->getRequest()->getParam('use_massaction', false);
        $grid = $this->getLayout()->createBlock(
            'astrio_specialproducts/adminhtml_specialProducts_set_page_widget_chooser',
            'specialProducts_set_page_widget_chooser',
            array(
                'id'                => $uniqId,
                'use_massaction'    => $massAction,
            )
        );
        
        $html = $grid->toHtml();
        $this->getResponse()->setBody($html);
    }
}
