<?php

/**
 * Astrio Co.
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
 * @copyright  Copyright (c) 2010-2016 Astrio Co. (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 *  File uploader helper
 *
 * @category   Astrio
 * @package    Astrio_Core
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_Core_Helper_FileUploader extends Mage_Core_Helper_Abstract
{

    /**
     * Get file data for Varien_File_Uploader from nested file input
     *
     * <input type="file" name="product[options][2][values][1][image]">
     *
     * $fileData = $helper->getUploadFileDataByPath('product/options/2/values/1/image');
     * $uploader = new Varien_File_Uploader($fileData);
     *
     * @param string $path file input path
     * @return array|bool
     */
    public function getUploadFileDataByPath($path)
    {
        $path = trim($path);
        if (!strlen($path)) {
            return false;
        }

        $parts = explode('/', $path);
        $firstKey = array_shift($parts);
        if (!isset($_FILES[$firstKey])) {
            return false;
        }

        $dataKeys = array('name', 'type', 'tmp_name', 'error', 'size');
        $data = array();
        foreach ($dataKeys as $dataKey) {
            $value = null;
            if (isset($_FILES[$firstKey][$dataKey])) {
                $current = &$_FILES[$firstKey][$dataKey];
                $partsTmp = $parts;
                if (count($partsTmp)) {
                    while (count($partsTmp)) {
                        $partKey = array_shift($partsTmp);
                        if (isset($current[$partKey])) {
                            $current = &$current[$partKey];
                            if (!count($partsTmp)) {
                                $value = $current;
                            }
                        } else {
                            break;
                        }
                    }

                } else {
                    $value = $current;
                }
            }
            if ($value !== null) {
                $data[$dataKey] = $value;
            }
        }

        if (empty($data['tmp_name'])) {
            return false;
        }
        return $data;
    }

}