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
 * @package    Astrio_SpecialProducts
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Astrio
 *
 * @category   Astrio
 * @package    Astrio_SpecialProducts
 * @author     Eldaniz Gasymov <e.gasymov@astrio.net>
 */ 
class Astrio_SpecialProducts_Block_Adminhtml_SpecialProducts_Set_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    protected $_blockGroup  = 'astrio_specialproducts';

    protected $_controller  = 'adminhtml_specialProducts_set';
    
    protected $_mode        = 'edit';

    /**
     * Constructor
     *
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->_headerText = Mage::helper('astrio_specialproducts')->__($this->getRequest()->getParam('id') == 0 ? 'New Special Products Set' : 'Edit Special Products Set');

        $this->_removeButton('reset');

        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('astrio_specialproducts')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        /**
         * @var $set Astrio_SpecialProducts_Model_Set
         */
        $set = Mage::registry('astrio_specialproducts_set');

        if ($set->getId() && $set->getIsAuto()) {
            $this->_addButton('reindex', array(
                'label'     => Mage::helper('astrio_specialproducts')->__('Reindex'),
                'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/reindex', array('id' => $set->getId())) .'\')',
                'class'     => 'save',
            ), -100);
        }

        $this->_formScripts[] = "
            function saveAndContinueEdit() {
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    /**
     * Prepare layout
     */
    protected function _prepareLayout()
    {
        // added this code
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        }

        parent::_prepareLayout();
    }
}
