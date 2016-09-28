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
class Astrio_Brand_Adminhtml_Widget_InstanceController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Brands action
     */
    public function brandsAction()
    {
        $selected = $this->getRequest()->getParam('selected_brands', '');
        $chooser = $this->getLayout()
            ->createBlock('astrio_brand/adminhtml_brand_widget_chooser')
            ->setName(Mage::helper('core')->uniqHash('brand_grid_'))
            ->setUseMassaction(true)
            ->setSelectedBrands(explode(',', $selected));
        /**
         * @var $serializer Mage_Adminhtml_Block_Widget_Grid_Serializer
         * @var $chooser Astrio_Brand_Block_Adminhtml_Brand_Widget_Chooser
         */
        $serializer = $this->getLayout()->createBlock('adminhtml/widget_grid_serializer');
        $serializer->initSerializerBlock($chooser, 'getSelectedBrands', 'selected_brands', 'selected_brands');
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
