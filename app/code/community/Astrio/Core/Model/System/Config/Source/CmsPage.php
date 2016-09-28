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
class Astrio_Core_Model_System_Config_Source_CmsPage extends Astrio_Core_Model_System_Config_Source_Abstract
{

    protected static $_optionArray = null;
    
    protected static $_toOptionArray = null;

    protected static $_emptyValueLabel = '-- Please Select a Page --';

    /**
     * Retrieve option array
     *
     * @return array
     */
    protected static function _getOptionArray()
    {
        /**
         * @var Mage_Cms_Model_Resource_Page_Collection $collection
         */
        $collection = Mage::getResourceModel('cms/page_collection');

        $connection = $collection->getConnection();
        $select = $collection->getSelect();

        $select
            ->reset(Zend_Db_Select::ORDER)
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array(
                $collection->getResource()->getIdFieldName(),
                'title',
            ))
            ->order('title', Zend_Db_Select::SQL_ASC)
        ;

        return $connection->fetchPairs($select);
    }
}
