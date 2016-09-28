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
class Astrio_SpecialProducts_Block_Adminhtml_SpecialProducts_Set_Edit_Tab_General
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    
    /**
     * @var Astrio_SpecialProducts_Helper_Data
     */
    protected $_helper = null;

    /**
     * Get Astrio_SpecialProducts helper
     *
     * @return Astrio_SpecialProducts_Helper_Data
     */
    protected function _getHelper()
    {
        if (null === $this->_helper) {
            $this->_helper = Mage::helper('astrio_specialproducts');
        }

        return $this->_helper;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        /**
         * @var $set Astrio_SpecialProducts_Model_Set
         */
        $set = Mage::registry('astrio_specialproducts_set');
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('general_');

        $fieldSet = $form->addFieldset('general_form', array(
            'legend'	=> $this->_getHelper()->__('General'),
            'class'		=> 'fieldset-wide',
        ));

        $fieldSet->addField('name', 'text', array(
            'name'      => 'set[name]',
            'label'     => $this->_getHelper()->__('Name'),
            'required'  => true,
            'class'     => 'required-entry validate-length maximum-length-128',
        ));

        $fieldSet->addField('identifier', 'text', array(
            'name'      => 'set[identifier]',
            'label'     => $this->_getHelper()->__('Identifier'),
            'required'  => true,
            'class'     => 'required-entry validate-length maximum-length-128 validate-code',
        ));

        $fieldSet->addField('is_active', 'select', array(
            'name'      => 'set[is_active]',
            'label'     => $this->_getHelper()->__('Is Active'),
            'required'  => true,
            'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        if (Mage::app()->isSingleStoreMode()) {
            $fieldSet->addField('store_ids', 'hidden', array(
                'name'      => 'set[store_ids][]',
                'value'     => Mage::app()->getStore(true)->getId(),
            ));
            $set->setStoreIds(Mage::app()->getStore(true)->getId());
        } else {
            $element = $fieldSet->addField('store_ids', 'multiselect', array(
                'name'      => 'set[store_ids][]',
                'label'     => $this->_getHelper()->__('Store'),
                'required'  => true,
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(),
            ));
            $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
            $element->setRenderer($renderer);
        }

        Mage::dispatchEvent('adminhtml_specialproducts_set_edit_tab_general', array('tab' => $this, 'form' => $form));

        $form->setUseContainer(false);        
        $form->addValues($set->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare content for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->_getHelper()->__('General');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->_getHelper()->__('General');
    }

    /**
     * Returns status flag about this tab can be showed or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }
}
