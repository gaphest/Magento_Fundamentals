(function($) {
    $.productQuickView = function(options) {

        // Set the default options
        var defaults = {};

        // Merge the user defined options with the default options
        options = $.extend(defaults, options);

        $.productQuickViewOptions = options;

        /**
         * replace http / https to current protocol
         *
         * @param url
         * @returns {*}
         */
        var protocolReplace = function(url) {
            var currentUrlProtocol = window.location.protocol;
            var protocols = ['http:', 'https:'];
            for (var i = 0; i < protocols.length; i++) {
                if (url.indexOf(protocols[i]) == 0 && currentUrlProtocol != protocols[i]) {
                    return currentUrlProtocol + url.substr(protocols[i].length);
                }
            }
            return url;
        };

        var getProductId = function(link){
            var productId = false,
                classList = link.className.split(/\s+/);
            var targetClass = options.link_class + '-';
            for (var i = 0; i < classList.length; i++) {
                if (classList[i].indexOf(targetClass) === 0) {
                    productId = parseInt(classList[i].substr(targetClass.length));
                    break;
                }
            }
            return productId;
        };


        /**
         * inititalization
         */
        var initialize = function() {
            var url = protocolReplace(options.url);
            var linkClass = options.link_class ? options.link_class : false;
            if (linkClass && linkClass != ''){
                $('.' + linkClass).click(function(e){
                    var $link = $(this);
                    var productId = getProductId(this);
                    if (productId){
                        e.preventDefault();
                        sendAjax(url, {id: productId, currentUrl: window.location.href}, $link);
                    }
                });
            }
        };


        /**
         * method for show / hide overlay. before send ajax and after
         *
         * @param isStart
         */
        var runAjax = function(isStart) {
            isStart ? $('body').css({opacity: 0.5}) : $('body').css({opacity: 1.0});
        };

        var sendAjax = function(url, data, $element) {
            $.ajax({
                'type'      : 'GET',
                'dataType'  : 'json',
                'async'     : true,
                'url'       : url,
                'data'      : data,
                'beforeSend': function(jqXHR, settings) {
                    runAjax(true);
                },
                'complete'  : function(textStatus, jqXHR) {
                    runAjax(false);
                },
                'error'     : function(jqXHR, textStatus, errorThrown) {
                    alert('Some errors occurred!');
                },
                'success'   : function(data, textStatus, jqXHR) {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }
                    else if (data.success && data.blocks){
                        showBlocks(data.blocks);
                    }
                    else if(data.error && data.error_text){
                        alert(data.error_text);
                    }
                }
            });
        };

        var showBlocks = function(data){
            // show popup etc
            /*$.each(data.blocks, function(name, html) {

            });*/

            //afterProductShow();
        };

        var afterProductShow = function(){
            if (window.optionsPrice && window.optionsPrice.productId){
                optionsPrice.containers[optionsPrice.containers.length] = 'product-price-qv-' + optionsPrice.productId;
            }
        };

        initialize();
    };

    $.productQuickViewOptions = {};

    /**
     * reinitialize quick view with the same parameters
     */
    $.reInitProductQuickView = function () {
        $.productQuickView($.productQuickViewOptions);
    };
}(jQuery));