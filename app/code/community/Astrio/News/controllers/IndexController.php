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
class Astrio_News_IndexController extends Mage_Core_Controller_Front_Action
{

    /**
     * Get Astrio_News helper
     *
     * @return Astrio_News_Helper_Data
     */
    protected function _getNewsHelper()
    {
        return Mage::helper('astrio_news');
    }

    /**
     * Add bread crumbs
     *
     * @return $this
     */
    protected function _addBreadcrumbs()
    {
        /**
         * @var $breadcrumbs Mage_Page_Block_Html_Breadcrumbs
         */
        if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbs->addCrumb('home', array(
                'label' => $this->_getNewsHelper()->__('Home'),
                'title' => $this->_getNewsHelper()->__('Home Page'),
                'link' => Mage::getBaseUrl()
            ));

            $breadcrumbs->addCrumb('news_list', array(
                'label' => $this->_getNewsHelper()->__('News'),
                'title' => $this->_getNewsHelper()->__('All News'),
            ));
        }

        return $this;
    }

    /**
     * Index action
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_addBreadcrumbs();

        /**
         * @var Mage_Page_Block_Html_Head $head
         */
        if ($head = $this->getLayout()->getBlock('head')) {
            if ($metaTitle = $this->_getNewsHelper()->getListMetaTitle()) {
                $head->setTitle($metaTitle);
            }

            if ($metaKeywords = $this->_getNewsHelper()->getListMetaKeywords()) {
                $head->setKeywords($metaKeywords);
            }

            if ($metaDescription = $this->_getNewsHelper()->getListMetaDescription()) {
                $head->setDescription($metaDescription);
            }
        }

        $this->renderLayout();
    }
}
