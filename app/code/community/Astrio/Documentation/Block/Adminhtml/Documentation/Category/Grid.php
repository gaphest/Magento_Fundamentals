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
class Astrio_Documentation_Block_Adminhtml_Documentation_Category_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('astrio_documentation_category');
        $this->setDefaultSort('category_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Prepare grid collection object
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        /**
         * @var $collection Astrio_Documentation_Model_Resource_Category_Collection
         */
        $collection = Mage::getResourceModel('astrio_documentation/category_collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Preparing colums for grid
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('category_id', array(
            'index'     => 'category_id',
            'type'      => 'number',
            'header'    => $this->__('ID'),
            'width'     => '50px',
        ));

        $this->addColumn('name', array(
            'header'    => $this->__('Name'),
            'index'     => 'name',
            'type'      => 'text',
        ));

        $this->addColumn('position', array(
            'index'     => 'position',
            'type'      => 'number',
            'header'    => $this->__('Position'),
            'width'     => '50px',
        ));

        return parent::_prepareColumns();
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
     * Get row url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }
}
