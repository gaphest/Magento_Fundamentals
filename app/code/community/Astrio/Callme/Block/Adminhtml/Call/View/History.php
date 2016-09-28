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
 *  Call History Block
 *
 * @category   Astrio
 * @package    Astrio_Callme
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_Callme_Block_Adminhtml_Call_View_History extends Mage_Adminhtml_Block_Template
{

    /**
     * @var Astrio_Callme_Helper_Data
     */
    protected $_helper = null;

    /**
     * Prepare layout
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        $onclick = "submitAndReloadArea($('call_history_block').parentNode, '".$this->getSubmitUrl()."')";
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'   => Mage::helper('sales')->__('Submit Comment'),
                'class'   => 'save',
                'onclick' => $onclick
            ));
        $this->setChild('submit_button', $button);
        return parent::_prepareLayout();
    }

    /**
     * Gets statuses
     *
     * @return null
     */
    public function getStatuses()
    {
        return $this->_getHelper()->getCallStatuses();
    }

    /**
     * Retrieve call model
     *
     * @return Astrio_Callme_Model_Call
     */
    public function getCall()
    {
        return Mage::registry('astrio_callme_call');
    }

    /**
     * Gets submit url
     *
     * @return string
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('*/*/addComment', array('call_id' => $this->getCall()->getId()));
    }

    /**
     * Gets helper
     *
     * @return Astrio_Callme_Helper_Data
     */
    protected function _getHelper()
    {
        if (null === $this->_helper) {
            $this->_helper = Mage::helper('astrio_callme');
        }

        return $this->_helper;
    }
}
