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
class Astrio_Brand_Model_Brand extends Mage_Catalog_Model_Abstract
{
    // Attribute code
    const ATTRIBUTE_CODE = 'brand';

    /**
     * Entity code.
     *
     * Can be used as part of method name for entity processing
     */
    const ENTITY = 'astrio_brand';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'astrio_brand';

    /**
     * Event object name
     *
     * @var string
     */
    protected $_eventObject = 'brand';

    /**
     * Brand Url Instance
     *
     * @var Astrio_Brand_Model_Brand_Url
     */
    protected $_urlModel = null;

    protected static $_url;

    protected static $_urlRewrite;

    /**
     * Initialize resources
     */
    protected function _construct()
    {
        $this->_init('astrio_brand/brand');
    }

    /**
     * Gets resource model
     *
     * @return Astrio_Brand_Model_Resource_Brand
     */
    public function getResource()
    {
        return $this->_getResource();
    }

    /**
     * Retrieve model resource
     *
     * @return Astrio_Brand_Model_Resource_Brand
     */
    protected function _getResource()
    {
        return parent::_getResource();
    }

    /**
     * Retrieve Store Id
     *
     * @return int
     */
    public function getStoreId()
    {
        if ($this->hasData('store_id')) {
            return $this->getData('store_id');
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
     * Get product url model
     *
     * @return Astrio_Brand_Model_Brand_Url
     */
    public function getUrlModel()
    {
        if ($this->_urlModel === null) {
            $this->_urlModel = Mage::getSingleton('astrio_brand/factory')->getBrandUrlInstance();
        }
        return $this->_urlModel;
    }

    /**
     * Validate Brand Data
     *
     * @return Astrio_Brand_Model_Brand
     */
    public function validate()
    {
        Mage::dispatchEvent($this->_eventPrefix.'_validate_before', array($this->_eventObject => $this));
        $this->_getResource()->validate($this);
        Mage::dispatchEvent($this->_eventPrefix.'_validate_after', array($this->_eventObject => $this));
        return $this;
    }

    /**
     * Get brand name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_getData('name');
    }

    /**
     * Retrieve array of product id's for brand
     *
     * @return array
     */
    public function getProductIds()
    {
        if (!$this->getId()) {
            return array();
        }

        $array = $this->getData('products_ids');
        if (is_null($array)) {
            $array = $this->getResource()->getProductIds($this);
            $this->setData('products_ids', $array);
        }
        return $array;
    }

    /**
     * Retrieve product websites identifiers
     *
     * @return array
     */
    public function getWebsiteIds()
    {
        if (!$this->hasWebsiteIds()) {
            $ids = $this->_getResource()->getWebsiteIds($this);
            $this->setWebsiteIds($ids);
        }
        return $this->getData('website_ids');
    }

    /**
     * Get all sore ids where product is presented
     *
     * @return array
     */
    public function getStoreIds()
    {
        if (!$this->hasStoreIds()) {
            $storeIds = array();
            if ($websiteIds = $this->getWebsiteIds()) {
                foreach ($websiteIds as $websiteId) {
                    $websiteStores = Mage::app()->getWebsite($websiteId)->getStoreIds();
                    $storeIds = array_merge($storeIds, $websiteStores);
                }
            }
            $this->setStoreIds($storeIds);
        }
        return $this->getData('store_ids');
    }

    /**
     * Retrieve product attributes
     * if $groupId is null - retrieve all product attributes
     *
     * @param int $groupId Retrieve attributes of the specified group
     * @return array
     */
    public function getAttributes($groupId = null)
    {
        $brandAttributes = $this->getSetAttributes();
        if ($groupId) {
            /**
             * @var $attribute Mage_Eav_Model_Entity_Attribute_Abstract
             */
            $attributes = array();
            foreach ($brandAttributes as $attribute) {
                if ($attribute->isInGroup($this->getAttributeSetId(), $groupId)) {
                    $attributes[] = $attribute;
                }
            }
        } else {
            $attributes = $brandAttributes;
        }

        return $attributes;
    }

    /**
     * Before save
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
    }

    /**
     * Saving and init index
     *
     * @return Astrio_Brand_Model_Brand
     */
    protected function _afterSave()
    {
        $this->getResource()->saveProducts($this);

        $result = parent::_afterSave();

        Mage::getSingleton('index/indexer')->processEntityAction(
            $this, self::ENTITY, Mage_Index_Model_Event::TYPE_SAVE
        );
        return $result;
    }

    /**
     * Register indexing event before delete brand
     *
     * @return Astrio_Brand_Model_Brand
     */
    protected function _beforeDelete()
    {
        Mage::getSingleton('index/indexer')->logEvent(
            $this, self::ENTITY, Mage_Index_Model_Event::TYPE_DELETE
        );
        return parent::_beforeDelete();
    }

    /**
     * Init indexing process after brand delete commit
     *
     * @return Astrio_Brand_Model_Brand
     */
    protected function _afterDeleteCommit()
    {
        $result = parent::_afterDeleteCommit();
        Mage::getSingleton('index/indexer')->indexEvents(
            self::ENTITY, Mage_Index_Model_Event::TYPE_DELETE
        );
        return $result;
    }

    /**
     * Load product options if they exists
     *
     * @return Astrio_Brand_Model_Brand
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        return $this;
    }

    /**
     * Get attribute text by its code
     *
     * @param string $attributeCode Code of the attribute
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
     * Retrieve Brand URL
     *
     * @param bool $useSid use sid?
     * @return string
     */
    public function getBrandUrl($useSid = null)
    {
        return $this->getUrlModel()->getBrandUrl($this, $useSid);
    }

    /**
     * Retrieve URL in current store
     *
     * @param array $params the route params
     * @return string
     */
    public function getUrlInStore($params = array())
    {
        return $this->getUrlModel()->getUrlInStore($this, $params);
    }

    /**
     * Formats URL key
     *
     * @param string $str URL
     * @return string
     */
    public function formatUrlKey($str)
    {
        return $this->getUrlModel()->formatUrlKey($str);
    }

    /**
     * Save current attribute with code $code and assign new value
     *
     * @param string $code  Attribute code
     * @param mixed  $value New attribute value
     * @param int    $store Store ID
     * @return void
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
    }

    /**
     * Delete brand
     *
     * @return Astrio_Brand_Model_Brand
     */
    public function delete()
    {
        parent::delete();
        Mage::dispatchEvent($this->_eventPrefix.'_delete_after_done', array($this->_eventObject => $this));
        return $this;
    }

    /**
     * Returns request path
     *
     * @return string
     */
    public function getRequestPath()
    {
        if (!$this->_getData('request_path')) {
            $this->getBrandUrl();
        }
        return $this->_getData('request_path');
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
     * Get brand products collection
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getProductCollection()
    {
        /**
         * @var $collection Mage_Catalog_Model_Resource_Product_Collection
         */
        $collection = Mage::getResourceModel('catalog/product_collection');

        $brandAttributeCode = Astrio_Brand_Model_Brand::ATTRIBUTE_CODE;
        if ($collection->getResource() instanceof Mage_Catalog_Model_Resource_Product_Flat) {
            $collection->joinAttribute(
                $brandAttributeCode,
                'catalog_product/' . $brandAttributeCode,
                'entity_id',
                null,
                'inner',
                0
            );
            $collection->getSelect()->where('at_' . $brandAttributeCode . '.value = ?', $this->getId());
        } else {
            $collection->addAttributeToFilter($brandAttributeCode, $this->getId());
        }

        return $collection;
    }

    /**
     * Adds brand url rewrite to product collection
     *
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection collection
     * @return $this
     */
    public function addBrandUrlRewriteToProductCollection($collection)
    {
        $collection->setFlag('add_brand_url_rewrite', $this->getId());
        return $this;
    }

    /**
     * Reset all model data
     *
     * @return Mage_Catalog_Model_Product
     */
    public function reset()
    {
        $this->_clearData();
        return $this;
    }

    /**
     * Clearing brand's data
     *
     * @return Mage_Catalog_Model_Product
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
     * Gets if is visible
     *
     * @return mixed
     */
    public function isVisible()
    {
        return $this->getIsVisible();
    }

    /**
     * Gets if is visible
     *
     * @return mixed
     */
    public function getIsVisible()
    {
        return $this->_getData('is_visible');
    }

    /**
     * Get is active
     *
     * @return string
     */
    public function isActive()
    {
        return $this->getIsActive();
    }

    /**
     * Get is active
     *
     * @return string
     */
    public function getIsActive()
    {
        return $this->_getData('is_active');
    }

    /**
     * Gets if can show
     *
     * @return bool
     */
    public function canShow()
    {
        return $this->getId() && $this->_getData('is_active') && in_array($this->getStoreId(), $this->getStoreIds());
    }

    /**
     * Get array of brand set attributes
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

}