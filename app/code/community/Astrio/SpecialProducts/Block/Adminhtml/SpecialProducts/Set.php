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
class Astrio_SpecialProducts_Block_Adminhtml_SpecialProducts_Set extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_controller = 'adminhtml_specialProducts_set';
        $this->_blockGroup = 'astrio_specialproducts';
        $this->_headerText = Mage::helper('astrio_specialproducts')->__('Special Products Sets Management');
        $this->_addButtonLabel = Mage::helper('astrio_specialproducts')->__('Add New Special Products Set');
        parent::__construct();
    }
}
