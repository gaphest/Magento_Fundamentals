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
class Astrio_Core_Model_System_Config_Backend_Mapping extends Mage_Core_Model_Config_Data
{
    /**
     * Prepare data for save
     *
     * @return Mage_Eav_Model_Entity_Attribute_Backend_Abstract
     */
    protected function _beforeSave()
    {
        $data = $this->getValue();

        foreach ($data['db'] as $k => $value) {
            $data['db'][$k] = trim($value);
            if (!$data['db'][$k] || !$data['file'][$k]) {
                unset($data['db'][$k]);
                unset($data['file'][$k]);
                if (isset($data['additional'])) {
                    unset($data['additional'][$k]);
                }
            }
        }

        if (is_array($data)) {
            if (is_array($data['db'])) {
                $data['db']     = array_values($data['db']);
                $data['file']   = array_values($data['file']);
                if (isset($data['additional'])) {
                    $data['additional'] = array_values($data['additional']);
                }
            }
            $data = serialize($data);
            $this->setValue($data);
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
        $data = $this->getValue();

        if ($data) {
            if (is_string($data)) {
                $data = unserialize($data);
            } else {
                $data = (array)$data;
                if (isset($data[0]) && is_string($data[0])) {
                    $data = unserialize($data[0]);
                }

                foreach ($data as $k => $v) {
                    if (is_object($v)) {
                        $data[$k] = array_values($v->asArray());
                    }
                }
            }
            $this->setValue($data);
        }

        return parent::_afterLoad();
    }

}