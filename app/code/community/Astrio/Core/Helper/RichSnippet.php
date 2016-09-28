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
class Astrio_Core_Helper_RichSnippet extends Mage_Core_Helper_Abstract
{
    // xml config path for rich snippets google plus account link
    const XML_PATH_RICH_SNIPPETS_GOOGLE_PLUS_ACCOUNT_LINK = 'astrio_core/rich_snippets/google_plus_account_link';

    /**
     * Parse price
     *
     * @param string $html html
     * @return float
     */
    protected function _parsePrice($html)
    {
        $html = str_replace(',', '.', trim($html));
        $html = rtrim($html, '.');
        $html = preg_replace('/[^0-9.]/','', $html);
        return floatval($html);
    }

    /**
     * add itemprop="price" for rich snippets for price html
     *
     * @param string $html html
     * @param string $conditionSearch search condition
     * @param string $search search
     * @param string $replace replace
     * @return bool|string
     */
    protected function _replaceFrom($html, $conditionSearch, $search, $replace)
    {
        $p = mb_strpos($html, $conditionSearch, null, 'UTF-8');
        if ($p !== false) {
            $b = mb_strpos($html, $search, $p, 'UTF-8');
            if ($b !== false) {
                $before = mb_substr($html, 0, $b, 'UTF-8');

                $searchLength = mb_strlen($search, 'UTF-8');
                $after = mb_substr($html, $b + $searchLength, mb_strlen($html, 'UTF-8') - $b - $searchLength, 'UTF-8');

                return $before . $replace . $after;
            }
        }

        return false;
    }

    /**
     * add itemprop="price" for rich snippets for price html
     *
     * @param string $html html
     * @param Mage_Catalog_Model_Product $product product
     * @return string
     */
    protected function _addItemPropPrice($html, Mage_Catalog_Model_Product $product)
    {
        $priceValue = $this->_getFinalPrice($product);
        if ($_html = $this->_replaceFrom($html, '<span class="price" id="price-including-tax-', '<span', '<span itemprop="price" content="' . $priceValue . '"')) {
            return $_html;
        }

        if ($_html = $this->_replaceFrom($html, '<span class="price" id="product-price-', '<span', '<span itemprop="price" content="' . $priceValue . '"')) {
            return $_html;
        }

        if ($_html = $this->_replaceFrom($html, '<span class="regular-price" id="product-price-', '<span class="price">', '<span itemprop="price" content="' . $priceValue . '" class="price">')) {
            return $_html;
        }

        if ($_html = $this->_replaceFrom($html, '<span class="price" id="product-minimal-price-', '<span', '<span itemprop="price" content="' . $priceValue . '"')) {
            return $_html;
        }

        return str_replace('class="price"', 'class="price" itemprop="price"', $html);
    }

    /**
     * Get final price
     *
     * @param Mage_Catalog_Model_Product $product product
     * @return string
     */
    protected function _getFinalPrice(Mage_Catalog_Model_Product $product)
    {
        /**
         * @var $taxConfig Mage_Tax_Model_Config
         */
        $taxConfig = Mage::getSingleton('tax/config');

        $finalPrice = $product->getFinalPrice();
        if ($taxConfig->getPriceDisplayType() != Mage_Tax_Model_Config::DISPLAY_TYPE_EXCLUDING_TAX) {
            /**
             * @var $taxHelper Mage_Tax_Helper_Data
             */
            $taxHelper = Mage::helper('tax');
            $finalPrice = $taxHelper->getPrice($product, $finalPrice);
        }

        $finalPrice = round($finalPrice, 2);
        return number_format($finalPrice, 2, '.', '');
    }

    /**
     * add Rich Snippet "Offer" (price, availability)
     *
     * @param string $html html
     * @param Mage_Catalog_Model_Product $product product
     * @return string
     */
    public function addRichSnippetOfferForPriceHtml($html, Mage_Catalog_Model_Product $product)
    {
        $availability = $product->isAvailable() ? 'InStock' : 'OutOfStock';
        $availabilityLink = '<link itemprop="availability" href="http://schema.org/' . $availability . '" />';
        $priceCurrency = '<link itemprop="priceCurrency" content="' . Mage::app()->getStore()->getCurrentCurrencyCode() . '" />';
        $replacedHtml = $this->_replaceFrom($html, '<div class="price-box">', '<div class="price-box">', '<div class="price-box" itemprop="offers" itemscope itemtype="http://schema.org/Offer">' . $availabilityLink . $priceCurrency);
        if (!$replacedHtml) {
            return $html;
        }
        return $this->_addItemPropPrice($replacedHtml, $product);
    }

    /**
     * Get google plus account link
     *
     * @return mixed
     */
    public function getGooglePlusAccountLink()
    {
        return Mage::getStoreConfig(self::XML_PATH_RICH_SNIPPETS_GOOGLE_PLUS_ACCOUNT_LINK);
    }
}