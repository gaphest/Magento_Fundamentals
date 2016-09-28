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
 */
class Astrio_Brand_Block_Adminhtml_Brand_Widget_Chooser extends Mage_Adminhtml_Block_Widget_Grid
{

    protected $_selectedBrands = array();

    /**
     * @var Astrio_Brand_Helper_Data
     */
    protected $_helper = null;

    /**
     * @return Astrio_Brand_Helper_Data
     */
    protected function _getHelper()
    {
        if (null === $this->_helper) {
            $this->_helper = Mage::helper('astrio_brand');
        }
        return $this->_helper;
    }

    /**
     * Block construction, prepare grid params
     *
     * @param array $arguments Object data
     */
    public function __construct($arguments=array())
    {
        parent::__construct($arguments);
        $this->setDefaultSort('name');
        $this->setUseAjax(true);
    }

    /**
     * Checkbox Check JS Callback
     *
     * @return string
     */
    public function getCheckboxCheckCallback()
    {
        return "function (grid, element) {
                $(grid.containerId).fire('brands_checkbox:changed', {element: element});
            }";
    }

    /**
     * Grid Row JS Callback
     *
     * @return string
     */
    public function getRowClickCallback()
    {
        return false;
    }

    /**
     * Filter checked/unchecked rows in grid
     *
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_brands') {
            $selected = $this->getSelectedBrands();
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addIdFilter($selected);
            } else {
                $this->getCollection()->addIdFilter($selected, true);
            }

            return $this;
        }

        return parent::_addColumnFilterToCollection($column);
    }

    /**
     * Prepare collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        /* @var $collection Astrio_Brand_Model_Resource_Brand_Collection */
        $collection = Mage::getModel('astrio_brand/brand')->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('is_active');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return Astrio_Brand_Model_Resource_Brand_Collection
     */
    public function getCollection()
    {
        return parent::getCollection();
    }

    /**
     * Prepare columns for  grid
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('in_brands', array(
            'header_css_class' => 'a-center',
            'type'          => 'checkbox',
            'name'          => 'in_brands',
            'inline_css'    => 'checkbox entities',
            'field_name'    => 'in_brands',
            'values'        => $this->getSelectedBrands(),
            'align'         => 'center',
            'index'         => 'entity_id',
            'use_index'     => true,
        ));

        $this->addColumn('entity_id', array(
            'header' => Mage::helper('catalog')->__('ID'),
            'width' => '50px',
            'type'  => 'number',
            'index' => 'entity_id',
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('catalog')->__('Name'),
            'index' => 'name',
        ));

        $this->addColumn('is_active', array(
            'header' => Mage::helper('catalog')->__('Is Active'),
            'index' => 'is_active',
            'type'  => 'options',
            'options' => Mage::getSingleton('adminhtml/system_config_source_yesno')->toArray(),
        ));

        return parent::_prepareColumns();
    }

    /**
     * Adds additional parameter to URL for loading only products grid
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/brand/widgetChooser', array(
            '_current' => true,
            'uniq_id' => $this->getId(),
            'use_massaction' => $this->getUseMassaction(),
        ));
    }

    /**
     * Setter
     *
     * @param array $selectedBrands selected brands
     * @return $this
     */
    public function setSelectedBrands($selectedBrands)
    {
        $this->_selectedBrands = $selectedBrands;
        return $this;
    }

    /**
     * Getter
     *
     * @return array
     */
    public function getSelectedBrands()
    {
        if ($selectedBrands = $this->getRequest()->getParam('selected_brands', null)) {
            $this->setSelectedBrands($selectedBrands);
        }
        return $this->_selectedBrands;
    }
}
