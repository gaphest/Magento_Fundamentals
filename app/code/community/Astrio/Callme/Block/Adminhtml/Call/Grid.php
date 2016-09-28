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
 *  Calls grid
 *
 * @category   Astrio
 * @package    Astrio_Callme
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_Callme_Block_Adminhtml_Call_Grid extends Mage_Adminhtml_Block_Widget_Grid
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
        $this->setId('astrio_callGrid');
        $this->setDefaultSort('created_at');
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
        $collection = Mage::getResourceModel('astrio_callme/call_collection');
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
        $this->addColumn('call_id', array(
            'header'        => $this->_getHelper()->__('ID'),
            'align'         => 'right',
            'width'         => '50px',
            'index'         => 'call_id',
        ));

        $this->addColumn('created_at', array(
            'header'        => $this->_getHelper()->__('Created On'),
            'align'         => 'left',
            'type'          => 'datetime',
            'width'         => '100px',
            'index'         => 'created_at',
        ));

        $this->addColumn('phone', array(
            'header'        => $this->_getHelper()->__('Phone Number'),
            'align'         => 'left',
            'width'         => '100px',
            'index'         => 'phone',
            'type'          => 'text',
            'escape'        => true,
        ));

        $this->addColumn('comment', array(
            'header'        => $this->_getHelper()->__('Comment'),
            'align'         => 'left',
            'index'         => 'comment',
            'type'          => 'text',
            'truncate'      => 200,
            'nl2br'         => true,
            'escape'        => true,
            'width'         => '300px',
        ));

        $this->addColumn('is_notified', array(
            'header'        => $this->_getHelper()->__('Notified'),
            'align'         => 'left',
            'type'          => 'options',
            'width'         => '100px',
            'index'         => 'is_notified',
            'options'       => Mage::getModel('adminhtml/system_config_source_yesno')->toArray()
        ));

        $this->addColumn('status', array(
            'header'        => $this->_getHelper()->__('Status'),
            'align'         => 'left',
            'type'          => 'options',
            'width'         => '100px',
            'index'         => 'status',
            'options'       => Mage::getModel('astrio_callme/call_status_source')->toArray()
        ));

        return parent::_prepareColumns();
    }

    /**
     * Prepare mass action
     */
    protected function _prepareMassaction()
    {

    }

    /**
     * get edit url
     *
     * @param Mage_Catalog_Model_Product|Varien_Object $row row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/call/view', array('call_id' => $row->getId()));
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
