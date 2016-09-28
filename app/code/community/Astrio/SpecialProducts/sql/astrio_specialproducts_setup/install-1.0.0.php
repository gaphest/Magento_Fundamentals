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
/* @var $this Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Create table 'astrio_specialproducts/set'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('astrio_specialproducts/set'))
    ->addColumn('set_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Set Id')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 128, array(
        'nullable'  => false,
        'default'   => '',
    ), 'Name')
    ->addColumn('identifier', Varien_Db_Ddl_Table::TYPE_TEXT, 128, array(
        'nullable'  => false,
    ), 'Identifier')
    ->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable'  => false,
        'default'   => 0,
    ), 'Is Active')
    ->addColumn('is_auto', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable'  => false,
        'default'   => 0,
    ), 'Is Auto')
    ->addColumn('auto_type', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => 0,
    ), 'Auto Type')
    ->addColumn('catalog_rule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => true,
    ), 'Rule Id')
    ->addColumn('filter_by_category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => true,
    ), 'Filter By Category Id')
    ->addColumn('filter_greater_than', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => true,
    ), 'Filter Greater Than')
    ->addColumn('filter_in_last_days', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => true,
    ), 'Filter In Last Days')
    ->addColumn('products_limit', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => 0,
    ), 'Products Limit')
    ->addColumn('apply_label', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable'  => false,
        'default'   => 0,
    ), 'Apply Label')
    ->addColumn('use_separate_page', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable'  => false,
        'default'   => 0,
    ), 'Use Separate Page')
    ->addIndex(
        $installer->getIdxName('astrio_specialproducts/set', array('identifier')),
        array('identifier'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX)
    )
    ->addForeignKey(
        $installer->getFkName(
            'astrio_specialproducts/set',
            'catalog_rule_id',
            'catalogrule/rule',
            'rule_id'
        ),
        'catalog_rule_id',
        $installer->getTable('catalogrule/rule'),
        'rule_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName(
            'astrio_specialproducts/set',
            'filter_by_category_id',
            'catalog/category',
            'entity_id'
        ),
        'filter_by_category_id',
        $installer->getTable('catalog/category'),
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Special Products Set');
$installer->getConnection()->createTable($table);

/**
 * Create table 'astrio_specialproducts/set_store'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('astrio_specialproducts/set_store'))
    ->addColumn('set_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Set Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Store Id')
    ->addIndex(
        $installer->getIdxName('astrio_specialproducts/set_store', array('store_id')),
        array('store_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX)
    )
    ->addForeignKey(
        $installer->getFkName(
            'astrio_specialproducts/set_store',
            'set_id',
            'astrio_specialproducts/set',
            'set_id'
        ),
        'set_id',
        $installer->getTable('astrio_specialproducts/set'),
        'set_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName(
            'astrio_specialproducts/set_store',
            'store_id',
            'core/store',
            'store_id'
        ),
        'store_id',
        $installer->getTable('core/store'),
        'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Special Products Set Stores');
$installer->getConnection()->createTable($table);

/**
 * Create table 'astrio_specialproducts/set_product'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('astrio_specialproducts/set_product'))
    ->addColumn('set_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Set Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Store Id')
    ->addColumn('customer_group_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => true,
    ), 'Customer Group Id')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Product Id')
    ->addColumn('position', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'default'   => 0,
    ), 'Position')
    ->addIndex(
        $installer->getIdxName('astrio_specialproducts/set_label', array('set_id')),
        array('store_id', 'set_id', 'customer_group_id', 'product_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    )
    ->addForeignKey(
        $installer->getFkName(
            'astrio_specialproducts/set_product',
            'set_id',
            'astrio_specialproducts/set',
            'set_id'
        ),
        'set_id',
        $installer->getTable('astrio_specialproducts/set'),
        'set_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName(
            'astrio_specialproducts/set_product',
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
    ->addForeignKey(
        $installer->getFkName(
            'astrio_specialproducts/set_product',
            'store_id',
            'astrio_specialproducts/set_store',
            'store_id'
        ),
        'store_id',
        $installer->getTable('astrio_specialproducts/set_store'),
        'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName(
            'astrio_specialproducts/set_product',
            'customer_group_id',
            'customer/customer_group',
            'customer_group_id'
        ),
        'customer_group_id',
        $installer->getTable('customer/customer_group'),
        'customer_group_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Special Products Set Products');
$installer->getConnection()->createTable($table);

/**
 * Create table 'astrio_specialproducts/set_label'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('astrio_specialproducts/set_label'))
    ->addColumn('label_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'identity'  => false,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Label Id')
    ->addColumn('priority', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => 0,
    ), 'Priority')
    ->addColumn('position', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => 0,
    ), 'Position')
    ->addColumn('output_type', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => 0,
    ), 'Output Type')
    ->addColumn('title', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ), 'Title')
    ->addColumn('image', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ), 'Image')
    ->addForeignKey(
        $installer->getFkName(
            'astrio_specialproducts/set_label',
            'label_id',
            'astrio_specialproducts/set',
            'set_id'
        ),
        'label_id',
        $installer->getTable('astrio_specialproducts/set'),
        'set_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Special Products Set Labels');
$installer->getConnection()->createTable($table);

/**
 * Create table 'astrio_specialproducts/set_page'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('astrio_specialproducts/set_page'))
    ->addColumn('page_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'identity'  => false,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Page Id')
    ->addColumn('title', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false,
        'default'   => '',
    ), 'Title')
    ->addColumn('url_key', Varien_Db_Ddl_Table::TYPE_TEXT, 128, array(
        'nullable'  => false,
        'default'   => '',
    ), 'URL Key')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable'  => false,
        'default'   => '',
    ), 'Description')
    ->addColumn('meta_title', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false,
        'default'   => '',
    ), 'Meta Title')
    ->addColumn('meta_keywords', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false,
        'default'   => '',
    ), 'Meta Keywords')
    ->addColumn('meta_description', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable'  => false,
        'default'   => '',
    ), 'Meta Description')
    ->addForeignKey(
        $installer->getFkName(
            'astrio_specialproducts/set_page',
            'page_id',
            'astrio_specialproducts/set',
            'set_id'
        ),
        'page_id',
        $installer->getTable('astrio_specialproducts/set'),
        'set_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Special Products Set Pages');
$installer->getConnection()->createTable($table);

$installer->endSetup();
