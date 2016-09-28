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
class Astrio_Brand_Model_Indexer_Url extends Mage_Index_Model_Indexer_Abstract
{
    /**
     * Data key for matching result to be saved in
     */
    const EVENT_MATCH_RESULT_KEY = 'astrio_brand_url_match_result';

    /**
     * Index math: product save, category save, store save
     * store group save, config save
     *
     * @var array
     */
    protected $_matchedEntities = array(
        Mage_Catalog_Model_Product::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
            Mage_Index_Model_Event::TYPE_MASS_ACTION,
        ),
        Astrio_Brand_Model_Brand::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
        Mage_Core_Model_Store::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
        Mage_Core_Model_Store_Group::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
        Mage_Core_Model_Config_Data::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
        Mage_Catalog_Model_Convert_Adapter_Product::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        )
    );

    protected $_relatedConfigSettings = array(
        Astrio_Brand_Helper_Brand::XML_PATH_SEO_BRAND_URL_SUFFIX,
        Mage_Catalog_Helper_Product::XML_PATH_PRODUCT_URL_SUFFIX,
        Astrio_Brand_Helper_Brand::XML_PATH_SEO_PRODUCT_URL_USE_BRAND,
    );

    /**
     * Get Indexer name
     *
     * @return string
     */
    public function getName()
    {
        return Mage::helper('astrio_brand')->__('Astrio Brand URL Rewrites');
    }

    /**
     * Get Indexer description
     *
     * @return string
     */
    public function getDescription()
    {
        return Mage::helper('astrio_brand')->__('Index product and brand URL rewrites');
    }

    /**
     * Check if event can be matched by process.
     * Overwrote for specific config save, store and store groups save matching
     *
     * @param Mage_Index_Model_Event $event event
     * @return bool
     */
    public function matchEvent(Mage_Index_Model_Event $event)
    {
        $data = $event->getNewData();
        if (isset($data[self::EVENT_MATCH_RESULT_KEY])) {
            return $data[self::EVENT_MATCH_RESULT_KEY];
        }

        $entity = $event->getEntity();
        if ($entity == Mage_Core_Model_Store::ENTITY) {
            $store = $event->getDataObject();
            if ($store && ($store->isObjectNew() || $store->dataHasChangedFor('group_id'))) {
                $result = true;
            } else {
                $result = false;
            }
        } else if ($entity == Mage_Core_Model_Store_Group::ENTITY) {
            $storeGroup = $event->getDataObject();
            $hasDataChanges = $storeGroup && $storeGroup->dataHasChangedFor('website_id');
            if ($storeGroup && !$storeGroup->isObjectNew() && $hasDataChanges) {
                $result = true;
            } else {
                $result = false;
            }
        } else if ($entity == Mage_Core_Model_Config_Data::ENTITY) {
            $configData = $event->getDataObject();
            if ($configData && in_array($configData->getPath(), $this->_relatedConfigSettings)) {
                $result = $configData->isValueChanged();
            } else {
                $result = false;
            }
        } else {
            $result = parent::matchEvent($event);
        }

        $event->addNewData(self::EVENT_MATCH_RESULT_KEY, $result);

        return $result;
    }

    /**
     * Register data required by process in event object
     *
     * @param Mage_Index_Model_Event $event event
     * @return $this
     */
    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
        $event->addNewData(self::EVENT_MATCH_RESULT_KEY, true);
        $entity = $event->getEntity();
        switch ($entity) {
            case Mage_Catalog_Model_Product::ENTITY:
                switch($event->getType()) {
                    case Mage_Index_Model_Event::TYPE_SAVE:
                        $this->_registerProductEvent($event);
                        break;
                    case Mage_Index_Model_Event::TYPE_MASS_ACTION:
                        $this->_registerCatalogProductMassActionEvent($event);
                        break;
                }
                break;

            case Astrio_Brand_Model_Brand::ENTITY:
                $this->_registerBrandEvent($event);
                break;

            case Mage_Catalog_Model_Convert_Adapter_Product::ENTITY:
                $event->addNewData('catalog_url_reindex_all', true);
                break;

            case Mage_Core_Model_Store::ENTITY:
            case Mage_Core_Model_Store_Group::ENTITY:
            case Mage_Core_Model_Config_Data::ENTITY:
                $process = $event->getProcess();
                $process->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
                break;
        }
        return $this;
    }

    /**
     * Register event data during product save process
     *
     * @param Mage_Index_Model_Event $event event
     */
    protected function _registerProductEvent(Mage_Index_Model_Event $event)
    {
        $product = $event->getDataObject();
        $dataChange = $product->dataHasChangedFor('url_key')
            || $product->dataHasChangedFor(Astrio_Brand_Model_Brand::ATTRIBUTE_CODE)
            || $product->getIsChangedWebsites();

        if (!$product->getExcludeUrlRewrite() && $dataChange) {
            $event->addNewData('rewrite_product_ids', array($product->getId()));
        }
    }

    /**
     * Register event data during mass attribute action
     *
     * @param Mage_Index_Model_Event $event event
     */
    protected function _registerCatalogProductMassActionEvent(Mage_Index_Model_Event $event)
    {
        /* @var $actionObject Varien_Object */
        $actionObject = $event->getDataObject();
        $attributesData = $actionObject->getData('attributes_data');
        if (is_array($attributesData) && in_array(Astrio_Brand_Model_Brand::ATTRIBUTE_CODE, array_keys($attributesData))) {
            $event->addNewData('rewrite_product_ids', $actionObject->getData('product_ids'));
        }
    }

    /**
     * Register event data during category save process
     *
     * @param Mage_Index_Model_Event $event event
     */
    protected function _registerBrandEvent(Mage_Index_Model_Event $event)
    {
        $brand = $event->getDataObject();
        if ($brand->dataHasChangedFor('url_key') || $brand->getIsChangedWebsites() || $brand->getIsChangedProductList()) {
            $event->addNewData('rewrite_brand_ids', array($brand->getId()));
            if ($brand->getIsChangedProductList() && $changedProducts = $brand->getIsChangedProductsIds()) {
                $event->addNewData('rewrite_product_ids', $changedProducts);
            }
        }
    }

    /**
     * Process event
     *
     * @param Mage_Index_Model_Event $event event
     */
    protected function _processEvent(Mage_Index_Model_Event $event)
    {
        $data = $event->getNewData();
        if (!empty($data['catalog_url_reindex_all'])) {
            $this->reindexAll();
        }

        /* @var $urlModel Astrio_Brand_Model_Url */
        $urlModel = Mage::getSingleton('astrio_brand/url');

        // Force rewrites history saving
        $dataObject = $event->getDataObject();
        if ($dataObject instanceof Varien_Object && $dataObject->hasData('save_rewrites_history')) {
            $urlModel->setShouldSaveRewritesHistory($dataObject->getData('save_rewrites_history'));
        }

        if (isset($data['rewrite_product_ids'])) {
            $urlModel->clearStoreInvalidRewrites(); // Maybe some products were moved or removed from website
            foreach ($data['rewrite_product_ids'] as $productId) {
                $urlModel->refreshProductRewrite($productId);
            }
        }
        if (isset($data['rewrite_brand_ids'])) {
            $urlModel->clearStoreInvalidRewrites(); // Maybe some brands were moved
            foreach ($data['rewrite_brand_ids'] as $brandId) {
                $urlModel->refreshBrandRewrite($brandId);
            }
        }
    }

    /**
     * Rebuild all index data
     */
    public function reindexAll()
    {
        /** @var $resourceModel Astrio_Brand_Model_Resource_Url */
        $resourceModel = Mage::getResourceSingleton('astrio_brand/url');
        $resourceModel->beginTransaction();
        try {
            Mage::getSingleton('astrio_brand/url')->refreshRewrites();
            $resourceModel->commit();
        } catch (Exception $e) {
            $resourceModel->rollBack();
            throw $e;
        }
    }
}