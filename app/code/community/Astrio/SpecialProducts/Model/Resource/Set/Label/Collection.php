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
class Astrio_SpecialProducts_Model_Resource_Set_Label_Collection extends Astrio_Core_Model_Resource_Abstract_Collection
{

    // Alias for set table
    const SET_TABLE_ALIAS       = 'set';
    // Alias for set store table
    const SET_STORE_TABLE_ALIAS = 'set_store';

    /**
     * @var string
     */
    protected $_setTable;

    /**
     * @var string
     */
    protected $_setStoreTable;

    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('astrio_specialproducts/set_label');

        $this->_setTable        = $this->getTable('astrio_specialproducts/set');
        $this->_setStoreTable   = $this->getTable('astrio_specialproducts/set_store');
    }

    /**
     * Join set
     *
     * @return $this
     */
    public function joinSet()
    {
        if (!$this->getFlag('set_was_joined')) {
            $setTableAlias = self::SET_TABLE_ALIAS;
            $idFieldName = $this->getResource()->getIdFieldName();
            $this->getSelect()->join(
                array($setTableAlias => $this->_setTable),
                implode(' AND ', array(
                    "{$setTableAlias}.set_id = main_table.{$idFieldName}",
                    $this->getConnection()->quoteInto("{$setTableAlias}.apply_label = ?", 1),
                )),
                array('is_auto', 'auto_type', 'identifier')
            );

            $this->setFlag('set_was_joined', true);
        }

        return $this;
    }

    /**
     * Add active filter
     *
     * @param int $isActive is active?
     * @return $this
     */
    public function addActiveFilter($isActive = 1)
    {
        $this->joinSet();

        $setTableAlias = self::SET_TABLE_ALIAS;

        $this->getSelect()->where("{$setTableAlias}.is_active = ?", (int) $isActive);

        return $this;
    }

    /**
     * Add store filter
     *
     * @param null|Mage_Core_Model_Store|int $storeId store id or store model
     * @return $this
     */
    public function addStoreFilter($storeId = null)
    {
        $this->joinSet();

        if ($storeId instanceof Mage_Core_Model_Store) {
            $storeId = (int) $storeId->getId();
        } elseif ($storeId === null) {
            $storeId = (int) Mage::app()->getStore()->getId();
        } else {
            $storeId = (int) $storeId;
        }

        $setTableAlias      = self::SET_TABLE_ALIAS;
        $setStoreTableAlias = self::SET_STORE_TABLE_ALIAS;

        $this->getSelect()->join(
            array($setStoreTableAlias => $this->_setStoreTable),
            implode(' AND ', array(
                "{$setStoreTableAlias}.set_id = {$setTableAlias}.set_id",
                $this->getConnection()->quoteInto("{$setStoreTableAlias}.store_id = ?", $storeId)
            )),
            array()
        );

        return $this;
    }

    /**
     * Set order by priority
     *
     * @param string $dir direction
     * @return $this
     */
    public function setOrderByPriority($dir = Varien_Db_Select::SQL_DESC)
    {
        $this->getSelect()->order("main_table.priority " . $dir);

        return $this;
    }
}
