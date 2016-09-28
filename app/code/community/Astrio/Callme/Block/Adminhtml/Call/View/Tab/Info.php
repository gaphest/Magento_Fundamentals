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
 * @package    Astrio_Callme
 * @copyright  Copyright (c) 2010-2013 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 *  Info Tab for Call Form
 *
 * @category   Astrio
 * @package    Astrio_Callme
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_Callme_Block_Adminhtml_Call_View_Tab_Info
    extends Mage_Adminhtml_Block_Widget
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Retrieve call model instance
     *
     * @return Astrio_Callme_Model_Call
     */
    public function getCall()
    {
        return Mage::registry('astrio_callme_call');
    }


    /**
     * Tab settings
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('astrio_callme')->__('Information');
    }

    /**
     * Gets tab's title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('astrio_callme')->__('Call Information');
    }

    /**
     * Gets if can show a tab
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Gets if hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Gets call's store name
     *
     * @return null|string
     */
    public function getCallStoreName()
    {
        if ($this->getCall()) {
            $storeId = $this->getCall()->getStoreId();
            if (is_null($storeId)) {
                return Mage::helper('adminhtml')->__(' [deleted]');
            }

            $store = Mage::app()->getStore($storeId);
            $name = array(
                $store->getWebsite()->getName(),
                $store->getGroup()->getName(),
                $store->getName()
            );

            return implode('<br/>', $name);
        }

        return null;
    }
}
