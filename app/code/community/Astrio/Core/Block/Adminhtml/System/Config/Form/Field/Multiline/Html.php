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
 * @package Astrio
 * @author Eldaniz Gasymov <e.gasymov@astrio.net>
 */
class Astrio_Core_Block_Adminhtml_System_Config_Form_Field_Multiline_Html extends Mage_Adminhtml_Block_Widget_Container
{

    protected $_addMapButtonHtml;

    protected $_removeMapButtonHtml;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('astrio/core/system/config/form/field/multiline/html.phtml');
    }

    /**
     * Set element
     *
     * @param Varien_Data_Form_Element_Abstract $element element
     * @return $this
     */
    public function setElement(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setData('element', $element);
        return $this;
    }

    /**
     * Get element
     *
     * @return Varien_Data_Form_Element_Abstract
     */
    public function getElement()
    {
        return $this->getData('element');
    }

    /**
     * Get value
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->getElement()->getValue();
    }

    /**
     * Get remove map button html
     *
     * @return mixed
     */
    public function getRemoveMapButtonHtml()
    {
        if (!$this->_removeMapButtonHtml) {
            $this->_removeMapButtonHtml = $this->getLayout()->createBlock('adminhtml/widget_button')->setType('button')
                ->setClass('delete')->setLabel($this->__('Remove'))
                ->setOnClick("removeLineFromMultiline(this)")->toHtml();
        }
        return $this->_removeMapButtonHtml;
    }

    /**
     * Get disabled
     *
     * @return mixed
     */
    public function getDisabled()
    {
        return $this->getElement()->getDisabled();
    }

    /**
     * Get add button text
     *
     * @return string
     */
    public function getAddButtonText()
    {
        $text = (string)$this->getElement()->getFieldConfig()->add_button_text;
        return $text ? $text : 'Add Line';
    }

    /**
     * Get add map button html
     *
     * @return mixed
     */
    public function getAddMapButtonHtml()
    {
        if (!$this->_addMapButtonHtml) {
            $this->_addMapButtonHtml = $this->getLayout()->createBlock('adminhtml/widget_button')->setType('button')
                ->setClass('add')->setLabel($this->__($this->getAddButtonText()))
                ->setOnClick("addLineToMultiline('" . $this->getElement()->getId() . "')")->toHtml();
        }

        return $this->_addMapButtonHtml;
    }

    /**
     * To html
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getData('element')) {
            return '';
        }

        return parent::_toHtml();
    }
}