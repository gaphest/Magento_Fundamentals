/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    design
 * @package     rwd_default
 * @copyright   Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

$j(document).ready(function () {

    // ==============================================
    // UI Pattern - Slideshow
    // ==============================================

    var $slideshow = $j('.slideshow-container .slideshow')
        .owlCarousel({
            singleItem:true,
            slideSpeed : 600,
            paginationSpeed : 600,
            rewindSpeed : 600,
            autoPlay : true,
            stopOnHover : true,
            pagination : true,
            paginationNumbers: false
        });

    if($slideshow.length){
        $j('.slideshow-container .slideshow-prev').on('click',function(){
            $slideshow.data('owl-carousel').prev();
        });
        $j('.slideshow-container .slideshow-next').on('click',function(){
            $slideshow.data('owl-carousel').next();
        });
    }


});
