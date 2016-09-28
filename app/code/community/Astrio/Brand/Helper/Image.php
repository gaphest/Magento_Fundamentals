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
 * @package    Astrio_Brand
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Astrio
 *
 * @category Astrio
 * @package Astrio_Brand
 * @author Eldaniz Gasymov <e.gasymov@astrio.net>
 * @see Mage_Catalog_Helper_Image
 */
class Astrio_Brand_Helper_Image extends Mage_Core_Helper_Abstract
{

    /**
     * Current model
     *
     * @var Astrio_Brand_Model_Brand_Image
     */
    protected $_model;

    /**
     * Scheduled for resize image
     *
     * @var bool
     */
    protected $_scheduleResize = false;

    /**
     * Scheduled for rotate image
     *
     * @var bool
     */
    protected $_scheduleRotate = false;

    /**
     * Angle
     *
     * @var int
     */
    protected $_angle;

    /**
     * Current Brand
     *
     * @var Astrio_Brand_Model_Brand
     */
    protected $_brand;

    /**
     * Image File
     *
     * @var string
     */
    protected $_imageFile;

    /**
     * Image Placeholder
     *
     * @var string
     */
    protected $_placeholder;

    /**
     * Reset all previous data
     *
     * @return Astrio_Brand_Helper_Image
     */
    protected function _reset()
    {
        $this->_model = null;
        $this->_scheduleResize = false;
        $this->_scheduleRotate = false;
        $this->_angle = null;
        $this->_brand = null;
        $this->_imageFile = null;
        return $this;
    }

    /**
     * Initialize Helper to work with Image
     *
     * @param Astrio_Brand_Model_Brand $brand brand
     * @param string $attributeName attribute name
     * @param mixed $imageFile image file
     * @return Astrio_Brand_Helper_Image
     */
    public function init(Astrio_Brand_Model_Brand $brand, $attributeName, $imageFile=null)
    {
        $this->_reset();
        $this->_setModel(Mage::getModel('astrio_brand/brand_image'));
        $this->_getModel()->setDestinationSubdir($attributeName);
        $this->setBrand($brand);

        if ($imageFile) {
            $this->setImageFile($imageFile);
        } else {
            // add for work original size
            $this->_getModel()->setBaseFile($this->getBrand()->getData($this->_getModel()->getDestinationSubdir()));
        }
        return $this;
    }

    /**
     * Schedule resize of the image
     * $width *or* $height can be null - in this case, lacking dimension will be calculated.
     *
     * @see Astrio_Brand_Model_Brand_Image
     * @param int $width width
     * @param int $height height
     * @return Astrio_Brand_Helper_Image
     */
    public function resize($width, $height = null)
    {
        $this->_getModel()->setWidth($width)->setHeight($height);
        $this->_scheduleResize = true;
        return $this;
    }

    /**
     * Set image quality, values in percentage from 0 to 100
     *
     * @param int $quality quality
     * @return Astrio_Brand_Helper_Image
     */
    public function setQuality($quality)
    {
        $this->_getModel()->setQuality($quality);
        return $this;
    }

    /**
     * Guarantee, that image picture width/height will not be distorted.
     * Applicable before calling resize()
     * It is true by default.
     *
     * @see Astrio_Brand_Model_Brand_Image
     * @param bool $flag flag
     * @return Astrio_Brand_Helper_Image
     */
    public function keepAspectRatio($flag)
    {
        $this->_getModel()->setKeepAspectRatio($flag);
        return $this;
    }

    /**
     * Guarantee, that image will have dimensions, set in $width/$height
     * Applicable before calling resize()
     * Not applicable, if keepAspectRatio(false)
     *
     * $position - TODO, not used for now - picture position inside the frame.
     *
     * @see Astrio_Brand_Model_Brand_Image
     * @param bool $flag flag
     * @param array $position position
     * @return Astrio_Brand_Helper_Image
     */
    public function keepFrame($flag, $position = array('center', 'middle'))
    {
        $this->_getModel()->setKeepFrame($flag);
        return $this;
    }

    /**
     * Guarantee, that image will not lose transparency if any.
     * Applicable before calling resize()
     * It is true by default.
     *
     * $alphaOpacity - TODO, not used for now
     *
     * @see Astrio_Brand_Model_Brand_Image
     * @param bool $flag flag
     * @param int $alphaOpacity alpha opacity
     * @return Astrio_Brand_Helper_Image
     */
    public function keepTransparency($flag, $alphaOpacity = null)
    {
        $this->_getModel()->setKeepTransparency($flag);
        return $this;
    }

    /**
     * Guarantee, that image picture will not be bigger, than it was.
     * Applicable before calling resize()
     * It is false by default
     *
     * @param bool $flag flag
     * @return Astrio_Brand_Helper_Image
     */
    public function constrainOnly($flag)
    {
        $this->_getModel()->setConstrainOnly($flag);
        return $this;
    }

