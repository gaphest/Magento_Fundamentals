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
class Astrio_SpecialProducts_Model_Resource_Indexer_Set
{

    // Position column name
    const POSITION_COL_NAME = 'position';

    /**
     * @var Mage_Core_Model_Resource
     */
    protected $_coreResource;

    /**
     * @var string
     */
    protected $_setTable;

    /**
     * @var string
     */
    protected $_setProductsTable;

    /**
     * @var string
     */
    protected $_productWebsiteTable;

    /**
     * @var string
     */
    protected $_productTable;

    /**
     * @var string
     */
    protected $_categoryTable;

    /**
     * @var string
     */
    protected $_categoryProductTable;

    /**
     * @var string
     */
    protected $_productIndexPriceTable;

    /**
     * @var string
     */
    protected $_reportsEventTable;

    /**
     * @var string
     */
    protected $_reviewTable;

    /**
     * @var string
     */
    protected $_reviewDetailTable;

    /**
     * @var Varien_Db_Adapter_Interface
     */
    protected $_readConnection;

    /**
     * @var Varien_Db_Adapter_Interface
     */
    protected $_writeConnection;

    /**
     * @var array
     */
    protected $_visibilityIds;

    /**
     * @var string
     */
    protected $_setRowNumSQL;

    /**
     * @var Zend_Db_Expr
     */
    protected $_positionExpr;

    /**
     * @var array
     */
    protected $_categoryIds = array();

    /**
     * @var null|array
     */
    protected $_customerGroupIds = null;

    /**
     * @var null|int
     */
    protected $_productViewEventId = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        /**
         * @var $coreResource Mage_Core_Model_Resource
         */
        $coreResource = Mage::getSingleton('core/resource');
        $this->_coreResource            = $coreResource;
        $this->_setTable                = $coreResource->getTableName('astrio_specialproducts/set');
        $this->_setProductsTable        = $coreResource->getTableName('astrio_specialproducts/set_product');
        $this->_productWebsiteTable     = $coreResource->getTableName('catalog/product_website');
        $this->_productTable            = $coreResource->getTableName('catalog/product');
        $this->_categoryTable           = $coreResource->getTableName('catalog/category');
        $this->_categoryProductTable    = $coreResource->getTableName('catalog/category_product');

        $this->_stockStatusTable        = $coreResource->getTableName('cataloginventory/stock_status');

        $this->_catalogRuleProductTable = $coreResource->getTableName('catalogrule/rule_product');

        $this->_productIndexPriceTable  = $coreResource->getTableName('catalog/product_index_price');

        $this->_reportsEventTable       = $coreResource->getTableName('reports/event');
        $this->_reviewTable             = $coreResource->getTableName('review/review');
        $this->_reviewDetailTable       = $coreResource->getTableName('review/review_detail');

        $this->_readConnection          = $coreResource->getConnection('core_read');
        $this->_writeConnection         = $coreResource->getConnection('core_write');

        $this->_setRowNumSQL            = "SET @rownum=0";
        $this->_positionExpr            = new Zend_Db_Expr("@rownum:=@rownum+1");

