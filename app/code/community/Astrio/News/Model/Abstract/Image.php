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
 * @package    Astrio_News
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Astrio
 *
 * @category   Astrio
 * @package    Astrio_News
 * @author     Eldaniz Gasymov <e.gasymov@astrio.net>
 */
abstract class Astrio_News_Model_Abstract_Image extends Mage_Core_Model_Abstract
{

    protected $_width;

    protected $_height;

    protected $_quality = 100;

    protected $_keepAspectRatio  = true;

    protected $_keepFrame        = true;

    protected $_keepTransparency = true;

    protected $_constrainOnly    = false;

    protected $_backgroundColor  = array(255, 255, 255);

    protected $_baseFile;

    protected $_isBaseFilePlaceholder;

    protected $_newFile;

    protected $_processor;

    protected $_destinationSubdir;

    protected $_angle;

    /**
     * Get entity dir name
     */
    abstract public function getEntityDirName();

    /**
     * Set width
     *
     * @param  int $width width
     * @return Astrio_News_Model_News_Image
     */
    public function setWidth($width)
    {
        $this->_width = $width;
        return $this;
    }

    /**
     * Get width
     *
     * @return mixed
     */
    public function getWidth()
    {
        return $this->_width;
    }

    /**
     * Set Height
     *
     * @param  int $height height
     * @return Astrio_News_Model_News_Image
     */
    public function setHeight($height)
    {
        $this->_height = $height;
        return $this;
    }

    /**
     * Get height
     *
     * @return mixed
     */
    public function getHeight()
    {
        return $this->_height;
    }

    /**
     * Set image quality, values in percentage from 0 to 100
     *
     * @param  int $quality quality
     * @return Astrio_News_Model_News_Image
     */
    public function setQuality($quality)
    {
        $this->_quality = $quality;
        return $this;
    }

    /**
     * Get image quality
     *
     * @return int
     */
    public function getQuality()
    {
        return $this->_quality;
    }

    /**
     * Set keep aspect ration
     *
     * @param  boolean $keep keep?
     * @return Astrio_News_Model_News_Image
     */
    public function setKeepAspectRatio($keep)
    {
        $this->_keepAspectRatio = (bool)$keep;
        return $this;
    }

    /**
     * Set keep frame
     *
     * @param  boolean $keep keep?
     * @return Astrio_News_Model_News_Image
     */
    public function setKeepFrame($keep)
    {
        $this->_keepFrame = (bool)$keep;
        return $this;
    }

    /**
     * @param  boolean $keep keep?
     * @return Astrio_News_Model_News_Image
     */
    public function setKeepTransparency($keep)
    {
        $this->_keepTransparency = (bool)$keep;
        return $this;
    }

    /**
     * Set constrain only
     *
     * @param  boolean $flag flag?
     * @return Astrio_News_Model_News_Image
     */
    public function setConstrainOnly($flag)
    {
        $this->_constrainOnly = (bool)$flag;
        return $this;
    }

    /**
     * Set background color
     *
     * @param  array $rgbArray rgb array
     * @return Astrio_News_Model_News_Image
     */
    public function setBackgroundColor(array $rgbArray)
    {
        $this->_backgroundColor = $rgbArray;
        return $this;
    }

    /**
     * Set size
     *
     * @param  string $size size
     * @return Astrio_News_Model_News_Image
     */
    public function setSize($size)
    {
        // determine width and height from string
        list($width, $height) = explode('x', strtolower($size), 2);
        foreach (array('width', 'height') as $wh) {
            $$wh  = (int)$$wh;
            if (empty($$wh)) {
                $$wh = null;
            }
        }

        // set sizes
        $this->setWidth($width)->setHeight($height);

        return $this;
    }

    /**
     * Check memory
     *
     * @param  string $file file name
     * @return bool
     */
    protected function _checkMemory($file = null)
    {
        return $this->_getMemoryLimit() > ($this->_getMemoryUsage() + $this->_getNeedMemoryForFile($file)) || $this->_getMemoryLimit() == -1;
    }

    /**
     * Get memory limit
     *
     * @return string
     */
    protected function _getMemoryLimit()
    {
        $memoryLimit = trim(strtoupper(ini_get('memory_limit')));

        if (!isSet($memoryLimit[0])) {
            $memoryLimit = "128M";
        }

        if (substr($memoryLimit, -1) == 'K') {
            return substr($memoryLimit, 0, -1) * 1024;
        }

        if (substr($memoryLimit, -1) == 'M') {
            return substr($memoryLimit, 0, -1) * 1024 * 1024;
        }

        if (substr($memoryLimit, -1) == 'G') {
            return substr($memoryLimit, 0, -1) * 1024 * 1024 * 1024;
        }

        return $memoryLimit;
    }

