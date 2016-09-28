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
class Astrio_SpecialProducts_Block_Widget_SetTabs
    extends Mage_Catalog_Block_Product_List
    implements Mage_Widget_Block_Interface
{

    protected $_tabs = null;

    /**
     * @return int
     */
    public function getCustomerGroupId()
    {
        if (!$this->hasData('customer_group_id')) {
            /**
             * @var $customerSession Mage_Customer_Model_Session
             */
            $customerSession = Mage::getSingleton('customer/session');
            $this->setData('customer_group_id', (int) $customerSession->getCustomerGroupId());
        }

        return $this->_getData('customer_group_id');
    }

    /**
     * Get identifiers
     *
     * @return string
     */
    public function getIdentifiers()
    {
        return $this->_getData('identifiers');
    }

    /**
     * Get if is random
     *
     * @return int
     */
    public function getIsRandom()
    {
        if (!$this->hasData('is_random')) {
            $this->setData('is_random', 0);
        }

        return $this->_getData('is_random');
    }

    /**
     * Get count
     *
     * @return int
     */
    public function getCount()
    {
        if (!$this->hasData('count')) {
            $this->setData('count', 0);
        }

        return $this->_getData('count');
    }

    /**
     * Get active tab
     *
     * @return string
     */
    public function getActiveTab()
    {
        return $this->_getData('active_tab');
    }

    /**
     * Get template for products
     *
     * @return string
     */
    public function getTemplateForProducts()
    {
        return $this->_getData('template_for_products');
    }

    /**
     * array of tab blocks
     *
     * @return array
     */
    public function getTabs()
    {
        if ($this->_tabs === null) {
            $tabs = array();

            $identifiers = explode(',', $this->getIdentifiers());
            $usedIdentifiers = array();
            foreach ($identifiers as $identifier) {
                $identifier = trim($identifier);
                if (!$identifier) {
                    continue;
                }

                if (in_array($identifier, $usedIdentifiers)) {
                    continue;
                }

                $usedIdentifiers[] = $identifier;

                /**
                 * @var $setBlock Astrio_SpecialProducts_Block_Widget_Set
                 */
                $setBlock = $this->getLayout()->createBlock('astrio_specialproducts/widget_set');
                $setBlock
                    ->setIdentifier($identifier)
                    ->setCount($this->getCount())
                    ->setTemplate($this->getTemplateForProducts())
                    ->setIsRandom($this->getIsRandom());

                if ($setBlock->hasProducts()) {
                    $tabs[$identifier] = $setBlock;
                }
            }

            if ($tabs) {
                $activeTabIdentifier = $this->getActiveTab();
                if (!isset($tabs[$activeTabIdentifier])) {
                    foreach ($tabs as $tab) {
                        $activeTabIdentifier = $tab->getIdentifier();
                        break;
                    }
                    $this->setActiveTab($activeTabIdentifier);
                }
                $tabs[$activeTabIdentifier]->setIsActive(1);
            }

            $this->_tabs = $tabs;
        }

        return $this->_tabs;
    }

    /**
     * To html
     *
     * @return string
     */
    protected function _toHtml()
    {

        if (count($this->getTabs()) <= 0) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Get cache key info
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return parent::getCacheKeyInfo() + array(
            'identifiers'           => $this->getIdentifiers(),
            'is_random'             => (int) $this->getIsRandom(),
            'count'                 => (int) $this->getCount(),
            'customer_group_id'     => (int) $this->getCustomerGroupId(),
            'template_for_products' => $this->getTemplateForProducts(),
            'active_tab'            => $this->getActiveTab(),
        );
    }

    /**
     * Get cache life time
     *
     * @return int
     */
    public function getCacheLifetime()
    {
        if (!$this->hasData('cache_lifetime')) {
            $this->setData('cache_lifetime', 3600);
        }

        return $this->getData('cache_lifetime');
    }

    /**
     * Replace form key for add to cart url. Added 30.03.2015
     *
     * @param  string $html html
     * @return string
     */
    protected function _afterToHtml($html)
    {
        $formkey = Mage::getSingleton('core/session')->getFormKey();
        $formkey = "/form_key/".$formkey."/";
        $html = preg_replace("/\/form_key\/[a-zA-Z0-9]+\//", $formkey, $html);
        return parent::_afterToHtml($html);
    }
}
