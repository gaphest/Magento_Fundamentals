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
class Astrio_Brand_Helper_Data extends Mage_Core_Helper_Abstract
{
    // XML path for seo save history
    const XML_PATH_SEO_SAVE_HISTORY                     = 'astrio_brand/seo/save_rewrites_history';
    // XML path for seo brands list meta title
    const XML_PATH_SEO_BRANDS_LIST_META_TITLE           = 'astrio_brand/seo/brands_list_meta_title';
    // XML path for seo brands list meta keywords
    const XML_PATH_SEO_BRANDS_LIST_META_KEYWORDS        = 'astrio_brand/seo/brands_list_meta_keywords';
    // XML path for seo brands list meta description
    const XML_PATH_SEO_BRANDS_LIST_META_DESCRIPTION     = 'astrio_brand/seo/brands_list_meta_description';

    /**
     * Indicate whether to save URL Rewrite History or not (create redirects to old URLs)
     *
     * @param int|null $storeId Store View
     * @return bool
     */
    public function shouldSaveUrlRewritesHistory($storeId = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_SEO_SAVE_HISTORY, $storeId);
    }

    /**
     * Gets brands list meta title
     *
     * @param int|null $storeId Store View
     * @return mixed
     */
    public function getBrandsListMetaTitle($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_SEO_BRANDS_LIST_META_TITLE, $storeId);
    }

    /**
     * Gets brands list meta keywords
     *
     * @param int|null $storeId Store View
     * @return mixed
     */
    public function getBrandsListMetaKeywords($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_SEO_BRANDS_LIST_META_KEYWORDS, $storeId);
    }

    /**
     * Gets brands list meta description
     *
     * @param int|null $storeId Store View
     * @return mixed
     */
    public function getBrandsListMetaDescription($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_SEO_BRANDS_LIST_META_DESCRIPTION, $storeId);
    }

    /**
     * Get Brands List URL
     *
     * @param int|null $storeId Store View
     * @return string
     */
    public function getBrandsListUrl($storeId = null)
    {
        /**
         * @var $factory Astrio_Brand_Model_Factory
         */
        $factory = Mage::getSingleton('astrio_brand/factory');
        $urlRewriteHelper = $factory->getBrandUrlRewriteHelper();
        return $urlRewriteHelper->getBrandsListUrl($storeId);
    }

    /**
     * Get brand by product
     *
     * @param Mage_Catalog_Model_Product $product product
     * @return mixed
     * @throws Mage_Core_Exception
     */
    public function getBrandByProduct(Mage_Catalog_Model_Product $product)
    {
        /** @var Astrio_Brand_Model_Resource_Brand $brandModel */
        $brandModel = Mage::getModel('astrio_brand/brand');

        $storeId = $product->getStoreId();
        $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();

        $brand = $brandModel
            ->setStoreId($storeId)
            ->load($product->getData(Astrio_Brand_Model_Brand::ATTRIBUTE_CODE));

        if ($brand->getId()) {
            if (!$brand->isActive()) {
                $brand->unsetData();
            }
            // check if brand is associated with currently website
            if (!in_array($websiteId, $brandModel->getWebsiteIds($brand))) {
                $brand->unsetData();
            }
        }

        return $brand;
    }
}