    /**
     * Set color to fill image frame with.
     * Applicable before calling resize()
     * The keepTransparency(true) overrides this (if image has transparent color)
     * It is white by default.
     *
     * @see Astrio_Brand_Model_Brand_Image
     * @param array $colorRGB color rgb
     * @return Astrio_Brand_Helper_Image
     */
    public function backgroundColor($colorRGB)
    {
        // assume that 3 params were given instead of array
        if (!is_array($colorRGB)) {
            $colorRGB = func_get_args();
        }
        $this->_getModel()->setBackgroundColor($colorRGB);
        return $this;
    }

    /**
     * Rotate image into specified angle
     *
     * @param int $angle angle
     * @return Astrio_Brand_Helper_Image
     */
    public function rotate($angle)
    {
        $this->setAngle($angle);
        $this->_getModel()->setAngle($angle);
        $this->_scheduleRotate = true;
        return $this;
    }

    /**
     * Set placeholder
     *
     * @param string $fileName file name
     * @return void
     */
    public function placeholder($fileName)
    {
        $this->_placeholder = $fileName;
    }

    /**
     * Get Placeholder
     *
     * @return string
     */
    public function getPlaceholder()
    {
        if (!$this->_placeholder) {
            $attr = $this->_getModel()->getDestinationSubdir();
            $this->_placeholder = 'images/catalog/product/placeholder/'.$attr.'.jpg';
        }
        return $this->_placeholder;
    }

    /**
     * Return Image URL
     *
     * @return string
     */
    public function __toString()
    {
        try {
            $model = $this->_getModel();

            if ($this->getImageFile()) {
                $model->setBaseFile($this->getImageFile());
            } else {
                $model->setBaseFile($this->getBrand()->getData($model->getDestinationSubdir()));
            }

            if ($model->isCached()) {
                return $model->getUrl();
            } else {
                if ($this->_scheduleRotate) {
                    $model->rotate($this->getAngle());
                }

                if ($this->_scheduleResize) {
                    $model->resize();
                }

                $url = $model->saveFile()->getUrl();
            }
        } catch (Exception $e) {
            $url = Mage::getDesign()->getSkinUrl($this->getPlaceholder());
        }
        return $url;
    }

    /**
     * Set current Image model
     *
     * @param Astrio_Brand_Model_Brand_Image $model model
     * @return Astrio_Brand_Helper_Image
     */
    protected function _setModel($model)
    {
        $this->_model = $model;
        return $this;
    }

    /**
     * Get current Image model
     *
     * @return Astrio_Brand_Model_Brand_Image
     */
    protected function _getModel()
    {
        return $this->_model;
    }

    /**
     * Set Rotation Angle
     *
     * @param int $angle angle
     * @return Astrio_Brand_Helper_Image
     */
    public function setAngle($angle)
    {
        $this->_angle = $angle;
        return $this;
    }

    /**
     * Get Rotation Angle
     *
     * @return int
     */
    public function getAngle()
    {
        return $this->_angle;
    }

    /**
     * Set current Brand
     *
     * @param Astrio_Brand_Model_Brand $brand brand
     * @return Astrio_Brand_Helper_Image
     */
    public function setBrand($brand)
    {
        $this->_brand = $brand;
        return $this;
    }

    /**
     * Get current Brand
     *
     * @return Astrio_Brand_Model_Brand
     */
    public function getBrand()
    {
        return $this->_brand;
    }

    /**
     * Set Image file
     *
     * @param string $file file
     * @return Astrio_Brand_Helper_Image
     */
    public function setImageFile($file)
    {
        $this->_imageFile = $file;
        return $this;
    }

    /**
     * Get Image file
     *
     * @return string
     */
    public function getImageFile()
    {
        return $this->_imageFile;
    }

    /**
     * Retrieve size from string
     *
     * @param string $string string
     * @return array|bool
     */
    public function parseSize($string)
    {
        $size = explode('x', strtolower($string));
        if (sizeof($size) == 2) {
            return array(
                'width' => ($size[0] > 0) ? $size[0] : null,
                'heigth' => ($size[1] > 0) ? $size[1] : null,
            );
        }
        return false;
    }

    /**
     * Retrieve original image width
     *
     * @return int|null
     */
    public function getOriginalWidth()
    {
        return $this->_getModel()->getImageProcessor()->getOriginalWidth();
    }

    /**
     * Retrieve original image height
     *
     * @deprecated
     * @return int|null
     */
    public function getOriginalHeigh()
    {
        return $this->getOriginalHeight();
    }

    /**
     * Retrieve original image height
     *
     * @return int|null
     */
    public function getOriginalHeight()
    {
        return $this->_getModel()->getImageProcessor()->getOriginalHeight();
    }

    /**
     * Retrieve Original image size as array
     * 0 - width, 1 - height
     *
     * @return array
     */
    public function getOriginalSizeArray()
    {
        return array(
            $this->getOriginalWidth(),
            $this->getOriginalHeight()
        );
    }

    /**
     * Check - is this file an image
     *
     * @param string $filePath file path
     * @return bool
     * @throws Mage_Core_Exception
     */
    public function validateUploadFile($filePath)
    {
        if (!getimagesize($filePath)) {
            Mage::throwException($this->__('Disallowed file type.'));
        }

        $_processor = new Varien_Image($filePath);
        return $_processor->getMimeType() !== null;
    }
}