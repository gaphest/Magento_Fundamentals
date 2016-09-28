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
abstract class Astrio_Core_Model_System_Config_Source_Abstract
{

    /**
     * copy this variable to child class
     *
     * array(
     *     'value' => 'label',
     * )
     *
     * @var null|array
     */
    protected static $_optionArray = null;

    /**
     * copy this variable to child class
     *
     * array(
     *     array(
     *         'label' =>
     *         'value' =>
     *     ),
     * );
     *
     * @var null|array
     */
    protected static $_toOptionArray = null;

    protected static $_emptyValueLabel = '-- Please Select --';

    /**
     * override this method
     *
     * @return array
     */
    protected static function _getOptionArray()
    {
        return array();
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public static function getOptionArray()
    {
        if (static::$_optionArray === null) {
            static::$_optionArray = static::_getOptionArray();
        }

        return static::$_optionArray;
    }

    /**
     * Options getter
     * @param bool $isMultiSelect is multiselect?
     * @return array
     */
    public static function toOptionArray($isMultiSelect=false)
    {
        if (static::$_toOptionArray === null) {
            $options = array();
            $optionArray = static::getOptionArray();
            foreach ($optionArray as $value => $label) {
                $options[] = array(
                    'label' => $label,
                    'value' => $value,
                );
            }

            static::$_toOptionArray = $options;
        }

        if ($isMultiSelect) {
            return static::$_toOptionArray;
        }

        $options = static::$_toOptionArray;
        array_unshift($options, array(
            'label' => Mage::helper('astrio_core')->__(static::$_emptyValueLabel),
            'value' => '',
        ));

        return $options;
    }

    /**
     * Get options in "key-value" format
     * @param bool $isMultiSelect is multiselect?
     * @return array
     */
    public static function toArray($isMultiSelect=false)
    {
        if ($isMultiSelect) {
            return static::getOptionArray();
        }

        return array('' => '') + static::getOptionArray();
    }
}
