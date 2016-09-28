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
 * @package    Astrio_Core
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Astrio
 *
 * @category Astrio
 * @package Astrio_Brand
 * @author Eldaniz Gasymov <e.gasymov@astrio.net>
 */
class Astrio_Brand_Model_Resource_Brand_Collection extends Mage_Catalog_Model_Resource_Collection_Abstract
{
    /**
     * Alias for main table
     */
    const MAIN_TABLE_ALIAS = 'e';

    /**
     * Brand websites table name
     *
     * @var string
     */
    protected $_brandWebsiteTable;

    /**
     * Is add URL rewrites to collection flag
     *
     * @var bool
     */
    protected $_addUrlRewrite                = false;

    /**
     * Cache for all ids
     *
     * @var array
     */
    protected $_allIdsCache                  = null;

    /**
     * Catalog factory instance
     *
     * @var Astrio_Brand_Model_Factory
     */
    protected $_factory;

    /**
     * Initialize factory
     *
     * @param Mage_Core_Model_Resource_Abstract $resource resource
     * @param array $args args
     */
    public function __construct($resource = null, array $args = array())
    {
        parent::__construct($resource);
        $this->_factory = !empty($args['factory']) ? $args['factory'] : Mage::getSingleton('astrio_brand/factory');
    }

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('astrio_brand/brand');
        $this->_initTables();
    }

    /**
     * Define product website and category product tables
     */
    protected function _initTables()
    {
        $this->_brandWebsiteTable = $this->getResource()->getTable('astrio_brand/brand_website');
    }

    /**
     * Initialize collection select
     * Redeclared for remove entity_type_id condition
     * in astrio_brand we store just brands
     *
     * @return Astrio_Brand_Model_Resource_Brand_Collection
     */
    protected function _initSelect()
    {
        $this->getSelect()->from(array(self::MAIN_TABLE_ALIAS => $this->getEntity()->getEntityTable()));
        return $this;
    }

    /**
     * Add tax class id attribute to select and join price rules data if needed
     *
     * @return Astrio_Brand_Model_Resource_Brand_Collection
     */
    protected function _beforeLoad()
    {
        Mage::dispatchEvent('astrio_brand_collection_load_before', array('collection' => $this));

        return parent::_beforeLoad();
    }

    /**
     * Processing collection items after loading
     *
     * @return Astrio_Brand_Model_Resource_Brand_Collection
     */
    protected function _afterLoad()
    {
        if ($this->_addUrlRewrite) {
            $this->_addUrlRewrite();
        }

        if (count($this) > 0) {
            Mage::dispatchEvent('astrio_brand_collection_load_after', array('collection' => $this));
        }

        return $this;
    }

    /**
     * Add collection filters by identifiers
     *
     * @param mixed $brandId brand id
     * @param boolean $exclude exclude?
     * @return Astrio_Brand_Model_Resource_Brand_Collection
     */
    public function addIdFilter($brandId, $exclude = false)
    {
        if (empty($brandId)) {
            $this->_setIsLoaded(true);
            return $this;
        }
        if (is_array($brandId)) {
            if (!empty($brandId)) {
                if ($exclude) {
                    $condition = array('nin' => $brandId);
                } else {
                    $condition = array('in' => $brandId);
                }
            } else {
                $condition = '';
            }
        } else {
            if ($exclude) {
                $condition = array('neq' => $brandId);
            } else {
                $condition = $brandId;
            }
        }
        $this->addFieldToFilter('entity_id', $condition);
        return $this;
    }

    /**
     * Adding brand website names to result collection
     * Add for each brand websites information
     *
     * @return Astrio_Brand_Model_Resource_Brand_Collection
     */
    public function addWebsiteNamesToResult()
    {
        $brandWebsites = array();
        foreach ($this as $brand) {
            $brandWebsites[$brand->getId()] = array();
        }

        if (!empty($brandWebsites)) {
            $select = $this->getConnection()->select()
                ->from(array('brand_website' => $this->_brandWebsiteTable))
                ->join(
                    array('website' => $this->getResource()->getTable('core/website')),
                    'website.website_id = brand_website.website_id',
                    array('name'))
                ->where('brand_website.brand_id IN (?)', array_keys($brandWebsites))
                ->where('website.website_id > ?', 0);

            $data = $this->getConnection()->fetchAll($select);
            foreach ($data as $row) {
                $brandWebsites[$row['brand_id']][] = $row['website_id'];
            }
        }

        foreach ($this as $brand) {
            if (isset($brandWebsites[$brand->getId()])) {
                $brand->setData('websites', $brandWebsites[$brand->getId()]);
            }
        }
        return $this;
    }

    /**
     * Add store availability filter.
     *
     * @param mixed $store store
     * @return Astrio_Brand_Model_Resource_Brand_Collection
     */
    public function addStoreFilter($store = null)
    {
        if ($store === null) {
            $store = $this->getStoreId();
        }
        $store = Mage::app()->getStore($store);

        if (!$store->isAdmin()) {
            $this->addWebsiteFilter($store->getWebsiteId());
        }

        return $this;
    }

    /**
     * Add website filter to collection
     *
     * @param mixed $websites websites
     * @return Astrio_Brand_Model_Resource_Brand_Collection
     */
    public function addWebsiteFilter($websites = null)
    {
        if (!is_array($websites)) {
            $websites = array(Mage::app()->getWebsite($websites)->getId());
        }

        $conditions  = array('brand_website.brand_id = e.entity_id');
        if (count($websites) > 1) {
            $this->getSelect()->distinct(true);
        }
        $conditions[] = $this->getConnection()
            ->quoteInto('brand_website.website_id IN(?)', $websites);

        $this->getSelect()->join(
            array('brand_website' => $this->getTable('astrio_brand/brand_website')),
            join(' AND ', $conditions),
            array()
        );

        return $this;
    }

    /**
     * Retrieve max value by attribute
     *
     * @param string $attribute attribute
     * @return mixed
     */
    public function getMaxAttributeValue($attribute)
    {
        $select        = clone $this->getSelect();
        $attribute     = $this->getEntity()->getAttribute($attribute);
        $attributeCode = $attribute->getAttributeCode();
        $tableAlias    = $attributeCode . '_max_value';
        $fieldAlias    = 'max_' . $attributeCode;
        $condition  = 'e.entity_id = ' . $tableAlias . '.entity_id
            AND '.$this->_getConditionSql($tableAlias . '.attribute_id', $attribute->getId());

        $select->join(
            array($tableAlias => $attribute->getBackend()->getTable()),
            $condition,
            array($fieldAlias => new Zend_Db_Expr('MAX('.$tableAlias.'.value)'))
        )
            ->group('e.entity_type_id');

        $data = $this->getConnection()->fetchRow($select);
        if (isset($data[$fieldAlias])) {
            return $data[$fieldAlias];
        }

        return null;
    }

    /**
     * Retrieve ranging brand count for attribute range
     *
     * @param string $attribute attribute
     * @param int $range range
     * @return array
     */
    public function getAttributeValueCountByRange($attribute, $range)
    {
        $select        = clone $this->getSelect();
        $attribute     = $this->getEntity()->getAttribute($attribute);
        $attributeCode = $attribute->getAttributeCode();
        $tableAlias    = $attributeCode . '_range_count_value';

        $condition  = 'e.entity_id = ' . $tableAlias . '.entity_id
            AND ' . $this->_getConditionSql($tableAlias . '.attribute_id', $attribute->getId());

        $select->reset(Zend_Db_Select::GROUP);
        $select->join(
            array($tableAlias => $attribute->getBackend()->getTable()),
            $condition,
            array(
                'count_' . $attributeCode => new Zend_Db_Expr('COUNT(DISTINCT e.entity_id)'),
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
     * Retrieve brand count by some value of attribute
     *
     * @param string $attribute attribute
     * @return array($value=>$count)
     */
    public function getAttributeValueCount($attribute)
    {
        $select        = clone $this->getSelect();
        $attribute     = $this->getEntity()->getAttribute($attribute);
        $attributeCode = $attribute->getAttributeCode();
        $tableAlias    = $attributeCode . '_value_count';

        $select->reset(Zend_Db_Select::GROUP);
        $condition  = 'e.entity_id=' . $tableAlias . '.entity_id
            AND '.$this->_getConditionSql($tableAlias . '.attribute_id', $attribute->getId());

        $select->join(
            array($tableAlias => $attribute->getBackend()->getTable()),
            $condition,
            array(
                'count_' . $attributeCode => new Zend_Db_Expr('COUNT(DISTINCT e.entity_id)'),
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
     * @param string $attribute attribute code
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
     * @param Varien_Db_Select $select select
     * @param bool $resetLeftJoins reset left joins?
     * @return Varien_Db_Select
     */
    protected function _getSelectCountSql($select = null, $resetLeftJoins = true)
    {
        $this->_renderFilters();
        $countSelect = (is_null($select)) ? $this->_getClearSelect() : $this->_buildClearSelect($select);
        $countSelect->columns('COUNT(DISTINCT e.entity_id)');
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
     * @param Varien_Db_Select $select select
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
     * @param int|string $limit limit
     * @param int|string $offset offset
     * @return array
     */
    public function getAllIds($limit = null, $offset = null)
    {
        $idsSelect = $this->_getClearSelect();
        $idsSelect->columns('e.' . $this->getEntity()->getIdFieldName());
        $idsSelect->limit($limit, $offset);
        $idsSelect->resetJoinLeft();

        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }



    //TODO: addCountToCategories
    /** @var Mage_Catalog_Model_Resource_Product_Collection */


    /**
     * Add URL rewrites data to brand
     * If collection loadded - run processing else set flag
     *
     * @return Astrio_Brand_Model_Resource_Brand_Collection
     */
    public function addUrlRewrite()
    {
        $this->_addUrlRewrite = true;

        if ($this->isLoaded()) {
            $this->_addUrlRewrite();
        }

        return $this;
    }

    /**
     * Add URL rewrites to collection
     */
    protected function _addUrlRewrite()
    {
        $urlRewrites = null;
        if ($this->_cacheConf) {
            if (!($urlRewrites = Mage::app()->loadCache($this->_cacheConf['prefix'] . 'urlrewrite'))) {
                $urlRewrites = null;
            } else {
                $urlRewrites = unserialize($urlRewrites);
            }
        }

        if (!$urlRewrites) {
            $brandIds = array();
            foreach ($this->getItems() as $item) {
                $brandIds[] = $item->getEntityId();
            }
            if (!count($brandIds)) {
                return;
            }

            $select = $this->_factory->getBrandUrlRewriteHelper()
                ->getTableSelect($brandIds, Mage::app()->getStore()->getId());

            $urlRewrites = array();
            foreach ($this->getConnection()->fetchAll($select) as $row) {
                if (!isset($urlRewrites[$row['brand_id']]) && !empty($row['request_path'])) {
                    $urlRewrites[$row['brand_id']] = $row['request_path'];
                }
            }

            if ($this->_cacheConf) {
                Mage::app()->saveCache(
                    serialize($urlRewrites),
                    $this->_cacheConf['prefix'] . 'urlrewrite',
                    array_merge($this->_cacheConf['tags'], array(Astrio_Brand_Model_Brand_Url::CACHE_TAG)),
                    $this->_cacheLifetime
                );
            }
        }

        foreach ($this->getItems() as $item) {
            if (isset($urlRewrites[$item->getEntityId()])) {
                $item->setData('request_path', $urlRewrites[$item->getEntityId()]);
            } else {
                $item->setData('request_path', false);
            }
        }
    }

    /**
     * Retreive all ids
     *
     * @param boolean $resetCache reset cache?
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
     * @param array $value value
     * @return Astrio_Brand_Model_Resource_Brand_Collection
     */
    public function setAllIdsCache($value)
    {
        $this->_allIdsCache = $value;
        return $this;
    }

    /**
     * Add attribute to filter
     *
     * @param Mage_Eav_Model_Entity_Attribute_Abstract|string $attribute attribute
     * @param array $condition conditions
     * @param string $joinType join type
     * @return Astrio_Brand_Model_Resource_Brand_Collection
     */
    public function addAttributeToFilter($attribute, $condition = null, $joinType = 'inner')
    {
        $this->_allIdsCache = null;
        return parent::addAttributeToFilter($attribute, $condition, $joinType);
    }

    /**
     * Add attribute to sort order
     *
     * @param string $attribute attribute
     * @param string $dir dir
     * @return Astrio_Brand_Model_Resource_Brand_Collection
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
     * @return Astrio_Brand_Model_Resource_Brand_Collection
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
     * Sets Order field
     *
     * @param string $attribute attribute
     * @param string $dir dir
     * @return Astrio_Brand_Model_Resource_Brand_Collection
     */
    public function setOrder($attribute, $dir = 'desc')
    {
        parent::setOrder($attribute, $dir);
        return $this;
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
     * Adds brand collection attributes to select
     *
     * @return $this
     * @throws Mage_Core_Exception
     */
    public function addBrandCollectionAttributesToSelect()
    {
        $this->addAttributeToSelect(Mage::getSingleton('astrio_brand/config')->getBrandAttributes());
        return $this;
    }

    /**
     * Adds is active filter
     *
     * @param int $isActive is active?
     * @return $this
     */
    public function addIsActiveFilter($isActive = 1)
    {
        $this->addAttributeToFilter('is_active', $isActive);
        return $this;
    }

    /**
     * Adds include in menu filter
     *
     * @param int $includeInMenu include in menu?
     * @return $this
     */
    public function addIncludeInMenuFilter($includeInMenu = 1)
    {
        $this->addAttributeToFilter('include_in_menu', $includeInMenu);
        return $this;
    }

    /**
     * Adds is featured filter
     *
     * @param int $isFeatured is featured?
     * @return $this
     */
    public function addIsFeaturedFilter($isFeatured = 1)
    {
        $this->addAttributeToFilter('is_featured', $isFeatured);
        return $this;
    }

    /**
     * Sets order by position
     *
     * @param string $dir dir
     * @return $this
     */
    public function setOrderByPosition($dir = Varien_Data_Collection::SORT_ORDER_ASC)
    {
        $this->addAttributeToSort('position', $dir);
        return $this;
    }

    /**
     * Sets order by name
     *
     * @param string $dir dir
     * @return $this
     */
    public function setOrderByName($dir = Varien_Data_Collection::SORT_ORDER_ASC)
    {
        $this->addAttributeToSort('name', $dir);
        return $this;
    }
}