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
class Astrio_Brand_Rewrite_Mage_Catalog_Block_Breadcrumbs extends Mage_Catalog_Block_Breadcrumbs
{
    /**
     * Preparing layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbsBlock->addCrumb('home', array(
                'label' => Mage::helper('catalog')->__('Home'),
                'title' => Mage::helper('catalog')->__('Go to Home Page'),
                'link' => Mage::getBaseUrl()
            ));

            $title = array();
            $path  = Mage::helper('catalog')->getBreadcrumbPath();

            if ($product = Mage::registry('current_product')) {
                $brand = Mage::registry('current_brand');
                if ($brand instanceof Astrio_Brand_Model_Brand) {
                    /**
                     * @var $helper Astrio_Brand_Helper_Data
                     */
                    $helper = Mage::helper('astrio_brand');
                    $breadcrumbsBlock->addCrumb('brands', array(
                        'label' => Mage::helper('astrio_brand')->__('Brands'),
                        'title' => Mage::helper('astrio_brand')->__('Brands'),
                        'link' => $helper->getBrandsListUrl($brand->getStoreId())
                    ));
                    $breadcrumbsBlock->addCrumb('products_brand', array(
                        'label' => $brand->getName(),
                        'title' => $this->escapeHtml($brand->getName()),
                        'link' => $brand->getBrandUrl()
                    ));
                    $title[] = $this->escapeHtml($brand->getName());

                    //remove categories ... only brands + brand + product
                    $endElement = end($path);
                    foreach ($path as $name => $breadcrumb) {
                        if ($endElement != $breadcrumb) {
                            unset($path[$name]);
                        }
                    }
                }
            }

            foreach ($path as $name => $breadcrumb) {
                $breadcrumbsBlock->addCrumb($name, $breadcrumb);
                $title[] = $breadcrumb['label'];
            }

            if ($headBlock = $this->getLayout()->getBlock('head')) {
                $headBlock->setTitle(join($this->getTitleSeparator(), array_reverse($title)));
            }
        }
        return Mage_Core_Block_Abstract::_prepareLayout();
    }
}