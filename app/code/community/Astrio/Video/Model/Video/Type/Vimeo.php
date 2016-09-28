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
class Astrio_Video_Model_Video_Type_Vimeo extends Astrio_Video_Model_Video_Type_Abstract
{

    // Thumbnail cache time
    const THUMBNAIL_CACHE_TIME = 86400;

    protected $_hasEmbed = true;

    protected $_hasThumbnail = true;

    /**
     * Get embed video url
     *
     * @param Varien_Object $video video
     * @return bool|string
     */
    protected function _getEmbedVideoUrl(Varien_Object $video)
    {
        $content = trim($video->getContent());
        if ( (strpos($content, 'http://') !== false) || (strpos($content, 'https://') !== false)) {
            // url like https://player.vimeo.com/video/122733089
            if (strpos($content, 'player.vimeo.com') !== false) {
                return $content;
            } else {
                // url like https://vimeo.com/122733089
                if ($videoId = $this->getVideoId($video)) {
                    return sprintf('https://player.vimeo.com/video/%s', $videoId);
                }
            }

            return $content;
        }

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
        if ($videoId = $this->getVideoId($video)) {
            try {
                $imageCachePath = $this->_getMediaCachePath($video);
                $imageCacheUrl = false;

                if ($imageCachePath) {
                    $imageCacheUrl = Mage::getBaseUrl('media') . str_replace(array('\\', '/'), '/', $imageCachePath);
                    $absCachePath = Mage::getBaseDir('media') . DS . $imageCachePath;
                    if (file_exists($absCachePath)) {
                        if ($filetime = filemtime($absCachePath)) {
                            $lifeTime = time() - $filetime;
                            if ($lifeTime < self::THUMBNAIL_CACHE_TIME) {
                                return $imageCacheUrl;
                            }
                        }
                    }
                }

                $hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/$videoId.php"));
                if (is_array($hash) ) {
                    $url = isset($hash[0]['thumbnail_small']) ? $hash[0]['thumbnail_small'] : $hash[0]['thumbnail_small'];
                    if ($imageCachePath) {
                        $bytes = file_put_contents($absCachePath, file_get_contents($url));
                        if ($bytes) {
                            return $imageCacheUrl;
                        } else {
                            unlink($absCachePath);
                        }
                    }

                    return $url;
                }

            } catch(Exception $e){
                Mage::logException($e);
            }
        }

        return false;
    }

    /**
     * Get media cache path
     *
     * @param Varien_Object $video video
     * @return bool|string
     */
    protected function _getMediaCachePath(Varien_Object $video)
    {
        if (!$video->getId()) {
            return false;
        }

        $videoId = $video->getId();
        $dir = ceil($videoId / 500);

        $imageCacheDir = 'astrio' . DS . 'video' . DS . 'cache' . DS . 'vimeo' . DS .  $dir ;
        $imageCacheDirAbs = Mage::getBaseDir('media') . DS . $imageCacheDir ;

        if (!is_dir($imageCacheDirAbs)) {
            mkdir($imageCacheDirAbs, 0777, true);
            if (!is_dir($imageCacheDirAbs) || !is_writable($imageCacheDirAbs)) {
                return false;
            }
        }

        return $imageCacheDir .  DS . $videoId . '.jpg';
    }

    /**
     * Get video id
     *
     * @param Varien_Object $video video
     * @return bool
     */
    public function getVideoId(Varien_Object $video)
    {
        $content = trim($video->getContent());

        // url like https://player.vimeo.com/video/122733089
        if (preg_match('/video\/(\d+)/is', $content, $out)) {
            return $out[1];
        }

        // url like https://vimeo.com/122733089
        if (preg_match('/vimeo\.com\/(\d+)/is', $content, $out)) {
            return $out[1];
        }

        return false;
    }
}
