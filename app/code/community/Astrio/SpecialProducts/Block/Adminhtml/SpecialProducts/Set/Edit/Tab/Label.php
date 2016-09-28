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
class Astrio_SpecialProducts_Block_Adminhtml_SpecialProducts_Set_Edit_Tab_Label
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
        $form->setHtmlIdPrefix('label_');

        $fieldSet = $form->addFieldset('label_form', array(
            'legend'	=> $this->_getHelper()->__('Label'),
            'class'		=> 'fieldset-wide',
        ));

        $fieldSet->addField('apply_label', 'select', array(
            'name'      => 'set[apply_label]',
            'label'     => $this->_getHelper()->__('Apply Label'),
            'required'  => true,
            'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $fieldSet->addField('priority', 'text', array(
            'name'      => 'set[label_data][priority]',
            'label'     => $this->_getHelper()->__('Priority'),
            'required'  => false,
            'class'     => 'validate-digits',
        ));

        $fieldSet->addField('position', 'select', array(
            'name'      => 'set[label_data][position]',
            'label'     => $this->_getHelper()->__('Position'),
            'required'  => true,
            'class'     => 'validate-select',
            'values'    => Astrio_SpecialProducts_Model_Set_Label::getPositionToOptionArray(),
        ));

        $fieldSet->addField('output_type', 'select', array(
            'name'      => 'set[label_data][output_type]',
            'label'     => $this->_getHelper()->__('Output Type'),
            'required'  => true,
            'class'     => 'validate-select',
            'values'    => Astrio_SpecialProducts_Model_Set_Label::getOutputTypeToOptionArray(),
        ));

        $fieldSet->addType('label_image', 'Astrio_SpecialProducts_Block_Adminhtml_SpecialProducts_Set_Edit_Form_Element_Renderer_Label_Image');

        $fieldSet->addField('image', 'label_image', array(
            'label'     => $this->_getHelper()->__('Image'),
            'required'  => true,
            'name'      => 'set[label_data][image]',
            'class'     => 'required-entry',
        ));

        $fieldSet->addField('title', 'text', array(
            'label'     => $this->_getHelper()->__('Title'),
            'required'  => true,
            'name'      => 'set[label_data][title]',
            'class'     => 'required-entry',
            'note'      => $this->_getHelper()->__('You use placeholder '.Astrio_SpecialProducts_Helper_Label::PLACEHOLDER_PERCENT.' for display real discount percent. Works only for Auto Types: `Catalog Rule` and `On Sale`.'),
        ));

        /**
         * @var $dependence Mage_Adminhtml_Block_Widget_Form_Element_Dependence
         */
        $dependence = $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence');
        $dependence
            ->addFieldMap($form->getHtmlIdPrefix() . 'apply_label', 'apply_label')
            ->addFieldMap($form->getHtmlIdPrefix() . 'priority', 'priority')
            ->addFieldMap($form->getHtmlIdPrefix() . 'position', 'position')
            ->addFieldMap($form->getHtmlIdPrefix() . 'output_type', 'output_type')
            ->addFieldMap($form->getHtmlIdPrefix() . 'image', 'image')
            ->addFieldMap($form->getHtmlIdPrefix() . 'title', 'title')
            ->addFieldDependence('priority', 'apply_label', 1)
            ->addFieldDependence('position', 'apply_label', 1)
            ->addFieldDependence('output_type', 'apply_label', 1)
            ->addFieldDependence('image', 'apply_label', 1)
            ->addFieldDependence('title', 'apply_label', 1)
            ->addFieldDependence('image', 'output_type', Astrio_SpecialProducts_Model_Set_Label::OUTPUT_TYPE_IMAGE)
            ->addFieldDependence('title', 'output_type', Astrio_SpecialProducts_Model_Set_Label::OUTPUT_TYPE_TEXT);

        Mage::dispatchEvent('adminhtml_specialproducts_set_edit_tab_label', array('tab' => $this, 'form' => $form, 'dependence' => $dependence));

        $this->setChild('form_after', $dependence);

        $data = $set->getData();
        if ($set->getApplyLabel()) {
            $data += $set->getLabel()->toArray();
        }

        $form->setUseContainer(false);
        $form->addValues($data);
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
        return $this->_getHelper()->__('Label');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->_getHelper()->__('Label');
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
