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
class Astrio_News_Model_Category_Image extends Astrio_News_Model_Abstract_Image
{

    /**
     * Get entity dir name
     *
     * @return string
     */
    public function getEntityDirName()
    {
        return 'news_category';
    }
}
