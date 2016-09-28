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
 *  Tabs For Call View
 *
 * @category   Astrio
 * @package    Astrio_Callme
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_Callme_Block_Adminhtml_Call_View_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Retrieve available Call
     *
     * @return Astrio_Callme_Model_Call
     */
    public function getCall()
    {
        if ($this->hasCall()) {
            return $this->getData('call');
        }

        if (Mage::registry('astrio_callme_call')) {
            return Mage::registry('astrio_callme_call');
        }

        Mage::throwException(Mage::helper('astrio_callme')->__('Cannot get the Call instance.'));
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('astrio_callme_call_view_tabs');
        $this->setDestElementId('astrio_callme_call_view');
        $this->setTitle(Mage::helper('astrio_callme')->__('Call View'));
    }

}
