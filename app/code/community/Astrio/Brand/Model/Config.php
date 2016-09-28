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
 * @see Mage_Catalog_Model_Config
 */
class Astrio_Brand_Model_Config extends Mage_Eav_Model_Config
{

    /**
     * Array of attributes codes needed for brand load
     *
     * @var array
     */
    protected $_brandAttributes;

    /**
     * Brand Attributes used in brand listing
     *
     * @var array
     */
    protected $_usedInBrandListing;

    protected $_storeId = null;

    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('astrio_brand/config');
    }

    /**
     * Set store id
     *
     * @param integer $storeId store id
     * @return Mage_Catalog_Model_Config
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }

    /**
     * Return store id, if is not set return current app store
     *
     * @return integer
     */
    public function getStoreId()
    {
        if ($this->_storeId === null) {
            return Mage::app()->getStore()->getId();
        }
        return $this->_storeId;
    }

    /**
     * Load Brand attributes
     *
     * @return array
     */
    public function getBrandAttributes()
    {
        if (is_null($this->_brandAttributes)) {
            $this->_brandAttributes = array_keys($this->getAttributesUsedInBrandListing());
        }
        return $this->_brandAttributes;
    }

    /**
     * Retrieve resource model
     *
     * @return Astrio_Brand_Model_Resource_Config
     */
    protected function _getResource()
    {
        return Mage::getResourceModel('astrio_brand/config');
    }

    /**
     * Retrieve Attributes used in brand listing
     *
     * @return array
     */
    public function getAttributesUsedInBrandListing()
    {
        if (is_null($this->_usedInBrandListing)) {
            $this->_usedInBrandListing = array();
            $entityType = Astrio_Brand_Model_Brand::ENTITY;
            $attributesData = $this->_getResource()
                ->setStoreId($this->getStoreId())
                ->getAttributesUsedInListing();
            Mage::getSingleton('eav/config')
                ->importAttributesData($entityType, $attributesData);
            foreach ($attributesData as $attributeData) {
                $attributeCode = $attributeData['attribute_code'];
                $this->_usedInBrandListing[$attributeCode] = Mage::getSingleton('eav/config')
                    ->getAttribute($entityType, $attributeCode);
            }
        }
        return $this->_usedInBrandListing;
    }

}