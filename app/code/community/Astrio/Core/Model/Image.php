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
 *  Image model
 *
 * @category   Astrio
 * @package    Astrio_Core
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_Core_Model_Image extends Mage_Core_Model_Abstract
{

    /**
     * Get parameters hash
     *
     * @param array $params parameters
     * @return string
     */
    public function getParamsHash(array $params)
    {
        ksort($params);
        return md5(serialize($params));
    }

    /**
     * Resize image from skin directory
     *
     * @param string $resourcePath resource path
     * @param array  $params       parameters
     * @return bool|string
     */
    public function resizeMediaImage($resourcePath, array $params)
    {
        $mediaDir = Mage::getBaseDir('media') . DS;
        $resourceFullPath = $mediaDir .  $resourcePath;
        if (!is_file($resourceFullPath)) {
            return false;
        }

        $baseName   = basename($resourcePath);
        $dirName    = dirname($resourcePath);

        $paramsHash = $this->getParamsHash(array_merge($params, array('_path' => $this->_fixPath($resourcePath))));
        $resizedPath = 'astrio' . DS . 'cache' . DS . $dirName. DS . $paramsHash . DS . $baseName;
        $resizedFullPath = $mediaDir . $resizedPath;

        $resizedExists = is_file($resizedFullPath);
        if (!$resizedExists || filemtime($resourceFullPath) > filemtime($resizedFullPath)) {
            try {
                $imageObj = $this->getImageObjectWithParams($resourceFullPath, $params);
                $width = isset($params['width']) ? $params['width'] : null;
                $height = isset($params['height']) ? $params['height'] : null;
                $imageObj->resize($width, $height);
                $imageObj->save($resizedFullPath);
            } catch (Exception $e) {
                Mage::log('Can\'t create resized image with resource: ' . $resourceFullPath . '. Error message: ' . $e->getMessage(), Zend_Log::NOTICE);
            }
            $resizedExists = is_file($resizedFullPath);
        }

        $path = $resizedExists ? $resizedPath : $resourcePath;
        return Mage::getBaseUrl('media') . str_replace(DS, '/', $path);

    }

    /**
     * Resize image from skin directory
     *
     * @param string $resourcePath resource path
     * @param array  $params       parameters
     * @return bool|string
     */
    public function resizeSkinImage($resourcePath, array $params)
    {
        $mediaDir = Mage::getBaseDir('media') . DS;

        $resourceFullPath = Mage::getDesign()->getFilename($resourcePath, array('_type' => 'skin'));
        $url = Mage::getDesign()->getSkinUrl($resourcePath);

        if (!is_file($resourceFullPath)) {
            return false;
        }

        $baseName   = basename($resourcePath);
        $dirName    = dirname($resourcePath);

        $paramsHash = $this->getParamsHash(array_merge($params, array('_path' => $this->_fixPath($url))));
        $resizedPath = 'astrio' . DS . 'cache' . DS . 'skin' . DS .  $dirName. DS . $paramsHash . DS . $baseName;

        $resizedFullPath = $mediaDir . $resizedPath;
        $resizedExists = is_file($resizedFullPath);
        if (!$resizedExists || filemtime($resourceFullPath) > filemtime($resizedFullPath)) {
            try {
                $imageObj = $this->getImageObjectWithParams($resourceFullPath, $params);
                $width = isset($params['width']) ? $params['width'] : null;
                $height = isset($params['height']) ? $params['height'] : null;
                $imageObj->resize($width, $height);
                $imageObj->save($resizedFullPath);
            } catch (Exception $e) {
                Mage::log('Can\'t create resized image with resource: ' . $resourceFullPath . '. Error message: ' . $e->getMessage(), Zend_Log::NOTICE);
            }
            $resizedExists = is_file($resizedFullPath);
        }
        if ($resizedExists) {
            return Mage::getBaseUrl('media') . str_replace(DS, '/', $resizedPath);
        } else {
            return $url;
        }

    }


    /**
     * Create image object with params
     *
     * @param string $absImagePath absolute image path
     * @param array  $params       parameters
     * @return Varien_Image
     */
    public function getImageObjectWithParams($absImagePath, array $params)
    {

        $imageObj = new Varien_Image($absImagePath);
        $imageObj->constrainOnly(isset($params['constrain_only']) ? (bool)$params['constrain_only'] : true);
        $imageObj->keepAspectRatio(isset($params['keep_ratio']) ? (bool)$params['keep_ratio'] : true);
        $imageObj->keepFrame(isset($params['keep_frame']) ? (bool)$params['keep_frame'] : true);
        if (isset($params['bgrColor'])) {
            $imageObj->backgroundColor(is_array($params['bgrColor']) ? $params['bgrColor'] : $this->hex2rgb($params['bgrColor']));
        }

        $imageObj->keepTransparency(isset($params['keep_transparency']) ? (bool)$params['keep_transparency'] : true);
        $imageObj->quality(isset($params['quality']) ? $params['quality'] : 100);
        return $imageObj;
    }

    /**
     * Fix path
     *
     * @param string $path path
     * @return string
     */
    protected function _fixPath($path)
    {
        return str_replace('\\', '/', $path);
    }

    /**
     * Hex to rgb
     *
     * @param string $hex hex
     * @return array
     */
    public function hex2rgb($hex)
    {
        $hex = str_replace("#", "", $hex);

        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex,0,1).substr($hex,0,1));
            $g = hexdec(substr($hex,1,1).substr($hex,1,1));
            $b = hexdec(substr($hex,2,1).substr($hex,2,1));
        } else {
            $r = hexdec(substr($hex,0,2));
            $g = hexdec(substr($hex,2,2));
            $b = hexdec(substr($hex,4,2));
        }

        $rgb = array($r, $g, $b);
        return $rgb;
    }

}