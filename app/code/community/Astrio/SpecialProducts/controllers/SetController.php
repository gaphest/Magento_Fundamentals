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
class Astrio_SpecialProducts_SetController extends Mage_Core_Controller_Front_Action
{

    /**
     * Init page
     *
     * @return Astrio_SpecialProducts_Model_Set_Page|bool
     */
    protected function _initPage()
    {
        $pageId = (int) $this->getRequest()->getParam('id', false);
        if (!$pageId) {
            return false;
        }

        /**
         * @var $set Astrio_SpecialProducts_Model_Set
         */
        $set = Mage::getModel('astrio_specialproducts/set')->load($pageId);
        if (!$set->getId()) {
            return false;
        }

        if (!$set->getIsActive()) {
            return false;
        }

        if (!in_array(Mage::app()->getStore()->getId(), $set->getStoreIds())) {
            return false;
        }

        if (!$set->getUseSeparatePage()) {
            return false;
        }

        $page = $set->getPage();
        if (!$page->getId()) {
            return false;
        }

        Mage::register('astrio_specialproducts_set', $set);
        Mage::register('astrio_specialproducts_set_page', $page);

        return $page;
    }

    /**
     * Add bread crumbs
     *
     * @param  Astrio_SpecialProducts_Model_Set_Page $page page
     * @return $this
     */
    protected function _addBreadcrumbs(Astrio_SpecialProducts_Model_Set_Page $page)
    {
        /**
         * @var $breadcrumbs Mage_Page_Block_Html_Breadcrumbs
         */
        if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbs->addCrumb('home', array(
                'label' => Mage::helper('astrio_specialproducts')->__('Home'),
                'title' => Mage::helper('astrio_specialproducts')->__('Home Page'),
                'link' => Mage::getBaseUrl(),
            ));

            $breadcrumbs->addCrumb('brands', array(
                'label' => Mage::helper('astrio_specialproducts')->__($page->getTitle()),
                'title' => Mage::helper('astrio_specialproducts')->__($page->getTitle()),
                'link'  => '',
            ));

        }
        return $this;
    }

    /**
     * Index action
     */
    public function indexAction()
    {
        $page = $this->_initPage();
        if (!$page) {
            $this->norouteAction();
            return;
        }

        $update = $this->getLayout()->getUpdate();
        $update->addHandle('default');

        $this->addActionLayoutHandles();
        $update->addHandle('SPECIAL_PRODUCTS_SET_PAGE_' . $page->getId());
        $this->loadLayoutUpdates();

        $this->generateLayoutXml()->generateLayoutBlocks();

        /**
         * @var $root Mage_Page_Block_Html
         */
        if ($root = $this->getLayout()->getBlock('root')) {
            $root->addBodyClass('special-products-set-page-' . $page->getId());
        }

        $this->_addBreadcrumbs($page);

        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('checkout/session');

        /**
         * @var Mage_Page_Block_Html_Head $head
         */
        if ($head = $this->getLayout()->getBlock('head')) {
            if ($page->getMetaTitle()) {
                $head->setTitle($page->getMetaTitle());
            } elseif ($page->getTitle()) {
                $head->setTitle($page->getTitle());
            }

            if ($page->getMetaDescription()) {
                $head->setDescription($page->getMetaDescription());
            }

            if ($page->getMetaKeywords()) {
                $head->setKeywords($page->getMetaKeywords());
            }
        }

        $this->renderLayout();
    }
}
