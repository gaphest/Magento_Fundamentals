jQuery(function($) {
    // do nothing if there is no URL to send
    if (!window.CALLME_FORM_URL || !window.CALLME_POST_URL) {
        return;
    }

    // конструктор объекта
    var CallMeObject = function () {
        this.init();
    };
    CallMeObject.prototype.config = {
        selectors: {
            cmButton: '.call-me-button'
        },
        collections: {}
    };
    
    CallMeObject.prototype.init = function () {
        var self = this;
        self.createCallMeButton();
        self.onCallMeButton();
        self.onCallMeFormSubmit();
    };

    CallMeObject.prototype.createCallMeButton = function () {
        var self = this,
            defaultBtnText = (Translator) ? Translator.translate("Call Me") : "Call Me",
            $newButton = $('<div class="call-me-wrapper"><button class="button"><span><span>' + defaultBtnText + '</span></span></button></div>'),
            buttonClass= self.config.selectors.cmButton.replace('.', '');
        $newButton.addClass(buttonClass);
        $('#header').append($newButton);
        self.config.collections.$cmButton = $newButton;
    };

    CallMeObject.prototype.onCallMeButton = function () {
        var self = this;


        self.config.collections.$cmButton.magnificPopup({
            type:'ajax',
            ajax: {
                settings: {
                    'type'      : 'GET',
                    'dataType'  : 'html',
                    'async'     : true,
                    'cache'     : false,
                    'url'       : CALLME_FORM_URL,
                    'data'      : ''
                }
            },
            callbacks: {
                parseAjax: function(mfpResponse) {
                    // mfpResponse.data is a "data" object from ajax "success" callback
                    // for simple HTML file, it will be just String
                    // You may modify it to change contents of the popup
                    // For example, to show just #some-element:
                    // mfpResponse.data = $(mfpResponse.data).find('#some-element');

                    // mfpResponse.data must be a String or a DOM (jQuery) element

//                    console.log('Ajax content loaded:', mfpResponse);
                },
                ajaxContentAdded: function() {
                    // Ajax content is loaded and appended to DOM
//                    console.log(this.content);
                }
            }
        });





        self.config.collections.$cmButton.on('click', function(e) {
            var $button = $(this);
            e.preventDefault();
            $.ajax({
                'type'      : 'GET',
                'dataType'  : 'html',
                'async'     : true,
                'cache'     : false,
                'url'       : CALLME_FORM_URL,
                'data'      : '',
                'beforeSend': function(jqXHR, settings) {
                    $(document).trigger('custom_ajax_before_send');
                },
                'complete'  : function(textStatus, jqXHR) {
                    $(document).trigger('custom_ajax_complete');
                },
                'error'     : function(jqXHR, textStatus, errorThrown) {

                },
                'success'   : function(data, textStatus, jqXHR) {
//                    $button.after(data);
                    $('#some-button').magnificPopup({
                        items: {
                            src: 'path-to-image-1.jpg'
                        },
                        type: 'image' // this is default type
                    });
                }
            });
        });
    };

    CallMeObject.prototype.onCallMeFormSubmit = function () {
        $(document).on('submit', '#callme-form-popup', function(e) {
            if (!window.callmePopupForm) {
                return;
            }
            e.preventDefault();
            var $form = $(this);

            if (callmePopupForm.validator && callmePopupForm.validator.validate()) {
                $.ajax({
                    'type'	    : 'POST',
                    'dataType'  : 'json',
                    'async'	    : true,
                    'cache'	    : false,
                    'url'	    : CALLME_POST_URL,
                    'data'	    : $form.serialize(),
                    beforeSend : function (){
                        $('button', $form).prop('disabled', true);
                        $(document).trigger('custom_ajax_before_send');
                    },
                    'complete'  : function(textStatus, jqXHR) {
                        $('button', $form).prop('disabled', false);
                        $(document).trigger('custom_ajax_complete');
                    },
                    'error'     : function(jqXHR, textStatus, errorThrown) {

                    },
                    'success'   : function(data, textStatus, jqXHR) {
                        if (data.success){
                            $.magnificPopup.close();
                            $('.col-main').prepend('<ul class="messages" id="email_confirmation_msg"> <li class="notice-msg email-msg">'+data.message+'</li></ul>');
                        } else {
                            $form.find('div.msg').remove();
                            var msg = '<div class="msg">' + data.message + '</div>';
                            $form.append(msg);
                        }
                    }
                });
            }
        });
    };

    $(document).ready(function () {
        new CallMeObject();
    });
});