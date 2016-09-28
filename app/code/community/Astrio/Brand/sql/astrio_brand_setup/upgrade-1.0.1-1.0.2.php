<?php
/**
 * Astrio
 *
 * @category Astrio
 * @package Astrio_Brand
 * @author Eldaniz Gasymov <e.gasymov@astrio.net>
 */
/**
 * @var Astrio_Brand_Model_Resource_Setup $this
 */
$installer = $this;
$installer->startSetup();

/**
 * Modify core/url_rewrite table
 */
$installer->getConnection()
    ->addColumn($installer->getTable('core/url_rewrite'), 'brand_id', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned'  => true,
        'nullable'  => true,
        'comment'   => 'Brand ID'
    ));

$installer->getConnection()
    ->addForeignKey(
        $installer->getFkName('core/url_rewrite', 'brand_id', 'astrio_brand/brand', 'entity_id'),
        $installer->getTable('core/url_rewrite'),
        'brand_id',
        $installer->getTable('astrio_brand/brand'),
        'entity_id'
    );

$installer->endSetup();