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
 * @package    Astrio_Core
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Astrio
 *
 * @category Astrio
 * @package Astrio_Core
 * @author Eldaniz Gasymov <e.gasymov@astrio.net>
 */
class Astrio_Core_Rewrite_Mage_Widget_Block_Adminhtml_Widget_Instance_Edit_Tab_Main_Layout
    extends Mage_Widget_Block_Adminhtml_Widget_Instance_Edit_Tab_Main_Layout
{
    /**
     * Internal constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('astrio/core/widget/instance/edit/layout.phtml');
    }

    /**
     * Get display on options
     *
     * @return array|mixed
     */
    protected function _getDisplayOnOptions()
    {
        $options = parent::_getDisplayOnOptions();

        $transport = new Varien_Object();
        $transport->setData('options', $options);

        Mage::dispatchEvent('adminhtml_widget_instance_edit_tab_main_layout_get_display_on_options', array(
            'main_layout'   => $this,
            'transport'     => $transport,
        ));

        $options = $transport->getData('options');

        return $options;
    }

    /**
     * Get display on containers
     *
     * @return array|mixed
     */
    public function getDisplayOnContainers()
    {
        $container = parent::getDisplayOnContainers();

        $transport = new Varien_Object();
        $transport->setData('container', $container);

        Mage::dispatchEvent('adminhtml_widget_instance_edit_tab_main_layout_get_display_on_containers', array(
            'main_layout'   => $this,
            'transport'     => $transport,
        ));

        $container = $transport->getData('container');

        return $container;
    }
}
