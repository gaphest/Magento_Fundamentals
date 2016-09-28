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
 * @package    Astrio_News
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Astrio
 *
 * @category   Astrio
 * @package    Astrio_News
 * @author     Eldaniz Gasymov <e.gasymov@astrio.net>
 */
abstract class Astrio_News_Model_Resource_Abstract_Collection extends Mage_Catalog_Model_Resource_Collection_Abstract
{

    /**
     * Alias for main table
     */
    const MAIN_TABLE_ALIAS = 'e';

    protected $_entityTypeCode;

    /**
     * Cache for all ids
     *
     * @var array
     */
    protected $_allIdsCache = null;

    /**
     * Initialize factory
     *
     * @param Mage_Core_Model_Resource_Abstract $resource resource
     * @param array $args arguments
     */
    public function __construct($resource = null, array $args = array())
    {
        parent::__construct($resource);
        $this->_entityTypeCode = $this->getEntity()->getType();
    }

    /**
     * Initialize collection select
     * Re-declared for remove entity_type_id condition
     * in astrio_news we store just news
     *
     * @return Astrio_News_Model_Resource_News_Collection
     */
    protected function _initSelect()
    {
        $this->getSelect()->from(array(self::MAIN_TABLE_ALIAS => $this->getEntity()->getEntityTable()));
        return $this;
    }

    /**
     * Add collection filters by identifiers
     *
     * @param  mixed   $newsId  news id
     * @param  boolean $exclude exclude?
     * @return Astrio_News_Model_Resource_News_Collection
     */
    public function addIdFilter($newsId, $exclude = false)
    {
        if (empty($newsId)) {
            $this->_setIsLoaded(true);
            return $this;
        }

        if (is_array($newsId)) {
            if (!empty($newsId)) {
                if ($exclude) {
                    $condition = array('nin' => $newsId);
                } else {
                    $condition = array('in' => $newsId);
                }
            } else {
                $condition = '';
            }
        } else {
            if ($exclude) {
                $condition = array('neq' => $newsId);
            } else {
                $condition = $newsId;
            }
        }

        $this->addFieldToFilter('entity_id', $condition);
        return $this;
    }

    /**
     * Set store scope
     *
     * @param  int|string|Mage_Core_Model_Store $store store id or store object
     * @return $this
     */
    public function setStore($store)
    {
        return parent::setStore($store);
    }

    /**
     * Set store scope
     *
     * @param  int|string|Mage_Core_Model_Store $storeId store id or store object
     * @return $this
     */
    public function setStoreId($storeId)
    {
        return parent::setStoreId($storeId);
    }

    /**
     * Add store availability filter.
     *
     * @param  mixed $store store id
     * @return Astrio_News_Model_Resource_News_Collection
     */
    public function addStoreFilter($store = null)
    {
        return $this;
    }

    /**
     * Add tax class id attribute to select and join price rules data if needed
     *
     * @return Astrio_News_Model_Resource_News_Collection
     */
    protected function _beforeLoad()
    {
        Mage::dispatchEvent($this->_entityTypeCode . '_collection_load_before', array('collection' => $this));
        return parent::_beforeLoad();
    }

    /**
     * Processing collection items after loading
     *
     * @return Astrio_News_Model_Resource_News_Collection
     */
    protected function _afterLoad()
    {
        if (count($this) > 0) {
            Mage::dispatchEvent($this->_entityTypeCode . '_collection_load_after', array('collection' => $this));
        }

        return $this;
    }

    /**
     * Retrieve max value by attribute
     *
     * @param  string $attribute attribute
     * @return mixed
     */
    public function getMaxAttributeValue($attribute)
    {
        $select        = clone $this->getSelect();
        $attribute     = $this->getEntity()->getAttribute($attribute);
        $attributeCode = $attribute->getAttributeCode();
        $tableAlias    = $attributeCode . '_max_value';
        $fieldAlias    = 'max_' . $attributeCode;
        $condition  = self::MAIN_TABLE_ALIAS . '.entity_id = ' . $tableAlias . '.entity_id
            AND '.$this->_getConditionSql($tableAlias . '.attribute_id', $attribute->getId());

        $select->join(
            array($tableAlias => $attribute->getBackend()->getTable()),
            $condition,
            array($fieldAlias => new Zend_Db_Expr('MAX('.$tableAlias.'.value)'))
        )
            ->group(self::MAIN_TABLE_ALIAS . '.entity_type_id');

        $data = $this->getConnection()->fetchRow($select);
        if (isset($data[$fieldAlias])) {
            return $data[$fieldAlias];
        }

        return null;
    }

