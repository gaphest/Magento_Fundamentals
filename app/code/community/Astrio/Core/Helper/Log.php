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
 * Log helper
 *
 * @category   Astrio
 * @package    Astrio_Core
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_Core_Helper_Log extends Mage_Core_Helper_Abstract
{
    /**
     * Get log data array
     *
     * @param Varien_Object $object object
     * @param bool $recursive recursive?
     * @return array
     */
    protected static function _getLogDataRecursive(Varien_Object $object, $recursive)
    {
        $logData = array();
        foreach ($object->getData() as $key => $value) {
            if (is_object($value)) {
                if (($value instanceof Varien_Object) && $recursive) {
                    if ($object !== $value) {
                        $class = get_class($value);
                        $logData[$key] = array('_class_' => $class) + self::_getLogDataRecursive($value, true);
                    } else {
                        $logData[$key] = '_self_';
                    }
                } else {
                    if ($object !== $value) {
                        $class = get_class($value);
                        if ($object instanceof Varien_Object) {
                            $class .= ' ID: ' . $object->getId();
                        }
                        $logData[$key] = $class;
                    } else {
                        $logData[$key] = '_self_';
                    }
                }
            } else {
                $logData[$key] = $value;
            }
        }

        return $logData;
    }

    /**
     * Log varien object
     *
     * @param Varien_Object|string $message message
     * @param null $level log level
     * @param string $file file name
     * @param bool $forceLog force log?
     */
    public static function logVarienObject($message, $level = null, $file = '', $forceLog = false)
    {
        if ($message instanceof Varien_Object) {
            $logData = array('_class_' => get_class($message)) + self::_getLogDataRecursive($message, false);
        } elseif (is_object($message)) {
            $logData = get_class($message);
        } else {
            $logData = $message;
        }

        Mage::log($logData, $level, $file, $forceLog);
    }

    /**
     * Log varien object
     *
     * @param Varien_Object|string $message message
     * @param null $level log level
     * @param string $file file name
     * @param bool $forceLog force log?
     */
    public static function logVarienObjectRecursive($message, $level = null, $file = '', $forceLog = false)
    {
        if ($message instanceof Varien_Object) {
            $logData = array('_class_' => get_class($message)) + self::_getLogDataRecursive($message, true);
        } elseif (is_object($message)) {
            $logData = get_class($message);
        } else {
            $logData = $message;
        }

        Mage::log($logData, $level, $file, $forceLog);
    }
}