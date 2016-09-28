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
class Astrio_Menu_Block_Adminhtml_Menu_Item_Edit_Tab_Content extends Mage_Adminhtml_Block_Widget_Form
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
        $form->setHtmlIdPrefix('content_');

        $fieldset = $form->addFieldset('fielset_content', array(
            'legend'       => $this->_getHelper()->__('Content'),
            'class'        => 'fieldset-wide'
        ));

        /**
         * @var $templateSource Astrio_Menu_Model_System_Config_Source_TemplateMenuItem
         */
        $templateSource = Mage::getModel('astrio_menu/system_config_source_templateMenuItem');

        $fieldset->addField('template', 'select', array(
            'name'      => 'template',
            'label'     => $this->_getHelper()->__('Template'),
            'required'  => false,
            'class'     => '',
            'values'    => $templateSource->toOptionArray(),
        ));

        $sectionsCount = $this->_getHelper()->getMenuItemSectionsCount();
        for ($i = 1; $i <= $sectionsCount; $i++) {
            $fieldset->addField('section' . $i, 'editor', array(
                'name'      => 'sections[' . $i . ']',
                'label'     => $this->_getHelper()->__('Section #' . $i),
                'required'  => false,
                'class'     => '',
                'config'    => Mage::getSingleton('cms/wysiwyg_config')->getConfig(),
                'value'     => $model->getSection($i),
            ));
        }

        Mage::dispatchEvent('adminhtml_astrio_menu_item_edit_content_tab_prepare_form', array('form' => $form));

        $form->setUseContainer(false);
        $form->addValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}