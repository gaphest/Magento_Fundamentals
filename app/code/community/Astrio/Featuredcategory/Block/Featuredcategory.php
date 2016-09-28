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
 * @package    Astrio_Featuredcategory
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Featuredcategory block
 *
 * @category Astrio
 * @package  Astrio_Featuredcategory
 * @author   Astrio developers <developers@astrio.net>
 */
class Astrio_Featuredcategory_Block_Featuredcategory extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface
{
    // default subcategory count
    const DEFAULT_SUBCATEGORY_COUNT = 3;

    protected $_currentCategory = null;

    protected $_currentCategoryId = null;

    protected $_flatCategory = null;

    /**
     * Get current category id
     *
     * @return int
     */
    public function getCurrentCategoryId()
    {
        if ($this->_currentCategoryId === null) {
            if ($this->getCurCategory()) {
                // from cache
                $this->_currentCategoryId = $this->getCurCategory();
            } elseif ($category = Mage::registry('current_category')) {
                // current category
                $this->_currentCategoryId = $category->getId();
            } else {
                // root category
                $this->_currentCategoryId = Mage::app()->getStore()->getRootCategoryId();
            }
        }

        return $this->_currentCategoryId;
    }

    /**
     * Get current category
     *
     * @return Mage_Catalog_Model_Category
     */
    public function getCurrentCategory()
    {
        if ($this->_currentCategory === null) {

            $category = Mage::registry('current_category');
            if ($category && $category->getId() == $this->getCurrentCategoryId()) {
                $this->_currentCategory = $category;
            } else {
                $this->_currentCategory = Mage::getModel('catalog/category')->load($this->getCurrentCategoryId());
            }
        }

        return $this->_currentCategory;
    }

    /**
     * Get if is flat category enabled
     *
     * @return bool
     */
    public function isFlatCategoryEnabled()
    {
        if ($this->_flatCategory === null) {
            /**
             * @var $flatHelper Mage_Catalog_Helper_Category_Flat
             */
            $flatHelper = Mage::helper('catalog/category_flat');
            $this->_flatCategory = $flatHelper->isAvailable() && $flatHelper->isBuilt(true);
        }

        return $this->_flatCategory;
    }

    /**
     * Get param SubCatNum
     *
     * @return int
     */
    protected function _getSubCatNum()
    {
        if ($this->getData('sub_cat_num')) {
            return (int) $this->getData('sub_cat_num');
        }

        return self::DEFAULT_SUBCATEGORY_COUNT;
    }

    /**
     * Get children categories
     *
     * @param Mage_Catalog_Model_Category $category    category
     * @param bool                        $subcategory subcategory
     * @return Mage_Catalog_Model_Resource_Category_Collection | Mage_Catalog_Model_Resource_Category_Flat_Collection
     */
    public function getChildrenCategories(Mage_Catalog_Model_Category $category, $subcategory = true)
    {
        $collection = Mage::getModel('catalog/category')->getCollection();

        /**
         * @var $collection Mage_Catalog_Model_Resource_Category_Collection | Mage_Catalog_Model_Resource_Category_Flat_Collection
         */
        $collection
            ->setStoreId(Mage::app()->getStore()->getId())
            ->addIdFilter($category->getChildren())
            ->addIsActiveFilter()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('image')
            ->setOrder('position', Varien_Db_Select::SQL_ASC)
        ;

        if ($this->isFlatCategoryEnabled()) {
            /**
             * @var $collection Mage_Catalog_Model_Resource_Category_Flat_Collection
             */
            $collection
                ->addUrlRewriteToResult()
                ->addStoreFilter();
        } else {
            /**
             * @var $collection Mage_Catalog_Model_Resource_Category_Collection
             */
            $collection
                ->joinUrlRewrite();
        }

        if ($subcategory) {
            if (!$this->getAllSubcategory()) {
                $collection->addAttributeToFilter('is_featured', 1);
            }
            $collection->getSelect()
                ->reset(Zend_Db_Select::ORDER)
                ->order(new Zend_Db_Expr('RAND()'))
                ->limit($this->_getSubCatNum());
        } else {
            if (!$this->getAllSubcategory()) {
                $collection->addAttributeToFilter('is_featured', 1);
            }
            if ($this->getCatNum()) {
                $collection
                    ->getSelect()
                    ->reset(Zend_Db_Select::ORDER)
                    ->order(new Zend_Db_Expr('RAND()'))
                    ->limit($this->getCatNum());
            }
        }

        $collection->load();

        return $collection;
    }

    /**
     * Get collection category
     *
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    protected function _beforeToHtml()
    {
        $collection = $this->getChildrenCategories($this->getCurrentCategory(), false);
        $this->setCategoryCollection($collection);
    }

    /**
     * Get cache key informative items that must be preserved in cache placeholders
     * for block to be rerendered by placeholder
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $items = array(
            'sub_cat_num'       => serialize($this->_getSubCatNum()),
            'all_category'      => serialize($this->getAllCategory()),
            'all_subcategory'   => serialize($this->getAllSubcategory()),
            'cur_category'      => serialize($this->getCurrentCategoryId()),
            'cat_num'           => serialize((int) $this->getCatNum()),
        );

        return parent::getCacheKeyInfo() + $items;
    }

}
