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
class Astrio_SpecialProducts_Block_Adminhtml_SpecialProducts_Set_Edit_Tab_Conditions
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
        $form->setHtmlIdPrefix('conditions_');

        $fieldSet = $form->addFieldset('conditions_form', array(
            'legend'	=> $this->_getHelper()->__('Conditions'),
            'class'		=> 'fieldset-wide',
        ));

        $fieldSet->addField('is_auto', 'select', array(
            'name'      => 'set[is_auto]',
            'label'     => $this->_getHelper()->__('Select Products Automatically'),
            'required'  => true,
            'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
            'note'      => $this->_getHelper()->__('Please save mode to manually select products'),
        ));

        $fieldSet->addField('auto_type', 'select', array(
            'name'      => 'set[auto_type]',
            'label'     => $this->_getHelper()->__('Auto Type'),
            'required'  => true,
            'class'		=> 'validate-select',
            'values'    => Astrio_SpecialProducts_Model_Set::getAutoTypesToOptionArray(),
            'note'      => $this->_getHelper()->__('Please remember that you will need to regenerate automatic sets after save'),
        ));

        $fieldSet->addField('catalog_rule_id', 'select', array(
            'name'      => 'set[catalog_rule_id]',
            'label'     => $this->_getHelper()->__('Catalog Rule'),
            'required'  => true,
            'class'		=> 'validate-select',
            'values'    => Astrio_Core_Model_System_Config_Source_CatalogRule::toOptionArray(),
        ));

        $fieldSet->addField('filter_by_category_id', 'select', array(
            'name'      => 'set[filter_by_category_id]',
            'label'     => $this->_getHelper()->__('Filter By Category'),
            'required'  => false,
            'class'		=> '',
            'values'    => Astrio_Core_Model_System_Config_Source_Category::toOptionArray(),
        ));

        $fieldSet->addField('filter_greater_than', 'text', array(
            'name'      => 'set[filter_greater_than]',
            'label'     => $this->_getHelper()->__('Greater than or equal'),
            'required'  => false,
            'class'		=> 'validate-digits',
            'note'      => $this->_getHelper()->__('If auto type is `On Sale` - percent.<br/> In other case - count'),
        ));

        $fieldSet->addField('filter_in_last_days', 'text', array(
            'name'      => 'set[filter_in_last_days]',
            'label'     => $this->_getHelper()->__('In Last Days'),
            'required'  => false,
            'class'		=> 'validate-digits',
        ));

        $fieldSet->addField('products_limit', 'text', array(
            'name'      => 'set[products_limit]',
            'label'     => $this->_getHelper()->__('Products Limit'),
            'required'  => false,
            'class'		=> 'validate-digits',
        ));

        /**
         * @var $dependence Mage_Adminhtml_Block_Widget_Form_Element_Dependence
         */
        $dependence = $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence');
        $dependence
            ->addFieldMap($form->getHtmlIdPrefix() . 'is_auto', 'is_auto')
            ->addFieldMap($form->getHtmlIdPrefix() . 'auto_type', 'auto_type')
            ->addFieldMap($form->getHtmlIdPrefix() . 'catalog_rule_id', 'catalog_rule_id')
            ->addFieldMap($form->getHtmlIdPrefix() . 'filter_by_category_id', 'filter_by_category_id')
            ->addFieldMap($form->getHtmlIdPrefix() . 'filter_greater_than', 'filter_greater_than')
            ->addFieldMap($form->getHtmlIdPrefix() . 'filter_in_last_days', 'filter_in_last_days')
            ->addFieldMap($form->getHtmlIdPrefix() . 'products_limit', 'products_limit')
            ->addFieldDependence('auto_type', 'is_auto', 1)
            ->addFieldDependence('catalog_rule_id', 'is_auto', 1)
            ->addFieldDependence('catalog_rule_id', 'auto_type', Astrio_SpecialProducts_Model_Set::AUTO_TYPE_CATALOG_RULE)
            ->addFieldDependence('filter_by_category_id', 'is_auto', 1)
            ->addFieldDependence('products_limit', 'is_auto', 1)
            ->addFieldDependence('filter_greater_than', 'auto_type', array(
                (string) Astrio_SpecialProducts_Model_Set::AUTO_TYPE_ON_SALE,
                (string) Astrio_SpecialProducts_Model_Set::AUTO_TYPE_BESTSELLER,
                (string) Astrio_SpecialProducts_Model_Set::AUTO_TYPE_MOST_VIEWED,
                (string) Astrio_SpecialProducts_Model_Set::AUTO_TYPE_MOST_REVIEWED,
            ))
            ->addFieldDependence('filter_in_last_days', 'auto_type', array(
                (string) Astrio_SpecialProducts_Model_Set::AUTO_TYPE_RECENTLY_ADDED,
                (string) Astrio_SpecialProducts_Model_Set::AUTO_TYPE_BESTSELLER,
                (string) Astrio_SpecialProducts_Model_Set::AUTO_TYPE_MOST_VIEWED,
                (string) Astrio_SpecialProducts_Model_Set::AUTO_TYPE_MOST_REVIEWED,
            ))
        ;

        Mage::dispatchEvent('adminhtml_specialproducts_set_edit_tab_conditions', array('tab' => $this, 'form' => $form, 'dependence' => $dependence));

        $this->setChild('form_after', $dependence);

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
        return $this->_getHelper()->__('Conditions');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->_getHelper()->__('Conditions');
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
