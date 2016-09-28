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
class Astrio_Core_Block_Adminhtml_System_Config_Form_Field_Mapping_SameSelects extends Astrio_Core_Block_Adminhtml_System_Config_Form_Field_Mapping_Abstract
{
    /**
     * Get element html
     *
     * @param Varien_Data_Form_Element_Abstract $element element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        /**
         * @var $htmlBlock Astrio_Core_Block_Adminhtml_System_Config_Form_Field_Mapping_SameSelects_Html
         */
        $htmlBlock = $this->getLayout()->createBlock('astrio_core/adminhtml_system_config_form_field_mapping_sameSelects_html', 'mapping_' . $element->getId());
        $htmlBlock->setElement($element);
        return $htmlBlock->toHtml();
    }
}

