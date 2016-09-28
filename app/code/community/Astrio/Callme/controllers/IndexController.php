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
 *  Call Me controller
 *
 * @category   Astrio
 * @package    Astrio_Callme
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_Callme_IndexController extends Mage_Core_Controller_Front_Action
{

    /**
     * View callme form
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Add new call
     *
     * @return null
     */
    public function postAction()
    {
        if (!$this->getRequest()->isPost()) {
            $this->_redirect('*/*/index');
            return;
        }

        /**
         * @var  Astrio_Callme_Model_Call $call
         */
        $helper     = $this->_getHelper();
        $session    = $this->_getSession();
        $call       = Mage::getModel('astrio_callme/call');

        $call->setPhone($this->getRequest()->getPost('phone'));
        $call->setComment($this->getRequest()->getPost('comment'));

        $validationResult = $call->validate();
        if ($validationResult === true) {
            try{
                $call->save();
                $session->addSuccess($helper->__('Your request has been sent successfully'));
                $call->sendEmailToAdminAfterPost();
                Mage::dispatchEvent('astrio_callme_call_post_success', array(
                    'call'      => $call,
                    'request'   => $this->getRequest(),
                ));
            } catch(Exception $e) {
                Mage::logException($e);
                $session->addError($helper->__('Sorry, something went wrong'));
            }
        } else {
            foreach ($validationResult as $error) {
                $session->addError($error);
            }
        }

        $this->_redirect('*/*/index');
    }


    /**
     * Gets helper
     *
     * @return Astrio_Callme_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('astrio_callme');
    }

    /**
     * Gets session
     *
     * @return Mage_Core_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('core/session');
    }
}