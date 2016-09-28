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
class Astrio_News_Controller_Router extends Mage_Core_Controller_Varien_Router_Abstract
{

    /**
     * Initialize Controller Router
     *
     * @param Varien_Event_Observer $observer observer
     * @return $this
     */
    public function initControllerRouters($observer)
    {
        /**
         * @var $front Mage_Core_Controller_Varien_Front
         */
        $front = $observer->getEvent()->getData('front');
        $front->addRouter('news', $this);
        return $this;
    }

    /**
     * Get url key
     *
     * @param string $part part
     * @param string $suffix suffix
     * @return bool|string
     */
    protected function _getUrlKey($part, $suffix)
    {
        if ($suffix) {
            $pos = strrpos($part, $suffix);
            if (!$pos) {
                return false;
            }

            $validPos = strlen($part) - strlen($suffix);
            if ($pos != $validPos) {
                return false;
            }

            $part = substr($part, 0, $pos);
        }

        return $part;
    }

    /**
     * Check news identifier
     *
     * @param string $urlKey url key
     * @return int
     */
    protected function _checkNewsIdentifier($urlKey)
    {
        /**
         * @var $newsModel Astrio_News_Model_News
         */
        $newsModel = Mage::getModel('astrio_news/news');
        return $newsModel->checkIdentifier($urlKey, Mage::app()->getStore()->getId());
    }

    /**
     * Check category identifier
     *
     * @param string $urlKey url key
     * @return int
     */
    protected function _checkCategoryIdentifier($urlKey)
    {
        /**
         * @var $categoryModel Astrio_News_Model_Category
         */
        $categoryModel = Mage::getModel('astrio_news/category');
        return $categoryModel->checkIdentifier($urlKey, Mage::app()->getStore()->getId());
    }

    /**
     * Check category assigned to news
     *
     * @param string|int $newsId news id
     * @param Astrio_News_Model_Category $categoryId category id
     * @return bool
     */
    protected function _checkCategoryAssignedToNews($newsId, $categoryId)
    {
        /**
         * @var $newsResource Astrio_News_Model_Resource_News
         */
        $newsResource = Mage::getResourceModel('astrio_news/news');
        return in_array($categoryId, $newsResource->getCategoryIds($newsId));
    }

    /**
     * Validate and Match Cms Page and modify request
     *
     * @param Zend_Controller_Request_Http $request request
     * @return bool
     */
    public function match(Zend_Controller_Request_Http $request)
    {
        if (!Mage::isInstalled()) {
            Mage::app()->getFrontController()->getResponse()
                ->setRedirect(Mage::getUrl('install'))
                ->sendResponse();
            exit;
        }

        $identifier = trim($request->getPathInfo(), '/');

        if (!$identifier) {
            return false;
        }

        /**
         * @var $helper Astrio_News_Helper_Data
         */
        $helper = Mage::helper('astrio_news');
        $route = $helper->getRouteUrlKey();

        if (!$route) {
            return false;
        }

        $suffix = $helper->getUrlSuffix();

        $listRequestPath = $route . $suffix;

        //is news homepage
        if ($identifier == $listRequestPath) {
            $request->setModuleName('astrio_news')
                ->setControllerName('index')
                ->setActionName('index');
            $request->setAlias(
                Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
                $identifier
            );

            return true;
        }

        $parts = explode('/', $identifier);
        $patsCount = count($parts);

        //not news inner page.
        if ($patsCount < 2 || $parts[0] != $route) {
            return false;
        }

        //or news page, or router not match
        if ($patsCount == 2) {
            $newsUrlKey = $this->_getUrlKey($parts[1], $suffix);
            if (!$newsUrlKey) {
                return false;
            }

            $newsId = $this->_checkNewsIdentifier($newsUrlKey);
            if (!$newsId) {
                return false;
            }

            $request->setModuleName('astrio_news')
                ->setControllerName('news')
                ->setActionName('view')
                ->setParam('id', $newsId);
            $request->setAlias(
                Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
                $identifier
            );

            return true;
        }

        if ($categoryRoute = $helper->getCategoryRouteUrlKey()) {
            //category page or news in category page
            if ($parts[1] == $categoryRoute) {
                //category page
                if ($patsCount == 3) {
                    $categoryUrlKey = $this->_getUrlKey($parts[2], $suffix);
                    if (!$categoryUrlKey) {
                        return false;
                    }

                    $categoryId = $this->_checkCategoryIdentifier($categoryUrlKey);

                    if (!$categoryId) {
                        return false;
                    }

                    $request->setModuleName('astrio_news')
                        ->setControllerName('category')
                        ->setActionName('view')
                        ->setParam('id', $categoryId);
                    $request->setAlias(
                        Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
                        $identifier
                    );

                    return true;
                }

                //news in category page
                if ($patsCount == 4) {
                    $categoryUrlKey = $parts[2];
                    if (!$categoryUrlKey) {
                        return false;
                    }

                    $newsUrlKey = $this->_getUrlKey($parts[3], $suffix);
                    if (!$newsUrlKey) {
                        return false;
                    }

                    $categoryId = $this->_checkCategoryIdentifier($categoryUrlKey);
                    if (!$categoryId) {
                        return false;
                    }

                    $newsId = $this->_checkNewsIdentifier($newsUrlKey);
                    if (!$newsId) {
                        return false;
                    }

                    if (!$this->_checkCategoryAssignedToNews($newsId, $categoryId)) {
                        return false;
                    }

                    $request->setModuleName('astrio_news')
                        ->setControllerName('news')
                        ->setActionName('view')
                        ->setParam('id', $newsId)
                        ->setParam('category', $categoryId);
                    $request->setAlias(
                        Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
                        $identifier
                    );

                    return true;
                }

                return false;
            }
        }

        return false;
    }
}
