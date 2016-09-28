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
                        sendAjax(url, {id: productId, currentUrl: window.location.href }, $link);
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

            $.magnificPopup.open({
                items: {
                    src: url
                },
                callbacks: {
                    parseAjax: function(mfpResponse) {
                        if (mfpResponse.data && mfpResponse.data.error){
                            mfpResponse.data = mfpResponse.data.error_text;
                        }
                        else if (mfpResponse.data && mfpResponse.data.success && mfpResponse.data.blocks){
                            mfpResponse.data = '<div class="quick-product-popup">' + mfpResponse.data.blocks['product.info'] + '</div>';
                        }
                        else{
                            mfpResponse.data = 'Some errors occurred!';
                        }
                    },
                    ajaxContentAdded: function() {
                        afterProductShow();
                    }
                },
                type: 'ajax',
                ajax: {
                    settings: {
                        contentType: "application/json",
                        dataType: 'json',
                        data: data,
                        type: 'GET'
                    }
                }

            });

        };

        var afterProductShow = function(){
            if (window.optionsPrice && window.optionsPrice.productId){
                optionsPrice.containers[5] = 'product-price-qv-' + optionsPrice.productId;
            }
            ProductMediaManager.wireThumbnailsWithoutZoom();
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