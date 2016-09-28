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
 * @package    Astrio_Brand
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Astrio
 *
 * @category Astrio
 * @package Astrio_Brand
 * @author Eldaniz Gasymov <e.gasymov@astrio.net>
 * @see Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs
 */
class Astrio_Brand_Block_Adminhtml_Brand_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    protected $_attributeTabBlock = 'astrio_brand/adminhtml_brand_edit_tab_attributes';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('brand_info_tabs');
        $this->setDestElementId('brand_edit_form');
        $this->setTitle(Mage::helper('astrio_brand')->__('Brand Information'));
    }

    /**
     * Prepares layout
     *
     * @return Mage_Core_Block_Abstract
     * @throws Exception
     * @throws Mage_Core_Exception
     */
    protected function _prepareLayout()
    {
        $brand = $this->getBrand();

        if (!($setId = $brand->getAttributeSetId())) {
            $setId = $brand->getResource()->getEntityType()->getDefaultAttributeSetId();
        }

        $groupCollection = Mage::getResourceModel('eav/entity_attribute_group_collection')
            ->setAttributeSetFilter($setId)
            ->setSortOrder()
            ->load();

        foreach ($groupCollection as $group) {
            $attributes = $brand->getAttributes($group->getId());
            // do not add groups without attributes

            foreach ($attributes as $key => $attribute) {
                if ( !$attribute->getIsVisible() ) {
                    unset($attributes[$key]);
                }
            }

            if (count($attributes) == 0) {
                continue;
            }

            $this->addTab('group_'.$group->getId(), array(
                'label'     => Mage::helper('catalog')->__($group->getAttributeGroupName()),
                'content'   => $this->_translateHtml($this->getLayout()->createBlock($this->getAttributeTabBlock(),
                        'adminhtml.astrio.brand.edit.tab.attributes')->setGroup($group)
                        ->setGroupAttributes($attributes)
                        ->toHtml()),
            ));
        }

        /**
         * Don't display store tab for single mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addTab('websites', array(
                'label'     => Mage::helper('catalog')->__('Websites'),
                'content'   => $this->_translateHtml($this->getLayout()
                        ->createBlock('astrio_brand/adminhtml_brand_edit_tab_websites')->toHtml()),
            ));
        }

        $this->addTab('products', array(
            'label'     => Mage::helper('astrio_brand')->__('Products'),
            'url'       => $this->getUrl('*/*/products', array('_current' => true)),
            'class'     => 'ajax',
        ));

        Mage::dispatchEvent('adminhtml_brand_edit_tabs_add', array(
            'tabs' => $this,
            'brand' => $this->getBrand(),
        ));

        return parent::_prepareLayout();
    }

    /**
     * Retrieve product object from object if not from registry
     *
     * @return Astrio_Brand_Model_Brand
     */
    public function getBrand()
    {
        if (!($this->getData('brand') instanceof Astrio_Brand_Model_Brand)) {
            $this->setData('brand', Mage::registry('brand'));
        }
        return $this->getData('brand');
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
     * Sets attribute tab block
     *
     * @param Mage_Admin_Model_Block $attributeTabBlock attribute tab block
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