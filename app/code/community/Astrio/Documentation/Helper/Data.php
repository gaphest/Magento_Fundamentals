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
class Astrio_Documentation_Helper_Data extends Mage_Core_Helper_Abstract
{
    // xml config path for document allowed extensions
    const XML_PATH_DOCUMENT_ALLOWED_EXTENSIONS = 'astrio_documentation/documents/allowed_extensions';

    /**
     * Return document type option values
     *
     * @return array
     */
    public function getDocumentTypeOptions()
    {
        return array (
            Astrio_Documentation_Model_Document::TYPE_FILE    => $this->__('File'),
            Astrio_Documentation_Model_Document::TYPE_URL     => $this->__('Url'),
        );
    }

    /**
     * Get allowed extensions
     *
     * return array of allowed file types
     */
    public function getAllowedExtensions()
    {
        if ($extensions = Mage::getStoreConfig(self::XML_PATH_DOCUMENT_ALLOWED_EXTENSIONS)) {
            $extensions = explode(',', $extensions);
            array_walk($extensions, 'trim');
            $extensions = array_filter($extensions);
            return $extensions;
        }
        return array();
    }

    /**
     * Get uploaded file path
     *
     * @return string
     */
    public function getBaseFilePath()
    {
        return Mage::getBaseDir('media') . DS . str_replace('/', DS, Astrio_Documentation_Model_Document::FILE_PATH) . DS;
    }

    /**
     * Replace slashes with directory separator
     *
     * @param string $file file name
     * @return string
     */
    protected function _prepareFileForPath($file)
    {
        return str_replace('/', DS, $file);
    }

    /**
     * Return full path to lab file
     *
     * @param string $file file name
     * @return string
     */
    public function getFullPath($file)
    {
        $file = $this->_prepareFileForPath($file);
        $path = $this->getBaseFilePath();

        if (substr($file, 0, 1) == DS) {
            return $path . substr($file, 1);
        }

        return $path . $file;
    }

    /**
     * Gets file content type by file path
     *
     * @param string $filePath file path
     * @return string
     */
    public function getContentType($filePath)
    {
        if (function_exists('mime_content_type') && ($contentType = mime_content_type($filePath))) {
            return $contentType;
        } else {
            return Mage::helper('downloadable/file')->getFileType($filePath);
        }
    }

    /**
     * Loads document content to browser
     *
     * @param Astrio_Documentation_Model_Document $document document
     * @param Mage_Core_Controller_Response_Http  $response response
     * @return void
     * @throws Exception
     * @throws Mage_Core_Exception
     */
    public function loadDocument($document, $response)
    {
        $filePath = $this->getFullPath($document->getFilename());

        $contentType = $this->getContentType($filePath);

        $response->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', $contentType, true);

        $handle = new Varien_Io_File();

        if (!$handle->fileExists($filePath, true)) {
            Mage::throwException($this->__('The file does not exist.'));
        }

        $handle->streamOpen($filePath, 'r');

        if ($fileSize = $handle->streamStat($filePath)) {
            $response->setHeader('Content-Length', $fileSize);
        }

        $response->clearBody();
        $response->sendHeaders();

        session_write_close();

        while ($buffer = $handle->streamRead()) {
            print $buffer;
        }

    }

    /**
     * Gets formatted file size
     *
     * @param int $size size
     * @return string
     */
    public function formatFileSize($size)
    {
        $size = $size / (1024 * 1024);
        return number_format($size, 2, ',', ' ');
    }

    /**
     * Get download file url
     *
     * @param Astrio_Documentation_Model_Document $document document
     * @return string
     */
    public function getDownloadUrl($document)
    {
        return $this->_getUrl('astrio_documentation/document/download', array('id' => $document->getDocumentId()));
    }

    /**
     * Get download file url for admin area
     *
     * @param Astrio_Documentation_Model_Document $document document
     * @return string
     */
    public function getAdminDownloadUrl($document)
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/documentation_document/download', array('id' => $document->getDocumentId()));
    }

    /**
     * Get documents list url
     *
     * @return string
     */
    public function getDocumentsUrl()
    {
        return $this->_getUrl('astrio_documentation/document');
    }
}
