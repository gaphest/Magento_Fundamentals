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

require_once Mage::getModuleDir('controllers', 'Mage_Checkout'). DS . 'CartController.php';

/**
 * Astrio
 *
 * @category Astrio
 * @package Astrio_Core
 * @author Eldaniz Gasymov <e.gasymov@astrio.net>
 */
class Astrio_Core_CartController extends Mage_Checkout_CartController
{
    /**
     * Rewrites default method: we want rewrite only 1 action. others will be loaded from default controller!
     *
     * @param string $action action
     * @return bool
     */
    public function hasAction($action)
    {
        $actions = array(
            'addByAjax',
        );

        if (!in_array($action, $actions)) {
            return false;
        }

        return parent::hasAction($action);
    }

    /**
     * Add product to shopping cart action
     *
     * @return Mage_Core_Controller_Varien_Action
     * @throws Exception
     */
    public function addByAjaxAction()
    {
        $cart   = $this->_getCart();
        $params = $this->getRequest()->getParams();

        $success = true;
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');

            /**
             * Check product availability
             */
            if (!$product) {
                throw new Mage_Core_Exception($this->__('The product was not found.'));
            }

            $cart->addProduct($product, $params);
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();

            $this->_getSession()->setCartWasUpdated(true);

            /**
             * @todo remove wishlist observer processAddToCart
             */
            Mage::dispatchEvent('checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()) {
                    $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));
                    $this->_getSession()->addSuccess($message);
                }
            }
        } catch (Mage_Core_Exception $e) {
            $success = false;
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
                }
            }
        } catch (Exception $e) {
            $success = false;
            $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
            Mage::logException($e);
        }

        $result = array(
            'success' => $success,
            'message' => $this->_getAllMessages(),
        );

        if (!$success) {
            if (
                !empty($product)
                && $product instanceof Mage_Catalog_Model_Product
                && $product->getId()
                && is_array($product->getWebsiteIds())
                && in_array(Mage::app()->getStore()->getWebsiteId(), $product->getWebsiteIds())
                && $product->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_ENABLED
                && $product->isVisibleInSiteVisibility()
            ) {
                $url = $product->getProductUrl();
            } else {
                $url = $this->_getSession()->getRedirectUrl(true);
                if (!$url) {
                    $url = $this->_getRefererUrl();
                    if (!$url) {
                        $url = Mage::helper('checkout/cart')->getCartUrl();
                    }
                }
            }
            $result['redirect'] = $url;
        } elseif (Mage::getStoreConfig('checkout/cart/redirect_to_cart')) {
            $result['redirect'] = Mage::helper('checkout/cart')->getCartUrl();
        } else {
            $this->loadLayout();

            $result['summary_qty'] = $this->_getCart()->getSummaryQty();

            $result['blocks'] = array();

            if ($rootBlock = $this->getLayout()->getBlock('root')) {
                if ($updateBlocks = trim($rootBlock->getCartAjaxUpdateBlocks())) {
                    $updateBlocks = explode(',', $updateBlocks);
                    $urlEncoded = $this->getRequest()->getParam('uenc');
                    foreach ($updateBlocks as $blockName) {
                        if ($blockName = trim($blockName)) {
                            if ($blockInstance = $this->getLayout()->getBlock($blockName)) {
                                try {
                                    $result['blocks'][$blockName] = preg_replace("/uenc\/([a-zA-Z0-9-_,]*)/", "uenc/" . $urlEncoded, trim($blockInstance->toHtml()));
                                } catch (Exception $e) {
                                    Mage::log($e->getMessage(), Zend_Log::ERR);
                                }
                            }
                        }
                    }
                }
            }

            $this->_getSession()->getMessages()->clear();
        }

        /**
         * @var $coreHelper Mage_Core_Helper_Data
         */
        $coreHelper = Mage::helper('core');

        $this->getResponse()
            ->setHeader('Content-type', 'application/json', true)
            ->setBody($coreHelper->jsonEncode($result));
    }

    /**
     * Get all messages
     *
     * @return string
     */
    protected function _getAllMessages()
    {
        $messages = array();
        foreach ($this->_getSession()->getMessages()->getItems() as $message) {
            /** @var  Mage_Core_Model_Message_Abstract $message */
            $messages[] = $message->getText();
        }

        return implode("\n", $messages);
    }
}