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
 * @package    Astrio_News
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Astrio
 *
 * @category   Astrio
 * @package    Astrio_News
 * @author     Eldaniz Gasymov <e.gasymov@astrio.net>
 */
class Astrio_News_Model_Category_Attribute_Backend_UrlKey extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{
    
    /**
     * Format url key attribute before save, also use news title as url key if it empty
     *
     * @param  Varien_Object $object object
     * @return $this
     */
    public function beforeSave($object)
    {
        /**
         * @var $object Astrio_News_Model_Category
         */
        $attributeName = $this->getAttribute()->getName();

        $urlKey = $object->getData($attributeName);
        if ($urlKey === false) {
            return $this;
        }

        if ($urlKey == '') {
            $urlKey = $object->getName();
        }

        $object->setData($attributeName, $object->formatUrlKey($urlKey));

        return $this;
    }
}
