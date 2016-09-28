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
 * @package    Astrio_Core
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Astrio
 *
 * @category Astrio
 * @package Astrio_Core
 * @author Eldaniz Gasymov <e.gasymov@astrio.net>
 */
abstract class Astrio_Core_Block_Adminhtml_System_Config_Form_Field_Mapping_AbstractHtml
    extends Mage_Adminhtml_Block_Widget_Container
{

    protected $_attributes;

    protected $_addMapButtonHtml;

    protected $_removeMapButtonHtml;

    protected $_value;

    /**
     * Set element
     *
     * @param Varien_Data_Form_Element_Abstract $element element
     * @return $this
     */
    public function setElement(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setData('element', $element);
        $this->_value = $this->getElement()->getValue();
        return $this;
    }

    /**
     * Get element
     *
     * @return Varien_Data_Form_Element_Abstract
     */
    public function getElement()
    {
        return $this->getData('element');
    }

    /**
     * Get row selected
     *
     * @param string $key key
     * @param string $value value
     * @param string $source source
     * @return string
     */
    public function getRowSelected($key, $value, $source)
    {
        return isset($this->_value[$source][$key]) && $this->_value[$source][$key] == $value ? 'selected="selected"' : '';
    }

    /**
     * Get row value
     *
     * @param string $key key
     * @param string $source source
     * @return string
     */
    public function getRowValue($key, $source)
    {
        return isset($this->_value[$source][$key]) ? $this->_value[$source][$key] : '';
    }

    /**
     * Get mappings
     *
     * @return array
     */
    public function getMappings()
    {
        return isset($this->_value['db']) ? $this->_value['db'] : false;
    }

    /**
     * Get values
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * Get entity
     *
     * @return string
     */
    public function getEntity()
    {
        return (string) $this->getElement()->getFieldConfig()->entity;
    }

    /**
     * Get sorting
     *
     * @return bool
     */
    public function getSorting()
    {
        $sorting = (string) $this->getElement()->getFieldConfig()->sorting;
        if ($sorting !== '') {
            if ($sorting === 'false') {
                return false;
            }

            return (bool)$sorting;
        }

        return true;
    }

    /**
     * Get attributes
     *
     * @return mixed
     */
    public function getAttributes()
    {
        $entity = $this->getEntity();
        if (!isset($this->_attributes[$entity])) {

            switch ($entity) {
                case 'product':
                case 'catalog/product':
                    $attributes = Mage::getSingleton('catalog/convert_parser_product')
                        ->getExternalAttributes();
                    break;
                case 'customer':
                case 'customer/customer':
                    $attributes = Mage::getSingleton('customer/convert_parser_customer')
                        ->getExternalAttributes();
                    break;
                case 'category':
                case 'catalog/category':
                    $internalAttributes = array(
                        'all_children',
                        'children',
                        'children_count',
                        'level',
                        'path',
                        'path_in_store',
                    );
                    /**
                     * @var $categoryAttributes Mage_Catalog_Model_Resource_Category_Attribute_Collection
                     * @var $attribute Mage_Eav_Model_Entity_Attribute
                     */
                    $categoryAttributes = Mage::getResourceModel('catalog/category_attribute_collection')->load();
                    $attributes = array();
                    foreach ($categoryAttributes as $attribute) {
                        $code = $attribute->getAttributeCode();
                        if (!in_array($code, $internalAttributes)) {
                            $attributes[$code] = $code;
                        }
                    }
                    break;
                default:
                    $attributes = array();
                    $model = Mage::getSingleton($entity);
                    if (is_object($model) && method_exists($model, 'getExternalAttributes')) {
                        $attributes = $model->getExternalAttributes();
                    }
            }

            if ($this->getSorting()) {
                asort($attributes);
            }

            $emptyOptionText = (string) $this->getElement()->getFieldConfig()->empty_option_text;
            $emptyOptionText = $emptyOptionText ? $emptyOptionText : '-- Choose an attribute --';

            $attributes = array('' => $this->__($emptyOptionText)) + $attributes;

            $this->_attributes[$entity] = $attributes;
        }
        return $this->_attributes[$entity];
    }

    /**
     * Get remove map button html
     *
     * @return mixed
     */
    public function getRemoveMapButtonHtml()
    {
        if (!$this->_removeMapButtonHtml) {
            $this->_removeMapButtonHtml = $this->getLayout()->createBlock('adminhtml/widget_button')->setType('button')
                ->setClass('delete')->setLabel($this->__('Remove'))
                ->setOnClick("removeFieldMappingSelect(this)")->toHtml();
        }
        return $this->_removeMapButtonHtml;
    }

    /**
     * Get if disabled
     *
     * @return mixed
     */
    public function getDisabled()
    {
        return $this->getElement()->getDisabled();
    }

    /**
     * Get add button text
     *
     * @return string
     */
    public function getAddButtonText()
    {
        $text = (string)$this->getElement()->getFieldConfig()->add_button_text;
        return $text ? $text : 'Add Field Mapping';
    }

    /**
     * Get add map button html
     *
     * @return mixed
     */
    public function getAddMapButtonHtml()
    {
        if (!$this->_addMapButtonHtml) {
            $this->_addMapButtonHtml = $this->getLayout()->createBlock('adminhtml/widget_button')->setType('button')
                ->setClass('add')->setLabel($this->__($this->getAddButtonText()))
                ->setOnClick("addFieldMappingSelect('" . $this->getElement()->getId() . "')")->toHtml();
        }

        return $this->_addMapButtonHtml;
    }

    /**
     * To html
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getData('element')) {
            return '';
        }

        return parent::_toHtml();
    }
}