    /**
     * Retrieve ranging news count for attribute range
     *
     * @param  string $attribute attribute
     * @param  int    $range     range
     * @return array
     */
    public function getAttributeValueCountByRange($attribute, $range)
    {
        $select        = clone $this->getSelect();
        $attribute     = $this->getEntity()->getAttribute($attribute);
        $attributeCode = $attribute->getAttributeCode();
        $tableAlias    = $attributeCode . '_range_count_value';

        $condition  = self::MAIN_TABLE_ALIAS . '.entity_id = ' . $tableAlias . '.entity_id
            AND ' . $this->_getConditionSql($tableAlias . '.attribute_id', $attribute->getId());

        $select->reset(Zend_Db_Select::GROUP);
        $select->join(
            array($tableAlias => $attribute->getBackend()->getTable()),
            $condition,
            array(
                'count_' . $attributeCode => new Zend_Db_Expr('COUNT(DISTINCT ' . self::MAIN_TABLE_ALIAS . '.entity_id)'),
                'range_' . $attributeCode => new Zend_Db_Expr(
                        'CEIL((' . $tableAlias . '.value+0.01)/' . $range . ')')
            )
        )
            ->group('range_' . $attributeCode);

        $data   = $this->getConnection()->fetchAll($select);
        $res    = array();

        foreach ($data as $row) {
            $res[$row['range_' . $attributeCode]] = $row['count_' . $attributeCode];
        }

        return $res;
    }

    /**
     * Retrieve news count by some value of attribute
     *
     * @param  string $attribute attribute
     * @return array($value=>$count)
     */
    public function getAttributeValueCount($attribute)
    {
        $select        = clone $this->getSelect();
        $attribute     = $this->getEntity()->getAttribute($attribute);
        $attributeCode = $attribute->getAttributeCode();
        $tableAlias    = $attributeCode . '_value_count';

        $select->reset(Zend_Db_Select::GROUP);
        $condition  = self::MAIN_TABLE_ALIAS . '.entity_id=' . $tableAlias . '.entity_id
            AND '.$this->_getConditionSql($tableAlias . '.attribute_id', $attribute->getId());

        $select->join(
            array($tableAlias => $attribute->getBackend()->getTable()),
            $condition,
            array(
                'count_' . $attributeCode => new Zend_Db_Expr('COUNT(DISTINCT ' . self::MAIN_TABLE_ALIAS . '.entity_id)'),
                'value_' . $attributeCode => new Zend_Db_Expr($tableAlias . '.value')
            )
        )
            ->group('value_' . $attributeCode);

        $data   = $this->getConnection()->fetchAll($select);
        $res    = array();

        foreach ($data as $row) {
            $res[$row['value_' . $attributeCode]] = $row['count_' . $attributeCode];
        }

        return $res;
    }

    /**
     * Return all attribute values as array in form:
     * array(
     *   [entity_id_1] => array(
     *          [store_id_1] => store_value_1,
     *          [store_id_2] => store_value_2,
     *          ...
     *          [store_id_n] => store_value_n
     *   ),
     *   ...
     * )
     *
     * @param  string $attribute attribute code
     * @return array
     */
    public function getAllAttributeValues($attribute)
    {
        /** @var $select Varien_Db_Select */
        $select    = clone $this->getSelect();
        $attribute = $this->getEntity()->getAttribute($attribute);

        $select->reset()
            ->from($attribute->getBackend()->getTable(), array('entity_id', 'store_id', 'value'))
            ->where('attribute_id = ?', (int)$attribute->getId());

        $data = $this->getConnection()->fetchAll($select);
        $res  = array();

        foreach ($data as $row) {
            $res[$row['entity_id']][$row['store_id']] = $row['value'];
        }

        return $res;
    }

    /**
     * Get SQL for get record count without left JOINs
     *
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        return $this->_getSelectCountSql();
    }

    /**
     * Get SQL for get record count
     *
     * @param  Varien_Db_Select $select         select
     * @param  bool             $resetLeftJoins reset left joins?
     * @return Varien_Db_Select
     */
    protected function _getSelectCountSql($select = null, $resetLeftJoins = true)
    {
        $this->_renderFilters();
        $countSelect = (is_null($select)) ? $this->_getClearSelect() : $this->_buildClearSelect($select);
        $countSelect->columns('COUNT(DISTINCT ' . self::MAIN_TABLE_ALIAS . '.entity_id)');
        if ($resetLeftJoins) {
            $countSelect->resetJoinLeft();
        }
        return $countSelect;
    }

