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
 * @package Astrio_Core
 * @author Eldaniz Gasymov <e.gasymov@astrio.net>
 */
class Astrio_Core_Helper_Cart extends Mage_Core_Helper_Abstract
{
    // xml config path for if ajax cart enabled
    const XML_PATH_CART_AJAX_ENABLED = 'astrio_core/cart_ajax/enabled';

    /**
     * form key
     */
    protected $_formKey = null;

    /**
     * current url encoded
     */
    protected $_urlEncoded = null;

    /**
     * Get checkout session
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get core session
     *
     * @return Mage_Core_Model_Session
     */
    public function getCoreSession()
    {
        return Mage::getSingleton('core/session');
    }

    /**
     * Get core helper
     *
     * @return Mage_Core_Helper_Data
     */
    public function getCoreHelper()
    {
        return Mage::helper('core');
    }

    /**
     * Get core url helper
     *
     * @return Mage_Core_Helper_Url
     */
    public function getCoreUrlHelper()
    {
        return Mage::helper('core/url');
    }

    /**
     * Get quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->getCheckoutSession()->getQuote();
    }

    /**
     * Get form key
     *
     * @return string
     */
    protected function _getFormKey()
    {
        if ($this->_formKey === null) {
            $this->_formKey = $this->getCoreSession()->getFormKey();
        }
        return $this->_formKey;
    }

    /**
     * Get url encoded
     *
     * @return string
     */
    protected function _getUrlEncoded()
    {
        if ($this->_urlEncoded === null) {
            $this->_urlEncoded = $this->getCoreUrlHelper()->getEncodedUrl();
        }

        return $this->_urlEncoded;
    }

    /**
     * Get if is cart ajax enabled
     *
     * @param null|string|int $store store id
     * @return bool
     */
    public function isCartAjaxEnabled($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_CART_AJAX_ENABLED, $store);
    }

    /**
     * Get add to cart by ajax url
     *
     * @param Mage_Catalog_Model_Product $product product
     * @param array $params params
     * @return string
     */
    public function getAddToCartByAjaxUrl(Mage_Catalog_Model_Product $product, $params = array())
    {
        $routeParams = array(
            Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $this->_getUrlEncoded(),
            'product' => $product->getId(),
            Mage_Core_Model_Url::FORM_KEY => $this->_getFormKey()
        );

        return Mage::getUrl('astrio_core/cart/addByAjax', $routeParams + $params);
    }

    /**
     * Product is already added in cart or not?
     *
     * @param Mage_Catalog_Model_Product|int $productId product id
     * @return bool
     */
    public function isProductInCart($productId)
    {
        if ($productId instanceof Mage_Catalog_Model_Product) {
            $productId = $productId->getId();
        }

        /**
         * @var $item Mage_Sales_Model_Quote_Item
         */

        $quote = $this->getQuote();
        foreach ($quote->getAllVisibleItems() as $item) {
            if ($item->getProductId() == $productId) {
                return true;
            }
        }

        return false;
    }
}