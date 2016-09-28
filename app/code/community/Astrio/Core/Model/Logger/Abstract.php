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
 * Abstract logger model
 *
 * @category   Astrio
 * @package    Astrio_Core
 * @author     Demidov Ilya <i.demidov@astrio.com>
 */
abstract class Astrio_Core_Model_Logger_Abstract
{

    // possible log levels
    // Log level: disable
    const LEVEL_DISABLE = 0;
    // Log level: errors
    const LEVEL_ERRORS = 1;
    // Log level: errors, warnings
    const LEVEL_ERRORS_WARNING = 2;
    // Log level: errors, warnings, info
    const LEVEL_ERRORS_WARNING_INFO = 3;
    // Log level: all
    const LEVEL_ALL = 9;

    /**
     * Current log level
     *
     * @var null
     */
    protected $_logLevel = null;


    /**
     * @param null $logLevel log level
     * @return $this
     */
    public function setLogLevel($logLevel)
    {
        $this->_logLevel = $logLevel;
        return $this;
    }

    /**
     * @return null
     */
    public function getLogLevel()
    {
        if ($this->_logLevel === null) {
            return self::LEVEL_ALL;
        }
        return $this->_logLevel;
    }


    /**
     * Add log
     *
     * @param string $msg message
     * @param int $level log level
     * @return bool
     */
    public function addLog($msg, $level = Zend_Log::DEBUG)
    {
        $allowedLevels = 'debug';

        switch ($this->getLogLevel()) {
            case self::LEVEL_DISABLE:
                return true;
                break;
            case self::LEVEL_ERRORS:
                $allowedLevels = array(Zend_Log::ERR);
                break;
            case self::LEVEL_ERRORS_WARNING:
                $allowedLevels = array(Zend_Log::ERR, Zend_Log::WARN);
                break;

            case self::LEVEL_ERRORS_WARNING_INFO:
                $allowedLevels = array(Zend_Log::ERR, Zend_Log::WARN, Zend_Log::INFO);
                break;
        }

        if ($allowedLevels != 'debug' && !in_array($level, $allowedLevels)) {
            return true;
        }

        try {
            $this->_saveMessage($msg, $level);
        } catch(Exception $e){
            Mage::logException($e);
            return false;
        }
        return true;
    }

    /**
     * Log error
     *
     * @param string $msg message
     * @return bool
     */
    public function logError($msg)
    {
        return $this->addLog($msg, Zend_Log::ERR);
    }

    /**
     * Log info
     *
     * @param string $msg message
     * @return bool
     */
    public function logInfo($msg)
    {
        return $this->addLog($msg, Zend_Log::INFO);
    }

    /**
     * Log warning
     *
     * @param string $msg message
     * @return bool
     */
    public function logWarning($msg)
    {
        return $this->addLog($msg, Zend_Log::WARN);
    }

    /**
     * Log debug
     *
     * @param string $msg message
     * @return bool
     */
    public function logDebug($msg)
    {
        return $this->addLog($msg, Zend_Log::DEBUG);
    }

    /**
     * Save message
     *
     * @param string $msg message
     * @param int $level log level
     * @return mixed
     */
    abstract protected function _saveMessage($msg, $level);

    /**
     * Get codes
     *
     * @return array
     */
    public function getCodes()
    {
        return array(
            Zend_Log::ERR => $this->_getHelper()->__('Error'),
            Zend_Log::WARN => $this->_getHelper()->__('Warning'),
            Zend_Log::INFO => $this->_getHelper()->__('Information'),
            Zend_Log::DEBUG => $this->_getHelper()->__('Debug'),
        );
    }

    /**
     * Get helper
     *
     * @return Astrio_Core_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('astrio_core');
    }

}