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
 * @var Mage_Catalog_Helper_Product
 *
 * Astrio
 *
 * @category Astrio
 * @package Astrio_Brand
 * @author Eldaniz Gasymov <e.gasymov@astrio.net>
 */
class Astrio_Brand_Helper_Brand extends Mage_Core_Helper_Url
{
    // XML config path for seo brand url suffix
    const XML_PATH_SEO_BRAND_URL_SUFFIX         = 'astrio_brand/seo/brand_url_suffix';
    // XML config path for seo product url use brand
    const XML_PATH_SEO_PRODUCT_URL_USE_BRAND    = 'astrio_brand/seo/product_use_brands';
    // XML config path for seo use brand canonical tag
    const XML_PATH_SEO_USE_BRAND_CANONICAL_TAG  = 'astrio_brand/seo/use_canonical_tag';

    /**
     * Cache for product rewrite suffix
     *
     * @var array
     */
    protected $_brandUrlSuffix = array();

    /**
     * Retrieve brand rewrite suffix for store
     *
     * @param int $storeId store id
     * @return string
     */
    public function getBrandUrlSuffix($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }

        if (!isset($this->_brandUrlSuffix[$storeId])) {
            $suffix = Mage::getStoreConfig(self::XML_PATH_SEO_BRAND_URL_SUFFIX, $storeId);
            $suffix = ltrim($suffix, '.');
            $suffix = $suffix ? '.' . $suffix : '';
            $this->_brandUrlSuffix[$storeId] = $suffix;
        }
        return $this->_brandUrlSuffix[$storeId];
    }

    /**
     * Gets if brand for product url should be used
     *
     * @param int|null $storeId store id
     * @return bool
     */
    public function shouldUseBrandForProductUrl($storeId = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_SEO_PRODUCT_URL_USE_BRAND, $storeId);
    }

    /**
     * Check if <link rel="canonical"> can be used for brand
     *
     * @param int|null $store store id
     * @return bool
     */
    public function canUseCanonicalTag($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_SEO_USE_BRAND_CANONICAL_TAG, $store);
    }
}