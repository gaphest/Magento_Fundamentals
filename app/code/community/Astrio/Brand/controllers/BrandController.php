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
 * @see Mage_Catalog_CategoryController
 */
class Astrio_Brand_BrandController extends Mage_Core_Controller_Front_Action
{
    /**
     * Initialize requested category object
     *
     * @return Astrio_Brand_Model_Brand
     */
    protected function _initBrand()
    {
        /**
         * @var $brand Astrio_Brand_Model_Brand
         */
        Mage::dispatchEvent('astrio_brand_controller_brand_init_before', array('controller_action' => $this));
        $brandId = (int) $this->getRequest()->getParam('id', false);
        if (!$brandId) {
            return false;
        }

        $brand = Mage::getModel('astrio_brand/brand')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($brandId);

        if (!$brand->canShow()) {
            return false;
        }
        Mage::getSingleton('catalog/session')->setLastVisitedBrandId($brand->getId());
        Mage::register('current_brand', $brand);
        Mage::register('brand', $brand);

        try {
            Mage::dispatchEvent(
                'astrio_brand_controller_brand_init_after',
                array(
                    'brand' => $brand,
                    'controller_action' => $this
                )
            );
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            return false;
        }

        return $brand;
    }

    /**
     * Add bread crumbs
     *
     * @param int|null $brand brand
     * @return $this
     */
    protected function _addBreadcrumbs($brand = null)
    {
        /**
         * @var $breadcrumbs Mage_Page_Block_Html_Breadcrumbs
         */
        if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbs->addCrumb('home', array('label' => Mage::helper('astrio_brand')->__('Home'), 'title' => Mage::helper('astrio_brand')->__('Home Page'), 'link' => Mage::getBaseUrl()));
            $breadcrumbs->addCrumb('brands', array('label' => Mage::helper('astrio_brand')->__('Brands'), 'title' => Mage::helper('astrio_brand')->__('All Brands'), 'link' => $brand === null ? '' : Mage::helper('astrio_brand')->getBrandsListUrl()));
            if ($brand !== null) {
                $breadcrumbs->addCrumb('brand', array('label' => $brand->getName(), 'title' => $brand->getName()));
            }
        }
        return $this;
    }

    /**
     * Category view action
     */
    public function viewAction()
    {
        if ($brand = $this->_initBrand()) {

            Mage::getSingleton('catalog/session')->setLastViewedBrandId($brand->getId());

            $update = $this->getLayout()->getUpdate();
            $update->addHandle('default');

            $this->addActionLayoutHandles();
            $update->addHandle('BRAND_' . $brand->getId());
            $this->loadLayoutUpdates();

            $this->generateLayoutXml()->generateLayoutBlocks();

            $this->_addBreadcrumbs($brand);

            if ($root = $this->getLayout()->getBlock('root')) {
                $root->addBodyClass('brand-' . $brand->getId());
            }

            /**
             * @var Mage_Page_Block_Html_Head $head
             */
            if ($head = $this->getLayout()->getBlock('head')) {
                if ($brand->getMetaTitle()) {
                    $head->setTitle($brand->getMetaTitle());
                } elseif ($brand->getName()) {
                    $head->setTitle($brand->getName());
                }

                if ($brand->getMetaDescription()) {
                    $head->setDescription($brand->getMetaDescription());
                }

                if ($brand->getMetaKeywords()) {
                    $head->setKeywords($brand->getMetaKeywords());
                }

                /**
                 * @var $brandHelper Astrio_Brand_Helper_Brand
                 */
                $brandHelper = Mage::helper('astrio_brand/brand');
                if ($brandHelper->canUseCanonicalTag()) {
                    $head->addLinkRel('canonical', $brand->getBrandUrl());
                }
            }

            $this->_initLayoutMessages('catalog/session');
            $this->_initLayoutMessages('checkout/session');
            $this->renderLayout();
        } elseif (!$this->getResponse()->isRedirect()) {
            $this->_forward('noRoute');
        }
    }

    /**
     * List action
     */
    public function listAction()
    {
        $this->loadLayout();
        $this->_addBreadcrumbs();

        /**
         * @var Mage_Page_Block_Html_Head $head
         */
        if ($head = $this->getLayout()->getBlock('head')) {
            /**
             * @var $helper Astrio_Brand_Helper_Data
             */
            $helper = Mage::helper('astrio_brand');
            if ($metaTitle = $helper->getBrandsListMetaTitle()) {
                $head->setTitle($metaTitle);
            }

            if ($metaKeywords = $helper->getBrandsListMetaKeywords()) {
                $head->setKeywords($metaKeywords);
            }

            if ($metaDescription = $helper->getBrandsListMetaDescription()) {
                $head->setDescription($metaDescription);
            }
        }

        $this->renderLayout();
    }

}