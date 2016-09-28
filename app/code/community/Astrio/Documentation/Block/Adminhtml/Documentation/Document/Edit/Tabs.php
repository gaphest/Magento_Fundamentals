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
class Astrio_Documentation_Block_Adminhtml_Documentation_Document_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('astrio_documentation_document_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('Document Information'));
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
                'astrio_documentation/adminhtml_documentation_document_edit_tab_base',
                'documentation_document_edit_tab_base'
            );

        $this->addTab('base', array(
            'label' => $this->__('Base Info'),
            'title' => $this->__('Base Info'),
            'content' => $baseTab->toHtml(),
        ));

        $this->addTab('products', array(
            'label'     => $this->__('Products'),
            'url'       => $this->getUrl('*/*/products', array('_current' => true)),
            'class'     => 'ajax',
        ));

        Mage::dispatchEvent('adminhtml_astrio_documentation_item_edit_tabs', array('tabs' => $this));
        return parent::_beforeToHtml();
    }
}
