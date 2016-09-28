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
 * @package    Astrio_Menu
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Astrio
 *
 * @category Astrio
 * @package Astrio_Menu
 * @author Eldaniz Gasymov <e.gasymov@astrio.net>
 */
class Astrio_Menu_Model_System_Config_Source_FilterableProductAttribute
{

    protected $_optionArray = null;
    
    protected $_toOptionArray = null;

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        if ($this->_optionArray === null) {
            /**
             * @var $productCollection Mage_Catalog_Model_Resource_Product_Collection
             */
            $productCollection = Mage::getResourceModel('catalog/product_collection');
            $setIds = $productCollection->getSetIds();

            /** @var $collection Mage_Catalog_Model_Resource_Product_Attribute_Collection */
            $collection = Mage::getResourceModel('catalog/product_attribute_collection');
            $collection
                ->setItemObjectClass('catalog/resource_eav_attribute')
                ->setAttributeSetFilter($setIds)
                ->addStoreLabel(Mage::app()->getStore()->getId())
                ->setOrder('position', 'ASC');

            $collection->addIsFilterableFilter();
            $collection->load();

            $result = array();

            foreach ($collection as $attribute) {
                $result[$attribute->getAttributeCode()] = $attribute->getStoreLabel();
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
        array_unshift($options, array('label' => Mage::helper('astrio_menu')->__('-- Please Select an Attribute --'),'value' => '',));
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