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
 * Date helper
 *
 * @category   Astrio
 * @package    Astrio_Core
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_Core_Helper_Date extends Mage_Core_Helper_Abstract
{

    protected $_weekDayNameByDayNum = array();


    /**
     * Get week day name by day num.
     *
     * @param string|int $dayNum number of days
     * @param string $format format
     * @return bool
     */
    public function getWeekDayNameByDayNum($dayNum, $format = Zend_Date::WEEKDAY_NAME)
    {
        $dayNum = (int)$dayNum;

        if ($dayNum < 0 || $dayNum > 6) {
            return false;
        }

        if ($dayNum == 0) {
            $dayNum = 6;
        } else {
            $dayNum--;
        }

        if (isset($this->_weekDayNameByDayNum[$dayNum][$format])) {
            return $this->_weekDayNameByDayNum[$dayNum][$format];
        }

        $date = new Zend_Date(strtotime( 'next Monday +' . $dayNum . ' days' ), null, Mage::app()->getLocale()->getLocale());

        $this->_weekDayNameByDayNum[$dayNum][$format] = $date->get($format);
        return $this->_weekDayNameByDayNum[$dayNum][$format];
    }


}