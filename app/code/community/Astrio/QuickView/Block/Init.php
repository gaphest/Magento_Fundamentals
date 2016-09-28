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
 * Init block
 *
 * @category   Astrio
 * @package    Astrio_QuickView
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_QuickView_Block_Init extends Mage_Core_Block_Template
{

    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();
        if (!$this->getTemplate()) {
            $this->setTemplate('astrio/quickview/init.phtml');
        }
    }

    /**
     * To html
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_getHelper()->isEnabled()) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * Get helper
     *
     * @return Astrio_QuickView_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('astrio_quickview');
    }
}
