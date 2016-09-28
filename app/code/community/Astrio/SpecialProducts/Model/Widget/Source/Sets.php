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
class Astrio_SpecialProducts_Model_Widget_Source_Sets
{

    protected static $_toOptionArray = null;

    /**
     * To option array
     *
     * @return array
     */
    public static function toOptionArray()
    {
        if (self::$_toOptionArray === null) {
            /**
             * @var Astrio_SpecialProducts_Model_Resource_Set_Collection $collection
             */
            $collection = Mage::getResourceModel('astrio_specialproducts/set_collection');
            $select = $collection->getSelect();

            $select
                ->reset(Zend_Db_Select::ORDER)
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns(array(
                    'value' => 'identifier',
                    'label' => 'name',
                ))
                ->order('name', Zend_Db_Select::SQL_ASC);

            self::$_toOptionArray = $collection->getConnection()->fetchAll($select);
        }

        return self::$_toOptionArray;
    }
}
