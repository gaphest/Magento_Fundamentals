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
class Astrio_Brand_Block_Brand_View extends Mage_Core_Block_Template
{
    /**
     * @return Astrio_Brand_Model_Brand|null
     */
    public function getBrand()
    {
        $brand = Mage::registry('current_brand');
        if ($brand instanceof Astrio_Brand_Model_Brand && $brand->canShow()) {
            return $brand;
        }

        return null;
    }

    /**
     * Gets brand id
     *
     * @return bool|mixed
     */
    public function getBrandId()
    {
        $brand = $this->getBrand();
        return $brand ? $brand->getId() : false;
    }

    /**
     * To HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getBrandId()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return parent::getCacheKeyInfo() + array('brand_id' => (int) $this->getBrandId());
    }
}