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
class Astrio_Core_Rewrite_Mage_Widget_Model_Widget_Instance extends Mage_Widget_Model_Widget_Instance
{
    /**
     * Internal Constructor
     */
    protected function _construct()
    {
        parent::_construct();

        $transport = new Varien_Object();
        $transport->setData('layout_handles', $this->_layoutHandles);
        $transport->setData('specific_entities_layout_handles', $this->_specificEntitiesLayoutHandles);

        Mage::dispatchEvent('widget_instance_create_layout_handles_options', array(
            'widget_instance'   => $this,
            'transport'         => $transport,
        ));

        $this->_layoutHandles                   = $transport->getData('layout_handles');
        $this->_specificEntitiesLayoutHandles   = $transport->getData('specific_entities_layout_handles');
    }
}
