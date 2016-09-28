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
 * @package    Astrio_Documentation
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Astrio
 *
 * @category   Astrio
 * @package    Astrio_Documentation
 * @author     Eldaniz Gasymov <e.gasymov@astrio.net>
 */
class Astrio_Documentation_Model_Document extends Mage_Core_Model_Abstract
{
    /**
     * Documents with uploaded file
     */
    const TYPE_FILE = 0;

    /**
     * Documents with url
     */
    const TYPE_URL = 1;

    // file path
    const FILE_PATH = 'astrio/documentation';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'astrio_documentation_document';

    /**
     * Event object name
     *
     * @var string
     */
    protected $_eventObject = 'documentation_document';

    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('astrio_documentation/document');
    }

    /**
     * Get resource
     *
     * @return Astrio_Documentation_Model_Resource_Document
     */
    public function getResource()
    {
        return parent::getResource();
    }

    /**
     * Retrieve array of product id's for brand
     *
     * @return array
     */
    public function getProductIds()
    {
        if (!$this->hasData('products_ids')) {
            $this->setData('products_ids', $this->getResource()->getProductIds($this));
        }
        return $this->_getData('products_ids');
    }

    /**
     * Saving products
     *
     * @return $this
     */
    protected function _afterSave()
    {
        $this->getResource()->saveProducts($this);

        return parent::_afterSave();
    }

    /**
     * remove old image
     *
     * @return $this
     */
    public function afterCommitCallback()
    {
        if ($this->getOrigData('filename') && $this->getOrigData('filename') != $this->getFilename()) {
            @unlink(Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . str_replace('/', DS, Astrio_SpecialProducts_Model_Set_Label::IMAGE_PATH) . DS . $this->getOrigData('filename'));
        }

        return parent::afterCommitCallback();
    }

    /**
     * Checks if current document is file type
     *
     * @return bool
     */
    public function isFile()
    {
        return $this->getType() == self::TYPE_FILE;
    }

    /**
     * Gets file extension
     *
     * @return string
     */
    public function getFileType()
    {
        if (!$this->hasData('file_type')) {
            $pathInfo = pathinfo((string) $this->getFilename());
            $fileType = !empty($pathInfo['extension']) ? strtoupper($pathInfo['extension']) : '';
            $this->setData('file_type', $fileType);
        }
        return $this->_getData('file_type');
    }

    /**
     * Gets formatted file size
     *
     * @return string
     */
    public function getFormattedSize()
    {
        return Mage::helper('astrio_documentation')->formatFileSize($this->getFileSize());
    }

    /**
     * Get download file url
     *
     * @return string
     */
    public function getDownloadUrl()
    {
        return Mage::helper('astrio_documentation')->getDownloadUrl($this);
    }

    /**
     * Get download file name
     *
     * @return string
     */
    public function getDownloadName()
    {
        if (!$this->hasData('download_name')) {
            $pathInfo = pathinfo((string) $this->getFilename());
            $this->setData('download_name', !empty($pathInfo['basename']) ? $pathInfo['basename'] : '');
        }
        return $this->_getData('download_name');
    }
}
