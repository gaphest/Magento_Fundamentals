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
class Astrio_Brand_Block_Brand_Product_List extends Mage_Catalog_Block_Product_List
{

    protected $_productCollection = null;

    /**
     * @return Astrio_Brand_Model_Brand|null
     */
    public function getBrand()
    {
        $brand = Mage::registry('current_brand');
        if ($brand instanceof Astrio_Brand_Model_Brand && $brand->canShow()) {
            return $brand;
        }

        return null;
    }

    /**
     * @return Astrio_Brand_Model_Layer
     */
    public function getLayer()
    {
        $layer = Mage::registry('current_layer');
        if ($layer) {
            return $layer;
        }

        return Mage::getSingleton('astrio_brand/layer');
    }

    /**
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getLoadedProductCollection()
    {
        if (is_null($this->_productCollection)) {
            $layer = $this->getLayer();
            $this->_productCollection = $layer->getProductCollection();
        }

        return $this->_productCollection;
    }

    /**
     * Gets product collection
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _getProductCollection()
    {
        return $this->getLoadedProductCollection();
    }
}