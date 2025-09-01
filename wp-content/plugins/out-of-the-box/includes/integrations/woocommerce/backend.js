jQuery(function ($) {
    var outofthebox_wc = {
        // hold a reference to the last selected Dropbox button
        lastSelectedButton: false,
        module: $('#wpcp-modal-selector-dropbox .wpcp-module'),

        init: function () {
            // place wpcp container bottom body
            $('#wpcp-modal-selector-dropbox').parent().appendTo('body');

            // add button for simple product
            this.addButtons();
            this.addButtonEventHandler();
            // add buttons when variable product added
            $('#variable_product_options').on('woocommerce_variations_added', function () {
                outofthebox_wc.addButtons();
            });
            // add buttons when variable products loaded
            $('#woocommerce-product-data').on('woocommerce_variations_loaded', function () {
                outofthebox_wc.addButtons();
            });

            // Select the already added files in the File Browser module
            this.initSelectAdded();
            this.initAddButton();

            return this;
        },

        addButtons: function () {
            let self = this;

            var button = $(
                '<a class="button wpcp-insert-dropbox-content">' +
                    outofthebox_woocommerce_translation.choose_from +
                    '</a>'
            );
            $('.downloadable_files').each(function (index) {
                // we want our button to appear next to the insert button
                var insertButton = $(this).find('a.button.insert');
                // check if button already exists on element, bail if so
                if ($(this).find('a.button.wpcp-insert-dropbox-content').length > 0) {
                    return;
                }

                // finally clone the button to the right place
                insertButton.after(button.clone());
            });

            /* START Support for WooCommerce Product Documents */

            $('.wc-product-documents .button.wc-product-documents-set-file').each(function (index) {
                // check if button already exists on element, bail if so
                if ($(this).parent().find('a.button.wpcp-insert-dropbox-content').length > 0) {
                    return;
                }

                // finally clone the button to the right place
                $(this).after(button.clone());
            });

            $('#wc-product-documents-data').on('click', '.wc-product-documents-add-document', function () {
                self.addButtons();
            });
            /* END Support for WooCommerce Product Documents */
        },
        /**
         * Adds the click event to the dropbox buttons
         * and opens the Dropbox chooser
         */
        addButtonEventHandler: function () {
            let self = this;

            $('#woocommerce-product-data').on('click', 'a.button.wpcp-insert-dropbox-content', function (e) {
                self.openSelector();
                e.preventDefault();

                // save a reference to clicked button
                outofthebox_wc.lastSelectedButton = $(this);
            });

            $('#wpcp-modal-selector-dropbox .wpcp-dialog-close').on('click', function (e) {
                self.closeSelector();
            });

            $('#wpcp-modal-selector-dropbox .wpcp-wc-dialog-entry-select').on('click', function (e) {
                const account_id = self.module.attr('data-account-id');
                const entries_data = self.module
                    .find("input[name='selected-files[]']:checked")
                    .map(function () {
                        const $entry = $(this).parents('.entry');

                        return {
                            entry_id: $entry.attr('data-id'),
                            entry_name: $entry.attr('data-name'),
                            account_id: account_id,
                            entry_path: decodeURIComponent($entry.attr('data-url')),
                        };
                    })
                    .get();

                if (entries_data.length === 0) {
                    return self.closeSelector();
                }

                // Send the data via postMessage
                window.top.postMessage(
                    {
                        slug: 'outofthebox',
                        action: 'wpcp-select-entries',
                        entries: entries_data,
                    },
                    window.location.origin
                );

                setTimeout(function () {
                    self.closeSelector();
                }, 100);
            });
        },

        openSelector: function () {
            let self = this;

            window.addEventListener('message', outofthebox_wc.afterFileSelected);

            $('#wpcp-modal-selector-dropbox').show();
            $('#wpcp-modal-selector-dropbox .wpcp-wc-dialog-entry-select').prop('disabled', 'disabled');
        },

        closeSelector: function () {
            window.removeEventListener('message', outofthebox_wc.afterFileSelected);
            $('#wpcp-modal-selector-dropbox').fadeOut();

            outofthebox_wc.lastSelectedButton = null;
        },

        /**
         * Mark already added file in the File Browser moulde
         */
        initSelectAdded: function () {
            const self = this;

            self.module.on('wpcp-content-loaded', function (e, plugin) {
                plugin.element
                    .find("input[name='selected-files[]']:checked")
                    .prop('checked', false)
                    .removeClass('is-selected');

                const added_files = $(outofthebox_wc.lastSelectedButton)
                    .closest('.downloadable_files')
                    .find('.file_url > input')
                    .filter(function (index) {
                        return $(this).val().includes('https://dropbox.com/');
                    })
                    .toArray();

                added_files.forEach(function (input, index, array) {
                    const url = new URL($(input).val());
                    const entry_id = atob(decodeURIComponent(url.searchParams.get('id')));
                    const account_id = url.searchParams.get('account_id');

                    // Show the entry as selected
                    $('.wpcp-module[data-account-id="' + account_id + '"] .entry[data-id="' + entry_id + '"]').addClass(
                        'is-selected'
                    );
                });
            });
        },

        /**
         * Enable & Disable add button based on selection of entries
         */
        initAddButton: function () {
            let self = this;
            $(self.module).on(
                {
                    change: function (e) {
                        if (self.module.find("input[name='selected-files[]']:checked").length) {
                            $('#wpcp-modal-selector-dropbox .wpcp-wc-dialog-entry-select').prop('disabled', '');
                        } else {
                            $('#wpcp-modal-selector-dropbox .wpcp-wc-dialog-entry-select').prop('disabled', 'disabled');
                        }
                    },
                },
                "input[name='selected-files[]']"
            );
        },

        /**
         * Handle selected files
         */
        afterFileSelected: function (event) {
            let self = this;

            if (event.origin !== window.location.origin) {
                return;
            }

            if (typeof event.data !== 'object' || event.data === null || typeof event.data.action === 'undefined') {
                return;
            }

            if (event.data.action !== 'wpcp-select-entries') {
                return;
            }

            if (event.data.slug !== 'outofthebox') {
                return;
            }

            let files_added = [];
            let files_failed = [];

            event.data.entries.forEach(function (entry, index, array) {
                // Make sure only a single instance of the file can be added
                if (
                    $(outofthebox_wc.lastSelectedButton)
                        .closest('.downloadable_files')
                        .find('.file_url > input')
                        .filter(function (index) {
                            return $(this)
                                .val()
                                .includes(encodeURIComponent(btoa(entry.entry_id)) + '&account_id=' + entry.account_id);
                        }).length
                ) {
                    files_failed.push(entry.entry_name);
                    return false;
                }

                if ($(outofthebox_wc.lastSelectedButton).closest('.downloadable_files').length > 0) {
                    var table = $(outofthebox_wc.lastSelectedButton).closest('.downloadable_files').find('tbody');
                    var template = $(outofthebox_wc.lastSelectedButton)
                        .parent()
                        .find('.button.insert:first')
                        .data('row');
                    var fileRow = $(template);

                    fileRow.find('.file_name > input:first').val(entry.entry_name).change();
                    fileRow
                        .find('.file_url > input')
                        .val(
                            'https://dropbox.com/' +
                                decodeURIComponent(entry.entry_id) +
                                outofthebox_woocommerce_translation.download_url +
                                encodeURIComponent(btoa(entry.entry_id)) +
                                '&account_id=' +
                                entry.account_id
                        );
                    table.append(fileRow);

                    // trigger change event so we can save variation
                    $(table).find('input').last().change();
                }

                /* START Support for WooCommerce Product Documents */
                if ($(outofthebox_wc.lastSelectedButton).closest('.wc-product-document').length > 0) {
                    var row = $(outofthebox_wc.lastSelectedButton).closest('.wc-product-document');

                    row.find('.wc-product-document-label input:first').val(entry.entry_name).change();
                    row.find('.wc-product-document-file-location input:first').val(
                        outofthebox_woocommerce_translation.wcpd_url +
                            encodeURIComponent(btoa(entry.entry_id)) +
                            '&account_id=' +
                            entry.account_id
                    );
                }
                /* END Support for WooCommerce Product Documents */

                // Show the entry as selected
                $(
                    '.wpcp-module[data-account-id="' + entry.account_id + '"] .entry[data-id="' + entry.entry_id + '"]'
                ).addClass('is-selected');

                files_added.push(entry.entry_name);
            });

            if (files_failed.length) {
                window.showNotification(
                    false,
                    outofthebox_woocommerce_translation.notification_failed_file_msg.replace(
                        '{filename}',
                        '<strong>' + files_failed.join(', ') + '</strong>'
                    )
                );
            }

            if (files_added.length) {
                window.showNotification(
                    true,
                    outofthebox_woocommerce_translation.notification_success_file_msg.replace(
                        '{filename}',
                        '<strong>' + files_added.join(', ') + '</strong>'
                    )
                );
            }
        },
    };
    window.outofthebox_wc = outofthebox_wc.init();

    /* Callback function to add shortcode to WC field */
    if (typeof window.wpcp_oftb_wc_add_content === 'undefined') {
        window.wpcp_oftb_wc_add_content = function (data) {
            $('#outofthebox_upload_box_shortcode').val(data);
            window.modal_action.close();
            $('#wpcp-modal-action.OutoftheBox').remove();
        };
    }

    $('input#_uploadable').on('change', function () {
        var is_uploadable = $('input#_uploadable:checked').length;
        $('.show_if_uploadable').hide();
        $('.hide_if_uploadable').hide();
        if (is_uploadable) {
            $('.hide_if_uploadable').hide();
        }
        if (is_uploadable) {
            $('.show_if_uploadable').show();
        }
    });
    $('input#_uploadable').trigger('change');

    $('input#outofthebox_upload_box').on('change', function () {
        var outofthebox_upload_box = $('input#outofthebox_upload_box:checked').length;
        $('.show_if_outofthebox_upload_box').hide();
        if (outofthebox_upload_box) {
            $('.show_if_outofthebox_upload_box').show();
        }
    });
    $('input#outofthebox_upload_box').trigger('change');

    /* Shortcode Generator Popup */
    $('.wpcp-insert-dropbox-shortcode').on('click', function (e) {
        let shortcode = $('#outofthebox_upload_box_shortcode').val();

        openShortcodeBuilder(shortcode);
    });

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
                '?action=outofthebox-getpopup&type=modules&foruploadfield=1&callback=wpcp_oftb_wc_add_content&' +
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
});
