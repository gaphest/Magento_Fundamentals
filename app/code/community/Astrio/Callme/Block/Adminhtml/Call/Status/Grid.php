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
 * @package    Astrio_Callme
 * @copyright  Copyright (c) 2010-2013 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 *  Call Status grid
 *
 * @category   Astrio
 * @package    Astrio_Callme
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_Callme_Block_Adminhtml_Call_Status_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    /**
     * @var Astrio_Callme_Helper_Data
     */
    protected $_helper = null;

    /**
     * @return Astrio_Callme_Helper_Data
     */
    protected function _getHelper()
    {
        if (null === $this->_helper) {
            $this->_helper = Mage::helper('astrio_callme');
        }
        return $this->_helper;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('astrio_callStatusGrid');
        $this->setDefaultSort('name');
        $this->setDefaultDir('DESC');
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
        $collection = Mage::getResourceModel('astrio_callme/call_status_collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('status_id', array(
            'header'        => $this->_getHelper()->__('ID'),
            'align'         => 'right',
            'width'         => '50px',
            'index'         => 'status_id',
        ));

        $this->addColumn('name', array(
            'header'        => $this->_getHelper()->__('Name'),
            'align'         => 'left',
            'width'         => '100px',
            'index'         => 'name',
            'type'          => 'text',
            'escape'        => true,
        ));

        $this->addColumn('code', array(
            'header'        => $this->_getHelper()->__('Code'),
            'align'         => 'left',
            'width'         => '100px',
            'index'         => 'code',
            'type'          => 'text',
            'escape'        => true,
        ));

        return parent::_prepareColumns();
    }


    /**
     * Get edit url
     *
     * @param Mage_Catalog_Model_Product|Varien_Object $row row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/call_status/edit', array('id' => $row->getId()));
    }

    /**
     * Gets grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }
}
