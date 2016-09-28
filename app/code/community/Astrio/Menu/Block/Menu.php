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
class Astrio_Menu_Block_Menu extends Mage_Core_Block_Template
{
    /**
     * @return Astrio_Menu_Model_Resource_Menu_Item_Collection
     */
    public function getMenuItems()
    {
        if (!$this->hasData('menu_items')) {
            /**
             * @var $collection Astrio_Menu_Model_Resource_Menu_Item_Collection
             */
            $collection = Mage::getResourceModel('astrio_menu/menu_item_collection');
            $collection
                ->addStoreFilter()
                ->addIsActiveFilter()
                ->addOrderByPosition();

            $this->setData('menu_items', $collection);
        }

        return $this->getData('menu_items');
    }

    /**
     * Get extra string
     *
     * @param Astrio_Menu_Model_Menu_Item $menuItem menu item
     * @return string
     */
    public function getExtraString(Astrio_Menu_Model_Menu_Item $menuItem)
    {
        $rows = array();

        $str = trim($menuItem->getExtra());
        if ($str) {
            $rowsArr = explode("\n", $str);
            foreach ($rowsArr as $value) {
                $value = explode(":", $value);
                if (count($value) == 2) {
                    $rows[] = trim($value[0]) . '="' . trim($value[1]) . '"';
                }
            }
        }

        return implode(' ', $rows);
    }

    /**
     * Get menu item content
     *
     * @param Astrio_Menu_Model_Menu_Item $menuItem menu item
     * @return string
     */
    public function getMenuItemContent(Astrio_Menu_Model_Menu_Item $menuItem)
    {
        if (!$menuItem->getTemplate()) {
            return '';
        }

        /**
         * @var $menuItemBlock Astrio_Menu_Block_Menu_Item
         */
        $menuItemBlock = $this->getLayout()->createBlock('astrio_menu/menu_item');
        $menuItemBlock->setMenuItem($menuItem);
        return $menuItemBlock->toHtml();
    }

    /**
     * To html
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!count($this->getMenuItems())) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Get cache life time
     *
     * @return int
     */
    public function getCacheLifetime()
    {
        return 86400;
    }

    /**
     * is block html cache enabled ?
     *
     * @return bool
     */
    protected function _canUseCache()
    {
        /**
         * @var $cacheHelper Astrio_Core_Helper_Cache
         */
        $cacheHelper = Mage::helper('astrio_core/cache');
        return $cacheHelper->isCacheEnabled(self::CACHE_GROUP);
    }

    /**
     * Load block html from cache storage
     *
     * @return string | false
     */
    protected function _loadCache()
    {
        if (is_null($this->getCacheLifetime()) || !$this->_getApp()->useCache(self::CACHE_GROUP) && !$this->_canUseCache()) {
            return false;
        }
        $cacheKey = $this->getCacheKey();
        /** @var $session Mage_Core_Model_Session */
        $session = Mage::getSingleton('core/session');
        $cacheData = $this->_getApp()->loadCache($cacheKey);
        if ($cacheData) {
            $cacheData = str_replace(
                $this->_getSidPlaceholder($cacheKey),
                $session->getSessionIdQueryParam() . '=' . $session->getEncryptedSessionId(),
                $cacheData
            );
        }
        return $cacheData;
    }

    /**
     * Save block content to cache storage
     *
     * @param string $data data
     * @return Mage_Core_Block_Abstract
     */
    protected function _saveCache($data)
    {
        if (is_null($this->getCacheLifetime()) || !$this->_getApp()->useCache(self::CACHE_GROUP) && !$this->_canUseCache()) {
            return false;
        }
        $cacheKey = $this->getCacheKey();
        /** @var $session Mage_Core_Model_Session */
        $session = Mage::getSingleton('core/session');
        $data = str_replace(
            $session->getSessionIdQueryParam() . '=' . $session->getEncryptedSessionId(),
            $this->_getSidPlaceholder($cacheKey),
            $data
        );

        $tags = $this->getCacheTags();

        $this->_getApp()->saveCache($data, $cacheKey, $tags, $this->getCacheLifetime());
        $this->_getApp()->saveCache(
            json_encode($tags),
            $this->_getTagsCacheKey($cacheKey),
            $tags,
            $this->getCacheLifetime()
        );
        return $this;
    }

    /**
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return array(
            'BLOCK_TPL',
            Mage::app()->getStore()->getCode(),
            $this->getTemplateFile(),
            'template' => $this->getTemplate(),
            'is_secure' => Mage::app()->getStore()->isCurrentlySecure() // separate cache for http and https
        );
    }
}