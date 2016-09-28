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
 * @package    Astrio_Core
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Astrio
 *
 * @category Astrio
 * @package Astrio_Core
 * @author Eldaniz Gasymov <e.gasymov@astrio.net>
 */
class Astrio_Core_Rewrite_Enterprise_UrlRewrite_Model_Url_Rewrite_Request
    extends Enterprise_UrlRewrite_Model_Url_Rewrite_Request
{
    /**
     * Set request path alias to request model
     *
     * @return Enterprise_UrlRewrite_Model_Url_Rewrite_Request
     */
    protected function _setRequestPathAlias()
    {
        $return = parent::_setRequestPathAlias();

        Mage::dispatchEvent('enterprise_url_rewrite_set_request_path_alias', array(
            'rewrite' => $this->_rewrite,
            'request' => $this->_request,
        ));

        return $return;
    }
}