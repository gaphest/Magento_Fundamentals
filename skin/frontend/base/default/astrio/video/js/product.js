(function($) {
    $.fn.productVideo = function(options) {

        // Set the default options
        var defaults = {
            video_url               : '',
            container_filter        : '.more-views ul',
            container_html          : '<div class="more-views"><h2>More Views</h2><ul class="product-image-thumbs"></ul></div>',
            gallery_filter          : '.product-image-gallery',
            video_width             : 583,
            video_height            : 438
        };

        // Merge the user defined options with the default options
        options = $.extend(defaults, options);

        if (!options.video_url && !options.videos) {
            return;
        }

        if (!options.videos){
            options.videos = {1:{video_url: options.video_url, title: '', thumbnail_url: ''}}
        }

        var image_box = $(this);

        var gallery = image_box.find(options.gallery_filter);
        if (!image_box.length || !gallery.length) {
            return;
        }

        var container = image_box.find(options.container_filter);
        if (!container.length) {
            $(options.container_html).appendTo(image_box);
            container = $(options.container_filter);
        }
        if (!container.length || !gallery.length) {
            return;
        }

        var video_icon;
        for(var i in options.videos) {
            if (options.videos.hasOwnProperty(i)){
                if (options.videos[i].video_url){

                    if (options.videos[i].thumbnail_url){
                        video_icon = $('<li><a class="thumb-link" href="#"><img src="'+ options.videos[i].thumbnail_url + '" alt="'+ options.videos[i].title +'"/></a></li>').appendTo(container);
                    }
                    else{
                        video_icon = $('<li><a class="thumb-link" href="#"><span class="video-icon"></span></a></li>').appendTo(container);
                    }
                    $(video_icon).data('video-id', i);
                    video_icon.click(function() {
                        var target = gallery.find('.video-container');
                        var videoId = $(this).data('video-id');
                        var object = '<object width="' + options.video_width + '" height="' + options.video_height + '" data="' + options.videos[videoId].video_url + '" />';
                        if (!target.length) {
                            target =  $('<div class="video-container">' + object + '</div>').appendTo(gallery);
                        }
                        else{
                            target.html(object);
                        }
                        ProductMediaManager.swapImage(target);

                    });
                }
            }
        }
    };


    $.fn.productVideoList = function(options) {

        // Set the default options
        var defaults = {
            gallery_filter          : '.product-image-gallery',
            link_filter             : '.video-link',
            img_box_selector:       '.product-view .product-img-box:first',
            video_width             : 583,
            video_height            : 438
        };

        // Merge the user defined options with the default options
        options = $.extend(defaults, options);

        if (!options.videos) {
            return;
        }

        var image_box = $(options.img_box_selector);

        var gallery = image_box.find(options.gallery_filter);

        if (!image_box.length || !gallery.length) {
            return;
        }

        var list = $(this);

        list.find(options.link_filter).click(function(e){
            var videoId = $(this).data('video-id');
            if (videoId){
                e.preventDefault();
                var target = gallery.find('.video-container');
                var video_url = options.videos[videoId].video_url;
                if (video_url){
                    var object = '<object width="' + options.video_width + '" height="' + options.video_height + '" data="' + video_url + '" />';
                    if (!target.length) {
                        target =  $('<div class="video-container">' + object + '</div>').appendTo(gallery);
                    }
                    else{
                        target.html(object);
                    }
                    ProductMediaManager.swapImage(target);
                }
                else{
                    alert(Translator.translate('Video url not found!') );
                }
            }

        });
    };
}(jQuery));