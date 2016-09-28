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
 * @package    Astrio_Documentation
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Astrio
 *
 * @category   Astrio
 * @package    Astrio_Documentation
 * @author     Eldaniz Gasymov <e.gasymov@astrio.net>
 */
class Astrio_Documentation_Model_Category extends Mage_Core_Model_Abstract
{

    protected static $_categoryHashArray = null;

    protected static $_categoryOptionArray = null;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'astrio_documentation_category';

    /**
     * Event object name
     *
     * @var string
     */
    protected $_eventObject = 'documentation_category';

    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('astrio_documentation/category');
    }

    /**
     * @return Astrio_Documentation_Model_Resource_Category
     */
    public function getResource()
    {
        return parent::getResource();
    }

    /**
     * Get category hash array
     *
     * @return array|null
     */
    public static function getCategoryHashArray()
    {
        if (self::$_categoryHashArray === null) {
            /**
             * @var $collection Astrio_Documentation_Model_Resource_Category_Collection
             */
            $collection = Mage::getResourceModel('astrio_documentation/category_collection');
            self::$_categoryHashArray = $collection->getHashArray();
        }
        return self::$_categoryHashArray;
    }

    /**
     * Get category option array
     *
     * @param bool $withEmpty with empty?
     * @return array|null
     */
    public static function getCategoryOptionArray($withEmpty = true)
    {
        if (self::$_categoryOptionArray === null) {
            $options = array();
            $optionArray = static::getCategoryHashArray();
            foreach ($optionArray as $value => $label) {
                $options[] = array(
                    'label' => $label,
                    'value' => $value,
                );
            }
            self::$_categoryOptionArray = $options;
        }

        if (!$withEmpty) {
            return self::$_categoryOptionArray;
        }

        $options = self::$_categoryOptionArray;
        array_unshift($options, array(
            'label' => '',
            'value' => '',
        ));

        return $options;
    }
}
