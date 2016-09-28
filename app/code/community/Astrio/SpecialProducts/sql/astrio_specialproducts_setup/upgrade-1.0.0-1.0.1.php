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
 * FIXED BUG:
 * AFTER DELETE SPECIAL PRODUCTS SET PRODUCTS FROM OTHER SETS DISAPPEARS.
 *
 * @category   Astrio
 * @package    Astrio_SpecialProducts
 * @author     Eldaniz Gasymov <e.gasymov@astrio.net>
 */

/* @var $this Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$installer->getConnection()->dropForeignKey(
    $installer->getTable('astrio_specialproducts/set_product'),
    $installer->getFkName(
        'astrio_specialproducts/set_product',
        'store_id',
        'astrio_specialproducts/set_store',
        'store_id'
    )
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'astrio_specialproducts/set_product',
        'store_id',
        'core/store',
        'store_id'
    ),
    $installer->getTable('astrio_specialproducts/set_product'),
    'store_id',
    $installer->getTable('core/store'),
    'store_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE,
    Varien_Db_Ddl_Table::ACTION_CASCADE
);

$installer->endSetup();