    /**
     * Get memory usage
     *
     * @return int
     */
    protected function _getMemoryUsage()
    {
        if (function_exists('memory_get_usage')) {
            return memory_get_usage();
        }

        return 0;
    }

    /**
     * Get needed memory for file
     *
     * @param  null $file file name
     * @return float|int
     */
    protected function _getNeedMemoryForFile($file = null)
    {
        $file = is_null($file) ? $this->getBaseFile() : $file;
        if (!$file) {
            return 0;
        }

        if (!file_exists($file) || !is_file($file)) {
            return 0;
        }

        $imageInfo = getimagesize($file);

        if (!isset($imageInfo[0]) || !isset($imageInfo[1])) {
            return 0;
        }

        if (!isset($imageInfo['channels'])) {
            // if there is no info about this parameter lets set it for maximum
            $imageInfo['channels'] = 4;
        }

        if (!isset($imageInfo['bits'])) {
            // if there is no info about this parameter lets set it for maximum
            $imageInfo['bits'] = 8;
        }

        return round(($imageInfo[0] * $imageInfo[1] * $imageInfo['bits'] * $imageInfo['channels'] / 8 + Pow(2, 16)) * 1.65);
    }

    /**
     * Convert array of 3 items (decimal r, g, b) to string of their hex values
     *
     * @param  array $rgbArray rgb array
     * @return string
     */
    protected function _rgbToString($rgbArray)
    {
        $result = array();
        foreach ($rgbArray as $value) {
            if (null === $value) {
                $result[] = 'null';
            } else {
                $result[] = sprintf('%02s', dechex($value));
            }
        }

        return implode($result);
    }

    /**
     * Set file names for base file and new file
     *
     * @param  string $file file name
     * @throws Exception
     * @return Astrio_News_Model_News_Image
     */
    public function setBaseFile($file)
    {
        $this->_isBaseFilePlaceholder = false;

        if (($file) && (0 !== strpos($file, '/', 0))) {
            $file = '/' . $file;
        }

        $baseDir = Mage::getBaseDir('media') . DS . 'astrio' . DS . $this->getEntityDirName();

        if ('/no_selection' == $file) {
            $file = null;
        }

        if ($file) {
            if ((!$this->_fileExists($baseDir . $file)) || !$this->_checkMemory($baseDir . $file)) {
                $file = null;
            }
        }

        if (!$file) {
            // check if placeholder defined in config
            $isConfigPlaceholder = Mage::getStoreConfig("catalog/placeholder/{$this->getDestinationSubdir()}_placeholder");
            $configPlaceholder   = '/placeholder/' . $isConfigPlaceholder;
            $baseDirCatalog = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath();
            if ($isConfigPlaceholder && $this->_fileExists($baseDirCatalog . $configPlaceholder)) {
                $file = $configPlaceholder;
                $baseDir = $baseDirCatalog;
            } else {
                // replace file with skin or default skin placeholder
                $skinBaseDir     = Mage::getDesign()->getSkinBaseDir();
                $skinPlaceholder = "/images/catalog/product/placeholder/{$this->getDestinationSubdir()}.jpg";
                $file = $skinPlaceholder;
                if (file_exists($skinBaseDir . $file)) {
                    $baseDir = $skinBaseDir;
                } else {
                    $baseDir = Mage::getDesign()->getSkinBaseDir(array('_theme' => 'default'));
                    if (!file_exists($baseDir . $file)) {
                        $baseDir = Mage::getDesign()->getSkinBaseDir(array('_theme' => 'default', '_package' => 'base'));
                    }
                }
            }
            $this->_isBaseFilePlaceholder = true;
        }

        $baseFile = $baseDir . $file;

        if ((!$file) || (!file_exists($baseFile))) {
            throw new Exception(Mage::helper('catalog')->__('Image file was not found.'));
        }

        $this->_baseFile = $baseFile;

        // build new filename (most important params)
        $path = array(
            Mage::getBaseDir('media') . DS . 'astrio' . DS . $this->getEntityDirName(),
            'cache',
            Mage::app()->getStore()->getId(),
            $path[] = $this->getDestinationSubdir()
        );

        if ((!empty($this->_width)) || (!empty($this->_height))) {
            $path[] = "{$this->_width}x{$this->_height}";
        }

        // add misk params as a hash
        $miscParams = array(
            ($this->_keepAspectRatio ? '' : 'non') . 'proportional',
            ($this->_keepFrame ? '' : 'no')  . 'frame',
            ($this->_keepTransparency ? '' : 'no')  . 'transparency',
            ($this->_constrainOnly ? 'do' : 'not')  . 'constrainonly',
            $this->_rgbToString($this->_backgroundColor),
            'angle' . $this->_angle,
            'quality' . $this->_quality
        );

        $path[] = md5(implode('_', $miscParams));

        // append prepared filename
        $this->_newFile = implode('/', $path) . $file; // the $file contains heading slash

        return $this;
    }

