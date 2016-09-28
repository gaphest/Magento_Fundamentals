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
 */
class Astrio_Brand_Block_Brand_List extends Mage_Core_Block_Template
{

    protected $_collection = null;

    /**
     * @return Astrio_Brand_Model_Resource_Brand_Collection
     */
    public function getCollection()
    {
        if ($this->_collection === null) {
            /**
             * @var Astrio_Brand_Model_Resource_Brand_Collection $collection
             */
            $collection = Mage::getResourceModel('astrio_brand/brand_collection');
            $collection
                ->addStoreFilter()
                ->addBrandCollectionAttributesToSelect()
                ->addIsActiveFilter()
                ->addUrlRewrite()
                ->setOrderByPosition()
                ->setOrderByName()
                ->load()
            ;

            $this->_collection = $collection;
        }

        return $this->_collection;
    }

    /**
     * Gets columns count
     *
     * @return mixed
     */
    public function getColumnsCount()
    {
        if (!$this->getData('columns_count')) {
            $this->setData('columns_count', 4);
        }

        return $this->getData('columns_count');
    }
}