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
 * @package    Astrio_QuickView
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Index controller
 *
 * @category   Astrio
 * @package    Astrio_QuickView
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_QuickView_IndexController extends Mage_Core_Controller_Front_Action
{

    /**
     * Ajax product quick view action
     *
     * @return $this|Mage_Core_Controller_Varien_Action
     */
    public function viewAction()
    {
        $result = array('success' => false, 'error' => false);
        $productId = (int) $this->getRequest()->getParam('id');
        $isAjax = $this->getRequest()->isAjax();

        if (!$productId) {
            if (!$isAjax) {
                return $this->_redirect('');
            }

            $result['error'] = true;
            $result['error_text'] = Mage::helper('astrio_quickview')->__('Product ID is not specified!');
            return $this->_sendJson($result);
        }

        /** @var  Astrio_QuickView_Helper_Product_View $productViewHelper */
        $productViewHelper = Mage::helper('astrio_quickview/product_view');

        try {
            $productViewHelper->prepareLayout($productId, $this);
        } catch(Mage_Core_Exception $e) {
            $result['error'] = true;
            $result['error_text'] = $e->getMessage();
        } catch(Exception $e) {
            $result['error'] = true;
            $result['error_text'] = Mage::helper('astrio_quickview')->__('Internal error occurred');
        }

        if ($result['error']) {
            if (!$isAjax) {
                return $this->_redirect('');
            }

            return $this->_sendJson($result);
        }


        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::registry('current_product') ? Mage::registry('current_product') : false;

        if (!$isAjax) {
            return $product ? $this->_redirect($product->getProductUrl()) : $this->_redirect('');
        }

        $result['blocks'] = array();
        $layout = $this->getLayout();
        foreach ($this->_getRenderBlocks() as $blockName) {
            if ($block = $layout->getBlock($blockName)) {
                $html = $block->toHtml();
                $result['blocks'][$blockName] = $this->_prepareBlockHtml($blockName,$block, $html) ;
            }
        }

        $result['success'] = true;

        return $this->_sendJson($result);
    }


    /**
     * prepare block html
     *
     * @param string                   $blockName block name
     * @param Mage_Core_Block_Template $block     block
     * @param string                   $html      html
     * @return mixed
     */
    protected function _prepareBlockHtml($blockName, $block, $html)
    {
        $html = str_replace('product-price-', 'product-price-qv-', $html);
        $html = $this->_replaceUenc($html);
        return $html;
    }

    /**
     * Calculate UENC parameter value and replace it
     *
     * @param string $html html
     * @return string
     */
    protected function _replaceUenc($html)
    {
        /** @var  Mage_Core_Helper_Url $urlHelper */
        $urlHelper = Mage::helper('core/url');

        $refererUrl = $this->_getRefererUrl();
        $currentUrlEncoded = false;

        $currentUrl = $this->getRequest()->getParam('currentUrl');

        if ($this->_isUrlInternal($currentUrl)) {
            $currentUrlEncoded = $urlHelper->urlEncode($currentUrl);
        }

        if (!$currentUrlEncoded && $this->_isUrlInternal($refererUrl)) {
            $currentUrlEncoded = $urlHelper->urlEncode($refererUrl);
        }

        $product = $this->_getProduct();

        if (!$currentUrlEncoded && $product) {
            $currentUrlEncoded = $urlHelper->getEncodedUrl($product->getProductUrl());
        }

        $search = '/\/(' . Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED . ')\/[^\/]*\//';
        $replace = $currentUrlEncoded ? ('/$1/' . $currentUrlEncoded. '/') : '/';
        $content = preg_replace($search, $replace, $html);
        return $content;
    }



    /**
     * Check url to be used as internal
     *
     * @param   string $url url
     * @return  bool
     */
    protected function _isUrlInternal($url)
    {
        if (strpos($url, 'http') !== false) {
            /**
             * Url must start from base secure or base unsecure url
             */
            if ((strpos($url, Mage::app()->getStore()->getBaseUrl()) === 0)
                || (strpos($url, Mage::app()->getStore()->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, true)) === 0)
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Send json response
     *
     * @param string $result result
     * @return $this
     */
    protected function _sendJson($result)
    {
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        return $this;
    }


    /**
     * get blocks to render
     *
     * @return array
     */
    protected function _getRenderBlocks()
    {
        return array('product.info');
    }

    /**
     * @return Mage_Catalog_Model_Product
     */
    protected function _getProduct()
    {
        return Mage::registry('current_product');
    }
}