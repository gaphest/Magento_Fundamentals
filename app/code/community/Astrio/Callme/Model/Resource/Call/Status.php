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
 *  Call Status Resource Model
 *
 * @category   Astrio
 * @package    Astrio_Callme
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_Callme_Model_Resource_Call_Status extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('astrio_callme/call_status', 'status_id');
    }

    /**
     * Get All Call Statuses
     *
     * @return array
     */
    public function getAllStatuses()
    {
        /** @var Magento_Db_Adapter_Pdo_Mysql $connection */
        $connection = $this->getReadConnection();
        $select = $connection->select();
        $select->from(array('main_table' => $this->getMainTable()), array('code', 'name'))
            ->order('main_table.name ASC');
        return $connection->fetchPairs($select);
    }
}
