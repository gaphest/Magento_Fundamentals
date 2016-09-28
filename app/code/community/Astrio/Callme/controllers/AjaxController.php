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
class Astrio_Callme_AjaxController extends Mage_Core_Controller_Front_Action
{

    /**
     * Ajax add post action
     *
     * @return $this
     */
    public function callPostAction()
    {
        /**
         * @var  Astrio_Callme_Model_Call $call
         */
        $helper     = $this->_getHelper();
        $call       = Mage::getModel('astrio_callme/call');

        $call->setPhone($this->getRequest()->getPost('phone'));
        $call->setComment($this->getRequest()->getPost('comment'));

        $validationResult = $call->validate();
        if ($validationResult === true) {
            try {
                $call->save();
                $result = array('success' => true, 'message' => $helper->__('Your request has been sent successfully'));
                $call->sendEmailToAdminAfterPost();
                Mage::dispatchEvent('astrio_callme_call_post_success', array(
                    'call'      => $call,
                    'request'   => $this->getRequest(),
                ));
            } catch(Exception $e){
                Mage::logException($e);
                $result = array('success' => false, 'message' => $helper->__('Sorry, something went wrong'));
            }
        } else {
            $result = array('success' => false, 'message' => $validationResult[0]);
        }

        $this->sendJSON($result);

        return $this;
    }


    /**
     * Ajax form display
     */
    public function showFormAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * set content type application/json and set body json-encoded
     *
     * @param string $data data
     * @return $this
     */
    public function sendJSON($data)
    {
        /**
         * @var $coreHelper Mage_Core_Helper_Data
         */
        $coreHelper = Mage::helper('core');
        $this->getResponse()
            ->setHeader('Content-Type', 'application/json; charset=utf-8', true)
            ->setBody($coreHelper->jsonEncode($data));

        return $this;
    }

    /**
     * @return Astrio_Callme_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('astrio_callme');
    }

}