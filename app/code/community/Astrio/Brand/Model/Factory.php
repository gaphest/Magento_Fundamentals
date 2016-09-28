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
class Astrio_Brand_Model_Factory extends Mage_Core_Model_Factory
{
    /**
     * Xml path to the brand url rewrite helper class alias
     */
    const XML_PATH_BRAND_URL_REWRITE_HELPER_CLASS = 'global/astrio_brand/brand/url_rewrite/helper';

    /**
     * Path to brand_url model alias
     */
    const XML_PATH_BRAND_URL_MODEL = 'global/astrio_brand/brand/url/model';

    /**
     * Returns product url rewrite helper instance
     *
     * @return Astrio_Brand_Helper_Brand_Url_Rewrite_Interface
     */
    public function getBrandUrlRewriteHelper()
    {
        return $this->getHelper(
            (string)$this->_config->getNode(self::XML_PATH_BRAND_URL_REWRITE_HELPER_CLASS)
        );
    }

    /**
     * Retrieve brand_url instance
     *
     * @return Astrio_Brand_Model_Brand_Url
     */
    public function getBrandUrlInstance()
    {
        return $this->getModel(
            (string)$this->_config->getNode(self::XML_PATH_BRAND_URL_MODEL)
        );
    }
}