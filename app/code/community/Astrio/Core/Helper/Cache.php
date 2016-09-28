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
class Astrio_Core_Helper_Cache extends Mage_Core_Helper_Abstract
{

    protected $_isCacheEnabled = null;

    /**
     * is cache enabled ?
     *
     * @param string $code code
     * @return bool
     */
    public function isCacheEnabled($code)
    {
        if (!isset($this->_isCacheEnabled[$code])) {
            /**
             * @var $cacheResource Mage_Core_Model_Resource_Cache
             */
            $cacheResource = Mage::getResourceModel('core/cache');
            $adapter = $cacheResource->getReadConnection();
            $select = $adapter->select();
            $select->from($cacheResource->getMainTable(), array('value'))
                ->where('code = ?', $code);

            $this->_isCacheEnabled[$code] = (bool) $adapter->fetchOne($select);
        }

        return $this->_isCacheEnabled[$code];
    }
}