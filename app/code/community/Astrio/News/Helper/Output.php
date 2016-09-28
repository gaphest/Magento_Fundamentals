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
class Astrio_News_Helper_Output extends Mage_Core_Helper_Abstract
{

    /**
     * Array of existing handlers
     *
     * @var array
     */
    protected $_handlers;

    /**
     * Template processor instance
     *
     * @var Varien_Filter_Template
     */
    protected $_templateProcessor = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        Mage::dispatchEvent('astrio_news_helper_output_construct', array('helper' => $this));
    }

    /**
     * @return Mage_Catalog_Helper_Data
     */
    protected function _getCatalogHelper()
    {
        return Mage::helper('catalog');
    }

    /**
     * Prepare news attribute html output
     *
     * @param  Astrio_News_Model_News $news          news
     * @param  string                 $attributeHtml attribute html
     * @param  string                 $attributeName attribute name
     * @return string
     */
    public function newsAttribute($news, $attributeHtml, $attributeName)
    {
        /**
         * @var $eavConfig Mage_Eav_Model_Config
         */
        $eavConfig = Mage::getSingleton('eav/config');
        $attribute = $eavConfig->getAttribute(Astrio_News_Model_News::ENTITY, $attributeName);

        if ($attribute && $attribute->getId()) {
            if ($attribute->getFrontendInput() != 'media_image' && !$attribute->getIsWysiwygEnabled()) {
                if ($attribute->getFrontendInput() == 'textarea') {
                    $attributeHtml = nl2br($attributeHtml);
                }
            }

            if ($attribute->getIsWysiwygEnabled()) {
                if ($this->_getCatalogHelper()->isUrlDirectivesParsingAllowed()) {
                    $attributeHtml = $this->_getTemplateProcessor()->filter($attributeHtml);
                }
            }
        }

        $attributeHtml = $this->process('newsAttribute', $attributeHtml, array(
            'news'   => $news,
            'attribute' => $attributeName
        ));

        return $attributeHtml;
    }

    /**
     * Prepare category attribute html output
     *
     * @param  Astrio_News_Model_Category $category      category
     * @param  string                     $attributeHtml attribute html
     * @param  string                     $attributeName attribute name
     * @return string
     */
    public function categoryAttribute($category, $attributeHtml, $attributeName)
    {
        /**
         * @var $eavConfig Mage_Eav_Model_Config
         */
        $eavConfig = Mage::getSingleton('eav/config');
        $attribute = $eavConfig->getAttribute(Astrio_News_Model_Category::ENTITY, $attributeName);

        if ($attribute && $attribute->getId()) {
            if ($attribute->getFrontendInput() != 'media_image' && !$attribute->getIsWysiwygEnabled()) {
                if ($attribute->getFrontendInput() == 'textarea') {
                    $attributeHtml = nl2br($attributeHtml);
                }
            }

            if ($attribute->getIsWysiwygEnabled()) {
                if ($this->_getCatalogHelper()->isUrlDirectivesParsingAllowed()) {
                    $attributeHtml = $this->_getTemplateProcessor()->filter($attributeHtml);
                }
            }
        }

        $attributeHtml = $this->process('categoryAttribute', $attributeHtml, array(
            'category'  => $category,
            'attribute' => $attributeName
        ));

        return $attributeHtml;
    }

    /**
     * Get template processor
     *
     * @return Varien_Filter_Template
     */
    protected function _getTemplateProcessor()
    {
        if (null === $this->_templateProcessor) {
            $this->_templateProcessor = $this->_getCatalogHelper()->getPageTemplateProcessor();
        }

        return $this->_templateProcessor;
    }

    /**
     * Adding method handler
     *
     * @param  string $method  method
     * @param  object $handler handler
     * @return Mage_Catalog_Helper_Output
     */
    public function addHandler($method, $handler)
    {
        if (!is_object($handler)) {
            return $this;
        }

        $method = strtolower($method);

        if (!isset($this->_handlers[$method])) {
            $this->_handlers[$method] = array();
        }

        $this->_handlers[$method][] = $handler;
        return $this;
    }

    /**
     * Get all handlers for some method
     *
     * @param  string $method method
     * @return array
     */
    public function getHandlers($method)
    {
        $method = strtolower($method);
        return isset($this->_handlers[$method]) ? $this->_handlers[$method] : array();
    }

    /**
     * Process all method handlers
     *
     * @param  string $method method
     * @param  mixed  $result result
     * @param  array  $params params
     * @return mixed
     */
    public function process($method, $result, $params)
    {
        foreach ($this->getHandlers($method) as $handler) {
            if (method_exists($handler, $method)) {
                $result = $handler->$method($this, $result, $params);
            }
        }

        return $result;
    }
}
