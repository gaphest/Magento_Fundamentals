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
 * @package    Astrio_Documentation
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Astrio
 *
 * @category   Astrio
 * @package    Astrio_Documentation
 * @author     Eldaniz Gasymov <e.gasymov@astrio.net>
 */ 
class Astrio_Documentation_Block_Adminhtml_Documentation_Category_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Add fieldset
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /**
         * @var $category Astrio_Documentation_Model_Category
         */
        $category = Mage::registry('astrio_documentation_category');

        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method'    => 'post',
        ));

        $fieldSet = $form->addFieldset('base_fieldset', array(
            'legend'    => $this->__('Category data'),
        ));

        $fieldSet->addField('name', 'text', array(
            'name'      => 'name',
            'label'     => $this->__('Name'),
            'title'     => $this->__('Name'),
            'required'  => true,
            'class'     => 'required-entry validate-length validate-maximum-length-128'
        ));

        $fieldSet->addField('position', 'text', array(
            'name'      => 'position',
            'label'     => $this->__('Position'),
            'title'     => $this->__('Position'),
            'required'  => false,
            'class'     => 'validate-number validate-zero-or-greater validate-digits'
        ));

        Mage::dispatchEvent('adminhtml_astrio_documentation_category_edit_prepare_form', array(
            'form' => $form,
        ));

        $form->setUseContainer(true);
        $form->addValues($category->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
