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
 *  Call Model
 *
 * @category   Astrio
 * @package    Astrio_Callme
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_Callme_Model_Call extends Mage_Core_Model_Abstract
{

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'astrio_callme_call';

    /**
     * Event object name
     *
     * @var string
     */
    protected $_eventObject = 'call';

    protected $_statusHistory = null;

    /**
     * Internal constructor not depended on params. Can be used for object initialization
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('astrio_callme/call');
    }

    /**
     * Gets status
     *
     * @return mixed
     */
    public function getStatus()
    {
        return $this->_getData('status');
    }

    /**
     * Gets comment
     *
     * @return mixed
     */
    public function getComment()
    {
        return $this->_getData('comment');
    }

    /**
     * Gets phone
     *
     * @return mixed
     */
    public function getPhone()
    {
        return $this->_getData('phone');
    }

    /**
     * Gets if is not notified
     *
     * @return mixed
     */
    public function getIsNotified()
    {
        return $this->_getData('is_notified');
    }

    /**
     * Gets created at
     *
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->_getData('created_at');
    }

    /**
     * Gets remote ip
     *
     * @return mixed
     */
    public function getRemoteIp()
    {
        return $this->_getData('remote_ip');
    }

    /**
     * Gets before save
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        if ($this->isObjectNew()) {
            if (!$this->hasData('status')) {
                $this->setStatus($this->_getCallmeHelper()->getCallStatusAfterPost());
            }
            if (!$this->hasData('store_id')) {
                $this->setStoreId(Mage::app()->getStore()->getId());
            }
            if (!$this->hasData('created_at')) {
                $this->setCreatedAt(Varien_Date::now());
            }

            if (!$this->hasData('remote_ip')) {
                $this->setRemoteIp(Mage::helper('core/http')->getRemoteAddr(false));
            }
        }
        return parent::_beforeSave();
    }

    /**
     * Validate
     *
     * @return array|bool
     * @throws Exception
     * @throws Zend_Validate_Exception
     */
    public function validate()
    {
        $helper = $this->_getCallmeHelper();
        $errors = array();
        if (!Zend_Validate::is(trim($this->getPhone()) , 'NotEmpty')) {
            $errors[] = $helper->__('The phone number cannot be empty.');
        }
        if (empty($errors)) {
            return true;
        }
        return $errors;
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
     * Gets status label
     *
     * @return string
     */
    public function getStatusLabel()
    {
        return $this->_getCallmeHelper()->getCallStatusLabel($this);
    }

    /**
     * Get status history collection
     *
     * @param bool $reload reload?
     * @return mixed
     */
    public function getStatusHistoryCollection($reload = false)
    {
        if (is_null($this->_statusHistory) || $reload) {
            /**
             * @var $collection Astrio_Callme_Model_Resource_Call_Status_History_Collection
             */
            $collection = Mage::getResourceModel('astrio_callme/call_status_history_collection');
            $collection->setCallFilter($this)
                ->joinUserName()
                ->setOrder('created_at', Varien_Data_Collection::SORT_ORDER_DESC)
                ->setOrder('history_id', Varien_Data_Collection::SORT_ORDER_DESC);

            if ($this->getId()) {
                foreach ($collection as $status) {
                    $status->setCall($this);
                }
            }

            $this->_statusHistory = $collection;
        }

        return $this->_statusHistory;
    }

    /**
     * Gets astrio_callme helper
     *
     * @return Astrio_Callme_Helper_Data
     */
    protected function _getCallmeHelper()
    {
        return Mage::helper('astrio_callme');
    }

    /**
     * Add a comment to call
     *
     * @param string $comment comment
     * @param string $status status
     * @param bool $notify notify?
     * @return Astrio_Callme_Model_Call_Status_History
     */
    public function addStatusHistoryComment($comment, $status, $notify = false)
    {
        $this->_statusHistory = null;
        if (!$this->getId()) {
            $this->save();
        }

        $oldStatus = $this->getStatus();
        $this->setStatus($status);
        $this->save();

        /**
         * @var Astrio_Callme_Model_Call_Status_History $history
         */
        $history = Mage::getModel('astrio_callme/call_status_history')
            ->setCall($this)
            ->setStatus($status)
            ->setComment($comment)
            ->setCallId($this->getId())
            ->save();

        if ($notify) {
            if ($history->sendNewCommentEmail($this, $history->getEscapeComment(), $status, $oldStatus)) {
                $history->setIsAdminNotified(1)->save();
            }
        }

        return $history;
    }

    /**
     * Send email to admin
     *
     * @return $this
     */
    public function sendEmailToAdminAfterPost()
    {
        $email = Mage::getStoreConfig(Astrio_Callme_Helper_Data::XML_PATH_ASTRIO_CALLME_EMAIL_ADMIN_EMAIL);
        $email = trim($email);
        if (!$email || !Zend_Validate::is($email, 'EmailAddress')) {
            return $this;
        }

        try{
            /* @var $mailTemplate Mage_Core_Model_Email_Template */
            $mailTemplate = Mage::getModel('core/email_template');
            $mailTemplate->setDesignConfig(array('area' => 'frontend'));
            $mailTemplate->sendTransactional(
                Mage::getStoreConfig(Astrio_Callme_Helper_Data::XML_PATH_ASTRIO_CALLME_EMAIL_ADMIN_NOTIFICATION_TEMPLATE),
                Mage::getStoreConfig(Astrio_Callme_Helper_Data::XML_PATH_ASTRIO_CALLME_EMAIL_SENDER_EMAIL_IDENTITY),
                $email,
                null,
                array(
                    'call' => $this,
                )
            );
            $notified = $mailTemplate->getSentSuccess();
        }
        catch(Exception $e){
            $notified = false;
            Mage::logException($e);
        }

        $this->setIsNotified($notified ? 1 : 0)->save();
        return $this;
    }
}