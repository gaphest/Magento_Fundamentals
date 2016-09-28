<?php

/* @var $this Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('astrio_documentation/document'), 'type', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
    'nullable'  => false,
    'default'   => Astrio_Documentation_Model_Document::TYPE_FILE,
    'after'     => 'description',
    'comment'   => 'Type'
));

$installer->getConnection()->addColumn($installer->getTable('astrio_documentation/document'), 'url', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
    'nullable'  => true,
    'length'    => 255,
    'after'     => 'filename',
    'comment'   => 'Url'
));

$installer->getConnection()->addColumn($installer->getTable('astrio_documentation/document'), 'file_size', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'unsigned'  => true,
    'nullable'  => true,
    'after'     => 'filename',
    'comment'   => 'File Size'
));


$installer->endSetup();
