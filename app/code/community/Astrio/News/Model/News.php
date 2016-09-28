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
class Astrio_News_Model_News extends Astrio_News_Model_Abstract
{

    /**
     * Entity code.
     *
     * Can be used as part of method name for entity processing
     */
    const ENTITY = 'astrio_news';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'astrio_news';

    /**
     * Event object name
     *
     * @var string
     */
    protected $_eventObject = 'news';

    /**
     * Initialize resources
     */
    protected function _construct()
    {
        $this->_init('astrio_news/news');
    }

    /**
     * Get resource
     *
     * @return Astrio_News_Model_Resource_News
     */
    public function getResource()
    {
        return $this->_getResource();
    }

    /**
     * Retrieve model resource
     *
     * @return Astrio_News_Model_Resource_News
     */
    protected function _getResource()
    {
        return parent::_getResource();
    }

    /**
     * Get news title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_getData('title');
    }

    /**
     * Get all sore ids where news is presented
     *
     * @return array
     */
    public function getStoreIds()
    {
        if (!$this->hasStoreIds()) {
            $ids = $this->_getResource()->getStoreIds($this);
            $this->setStoreIds($ids);
        }

        return $this->_getData('store_ids');
    }

    /**
     * Get all sore ids where news is presented
     *
     * @return array
     */
    public function getCategoryIds()
    {
        if (!$this->hasCategoryIds()) {
            $ids = $this->_getResource()->getCategoryIds($this);
            $this->setCategoryIds($ids);
        }

        return $this->_getData('category_ids');
    }

    /**
     * Retrieve News URL
     *
     * @return string
     */
    public function getUrl()
    {
        if (!$this->getId()) {
            return false;
        }

        $params = array(
            'id' => $this->getId(),
        );

        if ($categoryId = $this->getData('current_category_id')) {
            $params['category'] = $categoryId;
        }

        if ($urlKey = $this->getUrlKey()) {
            /**
             * @var $helper Astrio_News_Helper_Data
             */
            $helper = Mage::helper('astrio_news');
            if ($route = $helper->getRouteUrlKey()) {
                if ($categoryId) {
                    $categoryRoute = $helper->getCategoryRouteUrlKey();
                    $categoryUrlKey = $this->getData('current_category_url_key');

                    if ($categoryRoute && $categoryUrlKey) {
                        $params['_direct'] = $route . '/' . $categoryRoute . '/' . $categoryUrlKey . '/' . $urlKey . $helper->getUrlSuffix();
                    }
                } else {
                    $params['_direct'] = $route . '/' . $urlKey . $helper->getUrlSuffix();
                }
            }
        }

        return Mage::getUrl('astrio_news/news/view', $params);
    }

    /**
     * Get if is assigned to store
     *
     * @return bool
     */
    public function isAssignedToStore()
    {
        return in_array(Mage_Core_Model_App::ADMIN_STORE_ID,  $this->getStoreIds()) || in_array($this->getStoreId(), $this->getStoreIds());
    }

    /**
     * Get if can show
     *
     * @return bool
     */
    public function canShow()
    {
        return parent::canShow() && $this->getData('published_at') <= Varien_Date::now();
    }

    /**
     * @return string
     */
    public function getPublishedAt()
    {
        return $this->_getData('published_at');
    }

    /**
     * Get object created at date affected current active store timezone
     *
     * @return Zend_Date
     */
    public function getPublishedAtDate()
    {
        return Mage::app()->getLocale()->date(
            Varien_Date::toTimestamp($this->getPublishedAt()),
            null,
            null,
            true
        );
    }

    /**
     * Get object created at date affected with object store timezone
     *
     * @return Zend_Date
     */
    public function getPublishedAtStoreDate()
    {
        return Mage::app()->getLocale()->storeDate(
            $this->getStore(),
            Varien_Date::toTimestamp($this->getPublishedAt()),
            true
        );
    }

    /**
     * Get formatted order created date in store timezone
     *
     * @param   string  $format   date format type (short|medium|long|full)
     * @param   boolean $showTime show time?
     * @return  string
     */
    public function getPublishedAtFormated($format, $showTime = true)
    {
        /**
         * @var $coreHelper Mage_Core_Helper_Data
         */
        $coreHelper = Mage::helper('core');
        return $coreHelper->formatDate($this->getPublishedAtStoreDate(), $format, $showTime);
    }
}
