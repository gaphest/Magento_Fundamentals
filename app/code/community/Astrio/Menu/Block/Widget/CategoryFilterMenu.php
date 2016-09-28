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
 * @package    Astrio_Menu
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Astrio
 *
 * @category Astrio
 * @package Astrio_Menu
 * @author Eldaniz Gasymov <e.gasymov@astrio.net>
 */
class Astrio_Menu_Block_Widget_CategoryFilterMenu
    extends Mage_Core_Block_Template
    implements Mage_Widget_Block_Interface
{
    // widget type: category
    const WIDGET_TYPE_CATEGORY          = 'category';
    // widget type: price
    const WIDGET_TYPE_PRICE             = 'price';
    // widget type: attribute
    const WIDGET_TYPE_ATTRIBUTE         = 'attribute';

    // widget sort: by position
    const WIDGET_SORT_BY_POSITION       = 'position';
    // widget sort: by name
    const WIDGET_SORT_BY_NAME           = 'name';
    // widget sort: by product count
    const WIDGET_SORT_BY_PRODUCT_COUNT  = 'product_count';

    /**
     * Get category id
     *
     * @return int
     */
    public function getCategoryId()
    {
        if (!$this->hasData('category_id') || is_string($this->_getData('category_id'))) {
            $this->setData('category_id', (int) $this->_getData('category_id'));
        }
        return $this->_getData('category_id');
    }

    /**
     * Get filter type
     *
     * @return string
     */
    public function getFilterType()
    {
        return $this->_getData('filter_type');
    }

    /**
     * Get attribute code
     *
     * @return string
     */
    public function getAttributeCode()
    {
        return $this->_getData('attribute_code');
    }

    /**
     * Get price range
     *
     * @return string
     */
    public function getPriceRange()
    {
        if (!$this->hasData('price_range') || is_string($this->_getData('price_range'))) {
            $range = (int) $this->_getData('price_range');
            if ($range <= 0) {
                $range = 0;
            }

            $this->setData('price_range', $range);
        }
        return $this->_getData('price_range');
    }

    /**
     * Get sort by
     *
     * @return string
     */
    public function getSortBy()
    {
        if (!$this->hasData('sort_by')) {
            $this->setData('sort_by', self::WIDGET_SORT_BY_POSITION);
        }
        return $this->_getData('sort_by');
    }

    /**
     * Get limit
     *
     * @return int
     */
    public function getLimit()
    {
        if (!$this->hasData('limit') || is_string($this->_getData('limit'))) {
            $limit = (int) $this->_getData('limit');
            if ($limit <= 0) {
                $limit = 0;
            }

            $this->setData('limit', $limit);
        }
        return $this->_getData('limit');
    }

    /**
     * Get category
     *
     * @param int $categoryId category id
     * @return bool|Mage_Catalog_Model_Category
     */
    protected function _getCategory($categoryId)
    {
        if (!$categoryId) {
            return false;
        }

        /**
         * @var $category Mage_Catalog_Model_Category
         */
        $category = Mage::getModel('catalog/category')->load($categoryId);

        if (!$category->getIsActive()) {
            return false;
        }

        if ($category->getId() != Mage::app()->getStore()->getRootCategoryId() && !$category->isInRootCategoryList()) {
            return false;
        }

        return $category;
    }

    /**
     * Get category
     *
     * @return bool|Mage_Catalog_Model_Category
     */
    public function getCategory()
    {
        if (!$this->hasData('category')) {
            $this->setData('category', $this->_getCategory($this->getCategoryId()));
        }
        return $this->getData('category');
    }

    /**
     * Get filter
     *
     * @return bool|Mage_Catalog_Model_Layer_Filter_Abstract
     */
    protected function _getFilter()
    {
        $layer = $this->getLayer();

        switch ($this->getFilterType())
        {
            case self::WIDGET_TYPE_CATEGORY:
                /**
                 * @var $filter Mage_Catalog_Model_Layer_Filter_Category
                 */
                $filter = Mage::getModel('catalog/layer_filter_category');
                $filter->setLayer($layer);
                return $filter;
            case self::WIDGET_TYPE_PRICE:
            case self::WIDGET_TYPE_ATTRIBUTE:
                $attributeCode = $this->getFilterType() == self::WIDGET_TYPE_PRICE ? 'price' : $this->getAttributeCode();

                /**
                 * @var $filterableAttributes Mage_Catalog_Model_Resource_Product_Attribute_Collection
                 * @var $filter Mage_Catalog_Model_Layer_Filter_Abstract
                 */
                $filterableAttributes = $layer->getFilterableAttributes();
                $attribute = $filterableAttributes->getItemByColumnValue('attribute_code', $attributeCode);
                if (!$attribute) {
                    return false;
                }

                if ($attribute->getAttributeCode() == 'price') {
                    $modelType = 'catalog/layer_filter_price';
                } elseif ($attribute->getBackendType() == 'decimal') {
                    $modelType = 'catalog/layer_filter_decimal';
                } else {
                    $modelType = 'catalog/layer_filter_attribute';
                }

                $filter = Mage::getModel($modelType);
                if ($this->getFilterType() == self::WIDGET_TYPE_PRICE) {
                    /**
                     * @var $filter Mage_Catalog_Model_Layer_Filter_Price
                     */
                    if ($this->getPriceRange() > 0) {
                        $filter->setPriceRange($this->getPriceRange());
                    }
                }
                $filter->setLayer($layer);
                $filter->setAttributeModel($attribute);
                return $filter;
        }

        return false;
    }

    /**
     * Get filter
     *
     * @return bool|Mage_Catalog_Model_Layer_Filter_Abstract
     */
    public function getFilter()
    {
        if (!$this->hasData('filter')) {
            $this->setData('filter', $this->_getFilter());
        }

        return $this->_getData('filter');
    }

    /**
     * Get filter label
     *
     * @return string
     */
    public function getFilterLabel()
    {
        $filter = $this->getFilter();
        if ($filter instanceof Mage_Catalog_Model_Layer_Filter_Category) {
            return $this->__('Category');
        }

        return $filter->getAttributeModel()->getStoreLabel();
    }

    /**
     * Get layer
     *
     * @return Mage_Catalog_Model_Layer
     */
    public function getLayer()
    {
        if (!$this->hasData('layer')) {
            /**
             * @var $layer Mage_Catalog_Model_Layer
             */
            $layer = Mage::getModel('catalog/layer');
            $layer->setCurrentCategory($this->getCategory());
            $this->setData('layer', $layer);
        }

        return $this->_getData('layer');
    }

    /**
     * Get options
     *
     * @return array
     */
    protected function _getOptions()
    {
        $filter = $this->getFilter();
        if ($filter instanceof Mage_Catalog_Model_Layer_Filter_Abstract) {
            return $filter->getItems();
        }

        return array();
    }

    /**
     * Sort options
     *
     * @param array $options options
     * @return mixed
     */
    protected function _sortOptions($options)
    {
        switch ($this->getSortBy())
        {
            case self::WIDGET_SORT_BY_POSITION:
                break;
            case self::WIDGET_SORT_BY_NAME:
                uasort($options, function ($a, $b) {
                    if ($a['label'] == $b['label']) {
                        return 0;
                    }

                    return ($a['label'] < $b['label']) ? -1 : 1;
                });
                break;
            case self::WIDGET_SORT_BY_PRODUCT_COUNT:
                uasort($options, function ($a, $b) {
                    if ($a['count'] == $b['count']) {
                        return 0;
                    }

                    return ($a['count'] > $b['count']) ? -1 : 1;
                });
                break;
        }

        return $options;
    }

    /**
     * Get options
     *
     * @return mixed
     */
    public function getOptions()
    {
        if (!$this->hasData('options')) {
            $options = $this->_getOptions();

            if ($options) {
                $options = $this->_sortOptions($options);

                if ($this->getLimit() > 0) {
                    $options = array_slice($options, 0, $this->getLimit());
                }
            }

            $this->setData('options', $options);
        }

        return $this->_getData('options');
    }

    /**
     * To html
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getCategory()) {
            return '';
        }

        if (!$this->getFilter()) {
            return '';
        }

        if (!$this->getOptions()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Get cache key info
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return parent::getCacheKeyInfo() + array(
            'category_id'   => $this->getCategoryId(),
            'filter_type'   => $this->getFilterType(),
            'attribute_code' => $this->getAttributeCode(),
            'price_range'   => $this->getPriceRange(),
            'sort_by'       => $this->getSortBy(),
            'limit'         => $this->getLimit(),
        );
    }
}