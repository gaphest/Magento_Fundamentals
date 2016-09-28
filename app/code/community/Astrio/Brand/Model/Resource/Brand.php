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
 * @package    Astrio_Core
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Astrio
 *
 * @category Astrio
 * @package Astrio_Brand
 * @author Eldaniz Gasymov <e.gasymov@astrio.net>
 * @see Mage_Catalog_Model_Resource_Product
 */
class Astrio_Brand_Model_Resource_Brand extends Astrio_Brand_Model_Resource_Abstract
{

    /**
     * Brand to website linkage table
     *
     * @var string
     */
    protected $_brandWebsiteTable;

    /**
     * Initialize resource
     */
    public function __construct()
    {
        parent::__construct();
        $resource = Mage::getSingleton('core/resource');
        $this->setType(Astrio_Brand_Model_Brand::ENTITY)
            ->setConnection(
                $resource->getConnection('catalog_read'),
                $resource->getConnection('catalog_write')
            );

        $this->_brandWebsiteTable = $this->getTable('astrio_brand/brand_website');
    }

    /**
     * Retrieve product website identifiers
     *
     * @param Astrio_Brand_Model_Brand|int $brand brand
     * @return array
     */
    public function getWebsiteIds($brand)
    {
        $adapter = $this->_getReadAdapter();

        if ($brand instanceof Astrio_Brand_Model_Brand) {
            $brandId = $brand->getId();
        } else {
            $brandId = $brand;
        }

        $select = $adapter->select()
            ->from($this->_brandWebsiteTable, 'website_id')
            ->where('brand_id = ?', (int)$brandId);

        return $adapter->fetchCol($select);
    }

    /**
     * Before save
     *
     * @param Varien_Object $object object
     * @return Mage_Eav_Model_Entity_Abstract
     */
    protected function _beforeSave(Varien_Object $object)
    {
        $this->_updateOrCreateBrandAttributeOption($object);

        return parent::_beforeSave($object);
    }

    /**
     * Gets brand attribute
     *
     * @return false|Mage_Eav_Model_Entity_Attribute_Abstract
     */
    protected function _getBrandAttribute()
    {
        /**
         * @var $eavConfig Mage_Eav_Model_Config
         */
        $eavConfig = Mage::getSingleton('eav/config');
        return $eavConfig->getAttribute(Mage_Catalog_Model_Product::ENTITY, Astrio_Brand_Model_Brand::ATTRIBUTE_CODE);
    }

    /**
     * Gets brand attribute option sort order
     *
     * @param Astrio_Brand_Model_Brand $brand brand
     * @return int
     * @throws Mage_Core_Exception
     */
    protected function _getBrandAttributeOptionSortOrder(Astrio_Brand_Model_Brand $brand)
    {
        if ($brand->getStoreId() == 0 || $brand->getId()) {
            return (int) $brand->getPosition();
        }

        /**
         * @var $brandCollection Astrio_Brand_Model_Resource_Brand_Collection
         */
        $brandCollection = Mage::getResourceModel('astrio_brand/brand_collection');
        $brandCollection->setStoreId(0);
        $brandCollection->addAttributeToSelect('position');
        $brandCollection->addIdFilter($brand->getId());
        return (int) $brandCollection->getFirstItem()->getPosition();
    }

