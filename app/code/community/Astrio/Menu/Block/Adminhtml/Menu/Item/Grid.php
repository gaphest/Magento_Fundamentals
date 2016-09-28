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
class Astrio_Menu_Block_Adminhtml_Menu_Item_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    /**
     * @var Astrio_Menu_Helper_Data
     */
    protected $_helper = null;

    /**
     * @return Astrio_Menu_Helper_Data
     */
    protected function _getHelper()
    {
        if (null === $this->_helper) {
            $this->_helper = Mage::helper('astrio_menu');
        }
        return $this->_helper;
    }
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('astrio_menu_menu_item');
        $this->setDefaultSort('position');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Prepare grid collection object
     *
     * @return Astrio_Menu_Block_Adminhtml_Menu_Item_Grid
     */
    protected function _prepareCollection()
    {
        /**
         * @var $collection Astrio_Menu_Model_Resource_Menu_Item_Collection
         */
        $collection = Mage::getResourceModel('astrio_menu/menu_item_collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Preparing columns for grid
     *
     * @return Astrio_Menu_Block_Adminhtml_Menu_Item_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('item_id', array(
            'header'    => $this->__('ID'),
            'width'     => '50px',
            'index'     => 'item_id'
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('stores', array(
                'header'        => $this->_getHelper()->__('Store View'),
                'index'         => 'stores',
                'type'          => 'store',
                'store_all'     => true,
                'store_view'    => true,
                'sortable'      => false,
                'filter_condition_callback' => array($this, '_filterStoreCondition'),
            ));
        }

        $this->addColumn('position', array(
            'header'    => $this->_getHelper()->__('Position'),
            'index'     => 'position',
            'type'      => 'number',
        ));

        $this->addColumn('name', array(
            'header'    => $this->_getHelper()->__('Name'),
            'index'     => 'name',
            'type'      => 'text',
        ));

        $this->addColumn('item_type', array(
            'header'    => $this->_getHelper()->__('Item Type'),
            'index'     => 'item_type',
            'align'     => 'left',
            'type'      => 'options',
            'options'   => Astrio_Menu_Model_Menu_Item_Source_ItemType::toOptionArray(),
        ));

        $this->addColumn('custom_link', array(
            'header'    => $this->_getHelper()->__('Link'),
            'index'     => 'custom_link',
            'type'      => 'text',
        ));

        /**
         * @var $sourceCategory Astrio_Core_Model_System_Config_Source_Category
         */
        $sourceCategory = Mage::getModel('astrio_core/system_config_source_category');
        $this->addColumn('category_id', array(
            'header'    => $this->_getHelper()->__('Category'),
            'index'     => 'category_id',
            'type'      => 'options',
            'options'   => $sourceCategory->toArray(true),
        ));

        /**
         * @var $sourceCmsPage Astrio_Core_Model_System_Config_Source_CmsPage
         */
        $sourceCmsPage = Mage::getModel('astrio_core/system_config_source_cmsPage');
        $this->addColumn('cms_page_id', array(
            'header'    => $this->_getHelper()->__('CMS Page'),
            'index'     => 'cms_page_id',
            'type'      => 'options',
            'options'   => $sourceCmsPage->toArray(true),
        ));

        $this->addColumn('is_active', array(
            'header'    => $this->_getHelper()->__('Active'),
            'index'     => 'is_active',
            'align'     => 'left',
            'type'      => 'options',
            'options'   => Mage::getSingleton('adminhtml/system_config_source_yesno')->toArray(),
        ));

        return parent::_prepareColumns();
    }

    /**
     * After load collection
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }

    /**
     * Filter storage condition
     *
     * @param Mage_Core_Model_Resource_Db_Collection_Abstract $collection collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column|Varien_Object $column column
     * @return $this
     */
    protected function _filterStoreCondition($collection, $column)
    {
        if ($value = $column->getFilter()->getValue()) {
            $this->getCollection()->addStoreFilter($value);
        }

        return $this;
    }

    /**
     * Get row url
     *
     * @param Mage_Catalog_Model_Product|Varien_Object $row row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }
}