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
 * @package    Astrio_News
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Astrio
 *
 * @category   Astrio
 * @package    Astrio_News
 * @author     Eldaniz Gasymov <e.gasymov@astrio.net>
 */
class Astrio_News_Model_Resource_Setup extends Mage_Eav_Model_Entity_Setup
{
    
    /**
     * Prepare catalog attribute values to save
     *
     * @param array $attr attribute
     * @return array
     */
    protected function _prepareValues($attr)
    {
        $data = parent::_prepareValues($attr);
        $data = array_merge($data, array(
            'is_global'          => $this->_getValue($attr, 'global', Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL),
            'is_visible'         => $this->_getValue($attr, 'visible', 1),
            'is_wysiwyg_enabled' => $this->_getValue($attr, 'wysiwyg_enabled', 0),
            'position'           => $this->_getValue($attr, 'position', 0),
            'used_in_listing'    => $this->_getValue($attr, 'used_in_listing', 0),
        ));
        return $data;
    }

    /**
     * Default entites and attributes
     *
     * @return array
     */
    public function getDefaultEntities()
    {
        return array(
            Astrio_News_Model_Category::ENTITY => array(
                'entity_model'                  => 'astrio_news/category',
                'attribute_model'               => 'astrio_news/resource_eav_attribute',
                'table'                         => 'astrio_news/category',
                'additional_attribute_table'    => 'astrio_news/eav_attribute',
                'entity_attribute_collection'   => 'astrio_news/category_attribute_collection',
                'default_group'                 => 'General',
                'attributes'                    => array(
                    'created_at'        => array(
                        'type'                      => 'static',
                        'input'                     => 'text',
                        'backend'                   => 'eav/entity_attribute_backend_time_created',
                        'sort_order'                => 1,
                        'visible'                   => false,
                    ),
                    'updated_at'        => array(
                        'type'                      => 'static',
                        'input'                     => 'text',
                        'backend'                   => 'eav/entity_attribute_backend_time_updated',
                        'sort_order'                => 2,
                        'visible'                   => false,
                    ),
                    'position'          => array(
                        'type'                      => 'int',
                        'label'                     => 'Position',
                        'input'                     => 'text',
                        'required'                  => false,
                        'sort_order'                => 10,
                        'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                        'group'                     => 'General',
                        'default'                   => 0,
                    ),
                    'name'              => array(
                        'type'                      => 'varchar',
                        'label'                     => 'Name',
                        'input'                     => 'text',
                        'required'                  => true,
                        'sort_order'                => 20,
                        'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'group'                     => 'General',
                        'used_in_listing'           => true,
                    ),
                    'is_active'         => array(
                        'type'                      => 'int',
                        'label'                     => 'Is Active',
                        'input'                     => 'select',
                        'source'                    => 'eav/entity_attribute_source_boolean',
                        'required'                  => false,
                        'sort_order'                => 30,
                        'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                        'group'                     => 'General',
                    ),
                    'url_key'           => array(
                        'type'                      => 'varchar',
                        'label'                     => 'URL Key',
                        'input'                     => 'text',
                        'backend'                   => 'astrio_news/category_attribute_backend_urlKey',
                        'table'                     => $this->getTable(array('astrio_news/category', 'url_key')),
                        'required'                  => false,
                        'sort_order'                => 40,
                        'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'group'                     => 'General',
                        'used_in_listing'           => true,
                    ),
                    'description'       => array(
                        'type'                      => 'text',
                        'label'                     => 'Description',
                        'input'                     => 'textarea',
                        'required'                  => false,
                        'sort_order'                => 50,
                        'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'wysiwyg_enabled'           => true,
                        'group'                     => 'General',
                    ),
                    'short_description' => array(
                        'type'                      => 'text',
                        'label'                     => 'Short Description',
                        'input'                     => 'textarea',
                        'required'                  => false,
                        'sort_order'                => 60,
                        'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'wysiwyg_enabled'           => true,
                        'group'                     => 'General',
                        'used_in_listing'           => true,
                    ),
                    'image'             => array(
                        'type'                      => 'varchar',
                        'label'                     => 'Image',
                        'input'                     => 'image',
                        'backend'                   => 'astrio_news/category_attribute_backend_image',
                        'required'                  => false,
                        'sort_order'                => 200,
                        'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'group'                     => 'Images',
                    ),
                    'small_image'       => array(
                        'type'                      => 'varchar',
                        'label'                     => 'Small Image',
                        'backend'                   => 'astrio_news/category_attribute_backend_image',
                        'input'                     => 'image',
                        'required'                  => false,
                        'sort_order'                => 210,
                        'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'group'                     => 'Images',
                        'used_in_listing'           => true,
                    ),
                    'thumbnail'         => array(
                        'type'                      => 'varchar',
                        'label'                     => 'Thumbnail',
                        'input'                     => 'image',
                        'backend'                   => 'astrio_news/category_attribute_backend_image',
                        'required'                  => false,
                        'sort_order'                => 220,
                        'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'group'                     => 'Images',
                        'used_in_listing'           => true,
                    ),
                    'meta_title'        => array(
                        'type'                      => 'varchar',
                        'label'                     => 'Page Title',
                        'input'                     => 'text',
                        'required'                  => false,
                        'sort_order'                => 300,
                        'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'group'                     => 'Meta Information',
                    ),
                    'meta_keywords'     => array(
                        'type'                      => 'text',
                        'label'                     => 'Meta Keywords',
                        'input'                     => 'textarea',
                        'required'                  => false,
                        'sort_order'                => 310,
                        'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'group'                     => 'Meta Information',
                    ),
                    'meta_description'  => array(
                        'type'                      => 'text',
                        'label'                     => 'Meta Description',
                        'input'                     => 'textarea',
                        'required'                  => false,
                        'sort_order'                => 320,
                        'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'group'                     => 'Meta Information',
                    ),
                ),
            ),
            Astrio_News_Model_News::ENTITY => array(
                'entity_model'                  => 'astrio_news/news',
                'attribute_model'               => 'astrio_news/resource_eav_attribute',
                'table'                         => 'astrio_news/news',
                'additional_attribute_table'    => 'astrio_news/eav_attribute',
                'entity_attribute_collection'   => 'astrio_news/news_attribute_collection',
                'default_group'                 => 'General',
                'attributes'                    => array(
                    'created_at'        => array(
                        'type'                      => 'static',
                        'input'                     => 'text',
                        'backend'                   => 'eav/entity_attribute_backend_time_created',
                        'sort_order'                => 1,
                        'visible'                   => false,
                    ),
                    'updated_at'        => array(
                        'type'                      => 'static',
                        'input'                     => 'text',
                        'backend'                   => 'eav/entity_attribute_backend_time_updated',
                        'sort_order'                => 2,
                        'visible'                   => false,
                    ),
                    'published_at'      => array(
                        'type'                      => 'datetime',
                        'label'                     => 'Published At',
                        'input'                     => 'datetime',
                        'backend'                   => 'astrio_news/news_attribute_backend_publishedAt',
                        'required'                  => true,
                        'sort_order'                => 10,
                        'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                        'group'                     => 'General',
                        'used_in_listing'           => true,
                        'note'                      => 'in default store view timezone'
                    ),
                    'title'             => array(
                        'type'                      => 'varchar',
                        'label'                     => 'Title',
                        'input'                     => 'text',
                        'required'                  => true,
                        'sort_order'                => 20,
                        'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'group'                     => 'General',
                        'used_in_listing'           => true,
                    ),
                    'is_active'         => array(
                        'type'                      => 'int',
                        'label'                     => 'Is Active',
                        'input'                     => 'select',
                        'source'                    => 'eav/entity_attribute_source_boolean',
                        'required'                  => false,
                        'sort_order'                => 30,
                        'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                        'group'                     => 'General',
                    ),
                    'url_key'           => array(
                        'type'                      => 'varchar',
                        'label'                     => 'URL Key',
                        'input'                     => 'text',
                        'backend'                   => 'astrio_news/news_attribute_backend_urlKey',
                        'table'                     => $this->getTable(array('astrio_news/news', 'url_key')),
                        'required'                  => false,
                        'sort_order'                => 40,
                        'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'group'                     => 'General',
                        'used_in_listing'           => true,
                    ),
                    'description'       => array(
                        'type'                      => 'text',
                        'label'                     => 'Description',
                        'input'                     => 'textarea',
                        'required'                  => true,
                        'sort_order'                => 50,
                        'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'wysiwyg_enabled'           => true,
                        'group'                     => 'General',
                    ),
                    'short_description' => array(
                        'type'                      => 'text',
                        'label'                     => 'Short Description',
                        'input'                     => 'textarea',
                        'required'                  => true,
                        'sort_order'                => 60,
                        'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'wysiwyg_enabled'           => true,
                        'group'                     => 'General',
                        'used_in_listing'           => true,
                    ),
                    'image'             => array(
                        'type'                      => 'varchar',
                        'label'                     => 'Image',
                        'input'                     => 'image',
                        'backend'                   => 'astrio_news/news_attribute_backend_image',
                        'required'                  => false,
                        'sort_order'                => 200,
                        'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'group'                     => 'Images',
                    ),
                    'small_image'       => array(
                        'type'                      => 'varchar',
                        'label'                     => 'Small Image',
                        'backend'                   => 'astrio_news/news_attribute_backend_image',
                        'input'                     => 'image',
                        'required'                  => false,
                        'sort_order'                => 210,
                        'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'group'                     => 'Images',
                        'used_in_listing'           => true,
                    ),
                    'thumbnail'         => array(
                        'type'                      => 'varchar',
                        'label'                     => 'Thumbnail',
                        'input'                     => 'image',
                        'backend'                   => 'astrio_news/news_attribute_backend_image',
                        'required'                  => false,
                        'sort_order'                => 220,
                        'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'group'                     => 'Images',
                        'used_in_listing'           => true,
                    ),
                    'meta_title'        => array(
                        'type'                      => 'varchar',
                        'label'                     => 'Page Title',
                        'input'                     => 'text',
                        'required'                  => false,
                        'sort_order'                => 300,
                        'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'group'                     => 'Meta Information',
                    ),
                    'meta_keywords'     => array(
                        'type'                      => 'text',
                        'label'                     => 'Meta Keywords',
                        'input'                     => 'textarea',
                        'required'                  => false,
                        'sort_order'                => 310,
                        'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'group'                     => 'Meta Information',
                    ),
                    'meta_description'  => array(
                        'type'                      => 'text',
                        'label'                     => 'Meta Description',
                        'input'                     => 'textarea',
                        'required'                  => false,
                        'sort_order'                => 320,
                        'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'group'                     => 'Meta Information',
                    ),
                ),
            ),
        );
    }
}
