<?php
/**
 * Astrio
 *
 * @category   Astrio
 * @package    Astrio_Documentation
 * @author     Eldaniz Gasymov <e.gasymov@astrio.net>
 */
/* @var $this Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Create table 'astrio_documentation/document'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('astrio_documentation/document'))
    ->addColumn('document_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Document Id')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 128, array(

    ), 'Name')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(

    ), 'Description')
    ->addColumn('filename', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(

    ), 'Filename')
    ->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => true,
        'unsigned'  => true,
        'default'   => null,
    ), 'Category ID')
    ->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable'  => false,
        'default'   => 0,
    ), 'Is Active')
    ->addColumn('position', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => 0,
    ), 'Position')
    ->addForeignKey(
        $installer->getFkName(
            'astrio_documentation/document',
            'category_id',
            'astrio_documentation/category',
            'category_id'
        ),
        'category_id',
        $installer->getTable('astrio_documentation/category'),
        'category_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Astrio Documentation');
$installer->getConnection()->createTable($table);

/**
 * Create table 'astrio_documentation/category'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('astrio_documentation/category'))
    ->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Category Id')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 128, array(
        'nullable'  => false,
    ), 'Name')
    ->addColumn('position', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => 0,
    ), 'Position')
    ->setComment('Astrio Documentation Category');
$installer->getConnection()->createTable($table);

/**
 * Create table 'astrio_documentation/document_product'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('astrio_documentation/document_product'))
    ->addColumn('document_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Documentation Id')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Product Id')
    ->addIndex(
        $installer->getIdxName(
            'astrio_documentation/document_product',
            array('product_id')
        ),
        array('product_id')
    )
    ->addForeignKey(
        $installer->getFkName(
            'astrio_documentation/document_product',
            'document_id',
            'astrio_documentation/document',
            'document_id'
        ),
        'document_id',
        $installer->getTable('astrio_documentation/document'),
        'document_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName(
            'astrio_documentation/document_product',
            'product_id',
            'catalog/product',
            'entity_id'
        ),
        'product_id',
        $installer->getTable('catalog/product'),
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Astrio Documentation Product');
$installer->getConnection()->createTable($table);

$installer->endSetup();
