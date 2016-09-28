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
 * @package    Astrio_QuickView
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Main helper
 *
 * @category   Astrio
 * @package    Astrio_QuickView
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_QuickView_Helper_Data extends Mage_Core_Helper_Abstract
{

    // config path for enabled flag
    const XML_PATH_ENABLED      = 'astrio_quickview/general/enabled';
    // config path for link class
    const XML_PATH_LINK_CLASS   = 'astrio_quickview/general/link_class';

    protected $_linkClass = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_linkClass = trim(str_replace(' ', '-', Mage::getStoreConfig(self::XML_PATH_LINK_CLASS)));
    }

    /**
     * Is module enabled ?
     *
     * @return bool
     */
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLED);
    }

    /**
     * Get CSS class for quick view link
     *
     * @param Mage_Catalog_Model_Product|int $product product or product id
     * @return mixed
     */
    public function getLinkClass($product)
    {
        if ($product === false) {
            return $this->_linkClass;
        }

        $productId = is_object($product) ? $product->getId() : $product;
        return $this->_linkClass . ' ' . $this->_linkClass . '-' . $productId;
    }

    /**
     * Get quick view url
     *
     * @return string
     */
    public function getUrl()
    {
        /** @var Mage_Core_Helper_Url  $coreHelper */
        $coreHelper = Mage::helper('core/url');
        $params = array(
            Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $coreHelper->getEncodedUrl()
        );

        return Mage::getUrl('astrio_quickview/index/view', $params);
    }
}
