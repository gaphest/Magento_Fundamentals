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
 * @package    Astrio_Menu
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Astrio
 *
 * @category Astrio
 * @package Astrio_Menu
 * @author Eldaniz Gasymov <e.gasymov@astrio.net>
 */
class Astrio_Menu_Adminhtml_Menu_ItemController extends Mage_Adminhtml_Controller_Action
{

    /**
     * @var Astrio_Menu_Helper_Data
     */
    protected $_helper = null;

    /**
     * Get helper
     *
     * @return Astrio_Menu_Helper_Data
     */
    protected function _getHelper()
    {
        if (null === $this->_helper) {
            $this->_helper = Mage::helper('astrio_menu');
        }
        return $this->_helper;
    }

    /**
     * Add content
     *
     * @param Mage_Core_Block_Abstract $block block
     * @return $this
     */
    protected function _addContent(Mage_Core_Block_Abstract $block)
    {
        $this->getLayout()->getBlock('content')->append($block);
        return $this;
    }

    /**
     * Init layout, menu and breadcrumb
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('astrio/menu')
            ->_addBreadcrumb($this->_getHelper()->__('Menu Management'), $this->_getHelper()->__('Menu Management'));

        $this->_title($this->_getHelper()->__('Menu Item Management'));

        return $this;
    }

    /**
     * Index action
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('astrio_menu/adminhtml_menu_item'));
        $this->renderLayout();
    }

    /**
     * Show Menu Management edit/create page
     */
    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('astrio_menu/menu_item')->load($id);

        if ($model->getId() || $id == 0) {
            $data = $this->_getSession()->getFormData(true);
            if (!empty($data)) {
                $model->addData($data);
            }

            Mage::register('astrio_menu_item', $model);

            $this->_initAction();
            $this->_title($this->_getHelper()->__($id == 0 ? 'New Menu Item' : 'Edit Menu Item'));
            $this->_addContent($this->getLayout()->createBlock('astrio_menu/adminhtml_menu_item_edit'));
            $this->renderLayout();
        } else {
            $this->_getSession()->addError($this->_getHelper()->__('Menu Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * New action
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Menu Management save action
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            try {
                /** @var $model Astrio_Menu_Model_Menu_Item */
                $model = Mage::getModel('astrio_menu/menu_item')->load($this->getRequest()->getParam('id'));
                
                $model->addData($data);
                // save and redirect
                $model->save();

                $this->_getSession()->addSuccess($this->_getHelper()->__('The Menu Item has been saved.'));

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_getSession()->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            } catch (Exception $e) {
                $this->_getSession()->addException($e, $this->_getHelper()->__('An error occurred while saving Menu Item.'));
                $this->_getSession()->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
            return;
        }
        $this->_getSession()->addError($this->_getHelper()->__('Unable to find Menu Item to save'));
        $this->_redirect('*/*/');
    }

    /**
     * Menu Management delete action
     */
    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('astrio_menu/menu_item');
                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();
                $this->_getSession()->addSuccess($this->_getHelper()->__('The Menu Item has been deleted.'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Menu Management grid action
     */
    public function gridAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('astrio_menu/adminhtml_menu_item_grid')->toHtml());
    }

    /**
     * Get if is allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        /**
         * @var $session Mage_Admin_Model_Session
         */
        $session = Mage::getSingleton('admin/session');

        return $session->isAllowed('astrio/menu');
    }
}
