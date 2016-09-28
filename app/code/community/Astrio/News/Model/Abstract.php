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
abstract class Astrio_News_Model_Abstract extends Mage_Catalog_Model_Abstract
{
    
    /**
     * Retrieve Store Id
     *
     * @return int
     */
    public function getStoreId()
    {
        if ($this->hasData('store_id')) {
            return $this->_getData('store_id');
        }
        return Mage::app()->getStore()->getId();
    }

    /**
     * Set Store Id
     *
     * @param int $storeId store id
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->setData('store_id', $storeId);
        return $this;
    }

    /**
     * Get collection instance
     *
     * @return object
     */
    public function getResourceCollection()
    {
        if (empty($this->_resourceCollectionName)) {
            Mage::throwException(Mage::helper('catalog')->__('The model collection resource name is not defined.'));
        }

        $collection = Mage::getResourceModel($this->_resourceCollectionName);
        $collection->setStoreId($this->getStoreId());
        return $collection;
    }

    /**
     * Validate News Data
     *
     * @return $this
     */
    public function validate()
    {
        Mage::dispatchEvent($this->_eventPrefix.'_validate_before', array($this->_eventObject => $this));
        $this->_getResource()->validate($this);
        Mage::dispatchEvent($this->_eventPrefix.'_validate_after', array($this->_eventObject => $this));
        return $this;
    }

    /**
     * Retrieve news attributes
     * if $groupId is null - retrieve all news attributes
     *
     * @param int $groupId Retrieve attributes of the specified group
     * @return array
     */
    public function getAttributes($groupId = null)
    {
        $newsAttributes = $this->getSetAttributes();
        if ($groupId) {
            /**
             * @var $attribute Mage_Eav_Model_Entity_Attribute_Abstract
             */
            $attributes = array();
            foreach ($newsAttributes as $attribute) {
                if ($attribute->isInGroup($this->getAttributeSetId(), $groupId)) {
                    $attributes[] = $attribute;
                }
            }
        } else {
            $attributes = $newsAttributes;
        }

        return $attributes;
    }

    /**
     * Get attribute text by its code
     *
     * @param  string|int $attributeCode Code of the attribute
     * @return string
     */
    public function getAttributeText($attributeCode)
    {
        return $this->getResource()
            ->getAttribute($attributeCode)
            ->getSource()
            ->getOptionText($this->getData($attributeCode));
    }

    /**
     * Formats URL key
     *
     * @param  string $str URL
     * @return string
     */
    public function formatUrlKey($str)
    {
        $urlKey = preg_replace('#[^0-9a-z]+#i', '-', Mage::helper('catalog/product_url')->format($str));
        $urlKey = strtolower($urlKey);
        $urlKey = trim($urlKey, '-');

        return $urlKey;
    }

    /**
     * Save current attribute with code $code and assign new value
     *
     * @param string $code  Attribute code
     * @param mixed  $value New attribute value
     * @param int    $store Store ID
     * @return $this
     */
    public function addAttributeUpdate($code, $value, $store)
    {
        $oldValue = $this->getData($code);
        $oldStore = $this->getStoreId();

        $this->setData($code, $value);
        $this->setStoreId($store);
        $this->getResource()->saveAttribute($this, $code);

        $this->setData($code, $oldValue);
        $this->setStoreId($oldStore);
        return $this;
    }

    /**
     * Retrieve default attribute set id
     *
     * @return int
     */
    public function getDefaultAttributeSetId()
    {
        return $this->getResource()->getEntityType()->getDefaultAttributeSetId();
    }

    /**
     * Reset all model data
     *
     * @return $this
     */
    public function reset()
    {
        $this->_clearData();
        return $this;
    }

    /**
     * Clearing news's data
     *
     * @return $this
     */
    protected function _clearData()
    {
        foreach ($this->_data as $data) {
            if (is_object($data) && method_exists($data, 'reset')) {
                $data->reset();
            }
        }

        $this->setData(array());
        $this->setOrigData();

        return $this;
    }

    /**
     * Get is active
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->getIsActive();
    }

    /**
     * Get is active
     *
     * @return bool
     */
    public function getIsActive()
    {
        return (bool) $this->_getData('is_active');
    }

    /**
     * Get if is assigned to store
     *
     * @return bool
     */
    public function isAssignedToStore()
    {
        return true;
    }

    /**
     * Get if can show
     *
     * @return bool
     */
    public function canShow()
    {
        return $this->getId() && $this->isActive() && $this->isAssignedToStore();
    }

    /**
     * Get array of news set attributes
     *
     * @return array
     */
    public function getSetAttributes()
    {
        return $this->getResource()
            ->loadAllAttributes($this)
            ->getSortedAttributes($this->getAttributeSetId());
    }

    /**
     * Retrieve attribute set id
     *
     * @return int
     */
    public function getAttributeSetId()
    {
        if (is_null($this->getData('attribute_set_id'))) {
            $this->setData('attribute_set_id', $this->getDefaultAttributeSetId());
        }

        return $this->getData('attribute_set_id');
    }

    /**
     * Check if page identifier exist for specific store
     * return page id if page exists
     *
     * @param  string $identifier identifier
     * @param  int $storeId store id
     * @return int
     */
    public function checkIdentifier($identifier, $storeId)
    {
        /**
         * @var $collection Astrio_News_Model_Resource_Abstract_Collection
         */
        $collection = $this->getCollection();
        $collection->setStoreId($storeId);
        $collection->addStoreFilter()
            ->addIsActiveFilter()
            ->addAttributeToFilter('url_key', $identifier);

        $connection = $collection->getConnection();
        $select = $collection->getSelect();
        $select->reset(Zend_Db_Select::COLUMNS)
            ->columns($collection::MAIN_TABLE_ALIAS . '.' . $collection->getResource()->getIdFieldName());

        return $connection->fetchOne($select);
    }
}
