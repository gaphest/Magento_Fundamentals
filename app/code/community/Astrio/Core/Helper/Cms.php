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
 * @package    Astrio_Core
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Astrio
 *
 * @category Astrio
 * @package Astrio_Core
 * @author Eldaniz Gasymov <e.gasymov@astrio.net>
 */
class Astrio_Core_Helper_Cms extends Mage_Core_Helper_Abstract
{
    /**
     * Get cms block html
     *
     * @param int|string $blockId block id
     * @return string
     */
    public function getCmsBlockHtml($blockId)
    {
        /**
         * @var $cmsBlock Mage_Cms_Block_Block
         */
        $cmsBlock = Mage::app()->getLayout()->createBlock('cms/block');
        $cmsBlock->setBlockId($blockId);
        return $cmsBlock->toHtml();
    }

    /**
     * Process content like cms block
     *
     * @param string $content content
     * @return string
     */
    public function processContentLikeCmsBlock($content)
    {
        /**
         * @var $cmsHelper Mage_Cms_Helper_Data
         */
        $cmsHelper = Mage::helper('cms');
        $processor = $cmsHelper->getBlockTemplateProcessor();
        return $processor->filter($content);
    }

    /**
     * Process content like cms page
     *
     * @param string $content content
     * @return string
     */
    public function processContentLikeCmsPage($content)
    {
        /**
         * @var $cmsHelper Mage_Cms_Helper_Data
         */
        $cmsHelper = Mage::helper('cms');
        $processor = $cmsHelper->getPageTemplateProcessor();
        return $processor->filter($content);
    }
}