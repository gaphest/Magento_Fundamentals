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
class Astrio_News_Model_News_Attribute_Backend_PublishedAt
    extends Mage_Eav_Model_Entity_Attribute_Backend_Datetime
{

    /**
     * Prepare date for save in DB
     *
     * string format used from input fields (all date input fields need apply locale settings)
     * int value can be declared in code (this meen whot we use valid date)
     *
     * @param  string|int $date date
     * @return string
     */
    public function formatDate($date)
    {
        if (empty($date)) {
            return null;
        }

        if (preg_match('/^[0-9]+$/', $date)) {
            // unix timestamp given - simply instantiate date object
            $date = new Zend_Date((int)$date);
        } else if (preg_match('#^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$#', $date)) {
            // international format
            $zendDate = new Zend_Date();
            $date = $zendDate->setIso($date);
        } else {
            // parse this date in current locale, do not apply GMT offset
            $date = Mage::app()->getLocale()->date($date,
                Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
                null, false
            );
        }

        return $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
    }

    /**
     * Get core date model
     *
     * @return Mage_Core_Model_Date
     */
    protected function _getCoreDateModel()
    {
        return Mage::getSingleton('core/date');
    }

    /**
     * Modify date using time zone only in admin panel.
     *
     * @return bool
     */
    protected function _modifyDateUsingTimezone()
    {
        $controllerAction = Mage::app()->getFrontController()->getAction();
        return $controllerAction instanceof Astrio_News_Adminhtml_NewsController;
    }

    /**
     * After load method
     *
     * @param  Varien_Object $object object
     * @return $this
     */
    public function afterLoad($object)
    {
        $return = parent::afterLoad($object);

        if ($this->_modifyDateUsingTimezone()) {
            $attributeName = $this->getAttribute()->getName();

            $date = $object->getData($attributeName);
            $date = $this->_getCoreDateModel()->date(null, $date);
            $object->setData($attributeName, $date);
        }

        return $return;
    }

    /**
     * Formatting date value before save
     *
     * Should set (bool, string) correct type for empty value from html form,
     * necessary for farther process, else date string
     *
     * @param  Varien_Object $object object
     * @throws Mage_Eav_Exception
     * @return $this
     */
    public function beforeSave($object)
    {
        $return = parent::beforeSave($object);

        if ($this->_modifyDateUsingTimezone()) {
            $attributeName = $this->getAttribute()->getName();

            $date = $object->getData($attributeName);
            $date = $this->_getCoreDateModel()->gmtDate(null, $date);
            $object->setData($attributeName, $date);
        }

        return $return;
    }
}
