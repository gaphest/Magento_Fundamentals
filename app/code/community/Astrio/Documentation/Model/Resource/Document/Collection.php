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
class Astrio_Documentation_Model_Resource_Document_Collection
    extends Astrio_Core_Model_Resource_Abstract_Collection
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('astrio_documentation/document');
    }

    /**
     * Add is active filter
     *
     * @param int $isActive is active?
     * @return $this
     */
    public function addIsActiveFilter($isActive = 1)
    {
        $isActive = (bool) $isActive;
        $this->addFieldToFilter('is_active', (int) $isActive);
        return $this;
    }
}
