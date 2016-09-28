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
 * Product video list
 *
 * @category   Astrio
 * @package    Astrio_Video
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_Video_Block_Product_List extends Mage_Core_Block_Template
{

    protected $_collection = null;

    protected $_product = null;

    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();

        $this->addData(array(
            'cache_lifetime' => 14400,
            'cache_tags'     => array(Mage_Catalog_Model_Product::CACHE_TAG),
        ));
    }

    /**
     * Get video collection
     *
     * @return null|Astrio_Video_Model_Resource_Video_Collection
     */
    public function getCollection()
    {
        if ($this->_collection === null) {
            $productId = $this->getProduct() ? $this->getProduct()->getId() : 0;
            /** @var  Astrio_Video_Model_Resource_Video_Collection $collection */
            $collection = Mage::getResourceModel('astrio_video/video_collection');
            $collection
                ->addProductFilter($productId)
                ->addPositionOrder();

            $this->_collection = $collection;
        }

        return $this->_collection;
    }

    /**
     * Get product
     *
     * @return bool|Mage_Core_Model_Abstract|mixed|null
     */
    public function getProduct()
    {
        if ($this->_product === null) {
            $this->_product = false;
            if ($this->getProductId()) {
                $this->_product = Mage::getModel('catalog/product')->load((int)$this->getProductId());
            } elseif (Mage::registry('current_product')) {
                $this->_product = Mage::registry('current_product');
            }
        }

        return $this->_product;
    }

    /**
     * Get json config
     *
     * @param array $additionalConfig additional config
     * @return mixed
     */
    public function getJsonConfig($additionalConfig = array())
    {
        $config = array();

        /** @var  Astrio_Video_Model_Video $video */

        foreach ($this->getCollection() as $video) {
            $data = array();
            $data['id'] = $video->getId();
            $data['title'] = $video->getTitle();
            $data['video_url'] = $video->getEmbedVideoUrl();
            $data['thumbnail_url'] = $video->getThumbnailUrl();
            $data['type'] = $video->getType();
            $data['content'] = $video->getContent();
            $config['videos'][$video->getId()] = $data;
        }

        $config['video_url'] = $this->getVideoUrl();

        if ($additionalConfig) {
            $config = array_merge($config, $additionalConfig);
        }

        return Mage::helper('core')->jsonEncode($config);
    }

    /**
     * Get first product video url
     *
     * @return string
     */
    public function getVideoUrl()
    {
        foreach ($this->getCollection() as $video) {
            /** @var  Astrio_Video_Model_Video $video */
            if ($url = $video->getEmbedVideoUrl()) {
                return $url;
            }
        }

        return '';
    }

    /**
     * do not render template if video is not assigned.
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getProduct()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $info = parent::getCacheKeyInfo();
        if ($product = $this->getProduct()) {
            $info[] = $product->getId();
        }

        return $info;
    }
}
