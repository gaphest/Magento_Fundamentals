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
class Astrio_News_Model_Resource_Config extends Mage_Core_Model_Resource_Db_Abstract
{
    
    /**
     *
     * @var int
     */
    protected $_entityTypeIds   = array();

    /**
     * Store id
     *
     * @var int
     */
    protected $_storeId         = null;

    /**
     * Initialize connection
     *
     */
    protected function _construct()
    {
        $this->_init('eav/attribute', 'attribute_id');
    }

    /**
     * Set store id
     *
     * @param  integer $storeId store id
     * @return Astrio_News_Model_Resource_Config
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = (int)$storeId;
        return $this;
    }

    /**
     * Return store id.
     * If is not set return current app store
     *
     * @return integer
     */
    public function getStoreId()
    {
        if ($this->_storeId === null) {
            return Mage::app()->getStore()->getId();
        }

        return $this->_storeId;
    }

    /**
     * Retrieve astrio_news entity type id
     *
     * @param  string $entityType entity type
     * @return int
     */
    public function getEntityTypeId($entityType)
    {
        if (!isset($this->_entityTypeIds[$entityType])) {
            $this->_entityTypeIds[$entityType] = Mage::getSingleton('eav/config')->getEntityType($entityType)->getId();
        }

        return $this->_entityTypeIds[$entityType];
    }

    /**
     * Retrieve Attributes Used in listing
     *
     * @param  string $entityType entity type
     * @return array
     */
    public function getAttributesUsedInListing($entityType)
    {
        $adapter = $this->_getReadAdapter();
        $storeLabelExpr = $adapter->getCheckSql('al.value IS NOT NULL', 'al.value', 'main_table.frontend_label');

        $select  = $adapter->select()
            ->from(array('main_table' => $this->getTable('eav/attribute')))
            ->join(
                array('additional_table' => $this->getTable('astrio_news/eav_attribute')),
                'main_table.attribute_id = additional_table.attribute_id'
            )
            ->joinLeft(
                array('al' => $this->getTable('eav/attribute_label')),
                'al.attribute_id = main_table.attribute_id AND al.store_id = ' . (int)$this->getStoreId(),
                array('store_label' => $storeLabelExpr)
            )
            ->where('main_table.entity_type_id = ?', (int)$this->getEntityTypeId($entityType))
            ->where('additional_table.used_in_listing = ?', 1);

        return $adapter->fetchAll($select);
    }
}
