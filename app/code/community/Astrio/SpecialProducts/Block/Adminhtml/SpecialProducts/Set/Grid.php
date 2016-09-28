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
class Astrio_SpecialProducts_Block_Adminhtml_SpecialProducts_Set_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    
    /**
     * @var Astrio_SpecialProducts_Helper_Data
     */
    protected $_helper = null;

    /**
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
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('astrio_specialproducts_set');
        $this->setDefaultSort('set_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Prepare grid collection object
     *
     * @return Astrio_SpecialProducts_Block_Adminhtml_SpecialProducts_Set_Grid
     */
    protected function _prepareCollection()
    {
        /**
         * @var $collection Astrio_SpecialProducts_Model_Resource_Set_Collection
         */
        $collection = Mage::getResourceModel('astrio_specialproducts/set_collection');
        $this->setCollection($collection);
        $return = parent::_prepareCollection();

        $collection->addStoreIdsToResult();

        return $return;
    }

    /**
     * Get collection
     *
     * @return Astrio_SpecialProducts_Model_Resource_Set_Collection
     */
    public function getCollection()
    {
        return parent::getCollection();
    }

    /**
     * Filter store condition
     *
     * @param  Astrio_SpecialProducts_Model_Resource_Set_Collection $collection collection
     * @param  Mage_Adminhtml_Block_Widget_Grid_Column|Varien_Object $column column
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
     * Preparing columns for grid
     *
     * @return Astrio_SpecialProducts_Block_Adminhtml_SpecialProducts_Set_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('set_id', array(
            'header'    => $this->__('ID'),
            'width'     => '50px',
            'index'     => 'set_id'
        ));

        $this->addColumn('name', array(
            'header'    => $this->_getHelper()->__('Name'),
            'index'     => 'name',
            'type'      => 'text',
        ));

        $this->addColumn('identifier', array(
            'header'    => $this->_getHelper()->__('Identifier'),
            'index'     => 'identifier',
            'type'      => 'text',
        ));

        $this->addColumn('is_active', array(
            'header'    => $this->_getHelper()->__('Active'),
            'index'     => 'is_active',
            'align'     => 'left',
            'type'      => 'options',
            'options'   => Mage::getSingleton('adminhtml/system_config_source_yesno')->toArray(),
            'width'     => '70px',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_ids', array(
                'header'        => $this->_getHelper()->__('Store View'),
                'index'         => 'store_ids',
                'type'          => 'store',
                'store_all'     => true,
                'store_view'    => true,
                'sortable'      => false,
                'width'         => '170px',
                'filter_condition_callback' => array($this, '_filterStoreCondition'),
            ));
        }

        $this->addColumn('is_auto', array(
            'header'    => $this->_getHelper()->__('Select Products Automatically'),
            'index'     => 'is_auto',
            'align'     => 'left',
            'type'      => 'options',
            'options'   => Mage::getSingleton('adminhtml/system_config_source_yesno')->toArray(),
            'width'     => '70px',
        ));

        $this->addColumn('auto_type', array(
            'header'    => $this->_getHelper()->__('Auto Type'),
            'index'     => 'auto_type',
            'align'     => 'left',
            'type'      => 'options',
            'options'   => Astrio_SpecialProducts_Model_Set::getAutoTypeOptionArray(),
            'width'     => '70px',
            'sortable'  => false,
        ));

        $this->addColumn('catalog_rule_id', array(
            'header'    => $this->_getHelper()->__('Catalog Rule'),
            'index'     => 'catalog_rule_id',
            'align'     => 'left',
            'type'      => 'options',
            'options'   => Astrio_Core_Model_System_Config_Source_CatalogRule::getOptionArray(),
            'width'     => '100px',
            'sortable'  => false,
        ));

        $this->addColumn('filter_by_category_id', array(
            'header'    => $this->_getHelper()->__('Filter By Category'),
            'index'     => 'filter_by_category_id',
            'align'     => 'left',
            'type'      => 'options',
            'options'   => Astrio_Core_Model_System_Config_Source_Category::getOptionArray(),
            'width'     => '100px',
            'sortable'  => false,
        ));

        $this->addColumn('apply_label', array(
            'header'    => $this->_getHelper()->__('Apply Label'),
            'index'     => 'apply_label',
            'align'     => 'left',
            'type'      => 'options',
            'options'   => Mage::getSingleton('adminhtml/system_config_source_yesno')->toArray(),
            'width'     => '70px',
        ));

        $this->addColumn('use_separate_page', array(
            'header'    => $this->_getHelper()->__('Use Separate Page'),
            'index'     => 'use_separate_page',
            'align'     => 'left',
            'type'      => 'options',
            'options'   => Mage::getSingleton('adminhtml/system_config_source_yesno')->toArray(),
            'width'     => '70px',
        ));

        $this->addColumn('action', array(
            'header'    => $this->_getHelper()->__('Action'),
            'width'     => '50px',
            'type'      => 'action',
            'getter'    => 'getId',
            'actions'   => array(
                array(
                    'caption' => $this->_getHelper()->__('Edit'),
                    'url'     => array(
                        'base'      => '*/*/edit',
                        'params'    => array()
                    ),
                    'field'   => 'id'
                )
            ),
            'filter'    => false,
            'sortable'  => false,
        ));

        return parent::_prepareColumns();
    }

    /**
     * Get row url
     *
     * @param  Varien_Object $row row
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
