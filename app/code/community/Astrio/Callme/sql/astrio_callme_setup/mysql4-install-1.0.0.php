<?php
/**
 * Callme installation script
 *
 * @category   Astrio
 * @package    Astrio_Callme
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();


/**
 * Create table 'astrio_callme/call_status'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('astrio_callme/call_status'))
    ->addColumn('status_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Entity ID')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false,
    ), 'Status Name')
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
        'nullable'  => false,
    ), 'Code')
    ->addIndex(
        $installer->getIdxName('astrio_callme/call_status', array('code'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('code'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    )
;

$installer->getConnection()->createTable($table);

/**
 * Create table 'astrio_callme/call'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('astrio_callme/call'))
    ->addColumn('call_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Entity ID')
    ->addColumn('phone', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false,
    ), 'Phone Number')
    ->addColumn('comment', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable'  => true,
        'default' => '',
    ), 'Comment Text')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
    ), 'Creation Time')
    ->addColumn('is_notified', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable'  => false,
        'default'   => 0,
    ), 'Notified')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => true,
        'default'   => 0,
        'unsigned'  => true,
    ), 'Notified')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
        'nullable'  => true,
        'default'   => '',
    ), 'Status')
    ->addColumn('remote_ip', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
        'nullable'  => true,
    ), 'Remote IP')
    ->addIndex(
        $installer->getIdxName('astrio_callme/call', array('store_id')),
        array('store_id')
    )
    ->addIndex(
        $installer->getIdxName('astrio_callme/call', array('status')),
        array('status')
    )
    ->addForeignKey(
        $installer->getFkName('astrio_callme/call', 'store_id', 'core/store', 'store_id'),
        'store_id',
        $installer->getTable('core/store'),
        'store_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
;

$installer->getConnection()->createTable($table);

/**
 * Create table 'astrio_callme/call_status_history'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('astrio_callme/call_status_history'))
    ->addColumn('history_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Entity Id')
    ->addColumn('call_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Call Id')
    ->addColumn('user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => true,
    ), 'Admin User Id')
    ->addColumn('is_admin_notified', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    ), 'Is Admin Notified')
    ->addColumn('comment', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
    ), 'Comment')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
    ), 'Call Status')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
    ), 'Created At')
    ->addIndex(
        $installer->getIdxName('astrio_callme/call_status_history', array('call_id')),
        array('call_id')
    )
    ->addIndex(
        $installer->getIdxName('astrio_callme/call_status_history', array('created_at')),
        array('created_at')
    )
    ->addIndex(
        $installer->getIdxName('astrio_callme/call_status_history', array('user_id')),
        array('user_id')
    )
    ->addForeignKey(
        $installer->getFkName('astrio_callme/call_status_history', 'call_id', 'astrio_callme/call', 'call_id'),
        'call_id',
        $installer->getTable('astrio_callme/call'),
        'call_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('astrio_callme/call_status_history', 'user_id', 'admin/user', 'user_id'),
        'user_id',
        $installer->getTable('admin/user'),
        'user_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Call Status History');
$installer->getConnection()->createTable($table);

$installer->endSetup();
