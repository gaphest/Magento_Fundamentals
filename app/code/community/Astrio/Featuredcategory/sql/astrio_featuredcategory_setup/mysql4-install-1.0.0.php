<?php
/**
 * @category   Astrio
 * @package    Astrio_Featuredcategory
 */

/**
 * @var $installer Mage_Eav_Model_Entity_Setup
 */

$installer = Mage::getModel('eav/entity_setup', 'core_setup');

$installer->startSetup();

$installer->addAttribute('catalog_category', 'is_featured',  array(
    'group'         => 'General Information',
    'type'          => 'varchar',
    'label'         => 'Is featured',
    'input'         => 'select',
    'source'        => 'eav/entity_attribute_source_boolean',
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'       => true,
    'required'      => false,
    'user_defined'  => false,
    'default'       => ''
));

$installer->endSetup();