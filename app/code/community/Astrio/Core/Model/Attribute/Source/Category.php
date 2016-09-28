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
class Astrio_Core_Model_Attribute_Source_Category extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {
        $isMultiSelect = $this->getAttribute()->getFrontend()->getInputType() == 'multiselect';
        return $this->toOptionArray($isMultiSelect);
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        return Astrio_Core_Model_System_Config_Source_Category::getOptionArray();
    }

    /**
     * Options getter
     * @param bool $isMultiSelect is multiselect?
     * @return array
     */
    public function toOptionArray($isMultiSelect=false)
    {
        return Astrio_Core_Model_System_Config_Source_Category::toOptionArray($isMultiSelect);
    }

    /**
     * Get options in "key-value" format
     *
     * @param bool $isMultiSelect is multiselect?
     * @return array
     */
    public function toArray($isMultiSelect=false)
    {
        return Astrio_Core_Model_System_Config_Source_Category::toArray($isMultiSelect);
    }

    /**
     * Get a text for option value
     *
     * @param string|integer $value value
     * @return string
     */
    public function getOptionText($value)
    {
        $options = $this->getOptionArray();

        $isMultiSelect = $this->getAttribute()->getFrontend()->getInputType() == 'multiselect';
        if ($isMultiSelect) {
            $isMultiple = false;
            if (strpos($value, ',')) {
                $isMultiple = true;
                $value = explode(',', $value);
            }

            if ($isMultiple) {
                $values = array();
                foreach ($value as $val) {
                    if (isset($options[$val])) {
                        $values[] = $options[$val];
                    }
                }
                return $values;
            }
        }

        if (isset($options[$value])) {
            return $options[$value];
        }

        return false;
    }

    /**
     * Retrieve flat column definition
     *
     * @return array
     */
    public function getFlatColums()
    {
        $columns = array();
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $isMulti = $this->getAttribute()->getFrontend()->getInputType() == 'multiselect';

        if (Mage::helper('core')->useDbCompatibleMode()) {
            $columns[$attributeCode] = array(
                'type'      => $isMulti ? 'varchar(255)' : 'int',
                'unsigned'  => $isMulti ? false : true,
                'is_null'   => true,
                'default'   => null,
                'extra'     => null
            );
            if (!$isMulti) {
                $columns[$attributeCode . '_value'] = array(
                    'type'      => 'varchar(255)',
                    'unsigned'  => false,
                    'is_null'   => true,
                    'default'   => null,
                    'extra'     => null
                );
            }
        } else {
            $columns[$attributeCode] = array(
                'type'      => $isMulti ? Varien_Db_Ddl_Table::TYPE_TEXT : Varien_Db_Ddl_Table::TYPE_INTEGER,
                'length'    => $isMulti ? '255' : null,
                'unsigned'  => $isMulti ? false : true,
                'nullable'  => true,
                'default'   => null,
                'extra'     => null,
                'comment'   => $attributeCode . ' column'
            );
            if (!$isMulti) {
                $columns[$attributeCode . '_value'] = array(
                    'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                    'length'    => 255,
                    'unsigned'  => false,
                    'nullable'  => true,
                    'default'   => null,
                    'extra'     => null,
                    'comment'   => $attributeCode . ' column'
                );
            }
        }

        return $columns;
    }

    /**
     * Retrieve Indexes(s) for Flat
     *
     * @return array
     */
    public function getFlatIndexes()
    {
        $indexes = array();

        $index = sprintf('IDX_%s', strtoupper($this->getAttribute()->getAttributeCode()));
        $indexes[$index] = array(
            'type'      => 'index',
            'fields'    => array($this->getAttribute()->getAttributeCode())
        );

        $sortable   = $this->getAttribute()->getUsedForSortBy();
        if ($sortable && $this->getAttribute()->getFrontend()->getInputType() != 'multiselect') {
            $index = sprintf('IDX_%s_VALUE', strtoupper($this->getAttribute()->getAttributeCode()));

            $indexes[$index] = array(
                'type'      => 'index',
                'fields'    => array($this->getAttribute()->getAttributeCode() . '_value')
            );
        }

        return $indexes;
    }

    /**
     * Retrieve Select For Flat Attribute update
     *
     * @param int $store store id
     * @return Varien_Db_Select|null
     */
    public function getFlatUpdateSelect($store)
    {
        return Mage::getResourceModel('eav/entity_attribute')
            ->getFlatUpdateSelect($this->getAttribute(), $store);
    }
}