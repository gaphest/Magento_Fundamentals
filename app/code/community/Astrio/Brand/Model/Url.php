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
 * @see Mage_Catalog_Model_Url
 */
class Astrio_Brand_Model_Url
{
    /**
     * Number of characters allowed to be in URL path
     *
     * @var int
     */
    const MAX_REQUEST_PATH_LENGTH = 240;

    /**
     * Number of characters allowed to be in URL path
     * after MAX_REQUEST_PATH_LENGTH number of characters
     *
     * @var int
     */
    const ALLOWED_REQUEST_PATH_OVERFLOW = 10;

    /**
     * Resource model
     *
     * @var Astrio_Brand_Model_Resource_Url
     */
    protected $_resourceModel;

    /**
     * Brands cache for products
     *
     * @var array
     */
    protected $_brands = array();

    /**
     * Rewrite cache
     *
     * @var array
     */
    protected $_rewrites = array();

    /**
     * Current url rewrite rule
     *
     * @var Varien_Object
     */
    protected $_rewrite;

    /**
     * Cache for product rewrite suffix
     *
     * @var array
     */
    protected $_productUrlSuffix = array();

    /**
     * Cache for brand rewrite suffix
     *
     * @var array
     */
    protected $_brandUrlSuffix = array();

    /**
     * Flag to overwrite config settings for Catalog URL rewrites history maintainance
     *
     * @var bool
     */
    protected $_saveRewritesHistory = null;

    /**
     * Retrieve stores array or store model
     *
     * @param int $storeId store id
     * @return Mage_Core_Model_Store|array
     */
    public function getStores($storeId = null)
    {
        return $this->getResource()->getStores($storeId);
    }

    /**
     * Retrieve resource model
     *
     * @return Astrio_Brand_Model_Resource_Url
     */
    public function getResource()
    {
        if (is_null($this->_resourceModel)) {
            $this->_resourceModel = Mage::getResourceModel('astrio_brand/url');
        }
        return $this->_resourceModel;
    }

    /**
     * Retrieve Brand model singleton
     *
     * @return Astrio_Brand_Model_Brand
     */
    public function getBrandModel()
    {
        return $this->getResource()->getBrandModel();
    }

    /**
     * Retrieve product model singleton
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProductModel()
    {
        return $this->getResource()->getProductModel();
    }

    /**
     * Setter for $_saveRewritesHistory
     * Force Rewrites History save bypass config settings
     *
     * @param bool $flag flag
     * @return Astrio_Brand_Model_Url
     */
    public function setShouldSaveRewritesHistory($flag)
    {
        $this->_saveRewritesHistory = (bool)$flag;
        return $this;
    }

    /**
     * Indicate whether to save URL Rewrite History or not (create redirects to old URLs)
     *
     * @param int $storeId Store View
     * @return bool
     */
    public function getShouldSaveRewritesHistory($storeId = null)
    {
        if ($this->_saveRewritesHistory !== null) {
            return $this->_saveRewritesHistory;
        }
        return Mage::helper('astrio_brand')->shouldSaveUrlRewritesHistory($storeId);
    }

    /**
     * Refresh all rewrite urls for some store or for all stores
     * Used to make full reindexing of url rewrites
     *
     * @param int $storeId store id
     * @return Astrio_Brand_Model_Url
     */
    public function refreshRewrites($storeId = null)
    {
        if (is_null($storeId)) {
            foreach ($this->getStores() as $store) {
                $this->refreshRewrites($store->getId());
            }
            return $this;
        }

        $brandIds = $this->getResource()->getStoreBrandIds($storeId);

        $this->clearStoreInvalidRewrites($storeId);
        foreach ($brandIds as $brandId) {
            $this->refreshBrandRewrite($brandId, $storeId, false);
        }
        $this->refreshProductRewrites($storeId);
        $this->getResource()->clearBrandProduct($storeId);

        return $this;
    }

