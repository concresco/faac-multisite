(function ($) {
    'use strict';

    $(window).on('elementor/frontend/init', function () {
        elementor.channels.editor.on('wpcp:editor:edit_outofthebox_shortcode', openShortcodeBuilder);
        elementorFrontend.hooks.addAction('frontend/element_ready/wpcp-outofthebox.default', function () {
            $('.OutoftheBox').parent().trigger('inview');
        });
    });

    function openShortcodeBuilder(view) {
        let initiator = view._parent.model.attributes.elType ? 'widget' : 'form';

        window.wpcp_oftb_elementor_add_content = function (value) {
            if (initiator === 'widget') {
                view._parent.model.setSetting('shortcode', value);
                window.parent.jQuery('.elementor-control-shortcode textarea').trigger('input');
            } else {
                view.$el.next().find('textarea').val(value).trigger('input');
            }
            window.modal_action.close();
            $('#wpcp-modal-action.OutoftheBox').remove();
        };

        if ($('#wpcp-modal-action.OutoftheBox').length > 0) {
            if (typeof window.modal_action !== 'undefined') {
                window.modal_action.close();
            }
            $('#wpcp-modal-action.OutoftheBox').remove();
        }

        /* Build the  Dialog */
        let modalbuttons = '';
        let modalheader = $(
            `<div class="wpcp-modal-header" tabindex="0">                          
                    <a tabindex="0" class="close-button"  onclick="window.modal_action.close();"><i class="eva eva-close eva-lg" aria-hidden="true"></i></a>
                </div>`
        );
        let modalbody = $('<div class="wpcp-modal-body" tabindex="0" style="display:none"></div>');
        let modalfooter = $(
            '<div class="wpcp-modal-footer" style="display:none"><div class="wpcp-modal-buttons">' + '' + '</div></div>'
        );
        let modaldialog = $(
            '<div id="wpcp-modal-action" class="OutoftheBox wpcp wpcp-modal wpcp-modal95 wpcp-modal-minimal light"><div class="modal-dialog"><div class="modal-content"><div class="loading"><div class="loader-beat"></div></div></div></div></div>'
        );

        $('body').append(modaldialog);

        let raw_content =
            initiator === 'widget'
                ? view._parent.model.getSetting('shortcode', 'true')
                : view._parent.model.attributes['wpcp_outofthebox_module_data'];
        let shortcode = raw_content.replace('</p>', '').replace('<p>', '');
        let query = 'shortcode=' + WPCP_shortcodeEncode(shortcode);

        let $iframe_template = $(
            "<iframe src='" +
                OutoftheBox_vars.ajax_url +
                '?action=outofthebox-getpopup&type=modules&callback=wpcp_oftb_elementor_add_content&' +
                query +
                (initiator === 'form' ? '&foruploadfield=1' : '') +
                "' width='100%' tabindex='-1' style='border:none' title=''></iframe>"
        );
        let $iframe = $iframe_template.appendTo(modalbody);

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
