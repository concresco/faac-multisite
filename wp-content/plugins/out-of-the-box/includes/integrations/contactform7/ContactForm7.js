jQuery(document).ready(function ($) {
    'use strict';

    // Define variables for elements
    let $outofthebox_module_id = $('#tag-generator-panel-outofthebox-module-id');
    let $outofthebox_selector_button = $('button#tag-generator-panel-outofthebox-module-selector');
    let $outofthebox_dialog = $('dialog#tag-generator-panel-outofthebox-module-selector-dialog');
    let $outofthebox_dialog_close_button = $('button.tag-generator-panel-outofthebox-module-close');

    // Event handler for selector button click
    $outofthebox_selector_button.on('click', function (e) {
        e.preventDefault();

        let iFrame = $outofthebox_dialog.find('iframe');
        let moduleBuilderUrl = iFrame.attr('data-src');

        // Append module ID or default shortcode to URL
        if ($outofthebox_module_id.val() !== '') {
            moduleBuilderUrl += '&module=' + $outofthebox_module_id.val();
        } else {
            moduleBuilderUrl +=
                '&shortcode=' +
                WPCP_shortcodeEncode(
                    '[outofthebox mode="upload" viewrole="all" upload="1" uploadrole="all" upload_auto_start="0" userfolders="auto" viewuserfoldersrole="none"]'
                );
        }

        iFrame.attr('src', moduleBuilderUrl);

        // Show the dialog
        $outofthebox_dialog[0].showModal();
    });

    // Event handler for dialog close button click
    $outofthebox_dialog_close_button.on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        // Close the dialog
        $outofthebox_dialog[0].close();
    });

    // Callback function to add shortcode to CF7 input field
    if (typeof window.wpcp_oftb_cf7_add_content === 'undefined') {
        window.wpcp_oftb_cf7_add_content = function (data) {
            let moduleId = data.match(/module="(\d+)"/)[1];

            $outofthebox_module_id.val(moduleId);

            // Trigger change and keyup events. Use vanilla JS to trigger events, jQuery events are not working
            let event = new Event('change', { bubbles: true });
            $outofthebox_module_id[0].dispatchEvent(event);

            event = new Event('keyup', { bubbles: true });
            $outofthebox_module_id[0].dispatchEvent(event);

            // Close the dialog
            $outofthebox_dialog[0].close();
        };
    }
});
