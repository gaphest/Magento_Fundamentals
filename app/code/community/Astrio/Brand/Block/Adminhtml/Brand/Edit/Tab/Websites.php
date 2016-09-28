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
 * @package    Astrio_Brand
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Astrio
 *
 * @category Astrio
 * @package Astrio_Brand
 * @author Eldaniz Gasymov <e.gasymov@astrio.net>
 */
class Astrio_Brand_Block_Adminhtml_Brand_Edit_Tab_Websites extends Mage_Adminhtml_Block_Store_Switcher
{

    protected $_storeFromHtml;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('astrio/brand/edit/websites.phtml');
    }

    /**
     * Retrieve edited brand model instance
     *
     * @return Astrio_Brand_Model_Brand
     */
    public function getBrand()
    {
        return Mage::registry('brand');
    }

    /**
     * Get store ID of current brand
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->getBrand()->getStoreId();
    }

    /**
     * Get ID of current brand
     *
     * @return int
     */
    public function getBrandId()
    {
        return $this->getBrand()->getId();
    }

    /**
     * Retrieve array of website IDs of current brand
     *
     * @return array
     */
    public function getWebsites()
    {
        return $this->getBrand()->getWebsiteIds();
    }

    /**
     * Returns whether brand associated with website with $websiteId
     *
     * @param int $websiteId website id
     * @return bool
     */
    public function hasWebsite($websiteId)
    {
        return in_array($websiteId, $this->getBrand()->getWebsiteIds());
    }

    /**
     * Check websites block is readonly
     *
     * @return boolean
     */
    public function isReadonly()
    {
        return $this->getBrand()->getWebsitesReadonly();
    }

    /**
     * Retrieve store name by its ID
     *
     * @param int $storeId store id
     * @return null|string
     */
    public function getStoreName($storeId)
    {
        return Mage::app()->getStore($storeId)->getName();
    }

    /**
     * Get HTML of store chooser
     *
     * @param Mage_Core_Model_Store $storeTo store model
     * @return string
     */
    public function getChooseFromStoreHtml($storeTo)
    {
        if (!$this->_storeFromHtml) {
            $this->_storeFromHtml = '<select name="copy_to_stores[__store_identifier__]" disabled="disabled">';
            $this->_storeFromHtml .= '<option value="0">'.Mage::helper('catalog')->__('Default Values').'</option>';
            foreach ($this->getWebsiteCollection() as $_website) {
                if (!$this->hasWebsite($_website->getId())) {
                    continue;
                }
                $optGroupLabel = $this->escapeHtml($_website->getName());
                $this->_storeFromHtml .= '<optgroup label="' . $optGroupLabel . '"></optgroup>';
                foreach ($this->getGroupCollection($_website) as $_group) {
                    $optGroupName = $this->escapeHtml($_group->getName());
                    $this->_storeFromHtml .= '<optgroup label="&nbsp;&nbsp;&nbsp;&nbsp;' . $optGroupName . '">';
                    foreach ($this->getStoreCollection($_group) as $_store) {
                        $this->_storeFromHtml .= '<option value="' . $_store->getId() . '">&nbsp;&nbsp;&nbsp;&nbsp;';
                        $this->_storeFromHtml .= $this->escapeHtml($_store->getName()) . '</option>';
                    }
                }
                $this->_storeFromHtml .= '</optgroup>';
            }
            $this->_storeFromHtml .= '</select>';
        }
        return str_replace('__store_identifier__', $storeTo->getId(), $this->_storeFromHtml);
    }
}