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
 * @package    Astrio_News
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Astrio
 *
 * @category   Astrio
 * @package    Astrio_News
 * @author     Eldaniz Gasymov <e.gasymov@astrio.net>
 */
class Astrio_News_Block_Adminhtml_News_Edit_Tab_Stores extends Mage_Adminhtml_Block_Catalog_Form
{
    
    /**
     * @return Astrio_News_Model_News
     */
    protected function _getEntity()
    {
        return Mage::registry('news');
    }

    /**
     * Prepare attributes form
     *
     * @return null
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        // Initialize news object as form property to use it during elements generation
        $form->setDataObject($this->_getEntity());

        $fieldset = $form->addFieldset('stores_fieldset', array(
            'legend' => Mage::helper('astrio_news')->__('Stores'),
            'class' => 'fieldset-wide'
        ));

        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $field = $fieldset->addField('store_ids', 'multiselect', array(
                'name'      => 'store_ids[]',
                'label'     => Mage::helper('astrio_news')->__('Store View'),
                'title'     => Mage::helper('astrio_news')->__('Store View'),
                'required'  => false,
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
                'disabled'  => false,
            ));
            $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
            $field->setRenderer($renderer);
            /**
             * get store ids for load store ids data :)
             */
            if ($this->_getEntity()->getId()) {
                $this->_getEntity()->getStoreIds();
            }
        } else {
            $fieldset->addField('store_ids', 'hidden', array(
                'name'      => 'store_ids[]',
                'value'     => Mage::app()->getStore(true)->getId()
            ));
        }

        $values = $this->_getEntity()->getData();

        $form->addValues($values);
        $form->setFieldNameSuffix('news');

        Mage::dispatchEvent('adminhtml_astrio_news_edit_prepare_form', array('form' => $form));

        $this->setForm($form);
    }
}
