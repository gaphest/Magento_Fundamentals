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
class Astrio_SpecialProducts_Adminhtml_Widget_InstanceController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Special products set pages action
     */
    public function specialProductsSetPagesAction()
    {
        $selected = $this->getRequest()->getParam('selected_set_pages', '');
        $chooser = $this->getLayout()
            ->createBlock('astrio_specialproducts/adminhtml_specialProducts_set_page_widget_chooser')
            ->setName(Mage::helper('core')->uniqHash('special_products_set_page_grid_'))
            ->setUseMassaction(true)
            ->setSelectedSetPages(explode(',', $selected));
        /**
         * @var $serializer Mage_Adminhtml_Block_Widget_Grid_Serializer
         * @var $chooser Astrio_SpecialProducts_Block_Adminhtml_SpecialProducts_Set_Page_Widget_Chooser
         */
        $serializer = $this->getLayout()->createBlock('adminhtml/widget_grid_serializer');
        $serializer->initSerializerBlock($chooser, 'getSelectedSetPages', 'selected_set_pages', 'selected_set_pages');
        $this->_setBody($chooser->toHtml() . $serializer->toHtml());
    }

    /**
     * Set body to response
     *
     * @param string $body body
     */
    private function _setBody($body)
    {
        Mage::getSingleton('core/translate_inline')->processResponseBody($body);
        $this->getResponse()->setBody($body);
    }

    /**
     * Check is allowed access to action
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('cms/widget_instance');
    }
}