    /**
     * Get base file name
     *
     * @return mixed
     */
    public function getBaseFile()
    {
        return $this->_baseFile;
    }

    /**
     * Get new file name
     *
     * @return mixed
     */
    public function getNewFile()
    {
        return $this->_newFile;
    }

    /**
     * Set image processor
     *
     * @param  Varien_Image $processor processor
     * @return $this
     */
    public function setImageProcessor($processor)
    {
        $this->_processor = $processor;
        return $this;
    }

    /**
     * Get image processor
     *
     * @return Varien_Image
     */
    public function getImageProcessor()
    {
        if ( !$this->_processor ) {
            $this->_processor = new Varien_Image($this->getBaseFile());
        }

        $this->_processor->keepAspectRatio($this->_keepAspectRatio);
        $this->_processor->keepFrame($this->_keepFrame);
        $this->_processor->keepTransparency($this->_keepTransparency);
        $this->_processor->constrainOnly($this->_constrainOnly);
        $this->_processor->backgroundColor($this->_backgroundColor);
        $this->_processor->quality($this->_quality);
        return $this->_processor;
    }

    /**
     * Resize
     *
     * @see    Varien_Image_Adapter_Abstract
     * @return $this
     */
    public function resize()
    {
        if (is_null($this->getWidth()) && is_null($this->getHeight())) {
            return $this;
        }

        $this->getImageProcessor()->resize($this->_width, $this->_height);
        return $this;
    }

    /**
     * Rotate
     *
     * @param  string $angle agnle
     * @return $this
     */
    public function rotate($angle)
    {
        $angle = intval($angle);
        $this->getImageProcessor()->rotate($angle);
        return $this;
    }

    /**
     * Set angle for rotating
     *
     * This func actually affects only the cache filename.
     *
     * @param  int $angle angle
     * @return $this
     */
    public function setAngle($angle)
    {
        $this->_angle = $angle;
        return $this;
    }

    /**
     * Save file
     *
     * @return $this
     */
    public function saveFile()
    {
        $filename = $this->getNewFile();
        $this->getImageProcessor()->save($filename);
        Mage::helper('core/file_storage_database')->saveFile($filename);
        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        $baseDir = Mage::getBaseDir('media');
        $path = str_replace($baseDir . DS, "", $this->_newFile);
        return Mage::getBaseUrl('media') . str_replace(DS, '/', $path);
    }

    /**
     * Push
     */
    public function push()
    {
        $this->getImageProcessor()->display();
    }

    /**
     * Set destination sub directory
     *
     * @param  string $dir directory
     * @return $this
     */
    public function setDestinationSubdir($dir)
    {
        $this->_destinationSubdir = $dir;
        return $this;
    }

    /**
     * Get destination sub directory
     *
     * @return string
     */
    public function getDestinationSubdir()
    {
        return $this->_destinationSubdir;
    }

    /**
     * Get if is cached
     *
     * @return bool
     */
    public function isCached()
    {
        return $this->_fileExists($this->_newFile);
    }

    /**
     * Clear cache
     */
    public function clearCache()
    {
        $directory = Mage::getBaseDir('media') . DS . 'astrio' . DS . $this->getEntityDirName() . DS . 'cache' . DS;
        $io = new Varien_Io_File();
        $io->rmdir($directory, true);

        Mage::helper('core/file_storage_database')->deleteFolder($directory);
    }

    /**
     * First check this file on FS
     * If it does not exist - try to download it from DB
     *
     * @param  string $filename file name
     * @return bool
     */
    protected function _fileExists($filename)
    {
        if (file_exists($filename)) {
            return true;
        } else {
            return Mage::helper('core/file_storage_database')->saveFileToFilesystem($filename);
        }
    }
}
