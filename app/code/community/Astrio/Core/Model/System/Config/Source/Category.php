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
 * @package    Astrio_Core
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Astrio
 *
 * @category Astrio
 * @package Astrio_Core
 * @author Eldaniz Gasymov <e.gasymov@astrio.net>
 */
class Astrio_Core_Model_System_Config_Source_Category extends Astrio_Core_Model_System_Config_Source_Abstract
{

    protected static $_optionArray = null;
    
    protected static $_toOptionArray = null;

    protected static $_emptyValueLabel = '-- Please Select a Category --';

    /**
     * Retrieve option array
     *
     * @return array
     */
    protected static function _getOptionArray()
    {
        $options = array();

        $categories = static::_processCategoriesTree( static::_getCategoriesArray() );
        $nonEscapableNbspChar = html_entity_decode('&#160;', ENT_NOQUOTES, 'UTF-8');
        foreach ($categories as $category) {
            if ($category['level'] > 0) {
                $options[$category['entity_id']] = str_repeat($nonEscapableNbspChar, ($category['level'] - 1) * 4) . $category['name'];
            }
        }

        return $options;
    }

    /**
     * Get categories array
     *
     * @return array
     * @throws Mage_Core_Exception
     */
    protected static function _getCategoriesArray()
    {
        /**
         * @var $collection Mage_Catalog_Model_Resource_Category_Collection
         */
        $collection = Mage::getResourceModel('catalog/category_collection');
        $collection->joinAttribute(
            'name',
            'catalog_category/name',
            'entity_id',
            null,
            'inner',
            Mage_Core_Model_App::ADMIN_STORE_ID
        );

        $connection = $collection->getConnection();
        $select = $collection->getSelect();

        $select
            ->reset(Zend_Db_Select::ORDER)
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array(
                'entity_id' => 'e.entity_id',
                'level'     => 'e.level',
                'parent_id' => 'e.parent_id',
                'name'      => 'at_name.value'
            ))
            ->order('position', Zend_Db_Select::SQL_ASC)
            ->where('level > ?', 0)
        ;

        $categories = array();

        $stmt = $connection->query($select);
        while ($row = $stmt->fetch()) {
            $categories[$row['parent_id']][$row['entity_id']] = array(
                'entity_id' => $row['entity_id'],
                'level'     => $row['level'],
                'name'      => $row['name'],
            );
        }

        return $categories;
    }

    /**
     * Process categories tree
     *
     * @param array $categories categories
     * @param int $parentId parent id
     * @return array
     */
    protected static function _processCategoriesTree($categories, $parentId = 1)
    {
        $result = array();
        foreach ($categories[$parentId] as $categoryId => $data) {
            $result[$categoryId] = $data;
            if (array_key_exists($categoryId, $categories)) {
                $result = $result + static::_processCategoriesTree($categories, $categoryId);
            }
        }

        return $result;
    }
}
