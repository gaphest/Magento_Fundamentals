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
 *
 * Astrio
 *
 * @category Astrio
 * @package Astrio_Brand
 * @author Eldaniz Gasymov <e.gasymov@astrio.net>
 * @see Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs
 */
class Astrio_Brand_Block_Adminhtml_Brand_Edit_Js extends Mage_Adminhtml_Block_Template
{
    /**
     * Get currently edited brand
     *
     * @return Astrio_Brand_Model_Brand
     */
    public function getBrand()
    {
        return Mage::registry('current_brand');
    }

    /**
     * Get store object of curently edited product
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        $brand = $this->getBrand();
        if ($brand) {
            return Mage::app()->getStore($brand->getStoreId());
        }
        return Mage::app()->getStore();
    }
}
