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
 * @package    Astrio_SpecialProducts
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Astrio
 *
 * @category   Astrio
 * @package    Astrio_SpecialProducts
 * @author     Eldaniz Gasymov <e.gasymov@astrio.net>
 */
class Astrio_SpecialProducts_Model_Set_Label extends Mage_Core_Model_Abstract
{

    // Position: top-left
    const POSITION_TOP_LEFT         = 1;
    // Position: top-right
    const POSITION_TOP_RIGHT        = 2;
    // Position: bottom-right
    const POSITION_BOTTOM_RIGHT     = 3;
    // Position: bottom-left
    const POSITION_BOTTOM_LEFT      = 4;

    // Output type: image
    const OUTPUT_TYPE_IMAGE         = 1;
    // Output type: text
    const OUTPUT_TYPE_TEXT          = 2;

    // Image path
    const IMAGE_PATH = 'astrio/specialproducts/label';
    
    protected static $_positionOptions      = null;
    
    protected static $_outputTypeOptions    = null;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'astrio_specialproducts_set_label';

    /**
     * Event object name
     *
     * @var string
     */
    protected $_eventObject = 'label';

    /**
     * Initialize resources
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('astrio_specialproducts/set_label');
    }

    /**
     * Get resource
     *
     * @return Astrio_SpecialProducts_Model_Resource_Set_Label
     */
    public function getResource()
    {
        return parent::getResource();
    }

    /**
     * Remove old image
     *
     * @return $this
     */
    public function afterCommitCallback()
    {
        if ($this->getOrigData('image') && $this->getOrigData('image') != $this->getImage()) {
            @unlink(Mage::getBaseDir('media') . DS . str_replace('/', DS, Astrio_SpecialProducts_Model_Set_Label::IMAGE_PATH) . DS . $this->getOrigData('image'));
        }

        return parent::afterCommitCallback();
    }

    /**
     * Get position option array
     *
     * @return array|null
     */
    public static function getPositionOptionArray()
    {
        if (self::$_positionOptions === null) {
            /**
             * @var $helper Astrio_SpecialProducts_Helper_Data
             */
            $helper = Mage::helper('astrio_specialproducts');

            $result = array(
                self::POSITION_TOP_LEFT     => $helper->__('Top Left'),
                self::POSITION_TOP_RIGHT    => $helper->__('Top Right'),
                self::POSITION_BOTTOM_RIGHT => $helper->__('Bottom Right'),
                self::POSITION_BOTTOM_LEFT  => $helper->__('Bottom Left'),
            );

            self::$_positionOptions = $result;
        }

        return self::$_positionOptions;
    }

    /**
     * Options getter
     *
     * @param  bool $isMultiSelect is multi select?
     * @return array
     */
    public static function getPositionToOptionArray($isMultiSelect=false)
    {
        $options = array();
        $optionArray = self::getPositionOptionArray();
        foreach ($optionArray as $value => $label) {
            $options[] = array(
                'label' => $label,
                'value' => $value,
            );
        }

        if ($isMultiSelect) {
            return $options;
        }

        array_unshift($options, array('label' => Mage::helper('astrio_specialproducts')->__('-- Please Select a Position --'),'value' => '',));
        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @param  bool $isMultiSelect is multi select
     * @return array
     */
    public static function getPositionToArray($isMultiSelect=false)
    {
        if ($isMultiSelect) {
            return self::getPositionOptionArray();
        }

        return array('' => '') + self::getPositionOptionArray();
    }

    /**
     * Get output type option array
     *
     * @return array|null
     */
    public static function getOutputTypeOptionArray()
    {
        if (self::$_outputTypeOptions === null) {
            /**
             * @var $helper Astrio_SpecialProducts_Helper_Data
             */
            $helper = Mage::helper('astrio_specialproducts');

            $result = array(
                self::OUTPUT_TYPE_IMAGE => $helper->__('Image'),
                self::OUTPUT_TYPE_TEXT  => $helper->__('Text'),
            );

            self::$_outputTypeOptions = $result;
        }

        return self::$_outputTypeOptions;
    }

    /**
     * Get output type to option array
     *
     * @param  bool $isMultiSelect is multi select?
     * @return array
     */
    public static function getOutputTypeToOptionArray($isMultiSelect=false)
    {
        $options = array();
        $optionArray = self::getOutputTypeOptionArray();
        foreach ($optionArray as $value => $label) {
            $options[] = array(
                'label' => $label,
                'value' => $value,
            );
        }

        if ($isMultiSelect) {
            return $options;
        }

        array_unshift($options, array('label' => Mage::helper('astrio_specialproducts')->__('-- Please Select a Output Type --'),'value' => '',));
        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @param bool $isMultiSelect is multi select?
     * @return array
     */
    public static function getOutputTypeToArray($isMultiSelect=false)
    {
        if ($isMultiSelect) {
            return self::getOutputTypeOptionArray();
        }

        return array('' => '') + self::getOutputTypeOptionArray();
    }

    /**
     * Get priority
     *
     * @return int
     */
    public function getPriority()
    {
        return (int) $this->_getData('priority');
    }

    /**
     * Get position
     *
     * @return int
     */
    public function getPosition()
    {
        return (int) $this->_getData('position');
    }

    /**
     * Get output type
     *
     * @return int
     */
    public function getOutputType()
    {
        return (int) $this->_getData('output_type');
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_getData('title');
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->_getData('image');
    }
}