    /**
     * Updates or creates brand attribute option
     *
     * @param Astrio_Brand_Model_Brand $brand brand
     * @return $this
     * @throws Exception
     */
    protected function _updateOrCreateBrandAttributeOption(Astrio_Brand_Model_Brand $brand)
    {
        $optionTable        = $this->getTable('eav/attribute_option');
        $optionValueTable   = $this->getTable('eav/attribute_option_value');

        $adapter = $this->_getWriteAdapter();

        $attribute = $this->_getBrandAttribute();
        $attributeId = $attribute->getId();

        $storeId = $brand->getStoreId();
        $optionValue = $brand->getName();

        $isObjectNew = $brand->getId() == 0;

        $sortOrder = $this->_getBrandAttributeOptionSortOrder($brand);

        if ($isObjectNew) {
            $adapter->insert($optionTable, array('attribute_id' => $attributeId, 'sort_order' => $sortOrder));
            $optionId = (int) $adapter->lastInsertId($optionTable);
            $brand->setId($optionId);
        } else {
            $optionId = (int) $brand->getId();

            $select = $adapter->select();
            $select->from($optionTable)
                ->where('attribute_id = ?', $attributeId)
                ->where('option_id = ?', $optionId);

            $optionRow = $adapter->fetchRow($select);
            if (!$optionRow) {
                $adapter->insert($optionTable, array('attribute_id' => $attributeId, 'option_id' => $optionId, 'sort_order' => $sortOrder));
            } elseif ($optionRow['sort_order'] != $sortOrder) {
                $conditions = array(
                    $adapter->quoteInto('attribute_id = ?', $attributeId),
                    $adapter->quoteInto('option_id = ?', $optionId),
                );
                $adapter->update($optionTable, array('sort_order' => $sortOrder), implode(' AND ', $conditions));
            }
        }

        if ($storeId && $optionValue === false) {
            $conditions = array(
                $adapter->quoteInto('store_id = ?', $storeId),
                $adapter->quoteInto('option_id = ?', $optionId),
            );
            $adapter->delete($optionValueTable, implode(' AND ', $conditions));
        } else {
            //if brand new and store not default... create two option values for two stores.
            $stores = $isObjectNew && $storeId != 0 ? array(0, $storeId) : array($storeId);

            foreach ($stores as $storeId) {
                $select = $adapter->select();
                $select->from($optionValueTable)
                    ->where('store_id = ?', $storeId)
                    ->where('option_id = ? ', $optionId);

                $optionValueRow = $adapter->fetchRow($select);
                if (!$optionValueRow) {
                    $adapter->insert($optionValueTable, array('option_id' => $optionId, 'store_id' => $storeId, 'value' => $optionValue));
                } elseif ($optionValueRow['value'] != $optionValue) {
                    $conditions = array(
                        $adapter->quoteInto('option_id = ?', $optionId),
                        $adapter->quoteInto('store_id = ?', $storeId),
                    );
                    $adapter->update($optionValueTable, array('value' => $optionValue), implode(' AND ', $conditions));
                }
            }
        }

        // reindex and other actions
        $attribute->setDataChanges(true);
        $attribute->save();

        return $this;
    }

    /**
     * Deletes brand attribute option
     *
     * @param Astrio_Brand_Model_Brand $brand brand
     * @throws Exception
     */
    protected function _deleteBrandAttributeOptions(Astrio_Brand_Model_Brand $brand)
    {
        $adapter = $this->_getWriteAdapter();

        $attribute = $this->_getBrandAttribute();

        $optionTable = $this->getTable('eav/attribute_option');

        $conditions = array(
            $adapter->quoteInto('attribute_id = ?', $attribute->getId()),
            $adapter->quoteInto('option_id = ?', $brand->getId()),
        );

        $adapter->delete($optionTable, implode(' AND ', $conditions));

        // reindex and other actions
        $attribute->setDataChanges(true);
        $attribute->save();
    }

    /**
     * After delete
     *
     * @param Varien_Object $brand brand
     * @return Mage_Eav_Model_Entity_Abstract
     */
    protected function _afterDelete(Varien_Object $brand)
    {
        /**
         * @var $brand Astrio_Brand_Model_Brand
         */
        $return = parent::_afterDelete($brand);

        $this->_deleteBrandAttributeOptions($brand);

        return $return;
    }

    /**
     * Save data related with product
     *
     * @param Varien_Object $brand brand
     * @return Astrio_Brand_Model_Resource_Brand
     */
    protected function _afterSave(Varien_Object $brand)
    {
        /**
         * @var $brand Astrio_Brand_Model_Brand
         */
        $this->_saveWebsiteIds($brand);

        return parent::_afterSave($brand);
    }

