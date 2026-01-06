(function ($) {
    "use strict";

    let editormetabox = jQuery('#wpdesk-coupons_editor_metabox');
    if (typeof wpdesk_canva_editor_shortcodes !== 'undefined' && editormetabox.length ) {
        if ( wpdesk_canva_editor_shortcodes.length < 3 ) {
            jQuery('.pro-link-box').show();
        }
    }

    let submit_button = jQuery('input[name="save_wpdesk_canva_template"]');
    if (submit_button.length) {
        submit_button.click(function () {
            let post_id = $('#editor_post_id').val();
            let title = $('#title').val();
            let spinner = jQuery($(this)).parent().find('.spinner');
            spinner.css('visibility', 'visible');

            let editor_data = window.WPDeskCanvaEditor.getEditorData();

            var data = {
                action: 'editor_save_post_' + wp_canva_admin.post_type,
                editor_data: editor_data,
                post_id: post_id,
                post_title: title,
                security: wp_canva_admin.nonce,
            };
            jQuery.post(ajaxurl, data, function (response) {
                if (response.success) {
                    spinner.css('visibility', 'hidden');
                    jQuery('#process_save_template').text(response.data.message);
                    // After save change URL for edit page.
                    var url = 'post.php?post=' + response.data.post_id + '&action=edit';
                    window.history.pushState("post", response.data.post_id, url);
                    jQuery('#coupons_post_id').val(response.data.post_id);
                } else {
                    spinner.css('visibility', 'hidden');
                    jQuery('#process_save_template').text(response.data.message);
                }
                setTimeout(function () {
                    jQuery('#process_save_template').text('');
                }, 2000);
            });
            return false;
        });
    }
})
(jQuery);
