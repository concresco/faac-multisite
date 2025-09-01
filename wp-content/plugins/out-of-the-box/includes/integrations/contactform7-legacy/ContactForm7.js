jQuery(document).ready(function ($) {
    'use strict';

    $('body').on('change', '.outofthebox-shortcode-value', function () {
        var decoded_shortcode = WPCP_shortcodeDecode($(this).val());
        $('#outofthebox-shortcode-decoded-value').val(decoded_shortcode).css('display', 'block');
    });

    $('body').on('keyup', '#outofthebox-shortcode-decoded-value', function () {
        var encoded_data = WPCP_shortcodeEncode($(this).val());
        $('.outofthebox-shortcode-value', 'body').val(encoded_data);
        $('.outofthebox-shortcode-value').trigger('change');
    });

    var default_value =
        '[outofthebox  mode="upload" upload="1" uploadrole="all" upload_auto_start="0" viewrole="all" userfolders="auto" viewuserfoldersrole="none"]';
    var encoded_data = WPCP_shortcodeEncode(default_value);
    $('.outofthebox-shortcode-value', 'body').val(encoded_data).trigger('change');

    // Callback function to add shortcode to CF7 input field
    if (typeof window.wpcp_oftb_cf7_add_content === 'undefined') {
        window.wpcp_oftb_cf7_add_content = function (data) {
            var encoded_data = WPCP_shortcodeEncode(data);

            $('.outofthebox-shortcode-value').val(encoded_data);
            $('.outofthebox-shortcode-value').trigger('change');

            if (data.indexOf('userfolders="auto"') > -1) {
                $('.out-of-the-box-upload-folder').fadeIn();
            } else {
                $('.out-of-the-box-upload-folder').fadeOut();
            }

            window.modal_action.close();
        };
    }

    // Modal opening Module Configurator
    $('body').on('click', '.OutoftheBox-CF-shortcodegenerator', function () {
        if ($('#wpcp-modal-action.OutoftheBox').length > 0) {
            window.modal_action.close();
            $('#wpcp-modal-action.OutoftheBox').remove();
        }

        /* Build the Insert Dialog */
        let modalbuttons = '';
        let modalheader = $(
            `<div class="wpcp-modal-header" tabindex="0">                          
                    <a tabindex="0" class="close-button"  onclick="window.modal_action.close();"><i class="eva eva-close eva-lg" aria-hidden="true"></i></a>
                </div>`
        );
        let modalbody = $('<div class="wpcp-modal-body" tabindex="0" style="display:none"></div>');
        let modalfooter = $(
            '<div class="wpcp-modal-footer" style="display:none"><div class="wpcp-modal-buttons"></div></div>'
        );
        let modaldialog = $(
            '<div id="wpcp-modal-action" class="OutoftheBox wpcp wpcp-modal wpcp-modal80 wpcp-modal-minimal light"><div class="modal-dialog"><div class="modal-content"><div class="loading"><div class="loader-beat"></div></div></div></div></div>'
        );

        $('body').append(modaldialog);

        var $iframe_template = $('#outofthebox-shortcode-iframe');
        var $iframe = $iframe_template.clone().appendTo(modalbody).show();

        $('#wpcp-modal-action.OutoftheBox .modal-content').append(modalheader, modalbody, modalfooter);

        var raw_content = $('#outofthebox-shortcode-decoded-value', 'body').val();
        var shortcode = raw_content.replace('</p>', '').replace('<p>', '');
        var query = 'shortcode=' + WPCP_shortcodeEncode(shortcode);

        $iframe.attr('src', $iframe_template.attr('data-src') + '&' + query);

        $iframe.on('load', function () {
            $('.wpcp-modal-body').fadeIn();
            $('.wpcp-modal-footer').fadeIn();
            $('.modal-content .loading:first').fadeOut();
        });

        /* Open the Dialog and load the images inside it */
        let modal_action = new RModal(document.getElementById('wpcp-modal-action'), {
            bodyClass: 'rmodal-open',
            dialogOpenClass: 'animated slideInDown',
            dialogCloseClass: 'animated slideOutUp',
            escapeClose: true,
        });
        document.addEventListener(
            'keydown',
            function (ev) {
                modal_action.keydown(ev);
            },
            false
        );
        modal_action.open();
        window.modal_action = modal_action;
    });
});
