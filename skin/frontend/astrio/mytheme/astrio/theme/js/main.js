(function ($) {
	/* Main Function */

    var isTouch = Modernizr.touch;
    /*---------------*/
	astrio = function () {

        /*-----------------------Custom functions-----------------------*/

        var skipLinks = function(){
            var skipLinkClass = "skip-link",
                skipContentClass = "skip-content",
                skipActiveClass = "skip-active",
                skipLinks = $('.' + skipLinkClass),
                skipContents = $('.' + skipContentClass),
                hoverTicker = null;

            if(isTouch){
                skipLinks
                    .not('.js-skip-initialised')
                    .on('click', function (e) {
                        e.preventDefault();
                        var self = $(this).addClass('js-skip-initialised'),
                            target = self.attr('data-target-element') ? self.attr('data-target-element') : self.attr('href'),
                            elem = $(target),
                            isSkipContentOpen = elem.hasClass(skipActiveClass) ? 1 : 0;

                        // Hide all stubs
                        skipLinks.removeClass(skipActiveClass);
                        skipContents.removeClass(skipActiveClass);

                        // Toggle stubs
                        if (isSkipContentOpen) {
                            self.removeClass(skipActiveClass);
                        } else {
                            self.addClass(skipActiveClass);
                            elem.addClass(skipActiveClass);
                        }
                });
            } else {
                skipLinks
                    .not('.js-skip-initialised')
                    .each(function(){
                        var self = $(this).addClass('js-skip-initialised'),
                            target = self.attr('data-target-element') ? self.attr('data-target-element') : self.attr('href'),
                            elem = $(target),
                            unHoverTimeout = 500,
                            ourPair = self.add(elem);

                        ourPair
                            .hover(
                                function(){
                                    if(hoverTicker) {
                                        window.clearTimeout(hoverTicker);
                                        hoverTicker = null;
                                    }
                                    if(!$(this).hasClass(skipActiveClass)){
                                        $('.' + skipLinkClass).removeClass(skipActiveClass);
                                        $('.' + skipContentClass).removeClass(skipActiveClass);
                                        ourPair.addClass(skipActiveClass);
                                    }
                                },
                                function(){
                                    hoverTicker = window.setTimeout(function(){
                                        ourPair.removeClass(skipActiveClass);
                                    }, unHoverTimeout);
                                }
                        );
                });
            }
        };

        /*--------------------------------------------------------------*/

		var init = function () {
            skipLinks();
		};

		return {
			init: init,
            skipLinks : skipLinks
		};
	}();
    /*--------------------------------------------------------------*/

    /*-----------------Initialize all js on DOM ready---------------*/
	$(document).ready(function() {
		astrio.init();
	});
    /*--------------------------------------------------------------*/

	/*-----------------Cart Ajax Success---------------*/
	$(document).on('cart-ajax-success',function(event,blocks){
		$.each(blocks, function(name, html) {
			/**
			 * name - name of block in layout
			 * html - content of this block
			 */
            var block = $('[data-block-name="' + name + '"]').html(html),
                skipLinks = block.find('.skip-link'),
                headerCart = block.find('#header-cart'),
                showCartTime = 3000;

            if (skipLinks[0]){
                astrio.skipLinks();
            }

            if(headerCart[0]){
                headerCart.addClass('skip-active');
                setTimeout(function(){
                    headerCart.removeClass('skip-active');
                },showCartTime);
            }

		});
	});
	/*--------------------------------------------------------------*/

})(jQuery);