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
class Astrio_SpecialProducts_Model_Widget_Source_Set
{

    protected static $_toOptionArray = null;

    /**
     * Options getter
     *
     * @return array
     */
    public static function toOptionArray()
    {
        if (self::$_toOptionArray === null) {
            $options = Astrio_SpecialProducts_Model_Widget_Source_Sets::toOptionArray();
            array_unshift($options, array('label' => Mage::helper('astrio_specialproducts')->__('-- Please Select a Set --'),'value' => '',));
            self::$_toOptionArray = $options;
        }

        return self::$_toOptionArray;
    }
}
