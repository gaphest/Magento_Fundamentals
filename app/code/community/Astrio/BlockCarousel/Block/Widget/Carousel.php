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
 * @package    Astrio_BlockCarousel
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Block Carousel Widget Carousel block
 *
 * @category   Astrio
 * @package    Astrio_BlockCarousel
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_BlockCarousel_Block_Widget_Carousel extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface
{

    /**
     * List of rotated blocks
     *
     * @var Mage_Cms_Model_Resource_Block_Collection
     */
    protected $_blocks;

    /**
     * Template processor for Block Content
     *
     * @var Varien_Filter_Template
     */
    protected $_processor;

    /**
     * Storage for used blocks
     *
     * @var array
     */
    static protected $_blockUsageMap = array();

    /**
     * do not render template if blocks are not assigned.
     *
     * @return Mage_Cms_Model_Resource_Block_Collection
     */
    protected function _toHtml()
    {
        $blocks = $this->getBlocks();

        if ($blocks->getSize() <= 0) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Get list of widget blocks
     *
     * @return array
     */
    public function getBlocks()
    {
        if (!isset($this->_blocks)) {
            $blockIds = explode(',', (string) $this->getData('block_ids'));

            /** @var Mage_Cms_Model_Resource_Block_Collection $this->_blocks */
            $this->_blocks = Mage::getResourceModel('cms/block_collection')
                ->addStoreFilter(Mage::app()->getStore())
                ->addFieldToFilter('is_active', true)
                ->addFieldToFilter('main_table.block_id', array('in' => $blockIds));
        }
        return $this->_blocks;
    }

    /**
     * Get blocks content
     *
     * @param Mage_Cms_Model_Block $block block to render
     * @return string
     */
    public function renderBlock($block)
    {
        $blockHash = $block->getId();
        if (!isset(self::$_blockUsageMap[$blockHash])) {
            $processor = $this->_getBlockTemplateProcessor();
            $html = $processor->filter($block->getContent());

            self::$_blockUsageMap[$blockHash] = $html;
        }

        return self::$_blockUsageMap[$blockHash];
    }

    /**
     * Gets template processor for Block Content
     *
     * @return Varien_Filter_Template
     */
    protected function _getBlockTemplateProcessor()
    {
        if (!isset($this->_processor)) {
            /* @var $helper Mage_Cms_Helper_Data */
            $helper = Mage::helper('cms');
            $this->_processor = $helper->getBlockTemplateProcessor();
        }
        return $this->_processor;
    }
}
