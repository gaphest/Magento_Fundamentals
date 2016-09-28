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
abstract class Astrio_Core_Model_System_Config_Source_TemplateAbstract
{

    protected $_optionArray = null;
    
    protected $_toOptionArray = null;

    // template directory path
    const TEMPLATE_DIR_PATH = 'please/override/me';

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        if ($this->_optionArray === null) {

            $path = trim(static::TEMPLATE_DIR_PATH, '/');

            $itemsDir = BP . DS. 'app' . DS . 'design' . DS . 'frontend' . DS . 'base' . DS . 'default' . DS . 'template' . DS . str_replace('/', DS, $path) . DS;
            $templates = glob($itemsDir . '*.[pP][hH][tT][mM][lL]');

            $result = array();

            foreach ($templates as $template) {
                $template = basename($template);
                $result[$path . '/' . $template] = $template;
            }

            $this->_optionArray = $result;
        }

        return $this->_optionArray;
    }

    /**
     * Options getter
     *
     * @param bool $isMultiSelect is multiselect?
     * @return array
     */
    public function toOptionArray($isMultiSelect=false)
    {
        if ($this->_toOptionArray === null) {
            $this->_toOptionArray = array();
            foreach ($this->getOptionArray() as $value => $label) {
                $this->_toOptionArray[] = array(
                    'label' => $label,
                    'value' => $value,
                );
            }
        }

        if ($isMultiSelect) {
            return $this->_toOptionArray;
        }

        $options = $this->_toOptionArray;
        array_unshift($options, array('label' => Mage::helper('astrio_core')->__('-- Please Select a Template --'),'value' => '',));
        return $options;
    }

    /**
     * Get options in "key-value" format
     * @param bool $isMultiSelect is multiselect?
     * @return array
     */
    public function toArray($isMultiSelect=false)
    {
        if ($isMultiSelect) {
            return $this->getOptionArray();
        }

        return array('' => '') + $this->getOptionArray();
    }
}