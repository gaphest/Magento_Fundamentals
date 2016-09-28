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
class Astrio_News_Block_Adminhtml_News_Edit_Tab_Categories extends Mage_Adminhtml_Block_Catalog_Form
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
            'legend' => Mage::helper('astrio_news')->__('Categories'),
            'class' => 'fieldset-wide'
        ));

        /**
         * @var $categories Astrio_News_Model_Resource_Category_Collection
         */
        $categories = Mage::getResourceModel('astrio_news/category_collection');
        $categories->addAttributeToSelect('name');
        $categories->addAttributeToSort('name', Varien_Data_Collection::SORT_ORDER_ASC);

        $fieldset->addField('category_ids', 'multiselect', array(
            'name'      => 'category_ids[]',
            'label'     => Mage::helper('astrio_news')->__('Categories'),
            'title'     => Mage::helper('astrio_news')->__('Categories'),
            'required'  => false,
            'values'    => $categories->toOptionArray(),
        ));

        /**
         * get store ids for load store ids data :)
         */
        if ($this->_getEntity()->getId()) {
            $this->_getEntity()->getCategoryIds();
        }

        $values = $this->_getEntity()->getData();

        $form->addValues($values);
        $form->setFieldNameSuffix('news');

        Mage::dispatchEvent('adminhtml_astrio_news_edit_prepare_form', array('form' => $form));

        $this->setForm($form);
    }
}
