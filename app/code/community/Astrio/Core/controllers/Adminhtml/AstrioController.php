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
class Astrio_Core_Adminhtml_AstrioController extends Mage_Adminhtml_Controller_Action
{
    /**
     * clean unused product images
     * running from button at system -> cache
     */
    public function cleanImagesAction()
    {
        try {
            /**
             * @var $imageClearer Astrio_Core_Model_ImageClearer
             */
            $imageClearer = Mage::getModel('astrio_core/imageClearer');
            $imageClearer->clearUnusedImages();
            $this->_getSession()->addSuccess(Mage::helper('astrio_core')->__('The unused images was successfully removed.'));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addException($e, Mage::helper('astrio_core')->__('An error occurred while clearing the unused images.'));
        }

        $this->_redirect('*/cache');
    }

    /**
     * @return array
     */
    protected function _getProcesses()
    {
        $processes = array();

        $factory = new Mage_Core_Model_Factory();

        /**
         * @var $indexer Mage_Index_Model_Indexer
         * @var $process Mage_Index_Model_Process
         */
        $indexer = $factory->getSingleton($factory->getIndexClassAlias());

        $collection = $indexer->getProcessesCollection();
        foreach ($collection as $process) {
            if ($process->getIndexer()->isVisible() === false) {
                continue;
            }
            $processes[] = $process;
        }

        return $processes;
    }

    /**
     * reindex all action.
     * works like reindex all from shell.
     * running from button at system -> index
     */
    public function reindexAllAction()
    {
        $processes = $this->_getProcesses();

        try {
            Mage::dispatchEvent('shell_reindex_init_process');
            foreach ($processes as $process) {
                /* @var $process Mage_Index_Model_Process */
                try {
                    $process->reindexEverything();
                    Mage::dispatchEvent($process->getIndexerCode() . '_shell_reindex_after');
                    $this->_getSession()->addSuccess($process->getIndexer()->getName() . " index was rebuilt successfully");
                } catch (Mage_Core_Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                } catch (Exception $e) {
                    $this->_getSession()->addError($process->getIndexer()->getName() . " index process unknown error:" . $e->__toString());
                }
            }
            Mage::dispatchEvent('shell_reindex_finalize_process');
        } catch (Exception $e) {
            Mage::dispatchEvent('shell_reindex_finalize_process');
            $this->_getSession()->addError($e->getMessage());
        }

        $this->_redirect('*/process/list');
    }

    /**
     * Rewrites Action
     */
    public function rewritesAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        /**
         * @var $adminSession Mage_Admin_Model_Session
         */
        $adminSession = Mage::getSingleton('admin/session');
        return $adminSession->isAllowed('astrio/astrio_core');
    }
}