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
 * @package    Astrio_Video
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Data helper
 *
 * @category   Astrio
 * @package    Astrio_Video
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_Video_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * Get type model
     *
     * @param  string $type type
     * @return bool|Mage_Core_Model_Abstract
     */
    public function getTypeModel($type)
    {
        if (empty($type)) {
            return false;
        }

        return Mage::getSingleton(sprintf('astrio_video/video_type_%s', $type));
    }
}
