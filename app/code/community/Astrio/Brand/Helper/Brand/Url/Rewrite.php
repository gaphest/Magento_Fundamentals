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
 * @package    Astrio_Brand
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
class Astrio_Brand_Helper_Brand_Url_Rewrite implements Astrio_Brand_Helper_Brand_Url_Rewrite_Interface
{
    // XML confif path for seo brands list url key
    const XML_PATH_SEO_BRANDS_LIST_URL_KEY  = 'astrio_brand/seo/brands_list_url_key';

    protected $_brandsUrlPath   = array();

    protected $_brandsUrl       = array();

    /**
     * Adapter instance
     *
     * @var Varien_Db_Adapter_Interface
     */
    protected $_connection;

    /**
     * Resource instance
     *
     * @var Mage_Core_Model_Resource
     */
    protected $_resource;

    /**
     * Initialize resource and connection instances
     *
     * @param array $args args
     */
    public function __construct(array $args = array())
    {
        $this->_resource = Mage::getSingleton('core/resource');
        $this->_connection = !empty($args['connection']) ? $args['connection'] : $this->_resource
            ->getConnection(Mage_Core_Model_Resource::DEFAULT_READ_RESOURCE);
    }

    /**
     * Prepare and return select
     *
     * @param array $brandIds brand ids
     * @param int $storeId store id
     * @return Varien_Db_Select
     */
    public function getTableSelect(array $brandIds, $storeId)
    {
        $select = $this->_connection->select()
            ->from($this->_resource->getTableName('core/url_rewrite'), array('brand_id', 'request_path'))
            ->where('store_id = ?', (int)$storeId)
            ->where('is_system = ?', 1)
            ->where('product_id IS NULL')
            ->where('brand_id IN(?)', $brandIds);
        return $select;
    }

    /**
     * Gets table select for product collection
     *
     * @param array $productIds product ids
     * @param int $brandId brand ids
     * @param int $storeId store ids
     * @return Varien_Db_Select
     */
    public function getTableSelectForProductCollection(array $productIds, $brandId, $storeId)
    {
        $select = $this->_connection->select()
            ->from($this->_resource->getTableName('core/url_rewrite'), array('product_id', 'request_path'))
            ->where('store_id = ?', (int)$storeId)
            ->where('is_system = ?', 1)
            ->where('category_id IS NULL')
            ->where('brand_id = ? OR brand_id IS NULL', (int)$brandId)
            ->where('product_id IN(?)', $productIds)
            ->order('brand_id ' . Varien_Data_Collection::SORT_ORDER_DESC);
        return $select;
    }

    /**
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection collection
     * @param int $brandId brand ids
     * @return array
     */
    public function addProductUrlRewrites($collection, $brandId)
    {
        $storeId = $collection->getStoreId();
        $productIds = array_keys($collection->getItems());
        /**
         * @var $brandHelper Astrio_Brand_Helper_Brand
         */
        $brandHelper = Mage::helper('astrio_brand/brand');
        if (!$brandHelper->shouldUseBrandForProductUrl($storeId)) {
            $brandId = 0;
        }

        $select = $this->getTableSelectForProductCollection($productIds, $brandId, $storeId);
        $urlRewrites = array();
        foreach ($this->_connection->fetchAll($select) as $row) {
            if (!isset($urlRewrites[$row['product_id']])) {
                $urlRewrites[$row['product_id']] = $row['request_path'];
            }
        }

        foreach ($collection->getItems() as $item) {
            $item->setDoNotUseCategoryId(true);

            if (isset($urlRewrites[$item->getEntityId()])) {
                $item->setData('request_path', $urlRewrites[$item->getEntityId()]);
            } else {
                $item->setData('request_path', false);
            }
        }

        return $this;
    }


    /**
     * Get Brands List URL key
     *
     * @param int $storeId Store View
     * @return string
     */
    public function getBrandsListUrlKey($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_SEO_BRANDS_LIST_URL_KEY, $storeId);
    }

    /**
     * Gets brands list url path
     *
     * @param null|int $storeId store id
     * @return mixed
     */
    public function getBrandsListUrlPath($storeId = null)
    {
        if ($storeId === null) {
            $storeId = Mage::app()->getStore()->getId();
        }
        if (!isset($this->_brandsUrlPath[$storeId])) {
            /**
             * @var $brandHelper Astrio_Brand_Helper_Brand
             */
            $brandHelper = Mage::helper('astrio_brand/brand');
            $this->_brandsUrlPath[$storeId] = $this->getBrandsListUrlKey($storeId);
            if ($this->_brandsUrlPath[$storeId]) {
                $this->_brandsUrlPath[$storeId] .= $brandHelper->getBrandUrlSuffix($storeId);
            }
        }

        return $this->_brandsUrlPath[$storeId];
    }

    /**
     * Gets brands list url
     *
     * Get Brands List URL
     * @param null|int $storeId store id
     * @return string
     */
    public function getBrandsListUrl($storeId = null)
    {
        if ($storeId === null) {
            $storeId = Mage::app()->getStore()->getId();
        }
        if (!isset($this->_brandsUrl[$storeId])) {
            $params = array();

            $rewrite = Mage::getSingleton('astrio_brand/brand_url')->getUrlRewrite();
            $rewrite->setStoreId($storeId)
                ->loadByIdPath('astrio_brand/list');

            if ($rewrite->getId()) {
                $urlPath = $rewrite->getRequestPath();
                $params['_direct'] = $urlPath;
            }

            $this->_brandsUrl[$storeId] = Mage::getUrl('astrio_brand/brand/list', $params);
        }

        return $this->_brandsUrl[$storeId];
    }

    /**
     * Creates brands list url rewrite
     *
     * @param null|int $storeId store id
     * @return $this
     * @throws Exception
     */
    public function createBrandsListUrlRewrite($storeId = null)
    {
        if ($storeId === null) {
            /**
             * @var $store Mage_Core_Model_Store
             */
            $stores = Mage::app()->getStores();
            foreach ($stores as $store) {
                $this->createBrandsListUrlRewrite($store->getId());
            }
            return $this;
        }
        /**
         * @var Mage_Core_Model_Url_Rewrite $urlRewrite
         */
        $urlRewrite = Mage::getModel('core/url_rewrite');
        $urlRewrite->setStoreId($storeId);
        $urlRewrite->loadByIdPath('astrio_brand/list');
        if ($url = $this->getBrandsListUrlPath($storeId)) {
            $urlRewrite
                ->setIdPath('astrio_brand/list')
                ->setTargetPath('astrio_brand/brand/list')
                ->setIsSystem(1)
                ->setDescription('')
                ->setRequestPath($url)
                ->setStoreId($storeId)
                ->save();
        } elseif ($urlRewrite->getId()) {
            $urlRewrite->delete();
        }

        return $this;
    }
}