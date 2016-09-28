(function($) {
    $.cartAjax = function(options) {

        // Set the default options
        var defaults = {
            selector_button             : '.cart-ajax',
            selector_overlay            : '#cart-ajax-overlay',
            attribute_url               : 'data-cart-ajax-url',
            attribute_related_qty_id    : 'data-cart-ajax-qty-field-id',
            attribute_related_form_id   : 'data-cart-ajax-form-id'
        };

        // Merge the user defined options with the default options
        options = $.extend(defaults, options);

        $.cartAjaxOptions = options;

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

        /**
         * get ajax url
         *
         * @param $element
         * @returns {}
         */
        var getUrl = function($element) {
            //var isLink = $element.prop('tagName') == 'A';
            var url = /*isLink ? $element.attr('href') : */$element.attr(options.attribute_url);

            return protocolReplace(url);
        };

        /**
         * get qty
         *
         * @param $element
         * @returns {*}
         */
        var getQty = function($element) {

            var qty = null;

            if ($element.attr(options.attribute_related_qty_id)) {
                var $qtyField = $('#' + $element.attr(options.attribute_related_qty_id));
                if ($qtyField.length) {
                    qty = parseInt($qtyField.val());
                    if (isNaN(qty) || qty <= 0) {
                        qty = null;
                    }
                }
            }

            return qty;
        };

        /**
         * get form
         *
         * @param $element
         * @returns {*}
         */
        var getForm = function($element) {
            if ($element.attr(options.attribute_related_form_id)) {
                var $form = $('#' + $element.attr(options.attribute_related_form_id));
                if ($form.length) {
                    return $form;
                }
            }
            return false;
        };

        /**
         * validate form
         *
         * @param $form
         * @returns {*}
         */
        var validateVarienForm = function($form) {
            if ($form) {
                var elementVarienForm = new VarienForm($form.attr('id'));
                return elementVarienForm.validator.validate();
            }
            return true;
        };

        /**
         * get post data for request
         *
         * @param $element
         * @param $form
         * @returns {*}
         */
        var getPostData = function($element, $form) {
            if ($form) {
                return $form.serialize();
            }

            var qty = getQty($element);
            if (qty) {
                return 'qty=' + qty;
            }

            return '';
        };

        var $overlay = $(options.selector_overlay);

        /**
         * method for show / hide overlay. before send ajax and after
         *
         * @param isStart
         */
        var runAjax = function(isStart) {
            if ($overlay.length) {
                isStart ? $overlay.show() : $overlay.hide();
            }
        };

        /**
         * send ajax request
         *
         * @param url
         * @param postData
         * @param $element
         */
        var sendAjax = function(url, postData, $element) {
            $.ajax({
                'type'      : 'POST',
                'dataType'  : 'json',
                'async'     : true,
                'cache'     : false,
                'url'       : url,
                'data'      : postData,
                'beforeSend': function(jqXHR, settings) {
                    runAjax(true);
                },
                'complete'  : function(textStatus, jqXHR) {
                    runAjax(false);
                },
                'error'     : function(jqXHR, textStatus, errorThrown) {
                    alert('Error');
                },
                'success'   : function(data, textStatus, jqXHR) {
                    if (!data.success) {
                        window.location.href = data.redirect;
                    } else if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        /**
                         * data['blocks'] - object {{name:html}...}
                         */
                        $(document).trigger('cart-ajax-success',data['blocks']);
                    }
                }
            });
        };

        /**
         * inititalization
         */
        var initialize = function() {
            var $elements = $(options.selector_button);

            $.each($elements, function(index, element) {
                var $element = $(element);

                if ($element.data('cart-ajax-initialized')) {
                    return;
                }

                $element.data('cart-ajax-initialized', true);

                var $form = getForm($element);
                if ($form && $form.find('input[type="file"]').length) {
                    return;
                }

                $element.prop('onclick', null);

                $element.on('click', function(event) {
                    event.preventDefault();

                    //validate form if form specified exists
                    if ($form && !validateVarienForm($form)) {
                        return;
                    }

                    //get url for ajax request
                    var url = getUrl($element);

                    //get post data for ajax request
                    var postData = getPostData($element, $form);

                    sendAjax(url, postData, $element);
                });
            });
        };

        initialize();
    };

    $.cartAjaxOptions = {};

    /**
     * reinitialize cart ajax with the same parameters
     */
    $.reInitCartAjax = function () {
        $.cartAjax($.cartAjaxOptions);
    };
}(jQuery));