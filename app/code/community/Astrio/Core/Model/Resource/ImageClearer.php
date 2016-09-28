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
 * @package Astrio_ImageClearer
 * @author Eldaniz Gasymov <e.gasymov@astrio.net>
 */
class Astrio_Core_Model_Resource_ImageClearer extends Mage_Core_Model_Resource_Db_Abstract
{
    // xml config path for gallery table
    const GALLERY_TABLE   = 'catalog/product_attribute_media_gallery';

    protected $_mediaPath = null;

    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init(self::GALLERY_TABLE, 'value_id');
        /**
         * @var $mediaConfig Mage_Catalog_Model_Product_Media_Config
         */
        $mediaConfig = Mage::getSingleton('catalog/product_media_config');
        $this->_mediaPath = $mediaConfig->getBaseMediaPath();
    }

    /**
     * Load gallery
     *
     * @return array
     */
    public function loadGallery()
    {
        $result = array();

        $adapter = $this->_getReadAdapter();

        $select = $adapter->select();
        $select->distinct(true)
            ->from(array('main' => $this->getMainTable()), array('value'));

        $stmt = $adapter->query($select);
        while ($row = $stmt->fetch()) {
            $result[$this->_mediaPath . str_replace('/', DS, $row['value'])] = 1;
        }

        return $result;
    }

    /**
     * Clear unused images
     *
     * @return $this
     */
    public function clearUnusedImages()
    {
        $images = $this->loadGallery();

        $directoryIteratorOne = new DirectoryIterator($this->_mediaPath);

        /**
         * @var $fileInfoOne DirectoryIterator
         * @var $fileInfoTwo DirectoryIterator
         * @var $fileInfoThree DirectoryIterator
         */
        foreach ($directoryIteratorOne as $fileInfoOne) {
            if (!$fileInfoOne->isDir() || $fileInfoOne->isDot() || strlen($fileInfoOne->getFilename()) > 1) {
                continue;
            }

            $directoryIteratorTwo = new DirectoryIterator($fileInfoOne->getRealPath());
            foreach ($directoryIteratorTwo as $fileInfoTwo) {
                if (!$fileInfoTwo->isDir() || $fileInfoTwo->isDot() || strlen($fileInfoTwo->getFilename()) > 1) {
                    continue;
                }

                $directoryIteratorThree = new DirectoryIterator($fileInfoTwo->getRealPath());
                foreach ($directoryIteratorThree as $fileInfoThree) {
                    if (!$fileInfoThree->isFile() || $fileInfoThree->isDot()) {
                        continue;
                    }

                    $realPath = $fileInfoThree->getRealPath();

                    if (!array_key_exists($realPath, $images)) {
                        @unlink($realPath);
                    }
                }
            }
        }

        return $this;
    }

}