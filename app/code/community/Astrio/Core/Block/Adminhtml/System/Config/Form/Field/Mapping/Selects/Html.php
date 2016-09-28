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
 * @see Mage_Adminhtml_Block_System_Convert_Gui_Edit_Tab_Wizard
 */
class Astrio_Core_Block_Adminhtml_System_Config_Form_Field_Mapping_Selects_Html
    extends Astrio_Core_Block_Adminhtml_System_Config_Form_Field_Mapping_AbstractHtml
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('astrio/core/system/config/form/field/mapping/selects/html.phtml');
    }

    /**
     * Get entity 2
     *
     * @return string
     */
    public function getEntity2()
    {
        return (string) $this->getElement()->getFieldConfig()->entity2;
    }

    /**
     * Get attributes 2
     *
     * @return mixed
     */
    public function getAttributes2()
    {
        $entity = $this->getEntity2();
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

            $emptyOptionText = (string) $this->getElement()->getFieldConfig()->empty_option_text2;
            $emptyOptionText = $emptyOptionText ? $emptyOptionText : '-- Choose an attribute --';

            $attributes = array('' => $this->__($emptyOptionText)) + $attributes;

            $this->_attributes[$entity] = $attributes;
        }
        return $this->_attributes[$entity];
    }
}