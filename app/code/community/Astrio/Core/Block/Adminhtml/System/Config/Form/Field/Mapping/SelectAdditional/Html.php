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
 * @see Mage_Adminhtml_Block_System_Convert_Gui_Edit_Tab_Wizard
 */
class Astrio_Core_Block_Adminhtml_System_Config_Form_Field_Mapping_SelectAdditional_Html
    extends Astrio_Core_Block_Adminhtml_System_Config_Form_Field_Mapping_AbstractHtml
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('astrio/core/system/config/form/field/mapping/selectAdditional/html.phtml');
    }

    /**
     * Get additional options
     *
     * @return array
     */
    public function getAdditionalOptions()
    {
        return array(
            0 => $this->__('On Create'),
            1 => $this->__('On Update'),
        );
    }
}