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
class Astrio_Documentation_DocumentController extends Mage_Core_Controller_Front_Action
{

    /**
     * Promo form page
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Documentation'));
        $this->renderLayout();
    }

    /**
     * Download action
     */
    public function downloadAction()
    {
        $id = $this->getRequest()->getParam('id');
        $document = Mage::getModel('astrio_documentation/document')->load($id);
        if (!$document->getId() || !$document->isFile() || !$document->getIsActive()) {
            $this->_forward('noRoute');
            return;
        }

        try {
            $response = $this->getResponse();
            Mage::helper('astrio_documentation')->loadDocument($document, $response);
            exit(0);
        } catch (Exception $e) {
            Mage::getSingleton('customer/session')->addError($this->__('Can not download file.'));
            $this->_redirect('*/*');
        }
    }
}
