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
class Astrio_SpecialProducts_Helper_Label extends Mage_Core_Helper_Abstract
{

    // Alias for product sets table
    const SET_PRODUCT_TABLE_ALIAS   = 'set_product';
    // Label for table alias
    const LABEL_TABLE_ALIAS         = 'label';

    // Key for product data labels
    const PRODUCT_DATA_LABELS_KEY   = 'labels';

    // Placeholder for percent sign
    const PLACEHOLDER_PERCENT       = '#percent#';

    /**
     * @var Mage_Core_Model_Resource
     */
    protected $_coreResource;

    /**
     * @var Varien_Db_Adapter_Interface
     */
    protected $_readConnection;

    /**
     * @var string
     */
    protected $_setProductTableName;

    /**
     * @var string
     */
    protected $_labelTableName;

    /**
     * @var string
     */
    protected $_labelsImagePath;

    /**
     * @var int
     */
    protected $_customerGroupId = null;

    /**
     * @var Astrio_SpecialProducts_Model_Resource_Set_Label_Collection
     */
    protected $_labelsCollectionItems = null;

    /**
     * @var Mage_Tax_Helper_Data
     */
    protected $_taxHelper = null;

    /**
     * @var bool
     */
    protected $_simplePricesTax = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        /**
         * @var $coreResource Mage_Core_Model_Resource
         */
        $coreResource = Mage::getSingleton('core/resource');

        $this->_coreResource        = $coreResource;
        $this->_readConnection      = $coreResource->getConnection('core_read');
        $this->_setProductTableName = $coreResource->getTableName('astrio_specialproducts/set_product');
        $this->_labelTableName      = $coreResource->getTableName('astrio_specialproducts/set_label');

        $this->_labelsImagePath     = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . Astrio_SpecialProducts_Model_Set_Label::IMAGE_PATH;

