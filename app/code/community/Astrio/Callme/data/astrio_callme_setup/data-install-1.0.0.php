<?php
/**
 * Astrio
 *
 * @category Astrio
 * @package Astrio_Callme
 * @author  Demidov Ilya <i.demidov@astrio.net>
 */

/**
 * @var $installer Mage_Core_Model_Resource_Setup
 */
$installer = $this;

//Fill table astrio_callme/call_status
$callStatuses = array(
    1  => array(
        'name' => 'Pending',
        'code' => 'pending',
    ),
    2  => array(
        'name' => 'Complete',
        'code' => 'complete',
    ),
);
foreach ($callStatuses as $k => $v) {
    $bind = array(
        'status_id' => $k,
        'name'      => $v['name'],
        'code'      => $v['code'],
    );

    $installer->getConnection()->insertOnDuplicate($installer->getTable('astrio_callme/call_status'), $bind);
}