    /**
     * Refresh brand rewrite
     *
     * @param Varien_Object $brand brand
     * @param bool $refreshProducts refresh products
     * @return Astrio_Brand_Model_Url
     */
    protected function _refreshBrandRewrites(Varien_Object $brand, $refreshProducts = true)
    {
        if ($brand->getUrlKey() == '') {
            $urlKey = $this->getBrandModel()->formatUrlKey($brand->getName());
        } else {
            $urlKey = $this->getBrandModel()->formatUrlKey($brand->getUrlKey());
        }

        $idPath      = $this->generatePath('id', null, $brand);
        $targetPath  = $this->generatePath('target', null, $brand);
        $requestPath = $this->getBrandRequestPath($brand);

        $rewriteData = array(
            'store_id'      => $brand->getStoreId(),
            'brand_id'      => $brand->getId(),
            'product_id'    => null,
            'id_path'       => $idPath,
            'request_path'  => $requestPath,
            'target_path'   => $targetPath,
            'is_system'     => 1
        );

        $this->getResource()->saveRewrite($rewriteData, $this->_rewrite);

        if ($this->getShouldSaveRewritesHistory($brand->getStoreId())) {
            $this->_saveRewriteHistory($rewriteData, $this->_rewrite);
        }

        if ($brand->getUrlKey() != $urlKey) {
            $brand->setUrlKey($urlKey);
            $this->getResource()->saveBrandAttribute($brand, 'url_key');
        }

        if ($refreshProducts) {
            $this->_refreshBrandProductRewrites($brand);
        }

        return $this;
    }

    /**
     * Refresh product rewrite
     *
     * @param Varien_Object $product product
     * @param Varien_Object $brand brand
     * @return Astrio_Brand_Model_Url
     */
    protected function _refreshProductRewrite(Varien_Object $product, Varien_Object $brand)
    {
        if ($product->getUrlKey() == '') {
            $urlKey = $this->getProductModel()->formatUrlKey($product->getName());
        } else {
            $urlKey = $this->getProductModel()->formatUrlKey($product->getUrlKey());
        }

        $idPath      = $this->generatePath('id', $product, $brand);
        $targetPath  = $this->generatePath('target', $product, $brand);
        $requestPath = $this->getProductRequestPath($product, $brand);

        $brandId = $brand->getId();
        $updateKeys = true;

        $rewriteData = array(
            'store_id'      => $brand->getStoreId(),
            'brand_id'      => $brandId,
            'product_id'    => $product->getId(),
            'id_path'       => $idPath,
            'request_path'  => $requestPath,
            'target_path'   => $targetPath,
            'is_system'     => 1
        );

        $this->getResource()->saveRewrite($rewriteData, $this->_rewrite);

        if ($this->getShouldSaveRewritesHistory($brand->getStoreId())) {
            $this->_saveRewriteHistory($rewriteData, $this->_rewrite);
        }

        if ($updateKeys && $product->getUrlKey() != $urlKey) {
            $product->setUrlKey($urlKey);
            $this->getResource()->saveProductAttribute($product, 'url_key');
        }
        if ($updateKeys && $product->getUrlPath() != $requestPath) {
            $product->setUrlPath($requestPath);
            $this->getResource()->saveProductAttribute($product, 'url_path');
        }

        return $this;
    }

    /**
     * Refresh products for brand
     *
     * @param Varien_Object $brand brand
     * @return Astrio_Brand_Model_Url
     */
    protected function _refreshBrandProductRewrites(Varien_Object $brand)
    {
        $originalRewrites = $this->_rewrites;
        $process = true;
        $lastEntityId = 0;
        $firstIteration = true;
        while ($process == true) {
            $products = $this->getResource()->getProductsByBrand($brand, $lastEntityId);
            if (!$products) {
                if ($firstIteration) {
                    $this->getResource()->deleteBrandProductStoreRewrites(
                        $brand->getId(),
                        array(),
                        $brand->getStoreId()
                    );
                }
                $process = false;
                break;
            }

            $brandIds = array($brand->getId());
            $this->_rewrites = $this->getResource()->prepareRewrites(
                $brand->getStoreId(),
                $brandIds,
                array_keys($products)
            );

            foreach ($products as $product) {
                $this->_refreshProductRewrite($product, $brand);
            }
            $firstIteration = false;
            unset($products);
        }
        $this->_rewrites = $originalRewrites;
        return $this;
    }

