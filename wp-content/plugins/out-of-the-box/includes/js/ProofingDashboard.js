(function ($) {
    ('use strict');

    let collection = {};

    // Templates for rendering items and filters
    const itemTemplate = _.template($('#wpcp-proof-collection-item-template').html());
    const filterPeopleTemplate = _.template($('#wpcp-proof-collection-filter-people-template').html());
    const filterLabelTemplate = _.template($('#wpcp-proof-collection-filter-label-template').html());
    const selectionItemTemplate = _.template($('#wpcp-proof-collection-selection-item-template').html());

    const $itemsView = $('#wpcp-proof-collection-items');
    const $statusBar = $('#wpcp-proof-collection-status-bar');

    let filteredItems = [];
    let selectedItems = [];

    // Initialize the collection by loading data and setting up event listeners
    function initCollection() {
        loadCollection();
        initEventListeners();
    }

    /** Load Collection */
    function loadCollection() {
        const moduleId = $('#post_ID').val();

        performAjaxRequest(
            {
                type: 'get-collection',
                module: moduleId,
            },
            function (response) {
                if (response.success === true) {
                    collection = response.data;
                    renderCollection();
                }
            }
        );
    }

    // Perform an AJAX request with the given action, data, and callback
    function performAjaxRequest(requestData, successCallback) {
        $.ajax({
            type: 'POST',
            url: WPCloudplugin_AdminUI_vars.ajax_url,
            data: {
                action: 'outofthebox-proofing-dashboard',
                _ajax_nonce: WPCloudplugin_AdminUI_vars.admin_nonce,
                ...requestData,
            },
            dataType: 'json',
            success: successCallback,
            error: function () {
                window.showNotification(false, WPCloudplugin_AdminUI_vars.str_ajax_request_failed);
            },
        });
    }

    // Render the collection by initializing filters and updating results
    function renderCollection() {
        if (collection.users.length > 0) {
            $itemsView.empty();
        }

        initializeFilters();
        updateResults([], []);

        $('#loading-overlay').fadeOut(1000, function () {
            $(this).remove();
        });
    }

    // Initialize filters dynamically
    function initializeFilters() {
        // Create People filters

        const peopleContainer = $('#wpcp-proof-collection-filter-people');
        if (collection.users.length > 0) {
            peopleContainer.empty();
        }
        collection.users.forEach((user) => {
            peopleContainer.append(filterPeopleTemplate(user));
        });

        // Create Labels filters
        const labelsContainer = $('#wpcp-proof-collection-filter-labels');
        if (collection.labels.length > 0) {
            labelsContainer.empty();
        }
        collection.labels.forEach((label) => {
            labelsContainer.append(filterLabelTemplate(label));
        });

        // Create Selection items
        const selectionsContainer = $('#wpcp-proof-collection-selections');
        if (collection.users.length > 0) {
            selectionsContainer.empty();
        }
        collection.users.forEach((user) => {
            selectionsContainer.append(selectionItemTemplate(user));
        });
    }

    // Update the filtered results based on selected users and labels
    function updateResults(selectedUsers, selectedLabelsIds) {
        filteredItems = collection.items.filter((item) => {
            const userMatch =
                selectedUsers.length === 0 ||
                selectedUsers.some((user) => item.users.some((itemUser) => String(itemUser) === String(user)));
            const labelMatch =
                selectedLabelsIds.length === 0 || selectedLabelsIds.some((label_id) => item.labels.includes(label_id));
            return userMatch && labelMatch;
        });

        renderItems();
        updateCounts();
        updateItemSelection();
    }

    // Reset all filters and update results
    function resetFilters() {
        updateResults([], []);
        $('.wpcp-proof-filter:checked').prop('checked', false);
    }

    // Update the counts for filters
    function updateCounts() {
        const userCounts = {};
        const labelCounts = {};

        collection.users.forEach((user) => (userCounts[user.id] = 0));
        collection.labels.forEach((label) => (labelCounts[label.id] = 0));

        filteredItems.forEach((item) => {
            item.users.forEach((user) => (userCounts[user] = (userCounts[user] || 0) + 1));
            item.labels.forEach((label) => (labelCounts[label] = (labelCounts[label] || 0) + 1));
        });

        $('.wpcp-proof-filter-list[data-user-id]').each(function () {
            const userId = $(this).find('input').val();
            $(this)
                .find(`.counter`)
                .text('(' + (userCounts[userId] || 0) + ')');
        });

        $('.wpcp-proof-filter-list[data-label-id]').each(function () {
            const labelId = $(this).find('input').val();
            $(this)
                .find(`.counter`)
                .text('(' + (labelCounts[labelId] || 0) + ')');
        });

        const $selectButton = $('.wpcp-proof-collection-select-assets');
        $selectButton.find('span').text($selectButton.attr('data-button-text').replace('%s', filteredItems.length));
    }

    // Update the selection of items
    function updateItemSelection() {
        selectedItems = [];
        $('.wpcp-proof-collection-item.wpcp-selected').not(':visible').removeClass('wpcp-selected');
        $('.wpcp-proof-collection-item.wpcp-selected:visible').each(function () {
            selectedItems.push($(this).attr('id'));
        });

        if (selectedItems.length === 0) {
            $statusBar.fadeOut();
            return;
        }

        $statusBar.find('.wpcp-proof-collection-status-bar-selected-assets').text(selectedItems.length);
        $statusBar.fadeIn();

        // Collect file names
        const filenames = $('.wpcp-proof-collection-item.wpcp-selected')
            .map(function () {
                return $(this).attr('data-filename');
            })
            .get()
            .join(',');

        $('#wpcp-modal-export-filenames').val(filenames);
    }

    // Render the items based on the filtered results
    function renderItems() {
        // Iterate over all items and toggle visibility based on their presence in filteredItems
        collection.items.forEach((item) => {
            const existingItem = $itemsView.find(`[data-id="${item.id}"]`);

            if (filteredItems.includes(item)) {
                if (existingItem.length) {
                    existingItem.show(); // Show the item if it's in the filtered list
                } else {
                    $itemsView.append(
                        itemTemplate({
                            item: item,
                            labels: collection.labels.filter(function (label) {
                                return item.labels.includes(label.id);
                            }),
                        })
                    );
                }
            } else {
                if (existingItem.length) {
                    existingItem.hide(); // Hide the item if it's not in the filtered list
                }
            }
        });
    }

    // Initialize event listeners for various user interactions
    function initEventListeners() {
        // Filter Events
        $('#wpcp').on(
            {
                change: function (e) {
                    const selectedUsers = $('.wpcp-proof-filter-user:checked')
                        .map(function () {
                            return $(this).val();
                        })
                        .get();

                    const selectedLabels = $('.wpcp-proof-filter-label:checked')
                        .map(function () {
                            return $(this).val();
                        })
                        .get();

                    updateResults(selectedUsers, selectedLabels);
                },
            },
            '.wpcp-proof-filter'
        );

        // Reset Filter
        $('#wpcp').on(
            {
                click: function (e) {
                    $('.wpcp-proof-filter').prop('checked', false);
                    $('.wpcp-proof-collection-item').removeClass('wpcp-selected');
                    updateResults([], []);
                },
            },
            '.wpcp-proof-collection-filter-reset'
        );

        // Selecting assets by mouse
        var scrollSpeed = 20; // Adjust scroll speed as needed
        var scrollThreshold = 50; // Distance from top/bottom of viewport to start scrolling
        var scrollDownIntervalId, scrollUpIntervalId;
        $('#wpcp').selectable({
            classes: {
                'ui-selectee': 'wpcp-selectee',
                'ui-selecting': 'wpcp-selecting',
                'ui-selected': 'wpcp-selected',
                'ui-selectable-helper': 'wpcp wpcp-selectable-helper',
            },
            filter: 'li.wpcp-proof-collection-item',
            start: function (event, ui) {
                var isScrolling = false;

                function smoothScroll(direction) {
                    var offset = $(window).scrollTop();
                    var windowHeight = $(window).height();
                    var docHeight = $(document).height();

                    if (direction === 'down') {
                        if (offset < docHeight - windowHeight) {
                            $(window).scrollTop(offset + scrollSpeed);
                        }
                    } else if (direction === 'up') {
                        if (offset > 0) {
                            $(window).scrollTop(offset - scrollSpeed);
                        }
                    }
                }

                $(window).on('mousemove', function (e) {
                    var offset = $(window).scrollTop();
                    var windowHeight = $(window).height();
                    var cursorPosition = e.clientY; // The Y position of the cursor relative to the viewport

                    // Scroll Down if the cursor is near the bottom
                    if (cursorPosition > windowHeight - scrollThreshold) {
                        if (!isScrolling) {
                            isScrolling = true;
                            scrollDownIntervalId = requestAnimationFrame(function scrollDown() {
                                smoothScroll('down');
                                if (cursorPosition > windowHeight - scrollThreshold) {
                                    scrollDownIntervalId = requestAnimationFrame(scrollDown);
                                }
                            });
                        }
                    }
                    // Scroll Up if the cursor is near the top
                    else if (cursorPosition < scrollThreshold) {
                        if (!isScrolling) {
                            isScrolling = true;
                            scrollUpIntervalId = requestAnimationFrame(function scrollUp() {
                                smoothScroll('up');
                                if (cursorPosition < scrollThreshold) {
                                    scrollUpIntervalId = requestAnimationFrame(scrollUp);
                                }
                            });
                        }
                    } else {
                        isScrolling = false;
                        cancelAnimationFrame(scrollDownIntervalId);
                        cancelAnimationFrame(scrollUpIntervalId);
                    }
                });
            },
            stop: function (event, ui) {
                $(window).off('mousemove');
                cancelAnimationFrame(scrollDownIntervalId);
                cancelAnimationFrame(scrollUpIntervalId);
                updateItemSelection();
            },
        });

        // Selecting single asset
        $('#wpcp').on(
            {
                click: function (e) {
                    $(this).toggleClass('wpcp-selected');
                    updateItemSelection();
                },
            },
            '.wpcp-proof-collection-item'
        );

        // Selecting all assets
        $('#wpcp').on(
            {
                click: function (e) {
                    $('.wpcp-proof-collection-item.wpcp-selected').removeClass('wpcp-selected');
                    $('.wpcp-proof-collection-item:visible').addClass('wpcp-selected');
                    updateItemSelection();
                },
            },
            '.wpcp-proof-collection-select-assets'
        );

        // Deselecting all assets
        $('#wpcp').on(
            {
                click: function (e) {
                    $('.wpcp-proof-collection-item').removeClass('wpcp-selected');
                    updateItemSelection();
                },
            },
            '.wpcp-proof-collection-deselect-assets'
        );

        // Add New user to collection
        $('#wpcp').on(
            {
                click: function (e) {
                    const moduleId = $('#post_ID').val();

                    // Perform AJAX request to delete the selection
                    performAjaxRequest(
                        {
                            type: 'add_user_selection',
                            module: moduleId,
                            email: $('#wpcp-proof-collection-add-user-email').val(),
                        },
                        function (response) {
                            if (response.success === true) {
                                window.showNotification(
                                    true,
                                    WPCloudplugin_AdminUI_vars.str_selection_add_user_success
                                );
                                window.location.reload();
                            } else {
                                window.showNotification(
                                    false,
                                    WPCloudplugin_AdminUI_vars.str_selection_add_user_failed
                                );
                            }
                        }
                    );
                },
            },
            '.wpcp-proof-collection-add-user'
        );

        // Link Buttons
        $('#wpcp').on(
            {
                click: function (e) {
                    e.stopPropagation();

                    return true;
                },
            },
            '.wpcp-open-link'
        );

        /**
         * Event handler for deleting a Selection.
         */
        $('#wpcp').on(
            {
                click: function (e) {
                    const moduleId = $('#post_ID').val();
                    const $selectionBox = $(this).closest('[data-selection-ident]');
                    const selectionIdent = $selectionBox.attr('data-selection-ident');

                    // Perform AJAX request to delete the selection
                    performAjaxRequest(
                        {
                            type: 'delete-selection',
                            module: moduleId,
                            ident: selectionIdent,
                        },
                        function (response) {
                            if (response.success === true) {
                                window.showNotification(true, WPCloudplugin_AdminUI_vars.str_selection_deleted_success);
                                window.location.reload();
                            } else {
                                window.showNotification(false, WPCloudplugin_AdminUI_vars.str_selection_deleted_failed);
                            }
                        }
                    );
                },
            },
            '.wpcp-proof-collection-delete-selection'
        );

        /**
         * Event handler for toggling approval status.
         */
        $('#wpcp').on(
            {
                change: function (e) {
                    const moduleId = $('#post_ID').val();
                    const $selectionBox = $(this).closest('[data-selection-ident]');
                    const selectionIdent = $selectionBox.attr('data-selection-ident');
                    const $checkbox = $(this);

                    // UI update
                    const $button = $(this).parent('button'),
                        $button_container = $button.children('.wpcp-input-checkbox-button-container'),
                        $button_off = $button_container.children('.wpcp-input-checkbox-button-off'),
                        $button_on = $button_container.children('.wpcp-input-checkbox-button-on'),
                        is_checked = $(this).prop('checked');

                    if (is_checked) {
                        $button.removeClass('bg-gray-200').addClass('bg-brand-color-900');
                        $button_container.removeClass('translate-x-0').addClass('translate-x-4');
                        $button_off
                            .removeClass('opacity-100 ease-in duration-200')
                            .addClass('opacity-0 ease-out duration-100');
                        $button_on
                            .removeClass('opacity-0 ease-out duration-100')
                            .addClass('opacity-100 ease-in duration-200');
                    } else {
                        $button.addClass('bg-gray-200').removeClass('bg-brand-color-900');
                        $button_container.addClass('translate-x-0').removeClass('translate-x-4');
                        $button_off
                            .addClass('opacity-100 ease-in duration-200')
                            .removeClass('opacity-0 ease-out duration-100');
                        $button_on
                            .addClass('opacity-0 ease-out duration-100')
                            .removeClass('opacity-100 ease-in duration-200');
                    }

                    // Perform AJAX request to set approval status
                    performAjaxRequest(
                        {
                            type: 'set-approval',
                            module: moduleId,
                            ident: selectionIdent,
                            approved: is_checked,
                        },
                        function (response) {
                            if (response.success === true) {
                                window.showNotification(
                                    true,
                                    response.data.approved
                                        ? WPCloudplugin_AdminUI_vars.str_selection_set_approval_approved_success
                                        : WPCloudplugin_AdminUI_vars.str_selection_set_approval_open_success
                                );
                            } else {
                                window.showNotification(
                                    false,
                                    WPCloudplugin_AdminUI_vars.str_selection_set_approval_failed
                                );
                                $checkbox.prop('checked', !$checkbox.prop('checked'));
                            }
                        }
                    );
                },
            },
            '.wpcp-proof-collection-toggle-approved input[type="checkbox"]'
        );

        // Toggle approval status by clicking the container
        $('#wpcp').on(
            {
                click: function (e) {
                    const $checkbox = $(this).find('input[type="checkbox"]');
                    $checkbox.prop('checked', !$checkbox.prop('checked')).trigger('change');
                },
            },
            '.wpcp-proof-collection-toggle-approved'
        );

        $('#wpcp').on(
            {
                click: function (e) {
                    $('#wpcp-modal-export').fadeOut();
                },
            },
            '.wpcp-proof-copy-filenames'
        );

        /**
         * Event handler for copy input field values to clipboard.
         */
        new ClipboardJS('#wpcp .wpcp-copy-to-clipboard', {
            text: function (trigger) {
                return $($(trigger).attr('data-input')).val();
            },
        })
            .on('success', function (e) {
                window.showNotification(true, WPCloudplugin_AdminUI_vars.str_copied_to_clipboard);
                e.clearSelection();
            })
            .on('error', function () {
                window.prompt('Copy to clipboard: Ctrl+C, Enter', $($(this).attr('data-input')).val());
            });
    }

    // Initialize the collection on document ready
    initCollection();
})(jQuery);
