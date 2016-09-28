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
 * @package    Astrio_Documentation
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Astrio
 *
 * @category   Astrio
 * @package    Astrio_Documentation
 * @author     Eldaniz Gasymov <e.gasymov@astrio.net>
 */
class Astrio_Documentation_Block_Document_List extends Mage_Core_Block_Template
{
    // xml config path for limit
    const XML_PATH_LIMIT = 'astrio_documentation/documents/limit';
    // page parameter
    const PAGE_PARAM = 'p';
    // default limit
    const DEFAULT_LIMIT = 10;

    /**
     * @var int
     */
    protected $_limit;

    /**
     * @var Mage_Page_Block_Html_Pager
     */
    protected $_pagerBlock;

    /** @var Astrio_Documentation_Model_Resource_Document_Collection */
    protected $_collection;

    /**
     * Get documents collection
     *
     * @return Astrio_Documentation_Model_Resource_Document_Collection
     */
    public function getCollection()
    {
        if (!isset($this->_collection)) {
            /** @var Astrio_Documentation_Model_Resource_Document_Collection */
            $this->_collection = Mage::getResourceModel('astrio_documentation/document_collection')
                ->addIsActiveFilter()
                ->addFieldToFilter('type', Astrio_Documentation_Model_Document::TYPE_FILE)
                ->setOrder('position', Varien_Data_Collection::SORT_ORDER_ASC)
                ->setOrder('name', Varien_Data_Collection::SORT_ORDER_ASC)
                ->setOrder('description', Varien_Data_Collection::SORT_ORDER_DESC);
        }
        return $this->_collection;
    }

    /**
     * Before toHtml
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _beforeToHtml()
    {
        $collection = $this->getCollection();
        $this->getPagerBlock()->setCollection($collection);
        return parent::_beforeToHtml();
    }

    /**
     * Get pager block
     *
     * @return Mage_Page_Block_Html_Pager
     */
    public function getPagerBlock()
    {
        if (!isset($this->_pagerBlock)) {
            /* @var $pagerBlock Mage_Page_Block_Html_Pager */
            if ($pager = $this->getChild('pager')) {
                $pagerBlock = $pager;
            } else {
                $pagerBlock = $this->getLayout()->createBlock('page/html_pager');
            }
            $limit = $this->_getLimit();
            $pagerBlock->setAvailableLimit(array($limit => $limit));
            $pagerBlock->setUseContainer(false)
                ->setShowPerPage(false)
                ->setShowAmounts(false)
                ->setLimitVarName('limit')
                ->setPageVarName(self::PAGE_PARAM)
                ->setLimit($limit)
                ->setFrameLength(Mage::getStoreConfig('design/pagination/pagination_frame'))
                ->setJump(Mage::getStoreConfig('design/pagination/pagination_frame_skip'));
            $this->_pagerBlock = $pagerBlock;

        }
        return $this->_pagerBlock;
    }

    /**
     * Get pages
     *
     * @return string
     */
    public function getPages()
    {
        return $this->getPagerBlock()->toHtml();
    }

    /**
     * Get limit
     *
     * @return int
     */
    protected function _getLimit()
    {
        if (!isset($this->_limit)) {
            $limit = (int)Mage::getStoreConfig(self::XML_PATH_LIMIT);
            $this->_limit = ($limit > 0) ? $limit : self::DEFAULT_LIMIT;
        }

        return $this->_limit;
    }
}
