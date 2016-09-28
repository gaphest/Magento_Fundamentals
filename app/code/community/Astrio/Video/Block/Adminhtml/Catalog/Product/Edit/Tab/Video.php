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
 * Product video tab
 *
 * @category   Astrio
 * @package    Astrio_Video
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_Video_Block_Adminhtml_Catalog_Product_Edit_Tab_Video extends Mage_Adminhtml_Block_Widget
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    protected $_videoCollection = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setSkipGenerateContent(true);
        $this->setTemplate('astrio/video/product/edit/tab/video.phtml');
    }

    /**
     * Preparing layout, adding buttons
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('astrio_video')->__('Delete'),
                    'class' => 'delete astrio-delete-video'
                ))
        );

        $this->setChild('add_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('astrio_video')->__('Add Video'),
                    'class' => 'add',
                    'id'    => 'astrio_add_new_video_button'
                ))
        );

        return parent::_prepareLayout();
    }

    /**
     * Get if can show tab
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Get tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Video');
    }

    /**
     * Get tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Video');
    }

    /**
     * Get if is hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Get tab url
     *
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('*/video_product_edit/form', array('_current' => true));
    }

    /**
     * Get tab class
     *
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax';
    }

    /**
     * Get videos
     *
     * @return Astrio_Video_Model_Resource_Video_Collection|null
     */
    public function getVideos()
    {
        if ($this->_videoCollection === null) {
            /** @var  Astrio_Video_Model_Resource_Video_Collection $collection */
            $collection = Mage::getResourceModel('astrio_video/video_collection');
            $collection
                ->addProductFilter((int)$this->getProductId())
                ->addPositionOrder();

            $this->_videoCollection = $collection;
        }

        return $this->_videoCollection;
    }

    /**
     * Get product id
     *
     * @return bool|mixed
     */
    public function getProductId()
    {
        if ($this->hasData('product_id')) {
            return $this->getData('product_id');
        }

        if (Mage::registry('product')) {
            return Mage::registry('product')->getId();
        }

        if (Mage::registry('current_product')) {
            return Mage::registry('current_product')->getId();
        }

        return false;
    }


    /**
     * Retrieve HTML of delete button
     *
     * @return string
     */
    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

    /**
     * Retrieve HTML of add button
     *
     * @return string
     */
    public function getAddNewButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }
}
