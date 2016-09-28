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
 *  Call Status History Collection Model
 *
 * @category   Astrio
 * @package    Astrio_Callme
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_Callme_Model_Resource_Call_Status_History_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    /**
     * Call field for setCallFilter
     *
     * @var string
     */
    protected $_callField   = 'call_id';

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('astrio_callme/call_status_history');
    }

    /**
     * Add call filter
     *
     * @param int|Astrio_Callme_Model_Call $call call
     * @return Astrio_Callme_Model_Resource_Call_Status_History_Collection
     */
    public function setCallFilter($call)
    {
        if ($call instanceof Astrio_Callme_Model_Call) {
            $callId = $call->getId();
            if ($callId) {
                $this->addFieldToFilter($this->_callField, $callId);
            } else {
                $this->_totalRecords = 0;
                $this->_setIsLoaded(true);
            }
        } else {
            $this->addFieldToFilter($this->_callField, $call);
        }

        return $this;
    }

    /**
     * Join user's name
     *
     * @return $this
     */
    public function joinUserName()
    {
        $this->getSelect()->joinLeft(
            array('au' => $this->getTable('admin/user')),
            'au.user_id = main_table.user_id',
            array('username')
        );

        return $this;
    }
}