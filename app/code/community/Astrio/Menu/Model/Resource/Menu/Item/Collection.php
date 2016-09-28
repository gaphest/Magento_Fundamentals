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
class Astrio_Menu_Model_Resource_Menu_Item_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected $_storeTable;

    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('astrio_menu/menu_item');

        $this->_storeTable = $this->getTable('astrio_menu/menu_item_store');
    }

    /**
     * Add is active filter
     *
     * @param int $isActive is active?
     * @return $this
     */
    public function addIsActiveFilter($isActive = 1)
    {
        $isActive = (bool) $isActive;
        $this->addFieldToFilter('is_active', (int) $isActive);
        return $this;
    }

    /**
     * Add store filter
     *
     * @param null $storeId store id
     * @return $this
     */
    public function addStoreFilter($storeId = null)
    {
        if ($storeId instanceof Mage_Core_Model_Store) {
            $storeId = (int) $storeId->getId();
        } elseif ($storeId === null) {
            $storeId = (int) Mage::app()->getStore()->getId();
        } else {
            $storeId = (int) $storeId;
        }

        if ($storeId) {
            $idFieldName = $this->getResource()->getIdFieldName();

            $alias = 'store';

            $conditions = array(
                $alias . '.' . $idFieldName . ' = main_table.' . $idFieldName,
                $this->getConnection()->quoteInto('store.store_id = ?', $storeId),
            );

            $this->getSelect()->join(
                array($alias => $this->_storeTable),
                implode(' AND ', $conditions),
                array()
            );
        }

        return $this;
    }

    /**
     * Group by id
     *
     * @return $this
     */
    public function groupById()
    {
        if (!$this->getFlag('grouped_by_id')) {
            $this->getSelect()->group('main_table.' . $this->getResource()->getIdFieldName());
            $this->setFlag('grouped_by_id', true);
        }

        return $this;
    }

    /**
     * Add order by position
     *
     * @param string $dir sort direction
     * @return $this
     */
    public function addOrderByPosition($dir = Varien_Data_Collection::SORT_ORDER_ASC)
    {
        $this->addOrder('main_table.position', $dir);
        return $this;
    }
}