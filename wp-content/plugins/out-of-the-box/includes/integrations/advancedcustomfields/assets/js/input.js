(function ($) {
    class ACF_OutoftheBox_Field {
        data = {};
        $input = null;
        $table;
        $selector;
        $module;
        $add_button;
        max_items;

        eventListener = (event) => {
            this.callback_handler(event);
        };

        constructor($field) {
            this.$input = $field.find('input[data-name="id"]');
            this.$table = $field.find('.wpcp-acf-items-table');

            $('#wpcp-modal-acf-selector-dropbox .wpcp-module').parents('.wpcp-dialog').not(':first').remove(); // Remove duplicates in Repeater fields
            this.$selector = jQuery('#wpcp-modal-acf-selector-dropbox');

            this.$module = this.$selector.find('.wpcp-module');
            this.$add_button = $field.find('.wpcp-acf-add-item');
            this.max_items = this.$add_button.data('max-items');

            // place wpcp container bottom body
            this.$selector.parent().appendTo('body');

            this.read_data();

            this._init_buttons($field);

            this._initSelectAdded();
        }

        read_data() {
            try {
                this.data = JSON.parse(this.$input.val());
            } catch (e) {
                this.data = {};
            }
            this.render_entries();
        }

        save_data() {
            this.$input.val(JSON.stringify(this.data));
            this.render_entries();
            this.update_add_button();
        }

        _init_buttons($field) {
            let self = this;
            this.$add_button.on('click', function (e) {
                self.openSelector();
                e.preventDefault();
            });

            $($field).on('click', '.wpcp-acf-remove-item', function (e) {
                var row = $(this).parents('tr');
                delete self.data[row.data('entry-id')];
                self.save_data();
            });

            $('.wpcp-dialog-close').on('click', function (e) {
                self.closeSelector();
            });

            self.initAddButton();

            self.$selector.find('.wpcp-acf-dialog-entry-select').on('click', function (e) {
                const account_id = self.$module.attr('data-account-id');
                const entries_data = self.$module
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
        }

        update_add_button() {
            // Disable/hide Add button if needed
            this.$add_button.prop('disabled', false);
            if (self.max_items > 0 && Object.entries(this.data).length >= this.max_items) {
                this.$add_button.prop('disabled', true);
            }
        }

        openSelector() {
            window.addEventListener('message', this.eventListener);

            // Refresh File List to render the selected items
            if (this.$module.hasClass('wpcp-thumb-view') || this.$module.hasClass('wpcp-list-view')) {
                if (this.$module.find('.skeleton-entry').length === 0) {
                    this.$module.data('cp-OutoftheBox')._getFileList({});
                }
            }

            this.$selector.fadeIn();
            this.$selector.find('.wpcp-acf-dialog-entry-select').prop('disabled', 'disabled');
        }

        closeSelector() {
            window.removeEventListener('message', this.eventListener);
            this.$selector.fadeOut();
        }

        /**
         * Enable & Disable add button based on selection of entries
         */
        initAddButton() {
            let self = this;

            self.$module.on(
                {
                    change: function (e) {
                        if (self.$module.find("input[name='selected-files[]']:checked").length) {
                            self.$selector.find('.wpcp-acf-dialog-entry-select').prop('disabled', '');
                        } else {
                            self.$selector.find('.wpcp-acf-dialog-entry-select').prop('disabled', 'disabled');
                        }
                    },
                },
                "input[name='selected-files[]']"
            );
        }

        /**
         * Mark already added file in the File Browser moulde
         */
        _initSelectAdded() {
            let self = this;

            self.$module.on('wpcp-content-loaded', function (e, plugin) {
                plugin.element
                    .find("input[name='selected-files[]']:checked")
                    .prop('checked', false)
                    .removeClass('is-selected');

                for (const [key, entry] of Object.entries(self.data)) {
                    // Show the entry as selected
                    $(
                        '.wpcp-module[data-account-id="' +
                            entry.account_id +
                            '"] .entry[data-id="' +
                            entry.entry_id +
                            '"]'
                    ).addClass('is-selected');
                }
            });
        }

        render_entries() {
            var $tbody = this.$table.find('tbody');
            $tbody.empty();

            if (Object.entries(this.data).length === 0) {
                $tbody.append('<tr><td></td><td>No files added</td><td></td><td></td></tr>');
                return;
            }

            var data_i = 1;
            for (const [key, entry] of Object.entries(this.data)) {
                var style = this.max_items > 0 && data_i > this.max_items ? 'style="background:lightcoral"' : '';

                $tbody.append(
                    '<tr data-entry-id="' +
                        key +
                        '" data-account-id="' +
                        entry.account_id +
                        '" ' +
                        style +
                        '><td>' +
                        (entry.icon_url ? '<img src="' + entry.icon_url + '" style="height:18px; width:18px;"/>' : '') +
                        '</td><td>' +
                        entry.name +
                        (entry.size ? ' (' + entry.size + ')' : '') +
                        '</td><td style="max-width:300px;overflow:hidden;white-space:nowrap;text-overflow: ellipsis;">' +
                        entry.entry_id +
                        '</td><td>' +
                        (entry.direct_url
                            ? '<a href="' +
                              entry.direct_url +
                              '" target="_blank" class="button button-secondary button-small">View</a>&nbsp;'
                            : '') +
                        (entry.download_url
                            ? '<a href="' +
                              entry.download_url +
                              '" target="_blank" class="button button-secondary button-small">Download</a>&nbsp;'
                            : '') +
                        '<a href="#" class="wpcp-acf-remove-item button button-secondary button-small">&#10006;</a></td></tr>'
                );

                data_i++;
            }

            this.update_add_button();
        }

        callback_handler(event) {
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

            event.data.entries.forEach(function (entry, index, array) {
                if (self.max_items > 0 && Object.entries(self.data).length >= self.max_items) {
                    return;
                }

                self.data[entry.entry_id] = {
                    account_id: entry.account_id,
                    entry_id: entry.entry_id,
                    name: entry.entry_name,
                    size: '',
                    direct_url: '',
                    download_url: '',
                    shortlived_download_url: '',
                    shared_url: '',
                    embed_url: '',
                    thumbnail_url: '',
                    icon_url: '',
                };

                // Show the entry as selected
                $(
                    '.wpcp-module[data-account-id="' + entry.account_id + '"] .entry[data-id="' + entry.entry_id + '"]'
                ).addClass('is-selected');

                files_added.push(entry.entry_name);
            });

            this.save_data();

            if (files_added.length > 0) {
                window.showNotification(true, '<strong>' + files_added.join(', ') + '</strong>');
            } else {
                window.showNotification(false, '<strong>Not all selected items were added</strong>');
            }
        }
    }

    function initialize_field($field) {
        new ACF_OutoftheBox_Field($field);
    }

    if (typeof acf.add_action !== 'undefined') {
        acf.add_action('ready_field/type=OutoftheBox_Field', initialize_field);
        acf.add_action('append_field/type=OutoftheBox_Field', initialize_field);
    } else {
        $(document).on('acf/setup_fields', function (e, postbox) {
            // find all relevant fields
            $(postbox)
                .find('.field[data-field_type="OutoftheBox_Field"]')
                .each(function () {
                    // initialize
                    initialize_field($(this));
                });
        });
    }
})(jQuery);
