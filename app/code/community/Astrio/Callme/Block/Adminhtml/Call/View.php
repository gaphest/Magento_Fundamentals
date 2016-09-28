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
 *  Call Form
 *
 * @category   Astrio
 * @package    Astrio_Callme
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_Callme_Block_Adminhtml_Call_View extends Mage_Adminhtml_Block_Widget_Form_Container
{

    protected $_blockGroup = 'astrio_callme';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_objectId    = 'call_id';
        $this->_controller  = 'adminhtml_call';
        $this->_mode        = 'view';
        parent::__construct();
        $this->setId('astrio_callme_call_view');
        $this->_removeButton('delete');
        $this->_removeButton('reset');
        $this->_removeButton('save');
    }

    /**
     * Retrieve call model object
     *
     * @return Astrio_Callme_Model_Call
     */
    public function getCall()
    {
        return Mage::registry('astrio_callme_call');
    }

    /**
     * Retrieve Call Identifier
     *
     * @return int
     */
    public function getCallId()
    {
        return $this->getCall()->getId();
    }

    /**
     * Gets header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        return Mage::helper('sales')->__('Call #%s | %s', $this->getCall()->getId(), $this->formatDate($this->getCall()->getCreatedAtDate(), 'medium', true));
    }


}
