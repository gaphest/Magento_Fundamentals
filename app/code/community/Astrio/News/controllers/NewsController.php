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
class Astrio_News_NewsController extends Mage_Core_Controller_Front_Action
{

    /**
     * @return Astrio_News_Helper_Data
     */
    protected function _getNewsHelper()
    {
        return Mage::helper('astrio_news');
    }

    /**
     * Initialize requested category object
     *
     * @return Astrio_News_Model_News
     */
    protected function _initNews()
    {
        /**
         * @var $news Astrio_News_Model_News news
         * @var $category Astrio_News_Model_Category category
         */
        Mage::dispatchEvent('astrio_news_controller_news_init_before', array('controller_action' => $this));
        $newsId = (int) $this->getRequest()->getParam('id', false);
        if (!$newsId) {
            return false;
        }

        $news = Mage::getModel('astrio_news/news')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($newsId);

        if (!$news->canShow()) {
            return false;
        }

        Mage::getSingleton('core/session')->setLastVisitedNewsId($news->getId());
        Mage::register('current_news', $news);
        Mage::register('news', $news);

        try {
            Mage::dispatchEvent(
                'astrio_news_controller_news_init_after',
                array(
                    'news' => $news,
                    'controller_action' => $this
                )
            );
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            return false;
        }

        $categoryId = (int) $this->getRequest()->getParam('category', false);
        if ($categoryId) {
            if (in_array($categoryId, $news->getCategoryIds())) {
                $category = Mage::getModel('astrio_news/category')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($categoryId);

                if ($category->canShow()) {
                    Mage::register('current_news_category', $category);
                }
            }
        }

        return $news;
    }

    /**
     * Add bread crumbs
     *
     * @param Astrio_News_Model_News $news news
     * @return $this
     */
    protected function _addBreadcrumbs($news)
    {
        /**
         * @var $breadcrumbs Mage_Page_Block_Html_Breadcrumbs
         */
        if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbs->addCrumb('home', array(
                'label' => $this->_getNewsHelper()->__('Home'),
                'title' => $this->_getNewsHelper()->__('Home Page'),
                'link' => Mage::getBaseUrl(),
            ));

            $breadcrumbs->addCrumb('news_list', array(
                'label' => $this->_getNewsHelper()->__('News'),
                'title' => $this->_getNewsHelper()->__('All News'),
                'link' => $this->_getNewsHelper()->getListUrl()
            ));

            if ($category = Mage::registry('current_news_category')) {
                $breadcrumbs->addCrumb('category', array(
                    'label' => $category->getName(),
                    'title' => $category->getName(),
                    'link'  => $category->getUrl(),
                ));
            }

            $breadcrumbs->addCrumb('news', array(
                'label' => $news->getTitle(),
                'title' => $news->getTitle(),
            ));
        }

        return $this;
    }

    /**
     * View action
     *
     * Category view action
     */
    public function viewAction()
    {
        if ($news = $this->_initNews()) {

            Mage::getSingleton('core/session')->setLastViewedNewsId($news->getId());

            $update = $this->getLayout()->getUpdate();
            $update->addHandle('default');

            $this->addActionLayoutHandles();
            $update->addHandle('NEWS_' . $news->getId());
            $this->loadLayoutUpdates();

            $this->generateLayoutXml()->generateLayoutBlocks();

            $this->_addBreadcrumbs($news);

            if ($root = $this->getLayout()->getBlock('root')) {
                $root->addBodyClass('news-' . $news->getId());
            }

            /**
             * @var Mage_Page_Block_Html_Head $head
             */
            if ($head = $this->getLayout()->getBlock('head')) {
                if ($news->getMetaTitle()) {
                    $head->setTitle($news->getMetaTitle());
                } elseif ($news->getTitle()) {
                    $head->setTitle($news->getTitle());
                }

                if ($news->getMetaDescription()) {
                    $head->setDescription($news->getMetaDescription());
                }

                if ($news->getMetaKeywords()) {
                    $head->setKeywords($news->getMetaKeywords());
                }

                if ($this->_getNewsHelper()->canUseCanonicalTag()) {
                    $head->addLinkRel('canonical', $news->getUrl());
                }
            }

            $this->renderLayout();
        } elseif (!$this->getResponse()->isRedirect()) {
            $this->_forward('noRoute');
        }
    }
}
