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
class Astrio_SpecialProducts_Block_Adminhtml_SpecialProducts_Set_Edit_Tab_Products
    extends Mage_Adminhtml_Block_Widget_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    /**
     * @var Astrio_SpecialProducts_Helper_Data
     */
    protected $_helper = null;

    /**
     * Get Astrio_SpecialProducts helper
     *
     * @return Astrio_SpecialProducts_Helper_Data
     */
    protected function _getHelper()
    {
        if (null === $this->_helper) {
            $this->_helper = Mage::helper('astrio_specialproducts');
        }

        return $this->_helper;
    }

    /**
     * Constructor
     *
     * @param array $attributes attributes
     */
    public function __construct($attributes=array())
    {
        parent::__construct($attributes);
        $this->setUseAjax(true);
        if (!$this->_getSet()->getIsAuto()) {
            $this->setDefaultSort('entity_id');
            $this->setDefaultFilter(array('in_products' => 1));
        } else {
            $this->setDefaultSort('position');
            $this->setDefaultDir('ASC');
        }
    }

    /**
     * Get special products set from registry
     *
     * @return Astrio_SpecialProducts_Model_Set
     */
    protected function _getSet()
    {
        return Mage::registry('astrio_specialproducts_set');
    }

    /**
     * Get store id
     *
     * @return mixed
     */
    public function getStoreId()
    {
        return $this->_getData('store_id');
    }

    /**
     * Prepare collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $set = $this->_getSet();

        /**
         * @var $collection Mage_Catalog_Model_Resource_Product_Collection
         */
        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->addStoreFilter($this->getStoreId());

        $attributes = array(
            'name',
            'status',
            'visibility',
            'price',
        );

        foreach ($attributes as $attrCode) {
            $collection->joinAttribute(
                $attrCode,
                Mage_Catalog_Model_Product::ENTITY . '/' . $attrCode,
                'entity_id',
                null,
                'left',
                Mage_Core_Model_App::ADMIN_STORE_ID
            );
        }

        if ($set->getIsAuto()) {
            $set->joinProductCollectionToSpecialProductsSet($collection, $this->getStoreId());
        }

        Mage::dispatchEvent('adminhtml_specialproducts_set_edit_tab_products_prepare_collection', array('tab' => $this, 'collection' => $collection));

        $this->setCollection($collection);

        $return = parent::_prepareCollection();
        if ($set->usesCustomerGroups()) {
            $set->getResource()->addCustomerGroupIdsToSpecialProducts($collection, $set, $this->getStoreId());
        }

        Mage::dispatchEvent('adminhtml_specialproducts_set_edit_tab_products_prepare_collection_after', array('tab' => $this, 'collection' => $collection));

        return $return;
    }

    /**
     * Sets sorting order by some column
     *
     * @param  Mage_Adminhtml_Block_Widget_Grid_Column $column column
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _setCollectionOrder($column)
    {
        $collection = $this->getCollection();
        if ($collection) {
            $columnIndex = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();
            if ($columnIndex == 'position') {
                $collection->getSelect()->order(Astrio_SpecialProducts_Model_Resource_Set::SPECIAL_PRODUCTS_TABLE_ALIAS . "." . $columnIndex . " " . strtoupper($column->getDir()));
            }
        }

        return parent::_setCollectionOrder($column);
    }

    /**
     * Add filter
     *
     * @param  object $collection collection
     * @param  object $column     column
     * @return $this
     */
    protected function _filterProductsCondition($collection, $column)
    {
        $productIds = $this->_getSelectedProducts();
        if (empty($productIds)) {
            $productIds = 0;
        }

        if ($column->getFilter()->getValue()) {
            $this->getCollection()->addFieldToFilter('entity_id', array('in' => $productIds));
        } else {
            if ($productIds) {
                $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $productIds));
            }
        }

        return $this;
    }

    /**
     * Add filter
     *
     * @param  object $collection collection
     * @param  object $column     column
     * @return $this
     */
    protected function _filterCustomerGroupCondition($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        $this->getCollection()->getSelect()->where(Astrio_SpecialProducts_Model_Resource_Set::SPECIAL_PRODUCTS_TABLE_ALIAS .".customer_group_id = ?", (int) $value);
        return $this;
    }

    /**
     * Add columns to grid
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        if (!$this->_getSet()->getIsAuto()) {
            $this->addColumn('in_products', array(
                'header_css_class'  => 'a-center',
                'name'              => 'in_products',
                'type'              => 'checkbox',
                'values'            => $this->_getSelectedProducts(),
                'align'             => 'center',
                'index'             => 'entity_id',
                'filter_condition_callback' => array($this, '_filterProductsCondition'),
            ));
        }

        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('catalog')->__('ID'),
            'sortable'  => true,
            'width'     => 60,
            'index'     => 'entity_id',
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('catalog')->__('Name'),
            'index'     => 'name',
        ));

        $this->addColumn('type', array(
            'header'    => Mage::helper('catalog')->__('Type'),
            'width'     => 130,
            'index'     => 'type_id',
            'type'      => 'options',
            'options'   => Mage::getSingleton('catalog/product_type')->getOptionArray(),
        ));

        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->load()
            ->toOptionHash();

        $this->addColumn('set_name', array(
            'header'    => Mage::helper('catalog')->__('Attrib. Set Name'),
            'width'     => 130,
            'index'     => 'attribute_set_id',
            'type'      => 'options',
            'options'   => $sets,
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('catalog')->__('Status'),
            'width'     => 90,
            'index'     => 'status',
            'type'      => 'options',
            'options'   => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        ));

        $this->addColumn('visibility', array(
            'header'    => Mage::helper('catalog')->__('Visibility'),
            'width'     => 90,
            'index'     => 'visibility',
            'type'      => 'options',
            'options'   => Mage::getSingleton('catalog/product_visibility')->getOptionArray(),
        ));

        $this->addColumn('sku', array(
            'header'    => Mage::helper('catalog')->__('SKU'),
            'width'     => 120,
            'index'     => 'sku',
        ));

        $this->addColumn('price', array(
            'header'        => Mage::helper('catalog')->__('Price'),
            'type'          => 'currency',
            'currency_code' => (string)Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index'         => 'price',
            'width'         => 75,
        ));

        $set = $this->_getSet();
        if ($set->usesCustomerGroups()) {
            $groups = Mage::getResourceModel('customer/group_collection')
                ->load()
                ->toOptionHash();
            $this->addColumn('customer_group_ids', array(
                'header'    => $this->_getHelper()->__('Customer Group'),
                'width'     => 120,
                'index'     => 'customer_group_ids',
                'type'      => 'options',
                'options'   => $groups,
                'filter_condition_callback' => array($this, '_filterCustomerGroupCondition'),
            ));
        }

        $this->addColumn('position', array(
            'header'         => Mage::helper('catalog')->__('Position'),
            'width'          => 60,
            'name'           => 'position',
            'type'           => 'number',
            'validate_class' => 'validate-number',
            'index'          => 'position',
            'editable'       => !$this->_getSet()->getIsAuto(),
            'edit_only'      => false,
            'filter'         => false,
            'sortable'       => $this->_getSet()->getIsAuto(),
        ));

        Mage::dispatchEvent('adminhtml_specialproducts_set_edit_tab_products_prepare_columns', array('tab' => $this));
        return parent::_prepareColumns();
    }

    /**
     * Retrieve grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/storeProductsGrid', array('id' => $this->_getSet()->getId(), 'store_id' => $this->getStoreId()));
    }

    /**
     * Get selected products
     *
     * @return array|mixed
     * @throws Exception
     */
    protected function _getSelectedProducts()
    {
        $products = $this->getRequest()->getPost('products_' . $this->getRequest()->getParam('store_id'), null);
        if (!is_array($products)) {
            $products = array_keys($this->getSelectedProducts());
        }

        return $products;
    }

    /**
     * Get selected products
     *
     * @return array
     */
    public function getSelectedProducts()
    {
        return $this->_getSet()->getProductsByStore($this->getStoreId());
    }

    /**
     * Prepare content for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->_getHelper()->__('Products (%s)', Mage::app()->getStore($this->getStoreId())->getName());
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->_getHelper()->__('Products (%s)', Mage::app()->getStore($this->getStoreId())->getName());
    }

    /**
     * Returns status flag about this tab can be showed or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return $this->_getSet()->getId() > 0;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return $this->_getSet()->getId() <= 0;
    }
}
