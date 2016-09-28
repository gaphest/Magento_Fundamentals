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
class Astrio_SpecialProducts_Block_SeparatePage extends Mage_Core_Block_Template
{

    /**
     * @return null|Astrio_SpecialProducts_Model_Set
     */
    public function getSet()
    {
        /**
         * @var $set Astrio_SpecialProducts_Model_Set
         */
        $set = Mage::registry('astrio_specialproducts_set');
        if ($set && $set->getId() && $set->getIsActive() && in_array(Mage::app()->getStore()->getId(), $set->getStoreIds())) {
            return $set;
        }

        return null;
    }

    /**
     * Get page
     *
     * @return null|Astrio_SpecialProducts_Model_Set_Page
     */
    public function getPage()
    {
        if ($set = $this->getSet()) {
            $page = $set->getPage();
            if ($page->getId()) {
                return $page;
            }
        }

        return null;
    }

    /**
     * To html
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getPage()) {
            return '';
        }

        return parent::_toHtml();
    }
}
