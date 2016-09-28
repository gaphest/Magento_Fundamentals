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
 * @see Mage_Catalog_Model_Resource_Url
 */ 
class Astrio_Brand_Model_Resource_Url extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * Stores configuration array
     *
     * @var array
     */
    protected $_stores;

    /**
     * Brand attribute properties cache
     *
     * @var array
     */
    protected $_brandAttributes           = array();

    /**
     * Product attribute properties cache
     *
     * @var array
     */
    protected $_productAttributes         = array();

    /**
     * Limit products for select
     *
     * @var int
     */
    protected $_productLimit              = 250;

    protected $_brandAttribute            = null;

    /**
     * Load core Url rewrite model
     *
     */
    protected function _construct()
    {
        $this->_init('core/url_rewrite', 'url_rewrite_id');
    }

    /**
     * Retrieve stores array or store model
     *
     * @param int $storeId store id
     * @return Mage_Core_Model_Store|array
     */
    public function getStores($storeId = null)
    {
        if ($this->_stores === null) {
            $this->_stores = Mage::app()->getStores();
        }
        if ($storeId && isset($this->_stores[$storeId])) {
            return $this->_stores[$storeId];
        }
        return $this->_stores;
    }

    /**
     * Retrieve Brand model singleton
     *
     * @return Astrio_Brand_Model_Brand
     */
    public function getBrandModel()
    {
        return Mage::getSingleton('astrio_brand/brand');
    }

    /**
     * Retrieve product model singleton
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProductModel()
    {
        return Mage::getSingleton('catalog/product');
    }

    /**
     * Retrieve rewrite by idPath
     *
     * @param string $idPath id path
     * @param int $storeId store id
     * @return Varien_Object|false
     */
    public function getRewriteByIdPath($idPath, $storeId)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->getMainTable())
            ->where('store_id = :store_id')
            ->where('id_path = :id_path');
        $bind = array(
            'store_id' => (int)$storeId,
            'id_path'  => $idPath
        );
        $row = $adapter->fetchRow($select, $bind);

        if (!$row) {
            return false;
        }
        $rewrite = new Varien_Object($row);
        $rewrite->setIdFieldName($this->getIdFieldName());

        return $rewrite;
    }

    /**
     * Retrieve rewrite by requestPath
     *
     * @param string $requestPath request path
     * @param int $storeId store id
     * @return Varien_Object|false
     */
    public function getRewriteByRequestPath($requestPath, $storeId)
    {
        $adapter = $this->_getWriteAdapter();
        $select = $adapter->select()
            ->from($this->getMainTable())
            ->where('store_id = :store_id')
            ->where('request_path = :request_path');
        $bind = array(
            'request_path'  => $requestPath,
            'store_id'      => (int)$storeId
        );
        $row = $adapter->fetchRow($select, $bind);

        if (!$row) {
            return false;
        }
        $rewrite = new Varien_Object($row);
        $rewrite->setIdFieldName($this->getIdFieldName());

        return $rewrite;
    }

    /**
     * Get last used increment part of rewrite request path
     *
     * @param string $prefix prefix
     * @param string $suffix suffex
     * @param int $storeId store id
     * @return int
     */
    public function getLastUsedRewriteRequestIncrement($prefix, $suffix, $storeId)
    {
        $adapter = $this->_getWriteAdapter();
        $requestPathField = new Zend_Db_Expr($adapter->quoteIdentifier('request_path'));
        //select increment part of request path and cast expression to integer
        $urlIncrementPartExpression = Mage::getResourceHelper('eav')
            ->getCastToIntExpression($adapter->getSubstringSql(
                $requestPathField,
                strlen($prefix) + 1,
                $adapter->getLengthSql($requestPathField) . ' - ' . strlen($prefix) . ' - ' . strlen($suffix)
            ));
        $select = $adapter->select()
            ->from($this->getMainTable(), new Zend_Db_Expr('MAX(' . $urlIncrementPartExpression . ')'))
            ->where('store_id = :store_id')
            ->where('request_path LIKE :request_path')
            ->where($adapter->prepareSqlCondition('request_path', array(
                'regexp' => '^' . preg_quote($prefix) . '[0-9]*' . preg_quote($suffix) . '$'
            )));
        $bind = array(
            'store_id'            => (int)$storeId,
            'request_path'        => $prefix . '%' . $suffix,
        );

        return (int)$adapter->fetchOne($select, $bind);
    }

    /**
     * Validate array of request paths. Return first not used path in case if validations passed
     *
     * @param array $paths paths
     * @param int $storeId store id
     * @return false | string
     */
    public function checkRequestPaths($paths, $storeId)
    {
        $adapter = $this->_getWriteAdapter();
        $select = $adapter->select()
            ->from($this->getMainTable(), 'request_path')
            ->where('store_id = :store_id')
            ->where('request_path IN (?)', $paths);
        $data = $adapter->fetchCol($select, array('store_id' => $storeId));
        $paths = array_diff($paths, $data);
        if (empty($paths)) {
            return false;
        }
        reset($paths);

        return current($paths);
    }

    /**
     * Prepare rewrites for condition
     *
     * @param int $storeId store id
     * @param int|array $brandIds brand ids
     * @param int|array $productIds products ids
     * @return array
     */
    public function prepareRewrites($storeId, $brandIds = null, $productIds = null)
    {
        $rewrites   = array();
        $adapter    = $this->_getWriteAdapter();
        $select     = $adapter->select()
            ->from($this->getMainTable())
            ->where('store_id = :store_id')
            ->where('is_system = ?', 1);
        $bind = array('store_id' => $storeId);
        if ($brandIds === null) {
            $select->where('brand_id IS NULL');
        } elseif ($brandIds) {
            $select->where('brand_id IN(?)', $brandIds);
        }

        if ($productIds === null) {
            $select->where('product_id IS NULL');
        } elseif ($productIds) {
            $select->where('product_id IN(?)', $productIds);
        }

        $rowSet = $adapter->fetchAll($select, $bind);

        foreach ($rowSet as $row) {
            $rewrite = new Varien_Object($row);
            $rewrite->setIdFieldName($this->getIdFieldName());
            $rewrites[$rewrite->getIdPath()] = $rewrite;
        }

        return $rewrites;
    }

    /**
     * Save rewrite URL
     *
     * @param array $rewriteData rewrite data
     * @param int|Varien_Object $rewrite rewrite
     * @return Astrio_Brand_Model_Resource_Url
     */
    public function saveRewrite($rewriteData, $rewrite)
    {
        $adapter = $this->_getWriteAdapter();
        try {
            $adapter->insertOnDuplicate($this->getMainTable(), $rewriteData);
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::throwException(Mage::helper('catalog')->__('An error occurred while saving the URL rewrite'));
        }

        if ($rewrite && $rewrite->getId()) {
            if ($rewriteData['request_path'] != $rewrite->getRequestPath()) {
                // Update existing rewrites history and avoid chain redirects
                $where = array('target_path = ?' => $rewrite->getRequestPath());
                if ($rewrite->getStoreId()) {
                    $where['store_id = ?'] = (int)$rewrite->getStoreId();
                }
                $adapter->update(
                    $this->getMainTable(),
                    array('target_path' => $rewriteData['request_path']),
                    $where
                );
            }
        }
        unset($rewriteData);

        return $this;
    }

    /**
     * Saves rewrite history
     *
     * @param array $rewriteData rewrite data
     * @return Astrio_Brand_Model_Resource_Url
     */
    public function saveRewriteHistory($rewriteData)
    {
        $rewriteData = new Varien_Object($rewriteData);
        // check if rewrite exists with save request_path
        $rewrite = $this->getRewriteByRequestPath($rewriteData->getRequestPath(), $rewriteData->getStoreId());
        if ($rewrite === false) {
            // create permanent redirect
            $this->_getWriteAdapter()->insert($this->getMainTable(), $rewriteData->getData());
        }

        return $this;
    }

    /**
     * Save brand attribute
     *
     * @param Varien_Object $brand brand
     * @param string $attributeCode attributes code
     * @return Astrio_Brand_Model_Resource_Url
     */
    public function saveBrandAttribute(Varien_Object $brand, $attributeCode)
    {
        $adapter = $this->_getWriteAdapter();
        if (!isset($this->_brandAttributes[$attributeCode])) {
            $attribute = $this->getBrandModel()->getResource()->getAttribute($attributeCode);

            $this->_brandAttributes[$attributeCode] = array(
                'entity_type_id' => $attribute->getEntityTypeId(),
                'attribute_id'   => $attribute->getId(),
                'table'          => $attribute->getBackend()->getTable(),
                'is_global'      => $attribute->getIsGlobal()
            );
            unset($attribute);
        }

        $attributeTable = $this->_brandAttributes[$attributeCode]['table'];

        $attributeData = array(
            'entity_type_id'    => $this->_brandAttributes[$attributeCode]['entity_type_id'],
            'attribute_id'      => $this->_brandAttributes[$attributeCode]['attribute_id'],
            'store_id'          => $brand->getStoreId(),
            'entity_id'         => $brand->getId(),
            'value'             => $brand->getData($attributeCode)
        );

        if ($this->_brandAttributes[$attributeCode]['is_global'] || $brand->getStoreId() == 0) {
            $attributeData['store_id'] = 0;
        }

        $select = $adapter->select()
            ->from($attributeTable)
            ->where('entity_type_id = ?', (int)$attributeData['entity_type_id'])
            ->where('attribute_id = ?', (int)$attributeData['attribute_id'])
            ->where('store_id = ?', (int)$attributeData['store_id'])
            ->where('entity_id = ?', (int)$attributeData['entity_id']);

        $row = $adapter->fetchRow($select);
        $whereCond = array('value_id = ?' => $row['value_id']);
        if ($row) {
            $adapter->update($attributeTable, $attributeData, $whereCond);
        } else {
            $adapter->insert($attributeTable, $attributeData);
        }

        if ($attributeData['store_id'] != 0) {
            $attributeData['store_id'] = 0;
            $select = $adapter->select()
                ->from($attributeTable)
                ->where('entity_type_id = ?', (int)$attributeData['entity_type_id'])
                ->where('attribute_id = ?', (int)$attributeData['attribute_id'])
                ->where('store_id = ?', (int)$attributeData['store_id'])
                ->where('entity_id = ?', (int)$attributeData['entity_id']);

            $row = $adapter->fetchRow($select);
            if ($row) {
                $whereCond = array('value_id = ?' => $row['value_id']);
                $adapter->update($attributeTable, $attributeData, $whereCond);
            } else {
                $adapter->insert($attributeTable, $attributeData);
            }
        }
        unset($attributeData);

        return $this;
    }

    /**
     * Retrieve brand attributes
     *
     * @param string $attributeCode attribute code
     * @param int|array $brandIds brand ids
     * @param int $storeId store id
     * @return array
     */
    protected function _getBrandAttribute($attributeCode, $brandIds, $storeId)
    {
        $adapter = $this->_getWriteAdapter();
        if (!isset($this->_brandAttributes[$attributeCode])) {
            $attribute = $this->getBrandModel()->getResource()->getAttribute($attributeCode);

            $this->_brandAttributes[$attributeCode] = array(
                'entity_type_id' => $attribute->getEntityTypeId(),
                'attribute_id'   => $attribute->getId(),
                'table'          => $attribute->getBackend()->getTable(),
                'is_global'      => $attribute->getIsGlobal(),
                'is_static'      => $attribute->isStatic()
            );
            unset($attribute);
        }

        if (!is_array($brandIds)) {
            $brandIds = array($brandIds);
        }

        $attributeTable = $this->_brandAttributes[$attributeCode]['table'];
        $select         = $adapter->select();
        $bind           = array();
        if ($this->_brandAttributes[$attributeCode]['is_static']) {
            $select
                ->from(
                    $this->getTable('astrio_brand/brand'),
                    array('value' => $attributeCode, 'entity_id' => 'entity_id')
                )
                ->where('entity_id IN(?)', $brandIds);
        } elseif ($this->_brandAttributes[$attributeCode]['is_global'] || $storeId == 0) {
            $select
                ->from($attributeTable, array('entity_id', 'value'))
                ->where('attribute_id = :attribute_id')
                ->where('store_id = ?', 0)
                ->where('entity_id IN(?)', $brandIds);
            $bind['attribute_id'] = $this->_brandAttributes[$attributeCode]['attribute_id'];
        } else {
            $valueExpr = $adapter->getCheckSql('t2.value_id > 0', 't2.value', 't1.value');
            $select
                ->from(
                    array('t1' => $attributeTable),
                    array('entity_id', 'value' => $valueExpr)
                )
                ->joinLeft(
                    array('t2' => $attributeTable),
                    't1.entity_id = t2.entity_id AND t1.attribute_id = t2.attribute_id AND t2.store_id = :store_id',
                    array()
                )
                ->where('t1.store_id = ?', 0)
                ->where('t1.attribute_id = :attribute_id')
                ->where('t1.entity_id IN(?)', $brandIds);

            $bind['attribute_id'] = $this->_brandAttributes[$attributeCode]['attribute_id'];
            $bind['store_id']     = $storeId;
        }

        $rowSet = $adapter->fetchAll($select, $bind);

        $attributes = array();
        foreach ($rowSet as $row) {
            $attributes[$row['entity_id']] = $row['value'];
        }
        unset($rowSet);
        foreach ($brandIds as $brandId) {
            if (!isset($attributes[$brandId])) {
                $attributes[$brandId] = null;
            }
        }

        return $attributes;
    }

    /**
     * Save product attribute
     *
     * @param Varien_Object $product product
     * @param string $attributeCode attribute code
     * @return Astrio_Brand_Model_Resource_Url
     */
    public function saveProductAttribute(Varien_Object $product, $attributeCode)
    {
        $adapter = $this->_getWriteAdapter();
        if (!isset($this->_productAttributes[$attributeCode])) {
            $attribute = $this->getProductModel()->getResource()->getAttribute($attributeCode);

            $this->_productAttributes[$attributeCode] = array(
                'entity_type_id' => $attribute->getEntityTypeId(),
                'attribute_id'   => $attribute->getId(),
                'table'          => $attribute->getBackend()->getTable(),
                'is_global'      => $attribute->getIsGlobal()
            );
            unset($attribute);
        }

        $attributeTable = $this->_productAttributes[$attributeCode]['table'];

        $attributeData = array(
            'entity_type_id'    => $this->_productAttributes[$attributeCode]['entity_type_id'],
            'attribute_id'      => $this->_productAttributes[$attributeCode]['attribute_id'],
            'store_id'          => $product->getStoreId(),
            'entity_id'         => $product->getId(),
            'value'             => $product->getData($attributeCode)
        );

        if ($this->_productAttributes[$attributeCode]['is_global'] || $product->getStoreId() == 0) {
            $attributeData['store_id'] = 0;
        }

        $select = $adapter->select()
            ->from($attributeTable)
            ->where('entity_type_id = ?', (int)$attributeData['entity_type_id'])
            ->where('attribute_id = ?', (int)$attributeData['attribute_id'])
            ->where('store_id = ?', (int)$attributeData['store_id'])
            ->where('entity_id = ?', (int)$attributeData['entity_id']);

        $row = $adapter->fetchRow($select);
        if ($row) {
            $whereCond = array('value_id = ?' => $row['value_id']);
            $adapter->update($attributeTable, $attributeData, $whereCond);
        } else {
            $adapter->insert($attributeTable, $attributeData);
        }

        if ($attributeData['store_id'] != 0) {
            $attributeData['store_id'] = 0;
            $select = $adapter->select()
                ->from($attributeTable)
                ->where('entity_type_id = ?', (int)$attributeData['entity_type_id'])
                ->where('attribute_id = ?', (int)$attributeData['attribute_id'])
                ->where('store_id = ?', (int)$attributeData['store_id'])
                ->where('entity_id = ?', (int)$attributeData['entity_id']);

            $row = $adapter->fetchRow($select);
            if ($row) {
                $whereCond = array('value_id = ?' => $row['value_id']);
                $adapter->update($attributeTable, $attributeData, $whereCond);
            } else {
                $adapter->insert($attributeTable, $attributeData);
            }
        }
        unset($attributeData);

        return $this;
    }

    /**
     * Retrieve product attribute
     *
     * @param string $attributeCode attribute code
     * @param int|array $productIds product ids
     * @param string $storeId store id
     * @return array
     */
    protected function _getProductAttribute($attributeCode, $productIds, $storeId)
    {
        $adapter = $this->_getReadAdapter();
        if (!isset($this->_productAttributes[$attributeCode])) {
            $attribute = $this->getProductModel()->getResource()->getAttribute($attributeCode);

            $this->_productAttributes[$attributeCode] = array(
                'entity_type_id' => $attribute->getEntityTypeId(),
                'attribute_id'   => $attribute->getId(),
                'table'          => $attribute->getBackend()->getTable(),
                'is_global'      => $attribute->getIsGlobal()
            );
            unset($attribute);
        }

        if (!is_array($productIds)) {
            $productIds = array($productIds);
        }
        $bind = array('attribute_id' => $this->_productAttributes[$attributeCode]['attribute_id']);
        $select = $adapter->select();
        $attributeTable = $this->_productAttributes[$attributeCode]['table'];
        if ($this->_productAttributes[$attributeCode]['is_global'] || $storeId == 0) {
            $select
                ->from($attributeTable, array('entity_id', 'value'))
                ->where('attribute_id = :attribute_id')
                ->where('store_id = ?', 0)
                ->where('entity_id IN(?)', $productIds);
        } else {
            $valueExpr = $adapter->getCheckSql('t2.value_id > 0', 't2.value', 't1.value');
            $select
                ->from(
                    array('t1' => $attributeTable),
                    array('entity_id', 'value' => $valueExpr)
                )
                ->joinLeft(
                    array('t2' => $attributeTable),
                    't1.entity_id = t2.entity_id AND t1.attribute_id = t2.attribute_id AND t2.store_id=:store_id',
                    array()
                )
                ->where('t1.store_id = ?', 0)
                ->where('t1.attribute_id = :attribute_id')
                ->where('t1.entity_id IN(?)', $productIds);
            $bind['store_id'] = $storeId;
        }

        $rowSet = $adapter->fetchAll($select, $bind);

        $attributes = array();
        foreach ($rowSet as $row) {
            $attributes[$row['entity_id']] = $row['value'];
        }
        unset($rowSet);
        foreach ($productIds as $productId) {
            if (!isset($attributes[$productId])) {
                $attributes[$productId] = null;
            }
        }

        return $attributes;
    }

    /**
     * Retrieve brands objects
     * Either $brandIds or $path (with ending slash) must be specified
     *
     * @param int|array $brandIds brand ids
     * @param int $storeId store id
     * @param string $path path
     * @return array
     */
    protected function _getBrands($brandIds, $storeId = null, $path = null)
    {
        $isActiveAttribute = Mage::getSingleton('eav/config')
            ->getAttribute(Astrio_Brand_Model_Brand::ENTITY, 'is_active');
        $brands        = array();
        $adapter           = $this->_getReadAdapter();

        if (!is_array($brandIds)) {
            $brandIds = array($brandIds);
        }
        $isActiveExpr = $adapter->getCheckSql('c.value_id > 0', 'c.value', 'd.value'); // TODO : CHECK THIS
        $select = $adapter->select()
            ->from(array('main_table' => $this->getTable('astrio_brand/brand')), array(
                'main_table.entity_id',
                'is_active' => $isActiveExpr,
                ));

        // Prepare variables for checking whether brands belong to store
        if ($path === null) {
            $select->where('main_table.entity_id IN(?)', $brandIds);
        }
        $table = $this->getTable(array('astrio_brand/brand', 'int'));
        $select->joinLeft(array('d' => $table),
            'd.attribute_id = :attribute_id AND d.store_id = 0 AND d.entity_id = main_table.entity_id',
            array()
        )
            ->joinLeft(array('c' => $table),
                'c.attribute_id = :attribute_id AND c.store_id = :store_id AND c.entity_id = main_table.entity_id',
                array()
            );

        $bind = array(
            'attribute_id' => (int)$isActiveAttribute->getId(),
            'store_id'     => (int)$storeId
        );

        if ($storeId) {
            $select->join(
                array('brand_website' => $this->getTable('astrio_brand/brand_website')),
                $adapter->quoteInto('brand_website.brand_id = main_table.entity_id AND brand_website.website_id = ?', $this->getStores($storeId)->getWebsiteId()),
                array()
            );
        }

        $rowSet = $adapter->fetchAll($select, $bind);
        foreach ($rowSet as $row) {
            $brand = new Varien_Object($row);
            $brand->setIdFieldName('entity_id');
            $brand->setStoreId($storeId);

            $brands[$brand->getId()] = $brand;
        }
        unset($rowSet);

        if ($storeId !== null && $brands) {
            foreach (array('name', 'url_key') as $attributeCode) {
                $attributes = $this->_getBrandAttribute($attributeCode, array_keys($brands),
                    $brand->getStoreId());
                foreach ($attributes as $brandId => $attributeValue) {
                    $brands[$brandId]->setData($attributeCode, $attributeValue);
                }
            }
        }

        return $brands;
    }

    /**
     * Retrieve brand data object
     *
     * @param int $brandId brand id
     * @param int $storeId store id
     * @return Varien_Object
     */
    public function getBrand($brandId, $storeId)
    {
        if (!$brandId || !$storeId) {
            return false;
        }

        $brands = $this->_getBrands($brandId, $storeId);
        if (isset($brands[$brandId])) {
            return $brands[$brandId];
        }
        return false;
    }

    /**
     * Retrieve brands data objects by their ids. Return only brands that belong to specified store.
     *
     * @param int|array $brandIds brand ids
     * @param int $storeId store id
     * @return array
     */
    public function getBrands($brandIds, $storeId)
    {
        if (!$brandIds || !$storeId) {
            return false;
        }

        return $this->_getBrands($brandIds, $storeId);
    }

    /**
     * Retrieves all brand ids
     * Actually this routine can be used to get children ids of any brand, not only root.
     * But as far as result is cached in memory, it's not recommended to do so.
     *
     * @param int $storeId store id
     * @return array
     */
    public function getStoreBrandIds($storeId)
    {
        $store = $this->getStores($storeId);
        // Select all descedant brand ids
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from(array('e' => $this->getTable('astrio_brand/brand')), array('entity_id'))
            ->join(
                array('brand_website' => $this->getTable('astrio_brand/brand_website')),
                $adapter->quoteInto('brand_website.brand_id = e.entity_id AND brand_website.website_id = ?', $store->getWebsiteId()),
                array()
            );

        $brandIds = array();
        $rowSet = $adapter->fetchAll($select);
        foreach ($rowSet as $row) {
            $brandIds[$row['entity_id']] = $row['entity_id'];
        }
        return $brandIds;
    }

    /**
     * Retrieve product ids by brand
     *
     * @param Varien_Object|int $brand brand
     * @return array
     */
    public function getProductIdsByBrand($brand)
    {
        if ($brand instanceof Varien_Object) {
            $brandId = $brand->getId();
        } else {
            $brandId = $brand;
        }
        $adapter = $this->_getReadAdapter();
        $brandAttribute = $this->getBrandAttribute();
        $select = $adapter->select()
            ->from($brandAttribute->getBackendTable(), array('product_id' => 'entity_id'))
            ->where('attribute_id = :attribute_id')
            ->where('value = :brand_id')
            ->order('product_id');
        ;
        $bind = array(
            'brand_id' => $brandId,
            'attribute_id' => $brandAttribute->getId(),
        );

        return $adapter->fetchCol($select, $bind);
    }

    /**
     * Retrieve Product data objects
     *
     * @param int|array $productIds product ids
     * @param int $storeId store id
     * @param int $entityId entity id
     * @param int $lastEntityId last entity id
     * @return array
     */
    protected function _getProducts($productIds, $storeId, $entityId, &$lastEntityId)
    {
        $products   = array();
        $websiteId  = Mage::app()->getStore($storeId)->getWebsiteId();
        $adapter    = $this->_getReadAdapter();
        if ($productIds !== null) {
            if (!is_array($productIds)) {
                $productIds = array($productIds);
            }
        }
        $bind = array(
            'website_id' => (int)$websiteId,
            'entity_id'  => (int)$entityId,
        );
        $select = $adapter->select()
            ->useStraightJoin(true)
            ->from(array('e' => $this->getTable('catalog/product')), array('entity_id'))
            ->join(
                array('w' => $this->getTable('catalog/product_website')),
                'e.entity_id = w.product_id AND w.website_id = :website_id',
                array()
            )
            ->where('e.entity_id > :entity_id')
            ->order('e.entity_id')
            ->limit($this->_productLimit);
        if ($productIds !== null) {
            $select->where('e.entity_id IN(?)', $productIds);
        }

        $rowSet = $adapter->fetchAll($select, $bind);
        foreach ($rowSet as $row) {
            $product = new Varien_Object($row);
            $product->setIdFieldName('entity_id');
            $product->setDataUsingMethod(Astrio_Brand_Model_Brand::ATTRIBUTE_CODE, null);
            $product->setStoreId($storeId);
            $products[$product->getId()] = $product;
            $lastEntityId = $product->getId();
        }

        unset($rowSet);

        if ($products) {
            $brandAttribute = $this->getBrandAttribute();
            $select = $adapter->select()
                ->from($brandAttribute->getBackendTable(), array('product_id' => 'entity_id', 'brand_id' => 'value'))
                ->where('attribute_id = ?', $brandAttribute->getId())
                ->where('entity_id IN(?)', array_keys($products));

            $brands = $adapter->fetchAll($select);
            foreach ($brands as $brand) {
                $productId = $brand['product_id'];
                $products[$productId]->setDataUsingMethod(Astrio_Brand_Model_Brand::ATTRIBUTE_CODE, $brand['brand_id']);
            }

            foreach (array('name', 'url_key', 'url_path') as $attributeCode) {
                $attributes = $this->_getProductAttribute($attributeCode, array_keys($products), $storeId);
                foreach ($attributes as $productId => $attributeValue) {
                    $products[$productId]->setData($attributeCode, $attributeValue);
                }
            }
        }

        return $products;
    }

    /**
     * Retrieve Product data object
     *
     * @param int $productId product id
     * @param int $storeId store id
     * @return Varien_Object
     */
    public function getProduct($productId, $storeId)
    {
        $entityId = 0;
        $products = $this->_getProducts($productId, $storeId, 0, $entityId);
        if (isset($products[$productId])) {
            return $products[$productId];
        }
        return false;
    }

    /**
     * Retrieve Product data objects for store
     *
     * @param int $storeId store id
     * @param int $lastEntityId last entity id
     * @return array
     */
    public function getProductsByStore($storeId, &$lastEntityId)
    {
        return $this->_getProducts(null, $storeId, $lastEntityId, $lastEntityId);
    }

    /**
     * Retrieve Product data objects in brand
     *
     * @param Varien_Object $brand brand
     * @param int $lastEntityId last entity id
     * @return array
     */
    public function getProductsByBrand(Varien_Object $brand, &$lastEntityId)
    {
        $productIds = $this->getProductIdsByBrand($brand);
        if (!$productIds) {
            return array();
        }
        return $this->_getProducts($productIds, $brand->getStoreId(), $lastEntityId, $lastEntityId);
    }

    /**
     * Find and remove unused products rewrites - a case when products were moved away from the brand
     * (either to other brand or deleted), so rewrite "brand_id-product_id" is invalid
     *
     * @param int $storeId store id
     * @return Astrio_Brand_Model_Resource_Url
     */
    public function clearBrandProduct($storeId)
    {
        $brandAttribute = $this->getBrandAttribute();
        $adapter = $this->_getWriteAdapter();
        $select = $adapter->select()
            ->from(array('tur' => $this->getMainTable()), $this->getIdFieldName())
            ->joinLeft(
                array('tcp' => $brandAttribute->getBackendTable()),
                $adapter->quoteInto('tur.brand_id = tcp.value AND tur.product_id = tcp.entity_id AND tcp.attribute_id = ?', $brandAttribute->getId()),
                array()
            )
            ->where('tur.store_id = :store_id')
            ->where('tur.brand_id IS NOT NULL')
            ->where('tur.product_id IS NOT NULL')
            ->where('tcp.value IS NULL');
        $rewriteIds = $adapter->fetchCol($select, array('store_id' => $storeId));
        if ($rewriteIds) {
            $where = array($this->getIdFieldName() . ' IN(?)' => $rewriteIds);
            $adapter->delete($this->getMainTable(), $where);
        }

        return $this;
    }

    /**
     * Remove unused rewrites for product - called after we created all needed rewrites for product and know the brands
     * where the product is contained ($excludeBrandIds), so we can remove all invalid product rewrites that have other brand ids
     *
     * Notice: this routine is not identical to clearBrandProduct(), because after checking all brands this one removes rewrites
     * for product still contained within brands.
     *
     * @param int $productId Product entity Id
     * @param int $storeId Store Id for rewrites
     * @param array $excludeBrandIds Array of brand Ids that should be skipped
     * @return Astrio_Brand_Model_Resource_Url
     */
    public function clearProductRewrites($productId, $storeId, $excludeBrandIds = array())
    {
        $where = array(
            'product_id = ?' => $productId,
            'store_id = ?' => $storeId,
            'category_id IS NULL'
        );

        if (!empty($excludeBrandIds)) {
            $where['brand_id NOT IN (?)'] = $excludeBrandIds;
            // If there's at least one brand to skip, also skip root brand, because product belongs to website
            $where[] = 'brand_id IS NOT NULL';
        }

        $this->_getWriteAdapter()->delete($this->getMainTable(), $where);

        return $this;
    }

    /**
     * Finds and deletes all old brand and brand/product rewrites for store
     * left from the times when brands/products belonged to store
     *
     * @param int $storeId store id
     * @return Astrio_Brand_Model_Resource_Url
     */
    public function clearStoreBrandsInvalidRewrites($storeId)
    {
        $brandIds = $this->getStoreBrandIds($storeId);

        // Remove all store catalog rewrites that are for some brand or product not within store brands
        $where   = array(
            'store_id = ?' => $storeId,
            'brand_id IS NOT NULL', // For sure check that it's a catalog rewrite
            'brand_id NOT IN (?)' => $brandIds
        );

        $this->_getWriteAdapter()->delete($this->getMainTable(), $where);

        return $this;
    }

    /**
     * Finds and deletes product rewrites (that are not assigned to any brand) for store
     * left from the times when product was assigned to this store's website and now is not assigned
     *
     * Notice: this routine is different from clearProductRewrites() and clearBrandProduct() because
     * it handles direct rewrites to product without defined brand (brand_id IS NULL) whilst that routines
     * handle only product rewrites within brands
     *
     * @param int $storeId store id
     * @param int|array|null $productId product id
     * @return Astrio_Brand_Model_Resource_Url
     */
    public function clearStoreProductsInvalidRewrites($storeId, $productId = null)
    {
        $store   = $this->getStores($storeId);
        $adapter = $this->_getReadAdapter();
        $bind    = array(
            'website_id' => (int)$store->getWebsiteId(),
            'store_id'   => (int)$storeId
        );
        $select = $adapter->select()
            ->from(array('rewrite' => $this->getMainTable()), $this->getIdFieldName())
            ->joinLeft(
                array('website' => $this->getTable('catalog/product_website')),
                'rewrite.product_id = website.product_id AND website.website_id = :website_id',
                array()
            )->where('rewrite.store_id = :store_id')
            ->where('rewrite.brand_id IS NULL');
        if ($productId) {
            $select->where('rewrite.product_id IN (?)', $productId);
        } else {
            $select->where('rewrite.product_id IS NOT NULL');
        }
        $select->where('website.website_id IS NULL');

        $rewriteIds = $adapter->fetchCol($select, $bind);
        if ($rewriteIds) {
            $where = array($this->getIdFieldName() . ' IN(?)' => $rewriteIds);
            $this->_getWriteAdapter()->delete($this->getMainTable(), $where);
        }

        return $this;
    }

    /**
     * Finds and deletes old rewrites for store
     * a) brand rewrites left from the times when store had some other root brand
     * b) product rewrites left from products that once belonged to this site, but then deleted or just removed from website
     *
     * @param int $storeId store id
     * @return Astrio_Brand_Model_Resource_Url
     */
    public function clearStoreInvalidRewrites($storeId)
    {
        $this->clearStoreBrandsInvalidRewrites($storeId);
        $this->clearStoreProductsInvalidRewrites($storeId);
        return $this;
    }

    /**
     * Delete rewrites for associated to brand products
     *
     * @param int $brandId brand id
     * @param array $productIds product ids
     * @return Astrio_Brand_Model_Resource_Url
     */
    public function deleteBrandProductRewrites($brandId, $productIds)
    {
        $this->deleteBrandProductStoreRewrites($brandId, $productIds);
        return $this;
    }

    /**
     * Delete URL rewrites for brand products of specific store
     *
     * @param int $brandId brand id
     * @param array|int|null $productIds product ids
     * @param null|int $storeId store id
     * @return Astrio_Brand_Model_Resource_Url
     */
    public function deleteBrandProductStoreRewrites($brandId, $productIds = null, $storeId = null)
    {
        // Notice that we don't include brand_id = NULL in case of root brand,
        // because product removed from all brands but assigned to store's website is still
        // assumed to be in root cat. Unassigned products must be removed by other routine.
        $condition = array('brand_id = ?' => $brandId);
        if (empty($productIds)) {
            $condition[] = 'product_id IS NOT NULL';
        } else {
            $condition['product_id IN (?)'] = $productIds;
        }

        if ($storeId !== null) {
            $condition['store_id IN(?)'] = $storeId;
        }

        $this->_getWriteAdapter()->delete($this->getMainTable(), $condition);
        return $this;
    }

    /**
     * Find and return final id path by request path
     * Needed for permanent redirect old URLs.
     *
     * @param string $requestPath request path
     * @param int $storeId store id
     * @param array $_checkedPaths internal varible to prevent infinite loops.
     * @return string | bool
     */
    public function findFinalTargetPath($requestPath, $storeId, &$_checkedPaths = array())
    {
        if (in_array($requestPath, $_checkedPaths)) {
            return false;
        }

        $_checkedPaths[] = $requestPath;

        $select = $this->_getWriteAdapter()->select()
            ->from($this->getMainTable(), array('target_path', 'id_path'))
            ->where('store_id = ?', $storeId)
            ->where('request_path = ?', $requestPath);

        if ($row = $this->_getWriteAdapter()->fetchRow($select)) {
            $idPath = $this->findFinalTargetPath($row['target_path'], $storeId, $_checkedPaths);
            if (!$idPath) {
                return $row['id_path'];
            } else {
                return $idPath;
            }
        }

        return false;
    }

    /**
     * Delete rewrite path record from the database.
     *
     * @param string $requestPath request path
     * @param int $storeId store id
     * @return void
     */
    public function deleteRewrite($requestPath, $storeId)
    {
        $this->deleteRewriteRecord($requestPath, $storeId);
    }

    /**
     * Delete rewrite path record from the database with RP checking.
     *
     * @param string $requestPath request path
     * @param int $storeId store id
     * @param bool $rp whether check rewrite option to be "Redirect = Permanent"
     * @return void
     */
    public function deleteRewriteRecord($requestPath, $storeId, $rp = false)
    {
        $conditions = array(
            'store_id = ?' => $storeId,
            'request_path = ?' => $requestPath,
        );
        if ($rp) {
            $conditions['options = ?'] = 'RP';
        }
        $this->_getWriteAdapter()->delete($this->getMainTable(), $conditions);
    }

    /**
     * Gets brand attribute
     *
     * @return Mage_Eav_Model_Entity_Attribute_Abstract
     */
    public function getBrandAttribute()
    {
        if ($this->_brandAttribute === null) {
            /**
             * @var $eavConfig Mage_Eav_Model_Config
             */
            $eavConfig = Mage::getSingleton('eav/config');
            $this->_brandAttribute = $eavConfig->getAttribute(Mage_Catalog_Model_Product::ENTITY, Astrio_Brand_Model_Brand::ATTRIBUTE_CODE);
        }

        return $this->_brandAttribute;
    }
}
