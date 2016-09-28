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
class Astrio_SpecialProducts_Model_Set extends Mage_Core_Model_Abstract
{

    // Auto type: catalog rule
    const AUTO_TYPE_CATALOG_RULE    = 1;
    // Auto type: recently added
    const AUTO_TYPE_RECENTLY_ADDED  = 2;
    // Auto type: new
    const AUTO_TYPE_NEW             = 3;
    // Auto type: on sale
    const AUTO_TYPE_ON_SALE         = 4;
    // Auto type: bestseller
    const AUTO_TYPE_BESTSELLER      = 5;
    // Auto type: most viewed
    const AUTO_TYPE_MOST_VIEWED     = 6;
    // Auto type: most reviewed
    const AUTO_TYPE_MOST_REVIEWED   = 7;

    protected static $_autoTypeOptions = null;
    
    protected static $_autoTypeUsesCustomerGroup = array();

    /**
     * Entity code. Can be used for indexer.
     */
    const ENTITY            = 'astrio_specialproducts_set';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'astrio_specialproducts_set';

    /**
     * Event object name
     *
     * @var string
     */
    protected $_eventObject = 'set';

    /**
     * Initialize resources
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('astrio_specialproducts/set');
    }

    /**
     * Get resource model
     *
     * @return Astrio_SpecialProducts_Model_Resource_Set
     */
    public function getResource()
    {
        return parent::getResource();
    }

    /**
     * Get store ids
     *
     * @return array
     */
    public function getStoreIds()
    {
        if (!$this->hasData('store_ids')) {
            $this->setData('store_ids', $this->getResource()->getStoreIds($this));
        }
        return $this->getData('store_ids');
    }

    /**
     * Set store ids
     *
     * @param  mixed $storeIds store ids
     * @return $this
     */
    public function setStoreIds($storeIds)
    {
        $this->setData('store_ids', $storeIds);
        return $this;
    }

    /**
     * Get products by store
     *
     * @param  int $storeId store id
     * @return array
     */
    public function getProductsByStore($storeId)
    {
        return $this->getResource()->getProductsByStore($this, $storeId);
    }

    /**
     * Get auto type option array
     *
     * @return mixed|null
     */
    public static function getAutoTypeOptionArray()
    {
        if (self::$_autoTypeOptions === null) {
            /**
             * @var $helper Astrio_SpecialProducts_Helper_Data
             */
            $helper = Mage::helper('astrio_specialproducts');

            $result = array(
                self::AUTO_TYPE_CATALOG_RULE    => $helper->__('Catalog Rule'),
                self::AUTO_TYPE_RECENTLY_ADDED  => $helper->__('Recently Added'),
                self::AUTO_TYPE_NEW             => $helper->__('New'),
                self::AUTO_TYPE_ON_SALE         => $helper->__('On Sale'),
                self::AUTO_TYPE_BESTSELLER      => $helper->__('Bestseller'),
                self::AUTO_TYPE_MOST_VIEWED     => $helper->__('Most Viewed'),
                self::AUTO_TYPE_MOST_REVIEWED   => $helper->__('Most Reviewed'),
            );

            $object = new Varien_Object();
            $object->setData('types', $result);

            Mage::dispatchEvent('specialproducts_set_get_auto_type_options', array('object' => $object));

            self::$_autoTypeOptions = $object->getData('types');
        }

        return self::$_autoTypeOptions;
    }

