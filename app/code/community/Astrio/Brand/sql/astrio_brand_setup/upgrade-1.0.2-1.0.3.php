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
 * @var Mage_Catalog_Model_Resource_Setup $installer
 */
$installer = Mage::getResourceModel('catalog/setup', 'catalog_setup');
$installer->startSetup();

/**
 * @var $eavConfig Mage_Eav_Model_Config
 */
$eavConfig = Mage::getSingleton('eav/config');
$attribute = $eavConfig->getAttribute(Mage_Catalog_Model_Product::ENTITY, Astrio_Brand_Model_Brand::ATTRIBUTE_CODE);

$oldTable = $attribute->getBackendTable();
$newTable = $installer->getTable(array('catalog/product', 'int'));
if ($oldTable != $newTable) {
    $fields = array(
        'entity_type_id',
        'attribute_id',
        'store_id',
        'entity_id',
        'value',
    );
    $connection = $installer->getConnection();
    $select = $connection->select();
    $select->from($oldTable, $fields);
    $select->where('attribute_id = ?', $attribute->getId());
    $sql = $connection->insertFromSelect($select, $newTable, $fields);
    $connection->query($sql);

    $installer->updateAttribute($attribute->getEntityTypeId(), $attribute->getAttributeCode(), 'backend_table', null);

    if ($oldTable == $installer->getTable(array('catalog/product', 'brand'))) {
        $connection->dropTable($oldTable);
    } else {
        $connection->delete($oldTable, $installer->getConnection()->quoteInto('attribute_id = ?', $attribute->getId()));
    }
}

$installer->endSetup();