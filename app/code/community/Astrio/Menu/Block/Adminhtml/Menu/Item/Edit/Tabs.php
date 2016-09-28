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
 * @package    Astrio_Menu
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Astrio
 *
 * @category Astrio
 * @package Astrio_Menu
 * @author Eldaniz Gasymov <e.gasymov@astrio.net>
 */
class Astrio_Menu_Block_Adminhtml_Menu_Item_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('menu_item_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('astrio_menu')->__('Menu Item Information'));
    }

    /**
     * before to make html
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _beforeToHtml()
    {
        $baseTab = $this->getLayout()
            ->createBlock(
                'astrio_menu/adminhtml_menu_item_edit_tab_base',
                'menu_item_edit_base'
            );

        $this->addTab('base', array(
            'label' => $this->__('Base Info'),
            'title' => $this->__('Base Info'),
            'content' => $baseTab->toHtml(),
        ));


        $contentTab = $this->getLayout()
            ->createBlock(
                'astrio_menu/adminhtml_menu_item_edit_tab_content',
                'menu_item_edit_content'
            );

        $this->addTab('content', array(
            'label' => $this->__('Content'),
            'title' => $this->__('Content'),
            'content' => $contentTab->toHtml(),
        ));

        Mage::dispatchEvent('adminhtml_astrio_menu_item_edit_tabs', array('tabs' => $this));

        return parent::_beforeToHtml();
    }
}