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
class Astrio_Menu_Helper_Data extends Mage_Core_Helper_Abstract
{
    // xml config path for menu item sections count
    const XML_PATH_MENU_ITEM_SECTIONS_COUNT = 'astrio_menu/settings/sections_count';

    /**
     * @param null|int|Mage_Core_Model_Store $store store id
     * @return int
     */
    public function getMenuItemSectionsCount($store = null)
    {
        return (int) Mage::getStoreConfig(self::XML_PATH_MENU_ITEM_SECTIONS_COUNT, $store);
    }
}