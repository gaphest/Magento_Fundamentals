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
 *  Helper for Callme Module
 *
 * @category   Astrio
 * @package    Astrio_Callme
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_Callme_Helper_Data extends Mage_Core_Helper_Abstract
{
    // Config path for admin notification email template
    const XML_PATH_ASTRIO_CALLME_EMAIL_ADMIN_NOTIFICATION_TEMPLATE = 'astrio_callme/email/admin_notification_template';
    // Config path for admin comment email template
    const XML_PATH_ASTRIO_CALLME_EMAIL_ADMIN_COMMENT_TEMPLATE      = 'astrio_callme/email/admin_comment_template';
    // Config path for email sender identity
    const XML_PATH_ASTRIO_CALLME_EMAIL_SENDER_EMAIL_IDENTITY       = 'astrio_callme/email/sender_email_identity';
    // Config path for admin email
    const XML_PATH_ASTRIO_CALLME_EMAIL_ADMIN_EMAIL                 = 'astrio_callme/email/admin_email';

    // Config path for general status after post
    const XML_PATH_ASTRIO_CALLME_GENERAL_STATUS_AFTER_POST         = 'astrio_callme/general/status_after_post';

    protected $_callStatuses = null;

    /**
     * Gets call status after post
     *
     * @return mixed
     */
    public function getCallStatusAfterPost()
    {
        return Mage::getStoreConfig(Astrio_Callme_Helper_Data::XML_PATH_ASTRIO_CALLME_GENERAL_STATUS_AFTER_POST);
    }

    /**
     * Gets call status label
     *
     * @param Astrio_Callme_Model_Call|string $status call instance or status string
     * @return string
     */
    public function getCallStatusLabel($status)
    {
        if ($status instanceof Astrio_Callme_Model_Call) {
            $status = $status->getStatus();
        }

        $statuses = $this->getCallStatuses();
        return isset($statuses[$status]) ? $statuses[$status] : '';
    }

    /**
     * Get all Call Statuses
     *
     * @return null
     */
    public function getCallStatuses()
    {
        if ($this->_callStatuses === null) {
            $this->_callStatuses = Mage::getResourceModel('astrio_callme/call_status')->getAllStatuses();
        }

        return $this->_callStatuses;
    }

    /**
     * Gets admin's user name
     *
     * @return bool
     */
    public function getAdminUserName()
    {
        if (Mage::app()->getStore()->isAdmin()) {
            return Mage::getSingleton('admin/session')->getUser()->getUsername();
        }

        return false;
    }

    /**
     * Gets call view's url
     *
     * @param Astrio_Callme_Model_Call|string $call call instance or id string
     * @return string
     */
    public function getCallViewUrl($call)
    {
        if ($call instanceof Astrio_Callme_Model_Call) {
            $call = $call->getId();
        }

        return Mage::getUrl('adminhtml/call/view', array('call_id' => intval($call)));
    }
}