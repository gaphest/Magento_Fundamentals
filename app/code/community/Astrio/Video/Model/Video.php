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
 * Video item
 *
 * @category   Astrio
 * @package    Astrio_Video
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_Video_Model_Video extends Mage_Core_Model_Abstract
{

    // Type: youtube
    const TYPE_YOUTUBE = 'youtube';
    // Type: vimeo
    const TYPE_VIMEO   = 'vimeo';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'astrio_video';

    /**
     * Event object name
     *
     * @var string
     */
    protected $_eventObject = 'video';

    /**
     * @var Astrio_Video_Model_Video_Type_Abstract
     */
    protected $_typeModel = null;

    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('astrio_video/video');
    }

    /**
     * Get thumbnail url
     *
     * @return mixed
     */
    public function getThumbnailUrl()
    {
        if (!$this->hasData('thumbnail_url')) {
            $url = false;
            if ( ($typeModel = $this->getTypeModel()) && $typeModel->hasThumbnail()) {
                $url = $typeModel->getThumbnailUrl($this);
            }

            $this->setData('thumbnail_url', $url);
        }

        return $this->getData('thumbnail_url');
    }

    /**
     * Get type model
     *
     * @return Astrio_Video_Model_Video_Type_Abstract|bool|Mage_Core_Model_Abstract
     */
    public function getTypeModel()
    {
        if ($this->_typeModel === null) {
            $this->_typeModel = false;
            if ($type = $this->getType()) {
                $this->_typeModel = $this->_getHelper()->getTypeModel($type);
            }
        }

        return $this->_typeModel;
    }

    /**
     * Get type
     *
     * @return mixed
     */
    public function getType()
    {
        if ($this->hasData('type')) {
            return $this->getData('type');
        }

        $this->setData('type', $this->getTypeFromContent());
        return $this->getData('type');
    }

    /**
     * Get type from content
     *
     * @param  string|null $content content
     * @return bool|string
     */
    public function getTypeFromContent($content = null)
    {
        if ($content === null) {
            $content = $this->getContent();
        }

        if (strpos($content, 'youtube') !== false) {
            return self::TYPE_YOUTUBE;
        }

        if (strpos($content, 'vimeo') !== false) {
            return self::TYPE_VIMEO;
        }

        return false;
    }

    /**
     * Get embed video url
     *
     * @return mixed
     */
    public function getEmbedVideoUrl()
    {
        if (!$this->hasData('embed_video_url')) {
            if ($typeMode = $this->getTypeModel()) {
                $this->setData('embed_video_url', $typeMode->getEmbedVideoUrl($this));
            }
        }

        return $this->getData('embed_video_url');
    }

    /**
     * Get resource
     *
     * @return Astrio_Video_Model_Resource_Video
     */
    public function getResource()
    {
        return parent::getResource();
    }

    /**
     * @return Astrio_Video_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('astrio_video');
    }
}
