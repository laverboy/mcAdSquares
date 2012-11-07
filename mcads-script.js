/*global ajaxurl */
/*jshint devel:true */
/*jshint bitwise:false */

jQuery(document).ready(function($) {
    //mcads functions
    var pluginUrl = $('#mcadsheader').data('pluginurl');


    var showError = function (message) {
        var flash = $('div.flash');
        flash.css('border-width', '1px');
        flash.html('<p><strong>' + message + '</strong></p>').show();
        flash.delay(7000).fadeOut(function () {
            flash.css('border-width', '0');
            flash.html('');
        });
    };

    var handledrop = function (event) {
        
        //stop normal things happening
        event.stopPropagation();
        event.preventDefault();

        var self = this, index = $(this).index();

        //get data from file dropped
        var e = event.originalEvent;
        e.dataTransfer.dropEffect = 'copy';

        var file = e.dataTransfer.files[0];
        if (file.size > 500000) {
            /* throw some kind of too big error */
            console.log('bigger than 500k');
            showError("I'm afraid that file is too big, please keep it under 500KB.");
            return false;
        }

        //fire off to wordpress to store it in the plugin file system
        var formData = new FormData();
        formData.append('file', file);
        console.log(formData);

        // ?? save the url or the fact that it is being used ??

        formData.append('action', 'mcads_action');
        formData.append('index', index);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false
        }).done(function (resp) {
            console.log('uploaded: ', resp);

            if(resp === 'not jpeg') {
                showError("Just jpegs please.");
                return false;
            }

            //if all good need to show new image from file
            $(self).find('img').attr('src', pluginUrl + 'thumbs/' + index + '-thumb.jpg');
        });

    };

    $('.adsquare').on('drop', handledrop);
    
});