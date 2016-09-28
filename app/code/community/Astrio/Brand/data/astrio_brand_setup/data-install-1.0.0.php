<?php
/**
 * Astrio
 *
 * @category Astrio
 * @package Astrio_Brand
 * @author Eldaniz Gasymov <e.gasymov@astrio.net>
 */

/**
 * @var $installer Astrio_Brand_Model_Resource_Setup
 */
$installer = $this;

/**
 * @var $factory Astrio_Brand_Model_Factory
 * @var $store Mage_Core_Model_Store
 */
$factory = Mage::getSingleton('astrio_brand/factory');
$urlRewriteHelper = $factory->getBrandUrlRewriteHelper();
$urlRewriteHelper->createBrandsListUrlRewrite();