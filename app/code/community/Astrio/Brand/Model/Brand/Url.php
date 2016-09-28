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
class Astrio_Brand_Model_Brand_Url extends Varien_Object
{
    // Cache tag
    const CACHE_TAG = 'url_rewrite';

    /**
     * URL instance
     *
     * @var Mage_Core_Model_Url
     */
    protected  $_url;

    /**
     * URL Rewrite Instance
     *
     * @var Mage_Core_Model_Url_Rewrite
     */
    protected $_urlRewrite;

    /**
     * Factory instance
     *
     * @var Astrio_Brand_Model_Factory
     */
    protected $_factory;

    /**
     * @var Mage_Core_Model_Store
     */
    protected $_store;

    /**
     * Initialize Url model
     *
     * @param array $args args
     */
    public function __construct(array $args = array())
    {
        $this->_factory = !empty($args['factory']) ? $args['factory'] : Mage::getSingleton('astrio_brand/factory');
        $this->_store = !empty($args['store']) ? $args['store'] : Mage::app()->getStore();
    }

    /**
     * Retrieve URL Instance
     *
     * @return Mage_Core_Model_Url
     */
    public function getUrlInstance()
    {
        if (null === $this->_url) {
            $this->_url = Mage::getModel('core/url');
        }
        return $this->_url;
    }

    /**
     * Retrieve URL Rewrite Instance
     *
     * @return Mage_Core_Model_Url_Rewrite
     */
    public function getUrlRewrite()
    {
        if (null === $this->_urlRewrite) {
            $this->_urlRewrite = $this->_factory->getUrlRewriteInstance();
        }
        return $this->_urlRewrite;
    }

    /**
     * Retrieve URL in current store
     *
     * @param Astrio_Brand_Model_Brand $brand brand
     * @param array $params the URL route params
     * @return string
     */
    public function getUrlInStore(Astrio_Brand_Model_Brand $brand, $params = array())
    {
        $params['_store_to_url'] = true;
        return $this->getUrl($brand, $params);
    }

    /**
     * Retrieve Product URL
     *
     * @param  Astrio_Brand_Model_Brand $brand brand
     * @param  bool $useSid forced SID mode
     * @return string
     */
    public function getBrandUrl($brand, $useSid = null)
    {
        if ($useSid === null) {
            $useSid = Mage::app()->getUseSessionInUrl();
        }

        $params = array();
        if (!$useSid) {
            $params['_nosid'] = true;
        }

        return $this->getUrl($brand, $params);
    }

    /**
     * Format Key for URL
     *
     * @param string $str string
     * @return string
     */
    public function formatUrlKey($str)
    {
        $urlKey = preg_replace('#[^0-9a-z]+#i', '-', Mage::helper('catalog/product_url')->format($str));
        $urlKey = strtolower($urlKey);
        $urlKey = trim($urlKey, '-');

        return $urlKey;
    }

    /**
     * Retrieve Brand URL using UrlDataObject
     *
     * @param Astrio_Brand_Model_Brand $brand brand
     * @param array $params params
     * @return string
     */
    public function getUrl(Astrio_Brand_Model_Brand $brand, $params = array())
    {
        $url = $brand->getData('url');
        if (!empty($url)) {
            return $url;
        }

        $requestPath = $brand->getData('request_path');
        if (empty($requestPath)) {
            $requestPath = $this->_getRequestPath($brand);
            $brand->setRequestPath($requestPath);
        }

        if (isset($params['_store'])) {
            $storeId = $this->_getStoreId($params['_store']);
        } else {
            $storeId = $brand->getStoreId();
        }

        if ($storeId != $this->_getStoreId()) {
            $params['_store_to_url'] = true;
        }

        // reset cached URL instance GET query params
        if (!isset($params['_query'])) {
            $params['_query'] = array();
        }

        $this->getUrlInstance()->setStore($storeId);
        $brandUrl = $this->_getBrandUrl($brand, $requestPath, $params);
        $brand->setData('url', $brandUrl);
        return $brand->getData('url');
    }

    /**
     * Returns checked store_id value
     *
     * @param int|null $id store id
     * @return int
     */
    protected function _getStoreId($id = null)
    {
        return Mage::app()->getStore($id)->getId();
    }

    /**
     * Retrieve brand URL based on requestPath param
     *
     * @param Astrio_Brand_Model_Brand $brand brand
     * @param string $requestPath required path
     * @param array $routeParams route params
     *
     * @return string
     */
    protected function _getBrandUrl($brand, $requestPath, $routeParams)
    {
        if (!empty($requestPath)) {
            return $this->getUrlInstance()->getDirectUrl($requestPath, $routeParams);
        }
        $routeParams['id'] = $brand->getId();
        $routeParams['s'] = $brand->getUrlKey();
        return $this->getUrlInstance()->getUrl('astrio_brand/brand/view', $routeParams);
    }

    /**
     * Retrieve request path
     *
     * @param Astrio_Brand_Model_Brand $brand brands
     * @return bool|string
     */
    protected function _getRequestPath($brand)
    {
        $idPath = sprintf('astrio_brand/%d', $brand->getEntityId());
        $rewrite = $this->getUrlRewrite();
        $rewrite->setStoreId($brand->getStoreId())
            ->loadByIdPath($idPath);
        if ($rewrite->getId()) {
            return $rewrite->getRequestPath();
        }

        return false;
    }
}