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
class Astrio_Core_Model_Observer
{
    /**
     * Adminhtml cache refresh type
     * event: adminhtml_cache_refresh_type
     *
     * @param Varien_Event_Observer $observer observer
     * @return $this
     */
    public function adminhtmlCacheRefreshType(Varien_Event_Observer $observer)
    {
        if ($observer->getEvent()->getData('type') == 'full_page') {
            if ($options = Mage::app()->getConfig()->getNode('global/full_page_cache')) {
                $options = $options->asArray();
                if (isset($options['backend']) && $options['backend'] == 'Mage_Cache_Backend_File') {
                    if (isset($options['backend_options']['cache_dir'])) {
                        $fpcCacheDir = trim($options['backend_options']['cache_dir']);
                        if (strlen($fpcCacheDir)) {
                            $fpcCacheDir = Mage::getBaseDir('var') . DS . $fpcCacheDir;
                            if (is_dir($fpcCacheDir)) {
                                $file = new Varien_Io_File();
                                $file->rmdirRecursive($fpcCacheDir);
                            }
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Add author link
     * event: controller_action_layout_render_before
     *
     * @param Varien_Event_Observer $observer observer
     * @return $this
     */
    public function addAuthorLink(Varien_Event_Observer $observer)
    {
        /**
         * @var $head Mage_Page_Block_Html_Head
         * @var $helper Astrio_Core_Helper_RichSnippet
         */
        if ($head = Mage::app()->getLayout()->getBlock('head')) {
            $helper = Mage::helper('astrio_core/richSnippet');
            if ($accountLink = $helper->getGooglePlusAccountLink()) {
                $head->addLinkRel('author', $accountLink);
            }
        }

        return $this;
    }
}