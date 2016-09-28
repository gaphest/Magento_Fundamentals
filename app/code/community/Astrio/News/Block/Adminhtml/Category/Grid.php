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
class Astrio_News_Block_Adminhtml_Category_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('categoryGrid');
        $this->setDefaultSort('position');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('category_filter');
    }

    /**
     * Get store
     *
     * @return Mage_Core_Model_Store
     * @throws Exception
     */
    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    /**
     * Prepare collection
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     * @throws Mage_Core_Exception
     */
    protected function _prepareCollection()
    {
        /**
         * @var $collection Astrio_News_Model_Resource_Category_Collection
         */
        $store = $this->_getStore();
        $collection = Mage::getModel('astrio_news/category')->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('is_active')
            ->addAttributeToSelect('position');

        if ($store->getId()) {
            //$collection->setStoreId($store->getId());
            $adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
            $collection->joinAttribute(
                'name',
                'astrio_news_category/name',
                'entity_id',
                null,
                'inner',
                $adminStore
            );
            $collection->joinAttribute(
                'custom_name',
                'astrio_news_category/name',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return Astrio_News_Model_Resource_Category_Collection
     */
    public function getCollection()
    {
        return parent::getCollection();
    }

    /**
     * Prepare columns
     *
     * @return $this
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('entity_id',array(
            'header' => Mage::helper('astrio_news')->__('ID'),
            'width'  => '50px',
            'type'   => 'number',
            'index'  => 'entity_id',
        ));

        $this->addColumn('position',array(
            'header' => Mage::helper('astrio_news')->__('Position'),
            'width'  => '150px',
            'type'   => 'number',
            'index'  => 'position',
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('astrio_news')->__('Name'),
            'index'  => 'name',
        ));

        $store = $this->_getStore();
        if ($store->getId()) {
            $this->addColumn('custom_name', array(
                'header' => Mage::helper('astrio_news')->__('Name in %s', $store->getName()),
                'index'  => 'custom_name',
            ));
        }

        $this->addColumn('is_active', array(
            'header'    => Mage::helper('astrio_news')->__('Is Active'),
            'width'     => '75px',
            'index'     => 'is_active',
            'type'      => 'options',
            'options'   => Mage::getSingleton('adminhtml/system_config_source_yesno')->toArray(),
        ));

        $this->addColumn('action', array(
            'header'    => Mage::helper('astrio_news')->__('Action'),
            'width'     => '50px',
            'type'      => 'action',
            'getter'    => 'getId',
            'actions'   => array(
                array(
                    'caption' => Mage::helper('astrio_news')->__('Edit'),
                    'url'     => array(
                        'base' => '*/*/edit',
                        'params' => array('store' => $this->getRequest()->getParam('store'))
                    ),
                    'field'   => 'id'
                )
            ),
            'filter'    => false,
            'sortable'  => false,
            'index'     => 'stores',
        ));

        return parent::_prepareColumns();
    }

    /**
     * Prepare mass action
     *
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('categories');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('astrio_news')->__('Delete'),
            'url'  => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('astrio_news')->__('Are you sure?')
        ));

        Mage::dispatchEvent('adminhtml_news_category_grid_prepare_massaction', array('block' => $this));
        return $this;
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

    /**
     * Get row url
     *
     * @param Varien_Object $row row
     *
     * @return string
     * @throws Exception
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array(
                'store' => $this->getRequest()->getParam('store'),
                'id' => $row->getId())
        );
    }
}
