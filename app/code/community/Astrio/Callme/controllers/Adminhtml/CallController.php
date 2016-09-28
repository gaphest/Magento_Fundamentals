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
 *  Call Me Admin controller
 *
 * @category   Astrio
 * @package    Astrio_Callme
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_Callme_Adminhtml_CallController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_publicActions = array('view', 'index');

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
            ->_addBreadcrumb($this->__('Call Me'), $this->__('Calls'))
        ;
        return $this;
    }

    /**
     * Index action
     */
    public function indexAction()
    {
        $this->_title($this->__('Call Me'))->_title($this->__('Calls'));
        $this->_initAction()->renderLayout();
    }

    /**
     * Menu Management grid action
     */
    public function gridAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('astrio_callme/adminhtml_call_grid')->toHtml());
    }

    /**
     * View action
     */
    public function viewAction()
    {
        $id = $this->getRequest()->getParam('call_id');
        $call = Mage::getModel('astrio_callme/call')->load($id);

        if ($call->getId() || $id == 0) {
            Mage::register('astrio_callme_call', $call);

            $this->_title($this->__('Call Me'))->_title($this->__('Call View'));
            $this->_initAction()->renderLayout();

        } else {
            Mage::getSingleton('adminhtml/session')->addError($this->_getHelper()->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * Add call comment action
     */
    public function addCommentAction()
    {
        if ($call = $this->_initCall()) {
            try {
                $response = false;
                $data = $this->getRequest()->getPost('history');
                $notify = isset($data['is_admin_notified']) ? $data['is_admin_notified'] : false;
                $call->save();
                $call->addStatusHistoryComment($data['comment'], $data['status'], $notify);
                $this->loadLayout('empty');
                $this->renderLayout();
            }
            catch (Mage_Core_Exception $e) {
                $response = array(
                    'error'     => true,
                    'message'   => $e->getMessage(),
                );
            }
            catch (Exception $e) {
                $response = array(
                    'error'     => true,
                    'message'   => $this->__('Cannot add call history.')
                );
            }
            if (is_array($response)) {
                $response = Mage::helper('core')->jsonEncode($response);
                $this->getResponse()->setBody($response);
            }
        }
    }

    /**
     * Initialize call model instance
     *
     * @return Astrio_Callme_Model_Call || false
     */
    protected function _initCall()
    {
        $id = $this->getRequest()->getParam('call_id');
        $call = Mage::getModel('astrio_callme/call')->load($id);

        if (!$call->getId()) {
            $this->_getSession()->addError($this->__('This call no longer exists.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        Mage::register('astrio_callme_call', $call);
        return $call;
    }


     /**
     * Check allow or not access to ths page
     *
     * @return bool - is allowed to access this menu
     */
    protected function _isAllowed()
    {
        /**
         * @var $session Mage_Admin_Model_Session
         */
        $session = Mage::getSingleton('admin/session');
        return $session->isAllowed('astrio/callme/calls');
    }

    /**
     * @return Astrio_Callme_Helper_Data
     */
    protected function _getCallmeHelper()
    {
        return Mage::helper('astrio_callme');
    }

}