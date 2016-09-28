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
class Astrio_Core_Model_ImageClearer extends Mage_Core_Model_Abstract
{
    /**
     * Retrieve resource model
     *
     * @return Astrio_Core_Model_Resource_ImageClearer
     */
    protected function _getResource()
    {
        return Mage::getResourceSingleton('astrio_core/imageClearer');
    }

    /**
     * Retrieve model resource
     *
     * @return Astrio_Core_Model_Resource_ImageClearer
     */
    public function getResource()
    {
        return $this->_getResource();
    }

    /**
     * Clear unused images
     */
    public function clearUnusedImages()
    {
        $this->getResource()->clearUnusedImages();
    }
}