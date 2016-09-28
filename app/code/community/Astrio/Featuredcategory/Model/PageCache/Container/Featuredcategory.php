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
 * @package    Astrio_Featuredcategory
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Featuredcategory Block FPC Container
 *
 * @category Astrio
 * @package  Astrio_Featuredcategory
 * @author   Astrio developers <developers@astrio.net>
 */
class Astrio_Featuredcategory_Model_PageCache_Container_Featuredcategory extends Enterprise_PageCache_Model_Container_Abstract
{

    /**
     * Get cache additional identifiers from cookies.
     * Customers are differentiated because they can have different content of featured cats (due to template variables)
     * or different sets of featured cats targeted to their segment.
     *
     * @return string
     */
    protected function _getIdentifier()
    {
        return $this->_getCookieValue(Enterprise_PageCache_Model_Cookie::COOKIE_CUSTOMER, '');
    }

    /**
     * Returns cache identifier for informational data about customer featured cats
     *
     * @return string
     */
    protected function _getCacheId()
    {
        return 'FEATUREDCATEGORY_'
            . md5($this->_placeholder->getAttribute('cache_id')
            . '_' . $this->_getIdentifier());
    }

    /**
     * Save data to cache storage
     *
     * @param string   $data     data
     * @param string   $id       id
     * @param array    $tags     tags
     * @param null|int $lifetime life time
     * @return $this
     */
    protected function _saveCache($data, $id, $tags = array(), $lifetime = null)
    {
        // we are not save this block because it is random
        //parent::_saveCache($data, $id, $tags, $lifetime);
        return $this;
    }

    /**
     * Render block that was not cached
     *
     * @return false|string
     */
    protected function _renderBlock()
    {
        $block = $this->_getPlaceHolderBlock();

        $sub_cat_num = unserialize($this->_placeholder->getAttribute('sub_cat_num'));
        $cat_num = unserialize($this->_placeholder->getAttribute('cat_num'));
        $all_category = unserialize($this->_placeholder->getAttribute('all_category'));
        $all_subcategory = unserialize($this->_placeholder->getAttribute('all_subcategory'));
        $cur_category = unserialize($this->_placeholder->getAttribute('cur_category'));

        $block->setSubCatNum($sub_cat_num);
        $block->setCatNum($cat_num);
        $block->setAllCategory($all_category);
        $block->setAllSubcategory($all_subcategory);
        $block->setCurCategory($cur_category);

        $block->setTemplate($this->_placeholder->getAttribute('template'));
        $block->setLayout(Mage::app()->getLayout());

        return $block->toHtml();
    }
}
