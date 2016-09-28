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
class Astrio_SpecialProducts_Model_Set_Page extends Mage_Core_Model_Abstract
{
    
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'astrio_specialproducts_set_page';

    /**
     * Event object name
     *
     * @var string
     */
    protected $_eventObject = 'page';

    /**
     * Initialize resources
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('astrio_specialproducts/set_page');
    }

    /**
     * Get resource
     *
     * @return Astrio_SpecialProducts_Model_Resource_Set_Page
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
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_getData('title');
    }

    /**
     * Get url key
     *
     * @return string
     */
    public function getUrlKey()
    {
        return $this->_getData('url_key');
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->_getData('description');
    }

    /**
     * Get meta title
     *
     * @return string
     */
    public function getMetaTitle()
    {
        return $this->_getData('meta_title');
    }

    /**
     * Get meta keywords
     *
     * @return string
     */
    public function getMetaKeywords()
    {
        return $this->_getData('meta_keywords');
    }

    /**
     * Get meta description
     *
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->_getData('meta_description');
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
