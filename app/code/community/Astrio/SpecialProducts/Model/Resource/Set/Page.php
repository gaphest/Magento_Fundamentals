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
class Astrio_SpecialProducts_Model_Resource_Set_Page extends Astrio_Core_Model_Resource_Abstract
{

    /**
     * Primery key auto increment flag
     *
     * @var bool
     */
    protected $_isPkAutoIncrement = false;

    protected $_storeTable;

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('astrio_specialproducts/set_page', 'page_id');

        $this->_storeTable      = $this->getTable('astrio_specialproducts/set_store');
    }

    /**
     * Get store ids
     *
     * @param  Mage_Core_Model_Abstract $object object
     * @return array
     */
    public function getStoreIds(Mage_Core_Model_Abstract $object)
    {
        /**
         * @var $page Astrio_SpecialProducts_Model_Set
         */
        $page = $object;

        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->_storeTable, array('store_id'))
            ->where('set_id = :set_id');
        $storeIds = $adapter->fetchCol($select, array(':set_id' => $page->getId()));

        if (!empty($storeIds)) {
            return $storeIds;
        }

        if (Mage::app()->isSingleStoreMode()) {
            return array(Mage::app()->getStore(true)->getId());
        }

        return array();
    }

    /**
     * After save
     *
     * @param  Mage_Core_Model_Abstract $object object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        /**
         * @var $page Astrio_SpecialProducts_Model_Set_Page
         * @var $urlModel Astrio_SpecialProducts_Model_Set_Page_Url
         */
        $page = $object;
        $urlModel = Mage::getSingleton('astrio_specialproducts/set_page_url');
        $urlModel->updateUrlRewrites($page);

        return parent::_afterSave($page);
    }
}
