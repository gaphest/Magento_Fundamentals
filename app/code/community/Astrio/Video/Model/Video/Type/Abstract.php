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
 * Video type model
 *
 * @category   Astrio
 * @package    Astrio_Video
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
abstract class Astrio_Video_Model_Video_Type_Abstract
{

    protected $_hasThumbnail = false;

    protected $_hasEmbed = false;

    /**
     * @return boolean
     */
    public function hasThumbnail()
    {
        return $this->_hasThumbnail;
    }

    /**
     * @return boolean
     */
    public function hasEmbed()
    {
        return $this->_hasEmbed;
    }

    /**
     * Get thumbnail url
     *
     * @param  Varien_Object $video video
     * @return bool
     */
    public function getThumbnailUrl(Varien_Object $video)
    {
        if ($this->hasThumbnail()) {
            return $this->_getThumbnailUrl($video);
        }

        return false;
    }

    /**
     * Get embed video url
     *
     * @param Varien_Object $video video
     * @return bool
     */
    public function getEmbedVideoUrl(Varien_Object $video)
    {
        if ($this->hasEmbed()) {
            return $this->_getEmbedVideoUrl($video);
        }
        return false;
    }

    /**
     * Get embed video url
     *
     * @param Varien_Object $video video
     * @return bool
     */
    protected function _getEmbedVideoUrl(Varien_Object $video)
    {
        return false;
    }

    /**
     * Get thumbnail url
     *
     * @param Varien_Object $video video
     * @return bool
     */
    protected function _getThumbnailUrl(Varien_Object $video)
    {
        return false;
    }
}