        /**
         * @var $customerSession Mage_Customer_Model_Session
         */
        $customerSession = Mage::getSingleton('customer/session');
        $this->_customerGroupId = (int) $customerSession->getCustomerGroupId();
    }

    /**
     * @return array
     */
    public function getLabelsCollectionItems()
    {
        if ($this->_labelsCollectionItems === null) {
            /**
             * @var $collection Astrio_SpecialProducts_Model_Resource_Set_Label_Collection
             */
            $collection = Mage::getResourceModel('astrio_specialproducts/set_label_collection');
            $collection
                ->addActiveFilter()
                ->addStoreFilter()
                ->setOrderByPriority();

            Mage::dispatchEvent('specialproducts_set_labels_collection_load_before', array('collection' => $collection));

            $this->_labelsCollectionItems = $collection->getItems();
        }

        return $this->_labelsCollectionItems;
    }

    /**
     * Get labels to products
     *
     * @param  int|array $productIds product ids
     * @param  int       $storeId    store id
     * @return array
     */
    protected function _getLabelsToProducts($productIds, $storeId)
    {
        $productLabels = array();
        foreach ($productIds as $productId) {
            $productLabels[$productId] = array();
        }

        $setProductTableAlias = self::SET_PRODUCT_TABLE_ALIAS;
        $labelTableAlias = self::LABEL_TABLE_ALIAS;

        $select = $this->_readConnection->select();
        $select
            ->from(
                array($setProductTableAlias => $this->_setProductTableName),
                array('product_id', 'set_id')
            )
            ->join(
                array($labelTableAlias => $this->_labelTableName),
                "{$setProductTableAlias}.set_id = {$labelTableAlias}.label_id",
                array()
            )
            ->where("{$setProductTableAlias}.set_id IN(?)", array_keys($this->getLabelsCollectionItems()))
            ->where("{$setProductTableAlias}.store_id = ?", (int) $storeId)
            ->where("{$setProductTableAlias}.customer_group_id IS NULL OR {$setProductTableAlias}.customer_group_id = ?", $this->_customerGroupId)
            ->order("{$labelTableAlias}.priority " . Varien_Db_Select::SQL_DESC);

        if (is_array($productIds)) {
            $select->where("{$setProductTableAlias}.product_id IN(?)", $productIds);
        } else {
            $select->where("{$setProductTableAlias}.product_id = ?", $productIds);
        }

        $stmt = $this->_readConnection->query($select);
        while ($row = $stmt->fetch()) {
            $productLabels[$row['product_id']][] = $row['set_id'];
        }

        return $productLabels;
    }

    /**
     * Assign labels to product collection
     *
     * @param  Mage_Catalog_Model_Resource_Product_Collection $productCollection product collection
     * @return $this
     */
    public function assignLabelsToProductCollection(Mage_Catalog_Model_Resource_Product_Collection $productCollection)
    {
        if (count($productCollection) <= 0) {
            return $this;
        }

        $labelItems = $this->getLabelsCollectionItems();
        if (count($labelItems) <= 0) {
            return $this;
        }

        $items = $productCollection->getItems();
        $productIds = array_keys($items);

        $productLabels = $this->_getLabelsToProducts($productIds, $productCollection->getStoreId());

        /**
         * @var $product Mage_Catalog_Model_Product
         */
        foreach ($items as $product) {
            $product->setData(self::PRODUCT_DATA_LABELS_KEY, $productLabels[$product->getId()]);
        }

        return $this;
    }

    /**
     * Assign labels to products
     *
     * @param  Mage_Catalog_Model_Product $product product
     * @return $this
     */
    public function assignLabelsToProduct(Mage_Catalog_Model_Product $product)
    {
        if (!$product->getId()) {
            return $this;
        }

        $labelItems = $this->getLabelsCollectionItems();
        if (count($labelItems) <= 0) {
            return $this;
        }

        $productLabels = $this->_getLabelsToProducts(array($product->getId()), $product->getStoreId());

        $product->setData(self::PRODUCT_DATA_LABELS_KEY, $productLabels[$product->getId()]);

        return $this;
    }

    /**
     * Get Mage_Tax helper
     *
     * @return Mage_Tax_Helper_Data
     */
    public function getTaxHelper()
    {
        if (null === $this->_taxHelper) {
            $this->_taxHelper = Mage::helper('tax');
        }

        return $this->_taxHelper;
    }

    /**
     * Get if is simple prices tax
     *
     * @return bool
     */
    public function getIsSimplePricesTax()
    {
        if (null === $this->_simplePricesTax) {
            $_taxHelper = $this->getTaxHelper();
            $this->_simplePricesTax = ($_taxHelper->displayPriceIncludingTax() || $_taxHelper->displayBothPrices());
        }

        return $this->_simplePricesTax;
    }

    /**
     * Get product discount percent
     *
     * @param  Mage_Catalog_Model_Product $_product product
     * @return int
     */
    public function getProductDiscountPercent($_product)
    {
        $_taxHelper = $this->getTaxHelper();

        $_store = $_product->getStore();

        $_simplePricesTax = $this->getIsSimplePricesTax();

        $_convertedPrice = $_store->roundPrice($_store->convertPrice($_product->getPrice()));
        $_convertedFinalPrice = $_store->roundPrice($_store->convertPrice($_product->getFinalPrice()));

        $_regularPrice = $_taxHelper->getPrice($_product, $_convertedPrice, $_simplePricesTax);
        $_finalPrice = $_taxHelper->getPrice($_product, $_convertedFinalPrice);

        if ($_finalPrice >= $_regularPrice) {
            return 0;
        }

        $difference = $_regularPrice - $_finalPrice;
        $percent = ($difference / $_regularPrice) * 100;

        return round($percent);
    }

    /**
     * Get label html
     *
     * @param  Astrio_SpecialProducts_Model_Set_Label $label    label
     * @param  Mage_Catalog_Model_Product              $product product
     * @return string
     */
    public function getLabelHtml($label, $product)
    {
        switch ($label->getOutputType())
        {
            case Astrio_SpecialProducts_Model_Set_Label::OUTPUT_TYPE_IMAGE:
                $overlayContent = '    <img class="astrio-overlay astrio-overlay-image astrio-overlay-' . $label->getData('identifier') . '" src="' . $this->_labelsImagePath . $label->getImage() . '" />' . PHP_EOL;
                break;
            case Astrio_SpecialProducts_Model_Set_Label::OUTPUT_TYPE_TEXT:
                $autoType = $label->getData('auto_type');

                $title = $label->getTitle();

                if (mb_strpos($title, self::PLACEHOLDER_PERCENT, null, 'UTF-8') !== false) {
                    if (
                        $autoType == Astrio_SpecialProducts_Model_Set::AUTO_TYPE_ON_SALE
                        || $autoType == Astrio_SpecialProducts_Model_Set::AUTO_TYPE_CATALOG_RULE
                    ) {
                        $discountPercent = $this->getProductDiscountPercent($product);
                        if ($discountPercent <= 0) {
                            $overlayContent = '';
                            break;
                        }

                        $title = str_replace('#percent#', $discountPercent, $title);
                    }
                }

                $overlayContent = '    <span class="astrio-overlay astrio-overlay-text astrio-overlay-' . $label->getData('identifier') . '">' . $title . '</span>' . PHP_EOL;
                break;
            default:
                $overlayContent = '';
        }

        return $overlayContent;
    }

    /**
     * Get labels html
     *
     * @param  Mage_Catalog_Model_Product $product product
     * @return string
     */
    public function getLabelsHtml(Mage_Catalog_Model_Product $product)
    {
        $result = PHP_EOL;

        if (!$product->hasData(self::PRODUCT_DATA_LABELS_KEY)) {
            $this->assignLabelsToProduct($product);
        }

        $labels = $product->getData(self::PRODUCT_DATA_LABELS_KEY);
        if ($labels) {
            $labelItems = $this->getLabelsCollectionItems();

            $areas = array();

            foreach ($labels as $labelId) {
                if (!isset($labelItems[$labelId])) {
                    continue;
                }

                /**
                 * @var $label Astrio_SpecialProducts_Model_Set_Label
                 */
                $label = $labelItems[$labelId];

                $overlayContent = $this->getLabelHtml($label, $product);
                if (!$overlayContent) {
                    continue;
                }

                if (isset($areas[$label->getPosition()])) {
                    $areas[$label->getPosition()] .= $overlayContent;
                } else {
                    $areas[$label->getPosition()] = $overlayContent;
                }
            }

            foreach ($areas as $position => $html) {
                switch ($position)
                {
                    case Astrio_SpecialProducts_Model_Set_Label::POSITION_TOP_LEFT:
                        $positionClass = 'top-left';
                        break;
                    case Astrio_SpecialProducts_Model_Set_Label::POSITION_TOP_RIGHT:
                        $positionClass = 'top-right';
                        break;
                    case Astrio_SpecialProducts_Model_Set_Label::POSITION_BOTTOM_RIGHT:
                        $positionClass = 'bottom-right';
                        break;
                    case Astrio_SpecialProducts_Model_Set_Label::POSITION_BOTTOM_LEFT:
                        $positionClass = 'bottom-left';
                        break;
                    default:
                        $positionClass = '';
                }

                $result .= '<span class="astrio-overlay-area ' . $positionClass . '">' . PHP_EOL . $html . '</span>' . PHP_EOL;
            }
        }

        return $result;
    }
}
