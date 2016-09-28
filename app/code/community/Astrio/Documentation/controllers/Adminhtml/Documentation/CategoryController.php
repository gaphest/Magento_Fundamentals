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
class Astrio_Documentation_Adminhtml_Documentation_CategoryController extends Mage_Adminhtml_Controller_Action
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
            ->_setActiveMenu('astrio/documentation/category')
            ->_addBreadcrumb($this->__('Category Management'), $this->__('Category Management'));

        $this->_title($this->__('Category Management'));

        return $this;
    }

    /**
     * Index action
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('astrio_documentation/adminhtml_documentation_category'));
        $this->renderLayout();
    }

    /**
     * Show Category Management edit/create page
     */
    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');

        /**
         * @var $model Astrio_Documentation_Model_Category
         */
        $model = Mage::getModel('astrio_documentation/category')->load($id);
        if ($model->getId() || $id == 0) {
            $data = $this->_getSession()->getFormData(true);
            if (!empty($data)) {
                $model->addData($data);
            }

            Mage::register('astrio_documentation_category', $model);

            $this->_initAction();
            $this->_title($this->__($id == 0 ? 'New Category' : 'Edit Category'));
            $this->_addContent($this->getLayout()->createBlock('astrio_documentation/adminhtml_documentation_category_edit'));
            $this->renderLayout();
        } else {
            $this->_getSession()->addError($this->__('Category does not exist'));
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
     * Category Management save action
     *
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            try {
                /**
                 * @var $model Astrio_Documentation_Model_Category
                 */
                $model = Mage::getModel('astrio_documentation/category')->load($this->getRequest()->getParam('id'));
                $model->addData($data);
                // save and redirect
                $model->save();

                $this->_getSession()->addSuccess($this->__('The Category has been saved.'));

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
                $this->_getSession()->addException($e, $this->__('An error occurred while saving Category.'));
                $this->_getSession()->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
            return;
        }
        $this->_getSession()->addError($this->__('Unable to find Category to save'));
        $this->_redirect('*/*/');
    }

    /**
     * Category Management delete action
     */
    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                /**
                 * @var $model Astrio_Documentation_Model_Category
                 */
                $model = Mage::getModel('astrio_documentation/category');
                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();
                $this->_getSession()->addSuccess($this->__('The Category has been deleted.'));
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
     */
    public function gridAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('astrio_documentation/adminhtml_documentation_category_grid')->toHtml());
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

        return $session->isAllowed('astrio/documentation/category');
    }
}
