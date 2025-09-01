(function ($) {
    'use strict';
    $.widget('cp.WPCP_OutoftheBox_Proofing', {
        options: {
            listtoken: null,
        },

        /**
         * Initializes the widget and sets up the necessary options and elements.
         */
        _create: function () {
            this._initializeOptions();
            this._initializeData();
            this._initiate();
        },

        /**
         * Initializes the options for the widget.
         */
        _initializeOptions: function () {
            this.options.topContainer = this.element.parent();
            this.options.mainContainer = this.options.main.element;
            this.options.loadingContainer = this.element.find('.loading');
            this.options.statusBar = this.element.parent().find('.wpcp-proofing-status-bar');

            // Detect if the device supports touch
            this.options.supportTouch =
                ('ontouchstart' in window &&
                    /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) ||
                navigator.maxTouchPoints > 1;

            // Set the module IDs
            this.options.listtoken = this.element.attr('data-token');
            this.options.account_id = this.element.attr('data-account-id');
            this.options.drive_id = this.element.attr('data-drive-id');

            // Proofing settings
            this.options.max_items = parseInt(this.options.mainContainer.attr('data-max-items'));
            console.log(this.options.max_items);
        },

        /**
         * Initializes the data structure for proofing.
         */
        _initializeData: function () {
            const urlParams = new URLSearchParams(window.location.search);

            this.data = {
                ident: urlParams.get('ident'),
                user: null,
                items: [],
                approved: true,
                labels: [],
            };
        },

        /**
         * Destroys the widget instance.
         */
        _destroy: function () {
            return this._super();
        },

        /**
         * Sets a specific option for the widget.
         * @param {string} key - The option key.
         * @param {*} value - The option value.
         */
        _setOption: function (key, value) {
            this._super(key, value);
        },

        /**
         * Initiates the proofing process.
         */
        _initiate: function () {
            this._initProofing();
        },

        /**
         * Initializes the proofing functionality.
         */
        _initProofing: function () {
            this.getSelection();
            this.setListeners();
            this.setEvents();
            this.options.statusBar.fadeIn();
        },

        /**
         * Sets up event listeners for the widget.
         */
        setListeners: function () {
            const self = this;

            self.options.mainContainer.on('wpcp-content-loaded', (e, plugin) => {
                plugin.element
                    .find("input[name='selected-files[]']:checked")
                    .prop('checked', false)
                    .removeClass('is-selected');

                self.updateRender();
            });
        },

        /**
         * Sets up various event handlers for the widget.
         */
        setEvents: function () {
            const self = this;

            // Add Label Menu
            $(document).on('click', '.entry-label-button', (e) => {
                e.stopPropagation();
                self._showLabelMenu(e);
            });

            // Add Label Event
            $(document).on('click', '.entry_action_add_label', function () {
                const entryId = $(this).parents('[data-id]').attr('data-id');
                const labelId = $(this).parents('[data-label-id]').attr('data-label-id');

                if (labelId === 'none') {
                    self.deleteLabel(entryId);
                } else {
                    self.addLabel(entryId, labelId);
                }

                tippy.hideAll();
                self.updateRender();
                self.saveSelection();
            });

            // Selection Events
            $(document).on('change', ".entry_checkbox input[name='selected-files[]']", function () {
                const entryId = $(this).parents('[data-id]').attr('data-id');

                if ($(this).is(':checked')) {
                    self.addSelected(entryId);
                } else {
                    self.deleteSelected(entryId);
                }

                self.updateRender();
                self.saveSelection();
            });

            // Filter Events
            $(self.options.statusBar).on('click', '.wpcp-proofing-filter-selected', () => {
                self.options.topContainer.addClass('filter-selected').removeClass('filter-unselected');
            });

            $(self.options.statusBar).on('click', '.wpcp-proofing-filter-unselected', () => {
                self.options.topContainer.addClass('filter-unselected').removeClass('filter-selected');
            });

            $(self.options.statusBar).on('click', '.wpcp-proofing-filter-reset', () => {
                self.options.topContainer.removeClass('filter-unselected').removeClass('filter-selected');
            });

            // Save Button
            $(self.options.statusBar).on('click', '.wpcp-proofing-save', () => {
                self.saveSelection();
            });

            // Pre Send Button
            $(self.options.statusBar).on('click', '.wpcp-proofing-pre-send', () => {
                if (self.getSelected().length < 1) {
                    return;
                }
                self.openDialog('#wpcp-proofing-pre-send-view');
            });
        },

        /**
         * Shows the label menu.
         * @param {Event} e - The event object.
         */
        _showLabelMenu: function (e) {
            const $button = $(e.target);
            const labelContainer = `
            <ul data-id='${$button.parents('[data-id]').attr('data-id')}'>
                ${this.data.labels
                    .map(
                        (label) =>
                            `<li data-label-id='${label.id}'><a class="entry_action_add_label"><i class='eva eva-bookmark eva-lg' style="color:${label.color}"></i>${label.title}</a></li>`
                    )
                    .join('')}
                </ul>
                `;

            tippy(e.target, {
                content: labelContainer,
                trigger: 'manual',
                allowHTML: true,
                placement: this.is_rtl ? 'bottom-end' : 'bottom-start',
                appendTo: $button.parents('.OutoftheBox').get(0),
                moveTransition: 'transform 0.2s ease-out',
                interactive: true,
                onShow: (instance) => {
                    const $entry = $(instance.reference).closest('.entry');
                    $entry.addClass('hasfocus').addClass('popupopen');
                },
                onHide: (instance) => {
                    $(instance.reference).closest('.entry').removeClass('hasfocus').removeClass('popupopen');
                },
            }).show();
        },

        /**
         * Retrieves an item by its entry ID.
         * @param {string} entryId - The entry ID.
         * @returns {Object|null} The item object or null if not found.
         */
        getItem: function (entryId) {
            return this.data.items.find((item) => item.id === entryId) || null;
        },

        /**
         * Adds an item by its entry ID.
         * @param {string} entryId - The entry ID.
         * @returns {Object} The item object.
         */
        addItem: function (entryId) {
            let item = this.getItem(entryId);
            if (item) {
                return item;
            }

            const entryName = this.element
                .find('.entry[data-id="' + entryId + '"] .entry-info-name [data-name]')
                .attr('data-name');

            item = {
                id: entryId,
                name: entryName,
                selected: null,
                label: null,
            };

            this.data.items.push(item);
            return item;
        },

        /**
         * Marks an item as selected by its entry ID.
         * @param {string} entryId - The entry ID.
         */
        addSelected: function (entryId) {
            if (this.data.approved) {
                return;
            }

            this.addItem(entryId);
            const item = this.data.items.find((item) => item.id === entryId);
            if (item) {
                item.selected = true;
            }
        },

        /**
         * Retrieves all selected items.
         * @returns {Object} An object containing all selected items.
         */
        getSelected: function () {
            return this.data.items.filter((item) => item.selected);
        },

        /**
         * Unmarks an item as selected by its entry ID.
         * @param {string} entryId - The entry ID.
         */
        deleteSelected: function (entryId) {
            if (this.data.approved) {
                return;
            }

            const item = this.data.items.find((item) => item.id === entryId);
            if (item) {
                item.selected = false;
            }
        },

        /**
         * Adds a label to an item by its entry ID and label ID.
         * @param {string} entryId - The entry ID.
         * @param {string} labelId - The label ID.
         */
        addLabel: function (entryId, labelId) {
            if (this.data.approved) {
                return;
            }

            this.addItem(entryId);
            const item = this.data.items.find((item) => item.id === entryId);
            if (item) {
                item.label = labelId;
            }
        },

        /**
         * Retrieves all labeled items.
         * @returns {Object} An object containing all labeled items.
         */
        getLabeled: function () {
            return this.data.items.filter((item) => item.label);
        },

        /**
         * Removes a label from an item by its entry ID.
         * @param {string} entryId - The entry ID.
         */
        deleteLabel: function (entryId) {
            if (this.data.approved) {
                return;
            }

            const item = this.data.items.find((item) => item.id === entryId);
            if (item) {
                item.label = null;
            }
        },

        /**
         * Renders the selected items in the UI.
         */
        renderSelected: function () {
            this.element.find('.is-selected').removeClass('is-selected');
            $('.OutoftheBox .entry_checkbox input[name="selected-files[]"]').prop('checked', false);

            this.getSelected().forEach((item) => {
                this.element.find('.entry[data-id="' + item.id + '"]').addClass('is-selected');
                $('.OutoftheBox [data-id="' + item.id + '"] .entry_checkbox input[name="selected-files[]"]').prop(
                    'checked',
                    true
                );
            });
        },

        /**
         * Renders the labels in the UI.
         */
        renderLabels: function () {
            this.element.find('.has-label').removeClass('has-label');
            $('.OutoftheBox .entry-label-button i').css('color', '');

            this.getLabeled().forEach((item) => {
                const label = this.data.labels.find((label) => label.id === item.label);
                if (label) {
                    this.element.find('.entry[data-id="' + item.id + '"]').addClass('has-label');
                    $('.OutoftheBox [data-id="' + item.id + '"] .entry-label-button i').css('color', label.color);
                }
            });
        },

        /**
         * Updates the UI to reflect the current state of selections and labels.
         */
        updateRender: function () {
            // Set Readonly if needed
            this.options.topContainer.removeClass('wpcp-proofing-readonly');
            if (this.data.approved) {
                this.options.topContainer.addClass('wpcp-proofing-readonly');
            }

            // Render Selection boxes and Labels
            this.renderSelected();
            this.renderLabels();

            // Update selection in status bar
            const numberOfItems = this.getSelected().length;
            let numberOfItemsStr = numberOfItems;

            if (this.options.max_items) {
                numberOfItemsStr += ' / ' + this.options.max_items;
            }

            numberOfItemsStr += ' ' + (numberOfItems === 1 ? this.options.str_item : this.options.str_items);

            this.options.statusBar.find('.wpcp-proofing-selected-num').text(numberOfItemsStr);

            // Disable/Enable Send button
            this.options.statusBar
                .find('.wpcp-proofing-pre-send')
                .prop(
                    'disabled',
                    numberOfItems < 1 || (this.options.max_items && numberOfItems > this.options.max_items)
                );
        },

        /**
         * Makes an Ajax request.
         * @param {Object} requestData - The specific data for the Ajax request.
         * @param {Function} successCallback - The callback function for a successful response.
         * @param {Function} errorCallback - The callback function for an error response.
         * @param {Function} completeCallback - The callback function for when the request is complete.
         */
        performAjaxRequest: function (requestData, successCallback, errorCallback, completeCallback) {
            const self = this;

            $.ajax({
                type: 'POST',
                url: self.options.main.options.ajax_url,
                data: {
                    action: 'outofthebox-proofing',
                    account_id: self.options.account_id,
                    drive_id: self.options.drive_id,
                    listtoken: self.options.listtoken,
                    ident: self.data.ident,
                    _ajax_nonce: self.options.main.options.proofing_nonce,
                    ...requestData,
                },
                beforeSend: function () {
                    self.options.statusBar.find('.wpcp-proofing-save').hide();
                    self.options.statusBar.find('.wpcp-proofing-saving').show();
                },
                success: successCallback,
                error: errorCallback,
                complete: function () {
                    self.options.statusBar.find('.wpcp-proofing-save').show();
                    self.options.statusBar.find('.wpcp-proofing-saving').hide();
                    if (completeCallback) completeCallback();
                },
                dataType: 'json',
            });
        },

        /**
         * Retrieves the current selection from the server.
         */
        getSelection: function () {
            const requestData = { type: 'get-selection' };

            this.performAjaxRequest(
                requestData,
                (response) => {
                    if (response.success === true) {
                        this.data = response.data;
                        this.updateRender();
                    }
                },
                () => {},
                () => {}
            );
        },

        /**
         * Saves the current selection to the server.
         */
        saveSelection: function () {
            const requestData = {
                type: 'save-selection',
                items: JSON.stringify(this.data.items),
            };

            this.performAjaxRequest(
                requestData,
                (response) => {
                    if (response.success === true) {
                        this.data = response.data;
                        this.updateRender();
                    }
                },
                () => {
                    this.openDialog('#wpcp-proofing-warning');
                },
                () => {}
            );
        },

        /**
         * Approves the current selection and sends it to the server.
         */
        approveSelection: function () {
            const requestData = {
                type: 'approve-selection',
                items: JSON.stringify(this.data.items),
                approved: true,
                message: $('#wpcp-proofing-approval-message').val(),
            };

            this.performAjaxRequest(
                requestData,
                (response) => {
                    if (response.success === true) {
                        this.data = response.data;
                        this.updateRender();
                    }

                    this.openDialog('#wpcp-proofing-approved-view');
                },
                () => {
                    this.openDialog('#wpcp-proofing-warning');
                },
                () => {}
            );
        },

        /**
         * Opens a dialog with the specified ID.
         * @param {string} dialogId - The ID of the dialog to open.
         */
        openDialog: function (dialogId) {
            const self = this;

            // Close any open modal windows
            $('#wpcp-modal-action').remove();

            // Build the Dialog
            const modalContent = self.options.topContainer.find(dialogId).html();
            const compiledContent = _.template(modalContent);
            const data = {
                content_skin: self.options.main.options.content_skin,
                selected: self.getSelected().length,
            };
            const modalDialog = compiledContent(data);

            $('body').append(modalDialog);

            // Set the button actions
            $('#wpcp-modal-action .wpcp-modal-submit-btn').on('click', function () {
                $(this).prop('disabled', true);
                $(this).html(
                    '<i class="eva eva-settings-outline eva-spin eva-fw"></i><span> ' +
                        self.options.str_processing +
                        '</span>'
                );

                self.approveSelection();
            });

            // Open the dialog
            window.modal_action = new RModal(document.getElementById('wpcp-modal-action'), {
                bodyClass: 'rmodal-open',
                dialogOpenClass: 'animated slideInDown',
                dialogCloseClass: 'animated slideOutUp',
                escapeClose: true,
                afterClose() {},
            });

            document.addEventListener(
                'keydown',
                function (ev) {
                    modal_action.keydown(ev);
                },
                false
            );
            modal_action.open();
            return false;
        },
    });
})(jQuery);
