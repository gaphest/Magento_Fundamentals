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
class Astrio_News_Block_Adminhtml_News_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    protected $_attributeTabBlock = 'astrio_news/adminhtml_news_edit_tab_attributes';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('news_info_tabs');
        $this->setDestElementId('news_edit_form');
        $this->setTitle(Mage::helper('astrio_news')->__('News Information'));
    }

    /**
     * Prepare layout
     *
     * @return Mage_Core_Block_Abstract
     * @throws Exception
     * @throws Mage_Core_Exception
     */
    protected function _prepareLayout()
    {
        $news = $this->getNews();

        if (!($setId = $news->getAttributeSetId())) {
            $setId = $news->getResource()->getEntityType()->getDefaultAttributeSetId();
        }

        $groupCollection = Mage::getResourceModel('eav/entity_attribute_group_collection')
            ->setAttributeSetFilter($setId)
            ->setSortOrder()
            ->load();

        foreach ($groupCollection as $group) {
            $attributes = $news->getAttributes($group->getId());
            // do not add groups without attributes

            foreach ($attributes as $key => $attribute) {
                if ( !$attribute->getIsVisible() ) {
                    unset($attributes[$key]);
                }
                if ($attribute->getNote()) {
                    $attribute->setNote( $this->__($attribute->getNote()) );
                }
            }

            if (count($attributes) == 0) {
                continue;
            }

            $this->addTab('group_'.$group->getId(), array(
                'label'     => Mage::helper('catalog')->__($group->getAttributeGroupName()),
                'content'   => $this->_translateHtml($this->getLayout()->createBlock($this->getAttributeTabBlock(),
                        'adminhtml.astrio.news.edit.tab.attributes')->setGroup($group)
                        ->setGroupAttributes($attributes)
                        ->toHtml()),
            ));
        }

        /**
         * Don't display store tab for single mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addTab('stores', array(
                'label'     => Mage::helper('astrio_news')->__('Stores'),
                'content'   => $this->_translateHtml($this->getLayout()
                        ->createBlock('astrio_news/adminhtml_news_edit_tab_stores')->toHtml()),
            ));
        }

        $this->addTab('categories', array(
            'label'     => Mage::helper('astrio_news')->__('Categories'),
            'content'   => $this->_translateHtml($this->getLayout()
                    ->createBlock('astrio_news/adminhtml_news_edit_tab_categories')->toHtml()),
        ));

        Mage::dispatchEvent('adminhtml_news_edit_tabs_add', array(
            'tabs' => $this,
            'news' => $this->getNews(),
        ));

        return parent::_prepareLayout();
    }

    /**
     * Retrieve news object from object if not from registry
     *
     * @return Astrio_News_Model_News
     */
    public function getNews()
    {
        if (!($this->getData('news') instanceof Astrio_News_Model_News)) {
            $this->setData('news', Mage::registry('news'));
        }

        return $this->getData('news');
    }

    /**
     * Getting attribute block name for tabs
     *
     * @return string
     */
    public function getAttributeTabBlock()
    {
        if (is_null(Mage::helper('adminhtml/catalog')->getAttributeTabBlock())) {
            return $this->_attributeTabBlock;
        }

        return Mage::helper('adminhtml/catalog')->getAttributeTabBlock();
    }

    /**
     * Set attribute tab block
     *
     * @param Astrio_News_Block_Adminhtml_Category_Edit_Tab_Attributes $attributeTabBlock attribute tab block
     * @return $this
     */
    public function setAttributeTabBlock($attributeTabBlock)
    {
        $this->_attributeTabBlock = $attributeTabBlock;
        return $this;
    }

    /**
     * Translate html content
     *
     * @param string $html html
     * @return string
     */
    protected function _translateHtml($html)
    {
        Mage::getSingleton('core/translate_inline')->processResponseBody($html);
        return $html;
    }
}
