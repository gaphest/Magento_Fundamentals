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
 * @package    Astrio_SpecialProducts
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Astrio
 *
 * @category   Astrio
 * @package    Astrio_SpecialProducts
 * @author     Eldaniz Gasymov <e.gasymov@astrio.net>
 */
class Astrio_SpecialProducts_Block_Adminhtml_SpecialProducts_Set_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    
    /**
     * @var Astrio_SpecialProducts_Helper_Data
     */
    protected $_helper = null;

    /**
     * Get Astrio_SpecialProducts helper
     *
     * @return Astrio_SpecialProducts_Helper_Data
     */
    protected function _getHelper()
    {
        if (null === $this->_helper) {
            $this->_helper = Mage::helper('astrio_specialproducts');
        }
        return $this->_helper;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('menu_item_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->_getHelper()->__('Special Products Set Information'));
    }

    /**
     * before to make html
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _beforeToHtml()
    {
        /**
         * @var $set Astrio_SpecialProducts_Model_Set
         */
        $set = Mage::registry('astrio_specialproducts_set');

        if ($set->getId()) {
            $storeIds = $set->getStoreIds();

            foreach ($storeIds as $storeId) {
                $storeId = (int) $storeId;

                $gridId = 'specialproducts_edit_tab_products_' . $storeId;

                /**
                 * @var $grid Astrio_SpecialProducts_Block_Adminhtml_SpecialProducts_Set_Edit_Tab_Products
                 */
                $grid = $this->getLayout()->createBlock(
                    'astrio_specialproducts/adminhtml_specialProducts_set_edit_tab_products',
                    $gridId,
                    array(
                        'id'        => $gridId,
                        'store_id'  => $storeId
                    )
                );

                $gridHtml = $grid->toHtml();

                if (!$set->getIsAuto()) {
                    /**
                     * @var $serializer Mage_Adminhtml_Block_Widget_Grid_Serializer
                     */
                    $serializer = $this->getLayout()->createBlock('adminhtml/widget_grid_serializer');
                    $serializer->addColumnInputName('position');
                    $serializer->initSerializerBlock(
                        $gridId,
                        'getSelectedProducts',
                        'set[products][' . $storeId . ']',
                        'products_' . $storeId
                    );

                    $gridHtml .= $serializer->toHtml();
                }

                $this->addTab('products_' . $storeId, array(
                    'label'     => $grid->getTabLabel(),
                    'title'     => $grid->getTabTitle(),
                    'content'   => $gridHtml,
                ));
            }
        }

        Mage::dispatchEvent('adminhtml_specialproducts_set_edit_tabs', array('tabs' => $this));

        return parent::_beforeToHtml();
    }
}
