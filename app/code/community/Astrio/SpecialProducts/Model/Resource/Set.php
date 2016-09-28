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
class Astrio_SpecialProducts_Model_Resource_Set extends Astrio_Core_Model_Resource_Abstract
{

    // Special products table alias
    const SPECIAL_PRODUCTS_TABLE_ALIAS = 'special_products';

    protected $_storeTable;

    protected $_productsTable;

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('astrio_specialproducts/set', 'set_id');

        $this->_storeTable      = $this->getTable('astrio_specialproducts/set_store');
        $this->_productsTable   = $this->getTable('astrio_specialproducts/set_product');
    }

    /**
     * Before save
     *
     * @param  Mage_Core_Model_Abstract $object object
     * @return Mage_Core_Model_Resource_Db_Abstract
     * @throws Mage_Core_Exception
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        /**
         * @var $object Astrio_SpecialProducts_Model_Set
         */
        $set = $object;

        if (!$this->getIsUniqueSetToStores($set)) {
            Mage::throwException(Mage::helper('astrio_specialproducts')->__('A special products set identifier with the same properties already exists in the selected store.'));
        }

        if ($set->hasData('is_auto') && !$set->getIsAuto()) {
            $set->setData('auto_type', 0);
            $set->setData('catalog_rule_id', null);
            $set->setData('filter_by_category_id', null);
            $set->setData('filter_greater_than', null);
            $set->setData('filter_in_last_days', null);
            $set->setData('products_limit', 0);
        }

        if ($set->hasData('filter_by_category_id') && !$set->getData('filter_by_category_id')) {
            $set->setData('filter_by_category_id', null);
        }

        if ($set->getIsAuto() && $set->getAutoType() == Astrio_SpecialProducts_Model_Set::AUTO_TYPE_CATALOG_RULE) {
            if (!$set->getCatalogRuleId()) {
                Mage::throwException(Mage::helper('astrio_specialproducts')->__('Catalog Rule is required for auto type `Catalog Rule`'));
            }
        } else {
            $set->setCatalogRuleId(NULL);
        }

        return parent::_beforeSave($object);
    }

    /**
     * Get store ids
     *
     * @param  Mage_Core_Model_Abstract $object object
     * @return array
     */
    public function getStoreIds(Mage_Core_Model_Abstract $object)
    {
        /**
         * @var $set Astrio_SpecialProducts_Model_Set
         */
        $set = $object;

        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->_storeTable, array('store_id'))
            ->where('set_id = :set_id');
        $storeIds = $adapter->fetchCol($select, array(':set_id' => $set->getId()));

        if (!empty($storeIds)) {
            return $storeIds;
        }

        if (Mage::app()->isSingleStoreMode()) {
            return array(Mage::app()->getStore(true)->getId());
        }

        return array();
    }

    /**
     * Perform actions after object save
     *
     * @param  Mage_Core_Model_Abstract $object object
     * @return $this
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        /**
         * @var $set Astrio_SpecialProducts_Model_Set
         */
        $set = $object;

        $setId = (int) $set->getId();

        /**
         * save websites
         */
        $adapter = $this->_getWriteAdapter();
        if ($set->hasData('store_ids')) {
            $storeIds = $set->getStoreIds();
            $conditions = array(
                $adapter->quoteInto('set_id = ?', $set->getId()),
            );
            if ($storeIds) {
                $conditions[] = $adapter->quoteInto('store_id NOT IN(?)', $storeIds);
            }

            $adapter->delete($this->_storeTable, implode(' AND ', $conditions));

            $insertedIds = array();
            foreach ($storeIds as $storeId) {
                if (in_array($storeId, $insertedIds)) {
                    continue;
                }

                $insertedIds[] = $storeId;
                $storeInsert = array(
                    'store_id' => $storeId,
                    'set_id'   => $setId,
                );
                $adapter->insertOnDuplicate($this->_storeTable, $storeInsert);
            }
        }

        /**
         * save products
         */
        if (!$set->getIsAuto() && $set->hasData('products')) {
            $adapter->delete($this->_productsTable, $adapter->quoteInto('set_id = ?', $set->getId()));

            $productsData = $set->getProducts();
            $insertArray = array();
            foreach ($productsData as $storeId => $storeProducts) {
                foreach ($storeProducts as $productId => $productData) {
                    $insertArray[] = array(
                        'set_id'            => $setId,
                        'store_id'          => (int) $storeId,
                        'product_id'        => (int) $productId,
                        'customer_group_id' => null,
                        'position'          => (int) $productData['position'],
                    );
                }
            }

            $chunks = array_chunk($insertArray, 500);
            foreach ($chunks as $chunk) {
                $adapter->insertMultiple($this->_productsTable, $chunk);
            }
        }

        /**
         * save label
         */
        if ($set->hasData('label_data')) {
            $label = $set->getLabel();
            if ($set->getApplyLabel()) {
                $label->addData((array) $set->getData('label_data'));
                $label->setId($set->getId());
                $label->save();
            } elseif ($label->getId()) {
                $label->delete();
            }
        }
        /**
         * save page
         */
        if ($set->hasData('page_data')) {
            $page = $set->getPage();
            if ($set->getUseSeparatePage()) {
                $page->addData((array) $set->getData('page_data'));
                $page->setId($set->getId());
                $page->save();
            } elseif ($page->getId()) {
                $page->delete();
            }
        }

        return parent::_afterSave($object);
    }

    /**
     * Perform actions after object load
     *
     * @param  Mage_Core_Model_Abstract $object object
     * @return $this
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        /**
         * @var $set Astrio_SpecialProducts_Model_Set
         */
        $set = $object;
        $set->setStoreIds($this->getStoreIds($object));
        return parent::_afterLoad($object);
    }

    /**
     * Check for unique of identifier of block to selected store(s).
     *
     * @param  Mage_Core_Model_Abstract $object object
     * @return bool
     */
    public function getIsUniqueSetToStores(Mage_Core_Model_Abstract $object)
    {
        /**
         * @var $set Astrio_SpecialProducts_Model_Set
         */
        $set = $object;

        $mainTableAlias     = 'set';
        $storeTableAlias    = 'set_store';
        $idFieldName        = $this->getIdFieldName();

        $select = $this->_getReadAdapter()->select()
            ->from(array($mainTableAlias => $this->getMainTable()))
            ->join(
                array($storeTableAlias => $this->_storeTable),
                "{$mainTableAlias}.{$idFieldName} = {$storeTableAlias}.set_id",
                array()
            )
            ->where("{$mainTableAlias}.identifier = ?", $set->getData('identifier'))
            ->where("{$storeTableAlias}.store_id IN (?)", $set->getStoreIds());

        if ($set->getId()) {
            $select->where("{$mainTableAlias}.{$idFieldName} <> ?", $set->getId());
        }

        if ($this->_getReadAdapter()->fetchRow($select)) {
            return false;
        }

        return true;
    }

    /**
     * @param  Mage_Core_Model_Abstract $object  object
     * @param  int                      $storeId store id
     * @return array
     */
    public function getProductsByStore(Mage_Core_Model_Abstract $object, $storeId)
    {
        /**
         * @var $object Astrio_SpecialProducts_Model_Set
         */
        $select = $this->_getReadAdapter()->select();
        $select->from($this->_productsTable, array('product_id', 'position'))
            ->where('set_id = ?', (int) $object->getId())
            ->where('store_id = ?', (int) $storeId)
            ->group('product_id');

        $result = array();
        $stmt = $this->_getReadAdapter()->query($select);
        while ($row = $stmt->fetch()) {
            $result[$row['product_id']] = array('position' => $row['position']);
        }

        return $result;
    }

    /**
     * Join product collection to special products set with group
     *
     * @param  Astrio_SpecialProducts_Model_Set               $set        set
     * @param  Mage_Catalog_Model_Resource_Product_Collection $collection collection
     * @param  int|string                                     $storeId    store id
     * @param  int|string|null                                $groupId    group id
     * @return $this
     */
    public function joinProductCollectionToSpecialProductsSetWithGroup(Astrio_SpecialProducts_Model_Set $set, Mage_Catalog_Model_Resource_Product_Collection $collection, $storeId, $groupId = null)
    {
        $alias = self::SPECIAL_PRODUCTS_TABLE_ALIAS;

        $select = $collection->getSelect();
        $connection = $collection->getConnection();

        $conditions = array(
            $connection->quoteInto("{$alias}.set_id = ?", (int) $set->getId()),
            $connection->quoteInto("{$alias}.store_id = ?", (int) $storeId),
        );

        if ($groupId === null || !$set->usesCustomerGroups()) {
            $conditions[] = "{$alias}.customer_group_id IS NULL";
        } else {
            $conditions[] = $connection->quoteInto("{$alias}.customer_group_id = ?", (int) $groupId);
        }

        $conditions[] = $collection::MAIN_TABLE_ALIAS . "." . $collection->getResource()->getIdFieldName() . " = {$alias}.product_id";

        $select->join(
            array($alias => $this->_productsTable),
            implode(" AND ", $conditions),
            array('position')
        );

        return $this;
    }

    /**
     * Join product collection to special product set
     *
     * @param  Astrio_SpecialProducts_Model_Set               $set        set
     * @param  Mage_Catalog_Model_Resource_Product_Collection $collection collection
     * @param  int|string                                     $storeId    store id
     * @return $this
     */
    public function joinProductCollectionToSpecialProductsSet(Astrio_SpecialProducts_Model_Set $set, Mage_Catalog_Model_Resource_Product_Collection $collection, $storeId)
    {
        $alias = self::SPECIAL_PRODUCTS_TABLE_ALIAS;

        $select = $collection->getSelect();
        $connection = $collection->getConnection();

        $subQuery = $connection->select();
        $subQuery->from($this->_productsTable, array('product_id', 'position'))
            ->where("set_id = ?", (int) $set->getId())
            ->where("store_id = ?", (int) $storeId)
            ->group("product_id");

        $select->join(
            array($alias => $subQuery),
            implode(" AND ", array(
                $collection::MAIN_TABLE_ALIAS . "." . $collection->getResource()->getIdFieldName() . " = {$alias}.product_id"
            )),
            array('position')
        );

        return $this;
    }

    /**
     * Add customer group ids to special products
     *
     * @param  Mage_Catalog_Model_Resource_Product_Collection $collection collection
     * @param  Astrio_SpecialProducts_Model_Set               $set        set
     * @param  string|int                                     $storeId    store id
     * @return $this
     */
    public function addCustomerGroupIdsToSpecialProducts(Mage_Catalog_Model_Resource_Product_Collection $collection, Astrio_SpecialProducts_Model_Set $set, $storeId)
    {
        $products = $collection->getItems();

        if (!count($products)) {
            return $this;
        }

        $productCustomerGroups = array();
        foreach ($products as $productId => $product) {
            $productCustomerGroups[$productId] = array();
        }

        $select = $this->_getReadAdapter()->select();
        $select->from($this->_productsTable, array('product_id', 'customer_group_id'))
            ->where('set_id = ?', (int) $set->getId())
            ->where('store_id = ?', (int) $storeId)
            ->where('product_id IN(?)', array_keys($products));

        $stmt = $this->_getReadAdapter()->query($select);
        while ($row = $stmt->fetch()) {
            $productCustomerGroups[$row['product_id']][] = $row['customer_group_id'];
        }

        foreach ($products as $productId => $product) {
            if (isset($productCustomerGroups[$productId])) {
                $product->setCustomerGroupIds($productCustomerGroups[$productId]);
            } else {
                $product->setCustomerGroupIds(array());
            }
        }

        return $this;
    }
}
