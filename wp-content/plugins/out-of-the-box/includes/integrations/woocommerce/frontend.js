jQuery(document).ready(function ($) {
    'use strict';
    $.widget('cp.OutoftheBoxWC', {
        options: {},

        _create: function () {
            /* Ignite! */
            this._initiate();
        },

        _destroy: function () {
            return this._super();
        },

        _setOption: function (key, value) {
            this._super(key, value);
        },

        _initiate: function () {
            let self = this;
            self._initButtons();
            self._initDetails();
        },

        _initButtons: function () {
            let self = this;

            $('.wpcp-outofthebox .wpcp-wc-open-box').on('click', function (e) {
                self.openUploadBox($(this));
            });
        },

        _initDetails: function () {
            let self = this;

            $('.wpcp-outofthebox.wpcp-upload-container').each(function (e) {
                var item_id = $(this).data('item-id');
                var listtoken = $(this).find('.OutoftheBox').data('token');
                self._loadDetails(item_id, listtoken);
            });
        },

        _loadDetails: function (item_id, listtoken) {
            let self = this;
            let $upload_list = $('#wpcp-outofthebox-uploads-' + item_id + ' .wpcp-uploads-list');

            $.ajax({
                type: 'POST',
                url: self.options.ajax_url,
                data: {
                    action: 'outofthebox-get-filelist',
                    type: 'wc-item-details',
                    item_id: item_id,
                    listtoken: listtoken,
                    _ajax_nonce: self.options.refresh_nonce,
                },
                complete: function (response) {
                    $upload_list.addClass('wpcp-uploads-received');
                },
                success: function (response) {
                    if ($.isPlainObject(response) === false || response.length === 0) {
                        $upload_list.addClass('no-uploads-found');
                        return;
                    }

                    $upload_list.html('');
                    $.each(response, function (id, entry) {
                        if (entry === '') {
                            return;
                        }
                        $upload_list.append(
                            `<li><img class="wpcp-uploads-list-item-icon" src="${entry.icon}"/><div class="wpcp-uploads-list-item-name">${entry.name}</div><div class="wpcp-uploads-list-item-download"><a href=${entry.url}" download="${entry.name}"><i class="eva eva-download eva-lg"></i></a></div></li>`
                        );
                    });

                    $upload_list.fadeIn();
                },
                dataType: 'json',
            });
        },

        openUploadBox: function (button) {
            let self = this;

            var container = button.parent().find('.woocommerce-order-upload-box');
            var item_id = button.parent('[data-item-id]').data('item-id');
            var listtoken = container.find('[data-token]').data('token');

            /* Close any open modal windows */
            $('#wpcp-modal-upload-action').remove();

            /* Build the Upload Dialog */
            let modalbuttons = `<button class="button wpcp-modal-cancel-btn" data-action="cancel" type="button" onclick="modal_upload_action.close();" title="${self.options.str_close_title}">${self.options.str_close_title}</button>`;

            let modalheader = $(
                `<div class="wpcp-modal-header" tabindex="0">
                    <h2 id="uploads"><i class="eva eva-attach"></i> ${container.attr('data-title')}</h2>                
                    <div class="wpcp-modal-badges-group">
                        <div class="wpcp-modal-badge"><i class="eva eva-cube-outline eva-lg"></i> ${container.attr(
                            'data-order'
                        )}</div>
                        <div class="wpcp-modal-badge"><i class="eva eva-pricetags-outline eva-lg"></i> ${container.attr(
                            'data-product-name'
                        )}</div>
                    </div>                
                    <a tabindex="0" class="close-button" title="${
                        self.options.str_close_title
                    }" onclick="modal_upload_action.close();"><i class="eva eva-close eva-lg" aria-hidden="true"></i></a>
                </div>`
            );
            let modalbody = $('<div class="wpcp-modal-body" tabindex="0"></div>');
            let modalfooter = $(
                `<div class="wpcp-modal-footer"><div class="wpcp-modal-buttons">${modalbuttons}</div></div>`
            );

            let modaldialog = $(
                `<div id="wpcp-modal-upload-action" class="OutoftheBox wpcp wpcp-modal wpcp-woocommerce-upload-container ${self.options.content_skin}"><div class="modal-dialog"><div class="modal-content"></div></div></div>`
            );

            $('body').append(modaldialog);
            $('#wpcp-modal-upload-action .modal-content').append(modalheader, modalbody, modalfooter);

            /* Fill Textarea */
            $('.wpcp-modal-body').append(container);
            container.show();

            /* Set the button actions */
            $('#wpcp-modal-upload-action .wpcp-modal-confirm-btn').on('click', function (e) {
                modal_action.close();
            });

            /* Open the dialog */
            let modal_action = new RModal(document.getElementById('wpcp-modal-upload-action'), {
                bodyClass: 'rmodal-open',
                dialogOpenClass: 'animated slideInDown',
                dialogCloseClass: 'animated slideOutUp',
                escapeClose: true,
                afterClose() {
                    container.hide();
                    button.after(container);
                    self._loadDetails(item_id, listtoken);
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
            window.modal_upload_action = modal_action;
            return false;
        },
    });
});

// Initiate the Module!
jQuery(document).ready(function ($) {
    $(document).OutoftheBoxWC(OutoftheBox_vars);
});
