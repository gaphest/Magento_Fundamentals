/* Simple module */
(function ($) {
    var styleguide = function() {
        var self = this;

        /* vars */
        self.$menuUI = $('.list-group');
        self.$containerUI = $('.list-ui');
        self.menuOffset = self.$menuUI.offset();
        self.popupButton = $('.show-popup');

        /* call init */
        self.init();
    };


    /* declare init */
    styleguide.prototype.init = function() {
        this.stickyMenu();
        this.navHandler();
        this.removeStdClass();
        this.popupInit();
    };

    /* all prototype methods below */
    /*-------------------------------------------------------------*/

    /* sticky menu */
    styleguide.prototype.stickyMenu = function() {
        var self = this;
        self.$menuUI.sticky({topSpacing:self.menuOffset.top, getWidthFrom:self.$menuUI.parent()});
    };

    /* navigation func */
    styleguide.prototype.navHandler = function() {
        var self = this;
        $('a', self.$menuUI).on('click', function() {
            var headingId = $(this).attr('href').replace('#','');
            $(this)
                .addClass('active')
                .siblings()
                .removeClass('active');
            self.$containerUI
                .find('h2')
                .removeClass('active')
                .filter('#' + headingId)
                .addClass('active');
        });
    };

    /* In case .std is redutant we should remove it */
    styleguide.prototype.removeStdClass = function() {
        $('.containter').parent().removeClass('std');
        $('body').removeClass('cms-page-view');
    };

    /* Test popup */
    styleguide.prototype.popupInit = function() {
        $('.show-popup').magnificPopup({
            items: {
                src: '<div class="white-popup-block">Dynamically created popup</div>',
                type: 'inline'
            }
        });
    };

    /*-------------------------------------------------------------*/

    /* Call module */
    $(document).ready(function() {
        new styleguide();
    });

})(jQuery);