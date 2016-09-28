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
class Astrio_Core_Rewrite_Mage_Eav_Model_Entity_Attribute_Source_Table extends
    Mage_Eav_Model_Entity_Attribute_Source_Table
{

    protected static $_staticOptions          = array();

    protected static $_staticOptionsDefault   = array();

    /**
     * Get options
     *
     * @param string|int $attributeId   attribute id
     * @param string|int $storeId       store id
     * @param bool       $defaultValues default values?
     * @return mixed
     */
    protected function _getOptions($attributeId, $storeId, $defaultValues = false)
    {
        if (!isset(self::$_staticOptions[$attributeId])) {
            /**
             * @var $collection Mage_Eav_Model_Resource_Entity_Attribute_Option_Collection
             */
            $collection = Mage::getResourceModel('eav/entity_attribute_option_collection');
            $collection
                ->setAttributeFilter($attributeId)
                ->setStoreFilter($storeId)
            ;

            $collection->getSelect()
                ->reset(Zend_Db_Select::ORDER)
                ->order('main_table.sort_order ' . Zend_Db_Select::SQL_ASC)
                ->order('value ' . Zend_Db_Select::SQL_ASC)
            ;

            $stmt = $collection->getConnection()->query($collection->getSelect());

            $options = array();
            $optionsDefault = array();
            while ($row = $stmt->fetch()) {
                $options[] = array(
                    'value'     => $row['option_id'],
                    'label'     => $row['value'],
                );
                $optionsDefault[] = array(
                    'value'     => $row['option_id'],
                    'label'     => $row['default_value'],
                );
            }

            self::$_staticOptions[$attributeId][$storeId] = $options;
            self::$_staticOptionsDefault[$attributeId][$storeId] = $optionsDefault;
        }

        return $defaultValues ? self::$_staticOptionsDefault[$attributeId][$storeId] : self::$_staticOptions[$attributeId][$storeId];
    }

    /**
     * Retrieve Full Option values array
     *
     * @param bool $withEmpty     Add empty option to array
     * @param bool $defaultValues default values?
     * @return array
     */
    public function getAllOptions($withEmpty = true, $defaultValues = false)
    {
        if (!$withEmpty) {
            return $this->_getOptions($this->getAttribute()->getId(), $this->getAttribute()->getStoreId(), $defaultValues);
        }

        $options = $this->_getOptions($this->getAttribute()->getId(), $this->getAttribute()->getStoreId(), $defaultValues);
        array_unshift($options, array('label' => '', 'value' => ''));
        return $options;
    }

}