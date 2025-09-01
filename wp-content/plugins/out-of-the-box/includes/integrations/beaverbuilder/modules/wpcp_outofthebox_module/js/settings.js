(function ($) {
    FLBuilder._registerModuleHelper('wpcp_outofthebox_module', {
        /**
         * The 'rules' property is where you setup
         * validation rules that are passed to the jQuery
         * validate plugin (http://jqueryvalidation.org).
         *
         * @property rules
         * @type object
         */
        rules: {
            raw_shortcode: {
                required: true,
            },
        },

        /**
         * The 'init' method is called by the builder when
         * the settings form is opened.
         *
         * @method init
         */
        init: function () {
            let self = this;

            $('#fl-raw_shortcode-select').on('click', function () {
                window.addEventListener('message', self.callback_handler);
                self.openShortcodeBuilder();
            });
        },

        callback_handler: function (event) {
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

            $('#fl-raw_shortcode-input').val(event.data.shortcode).trigger('input');
            window.modal_action.close();
            $('#wpcp-modal-action.OutoftheBox').remove();

            window.removeEventListener('message', self.callback_handler);
        },

        openShortcodeBuilder: function () {
            if ($('#wpcp-modal-action.OutoftheBox').length > 0) {
                window.modal_action.close();
                $('#wpcp-modal-action.OutoftheBox').remove();
            }

            /* Build the  Dialog */
            var modalbuttons = '';
            let modalheader = $(
                `<div class="wpcp-modal-header" tabindex="0">                          
                    <a tabindex="0" class="close-button"  onclick="window.modal_action.close();"><i class="eva eva-close eva-lg" aria-hidden="true"></i></a>
                </div>`
            );
            let modalbody = $('<div class="wpcp-modal-body" tabindex="0" style="display:none"></div>');
            let modalfooter = '';
            let modaldialog = $(
                '<div id="wpcp-modal-action" class="OutoftheBox wpcp wpcp-modal wpcp-modal80 wpcp-modal-minimal light"><div class="modal-dialog"><div class="modal-content"><div class="loading"><div class="loader-beat"></div></div></div></div></div>'
            );

            $('body').append(modaldialog);

            var raw_content = $('#fl-raw_shortcode-input').val();
            var shortcode = raw_content.replace('</p>', '').replace('<p>', '');
            var query = 'shortcode=' + WPCP_shortcodeEncode(shortcode);

            var $iframe_template = $(
                "<iframe src='" +
                    window.ajaxurl +
                    '?action=outofthebox-getpopup&type=modules&callback=uses_listener&' +
                    query +
                    "' width='100%' tabindex='-1' style='border:none' title=''></iframe>"
            );
            var $iframe = $iframe_template.appendTo(modalbody);

            $('#wpcp-modal-action.OutoftheBox .modal-content').append(modalheader, modalbody, modalfooter);

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
                    window.removeEventListener('message', self.callback_handler);
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
        },
    });
})(jQuery);
