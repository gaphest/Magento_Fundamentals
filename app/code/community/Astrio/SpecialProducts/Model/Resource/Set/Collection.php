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
 * @category    Astrio
 * @package     Astrio_SpecialProducts
 * @author      Eldaniz Gasymov <e.gasymov@astrio.net>
 */
class Astrio_SpecialProducts_Model_Resource_Set_Collection extends Astrio_Core_Model_Resource_Abstract_Collection
{

    protected $_storeTable;

    protected $_productsTable;

    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('astrio_specialproducts/set');

        $this->_storeTable      = $this->getTable('astrio_specialproducts/set_store');
        $this->_productsTable   = $this->getTable('astrio_specialproducts/set_product');
    }

    /**
     * Add store filter
     *
     * @param  null|Mage_Core_Model_Store|int $storeId store id or store model
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

        if (Mage::app()->getStore($storeId)->isAdmin()) {
            return $this;
        }

        $setStoreTableAlias = 'set_store';

        $joinConditions = array(
            "main_table.set_id = {$setStoreTableAlias}.set_id",
            $this->getConnection()->quoteInto("{$setStoreTableAlias}.store_id = ?", $storeId),
        );

        $this->getSelect()
            ->join(
                array($setStoreTableAlias => $this->_storeTable),
                implode(' AND ', $joinConditions),
                array()
            );

        return $this;
    }

    /**
     * Adding set website names to result collection
     * Add for each set websites information
     *
     * @return $this
     */
    public function addStoreIdsToResult()
    {
        /**
         * @var $set Astrio_SpecialProducts_Model_Set
         */
        $setStores = array();
        foreach ($this as $set) {
            $setStores[$set->getId()] = array();
        }

        if (!empty($setStores)) {
            $select = $this->getConnection()->select()
                ->from($this->_storeTable)
                ->where('set_id IN (?)', array_keys($setStores));

            $stmt = $this->getConnection()->query($select);
            while ($row = $stmt->fetch()) {
                $setStores[$row['set_id']][] = $row['store_id'];
            }
        }

        foreach ($this as $set) {
            if (isset($setStores[$set->getId()])) {
                $set->setStoreIds($setStores[$set->getId()]);
            } else {
                $set->setStoreIds(array());
            }
        }

        return $this;
    }

    /**
     * Add is active filter
     *
     * @param  bool|int $isActive is active?
     * @return $this
     */
    public function addIsActiveFilter($isActive = 1)
    {
        $this->addFieldToFilter('main_table.is_active', (int) $isActive);
        return $this;
    }

    /**
     * Add is auto filter
     *
     * @param bool|int $isAuto is auto?
     * @return $this
     */
    public function addIsAutoFilter($isAuto = 1)
    {
        $this->addFieldToFilter('main_table.is_auto', (int) $isAuto);
        return $this;
    }

    /**
     * Add identifier filter
     *
     * @param  string $identifier identifier
     * @return $this
     */
    public function addIdentifierFilter($identifier)
    {
        $this->addFieldToFilter('main_table.identifier', $identifier);
        return $this;
    }

    /**
     * Add use separate page filter
     *
     * @param  int $use use?
     * @return $this
     */
    public function addUseSeparatePageFilter($use = 1)
    {
        $this->addFieldToFilter('main_table.use_separate_page', (int) $use);
        return $this;
    }
}
