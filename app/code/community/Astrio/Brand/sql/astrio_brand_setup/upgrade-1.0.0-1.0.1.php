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

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, Astrio_Brand_Model_Brand::ATTRIBUTE_CODE, array(
    'group'                      => 'General',
    'label'                      => 'Brand',
    'type'                       => 'int',
    'input'                      => 'select',
    'source'                     => 'eav/entity_attribute_source_table',
    'table'                      => null,
    'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'required'                   => 0,
    'user_defined'               => 1,

    'visible'                    => 1,
    'searchable'                 => 1,
    'filterable'                 => 1,
    'comparable'                 => 0,
    'visible_on_front'           => 0,
    'wysiwyg_enabled'            => 0,
    'is_html_allowed_on_front'   => 0,
    'visible_in_advanced_search' => 1,
    'filterable_in_search'       => 1,
    'used_in_product_listing'    => 1,
    'used_for_sort_by'           => 0,
    'apply_to'                   => null,
    'position'                   => 0,
    'is_configurable'            => 0,
    'used_for_promo_rules'       => 1,
));

$installer->updateAttribute(Mage_Catalog_Model_Product::ENTITY, Astrio_Brand_Model_Brand::ATTRIBUTE_CODE, 'is_used_for_price_rules', 1);

$installer->endSetup();