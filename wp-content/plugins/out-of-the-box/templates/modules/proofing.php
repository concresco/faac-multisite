<?php

namespace TheLion\OutoftheBox;

defined('ABSPATH') || exit;

?>
<div class="wpcp-proofing-status-bar" style="display: none;">
    <div class="wpcp-proofing-display-filter">
        <a class="wpcp-proofing-filter-selected">
            <span class="wpcp-proofing-filter-icon eva eva-checkmark"></span>
            <span class="wpcp-proofing-filter-label"><?php \esc_html_e('Selected', 'wpcloudplugins'); ?></span>
        </a>
        <a class="wpcp-proofing-filter-unselected">
            <span class="wpcp-proofing-filter-icon eva eva-close"></span>
            <span class="wpcp-proofing-filter-label"><?php \esc_html_e('Unselected', 'wpcloudplugins'); ?></span>
        </a>
        <a class="wpcp-proofing-filter-reset"><i class="eva eva-close ev-lg"></i><span><?php \esc_html_e('Reset filters', 'wpcloudplugins'); ?></span></a>
    </div>
    <div class="wpcp-proofing-selection-count">
        <i class="eva eva-hash"></i>
        <span class="wpcp-proofing-selected-num">0 <?php \esc_html_e('Items', 'wpcloudplugins'); ?></span>
    </div>

    <div class="wpcp-proofing-collection-actions">
        <span class="wpcp-proofing-save"><span><?php \esc_html_e('Saved', 'wpcloudplugins'); ?></span> <i class="eva eva-checkmark"></i></span>
        <span class="wpcp-proofing-saving"><i class="eva eva-refresh eva-spin eva-spin-center"></i></span>
        <button class="wpcp-proofing-pre-send"><?php \esc_html_e('Send', 'wpcloudplugins'); ?><span> <?php \esc_html_e('selection', 'wpcloudplugins'); ?></span>…</button>

    </div>
</div>

<script id="wpcp-proofing-pre-send-view" type="text/template">
    <div id="wpcp-modal-action" class="OutoftheBox wpcp wpcp-modal wpcp-proofing-container <%= content_skin %>">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-content">
                    <div class="wpcp-modal-header" tabindex="0">
                        <h2><i class="eva eva-checkmark-circle"></i> <?php esc_html_e('Approve Collection', 'wpcloudplugins'); ?></h2>
                        <a tabindex="0" class="close-button" title="<?php esc_attr_e('Close', 'wpcloudplugins'); ?>" onclick="window.modal_action.close();"><i class="eva eva-close eva-lg" aria-hidden="true"></i></a>
                    </div>
                    <div class="wpcp-modal-body" tabindex="0">
                        <div class="wpcp-proofing-approval-form">
                            <p>
                                <label for="wpcp-proofing_approval_message"><?php \esc_html_e('Anything else you want us to know?', 'wpcloudplugins'); ?> </label>
                                <textarea name="wpcp-proofing-approval-form[wpcp-proofing-approval-message]" id="wpcp-proofing-approval-message" placeholder="" rows="8"></textarea>
                            </p>
                        </div>
                        <p>
                            <strong><?php \esc_html_e('You are about to approve this collection.', 'wpcloudplugins'); ?></strong><br />
                            <?php \esc_html_e("Please note, that you won't be able to make changes to your selection after that.", 'wpcloudplugins'); ?>
                        </p>
                    </div>
                    <div class="wpcp-modal-footer">
                        <div class="wpcp-modal-buttons">
                            <button class="button wpcp-modal-cancel-btn secondary" data-action="cancel" type="button" onclick="window.modal_action.close();" title="<?php esc_attr_e('Close', 'wpcloudplugins'); ?>">
                                <?php esc_html_e('Close', 'wpcloudplugins'); ?>
                            </button>
                            <button class="button wpcp-modal-submit-btn" data-action="submit" type="button" title="<?php esc_attr_e('Approve selection', 'wpcloudplugins'); ?>">
                                <?php esc_html_e('Approve', 'wpcloudplugins'); ?> (<%= selected %>)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</script>

<script id="wpcp-proofing-approved-view" type="text/template">
    <div id="wpcp-modal-action" class="OutoftheBox wpcp wpcp-modal wpcp-proofing-container <%= content_skin %>">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-content">
                    <div class="wpcp-modal-header" tabindex="0">
                        <h2><i class="eva eva-checkmark-circle"></i> <?php esc_html_e('Collection Approved', 'wpcloudplugins'); ?></h2>
                        <a tabindex="0" class="close-button" title="<?php esc_attr_e('Close', 'wpcloudplugins'); ?>" onclick="window.modal_action.close();"><i class="eva eva-close eva-lg" aria-hidden="true"></i></a>
                    </div>
                    <div class="wpcp-modal-body" tabindex="0">
                        <p><strong><?php esc_html_e('Thank you!', 'wpcloudplugins'); ?></strong> <?php esc_html_e('The collection has been approved and a notification has been sent.', 'wpcloudplugins'); ?></p>
                        <p><?php esc_html_e('You can now close this browser window.', 'wpcloudplugins'); ?></p>
                    </div>
                    <div class="wpcp-modal-footer">
                        <div class="wpcp-modal-buttons">
                            <button class="button wpcp-modal-cancel-btn secondary" data-action="cancel" type="button" onclick="window.modal_action.close();" title="<?php esc_attr_e('Close', 'wpcloudplugins'); ?>">
                                <?php esc_html_e('Close', 'wpcloudplugins'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</script>

<script id="wpcp-proofing-warning" type="text/template">
    <div id="wpcp-modal-action" class="OutoftheBox wpcp wpcp-modal wpcp-proofing-container <%= content_skin %>">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-content">
                    <div class="wpcp-modal-header" tabindex="0">
                        <h2><i class="eva eva-checkmark-circle"></i> <?php esc_html_e('Collection Approved', 'wpcloudplugins'); ?></h2>
                        <a tabindex="0" class="close-button" title="<?php esc_attr_e('Close', 'wpcloudplugins'); ?>" onclick="window.modal_action.close();"><i class="eva eva-close eva-lg" aria-hidden="true"></i></a>
                    </div>
                    <div class="wpcp-modal-body" tabindex="0">
                        <p><strong><?php esc_html_e('Ai! Something has gone wrong and the selection has not yet been saved. Please try again, and if the problem persists, please contact us.', 'wpcloudplugins'); ?></p>
                    </div>
                    <div class="wpcp-modal-footer">
                        <div class="wpcp-modal-buttons">
                            <button class="button wpcp-modal-cancel-btn secondary" data-action="cancel" type="button" onclick="window.modal_action.close();" title="<?php esc_attr_e('Close', 'wpcloudplugins'); ?>">
                                <?php esc_html_e('Close', 'wpcloudplugins'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</script>