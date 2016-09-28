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
 * @package    Astrio_Menu
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Astrio
 *
 * @category Astrio
 * @package Astrio_Menu
 * @author Eldaniz Gasymov <e.gasymov@astrio.net>
 */
class Astrio_Menu_Block_Adminhtml_Menu_Item_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    protected $_blockGroup = 'astrio_menu';

    protected $_controller = 'adminhtml_menu_item';

    protected $_mode = 'edit';

    /**
     * Constructor
     *
     * @throws Exception
     */
    public function __construct()
    {
        $this->_headerText = Mage::helper('astrio_menu')->__($this->getRequest()->getParam('id') ? 'Edit Menu Item' : 'New Menu Item');
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('astrio_menu')->__('Save And Continue Edit'),
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