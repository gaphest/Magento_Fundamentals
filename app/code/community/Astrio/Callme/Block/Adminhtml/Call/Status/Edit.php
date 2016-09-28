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
 *  Call Status form container
 *
 * @category   Astrio
 * @package    Astrio_Callme
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_Callme_Block_Adminhtml_Call_Status_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    protected $_blockGroup = 'astrio_callme';

    protected $_controller = 'adminhtml_call_status';

    protected $_mode = 'edit';

    /**
     * Constructor
     *
     * @throws Exception
     */
    public function __construct()
    {
        $this->_headerText = Mage::helper('astrio_callme')->__($this->getRequest()->getParam('id') ? 'Edit Status' : 'New Status');
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('astrio_callme')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_removeButton('reset');
        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";

        parent::__construct();
    }
}
