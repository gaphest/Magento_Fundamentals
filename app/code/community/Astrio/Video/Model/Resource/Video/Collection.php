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
 * @package    Astrio_Video
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Video collection
 *
 * @category   Astrio
 * @package    Astrio_Video
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_Video_Model_Resource_Video_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('astrio_video/video');
    }

    /**
     * Add product filter
     *
     * @param  Mage_Catalog_Model_Product|int $product product model or id
     * @return $this
     */
    public function addProductFilter($product)
    {
        $productId = is_object($product) ? $product->getId() : intval($product);
        $this->addFieldToFilter('product_id', $productId);
        return $this;
    }

    /**
     * Add position order
     *
     * @param  string $dir direction
     * @return $this
     */
    public function addPositionOrder($dir = self::SORT_ORDER_ASC)
    {
        $this->setOrder('position', $dir);
        return $this;
    }
}
