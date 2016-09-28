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
class Astrio_Menu_Model_Menu_Item extends Mage_Core_Model_Abstract
{

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'astrio_menu_item';

    /**
     * Event object name
     *
     * @var string
     */
    protected $_eventObject = 'menu_item';

    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('astrio_menu/menu_item');
    }

    /**
     * Get resource
     *
     * @return Astrio_Menu_Model_Resource_Menu_Item
     */
    public function getResource()
    {
        return parent::getResource();
    }

    /**
     * Get stores
     *
     * @return array
     */
    public function getStores()
    {
        if (!$this->hasData('stores')) {
            $this->setData('stores', $this->getResource()->getStores($this));
        }
        return $this->getData('stores');
    }

    /**
     * Get name
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->_getData('name');
    }

    /**
     * Get position
     *
     * @return int
     */
    public function getPosition()
    {
        return (int) $this->_getData('position');
    }

    /**
     * Get item type
     *
     * @return mixed
     */
    public function getItemType()
    {
        return $this->_getData('item_type');
    }

    /**
     * Get category id
     *
     * @return mixed
     */
    public function getCategoryId()
    {
        return $this->_getData('category_id');
    }

    /**
     * Get cmd page id
     *
     * @return mixed
     */
    public function getCmsPageId()
    {
        return $this->_getData('cms_page_id');
    }

    /**
     * Get custom link
     *
     * @return mixed
     */
    public function getCustomLink()
    {
        return $this->_getData('custom_link');
    }

    /**
     * Get if is secure url
     *
     * @return bool
     */
    public function getIsSecureUrl()
    {
        return (bool) $this->_getData('is_secure_url');
    }

    /**
     * Get class link
     *
     * @return mixed
     */
    public function getClassLink()
    {
        return $this->_getData('class_link');
    }

    /**
     * Get extra
     *
     * @return mixed
     */
    public function getExtra()
    {
        return $this->_getData('extra');
    }

    /**
     * Get is alive
     *
     * @return bool
     */
    public function getIsActive()
    {
        return (bool) $this->_getData('is_active');
    }

    /**
     * Get sections
     *
     * @return mixed
     */
    public function getSections()
    {
        return $this->_getData('sections');
    }

    /**
     * Get sections array
     *
     * @return array
     */
    public function getSectionsArray()
    {
        if (!$this->hasData('sections_array')) {
            $sections = $this->getSections();
            $array = $sections ? unserialize($sections) : array();
            $this->setData('sections_array', $array);
        }

        return $this->_getData('sections_array');
    }

    /**
     * Get section
     *
     * @param int $number number
     * @return bool|string
     */
    public function getSection($number)
    {
        $sections = $this->getSectionsArray();
        if (isset($sections[$number])) {
            return $sections[$number];
        }
        return false;
    }
    /**
     * Get section html
     *
     * @param int $number number
     * @return bool|string
     */
    public function getSectionHtml($number)
    {
        $section = $this->getSection($number);
        if ($section) {
            /**
             * @var $coreHelper Astrio_Core_Helper_Cms
             */
            $coreHelper = Mage::helper('astrio_core/cms');
            return $coreHelper->processContentLikeCmsBlock($section);
        }
        return false;
    }

    /**
     * Get category
     *
     * @return bool|Mage_Catalog_Model_Category
     */
    public function getCategory()
    {
        if (!$this->hasData('category')) {
            if ($this->getItemType() == Astrio_Menu_Model_Menu_Item_Source_ItemType::CATEGORY) {
                /**
                 * @var $collection Mage_Catalog_Model_Resource_Category_Collection
                 */
                $collection = Mage::getResourceModel('catalog/category_collection');
                $collection
                    ->addIdFilter($this->getCategoryId())
                    ->addIsActiveFilter()
                    ->addNameToResult()
                    ->addUrlRewriteToResult()
                    ->addAttributeToSelect('url_key')
                    ->addAttributeToSelect('thumbnail')
                ;
                $category = count($collection) > 0 ? $collection->getFirstItem() : false;
            } else {
                $category = false;
            }

            $this->setData('category', $category);
        }

        return $this->_getData('category');
    }

    /**
     * Get cms page
     *
     * @return bool|Mage_Cms_Model_Page
     */
    public function getCmsPage()
    {
        if (!$this->hasData('cms_page')) {
            if ($this->getItemType() == Astrio_Menu_Model_Menu_Item_Source_ItemType::CMS_PAGE) {
                /**
                 * @var $collection Mage_Cms_Model_Resource_Page_Collection
                 */
                $collection = Mage::getResourceModel('cms/page_collection');
                $collection
                    ->addStoreFilter(Mage::app()->getStore())
                    ->addFieldToFilter($collection->getResource()->getIdFieldName(), $this->getCmsPageId());

                $page = count($collection) > 0 ? $collection->getFirstItem() : false;
            } else {
                $page = false;
            }

            $this->setData('cms_page', $page);
        }

        return $this->_getData('cms_page');
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        switch ($this->getItemType())
        {
            case Astrio_Menu_Model_Menu_Item_Source_ItemType::CATEGORY:
                if ($category = $this->getCategory()) {
                    return $category->getUrl();
                }
                break;
            case Astrio_Menu_Model_Menu_Item_Source_ItemType::CMS_PAGE:
                if ($page = $this->getCmsPage()) {
                    return Mage::getUrl(null, array('_direct' => $page->getIdentifier()));
                }
                break;
            case Astrio_Menu_Model_Menu_Item_Source_ItemType::CUSTOM:
                $url = trim($this->getCustomLink());
                $doNotProcess = array(
                    'http://',
                    'https://',
                    '//',
                );
                foreach ($doNotProcess as $start) {
                    if (mb_strpos($url, $start, null, 'UTF-8') === 0) {
                        return $url;
                    }
                }

                $url = Mage::getUrl($url, array('_secure' => $this->getIsSecureUrl()));

                return $url;

        }

        return '#';
    }
}