<?php

require_once 'Mage/Adminhtml/controllers/Catalog/ProductController.php';

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
 * @package    Astrio_Video
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Video tab ajax controller
 *
 * @category   Astrio
 * @package    Astrio_Video
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_Video_Adminhtml_Video_Product_EditController extends Mage_Adminhtml_Catalog_ProductController
{

    /**
     * Form action
     */
    public function formAction()
    {
        $product = $this->_initProduct();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('astrio_video/adminhtml_catalog_product_edit_tab_video', 'admin.astrio_videos')
                ->setProductId($product->getId())
                ->toHtml()
        );
    }
}
