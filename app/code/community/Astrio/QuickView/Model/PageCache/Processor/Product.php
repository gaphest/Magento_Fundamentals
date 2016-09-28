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
 * Product quick view view processor
 *
 * @category   Astrio
 * @package    Astrio_QuickView
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_QuickView_Model_PageCache_Processor_Product extends Enterprise_PageCache_Model_Processor_Default
{
    /**
     * Key for saving product id in metadata
     */
    const METADATA_PRODUCT_ID = 'current_product_id';

    /**
     * Prepare response body before caching
     *
     * @param Zend_Controller_Response_Http $response response
     * @return string
     */
    public function prepareContent(Zend_Controller_Response_Http $response)
    {
        $cacheInstance = Enterprise_PageCache_Model_Cache::getCacheInstance();

        /** @var Enterprise_PageCache_Model_Processor */
        $processor = Mage::getSingleton('enterprise_pagecache/processor');

        // save current product id
        $product = Mage::registry('current_product');
        if ($product) {
            $cacheId = $processor->getRequestCacheId() . '_current_product_id';
            $cacheInstance->save($product->getId(), $cacheId, array(Enterprise_PageCache_Model_Processor::CACHE_TAG));
            $processor->setMetadata(self::METADATA_PRODUCT_ID, $product->getId());
        }

        return parent::prepareContent($response);
    }

    /**
     * Return cache page id without application. Depends on GET super global array.
     *
     * @param Enterprise_PageCache_Model_Processor $processor processor
     * @return string
     */
    public function getPageIdWithoutApp(Enterprise_PageCache_Model_Processor $processor)
    {
        $pageId = parent::getPageIdWithoutApp($processor);
        return $this->_appendCustomerRatesToPageId($pageId);
    }
}