    /**
     * Refresh brand and childs rewrites
     * Called when reindexing all rewrites and as a reaction on brand change that affects rewrites
     *
     * @param int|array $brandIds brand ids
     * @param int|null $storeId store id
     * @param bool $refreshProducts refresh products?
     * @return Astrio_Brand_Model_Url
     */
    public function refreshBrandRewrite($brandIds, $storeId = null, $refreshProducts = true)
    {
        if (is_null($storeId)) {
            foreach ($this->getStores() as $store) {
                $this->refreshBrandRewrite($brandIds, $store->getId(), $refreshProducts);
            }
            return $this;
        }

        if (!is_array($brandIds)) {
            $brandIds = array($brandIds);
        }

        $brands = $this->getResource()->getBrands($brandIds, $storeId);
        if (!$brands) {
            return $this;
        }

        $this->_rewrites = $this->getResource()->prepareRewrites($storeId, $brandIds);
        foreach ($brands as $brand) {
            $this->_refreshBrandRewrites($brand, $refreshProducts);
        }

        unset($brands);
        $this->_rewrites = array();

        return $this;
    }

    /**
     * Refresh product rewrite urls for one store or all stores
     * Called as a reaction on product change that affects rewrites
     *
     * @param int $productId product id
     * @param int|null $storeId store id
     * @return Astrio_Brand_Model_Url
     */
    public function refreshProductRewrite($productId, $storeId = null)
    {
        if (is_null($storeId)) {
            foreach ($this->getStores() as $store) {
                $this->refreshProductRewrite($productId, $store->getId());
            }
            return $this;
        }

        $product = $this->getResource()->getProduct($productId, $storeId);
        if ($product) {
            // List of brands the product is assigned to, filtered by being within the store's brands root
            $brands = $this->getResource()->getBrands(array($product->getDataUsingMethod(Astrio_Brand_Model_Brand::ATTRIBUTE_CODE)), $storeId);
            $this->_rewrites = $this->getResource()->prepareRewrites($storeId, '', $productId);

            // Create product url rewrites
            foreach ($brands as $brand) {
                $this->_refreshProductRewrite($product, $brand);
            }

            // Remove all other product rewrites created earlier for this store - they're invalid now
            $excludeBrandIds = array_keys($brands);
            $this->getResource()->clearProductRewrites($productId, $storeId, $excludeBrandIds);

            unset($brands);
            unset($product);
        } else {
            // Product doesn't belong to this store - clear all its url rewrites including root one
            $this->getResource()->clearProductRewrites($productId, $storeId, array());
        }

        return $this;
    }

    /**
     * Refresh all product rewrites for designated store
     *
     * @param int $storeId store id
     * @return Astrio_Brand_Model_Url
     */
    public function refreshProductRewrites($storeId)
    {
        $this->_brands = array();

        $lastEntityId = 0;
        $process = true;

        while ($process == true) {
            $products = $this->getResource()->getProductsByStore($storeId, $lastEntityId);
            if (!$products) {
                $process = false;
                break;
            }

            $this->_rewrites = $this->getResource()->prepareRewrites($storeId, false, array_keys($products));

            $loadBrands = array();
            foreach ($products as $product) {
                foreach (array($product->getDataUsingMethod(Astrio_Brand_Model_Brand::ATTRIBUTE_CODE)) as $brandId) {
                    if (!isset($this->_brands[$brandId])) {
                        $loadBrands[$brandId] = $brandId;
                    }
                }
            }

            if ($loadBrands) {
                foreach ($this->getResource()->getBrands($loadBrands, $storeId) as $brand) {
                    $this->_brands[$brand->getId()] = $brand;
                }
            }

            foreach ($products as $product) {
                if (isset($this->_brands[$product->getDataUsingMethod(Astrio_Brand_Model_Brand::ATTRIBUTE_CODE)])) {
                    $this->_refreshProductRewrite($product, $this->_brands[$product->getDataUsingMethod(Astrio_Brand_Model_Brand::ATTRIBUTE_CODE)]);
                }
            }

            unset($products);
            $this->_rewrites = array();
        }

        $this->_brands = array();
        return $this;
    }

