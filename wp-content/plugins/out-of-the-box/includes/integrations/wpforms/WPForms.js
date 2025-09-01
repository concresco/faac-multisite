(function ($) {
    /* Module Configurator Popup */
    $('#wpforms-builder').on('click', '.outofthebox.open-shortcode-builder', function () {
        var input_field = $(this).prev();
        var shortcode = input_field.val();

        window.addEventListener('message', callback_handler);
        openShortcodeBuilder(shortcode);

        $('.wpcp_data_input').removeClass('wpcp_data_input');
        input_field.addClass('wpcp_data_input');
    });

    function callback_handler(event) {
        if (event.origin !== window.parent.location.origin) {
            return;
        }

        if (
            typeof event.data !== 'object' ||
            event.data === null ||
            typeof event.data.action === 'undefined' ||
            typeof event.data.shortcode === 'undefined'
        ) {
            return;
        }

        if (event.data.action !== 'wpcp-shortcode') {
            return;
        }

        if (event.data.slug !== 'outofthebox') {
            return;
        }

        $('.wpcp_data_input').val(event.data.shortcode).trigger('keyup change');
        window.modal_action.close();
        $('#wpcp-modal-action.OutoftheBox').remove();

        window.removeEventListener('message', callback_handler);
    }

    function openShortcodeBuilder(shortcode) {
        if ($('#wpcp-modal-action.OutoftheBox').length > 0) {
            window.modal_action.close();
            $('#wpcp-modal-action.OutoftheBox').remove();
        }

        /* Build the  Dialog */
        let modalbuttons = '';
        let modalheader = $(
            `<div class="wpcp-modal-header" tabindex="0">                          
                    <a tabindex="0" class="close-button"  onclick="window.modal_action.close();"><i class="eva eva-close eva-lg" aria-hidden="true"></i></a>
                </div>`
        );
        let modalbody = $('<div class="wpcp-modal-body" tabindex="0" style="display:none;padding:0!important;"></div>');
        let modaldialog = $(
            '<div id="wpcp-modal-action" class="OutoftheBox wpcp wpcp-modal wpcp-modal80 wpcp-modal-minimal light"><div class="modal-dialog"><div class="modal-content"><div class="loading"><div class="loader-beat"></div></div></div></div></div>'
        );

        $('body').append(modaldialog);

        var query = 'shortcode=' + WPCP_shortcodeEncode(shortcode);

        var $iframe_template = $(
            "<iframe src='" +
                window.ajaxurl +
                '?action=outofthebox-getpopup&type=modules&foruploadfield=1&callback=uses_listener&' +
                query +
                "' width='100%' height='600' tabindex='-1' style='border:none' title=''></iframe>"
        );
        var $iframe = $iframe_template.appendTo(modalbody);

        $('#wpcp-modal-action.OutoftheBox .modal-content').append(modalheader, modalbody);

        $iframe.on('load', function () {
            $('.wpcp-modal-body').fadeIn();
            $('.wpcp-modal-footer').fadeIn();
            $('.modal-content .loading:first').fadeOut();
        });

        /* Open the Dialog */
        let modal_action = new RModal(document.getElementById('wpcp-modal-action'), {
            bodyClass: 'rmodal-open',
            dialogOpenClass: 'animated slideInDown',
            dialogCloseClass: 'animated slideOutUp',
            escapeClose: true,
            afterClose() {
                window.removeEventListener('message', callback_handler);
            },
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
    }
})(jQuery);
