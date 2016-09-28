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
class Astrio_Core_Model_System_Config_Backend_Multiline extends Mage_Core_Model_Config_Data
{
    /**
     * Before save
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        $value = $this->getValue();
        if (is_array($value)) {
            foreach ($value as $k => $rowValue) {
                $value[$k] = trim($rowValue);
                if (!$value[$k]) {
                    unset($value[$k]);
                }
            }

            $this->setValue(serialize($value));
        }

        return parent::_beforeSave();
    }

    /**
     * After load
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterLoad()
    {
        if (!is_array($this->getValue())) {
            $value = $this->getValue();
            $this->setValue(empty($value) ? array() : unserialize($value));
        }

        return parent::_afterLoad();
    }
}