    /**
     * Deletes old rewrites for store, left from the times when store had some other root brand
     *
     * @param int $storeId store id
     * @return Astrio_Brand_Model_Url
     */
    public function clearStoreInvalidRewrites($storeId = null)
    {
        if (is_null($storeId)) {
            foreach ($this->getStores() as $store) {
                $this->clearStoreInvalidRewrites($store->getId());
            }
            return $this;
        }

        $this->getResource()->clearStoreInvalidRewrites($storeId);
        return $this;
    }

    /**
     * Get requestPath that was not used yet.
     *
     * Will try to get unique path by adding -1 -2 etc. between url_key and optional url_suffix
     *
     * @param int $storeId store id
     * @param string $requestPath request path
     * @param string $idPath id path
     * @return string
     */
    public function getUnusedPath($storeId, $requestPath, $idPath)
    {
        if (strpos($idPath, 'product') !== false) {
            $suffix = $this->getProductUrlSuffix($storeId);
        } else {
            $suffix = $this->getBrandUrlSuffix($storeId);
        }
        if (empty($requestPath)) {
            $requestPath = '-';
        } elseif ($requestPath == $suffix) {
            $requestPath = '-' . $suffix;
        }

        /**
         * Validate maximum length of request path
         */
        if (strlen($requestPath) > self::MAX_REQUEST_PATH_LENGTH + self::ALLOWED_REQUEST_PATH_OVERFLOW) {
            $requestPath = substr($requestPath, 0, self::MAX_REQUEST_PATH_LENGTH);
        }

        if (isset($this->_rewrites[$idPath])) {
            $this->_rewrite = $this->_rewrites[$idPath];
            if ($this->_rewrites[$idPath]->getRequestPath() == $requestPath) {
                return $requestPath;
            }
        } else {
            $this->_rewrite = null;
        }

        $rewrite = $this->getResource()->getRewriteByRequestPath($requestPath, $storeId);
        if ($rewrite && $rewrite->getId()) {
            if ($rewrite->getIdPath() == $idPath) {
                $this->_rewrite = $rewrite;
                return $requestPath;
            }
            // match request_url abcdef1234(-12)(.html) pattern
            $match = array();
            $regularExpression = '#^([0-9a-z/-]+?)(-([0-9]+))?('.preg_quote($suffix).')?$#i';
            if (!preg_match($regularExpression, $requestPath, $match)) {
                return $this->getUnusedPath($storeId, '-', $idPath);
            }
            $match[1] = $match[1] . '-';
            $match[4] = isset($match[4]) ? $match[4] : '';

            $lastRequestPath = $this->getResource()
                ->getLastUsedRewriteRequestIncrement($match[1], $match[4], $storeId);
            if ($lastRequestPath) {
                $match[3] = $lastRequestPath;
            }
            return $match[1]
            . (isset($match[3]) ? ($match[3] + 1) : '1')
            . $match[4];
        } else {
            return $requestPath;
        }
    }

    /**
     * Retrieve product rewrite sufix for store
     *
     * @param int $storeId store id
     * @return string
     */
    public function getProductUrlSuffix($storeId)
    {
        return Mage::helper('catalog/product')->getProductUrlSuffix($storeId);
    }

    /**
     * Retrieve brand rewrite sufix for store
     *
     * @param int $storeId store id
     * @return string
     */
    public function getBrandUrlSuffix($storeId)
    {
        return Mage::helper('astrio_brand/brand')->getBrandUrlSuffix($storeId);
    }

