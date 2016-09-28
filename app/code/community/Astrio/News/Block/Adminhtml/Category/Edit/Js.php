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
 * @package    Astrio_News
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Astrio
 *
 * @category   Astrio
 * @package    Astrio_News
 * @author     Eldaniz Gasymov <e.gasymov@astrio.net>
 */
class Astrio_News_Block_Adminhtml_Category_Edit_Js extends Mage_Adminhtml_Block_Template
{

    /**
     * Get currently edited news
     *
     * @return Astrio_News_Model_News
     */
    public function getNewsCategory()
    {
        return Mage::registry('current_news_category');
    }

    /**
     * Get store object of currently edited news
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        $category = $this->getNewsCategory();
        if ($category) {
            return Mage::app()->getStore($category->getStoreId());
        }

        return Mage::app()->getStore();
    }
}
