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
 * @package    Astrio_Menu
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Astrio
 *
 * @category Astrio
 * @package Astrio_Menu
 * @author Eldaniz Gasymov <e.gasymov@astrio.net>
 */
/* @var $this Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Create table 'astrio_menu/menu_item'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('astrio_menu/menu_item'))
    ->addColumn('item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Item Id')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 128, array(
        'nullable'  => false,
        'default'   => '',
    ), 'Name')
    ->addColumn('position', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => 0,
    ), 'Name')
    ->addColumn('item_type', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'unsigned'  => true,
        'default'   => 0,
    ), 'Item Type')
    ->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => true,
        'unsigned'  => true,
        'default'   => null,
    ), 'Category ID')
    ->addColumn('cms_page_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => true,
        'unsigned'  => false,
        'default'   => null,
    ), 'CMS Page ID')
    ->addColumn('custom_link', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false,
        'default'   => '',
    ), 'Custom Link')
    ->addColumn('is_secure_url', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable'  => false,
        'default'   => 0,
    ), 'Custom Link')
    ->addColumn('class_link', Varien_Db_Ddl_Table::TYPE_TEXT, 128, array(
        'nullable'  => false,
        'default'   => '',
    ), 'Class Link')
    ->addColumn('extra', Varien_Db_Ddl_Table::TYPE_TEXT, 128, array(
        'nullable'  => false,
        'default'   => '',
    ), 'Extra')
    ->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable'  => false,
        'default'   => 0,
    ), 'Is Active')
    ->addColumn('sections', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable'  => true,
    ), 'Sections')
    ->addColumn('template', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
    ), 'Template')
    ->addForeignKey(
        $installer->getFkName(
            'astrio_menu/menu_item',
            'category_id',
            'catalog/category',
            'entity_id'
        ),
        'category_id',
        $installer->getTable('catalog/category'),
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName(
            'astrio_menu/menu_item',
            'cms_page_id',
            'cms/page',
            'page_id'
        ),
        'cms_page_id',
        $installer->getTable('cms/page'),
        'page_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Menu Item');
$installer->getConnection()->createTable($table);

/**
 * Create table 'astrio_menu/menu_item_store'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('astrio_menu/menu_item_store'))
    ->addColumn('item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Item Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Store Id')
    ->addIndex($installer->getIdxName(
            'astrio_menu/menu_item_store',
            array('store_id')
        ),
        array('store_id'))
    ->addForeignKey($installer->getFkName(
            'astrio_menu/menu_item_store',
            'item_id',
            'astrio_menu/menu_item',
            'item_id'
        ),
        'item_id',
        $installer->getTable('astrio_menu/menu_item'), 'item_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey($installer->getFkName(
            'astrio_menu/menu_item_store',
            'store_id',
            'core/store',
            'store_id'
        ),
        'store_id',
        $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Menu Item Store');
$installer->getConnection()->createTable($table);

$installer->endSetup();