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
class Astrio_News_Model_Category extends Astrio_News_Model_Abstract
{

    /**
     * Entity code.
     *
     * Can be used as part of method name for entity processing
     */
    const ENTITY = 'astrio_news_category';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'astrio_news_category';

    /**
     * Event object name
     *
     * @var string
     */
    protected $_eventObject = 'category';

    /**
     * Initialize resources
     */
    protected function _construct()
    {
        $this->_init('astrio_news/category');
    }

    /**
     * Get resource model
     *
     * @return Astrio_News_Model_Resource_Category
     */
    public function getResource()
    {
        return $this->_getResource();
    }

    /**
     * Retrieve model resource
     *
     * @return Astrio_News_Model_Resource_Category
     */
    protected function _getResource()
    {
        return parent::_getResource();
    }

    /**
     * Get category name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_getData('name');
    }

    /**
     * Retrieve Category URL
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

        if ($urlKey = $this->getUrlKey()) {
            /**
             * @var $helper Astrio_News_Helper_Data
             */
            $helper = Mage::helper('astrio_news');
            if ($route = $helper->getRouteUrlKey()) {
                if ($categoryRoute = $helper->getCategoryRouteUrlKey()) {
                    $params['_direct'] = $route . '/' . $categoryRoute . '/' . $urlKey . $helper->getUrlSuffix();
                }
            }
        }

        return Mage::getUrl('astrio_news/category/view', $params);
    }
}
