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
 * @package    Astrio_Video
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Block init product
 *
 * @category   Astrio
 * @package    Astrio_Video
 * @author     Astrio developers <developers@astrio.net>
 */
class Astrio_Video_Block_Init_Product extends Astrio_Video_Block_Product_List
{
    /**
     * Do not render template if video is not assigned.
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getVideoUrl()) {
            return '';
        }

        return parent::_toHtml();
    }
}
