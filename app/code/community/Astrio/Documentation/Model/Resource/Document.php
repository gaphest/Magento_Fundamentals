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
class Astrio_Documentation_Model_Resource_Document extends Astrio_Core_Model_Resource_Abstract
{

    protected $_productsTable = null;

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('astrio_documentation/document', 'document_id');

        $this->_productsTable = $this->getTable('astrio_documentation/document_product');
    }

    /**
     * Get products ids
     *
     * @param Astrio_Documentation_Model_Document $document document
     * @return array
     */
    public function getProductIds(Astrio_Documentation_Model_Document $document)
    {
        if (!$document->getId()) {
            return array();
        }

        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->_productsTable, array('product_id'))
            ->where('document_id = ?', (int) $document->getId());

        return $adapter->fetchCol($select);
    }

    /**
     * Save products
     *
     * @param Astrio_Documentation_Model_Document $document document
     * @return $this
     */
    public function saveProducts(Astrio_Documentation_Model_Document $document)
    {
        $products = $document->getProductsData();
        if (!is_null($products) && is_array($products)) {
            $adapter = $this->_getWriteAdapter();

            $documentId = (int) $document->getId();

            $adapter->delete($this->_productsTable, $adapter->quoteInto('document_id = ?', $documentId));

            $products = array_unique($products);

            $insertData = array();
            foreach ($products as $productId) {
                $insertData[] = array(
                    'document_id'   => $documentId,
                    'product_id'    => $productId,
                );
            }

            /**
             * @var $adapter Varien_Db_Adapter_Pdo_Mysql
             */
            $adapter->insertOnDuplicate($this->_productsTable, $insertData, array());
        }

        return $this;
    }
}
