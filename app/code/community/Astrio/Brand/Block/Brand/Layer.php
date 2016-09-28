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
class Astrio_Brand_Block_Brand_Layer extends Mage_CatalogSearch_Block_Layer
{
    /**
     * Get layer object
     *
     * @return Astrio_Brand_Model_Layer
     */
    public function getLayer()
    {
        return Mage::getSingleton('astrio_brand/layer');
    }

    /**
     * Check availability display layer block
     *
     * @return bool
     */
    public function canShowBlock()
    {
        $_isLNAllowedByEngine = Mage::helper('catalogsearch')->getEngine()->isLeyeredNavigationAllowed();
        if (!$_isLNAllowedByEngine) {
            return false;
        }
        return Mage_Catalog_Block_Layer_View::canShowBlock();
    }
}