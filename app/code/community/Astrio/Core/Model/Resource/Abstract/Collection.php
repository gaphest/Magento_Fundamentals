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
abstract class Astrio_Core_Model_Resource_Abstract_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * do not load collection if used this method
     *
     * @return $this
     */
    public function setIsLoaded()
    {
        $this->_setIsLoaded(true);
        return $this;
    }

    /**
     * Add collection filters by identifiers
     *
     * @param mixed $id id
     * @param boolean $exclude exclude?
     * @return $this
     */
    public function addIdFilter($id, $exclude = false)
    {
        if (empty($id)) {
            $this->_setIsLoaded(true);
            return $this;
        }

        if (is_array($id)) {
            if ($exclude) {
                $condition = array('nin' => $id);
            } else {
                $condition = array('in' => $id);
            }
        } else {
            if ($exclude) {
                $condition = array('neq' => $id);
            } else {
                $condition = $id;
            }
        }

        $this->addFieldToFilter('main_table.' . $this->getResource()->getIdFieldName(), $condition);
        return $this;
    }
}