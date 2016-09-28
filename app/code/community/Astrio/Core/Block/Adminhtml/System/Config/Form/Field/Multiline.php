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
class Astrio_Core_Block_Adminhtml_System_Config_Form_Field_Multiline extends Mage_Adminhtml_Block_System_Config_Form_Field
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
         * @var $htmlBlock Astrio_Core_Block_Adminhtml_System_Config_Form_Field_Mapping_Select_Html
         */
        $htmlBlock = $this->getLayout()->createBlock('astrio_core/adminhtml_system_config_form_field_multiline_html', 'multiline_' . $element->getId());
        $htmlBlock->setElement($element);
        return $htmlBlock->toHtml();
    }

    /**
     * Enter description here...
     *
     * @param Varien_Data_Form_Element_Abstract $element element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $id = $element->getHtmlId();

        // replace [value] with [inherit]
        $namePrefix = preg_replace('#\[value\](\[\])?$#', '', $element->getName());

        $addInheritCheckbox = false;
        if ($element->getCanUseWebsiteValue()) {
            $addInheritCheckbox = true;
            $checkboxLabel = Mage::helper('adminhtml')->__('Use Website');
        } elseif ($element->getCanUseDefaultValue()) {
            $addInheritCheckbox = true;
            $checkboxLabel = Mage::helper('adminhtml')->__('Use Default');
        }

        if ($addInheritCheckbox) {
            $inherit = $element->getInherit() == 1 ? 'checked="checked"' : '';
            if ($inherit) {
                $element->setDisabled(true);
            }
        }

        $html = '<td colspan="2">';
        $html .= $this->_getElementHtml($element);
        $html .= '</td>';

        if ($addInheritCheckbox) {
            // default value
            $html .= '<td class="use-default">';
            //$html.= '<input id="'.$id.'_inherit" name="'.$namePrefix.'[inherit]" type="checkbox" value="1" class="input-checkbox config-inherit" '.$inherit.' onclick="$(\''.$id.'\').disabled = this.checked">';
            $html .= '<input id="' . $id . '_inherit" name="' . $namePrefix . '[inherit]" type="checkbox" value="1" class="checkbox config-inherit" ' . $inherit . ' onclick="toggleValueElements(this, Element.previous(this.parentNode))" /> ';
            $html .= '<label for="' . $id . '_inherit" class="inherit">' . $checkboxLabel . '</label>';
            $html .= '</td>';

            $html .= '<td class="scope-label">';
            if ($element->getScope()) {
                $html .= $element->getScopeLabel();
            }

            $html .= '</td>';
        } else {
            $html .= '<td class="scope-label">';
            if ($element->getScope()) {
                $html .= $element->getScopeLabel();
            }

            $html .= '</td>';

            $html .= '<td></td>';
        }

        return $this->_decorateRowHtml($element, $html);
    }
}