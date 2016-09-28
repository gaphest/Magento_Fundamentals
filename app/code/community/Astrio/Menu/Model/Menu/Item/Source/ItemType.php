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
class Astrio_Menu_Model_Menu_Item_Source_ItemType
{
    // item type: custom
    const CUSTOM    = 0;
    // item type: category
    const CATEGORY  = 1;
    // item type: cms page
    const CMS_PAGE  = 2;

    /**
     * To option array
     *
     * @return array
     */
    public static function toOptionArray()
    {
        /**
         * @var $helper Astrio_Menu_Helper_Data
         */
        $helper = Mage::helper('astrio_menu');
        return array(
            self::CUSTOM    => $helper->__('Custom'),
            self::CATEGORY  => $helper->__('Category'),
            self::CMS_PAGE  => $helper->__('CMS Page'),
        );
    }
}