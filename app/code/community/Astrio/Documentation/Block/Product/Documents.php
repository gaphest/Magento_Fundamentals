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
class Astrio_Documentation_Block_Product_Documents extends Mage_Core_Block_Template
{

    /** @var array $_categories */
    protected $_categories;

    /**
     * Retrieve current product model
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return Mage::registry('product');
    }

    /**
     * Get documents categories
     *
     * @return array
     */
    public function getCategories()
    {
        if (!isset($this->_categories)) {
            $this->_categories = array();
            $product = $this->getProduct();

            if ($product && $product->getId()) {
                /** @var Astrio_Documentation_Model_Resource_Document_Collection $_collection */
                $_collection = Mage::getResourceModel('astrio_documentation/document_collection')
                    ->addIsActiveFilter();
                $_collection->getSelect()->joinInner(
                    array('p' => $_collection->getTable('astrio_documentation/document_product')),
                    'main_table.document_id = p.document_id',
                    ''
                );

                $_collection->addFieldToFilter('p.product_id', $product->getId());
                $_collection->getSelect()->joinInner(
                    array('c' => $_collection->getTable('astrio_documentation/category')),
                    'main_table.category_id = c.category_id',
                    array('category_name' => 'c.name')
                );

                $_collection
                    ->setOrder('c.position', Varien_Data_Collection::SORT_ORDER_ASC)
                    ->setOrder('c.name', Varien_Data_Collection::SORT_ORDER_ASC)
                    ->setOrder('main_table.name', Varien_Data_Collection::SORT_ORDER_ASC)
                    ->setOrder('description', Varien_Data_Collection::SORT_ORDER_DESC);

                if ($_collection->getSize()) {
                    foreach ($_collection as $_document) {
                        $_catId = $_document->getCategoryId();
                        if (!isset($this->_categories[$_catId])) {
                            $this->_categories[$_catId] = array(
                                'name' => $_document->getCategoryName(),
                                'documents' => array(),
                            );
                        }
                        $this->_categories[$_catId]['documents'][] = $_document;
                    }
                }
            }
        }

        return $this->_categories;
    }
}
