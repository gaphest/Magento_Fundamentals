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
class Astrio_News_Helper_Data extends Mage_Core_Helper_Abstract
{

    // xml config path for seo route url key
    const XML_PATH_SEO_ROUTE_URL_KEY            = 'astrio_news/seo/route_url_key';
    // xml config path for seo category route url key
    const XML_PATH_SEO_CATEGORY_ROUTE_URL_KEY   = 'astrio_news/seo/category_route_url_key';
    // xml config path for seo url suffix
    const XML_PATH_SEO_URL_SUFFIX               = 'astrio_news/seo/url_suffix';
    // xml config path for seo list meta title
    const XML_PATH_SEO_LIST_META_TITLE          = 'astrio_news/seo/list_meta_title';
    // xml config path for seo list meta keywords
    const XML_PATH_SEO_LIST_META_KEYWORDS       = 'astrio_news/seo/list_meta_keywords';
    // xml config path for seo list meta description
    const XML_PATH_SEO_LIST_META_DESCRIPTION    = 'astrio_news/seo/list_meta_description';
    // xml config path for seo use canonical tag
    const XML_PATH_SEO_USE_CANONICAL_TAG        = 'astrio_news/seo/use_canonical_tag';

    protected $_routeUrlKey = array();

    protected $_categoryRouteUrlKey = array();

    /**
     * Cache for news rewrite suffix
     *
     * @var array
     */
    protected $_urlSuffix = array();

    /**
     * Retrieve news route url key
     *
     * @param mixed $store store id
     * @return string
     */
    public function getRouteUrlKey($store = null)
    {
        $storeId = Mage::app()->getStore($store)->getId();

        if (!isset($this->_routeUrlKey[$storeId])) {
            $this->_routeUrlKey[$storeId] = Mage::getStoreConfig(self::XML_PATH_SEO_ROUTE_URL_KEY, $storeId);
        }

        return $this->_routeUrlKey[$storeId];
    }

    /**
     * Retrieve news route url key
     *
     * @param mixed $store store id
     * @return string
     */
    public function getCategoryRouteUrlKey($store = null)
    {
        $storeId = Mage::app()->getStore($store)->getId();

        if (!isset($this->_categoryRouteUrlKey[$storeId])) {
            $this->_categoryRouteUrlKey[$storeId] = Mage::getStoreConfig(self::XML_PATH_SEO_CATEGORY_ROUTE_URL_KEY, $storeId);
        }

        return $this->_categoryRouteUrlKey[$storeId];
    }

    /**
     * Retrieve news rewrite suffix for store
     *
     * @param mixed $store store id
     * @return string
     */
    public function getUrlSuffix($store = null)
    {
        $storeId = Mage::app()->getStore($store)->getId();

        if (!isset($this->_urlSuffix[$storeId])) {
            $suffix = Mage::getStoreConfig(self::XML_PATH_SEO_URL_SUFFIX, $storeId);
            $suffix = ltrim($suffix, '.');
            $suffix = $suffix ? '.' . $suffix : '';
            $this->_urlSuffix[$storeId] = $suffix;
        }
        
        return $this->_urlSuffix[$storeId];
    }

    /**
     * Get list meta title
     *
     * @param string|int|null $store store id
     * @return mixed
     */
    public function getListMetaTitle($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_SEO_LIST_META_TITLE, $store);
    }

    /**
     * Get list meta keywords
     *
     * @param string|int|null $store store id
     * @return mixed
     */
    public function getListMetaKeywords($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_SEO_LIST_META_KEYWORDS, $store);
    }

    /**
     * Get list meta description
     *
     * @param string|int|null $store store id
     * @return mixed
     */
    public function getListMetaDescription($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_SEO_LIST_META_DESCRIPTION, $store);
    }

    /**
     * Check if <link rel="canonical"> can be used for news
     *
     * @param string|int|null $store store id
     * @return bool
     */
    public function canUseCanonicalTag($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_SEO_USE_CANONICAL_TAG, $store);
    }

    /**
     * Get list url
     *
     * @param null|string|int $store store id
     * @return string
     */
    public function getListUrl($store = null)
    {
        $params = array();
        if ($route = $this->getRouteUrlKey($store)) {
            $params['_direct'] = $route . $this->getUrlSuffix($store);
        }

        return Mage::getUrl('astrio_news/news/list', $params);
    }

    /**
     * Get active category id
     *
     * @return bool|mixed
     */
    public function getActiveCategoryId()
    {
        $category = Mage::registry('news_category');
        if ($category instanceof Astrio_News_Model_Category) {
            return $category->getId();
        }

        return false;
    }

    /**
     * Is news main page
     *
     * @return bool
     */
    public function isNewsMainPage()
    {
        $request = $this->_getRequest();

        return $request->getModuleName() == 'astrio_news'
            && $request->getControllerName() == 'index'
            && $request->getActionName() == 'index';
    }
}
