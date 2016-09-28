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
 *  Product helper
 *
 * @category   Astrio
 * @package    Astrio_QuickView
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_QuickView_Helper_Product_View extends Mage_Catalog_Helper_Product_View
{

    /**
     * Inits layout for viewing product page
     *
     * @param Mage_Catalog_Model_Product        $product    product
     * @param Mage_Core_Controller_Front_Action $controller controller
     *
     * @return Mage_Catalog_Helper_Product_View
     */
    public function initProductLayout($product, $controller)
    {
        $update = $controller->getLayout()->getUpdate();

        $this->_addActionLayoutHandles($controller);

        $update->addHandle('PRODUCT_TYPE_' . $product->getTypeId());
        $update->addHandle('PRODUCT_' . $product->getId());

        $update->addHandle('PRODUCT_QV_TYPE_' . $product->getTypeId());
        $update->addHandle('PRODUCT_QV_' . $product->getId());

        $controller->loadLayoutUpdates();

        $controller->generateLayoutXml()->generateLayoutBlocks();

        return $this;
    }

    /**
     * Add action layout handles
     *
     * @param Mage_Core_Controller_Varien_Action $controller controller
     */
    protected function _addActionLayoutHandles($controller)
    {
        $update = $controller->getLayout()->getUpdate();

        // load store handle
        $update->addHandle('STORE_'.Mage::app()->getStore()->getCode());

        // load theme handle
        $package = Mage::getSingleton('core/design_package');
        $update->addHandle(
            'THEME_'.$package->getArea().'_'.$package->getPackageName().'_'.$package->getTheme('layout')
        );

        // load action handle
        $update->addHandle('catalog_product_view');
        // load action handle
        $update->addHandle(strtolower($controller->getFullActionName()));

    }

    /**
     * Prepares product view page - inits layout and all needed stuff
     *
     * $params can have all values as $params in Mage_Catalog_Helper_Product - initProduct().
     * Plus following keys:
     *   - 'buy_request' - Varien_Object holding buyRequest to configure product
     *   - 'specify_options' - boolean, whether to show 'Specify options' message
     *   - 'configure_mode' - boolean, whether we're in Configure-mode to edit product configuration
     *
     * @param int                               $productId  product id
     * @param Mage_Core_Controller_Front_Action $controller controller
     * @param null|Varien_Object                $params     params
     *
     * @return Mage_Catalog_Helper_Product_View
     * @throws Mage_Core_Exception
     */
    public function prepareLayout($productId, $controller, $params = null)
    {
        // Prepare data
        /** @var  Mage_Catalog_Helper_Product $productHelper */
        $productHelper = Mage::helper('catalog/product');
        if (!$params) {
            $params = new Varien_Object();
        }

        // Standard algorithm to prepare and rendern product view page
        $product = $productHelper->initProduct($productId, $controller, $params);
        if (!$product) {
            throw new Mage_Core_Exception($this->__('Product is not loaded'), $this->ERR_NO_PRODUCT_LOADED);
        }

        $buyRequest = $params->getBuyRequest();
        if ($buyRequest) {
            $productHelper->prepareProductOptions($product, $buyRequest);
        }

        if ($params->hasConfigureMode()) {
            $product->setConfigureMode($params->getConfigureMode());
        }

        Mage::dispatchEvent('catalog_controller_product_view', array('product' => $product));

        $this->initProductLayout($product, $controller);

        return $this;
    }
}
