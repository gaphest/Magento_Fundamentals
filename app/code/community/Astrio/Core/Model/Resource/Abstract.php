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
abstract class Astrio_Core_Model_Resource_Abstract extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Truncate
     *
     * @return $this
     */
    public function truncate()
    {
        $this->_getWriteAdapter()->truncateTable($this->getMainTable());
        return $this;
    }

    /**
     * Mass delete
     *
     * @param array $ids ids to delete
     * @return $this
     */
    public function massDelete(array $ids)
    {
        if (!$ids) {
            return $this;
        }

        $this->_getWriteAdapter()->delete(
            $this->getMainTable(),
            $this->_getWriteAdapter()->quoteInto($this->getIdFieldName() . ' IN(?)', $ids)
        );

        return $this;
    }

    /**
     * Mass update
     *
     * @param array $ids ids to update
     * @param string $fieldName field name
     * @param mixed $fieldValue field value
     * @return $this
     */
    public function massUpdate(array $ids, $fieldName, $fieldValue)
    {
        $this->_getWriteAdapter()->update(
            $this->getMainTable(),
            array($fieldName => $fieldValue),
            $this->_getWriteAdapter()->quoteInto($this->getIdFieldName() . ' IN(?)', $ids)
        );

        return $this;
    }
}