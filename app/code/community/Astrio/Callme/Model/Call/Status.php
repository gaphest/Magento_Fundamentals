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
 * @package    Astrio_Callme
 * @copyright  Copyright (c) 2010-2013 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 *  Call Status Model
 *
 * @category   Astrio
 * @package    Astrio_Callme
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_Callme_Model_Call_Status extends Mage_Core_Model_Abstract
{

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'astrio_callme_call_status';

    /**
     * Event object name
     *
     * @var string
     */
    protected $_eventObject = 'status';

    /**
     * Internal constructor not depended on params. Can be used for object initialization
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('astrio_callme/call_status');
    }

    /**
     * Validate
     *
     * @return array|bool
     * @throws Exception
     * @throws Zend_Validate_Exception
     */
    public function validate()
    {
        $helper = $this->_getCallmeHelper();
        $errors = array();
        if (!Zend_Validate::is( trim($this->getName()) , 'NotEmpty')) {
            $errors[] = $helper->__('Status name cannot be empty.');
        }

        if (!Zend_Validate::is( $this->formatCode($this->getCode()) , 'NotEmpty')) {
            $errors[] = $helper->__('Status Code cannot be empty.');
        }

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }

    /**
     * Before save
     *
     * @return Mage_Core_Model_Abstract
     * @throws Mage_Core_Exception
     */
    protected function _beforeSave()
    {
        if ($this->hasData('code')) {
            $code = $this->formatCode($this->getData('code'));
            if (empty($code)) {
                Mage::throwException($this->_getCallmeHelper('Status Code cannot be empty.'));
            }

            $this->setCode($code);
        }
        return parent::_beforeSave();
    }

    /**
     * Format code
     *
     * @param string $code code
     * @return mixed|string
     */
    public function formatCode($code)
    {
        $code = mb_strtolower(trim($code), 'UTF-8');
        $code = str_replace(' ', '_', $code);
        $code = preg_replace('/[^a-z\_\-0-9]/', '', $code);
        return $code;
    }


    /**
     * @return Astrio_Callme_Helper_Data
     */
    protected function _getCallmeHelper()
    {
        return Mage::helper('astrio_callme');
    }

}