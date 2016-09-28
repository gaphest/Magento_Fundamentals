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
class Astrio_SpecialProducts_Block_Adminhtml_SpecialProducts_Set_Edit_Tab_Page
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
     * Get special products set
     *
     * @return Astrio_SpecialProducts_Model_Set
     */
    protected function _getSet()
    {
        return Mage::registry('astrio_specialproducts_set');
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
        $form->setHtmlIdPrefix('page_');

        $fieldSet = $form->addFieldset('page_form', array(
            'legend'	=> $this->_getHelper()->__('Page'),
            'class'		=> 'fieldset-wide',
        ));

        $fieldSet->addField('use_separate_page', 'select', array(
            'name'      => 'set[use_separate_page]',
            'label'     => $this->_getHelper()->__('Use Separate Page'),
            'required'  => true,
            'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $fieldSet->addField('title', 'text', array(
            'name'         => 'set[page_data][title]',
            'label'        => $this->_getHelper()->__('Title'),
            'required'     => true,
            'class'        => 'required-entry validate-length maximum-length-255',
        ));

        $fieldSet->addField('url_key', 'text', array(
            'name'         => 'set[page_data][url_key]',
            'label'        => $this->_getHelper()->__('URL Key'),
            'required'     => true,
            'class'        => 'required-entry validate-length maximum-length-128 validate-identifier',
        ));

        $fieldSet->addField('description', 'editor', array(
            'name'         => 'set[page_data][description]',
            'label'        => $this->_getHelper()->__('Description'),
            'required'     => false,
            'config'       => Mage::getSingleton('cms/wysiwyg_config')->getConfig(),
        ));

        $fieldSet->addField('meta_title', 'text', array(
            'name'         => 'set[page_data][meta_title]',
            'label'        => $this->_getHelper()->__('Meta Title'),
            'required'     => false,
            'class'        => 'validate-length maximum-length-255',
        ));

        $fieldSet->addField('meta_keywords', 'textarea', array(
            'name'         => 'set[page_data][meta_keywords]',
            'label'        => $this->_getHelper()->__('Meta Keywords'),
            'required'     => false,
            'class'        => 'validate-length maximum-length-255',
        ));

        $fieldSet->addField('meta_description', 'textarea', array(
            'name'         => 'set[page_data][meta_description]',
            'label'        => $this->_getHelper()->__('Meta Description'),
            'required'     => false,
            'class'        => '',
        ));

        /**
         * @var $dependence Mage_Adminhtml_Block_Widget_Form_Element_Dependence
         */
        $dependence = $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence');
        $dependence
            ->addFieldMap($form->getHtmlIdPrefix() . 'use_separate_page', 'use_separate_page')
            ->addFieldMap($form->getHtmlIdPrefix() . 'title', 'title')
            ->addFieldMap($form->getHtmlIdPrefix() . 'url_key', 'url_key')
            ->addFieldMap($form->getHtmlIdPrefix() . 'description', 'description')
            ->addFieldMap($form->getHtmlIdPrefix() . 'meta_title', 'meta_title')
            ->addFieldMap($form->getHtmlIdPrefix() . 'meta_keywords', 'meta_keywords')
            ->addFieldMap($form->getHtmlIdPrefix() . 'meta_description', 'meta_description')
            ->addFieldDependence('title', 'use_separate_page', 1)
            ->addFieldDependence('url_key', 'use_separate_page', 1)
            ->addFieldDependence('description', 'use_separate_page', 1)
            ->addFieldDependence('meta_title', 'use_separate_page', 1)
            ->addFieldDependence('meta_keywords', 'use_separate_page', 1)
            ->addFieldDependence('meta_description', 'use_separate_page', 1);

        Mage::dispatchEvent('adminhtml_specialproducts_set_edit_tab_page', array('tab' => $this, 'form' => $form, 'dependence' => $dependence));

        $this->setChild('form_after', $dependence);

        $data = $set->getData();
        if ($set->getUseSeparatePage()) {
            $data += $set->getPage()->toArray();
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
        return $this->_getHelper()->__('Page');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->_getHelper()->__('Page');
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
