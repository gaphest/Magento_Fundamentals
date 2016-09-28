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
class Astrio_Menu_Block_Adminhtml_Menu_Item_Edit_Tab_Base extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * @var Astrio_Menu_Helper_Data
     */
    protected $_helper = null;

    /**
     * @return Astrio_Menu_Helper_Data
     */
    protected function _getHelper()
    {
        if (null === $this->_helper) {
            $this->_helper = Mage::helper('astrio_menu');
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
         * @var $model Astrio_Menu_Model_Menu_Item
         */
        $model = Mage::registry('astrio_menu_item');
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('base_');

        $fieldset = $form->addFieldset('menu_item_form', array(
            'legend'	=> Mage::helper('astrio_menu')->__('Menu'),
            'class'		=> 'fieldset-wide',
        ));

        $fieldset->addField('name', 'text', array(
            'name'      => 'name',
            'label'     => Mage::helper('astrio_menu')->__('Name'),
            'required'  => true,
        ));

        $fieldset->addField('position', 'text', array(
            'name'      => 'position',
            'label'     => Mage::helper('astrio_menu')->__('Position'),
            'required'  => false,
            'class'     => 'validate-number',
        ));

        $fieldset->addField('item_type', 'select', array(
            'name'      => 'item_type',
            'label'     => Mage::helper('astrio_menu')->__('Item Type'),
            'required'  => true,
            'class'     => 'validate-select',
            'values'    => Astrio_Menu_Model_Menu_Item_Source_ItemType::toOptionArray(),
        ));

        /**
         * @var $sourceCategory Astrio_Core_Model_System_Config_Source_Category
         */
        $sourceCategory = Mage::getModel('astrio_core/system_config_source_category');

        $fieldset->addField('category_id', 'select', array(
            'name'      => 'category_id',
            'label'     => Mage::helper('astrio_menu')->__('Category'),
            'required'  => true,
            'class'     => 'validate-select',
            'values'    => $sourceCategory->toOptionArray(),
        ));

        /**
         * @var $sourceCmsPage Astrio_Core_Model_System_Config_Source_CmsPage
         */
        $sourceCmsPage = Mage::getModel('astrio_core/system_config_source_cmsPage');

        $fieldset->addField('cms_page_id', 'select', array(
            'name'      => 'cms_page_id',
            'label'     => Mage::helper('astrio_menu')->__('CMS Page'),
            'required'  => true,
            'class'     => 'validate-select',
            'values'    => $sourceCmsPage->toOptionArray(),
        ));

        $fieldset->addField('custom_link', 'text', array(
            'name'      => 'custom_link',
            'label'     => Mage::helper('astrio_menu')->__('Custom link'),
            'required'  => true,
        ));

        $fieldset->addField('is_secure_url', 'select', array(
            'name'      => 'is_secure_url',
            'label'     => Mage::helper('astrio_menu')->__('HTTPS'),
            'required'  => true,
            'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $fieldset->addField('class_link', 'text', array(
            'name'      => 'class_link',
            'label'     => Mage::helper('astrio_menu')->__('Class link'),
            'required'  => false,
        ));

        $fieldset->addField('extra', 'textarea', array(
            'name'      => 'extra',
            'label'     => Mage::helper('astrio_menu')->__('Extra'),
            'required'  => false,
        ));

        $fieldset->addField('is_active', 'select', array(
            'name'      => 'is_active',
            'label'     => Mage::helper('astrio_menu')->__('Is Active'),
            'required'  => true,
            'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $element = $fieldset->addField('stores', 'multiselect', array(
                'name'      => 'stores[]',
                'label'     => Mage::helper('astrio_menu')->__('Store'),
                'required'  => true,
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(),
            ));
            $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
            $element->setRenderer($renderer);
        } else {
            $fieldset->addField('stores', 'hidden', array(
                'name'      => 'stores[]',
                'value'     => Mage::app()->getStore(true)->getId(),
            ));
            $model->setStores(Mage::app()->getStore(true)->getId());
        }

        /**
         * @var $dependence Mage_Adminhtml_Block_Widget_Form_Element_Dependence
         */
        $dependence = $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence');
        $dependence
            ->addFieldMap($form->getHtmlIdPrefix() . 'item_type', 'item_type')
            ->addFieldMap($form->getHtmlIdPrefix() . 'custom_link', 'custom_link')
            ->addFieldMap($form->getHtmlIdPrefix() . 'category_id', 'category_id')
            ->addFieldMap($form->getHtmlIdPrefix() . 'cms_page_id', 'cms_page_id')
            ->addFieldMap($form->getHtmlIdPrefix() . 'is_secure_url', 'is_secure_url')
            ->addFieldDependence('is_secure_url', 'item_type', Astrio_Menu_Model_Menu_Item_Source_ItemType::CUSTOM)
            ->addFieldDependence('custom_link', 'item_type', Astrio_Menu_Model_Menu_Item_Source_ItemType::CUSTOM)
            ->addFieldDependence('category_id', 'item_type', Astrio_Menu_Model_Menu_Item_Source_ItemType::CATEGORY)
            ->addFieldDependence('cms_page_id', 'item_type', Astrio_Menu_Model_Menu_Item_Source_ItemType::CMS_PAGE)
        ;

        Mage::dispatchEvent('adminhtml_astrio_menu_item_edit_base_tab_prepare_form', array(
            'form'          => $form,
            'dependence'    => $dependence,
        ));

        $this->setChild('form_after', $dependence);

        $form->setUseContainer(false);
        $form->addValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}