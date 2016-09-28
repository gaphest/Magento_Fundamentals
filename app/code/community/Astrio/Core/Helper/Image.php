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
 *  Helper to work with images
 *
 * @category   Astrio
 * @package    Astrio_Core
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_Core_Helper_Image extends Mage_Core_Helper_Abstract
{

    /**
     * Get image model
     *
     * @return Astrio_Core_Model_Image
     */
    public function getImageModel()
    {
        return Mage::getSingleton('astrio_core/image');
    }

    /**
     * Resize image from media directory
     *
     * @param string $resourcePath resource path
     * @param array $params params
     * @return bool|string
     */
    public function getResizedMediaImage($resourcePath, array $params = array())
    {
        return $this->getImageModel()->resizeMediaImage($resourcePath, $params);
    }

    /**
     * Get resized skin image
     *
     * @param string $path path
     * @param array $params params
     * @return mixed
     */
    public function getResizedSkinImage($path, array $params = array())
    {
        return $this->getImageModel()->resizeSkinImage($path, $params);
    }

    /**
     * Resize image from media/catalog/category directory
     *
     * @param string $fileName file name
     * @param array $params params
     * @return bool|string
     */
    public function getCategoryResizedImage($fileName,  array $params = array())
    {
        $resourcePath = 'catalog'. DS . 'category' . DS . $fileName;
        return $this->getImageModel()->resizeMediaImage($resourcePath, $params);
    }
}
