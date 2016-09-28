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
class Astrio_Menu_Block_Menu_Item extends Mage_Core_Block_Template
{
    /**
     * @return Astrio_Menu_Model_Menu_Item
     */
    public function getMenuItem()
    {
        return $this->_getData('menu_item');
    }

    /**
     * Set menu item
     *
     * @param Astrio_Menu_Model_Menu_Item $menuItem menu item
     * @return $this
     */
    public function setMenuItem(Astrio_Menu_Model_Menu_Item $menuItem)
    {
        $this->setData('menu_item', $menuItem);
        $this->setTemplate($menuItem->getTemplate());
        return $this;
    }

    /**
     * if menu item is not specified return empty string
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getMenuItem()) {
            return '';
        }

        if (!$this->getMenuItem()->getTemplate()) {
            return '';
        }

        return parent::_toHtml();
    }
}