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
interface Astrio_Brand_Helper_Brand_Url_Rewrite_Interface
{
    /**
     * Prepare and return select
     *
     * @param array $brandIds brand ids
     * @param int $storeId store id
     * @return Varien_Db_Select
     */
    public function getTableSelect(array $brandIds, $storeId);

    /**
     * Prepare and return select for products
     *
     * @param array $productIds product ids
     * @param int $brandId brand id
     * @param int $storeId store id
     * @return mixed
     */
    public function getTableSelectForProductCollection(array $productIds, $brandId, $storeId);

    /**
     * Get Brands List URL
     *
     * @param null $storeId store id
     * @return string
     */
    public function getBrandsListUrl($storeId = null);

    /**
     * @param null|int $storeId store id
     * @return Astrio_Brand_Helper_Brand_Url_Rewrite_Interface
     */
    public function createBrandsListUrlRewrite($storeId = null);

    /**
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection collection
     * @param int $brandId brand id
     * @return mixed
     */
    public function addProductUrlRewrites($collection, $brandId);
}