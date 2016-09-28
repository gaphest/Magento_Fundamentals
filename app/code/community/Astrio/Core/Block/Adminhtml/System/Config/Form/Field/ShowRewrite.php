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
 *  Show rewrites button
 *
 * @category   Astrio
 * @package    Astrio_Core
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_Core_Block_Adminhtml_System_Config_Form_Field_ShowRewrite extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Render
     *
     * @param Varien_Data_Form_Element_Abstract $element element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        /** @var  Astrio_Core_Helper_Data $helper */
        $helper = Mage::helper('astrio_core');
        $html = '<button type="button" onclick="setLocation(\''.$this->getUrl('*/astrio/rewrites').'\')" style=""><span><span><span>'.$helper->__("Show").'</span></span></span></button>&nbsp;&nbsp;&nbsp;';
        return $html;
    }
}