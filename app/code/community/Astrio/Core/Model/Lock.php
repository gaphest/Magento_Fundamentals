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
 * Lock model
 *
 * @category   Astrio
 * @package    Astrio_Core
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_Core_Model_Lock
{

    /**
     * Array of registered file locks
     *
     * @var array
     */
    protected static $_lockFile = array();

    /**
     * Array of registered file lock resources
     *
     * @var array
     */
    protected static $_lockFileResource = array();

    /**
     * Singleton instance
     *
     * @var Astrio_Core_Model_Lock
     */
    protected static $_instance;

    /**
     * Lock files directory
     *
     * @var string
     */
    protected $_directory = 'astrio_core_locks';

    /**
     * Get lock singleton instance
     *
     * @return Astrio_Core_Model_Lock
     */
    public static function getInstance()
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     */
    protected function __construct()
    {
        register_shutdown_function(array($this, 'shutdownReleaseLocks'));
    }


    /**
     * Release all locks on application shutdown
     */
    public function shutdownReleaseLocks()
    {
        foreach (self::$_lockFile as $lockFile) {
            $this->_releaseLockFile($lockFile);
        }
        foreach (self::$_lockFileResource as $lockFileResource) {
            if ($lockFileResource) {
                fclose($lockFileResource);
            }
        }
    }

    /**
     * Set named lock
     *
     * @param string $lockName lock name
     * @param bool $block block?
     * @return bool
     */
    public function setLock($lockName, $block = false)
    {
        return $this->_setLockFile($lockName, $block);
    }

    /**
     * Set named file lock
     *
     * @param string $lockName lock name
     * @param bool $block block?
     * @return bool
     */
    protected function _setLockFile($lockName, $block = false)
    {
        if ($block) {
            $result = flock($this->_getLockFile($lockName), LOCK_EX);
        } else {
            $result = flock($this->_getLockFile($lockName), LOCK_EX | LOCK_NB);
        }
        if ($result) {
            self::$_lockFile[$lockName] = $lockName;
            return true;
        }
        return false;
    }


    /**
     * Release named lock by name
     *
     * @param string $lockName lock name
     * @return bool
     */
    public function releaseLock($lockName)
    {
        return $this->_releaseLockFile($lockName);
    }

    /**
     * Release named file lock by name
     *
     * @param string $lockName lock name
     * @return bool
     */
    protected function _releaseLockFile($lockName)
    {
        if (flock($this->_getLockFile($lockName), LOCK_UN)) {
            unset(self::$_lockFile[$lockName]);
            return true;
        }
        return false;
    }

    /**
     * Check whether the named lock exists
     *
     * @param string $lockName lock name
     * @return bool
     */
    public function isLockExists($lockName)
    {
        return $this->_isLockExistsFile($lockName);
    }

    /**
     * Check whether the named file lock exists
     *
     * @param string $lockName lock name
     * @return bool
     */
    protected function _isLockExistsFile($lockName)
    {
        $result = true;
        $fp = $this->_getLockFile($lockName);
        if (flock($fp, LOCK_EX | LOCK_NB)) {
            flock($fp, LOCK_UN);
            $result = false;
        }
        return $result;
    }


    /**
     * Get lock file resource
     *
     * @param string $lockName lock name
     * @return resource
     */
    protected function _getLockFile($lockName)
    {
        if (!isset(self::$_lockFileResource[$lockName]) || self::$_lockFileResource[$lockName] === null) {

            $dir = Mage::getBaseDir('var') . DS . $this->_directory;

            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
                if (!is_dir($dir)) {
                    Mage::throwException($this->_getHelper()->__('Unable to create directory %s', $dir));
                }
            }

            $file = $dir . DS . $lockName . '.lock';

            if (is_file($file)) {
                self::$_lockFileResource[$lockName] = fopen($file, 'w');
            } else {
                self::$_lockFileResource[$lockName] = fopen($file, 'x');
            }

            fwrite(self::$_lockFileResource[$lockName], date('r'));
        }
        
        return self::$_lockFileResource[$lockName];
    }


    /**
     * @return Astrio_Core_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('astrio_core');
    }
}