    /**
     * Save product website relations
     *
     * @param Astrio_Brand_Model_Brand $brand brand
     * @return Astrio_Brand_Model_Resource_Brand
     */
    protected function _saveWebsiteIds($brand)
    {
        $websiteIds = $brand->getWebsiteIds();

        $brand->setIsChangedWebsites(false);

        $adapter = $this->_getWriteAdapter();

        $oldWebsiteIds = $this->getWebsiteIds($brand);

        $insert = array_diff($websiteIds, $oldWebsiteIds);
        $delete = array_diff($oldWebsiteIds, $websiteIds);

        if (!empty($insert)) {
            $data = array();
            foreach ($insert as $websiteId) {
                $data[] = array(
                    'brand_id' => (int)$brand->getId(),
                    'website_id' => (int)$websiteId
                );
            }
            $adapter->insertMultiple($this->_brandWebsiteTable, $data);
        }

        if (!empty($delete)) {
            foreach ($delete as $websiteId) {
                $condition = array(
                    'brand_id = ?' => (int)$brand->getId(),
                    'website_id = ?' => (int)$websiteId,
                );

                $adapter->delete($this->_brandWebsiteTable, $condition);
            }
        }

        if (!empty($insert) || !empty($delete)) {
            $brand->setIsChangedWebsites(true);
        }

        return $this;
    }

    /**
     * Get positions of associated to brand $brand
     *
     * @param Astrio_Brand_Model_Brand $brand brand
     * @return array
     */
    public function getProductIds($brand)
    {
        /**
         * @var $productCollection Mage_Catalog_Model_Resource_Product_Collection
         */
        $productCollection = Mage::getModel('catalog/product')->getCollection();
        $productCollection->setStoreId($brand->getStoreId());
        $productCollection->addAttributeToFilter(Astrio_Brand_Model_Brand::ATTRIBUTE_CODE, $brand->getId());
        return $productCollection->getAllIds();
    }

    /**
     * Save products
     *
     * @param Astrio_Brand_Model_Brand $brand brand
     * @return $this
     */
    public function saveProducts($brand)
    {
        $products = $brand->getProductsData();
        if (!is_null($products) && is_array($products)) {

            $adapter = $this->_getWriteAdapter();

            /**
             * @var $eavConfig Mage_Eav_Model_Config
             */
            $eavConfig = Mage::getSingleton('eav/config');

            $brandAttribute = $eavConfig->getAttribute(Mage_Catalog_Model_Product::ENTITY, Astrio_Brand_Model_Brand::ATTRIBUTE_CODE);
            $attributeTable = $brandAttribute->getBackendTable();

            $select = $adapter->select()
                ->from($attributeTable, array('entity_id'))
                ->where('attribute_id = ?', (int)$brandAttribute->getId())
                ->where('value = ?', (int)$brand->getId());

            $oldProducts = $adapter->fetchCol($select);

            $insertOrUpdate = array_diff($products, $oldProducts);
            $delete = array_diff($oldProducts, $products);

            if ($insertOrUpdate || $delete) {
                $brand->setIsChangedProductList(true);

                if ($delete) {
                    $conditions = array();
                    $conditions[] = $adapter->quoteInto('attribute_id = ?', (int)$brandAttribute->getId());
                    $conditions[] = $adapter->quoteInto('value = ?', (int)$brand->getId());
                    $conditions[] = $adapter->quoteInto('entity_id IN (?)', $delete);
                    $adapter->delete($attributeTable, implode(' AND ', $conditions));
                }

                if ($insertOrUpdate) {
                    $entityTypeId = $eavConfig->getEntityType(Mage_Catalog_Model_Product::ENTITY)->getEntityTypeId();
                    foreach ($insertOrUpdate as $productId) {
                        $bind = array(
                            'entity_type_id' => $entityTypeId,
                            'attribute_id'  => (int)$brandAttribute->getId(),
                            'store_id'      => 0,
                            'entity_id'     => (int)$productId,
                            'value'         => (int)$brand->getId(),
                        );
                        $adapter->insertOnDuplicate($attributeTable, $bind, array('value'));
                    }
                }

                $brand->setIsChangedProductsIds(array_merge($delete, $insertOrUpdate));
            }
        }

        return $this;
    }

}