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
class Astrio_SpecialProducts_Model_Observer
{

    /**
     * event: widget_instance_create_layout_handles_options
     *
     * @param  Varien_Event_Observer $observer observer
     * @return $this
     */
    public function addHandlesToWidgetInstance(Varien_Event_Observer $observer)
    {
        $transport = $observer->getEvent()->getData('transport');

        $layoutHandles                   = $transport->getData('layout_handles');
        $specificEntitiesLayoutHandles   = $transport->getData('specific_entities_layout_handles');

        $layoutHandles['special_products_set_page'] = 'astrio_specialproducts_set_index';
        $specificEntitiesLayoutHandles['special_products_set_page'] = 'SPECIAL_PRODUCTS_SET_PAGE_{{ID}}';

        $transport->setData('layout_handles', $layoutHandles);
        $transport->setData('specific_entities_layout_handles', $specificEntitiesLayoutHandles);

        return $this;
    }

    /**
     * event: adminhtml_widget_instance_edit_tab_main_layout_get_display_on_options
     *
     * @param  Varien_Event_Observer $observer observer
     * @return $this
     */
    public function addDisplayOnOptionsToWidgetInstance(Varien_Event_Observer $observer)
    {
        $transport = $observer->getEvent()->getData('transport');

        $options = $transport->getData('options');

        $options[] = array(
            'label' => Mage::helper('astrio_specialproducts')->__('Special Products'),
            'value' => array(
                array(
                    'value' => 'special_products_set_page',
                    'label' => Mage::helper('core')->jsQuoteEscape(Mage::helper('astrio_specialproducts')->__('Special Products Set Page'))
                ),
            )
        );

        $transport->setData('options', $options);

        return $this;
    }

    /**
     * event: adminhtml_widget_instance_edit_tab_main_layout_get_display_on_containers
     *
     * @param  Varien_Event_Observer $observer observer
     * @return $this
     */
    public function addDisplayOnContainersToWidgetInstance(Varien_Event_Observer $observer)
    {
        $transport = $observer->getEvent()->getData('transport');

        $container = $transport->getData('container');

        $container['special_products_set_page'] = array(
            'label' => 'Special Products Set Page',
            'code' => 'special_products_set_page',
            'name' => 'special_products_set_page',
            'layout_handle' => 'default,astrio_specialproducts_set_index',
            'is_anchor_only' => 1,
            'product_type_id' => ''
        );

        $transport->setData('container', $container);

        return $this;
    }
}
