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
 * @package    Astrio_Callme
 * @copyright  Copyright (c) 2010-2013 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 *  Source for Call Status
 *
 * @category   Astrio
 * @package    Astrio_Callme
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_Callme_Model_Call_Status_Source
{

    /**
     * @var Astrio_Callme_Helper_Data
     */
    protected $_helper = null;

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        return $this->_getHelper()->getCallStatuses();
    }

    /**
     * Options getter
     * @param bool $isMultiSelect is multiselect?
     * @return array
     */
    public function toOptionArray($isMultiSelect=false)
    {
        $options = array();
        foreach ($this->getOptionArray() as $value => $label) {
            $options[] = array(
                'label' => $label,
                'value' => $value,
            );
        }
        return $options;
    }

    /**
     * Get options in "key-value" format
     * @param bool $isMultiSelect is multiselect?
     * @return array
     */
    public function toArray($isMultiSelect=false)
    {
        return $this->getOptionArray();
    }

    /**
     * @return Astrio_Callme_Helper_Data
     */
    protected function _getHelper()
    {
        if (null === $this->_helper) {
            $this->_helper = Mage::helper('astrio_callme');
        }
        return $this->_helper;
    }
}
