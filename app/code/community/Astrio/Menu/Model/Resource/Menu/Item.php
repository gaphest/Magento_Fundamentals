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
class Astrio_Menu_Model_Resource_Menu_Item extends Mage_Core_Model_Resource_Db_Abstract
{

    protected $_storeTable;

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('astrio_menu/menu_item', 'item_id');

        $this->_storeTable = $this->getTable('astrio_menu/menu_item_store');
    }

    /**
     * Before save
     *
     * @param Mage_Core_Model_Abstract $object object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        /**
         * @var $object Astrio_Menu_Model_Menu_Item
         */
        if ($object->getItemType() != Astrio_Menu_Model_Menu_Item_Source_ItemType::CATEGORY) {
            $object->setData('category_id', null);
        }
        if ($object->getItemType() != Astrio_Menu_Model_Menu_Item_Source_ItemType::CMS_PAGE) {
            $object->setData('cms_page_id', null);
        }

        $sections = $object->getData('sections');
        if (is_array($sections)) {
            foreach ($sections as $k => $section) {
                $sections[$k] = trim($section);
            }
            $object->setData('sections', serialize($sections));
        } elseif (!$sections) {
            $object->setData('sections', serialize(array()));
        }

        return parent::_beforeSave($object);
    }

    /**
     * Get stores
     *
     * @param Mage_Core_Model_Abstract $object object
     * @return array
     */
    public function getStores(Mage_Core_Model_Abstract $object)
    {
        if (Mage::app()->isSingleStoreMode()) {
            return array(Mage::app()->getStore(true)->getId());
        }

        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->_storeTable, array('store_id'))
            ->where('item_id = :item_id');
        $stores = $adapter->fetchCol($select, array(':item_id' => $object->getId()));

        if (!empty($stores)) {
            return $stores;
        }

        return array();
    }

    /**
     * Perform actions after object save
     *
     * @param Mage_Core_Model_Abstract $object object
     * @return $this
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $adapter = $this->_getWriteAdapter();
        if ($object->hasData('stores')) {
            $stores = $object->getStores();

            $condition = array('item_id = ?' => $object->getId());
            $adapter->delete($this->_storeTable, $condition);

            $insertedIds = array();
            foreach ($stores as $storeId) {
                if (in_array($storeId, $insertedIds)) {
                    continue;
                }

                $insertedIds[] = $storeId;
                $storeInsert = array(
                    'store_id'  => $storeId,
                    'item_id'   => $object->getId(),
                );
                $adapter->insertOnDuplicate($this->_storeTable, $storeInsert);
            }
        }

        return parent::_afterSave($object);
    }

    /**
     * Perform actions after object load
     *
     * @param Mage_Core_Model_Abstract $object object
     * @return $this
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $object->setStores($this->getStores($object));
        return parent::_afterLoad($object);
    }
}