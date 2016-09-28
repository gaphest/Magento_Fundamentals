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
 * Youtube type model
 *
 * @category   Astrio
 * @package    Astrio_Video
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_Video_Model_Video_Type_Youtube extends Astrio_Video_Model_Video_Type_Abstract
{

    protected $_hasThumbnail = true;

    protected $_hasEmbed = true;

    /**
     * Get embed video url
     *
     * @param  Varien_Object $video video
     * @return bool|string
     */
    protected function _getEmbedVideoUrl(Varien_Object $video)
    {
        $content = trim($video->getContent());

        // url like https://www.youtube.com/embed/s2_YDtKmsVA
        if ( (strpos($content, 'http://') !== false) || (strpos($content, 'https://') !== false)) {
            if (strpos($content, 'embed') !== false) {
                return $content;
            }
        }


        // url like http://www.youtube.com/watch?v=s2_YDtKmsVA
        if ($videoId = $this->getVideoId($video)) {
            return sprintf('https://www.youtube.com/embed/%s', $videoId);
        }

        return false;
    }

    /**
     * Get video id
     *
     * @param  Varien_Object $video video
     * @return bool|string
     */
    public function getVideoId(Varien_Object $video)
    {
        $content = trim($video->getContent());

        // url like http://www.youtube.com/watch?v=s2_YDtKmsVA
        if (preg_match('/[\?\&]v\=([^\&\"\']+)/is', $content, $out)) {
            return $out[1];
        }

        //// url like https://www.youtube.com/embed/s2_YDtKmsVA
        if (preg_match('/youtube\.com\/embed\/([^\&\"\']+)/is', $content, $out)) {
            return $out[1];
        }

        // content is video id
        if (preg_match('/^[^\/\\\:\"\']+$/is', $content, $out)) {
            return $content;
        }

        return false;
    }

    /**
     * Get thumbnail url
     *
     * @param  Varien_Object $video video
     * @return bool|string
     */
    protected function _getThumbnailUrl(Varien_Object $video)
    {
        $videoId = $this->getVideoId($video);
        if ($videoId) {
            return sprintf('http://img.youtube.com/vi/%s/1.jpg', $videoId);
        }

        return false;
    }
}
