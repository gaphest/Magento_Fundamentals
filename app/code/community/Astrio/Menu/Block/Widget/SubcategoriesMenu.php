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
class Astrio_Menu_Block_Widget_SubcategoriesMenu
    extends Mage_Core_Block_Template
    implements Mage_Widget_Block_Interface
{
    /**
     * Get parent category id
     *
     * @return int
     */
    public function getParentCategoryId()
    {
        if (!$this->hasData('parent_category_id') || is_string($this->_getData('parent_category_id'))) {
            $this->setData('parent_category_id', (int) $this->_getData('parent_category_id'));
        }
        return $this->_getData('parent_category_id');
    }

    /**
     * Get depth level
     *
     * @return int
     */
    public function getDepthLevel()
    {
        if (!$this->hasData('depth_level') || is_string($this->_getData('depth_level'))) {
            $depthLevel = (int) $this->_getData('depth_level');
            if ($depthLevel <= 0) {
                $depthLevel = 1;
            }

            $this->setData('depth_level', $depthLevel);
        }
        return $this->_getData('depth_level');
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
         * @var $collection Mage_Catalog_Model_Resource_Category_Collection
         */
        $collection = Mage::getResourceModel('catalog/category_collection');
        $collection
            ->addIdFilter($categoryId)
            ->addIsActiveFilter()
            ->addNameToResult()
            ->addUrlRewriteToResult()
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('thumbnail')
        ;

        if (count($collection) <= 0) {
            return false;
        }

        /**
         * @var $category Mage_Catalog_Model_Category
         */
        $category = $collection->getFirstItem();
        if ($category->getId() != Mage::app()->getStore()->getRootCategoryId() && !$category->isInRootCategoryList()) {
            return false;
        }

        return $category;
    }

    /**
     * Get subcategories
     *
     * @param Mage_Catalog_Model_Category $category category
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    public function getSubcategories(Mage_Catalog_Model_Category $category)
    {
        /**
         * @var $collection Mage_Catalog_Model_Resource_Category_Collection
         */
        $collection = $category->getCollection();

        $collection
            ->addIsActiveFilter()
            ->addNameToResult()
            ->addUrlRewriteToResult()
            ->setOrder('position', Varien_Db_Select::SQL_ASC)
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('thumbnail')
        ;

        $collection->addFieldToFilter('parent_id', $category->getId());
        $collection->addAttributeToFilter('include_in_menu', 1);

        if ($this->getLimit() > 0) {
            $collection
                ->setCurPage(1)
                ->setPageSize($this->getLimit());
        }

        return $collection;
    }

    /**
     * Get parent category
     *
     * @return bool|Mage_Catalog_Model_Category
     */
    public function getParentCategory()
    {
        if (!$this->hasData('parent_category')) {
            $categoryId = $this->getParentCategoryId();
            $this->setData('parent_category', $this->_getCategory($categoryId));
        }

        return $this->_getData('parent_category');
    }

    /**
     * Get current parent category
     *
     * @return Mage_Catalog_Model_Category
     */
    public function getCurrentParentCategory()
    {
        if (!$this->hasData('current_parent_category')) {
            return $this->getParentCategory();
        }
        return $this->_getData('current_parent_category');
    }

    /**
     * Set current parent category
     *
     * @param Mage_Catalog_Model_Category $category category
     * @return $this
     */
    public function setCurrentParentCategory(Mage_Catalog_Model_Category $category)
    {
        $this->setData('current_parent_category', $category);
        return $this;
    }

    /**
     * Get current subcategories
     *
     * @return Mage_Catalog_Model_Resource_Category_Collection|Mage_Catalog_Model_Resource_Category_Flat_Collection
     */
    public function getCurrentSubcategories()
    {
        return $this->_getData('current_subcategories');
    }

    /**
     * Set current subcategories
     *
     * @param Mage_Catalog_Model_Resource_Category_Collection|Mage_Catalog_Model_Resource_Category_Flat_Collection $collection collection
     * @return $this
     */
    public function setCurrentSubcategories($collection)
    {
        $this->setData('current_subcategories', $collection);
        return $this;
    }

    /**
     * Get original category level
     *
     * @return int
     */
    public function getOriginalCategoryLevel()
    {
        if (!$this->hasData('original_category_level')) {
            $level = (int) $this->getParentCategory()->getLevel();
            $this->setData('original_category_level', $level);
        }
        return $this->_getData('original_category_level');
    }

    /**
     * Get max category level
     *
     * @return int
     */
    public function getMaxCategoryLevel()
    {
        if (!$this->hasData('max_category_level')) {
            $level = (int) $this->getParentCategory()->getLevel() + $this->getDepthLevel();
            $this->setData('max_category_level', $level);
        }
        return $this->_getData('max_category_level');
    }

    /**
     * To html
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getParentCategory()) {
            return '';
        }

        if (!$this->getCurrentParentCategory()) {
            return '';
        }

        if ($this->getCurrentParentCategory()->getLevel() >= $this->getMaxCategoryLevel()) {
            return '';
        }

        $subcategories = $this->getSubcategories($this->getCurrentParentCategory());
        $this->setCurrentSubcategories($subcategories);

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
            'parent_category_id' => $this->getParentCategoryId(),
            'depth_level'        => $this->getDepthLevel(),
            'limit'              => $this->getLimit(),
        );
    }
}