        /**
         * @var $visibility Mage_Catalog_Model_Product_Visibility
         */
        $visibility = Mage::getSingleton('catalog/product_visibility');
        $this->_visibilityIds = $visibility->getVisibleInCatalogIds();
    }

    /**
     * Begin transaction
     *
     * @return $this
     */
    public function beginTransaction()
    {
        $this->_writeConnection->beginTransaction();
        return $this;
    }

    /**
     * Commit
     *
     * @return $this
     */
    public function commit()
    {
        $this->_writeConnection->commit();
        return $this;
    }

    /**
     * Rollback
     *
     * @return $this
     */
    public function rollBack()
    {
        $this->_writeConnection->rollBack();
        return $this;
    }

    /**
     * Reindex
     *
     * @param  array $setIds set ides
     * @return $this
     * @throws Exception
     */
    public function reindex(array $setIds = array())
    {
        foreach ($setIds as $setId) {
            /**
             * @var $set Astrio_SpecialProducts_Model_Set
             */
            $set = Mage::getModel('astrio_specialproducts/set')->load($setId);
            $this->reindexSet($set);
        }

        return $this;
    }

    /**
     * Reindex all
     *
     * @return $this
     */
    public function reindexAll()
    {
        /**
         * @var $collection Astrio_SpecialProducts_Model_Resource_Set_Collection
         */
        $collection = Mage::getResourceModel('astrio_specialproducts/set_collection');
        $collection->addIsAutoFilter();

        $this->reindex($collection->getAllIds());

        return $this;
    }

    /**
     * Reindex set
     *
     * @param  Astrio_SpecialProducts_Model_Set $set set
     * @return $this
     * @throws Exception
     */
    public function reindexSet(Astrio_SpecialProducts_Model_Set $set)
    {
        if (!$set->getId() || !$set->getIsAuto() || !$set->getAutoType()) {
            return $this;
        }

        $this->beginTransaction();
        try {
            $this->_writeConnection->delete($this->_setProductsTable, $this->_writeConnection->quoteInto('set_id = ?', (int) $set->getId()));

            switch ($set->getAutoType())
            {
                case Astrio_SpecialProducts_Model_Set::AUTO_TYPE_CATALOG_RULE:
                    $this->_processSetAutoTypeCatalogRule($set);
                    break;
                case Astrio_SpecialProducts_Model_Set::AUTO_TYPE_RECENTLY_ADDED:
                    $this->_processSetAutoTypeRecentlyAdded($set);
                    break;
                case Astrio_SpecialProducts_Model_Set::AUTO_TYPE_NEW:
                    $this->_processSetAutoTypeNew($set);
                    break;
                case Astrio_SpecialProducts_Model_Set::AUTO_TYPE_ON_SALE:
                    $this->_processSetAutoTypeOnSale($set);
                    break;
                case Astrio_SpecialProducts_Model_Set::AUTO_TYPE_BESTSELLER:
                    $this->_processSetAutoTypeBestseller($set);
                    break;
                case Astrio_SpecialProducts_Model_Set::AUTO_TYPE_MOST_VIEWED:
                    $this->_processSetAutoTypeMostViewed($set);
                    break;
                case Astrio_SpecialProducts_Model_Set::AUTO_TYPE_MOST_REVIEWED:
                    $this->_processSetAutoTypeMostReviewed($set);
                    break;
                default:
                    Mage::dispatchEvent('astrio_specialproducts_set_custom_type_reindex', array('set' => $set));
            }

            $this->commit();
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }

        return $this;
    }

    /**
     * Get category path
     *
     * @param  int $categoryId category id
     * @return string
     */
    protected function _getCategoryPath($categoryId)
    {
        $select = $this->_readConnection->select();
        $select->from($this->_categoryTable, 'path')
            ->where('entity_id = ?', $categoryId);

        return $this->_readConnection->fetchOne($select);
    }

    /**
     * Get category ids
     *
     * @param  int $categoryId category ids
     * @return mixed
     */
    protected function _getCategoryIds($categoryId)
    {
        if (!isset($this->_categoryIds[$categoryId])) {
            $result = array($categoryId);

            $path = $this->_getCategoryPath($categoryId);
            if ($path) {
                $select = $this->_readConnection->select();
                $select->from($this->_categoryTable, 'entity_id')
                    ->where('path LIKE "'.$path.'/%"', $categoryId);

                $stmt = $this->_readConnection->query($select);
                while ($row = $stmt->fetch()) {
                    $result[] = (int) $row['entity_id'];
                }
            }

            $this->_categoryIds[$categoryId] = $result;
        }

        return $this->_categoryIds[$categoryId];
    }

    /**
     * get prepared product collection
     *
     * @param  Astrio_SpecialProducts_Model_Set $set set
     * @param  int $storeId store id
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getProductCollection(Astrio_SpecialProducts_Model_Set $set, $storeId)
    {
        $websiteId = (int) Mage::app()->getStore($storeId)->getWebsiteId();

        /**
         * @var $collection Mage_Catalog_Model_Resource_Product_Collection
         */
        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->setStoreId($storeId);

        $productTableAlias = Mage_Catalog_Model_Resource_Product_Collection::MAIN_TABLE_ALIAS;
        $productIdColumn = $collection->getResource()->getIdFieldName();

        $select = $collection->getSelect();

        /**
         * filter by website
         */
        $productWebsiteTableAlias = 'product_website';
        $select
            ->joinInner(
                array($productWebsiteTableAlias => $this->_productWebsiteTable),
                implode(' AND ', array(
                    "{$productWebsiteTableAlias}.product_id = {$productTableAlias}.{$productIdColumn}",
                    $this->_readConnection->quoteInto("{$productWebsiteTableAlias}.website_id = ?", $websiteId),
                )),
                array()
            );

        /**
         * filter by category (and all inner categories)
         */
        if ($categoryId = $set->getFilterByCategoryId()) {
            $categoryIds = $this->_getCategoryIds($categoryId);
            $categoryProductTableAlias = 'category_product';
            $select
                ->joinInner(
                    array($categoryProductTableAlias => $this->_categoryProductTable),
                    implode(' AND ', array(
                        "{$categoryProductTableAlias}.product_id = {$productTableAlias}.{$productIdColumn}",
                        $this->_readConnection->quoteInto("{$categoryProductTableAlias}.category_id IN(?)", $categoryIds),
                    )),
                    array()
                );
        }

        /**
         * only enabled products
         */
        $collection->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);

        /**
         * only visible products
         */
        $collection->addAttributeToFilter('visibility', array('in' => $this->_visibilityIds));

        $select->group("{$productTableAlias}.{$productIdColumn}");

        Mage::dispatchEvent('specialproducts_set_product_collection_select_prepare', array(
            'set'           => $set,
            'store_id'      => $storeId,
            'resource'      => $this,
            'collection'    => $collection,
            'select'        => $select,
        ));

        return $collection;
    }

    /**
     * reset part "columns" and part "order" and select only product_id from products collection
     *
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection collection
     * @return $this
     */
    public function resetProductCollectionColumnsAndOrder(Mage_Catalog_Model_Resource_Product_Collection $collection)
    {
        $collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->reset(Zend_Db_Select::ORDER)
            ->columns(array(
                'product_id' => Mage_Catalog_Model_Resource_Product_Collection::MAIN_TABLE_ALIAS . '.' . $collection->getResource()->getIdFieldName(),
            ));

        return $this;
    }

    /**
     * Insert set products using select
     *
     * @param  Varien_Db_Select                 $select          select
     * @param  Astrio_SpecialProducts_Model_Set $set             set
     * @param  int                              $storeId         store id
     * @param  int|null                         $customerGroupId customer group id
     * @return $this
     */
    public function insertSetProductsUsingSelect(Varien_Db_Select $select, Astrio_SpecialProducts_Model_Set $set, $storeId, $customerGroupId)
    {
        if ($limit = $set->getProductsLimit()) {
            $select->limit($limit);
        }

        $columns = array(
            'set_id'            => new Zend_Db_Expr($set->getId()),
            'store_id'          => new Zend_Db_Expr($storeId),
            'customer_group_id' => new Zend_Db_Expr($customerGroupId === null ? 'NULL' : $customerGroupId),
            'product_id'        => 'product_id',
            'position'          => $this->_positionExpr,
        );

        $resultSelect = $this->_readConnection->select();
        $resultSelect->from(array('select' => $select), $columns);

        $this->_writeConnection->query($this->_setRowNumSQL);
        $insertSQL = $this->_writeConnection->insertFromSelect($resultSelect, $this->_setProductsTable, array_keys($columns), Varien_Db_Adapter_Interface::INSERT_ON_DUPLICATE);
        $this->_writeConnection->query($insertSQL);

        return $this;
    }

    /**
     * get all customer group ids
     *
     * @return array
     */
    public function getCustomerGroupIds()
    {
        if ($this->_customerGroupIds === null) {
            /**
             * @var $customerGroups Mage_Customer_Model_Resource_Group_Collection
             */
            $customerGroups = Mage::getResourceModel('customer/group_collection');
            $this->_customerGroupIds = $customerGroups->getAllIds();
        }

        return $this->_customerGroupIds;
    }

    /**
     * get product view event id (for most viewed)
     *
     * @return int
     */
    protected function _getProductViewEventId()
    {
        if ($this->_productViewEventId === null) {
            $select = $this->_readConnection->select();
            $select->from($this->_coreResource->getTableName('reports/event_type'), array('event_type_id'))
                ->where('event_name = ?', 'catalog_product_view');

            $this->_productViewEventId = (int) $this->_readConnection->fetchOne($select);
        }

        return $this->_productViewEventId;
    }

    /**
     * fill special products set by catalog rule
     *
     * @param  Astrio_SpecialProducts_Model_Set $set set
     * @return $this
     */
    protected function _processSetAutoTypeCatalogRule(Astrio_SpecialProducts_Model_Set $set)
    {
        $catalogRuleId = $set->getCatalogRuleId();
        if (!$catalogRuleId) {
            return $this;
        }

        /**
         * @var $catalogRule Mage_CatalogRule_Model_Rule
         */
        $catalogRule = Mage::getModel('catalogrule/rule')->load($catalogRuleId);
        if (!$catalogRule->getId() || !$catalogRule->getIsActive()) {
            return $this;
        }

        $websiteIds = $catalogRule->getWebsiteIds();
        $customerGroupIds = $catalogRule->getCustomerGroupIds();

        if (!$websiteIds) {
            return $this;
        }

        if (!$customerGroupIds) {
            return $this;
        }

        $catalogRule->getResource()->updateRuleProductData($catalogRule);

        $storeIds = $set->getStoreIds();
        foreach ($storeIds as $storeId) {
            $storeId = (int) $storeId;
            $websiteId = (int) Mage::app()->getStore($storeId)->getWebsiteId();
            if (!in_array($websiteId, $websiteIds)) {
                continue;
            }

            $collection = $this->getProductCollection($set, $storeId);
            $this->resetProductCollectionColumnsAndOrder($collection);
            $collectionSelect = $collection->getSelect();

            $productMainTableAlias          = $collection::MAIN_TABLE_ALIAS;
            $productsTableAlias             = 'products';
            $catalogRuleProductTableAlias   = 'catalogrule_product';
            $productIdField = $collection->getResource()->getIdFieldName();

            foreach ($customerGroupIds as $customerGroupId) {
                $customerGroupId = (int) $customerGroupId;

                /**
                 * @var $collection Mage_Catalog_Model_Resource_Product_Collection
                 */
                $collection = Mage::getResourceModel('catalog/product_collection');
                $collection->setStoreId($storeId);

                $select = $collection->getSelect();
                $select->reset(Zend_Db_Select::ORDER);
                $select->reset(Zend_Db_Select::COLUMNS);

                $select
                    ->columns(array('product_id' => $productIdField))
                    ->joinInner(
                        array($catalogRuleProductTableAlias => $this->_catalogRuleProductTable),
                        implode(" AND ", array(
                            "{$productMainTableAlias}.{$productIdField} = {$catalogRuleProductTableAlias}.product_id",
                            $collection->getConnection()->quoteInto("{$catalogRuleProductTableAlias}.rule_id = ?", $catalogRuleId),
                            $collection->getConnection()->quoteInto("{$catalogRuleProductTableAlias}.customer_group_id = ?", $customerGroupId),
                            $collection->getConnection()->quoteInto("{$catalogRuleProductTableAlias}.website_id = ?", $websiteId),
                        )),
                        array()
                    )
                    ->joinInner(
                        array($productsTableAlias => $collectionSelect),
                        "{$productsTableAlias}.product_id = {$productMainTableAlias}.{$productIdField}",
                        array()
                    );

                $orderExpr = new Zend_Db_Expr("{$catalogRuleProductTableAlias}.sort_order " . $select::SQL_ASC);
                $select->order($orderExpr);

                $this->insertSetProductsUsingSelect($select, $set, $storeId, $customerGroupId);
            }
        }

        return $this;
    }

    /**
     * fill special products set by created date
     *
     * @param  Astrio_SpecialProducts_Model_Set $set set
     * @return $this
     */
    protected function _processSetAutoTypeRecentlyAdded(Astrio_SpecialProducts_Model_Set $set)
    {
        $storeIds = $set->getStoreIds();
        foreach ($storeIds as $storeId) {
            $storeId = (int) $storeId;
            $collection = $this->getProductCollection($set, $storeId);

            if ($inLastYDays = $set->getFilterInLastDays()) {
                $collection->addAttributeToFilter('created_at', array('gteq' => date('Y-m-d H:i:s', time() - $inLastYDays * 86400)));
            }

            $this->resetProductCollectionColumnsAndOrder($collection);
            $collectionSelect = $collection->getSelect();

            /**
             * @var $collection Mage_Catalog_Model_Resource_Product_Collection
             */
            $collection = Mage::getResourceModel('catalog/product_collection');
            $collection->setStoreId($storeId);

            $select = $collection->getSelect();
            $select->reset(Zend_Db_Select::ORDER);
            $select->reset(Zend_Db_Select::COLUMNS);

            $productMainTableAlias = $collection::MAIN_TABLE_ALIAS;
            $productsTableAlias    = 'products';

            $productIdField = $collection->getResource()->getIdFieldName();

            $select
                ->columns(array('product_id' => $productIdField,))
                ->joinInner(
                    array($productsTableAlias => $collectionSelect),
                    "{$productsTableAlias}.product_id = {$productMainTableAlias}.{$productIdField}",
                    array()
                );

            $orderExpr = new Zend_Db_Expr("{$productMainTableAlias}.created_at " . Varien_Db_Select::SQL_DESC);
            $select->order($orderExpr);

            $this->insertSetProductsUsingSelect($select, $set, $storeId, null);
        }

        return $this;
    }

    /**
     * fill special products set by "news_from_date" and "news_to_date"
     *
     * @param  Astrio_SpecialProducts_Model_Set $set set
     * @return $this
     */
    protected function _processSetAutoTypeNew(Astrio_SpecialProducts_Model_Set $set)
    {
        $storeIds = $set->getStoreIds();
        foreach ($storeIds as $storeId) {
            $storeId = (int) $storeId;
            $collection = $this->getProductCollection($set, $storeId);

            $todayDate = Varien_Date::formatDate(true, true);
            $collection
                ->addAttributeToFilter('news_from_date', array('lt' => $todayDate))
                ->addAttributeToFilter('news_to_date', array(
                    array('gt' => $todayDate),
                    array('null' => true),
                ));

            $this->resetProductCollectionColumnsAndOrder($collection);
            $collectionSelect = $collection->getSelect();

            /**
             * @var $collection Mage_Catalog_Model_Resource_Product_Collection
             */
            $collection = Mage::getResourceModel('catalog/product_collection');
            $collection->setStoreId($storeId);
            $collection->joinAttribute(
                'news_from_date',
                Mage_Catalog_Model_Product::ENTITY . '/news_from_date',
                'entity_id',
                null,
                'inner',
                $storeId
            );

            $select = $collection->getSelect();
            $select->reset(Zend_Db_Select::ORDER);
            $collection->addAttributeToSort('news_from_date', $collection::SORT_ORDER_DESC);
            $select->reset(Zend_Db_Select::COLUMNS);

            $productMainTableAlias = $collection::MAIN_TABLE_ALIAS;
            $productsTableAlias    = 'products';

            $productIdField = $collection->getResource()->getIdFieldName();

            $select
                ->columns(array('product_id' => $productIdField))
                ->joinInner(
                    array($productsTableAlias => $collectionSelect),
                    "{$productsTableAlias}.product_id = {$productMainTableAlias}.{$productIdField}",
                    array()
                );

            $this->insertSetProductsUsingSelect($select, $set, $storeId, null);
        }

        return $this;
    }

    /**
     * fill special products set by discount percent
     *
     * @param  Astrio_SpecialProducts_Model_Set $set set
     * @return $this
     */
    protected function _processSetAutoTypeOnSale(Astrio_SpecialProducts_Model_Set $set)
    {
        $customerGroupIds = $this->getCustomerGroupIds();
        $storeIds = $set->getStoreIds();
        foreach ($storeIds as $storeId) {
            $storeId = (int) $storeId;
            $websiteId = (int) Mage::app()->getStore($storeId)->getWebsiteId();

            $collection = $this->getProductCollection($set, $storeId);
            $this->resetProductCollectionColumnsAndOrder($collection);
            $collectionSelect = $collection->getSelect();

            $productMainTableAlias = $collection::MAIN_TABLE_ALIAS;
            $productsTableAlias    = 'products';
            $productPriceIndexTableAlias  = 'price_index';
            $productIdField = $collection->getResource()->getIdFieldName();

            foreach ($customerGroupIds as $customerGroupId) {
                $customerGroupId = (int) $customerGroupId;
                /**
                 * @var $collection Mage_Catalog_Model_Resource_Product_Collection
                 */
                $collection = Mage::getResourceModel('catalog/product_collection');
                $collection->setStoreId($storeId);
                $collection->addPriceData($customerGroupId, $websiteId);

                $select = $collection->getSelect();
                $select->reset(Zend_Db_Select::ORDER);
                $select->reset(Zend_Db_Select::COLUMNS);

                $select
                    ->columns(array('product_id' => $productIdField))
                    ->joinInner(
                        array($productsTableAlias => $collectionSelect),
                        "{$productsTableAlias}.product_id = {$productMainTableAlias}.{$productIdField}",
                        array()
                    );

                $discountPercentExpr = "({$productPriceIndexTableAlias}.price - {$productPriceIndexTableAlias}.final_price) / {$productPriceIndexTableAlias}.price";

                if ($moreThan = $set->getFilterGreaterThan()) {
                    $moreThan = $moreThan / 100;
                    $select->where("({$discountPercentExpr}) >= ?", $moreThan);
                } else {
                    $select->where("({$discountPercentExpr}) >= ?", 0.005); //min 1%
                }

                $orderExpr = new Zend_Db_Expr("{$discountPercentExpr} " . $select::SQL_DESC);
                $select->order($orderExpr);

                $this->insertSetProductsUsingSelect($select, $set, $storeId, $customerGroupId);
            }
        }

        return $this;
    }

    /**
     * fill special products set by sold quantity
     *
     * @param  Astrio_SpecialProducts_Model_Set $set set
     * @return $this
     */
    protected function _processSetAutoTypeBestseller(Astrio_SpecialProducts_Model_Set $set)
    {
        $storeIds = $set->getStoreIds();
        foreach ($storeIds as $storeId) {
            $storeId = (int) $storeId;
            $collection = $this->getProductCollection($set, $storeId);
            $this->resetProductCollectionColumnsAndOrder($collection);
            $collectionSelect = $collection->getSelect();

            $storeIds = Mage::app()->getStore($storeId)->getWebsite()->getStoreIds();

            $productsTableAlias     = 'products';
            $orderItemTableAlias    = 'order_item';
            $orderTableAlias        = 'order';

            $select = $this->_readConnection->select();
            $select
                ->from(
                    array($orderItemTableAlias => $this->_coreResource->getTableName('sales/order_item')),
                    array('product_id' => "product_id")
                )
                ->joinInner(
                    array($productsTableAlias => $collectionSelect),
                    "{$productsTableAlias}.product_id = {$orderItemTableAlias}.product_id",
                    array()
                )
                ->joinInner(
                    array($orderTableAlias => $this->_coreResource->getTableName('sales/order')),
                    implode(' AND ', array(
                        "{$orderTableAlias}.entity_id = {$orderItemTableAlias}.order_id",
                        $this->_readConnection->quoteInto("{$orderTableAlias}.store_id IN(?)", $storeIds),
                        $this->_readConnection->quoteInto("{$orderTableAlias}.state NOT IN(?)", array(
                            Mage_Sales_Model_Order::STATE_CANCELED
                        )),
                    )),
                    array()
                )
                ->group("{$orderItemTableAlias}.product_id");

            $sumExpr = "SUM({$orderItemTableAlias}.qty_ordered)";
            $orderExpr = new Zend_Db_Expr("{$sumExpr} " . $select::SQL_DESC);
            $select->order($orderExpr);

            if ($moreThan = $set->getFilterGreaterThan()) {
                $select->having("{$sumExpr} >= ?", $moreThan);
            }

            if ($inLastYDays = $set->getFilterInLastDays()) {
                $select->where("{$orderTableAlias}.created_at >= ?", date('Y-m-d H:i:s', time() - $inLastYDays * 86400));
            }

            $this->insertSetProductsUsingSelect($select, $set, $storeId, null);
        }

        return $this;
    }

    /**
     * fill special products set by views count
     *
     * @param  Astrio_SpecialProducts_Model_Set $set set
     * @return $this
     */
    protected function _processSetAutoTypeMostViewed(Astrio_SpecialProducts_Model_Set $set)
    {
        $eventId = $this->_getProductViewEventId();
        if (!$eventId) {
            return $this;
        }

        $storeIds = $set->getStoreIds();
        foreach ($storeIds as $storeId) {
            $storeId = (int) $storeId;
            $collection = $this->getProductCollection($set, $storeId);
            $this->resetProductCollectionColumnsAndOrder($collection);
            $collectionSelect = $collection->getSelect();

            /**
             * @var $collection Mage_Catalog_Model_Resource_Product_Collection
             */
            $collection = Mage::getResourceModel('catalog/product_collection');
            $collection->setStoreId($storeId);

            $select = $collection->getSelect();
            $select->reset(Zend_Db_Select::ORDER);
            $select->reset(Zend_Db_Select::COLUMNS);

            $productMainTableAlias  = $collection::MAIN_TABLE_ALIAS;
            $productsTableAlias     = 'products';
            $reportsEventTableAlias = 'reports_event_table';

            $productIdField = $collection->getResource()->getIdFieldName();

            $select
                ->columns(array('product_id' => "{$productMainTableAlias}.{$productIdField}"))
                ->joinInner(
                    array($productsTableAlias => $collectionSelect),
                    "{$productsTableAlias}.product_id = {$productMainTableAlias}.{$productIdField}",
                    array()
                )
                ->joinInner(
                    array($reportsEventTableAlias => $this->_reportsEventTable),
                    implode(' AND ', array(
                        "{$productMainTableAlias}.{$productIdField} = {$reportsEventTableAlias}.object_id",
                        $collection->getConnection()->quoteInto("{$reportsEventTableAlias}.event_type_id  = ?", $eventId),
                    )),
                    array()
                )
                ->group("{$productMainTableAlias}.{$productIdField}");

            $countExpr = "COUNT({$reportsEventTableAlias}.event_id)";
            $orderExpr = new Zend_Db_Expr("{$countExpr} " . $select::SQL_DESC);
            $select->order($orderExpr);

            if ($moreThan = $set->getFilterGreaterThan()) {
                $select->having("{$countExpr} >= ?", $moreThan);
            }

            if ($inLastYDays = $set->getFilterInLastDays()) {
                $select->where("{$reportsEventTableAlias}.logged_at >= ?", date('Y-m-d H:i:s', time() - $inLastYDays * 86400));
            }

            $this->insertSetProductsUsingSelect($select, $set, $storeId, null);
        }

        return $this;
    }

    /**
     * fill special products set by reviews count
     *
     * @param Astrio_SpecialProducts_Model_Set $set set
     * @return $this
     */
    protected function _processSetAutoTypeMostReviewed(Astrio_SpecialProducts_Model_Set $set)
    {
        $storeIds = $set->getStoreIds();
        foreach ($storeIds as $storeId) {
            $storeId = (int) $storeId;
            $collection = $this->getProductCollection($set, $storeId);
            $this->resetProductCollectionColumnsAndOrder($collection);
            $collectionSelect = $collection->getSelect();

            /**
             * @var $collection Mage_Catalog_Model_Resource_Product_Collection
             */
            $collection = Mage::getResourceModel('catalog/product_collection');
            $collection->setStoreId($storeId);

            $select = $collection->getSelect();
            $select->reset(Zend_Db_Select::ORDER);
            $select->reset(Zend_Db_Select::COLUMNS);

            $productMainTableAlias  = $collection::MAIN_TABLE_ALIAS;
            $productsTableAlias     = 'products';
            $reviewTableAlias       = 'review';
            $reviewDetailTableAlias = 'review_detail';

            $productIdField = $collection->getResource()->getIdFieldName();

            $select
                ->columns(array('product_id' => "{$productMainTableAlias}.{$productIdField}"))
                ->joinInner(
                    array($productsTableAlias => $collectionSelect),
                    "{$productsTableAlias}.product_id = {$productMainTableAlias}.{$productIdField}",
                    array()
                )
                ->joinInner(
                    array($reviewTableAlias => $this->_reviewTable),
                    implode(' AND ', array(
                        "{$productMainTableAlias}.{$productIdField} = {$reviewTableAlias}.entity_pk_value",
                        $collection->getConnection()->quoteInto("{$reviewTableAlias}.status_id = ?", 1),
                        $collection->getConnection()->quoteInto("{$reviewTableAlias}.entity_id = ?", 1),
                    )),
                    array()
                )
                ->joinInner(
                    array($reviewDetailTableAlias => $this->_reviewDetailTable),
                    implode(' AND ', array(
                        "{$reviewTableAlias}.review_id = {$reviewDetailTableAlias}.review_id",
                        $collection->getConnection()->quoteInto("{$reviewDetailTableAlias}.store_id = ?", $storeId),
                    )),
                    array()
                )
                ->group("{$productMainTableAlias}.{$productIdField}");

            $countExpr = "COUNT({$reviewTableAlias}.review_id)";
            $orderExpr = new Zend_Db_Expr("{$countExpr} " . $select::SQL_DESC);
            $select->order($orderExpr);

            if ($moreThan = $set->getFilterGreaterThan()) {
                $select->having("{$countExpr} >= ?", $moreThan);
            }

            if ($inLastYDays = $set->getFilterInLastDays()) {
                $select->where("{$reviewTableAlias}.created_at >= ?", date('Y-m-d H:i:s', time() - $inLastYDays * 86400));
            }

            $this->insertSetProductsUsingSelect($select, $set, $storeId, null);
        }

        return $this;
    }
}
