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
 * @package    Astrio_Callme
 * @copyright  Copyright (c) 2010-2013 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 *  Call Status controller
 *
 * @category   Astrio
 * @package    Astrio_Callme
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_Callme_Adminhtml_Call_StatusController extends Mage_Adminhtml_Controller_Action
{

    /**
     * @var Astrio_Callme_Helper_Data
     */
    protected $_helper = null;

    /**
     * @return Astrio_Callme_Helper_Data
     */
    protected function _getHelper()
    {
        if (null === $this->_helper) {
            $this->_helper = Mage::helper('astrio_callme');
        }
        return $this->_helper;
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
     * Init action
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('astrio/callme/listing')
            ->_addBreadcrumb($this->__('Call Me'), $this->__('Call Statuses'))
        ;
        $this->_title($this->__('Call Me'));
        return $this;
    }

    /**
     * Index action
     */
    public function indexAction()
    {
        $this->_title($this->__('Call Me'))->_title($this->__('Call Statuses'));
        $this->_initAction()->renderLayout();
    }

    /**
     * Menu Management grid action
     */
    public function gridAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('astrio_callme/adminhtml_call_status_grid')->toHtml());
    }

    /**
     * Edit status action
     */
    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('astrio_callme/call_status')->load($id);

        if ($model->getId() || is_null($id)) {
            Mage::register('astrio_call_status', $model);
            $this->_title($this->__('Call Me'))->_title($this->__('Call Statuses'))->_title($this->__($id == 0 ? 'New Status' : 'Edit Status'));
            $this->_initAction()->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError($this->_getHelper()->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * Save action
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            /** @var Astrio_Callme_Model_Call $model */
            $model = Mage::getModel('astrio_callme/call_status')->load($this->getRequest()->getParam('id'));
            $model->addData($data);

            try {
                $validateResult = $model->validate();
                if (is_array($validateResult)) {
                    Mage::throwException($validateResult[0]);
                }

                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess($this->_getHelper()->__('Item was successfully saved'));
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError($this->_getHelper()->__('Unable to find item to save'));
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
     * Delete action
     */
    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $statusId = $this->getRequest()->getParam('id');

                $model = Mage::getModel('astrio_callme/call_status')->load($statusId);

                if (!$model->getId()) {
                    Mage::throwException($this->_getHelper()->__('Unable to find item to delete'));
                }

                /** @var Astrio_Callme_Model_Resource_Call_Collection $calls */
                $calls = Mage::getResourceModel('astrio_callme/call_collection');
                if ($calls->addStatusFilter($model)->getSize()) {
                    Mage::throwException($this->_getHelper()->__('There are calls to that status. Delete is not possible'));
                }

                if ($model->getCode() == $this->_getHelper()->getCallStatusAfterPost()) {
                    Mage::throwException($this->_getHelper()->__('Status is the default status. Delete is not possible'));
                }

                $model->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess($this->_getHelper()->__('Item was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Gets if is allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        /**
         * @var $session Mage_Admin_Model_Session
         */
        $session = Mage::getSingleton('admin/session');
        return $session->isAllowed('astrio/callme/statuses');
    }

}