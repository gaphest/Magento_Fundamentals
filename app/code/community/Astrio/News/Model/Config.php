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
 * @package    Astrio_News
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Astrio
 *
 * @category   Astrio
 * @package    Astrio_News
 * @author     Eldaniz Gasymov <e.gasymov@astrio.net>
 */
class Astrio_News_Model_Config extends Mage_Eav_Model_Config
{
    
    /**
     * Array of attributes codes needed for news load
     *
     * @var array
     */
    protected $_attributes;

    /**
     * News Attributes used in news listing
     *
     * @var array
     */
    protected $_usedInListing;

    protected $_storeId = null;

    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('astrio_news/config');
    }

    /**
     * Set store id
     *
     * @param  int $storeId store id
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
     * Load News attributes
     *
     * @param  string $entityType entity type
     * @return array
     */
    public function getAttributes($entityType)
    {
        if (!isset($this->_attributes[$entityType])) {
            $this->_attributes[$entityType] = array_keys($this->getAttributesUsedInListing($entityType));
        }

        return $this->_attributes[$entityType];
    }

    /**
     * Retrieve resource model
     *
     * @return Astrio_News_Model_Resource_Config
     */
    protected function _getResource()
    {
        return Mage::getResourceModel('astrio_news/config');
    }

    /**
     * Retrieve Attributes used in news listing
     *
     * @param  string $entityType entity type
     * @return array
     */
    public function getAttributesUsedInListing($entityType)
    {
        if (!isset($this->_usedInListing[$entityType])) {
            $result = array();
            $attributesData = $this->_getResource()
                ->setStoreId($this->getStoreId())
                ->getAttributesUsedInListing($entityType);

            Mage::getSingleton('eav/config')
                ->importAttributesData($entityType, $attributesData);

            foreach ($attributesData as $attributeData) {
                $attributeCode = $attributeData['attribute_code'];
                $result[$attributeCode] = Mage::getSingleton('eav/config')
                    ->getAttribute($entityType, $attributeCode);
            }

            $this->_usedInListing[$entityType] = $result;
        }
        
        return $this->_usedInListing[$entityType];
    }
}