    /**
     * Get unique brand request path
     *
     * @param Varien_Object $brand brand
     * @return string
     */
    public function getBrandRequestPath($brand)
    {
        $storeId = $brand->getStoreId();
        $idPath  = $this->generatePath('id', null, $brand);
        $suffix  = $this->getBrandUrlSuffix($storeId);

        if (isset($this->_rewrites[$idPath])) {
            $this->_rewrite = $this->_rewrites[$idPath];
            $existingRequestPath = $this->_rewrites[$idPath]->getRequestPath();
        }

        if ($brand->getUrlKey() == '') {
            $urlKey = $this->getBrandModel()->formatUrlKey($brand->getName());
        } else {
            $urlKey = $this->getBrandModel()->formatUrlKey($brand->getUrlKey());
        }

        $brandUrlSuffix = $this->getBrandUrlSuffix($brand->getStoreId());

        $requestPath = $urlKey . $brandUrlSuffix;
        if (isset($existingRequestPath) && $existingRequestPath == $requestPath . $suffix) {
            return $existingRequestPath;
        }

        if ($this->_deleteOldTargetPath($requestPath, $idPath, $storeId)) {
            return $requestPath;
        }

        return $this->getUnusedPath($brand->getStoreId(), $requestPath,
            $this->generatePath('id', null, $brand)
        );
    }

    /**
     * Check if current generated request path is one of the old paths
     *
     * @param string $requestPath request path
     * @param string $idPath id path
     * @param int $storeId store id
     * @return bool
     */
    protected function _deleteOldTargetPath($requestPath, $idPath, $storeId)
    {
        $finalOldTargetPath = $this->getResource()->findFinalTargetPath($requestPath, $storeId);
        if ($finalOldTargetPath && $finalOldTargetPath == $idPath) {
            $this->getResource()->deleteRewriteRecord($requestPath, $storeId, true);
            return true;
        }

        return false;
    }

    /**
     * Get unique product request path
     *
     * @param   Varien_Object $product product
     * @param   Varien_Object $brand brand
     * @return  string
     */
    public function getProductRequestPath($product, $brand)
    {
        if ($product->getUrlKey() == '') {
            $urlKey = $this->getProductModel()->formatUrlKey($product->getName());
        } else {
            $urlKey = $this->getProductModel()->formatUrlKey($product->getUrlKey());
        }
        $storeId = $brand->getStoreId();
        $suffix  = $this->getProductUrlSuffix($storeId);
        $idPath  = $this->generatePath('id', $product, $brand);
        /**
         * Prepare product base request path
         */
        $requestPath = $brand->getUrlKey() . '/' . $urlKey;

        if (strlen($requestPath) > self::MAX_REQUEST_PATH_LENGTH + self::ALLOWED_REQUEST_PATH_OVERFLOW) {
            $requestPath = substr($requestPath, 0, self::MAX_REQUEST_PATH_LENGTH);
        }

        $this->_rewrite = null;
        /**
         * Check $requestPath should be unique
         */
        if (isset($this->_rewrites[$idPath])) {
            $this->_rewrite = $this->_rewrites[$idPath];
            $existingRequestPath = $this->_rewrites[$idPath]->getRequestPath();

            if ($existingRequestPath == $requestPath . $suffix) {
                return $existingRequestPath;
            }

            $existingRequestPath = preg_replace('/' . preg_quote($suffix, '/') . '$/', '', $existingRequestPath);
            /**
             * Check if existing request past can be used
             */
            if ($product->getUrlKey() == '' && !empty($requestPath)
                && strpos($existingRequestPath, $requestPath) === 0
            ) {
                $existingRequestPath = preg_replace(
                    '/^' . preg_quote($requestPath, '/') . '/', '', $existingRequestPath
                );
                if (preg_match('#^-([0-9]+)$#i', $existingRequestPath)) {
                    return $this->_rewrites[$idPath]->getRequestPath();
                }
            }

            $fullPath = $requestPath.$suffix;
            if ($this->_deleteOldTargetPath($fullPath, $idPath, $storeId)) {
                return $fullPath;
            }
        }
        /**
         * Check 2 variants: $requestPath and $requestPath . '-' . $productId
         */
        $validatedPath = $this->getResource()->checkRequestPaths(
            array($requestPath.$suffix, $requestPath.'-'.$product->getId().$suffix),
            $storeId
        );

        if ($validatedPath) {
            return $validatedPath;
        }
        /**
         * Use unique path generator
         */
        return $this->getUnusedPath($storeId, $requestPath.$suffix, $idPath);
    }

