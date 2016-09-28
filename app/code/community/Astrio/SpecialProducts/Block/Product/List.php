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
 * @package    Astrio_SpecialProducts
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Astrio
 *
 * @category   Astrio
 * @package    Astrio_SpecialProducts
 * @author     Eldaniz Gasymov <e.gasymov@astrio.net>
 */
class Astrio_SpecialProducts_Block_Product_List extends Mage_Catalog_Block_Product_List
{

    protected $_productCollection = null;

    /**
     * Get layer
     *
     * @return Astrio_SpecialProducts_Model_Layer
     */
    public function getLayer()
    {
        $layer = Mage::registry('current_layer');
        if ($layer) {
            return $layer;
        }

        return Mage::getSingleton('astrio_specialproducts/layer');
    }

    /**
     * Get loaded product collection
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection|null
     */
    public function getLoadedProductCollection()
    {
        if ($this->_productCollection === null) {
            $layer = $this->getLayer();
            $this->_productCollection = $layer->getProductCollection();
        }

        return $this->_productCollection;
    }

    /**
     * Get product collection
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection|null
     */
    protected function _getProductCollection()
    {
        return $this->getLoadedProductCollection();
    }
}
