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
class Astrio_Documentation_Model_Resource_Category_Collection
    extends Astrio_Core_Model_Resource_Abstract_Collection
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('astrio_documentation/category');
    }

    /**
     * Get hash array
     *
     * @return array
     */
    public function getHashArray()
    {
        $adapter = $this->getConnection();

        $select = $adapter->select()
            ->from($this->getMainTable(), array(
                $this->getResource()->getIdFieldName(),
                'name',
            ))
            ->order('name ' . self::SORT_ORDER_ASC);

        return $adapter->fetchPairs($select);
    }
}
