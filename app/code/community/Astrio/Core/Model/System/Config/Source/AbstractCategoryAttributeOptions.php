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
class Astrio_Core_Model_System_Config_Source_AbstractCategoryAttributeOptions extends Astrio_Core_Model_System_Config_Source_Abstract
{

    // DO NOT FORGET TO ADD THIS ATTRIBUTES TO YOUR CLASS !!!!
    protected static $_optionArray = null;

    protected static $_toOptionArray = null;

    protected static $_emptyValueLabel = '-- Please Select --';

    protected static $_attributeCode = '';

    /**
     * Retrieve option array
     *
     * @return array
     */
    protected static function _getOptionArray()
    {
        $result = array();

        $attrCode = static::$_attributeCode;
        if ($attrCode) {
            /**
             * @var $productResource Mage_Catalog_Model_Resource_Category
             */
            $categoryResource = Mage::getResourceModel('catalog/category');
            $attribute = $categoryResource->getAttribute($attrCode);

            if ($attribute && $attribute instanceof Mage_Catalog_Model_Resource_Eav_Attribute && $attribute->getId()) {
                if ($attribute->usesSource()) {
                    $options = $attribute->getSource()->getAllOptions();
                    foreach ($options as $option) {
                        if (isset($option['value']) && strlen($option['value'])) {
                            $result[$option['value']] = $option['label'];
                        }
                    }
                }
            }
        }

        return $result;
    }
}