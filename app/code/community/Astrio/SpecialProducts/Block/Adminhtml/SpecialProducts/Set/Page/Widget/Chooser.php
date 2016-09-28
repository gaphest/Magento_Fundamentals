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
class Astrio_SpecialProducts_Block_Adminhtml_SpecialProducts_Set_Page_Widget_Chooser extends Mage_Adminhtml_Block_Widget_Grid
{

    protected $_selectedSetPages = array();

    /**
     * @var Astrio_SpecialProducts_Helper_Data
     */
    protected $_helper = null;

    /**
     * Get Astrio_SpecialProducts
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
                $(grid.containerId).fire('special_products_set_page_checkbox:changed', {element: element});
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
     * @param  Mage_Adminhtml_Block_Widget_Grid_Column $column column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_sets') {
            $selected = $this->getSelectedSetPages();
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
        /* @var $collection Astrio_SpecialProducts_Model_Resource_Set_Collection */
        $collection = Mage::getResourceModel('astrio_specialproducts/set_collection');
        $collection->addUseSeparatePageFilter();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return Astrio_SpecialProducts_Model_Resource_Set_Collection
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
        $this->addColumn('in_sets', array(
            'header_css_class' => 'a-center',
            'type'          => 'checkbox',
            'name'          => 'in_sets',
            'inline_css'    => 'checkbox entities',
            'field_name'    => 'in_sets',
            'values'        => $this->getSelectedSetPages(),
            'align'         => 'center',
            'index'         => 'set_id',
            'use_index'     => true,
        ));

        $this->addColumn('set_id', array(
            'header' => $this->_getHelper()->__('ID'),
            'align'  => 'right',
            'width'  => '50px',
            'index'  => 'set_id',
        ));

        $this->addColumn('name', array(
            'header' => $this->_getHelper()->__('Name'),
            'align'  => 'left',
            'index'  => 'name',
        ));

        $this->addColumn('identifier', array(
            'header' => $this->_getHelper()->__('Identifier'),
            'align'  => 'left',
            'index'  => 'identifier',
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
        return $this->getUrl('*/specialProducts_set/widgetChooser', array(
            '_current' => true,
            'uniq_id' => $this->getId(),
            'use_massaction' => $this->getUseMassaction(),
        ));
    }

    /**
     * Set selected set pages
     *
     * @param  array $selectedSetPages selected set pages
     * @return $this
     */
    public function setSelectedSetPages($selectedSetPages)
    {
        $this->_selectedSetPages = $selectedSetPages;
        return $this;
    }

    /**
     * Get selected set pages
     *
     * @return array
     */
    public function getSelectedSetPages()
    {
        if ($selectedSetPages = $this->getRequest()->getParam('selected_set_pages', null)) {
            $this->setSelectedSetPages($selectedSetPages);
        }

        return $this->_selectedSetPages;
    }
}
