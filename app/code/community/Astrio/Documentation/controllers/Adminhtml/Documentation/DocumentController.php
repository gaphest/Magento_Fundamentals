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
 * @package    Astrio_Documentation
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Astrio
 *
 * @category   Astrio
 * @package    Astrio_Documentation
 * @author     Eldaniz Gasymov <e.gasymov@astrio.net>
 */
class Astrio_Documentation_Adminhtml_Documentation_DocumentController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Custom constructor.
     *
     * @return void
     */
    protected function _construct()
    {
        // Define module dependent translate
        $this->setUsedModuleName('Astrio_Documentation');
    }

    /**
     * Add content
     *
     * @param Mage_Core_Block_Abstract $block block
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
            ->_setActiveMenu('astrio/documentation/document')
            ->_addBreadcrumb($this->__('Document Management'), $this->__('Document Management'));

        $this->_title($this->__('Document Management'));

        return $this;
    }

    /**
     * Index action
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('astrio_documentation/adminhtml_documentation_document'));
        $this->renderLayout();
    }

    /**
     * Show Document Management edit/create page
     */
    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');

        /**
         * @var $model Astrio_Documentation_Model_Document
         */
        $model = Mage::getModel('astrio_documentation/document')->load($id);
        if ($model->getId() || $id == 0) {
            $data = $this->_getSession()->getFormData(true);
            if (!empty($data)) {
                $model->addData($data);
            }

            Mage::register('astrio_documentation_document', $model);

            $this->_initAction();
            $this->_title($this->__($id == 0 ? 'New Document' : 'Edit Document'));
            $this->_addContent($this->getLayout()->createBlock('astrio_documentation/adminhtml_documentation_document_edit'));
            $this->renderLayout();
        } else {
            $this->_getSession()->addError($this->__('Document does not exist'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * New action
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Document Management save action
     *
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $data = isset($data['document']) && is_array($data['document']) ? $data['document'] : array();
            try {

                /**
                 * @var $model Astrio_Documentation_Model_Document
                 */
                $model = Mage::getModel('astrio_documentation/document')->load($this->getRequest()->getParam('id'));

                if ($data['type'] == Astrio_Documentation_Model_Document::TYPE_FILE) {
                    if (isset($_FILES['filename']) && !empty($_FILES['filename']) && file_exists($_FILES['filename']['tmp_name'])) {
                        $path = Mage::helper('astrio_documentation')->getBaseFilePath();
                        $uploader = new Varien_File_Uploader('filename');
                        $extensions = Mage::helper('astrio_documentation')->getAllowedExtensions();
                        if (!empty($extensions)) {
                            $uploader->setAllowedExtensions($extensions);
                        }
                        $uploader->setAllowCreateFolders(true);
                        $uploader->setAllowRenameFiles(true);
                        $uploader->setFilesDispersion(true);
                        $uploader->save($path, $uploader->getUploadedFileName());
                        $data['filename'] = $uploader->getUploadedFileName();
                        $data['file_size'] = filesize(Mage::helper('astrio_documentation')->getFullPath($data['filename']));
                    }
                } else {
                    $data['filename'] = null;
                }

                $model->addData($data);

                /**
                 * Init brand products
                 */
                if (isset($data['product_ids'])) {
                    $model->setProductsData(Mage::helper('adminhtml/js')->decodeGridSerializedInput($data['product_ids']));
                }

                // save and redirect
                $model->save();

                $this->_getSession()->addSuccess($this->__('The Document has been saved.'));

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_getSession()->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            } catch (Exception $e) {
                $this->_getSession()->addException($e, $this->__('An error occurred while saving Document.'));
                $this->_getSession()->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
            return;
        }
        $this->_getSession()->addError($this->__('Unable to find Document to save'));
        $this->_redirect('*/*/');
    }

    /**
     * Document Management delete action
     *
     */
    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                /**
                 * @var $model Astrio_Documentation_Model_Document
                 */
                $model = Mage::getModel('astrio_documentation/document');
                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();
                $this->_getSession()->addSuccess($this->__('The Document has been deleted.'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Menu Management grid action
     *
     */
    public function gridAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('astrio_documentation/adminhtml_documentation_document_grid')->toHtml());
    }

    /**
     * Products action
     */
    public function productsAction()
    {
        /**
         * @var $model Astrio_Documentation_Model_Document
         */
        $model = Mage::getModel('astrio_documentation/document')->load($this->getRequest()->getParam('id'));
        Mage::register('astrio_documentation_document', $model);

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Products grid action
     */
    public function productsGridAction()
    {
        /**
         * @var $model Astrio_Documentation_Model_Document
         */
        $model = Mage::getModel('astrio_documentation/document')->load($this->getRequest()->getParam('id'));
        Mage::register('astrio_documentation_document', $model);

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Download action
     */
    public function downloadAction()
    {
        $id = $this->getRequest()->getParam('id');
        $document = Mage::getModel('astrio_documentation/document')->load($id);
        if (!$document->getId() || !$document->isFile()) {
            $this->_forward('noRoute');
            return;
        }

        try {
            $response = $this->getResponse();
            Mage::helper('astrio_documentation')->loadDocument($document, $response);
            exit(0);
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Can not download file.'));
            $this->_redirect('*/*');
        }
    }

    /**
     * Get if Is allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        /**
         * @var $session Mage_Admin_Model_Session
         */
        $session = Mage::getSingleton('admin/session');

        return $session->isAllowed('astrio/documentation/document');
    }
}