    /**
     * Retrieve clear select
     *
     * @return Varien_Db_Select
     */
    protected function _getClearSelect()
    {
        return $this->_buildClearSelect();
    }

    /**
     * Build clear select
     *
     * @param  Varien_Db_Select $select select
     * @return Varien_Db_Select
     */
    protected function _buildClearSelect($select = null)
    {
        if (is_null($select)) {
            $select = clone $this->getSelect();
        }

        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);
        $select->reset(Zend_Db_Select::COLUMNS);

        return $select;
    }

    /**
     * Retrieve all ids for collection
     *
     * @param  int $limit  limit
     * @param  int $offset offset
     * @return array
     */
    public function getAllIds($limit = null, $offset = null)
    {
        $idsSelect = $this->_getClearSelect();
        $idsSelect->columns(self::MAIN_TABLE_ALIAS . '.' . $this->getEntity()->getIdFieldName());
        $idsSelect->limit($limit, $offset);
        $idsSelect->resetJoinLeft();

        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }

    /**
     * Retrieve all ids
     *
     * @param  boolean $resetCache reset cache?
     * @return array
     */
    public function getAllIdsCache($resetCache = false)
    {
        $ids = null;
        if (!$resetCache) {
            $ids = $this->_allIdsCache;
        }

        if (is_null($ids)) {
            $ids = $this->getAllIds();
            $this->setAllIdsCache($ids);
        }

        return $ids;
    }

    /**
     * Set all ids
     *
     * @param  array $value value
     * @return Astrio_News_Model_Resource_News_Collection
     */
    public function setAllIdsCache($value)
    {
        $this->_allIdsCache = $value;
        return $this;
    }

    /**
     * Add attribute to filter
     *
     * @param  Mage_Eav_Model_Entity_Attribute_Abstract|string $attribute attribute
     * @param  array                                           $condition condition
     * @param  string                                          $joinType  join type
     * @return Astrio_News_Model_Resource_News_Collection
     */
    public function addAttributeToFilter($attribute, $condition = null, $joinType = 'inner')
    {
        $this->_allIdsCache = null;
        return parent::addAttributeToFilter($attribute, $condition, $joinType);
    }

    /**
     * Add attribute to sort order
     *
     * @param  string $attribute attribute
     * @param  string $dir       direction
     * @return Astrio_News_Model_Resource_News_Collection
     */
    public function addAttributeToSort($attribute, $dir = self::SORT_ORDER_ASC)
    {
        $attrInstance = $this->getEntity()->getAttribute($attribute);
        if ($attrInstance && $attrInstance->usesSource() && $attrInstance->getSourceModel() != 'eav/entity_attribute_source_boolean') {
            $attrInstance->getSource()
                ->addValueSortToCollection($this, $dir);
            return $this;
        }

        return parent::addAttributeToSort($attribute, $dir);
    }

    /**
     * Clear collection
     *
     * @return Astrio_News_Model_Resource_News_Collection
     */
    public function clear()
    {
        foreach ($this->_items as $i => $item) {
            $item = $this->_items[$i] = null;
        }

        foreach ($this->_itemsById as $i => $item) {
            $item = $this->_itemsById[$i] = null;
        }

        unset($this->_items, $this->_data, $this->_itemsById);
        $this->_data = array();
        $this->_itemsById = array();
        return parent::clear();
    }

    /**
     * Set Order field
     *
     * @param  string $attribute attribute
     * @param  string $dir       direction
     * @return $this
     */
    public function setOrder($attribute, $dir = 'desc')
    {
        return parent::setOrder($attribute, $dir);
    }

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('entity_id', 'name');
    }

    /**
     * To option hash
     *
     * @return array
     */
    public function toOptionHash()
    {
        return $this->_toOptionHash('entity_id', 'name');
    }

    /**
     * Add attribute collection to select
     *
     * @return $this
     * @throws Mage_Core_Exception
     */
    public function addCollectionAttributesToSelect()
    {
        /**
         * @var $config Astrio_News_Model_Config
         */
        $config = Mage::getSingleton('astrio_news/config');
        $this->addAttributeToSelect($config->getAttributes($this->_entityTypeCode));
        return $this;
    }

    /**
     * Add is active filter
     *
     * @param int $isActive is active?
     * @return $this
     */
    public function addIsActiveFilter($isActive = 1)
    {
        $this->addAttributeToFilter('is_active', $isActive);
        return $this;
    }
}
