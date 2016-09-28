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
class Astrio_News_CategoryController extends Mage_Core_Controller_Front_Action
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
     * Initialize requested category object
     *
     * @return Astrio_News_Model_Category
     */
    protected function _initNewsCategory()
    {
        /**
         * @var $category Astrio_News_Model_Category
         */
        Mage::dispatchEvent('astrio_news_controller_news_category_init_before', array('controller_action' => $this));
        $categoryId = (int) $this->getRequest()->getParam('id', false);
        if (!$categoryId) {
            return false;
        }

        $category = Mage::getModel('astrio_news/category')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($categoryId);

        if (!$category->canShow()) {
            return false;
        }

        Mage::getSingleton('core/session')->setLastVisitedNewsCategoryId($category->getId());
        Mage::register('current_news_category', $category);
        Mage::register('news_category', $category);

        try {
            Mage::dispatchEvent(
                'astrio_news_controller_news_category_init_after',
                array(
                    'category'          => $category,
                    'controller_action' => $this
                )
            );
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            return false;
        }

        return $category;
    }

    /**
     * Add bread crumbs
     *
     * @param Astrio_News_Model_Category $category category
     * @return $this
     */
    protected function _addBreadcrumbs($category)
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
                'link' => $this->_getNewsHelper()->getListUrl()
            ));
            $breadcrumbs->addCrumb('category', array(
                'label' => $category->getName(),
                'title' => $category->getName()
            ));
        }

        return $this;
    }

    /**
     * Category view action
     */
    public function viewAction()
    {
        if ($category = $this->_initNewsCategory()) {

            Mage::getSingleton('core/session')->setLastViewedNewsCategoryId($category->getId());

            $update = $this->getLayout()->getUpdate();
            $update->addHandle('default');

            $this->addActionLayoutHandles();
            $update->addHandle('NEWS_CATEGORY_' . $category->getId());
            $this->loadLayoutUpdates();

            $this->generateLayoutXml()->generateLayoutBlocks();

            $this->_addBreadcrumbs($category);

            if ($root = $this->getLayout()->getBlock('root')) {
                $root->addBodyClass('news-category-' . $category->getId());
            }

            /**
             * @var Mage_Page_Block_Html_Head $head
             */
            if ($head = $this->getLayout()->getBlock('head')) {
                if ($category->getMetaTitle()) {
                    $head->setTitle($category->getMetaTitle());
                } elseif ($category->getName()) {
                    $head->setTitle($category->getName());
                }

                if ($category->getMetaDescription()) {
                    $head->setDescription($category->getMetaDescription());
                }

                if ($category->getMetaKeywords()) {
                    $head->setKeywords($category->getMetaKeywords());
                }

                if ($this->_getNewsHelper()->canUseCanonicalTag()) {
                    $head->addLinkRel('canonical', $category->getUrl());
                }
            }
            $this->renderLayout();
        } elseif (!$this->getResponse()->isRedirect()) {
            $this->_forward('noRoute');
        }
    }
}