    /**
     * Get auto types to option array
     *
     * @param  bool $isMultiSelect is multi select?
     * @return array
     */
    public static function getAutoTypesToOptionArray($isMultiSelect=false)
    {
        $options = array();
        $optionArray = self::getAutoTypeOptionArray();
        foreach ($optionArray as $value => $label) {
            $options[] = array(
                'label' => $label,
                'value' => $value,
            );
        }

        if ($isMultiSelect) {
            return $options;
        }

        array_unshift($options, array('label' => Mage::helper('astrio_specialproducts')->__('-- Please Select a Type --'),'value' => '',));
        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @param  bool $isMultiSelect is multi select?
     * @return array
     */
    public static function getAutoTypesToArray($isMultiSelect=false)
    {
        if ($isMultiSelect) {
            return self::getAutoTypeOptionArray();
        }

        return array('' => '') + self::getAutoTypeOptionArray();
    }

    /**
     * Get label
     *
     * @return Astrio_SpecialProducts_Model_Set_Label
     */
    public function getLabel()
    {
        if (!$this->hasData('label')) {
            $this->setData('label', Mage::getModel('astrio_specialproducts/set_label')->load($this->getId()));
        }

        return $this->_getData('label');
    }

    /**
     * Get page
     *
     * @return Astrio_SpecialProducts_Model_Set_Page
     */
    public function getPage()
    {
        if (!$this->hasData('page')) {
            $this->setData('page', Mage::getModel('astrio_specialproducts/set_page')->load($this->getId()));
        }

        return $this->_getData('page');
    }

    /**
     * Saving and init index
     *
     * @return $this
     */
    protected function _afterSave()
    {
        $result = parent::_afterSave();

        if ($this->getIsAuto()) {
            Mage::getSingleton('index/indexer')->processEntityAction(
                $this, self::ENTITY, Mage_Index_Model_Event::TYPE_SAVE
            );
        }

        return $result;
    }

    /**
     * Reindex
     *
     * @return $this
     */
    public function reindex()
    {
        if ($this->getId() && $this->getIsAuto()) {
            /**
             * @var $indexerResource Astrio_SpecialProducts_Model_Resource_Indexer_Set
             */
            $indexerResource = Mage::getResourceModel('astrio_specialproducts/indexer_set');
            $indexerResource->reindexSet($this);
        }

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_getData('name');
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->_getData('identifier');
    }

    /**
     * Get if is active
     *
     * @return bool
     */
    public function getIsActive()
    {
        return (bool) $this->_getData('is_active');
    }

    /**
     * Get if is auto
     *
     * @return bool
     */
    public function getIsAuto()
    {
        return (bool) $this->_getData('is_auto');
    }

    /**
     * Get auto type
     *
     * @return int
     */
    public function getAutoType()
    {
        return (int) $this->_getData('auto_type');
    }

    /**
     * Get catalog rule id
     *
     * @return bool
     */
    public function getCatalogRuleId()
    {
        return (int) $this->_getData('catalog_rule_id');
    }

    /**
     * Get filter by category id
     *
     * @return int
     */
    public function getFilterByCategoryId()
    {
        return (int) $this->_getData('filter_by_category_id');
    }

    /**
     * Get filter greater than
     *
     * @return int
     */
    public function getFilterGreaterThan()
    {
        return (int) $this->_getData('filter_greater_than');
    }

    /**
     * Get filter in last days
     *
     * @return int
     */
    public function getFilterInLastDays()
    {
        return (int) $this->_getData('filter_in_last_days');
    }

    /**
     * Get products limit
     *
     * @return int
     */
    public function getProductsLimit()
    {
        return (int) $this->_getData('products_limit');
    }

    /**
     * Get apply label
     *
     * @return bool
     */
    public function getApplyLabel()
    {
        return (bool) $this->_getData('apply_label');
    }

    /**
     * Get display in block
     *
     * @return bool
     */
    public function getDisplayInBlock()
    {
        return (bool) $this->_getData('display_in_block');
    }

    /**
     * Get use separate page
     *
     * @return bool
     */
    public function getUseSeparatePage()
    {
        return (bool) $this->_getData('use_separate_page');
    }

    /**
     * Uses customer groups
     *
     * @return bool
     */
    public function usesCustomerGroups()
    {
        if (!$this->getIsAuto() || !$this->getAutoType()) {
            return false;
        }

        return self::isSetUsesCustomerGroups($this->getAutoType());
    }

    /**
     * Get if is set uses customer groups
     *
     * @param  int $autoType auto type
     * @return bool
     */
    public static function isSetUsesCustomerGroups($autoType)
    {
        $autoType = (int) $autoType;
        if (!isset(self::$_autoTypeUsesCustomerGroup[$autoType])) {
            switch ($autoType)
            {
                case Astrio_SpecialProducts_Model_Set::AUTO_TYPE_RECENTLY_ADDED:
                case Astrio_SpecialProducts_Model_Set::AUTO_TYPE_NEW:
                case Astrio_SpecialProducts_Model_Set::AUTO_TYPE_BESTSELLER:
                case Astrio_SpecialProducts_Model_Set::AUTO_TYPE_MOST_VIEWED:
                case Astrio_SpecialProducts_Model_Set::AUTO_TYPE_MOST_REVIEWED:
                    self::$_autoTypeUsesCustomerGroup[$autoType] = false;
                    break;
                case Astrio_SpecialProducts_Model_Set::AUTO_TYPE_ON_SALE:
                case Astrio_SpecialProducts_Model_Set::AUTO_TYPE_CATALOG_RULE:
                    self::$_autoTypeUsesCustomerGroup[$autoType] = true;
                    break;
                default:
                    $object = new Varien_Object();
                    $object->setUses(false);
                    Mage::dispatchEvent('specialproducts_set_uses_customer_groups', array('auto_type' => $autoType));
                    self::$_autoTypeUsesCustomerGroup[$autoType] = (bool) $object->getUses();
            }
        }

        return self::$_autoTypeUsesCustomerGroup[$autoType];
    }

    /**
     * Join product collection to special products set
     *
     * @param  Mage_Catalog_Model_Resource_Product_Collection $collection collection
     * @param  int|string                                     $storeId    store id
     * @return $this
     */
    public function joinProductCollectionToSpecialProductsSet(Mage_Catalog_Model_Resource_Product_Collection $collection, $storeId)
    {
        $this->getResource()->joinProductCollectionToSpecialProductsSet($this, $collection, $storeId);
        return $this;
    }

    /**
     * Join product collection to special products set with group
     *
     * @param  Mage_Catalog_Model_Resource_Product_Collection $collection collection
     * @param  string|int                                     $storeId    store id
     * @param  string|int|null                                $groupId    group id
     * @return $this
     */
    public function joinProductCollectionToSpecialProductsSetWithGroup(Mage_Catalog_Model_Resource_Product_Collection $collection, $storeId, $groupId = null)
    {
        $this->getResource()->joinProductCollectionToSpecialProductsSetWithGroup($this, $collection, $storeId, $groupId);
        return $this;
    }

    /**
     * Get page url
     *
     * @return bool|string
     */
    public function getPageUrl()
    {
        /**
         * @var $urlModel Astrio_SpecialProducts_Model_Set_Page_Url
         */
        $urlModel = Mage::getSingleton('astrio_specialproducts/set_page_url');
        return $urlModel->getPageUrl($this);
    }
}
