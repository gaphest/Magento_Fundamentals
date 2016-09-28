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
class Astrio_Brand_Model_Layer extends Mage_Catalog_Model_Layer
{

    protected $_productCollection = null;

    protected $_brand = null;

    /**
     * gets brand
     *
     * @return Astrio_Brand_Model_Brand|bool|null
     */
    public function getBrand()
    {
        if ($this->_brand === null) {
            $this->_brand = false;
            /**
             * @var $brand Astrio_Brand_Model_Brand
             */
            $brand = Mage::registry('current_brand');
            if ($brand instanceof Astrio_Brand_Model_Brand && $brand->canShow()) {
                $this->_brand = $brand;
            }
        }

        return $this->_brand;
    }

    /**
     * Initialize product collection
     *
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection collection
     * @return Mage_Catalog_Model_Layer
     */
    public function prepareProductCollection($collection)
    {
        $result = parent::prepareProductCollection($collection);

        if ($this->getBrand()) {
            $this->getBrand()->addBrandUrlRewriteToProductCollection($collection);
        }

        $collection
            ->setStore(Mage::app()->getStore())
            ->addStoreFilter();

        return $result;
    }

    /**
     * Retrieve current layer product collection
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getProductCollection()
    {
        if ($this->_productCollection === null) {
            $collection = $this->getBrand() ? $this->getBrand()->getProductCollection() : Mage::getResourceModel('catalog/product_collection');
            $this->prepareProductCollection($collection);
            $this->_productCollection = $collection;
        }

        return $this->_productCollection;
    }

    /**
     * Add filters to attribute collection
     *
     * @param   Mage_Catalog_Model_Resource_Product_Attribute_Collection $collection collection
     * @return  Mage_Catalog_Model_Resource_Product_Attribute_Collection
     */
    protected function _prepareAttributeCollection($collection)
    {
        /**
         * @var Mage_Catalog_Model_Resource_Product_Attribute_Collection $collection
         */
        $collection = parent::_prepareAttributeCollection($collection);
        if ($this->getBrand()) {
            $collection->addFieldToFilter('attribute_code', array('neq' => Astrio_Brand_Model_Brand::ATTRIBUTE_CODE));
        }

        return $collection;
    }
}