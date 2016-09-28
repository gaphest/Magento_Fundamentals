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
 *  Call Status Edit form
 *
 * @category   Astrio
 * @package    Astrio_Callme
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_Callme_Block_Adminhtml_Call_Status_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * prepare form
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $status = Mage::registry('astrio_call_status');

        $form = new Varien_Data_Form(array(
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save', array('id' => $status->getId())),
                'method' => 'post',
            )
        );

        $fieldset = $form->addFieldset('status_form', array(
            'legend' => Mage::helper('astrio_callme')->__('Status Information')
        ));

        $fieldset->addField('name', 'text', array(
            'label' => Mage::helper('astrio_callme')->__('Name'),
            'name' => 'name',
        ));

        $fieldset->addField('code', 'text', array(
            'label' => Mage::helper('astrio_callme')->__('Code'),
            'name' => 'code',
        ));


        $form->setUseContainer(true);

        $form->setValues($status);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
