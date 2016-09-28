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
 *  Call Status History Model
 *
 * @category   Astrio
 * @package    Astrio_Callme
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_Callme_Model_Call_Status_History extends Mage_Core_Model_Abstract
{

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'astrio_callme_call_status_history';

    /**
     * Event object name
     *
     * @var string
     */
    protected $_eventObject = 'history';

    /**
     * Internal constructor not depended on params. Can be used for object initialization
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('astrio_callme/call_status_history');
    }

    /**
     * Before save
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        if ($this->isObjectNew()) {
            if (!$this->hasData('user_id')) {
                if (Mage::app()->getStore()->isAdmin()) {
                    $this->setUserId(Mage::getSingleton('admin/session')->getUser()->getUserId());
                }
            }
            if (!$this->hasData('created_at')) {
                $this->setCreatedAt(Varien_Date::now());
            }
        }

        return parent::_beforeSave();
    }

    /**
     * Get status label
     *
     * @return string
     */
    public function getStatusLabel()
    {
        return $this->_getCallmeHelper()->getCallStatusLabel($this->getStatus());
    }

    /**
     * Get a comment in a secure format
     *
     * @return string
     */
    public function getEscapeComment()
    {
        $comment = $this->getComment();
        return nl2br(htmlspecialchars($comment));
    }

    /**
     * Send new comment email
     *
     * @param Astrio_Callme_Model_Call $call call
     * @param string $comment comment
     * @param string $status status
     * @param string $oldStatus old status
     * @return bool
     * @throws Exception
     * @throws Zend_Validate_Exception
     */
    public function sendNewCommentEmail($call, $comment, $status, $oldStatus)
    {
        $email = Mage::getStoreConfig(Astrio_Callme_Helper_Data::XML_PATH_ASTRIO_CALLME_EMAIL_ADMIN_EMAIL);
        $email = trim($email);
        if (!$email || !Zend_Validate::is($email, 'EmailAddress')) {
            return false;
        }

        if ($status != $oldStatus) {
            $status = $this->_getCallmeHelper()->getCallStatusLabel($status);
        } else {
            $status = false;
        }

        try {
            /**
             * @var $mailTemplate Mage_Core_Model_Email_Template
             */
            $mailTemplate = Mage::getModel('core/email_template');
            $mailTemplate->setDesignConfig(array('area' => 'frontend'));
            $mailTemplate->sendTransactional(
                Mage::getStoreConfig(Astrio_Callme_Helper_Data::XML_PATH_ASTRIO_CALLME_EMAIL_ADMIN_COMMENT_TEMPLATE),
                Mage::getStoreConfig(Astrio_Callme_Helper_Data::XML_PATH_ASTRIO_CALLME_EMAIL_SENDER_EMAIL_IDENTITY),
                $email,
                null,
                array(
                    'comment'   => $comment,
                    'status'    => $status,
                    'username'  => $this->_getCallmeHelper()->getAdminUserName(),
                    'call'      => $call,
                    'call_url'  => $this->_getCallmeHelper()->getCallViewUrl($call)
                )
            );
            return $mailTemplate->getSentSuccess();
        } catch(Exception $e) {
            Mage::logException($e);
        }

        return false;
    }

    /**
     * @return Astrio_Callme_Helper_Data
     */
    protected function _getCallmeHelper()
    {
        return Mage::helper('astrio_callme');
    }
}