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
class Astrio_Documentation_Block_Adminhtml_Documentation_Document_Edit_Tab_Base extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare form before rendering HTML
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        /**
         * @var $model Astrio_Documentation_Model_Document
         */
        $model = Mage::registry('astrio_documentation_document');
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('base_');

        $fieldSet = $form->addFieldset('menu_item_form', array(
            'legend'	=> $this->__('Base'),
            'class'		=> 'fieldset-wide',
        ));

        $fieldSet->addField('name', 'text', array(
            'name'      => 'document[name]',
            'label'     => $this->__('Name'),
            'required'  => true,
        ));

        $fieldSet->addField('description', 'editor', array(
            'name'      => 'document[description]',
            'label'     => $this->__('Description'),
            'required'  => false,
            'class'     => '',
            'config'    => Mage::getSingleton('cms/wysiwyg_config')->getConfig(array(
                'add_variables' => false,
                'add_widgets' => false,
                'add_images' => false
            )),
        ));

        $fieldSet->addField('category_id', 'select', array(
            'name'      => 'document[category_id]',
            'label'     => $this->__('Category'),
            'required'  => true,
            'class'     => 'validate-select',
            'values'    => Astrio_Documentation_Model_Category::getCategoryOptionArray(),
        ));

        $fieldSet->addField('is_active', 'select', array(
            'name'      => 'document[is_active]',
            'label'     => $this->__('Active'),
            'required'  => true,
            'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $fieldSet->addField('position', 'text', array(
            'name'      => 'document[position]',
            'label'     => $this->__('Position'),
            'title'     => $this->__('Position'),
            'required'  => false,
            'class'     => 'validate-number validate-zero-or-greater validate-digits'
        ));

        $fieldSet->addField('type', 'select', array(
            'name'      => 'document[type]',
            'label'     => $this->__('Type'),
            'required'  => true,
            'values'    => Mage::helper('astrio_documentation')->getDocumentTypeOptions(),
        ));

        $fieldSet->addType('document_file', 'Astrio_Documentation_Block_Adminhtml_Documentation_Document_Form_Element_File');

        $fieldSet->addField('filename', 'document_file', array(
            'name'      => 'filename',
            'label'     => $this->__('File'),
            'required'  => true,
            'preview_url' => $model->getId() ? Mage::helper('astrio_documentation')->getAdminDownloadUrl($model) : false
        ));

        $fieldSet->addField('url', 'text', array(
            'name'      => 'document[url]',
            'label'     => $this->__('Url'),
            'required'  => true,
        ));

        /**
         * @var $dependence Mage_Adminhtml_Block_Widget_Form_Element_Dependence
         */
        $dependence = $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence');
        $dependence
            ->addFieldMap($form->getHtmlIdPrefix() . 'type', 'type')
            ->addFieldMap($form->getHtmlIdPrefix() . 'filename', 'filename')
            ->addFieldMap($form->getHtmlIdPrefix() . 'url', 'url')
            ->addFieldDependence('filename', 'type', Astrio_Documentation_Model_Document::TYPE_FILE)
            ->addFieldDependence('url', 'type', Astrio_Documentation_Model_Document::TYPE_URL)
        ;

        Mage::dispatchEvent('adminhtml_astrio_documentation_document_edit_base_tab_prepare_form', array(
            'form' => $form,
            'dependence' => $dependence
        ));
        $this->setChild('form_after', $dependence);

        $form->setUseContainer(false);
        $form->addValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