    /**
     * Generate either id path, request path or target path for product and/or brand
     *
     * For generating id or system path, either product or brand is required
     * For generating request path - brand is required
     * $parentPath used only for generating brand path
     *
     * @param string $type type
     * @param Varien_Object $product product
     * @param Varien_Object $brand brand
     * @return string
     * @throws Mage_Core_Exception
     */
    public function generatePath($type = 'target', $product = null, $brand = null)
    {
        if (!$product && !$brand) {
            Mage::throwException(Mage::helper('core')->__('Please specify either a brand or a product, or both.'));
        }

        // generate id_path
        if ('id' === $type) {
            if (!$product) {
                return 'astrio_brand/' . $brand->getId();
            }
            if ($brand) {
                return 'astrio_brand/product/' . $product->getId();
            }
            return 'product/' . $product->getId();
        }

        // generate request_path
        if ('request' === $type) {
            // for brand
            if (!$product) {
                if ($brand->getUrlKey() == '') {
                    $urlKey = $this->getBrandModel()->formatUrlKey($brand->getName());
                } else {
                    $urlKey = $this->getBrandModel()->formatUrlKey($brand->getUrlKey());
                }

                $brandUrlSuffix = $this->getBrandUrlSuffix($brand->getStoreId());

                return $this->getUnusedPath($brand->getStoreId(), $urlKey . $brandUrlSuffix,
                    $this->generatePath('id', null, $brand)
                );
            }

            // for product & brand
            if (!$brand) {
                Mage::throwException(Mage::helper('core')->__('A brand object is required for determining the product request path.')); // why?
            }

            if ($product->getUrlKey() == '') {
                $urlKey = $this->getProductModel()->formatUrlKey($product->getName());
            } else {
                $urlKey = $this->getProductModel()->formatUrlKey($product->getUrlKey());
            }
            $productUrlSuffix  = $this->getProductUrlSuffix($brand->getStoreId());

            return $this->getUnusedPath($brand->getStoreId(), $brand->getUrlKey() . '/' . $urlKey . $productUrlSuffix,
                $this->generatePath('id', $product, $brand)
            );
        }

        // generate target_path
        if (!$product) {
            return 'astrio_brand/brand/view/id/' . $brand->getId();
        }
        if ($brand) {
            return 'catalog/product/view/id/' . $product->getId() . '/brand/' . $brand->getId();
        }
        return 'catalog/product/view/id/' . $product->getId();
    }

    /**
     * Return unique string based on the time in microseconds.
     *
     * @return string
     */
    public function generateUniqueIdPath()
    {
        return str_replace('0.', '', str_replace(' ', '_', microtime()));
    }

    /**
     * Create Custom URL Rewrite for old product/brand URL after url_key changed
     * It will perform permanent redirect from old URL to new URL
     *
     * @param array $rewriteData New rewrite data
     * @param Varien_Object $rewrite Rewrite model
     * @return Astrio_Brand_Model_Url
     */
    protected function _saveRewriteHistory($rewriteData, $rewrite)
    {
        if ($rewrite instanceof Varien_Object && $rewrite->getId()) {
            $rewriteData['target_path'] = $rewriteData['request_path'];
            $rewriteData['request_path'] = $rewrite->getRequestPath();
            $rewriteData['id_path'] = $this->generateUniqueIdPath();
            $rewriteData['is_system'] = 0;
            $rewriteData['options'] = 'RP'; // Redirect = Permanent
            $this->getResource()->saveRewriteHistory($rewriteData);
        }

        return $this;
    }
}
