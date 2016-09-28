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
class Astrio_SpecialProducts_Model_Set_Page_Url
{

    /**
     * Update url rewrites
     *
     * @param  Astrio_SpecialProducts_Model_Set_Page $page page
     * @return $this
     * @throws Exception
     * @throws Mage_Core_Exception
     */
    public function updateUrlRewrites(Astrio_SpecialProducts_Model_Set_Page $page)
    {
        $storeIds = $page->getStoreIds();

        foreach ($storeIds as $storeId) {
            /**
             * @var $urlRewrite Mage_Core_Model_Url_Rewrite
             */
            $urlRewrite = Mage::getModel('core/url_rewrite');
            $urlRewrite->setStoreId($storeId);
            $url = $urlRewrite->loadByIdPath('specialproducts_set/' . $page->getId());

            $requestPath = trim($page->getUrlKey(), '/');

            if ($requestPath) {
                try {
                    $url->setIdPath('specialproducts_set/' . $page->getId())
                        ->setTargetPath('astrio_specialproducts/set/index/id/' . $page->getId())
                        ->setIsSystem(1)
                        ->setDescription('')
                        ->setRequestPath($requestPath)
                        ->setStoreId($storeId);
                    $url->save();
                } catch (Exception $e) {
                    Mage::throwException(Mage::helper('astrio_specialproducts')->__('Could not create URL Rewrite `%s` for set #%s', $requestPath, $page->getId()));
                }
            } elseif ($url->getId()) {
                $url->delete();
            }
        }

        return $this;
    }

    /**
     * Get page url
     *
     * @param  Astrio_SpecialProducts_Model_Set|Astrio_SpecialProducts_Model_Set_Page|int $pageId page id, special products set model or special products set page model
     * @return bool|string
     */
    public function getPageUrl($pageId)
    {
        if ($pageId instanceof Astrio_SpecialProducts_Model_Set) {
            if (!$pageId->getUseSeparatePage()) {
                return false;
            }

            $pageId = $pageId->getId();
        } elseif ($pageId instanceof Astrio_SpecialProducts_Model_Set_Page) {
            $pageId = $pageId->getId();
        }

        $pageId = (int) $pageId;
        if (!$pageId) {
            return false;
        }

        /**
         * @var $urlRewrite Mage_Core_Model_Url_Rewrite
         */
        $urlRewrite = Mage::getModel('core/url_rewrite');
        $urlRewrite->setStoreId(Mage::app()->getStore()->getId());
        $rewrite = $urlRewrite->loadByIdPath('specialproducts_set/' . $pageId);
        if ($rewrite->getId()) {
            return Mage::getUrl($rewrite->getRequestPath(), array('_direct' => $rewrite->getRequestPath()));
        }

        return Mage::getUrl('astrio_specialproducts/set/index', array('id' => $pageId));
    }
}
