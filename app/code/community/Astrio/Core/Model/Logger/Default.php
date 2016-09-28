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
 * Default logger. Log is saving to var/log directory
 *
 * @category   Astrio
 * @package    Astrio_Core
 * @author     Demidov Ilya <i.demidov@astrio.com>
 */
class Astrio_Core_Model_Logger_Default extends Astrio_Core_Model_Logger_Abstract
{

    /**
     * Log file name
     *
     * @var string
     */
    protected $_fileName = 'astrio_core.log';

    /**
     * Save message
     *
     * @param string $msg message
     * @param int $level log level
     * @return bool
     */
    protected function _saveMessage($msg, $level)
    {
        Mage::log($msg, $level, $this->getFileName(), true);
        return true;
    }

    /**
     * @param string $fileName file name
     * @return $this
     */
    public function setFileName($fileName)
    {
        $this->_fileName = $fileName;
        return $this;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->_fileName;
    }
}