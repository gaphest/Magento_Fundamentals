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
 *  Rewrites block
 *
 * @category   Astrio
 * @package    Astrio_Core
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_Core_Block_Adminhtml_Rewrites extends Mage_Adminhtml_Block_Template
{

    /**
     * Get rewrite list
     *
     * @return array|bool
     */
    public function getRewritesList()
    {
        $moduleFiles = glob(Mage::getBaseDir('etc') . DS . 'modules' . DS . '*.xml');

        if (!$moduleFiles) {
            return false;
        }

        // load file contents
        $unsortedConfig = new Varien_Simplexml_Config();
        $unsortedConfig->loadString('<config/>');
        $fileConfig = new Varien_Simplexml_Config();

        foreach ($moduleFiles as $filePath) {
            $fileConfig->loadFile($filePath);
            $unsortedConfig->extend($fileConfig);
        }

        // create sorted config [only active modules]
        $sortedConfig = new Varien_Simplexml_Config();
        $sortedConfig->loadString('<config><modules/></config>');

        foreach ($unsortedConfig->getNode('modules')->children() as $moduleName => $moduleNode) {
            if ('true' === (string)$moduleNode->active) {
                $sortedConfig->getNode('modules')->appendChild($moduleNode);
            }
        }

        $fileConfig = new Varien_Simplexml_Config();
        $_finalResult = array();


        foreach ($sortedConfig->getNode('modules')->children() as $moduleName => $moduleNode) {
            $codePool = (string)$moduleNode->codePool;
            $configPath = BP . DS . 'app' . DS . 'code' . DS . $codePool . DS . uc_words($moduleName, DS) . DS . 'etc' . DS . 'config.xml';

            if (!file_exists($configPath)) {
                continue;
            }

            $fileConfig->loadFile($configPath);
            $rewriteBlocks = array('blocks', 'models', 'helpers');
            foreach ($rewriteBlocks as $param) {
                if (!isset($_finalResult[$param])) {
                    $_finalResult[$param] = array();
                }

                if ($rewrites = $fileConfig->getXpath('global/' . $param . '/*/rewrite')) {
                    foreach ($rewrites as $rewrite) {
                        $parentElement = $rewrite->xpath('../..');
                        foreach ($parentElement[0] as $moduleKey => $moduleItems) {
                            foreach ($moduleItems->rewrite as $rewriteLine) {
                                foreach ($rewriteLine as $key => $value) {
                                    $_finalResult[$param][$moduleKey][$key]['rewrites'][] = (string)$value;

                                    if (!isset($_finalResult[$param][$moduleKey][$key]['current'])) {
                                        $class = (string) Mage::getConfig()->getNode('global/' . $param . '/' . $moduleKey . '/rewrite/' . $key);
                                        $_finalResult[$param][$moduleKey][$key]['current'] = $class;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $_finalResult;
    }


}