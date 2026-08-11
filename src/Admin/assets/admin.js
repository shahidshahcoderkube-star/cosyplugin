/**
 * admin.js
 * Central admin JavaScript controller for the Cosy Appointments plugin.
 *
 * Organized using the Object-Literal Namespace pattern inside a single
 * secure jQuery ready wrapper to prevent global scope pollution and
 * avoid duplicate event handler conflicts.
 *
 * Modules:
 *  - CosyMediaAdmin   : Handles approve/reject actions for provider media uploads.
 *  - CosyOrdersAdmin  : Handles the order details modal (open, populate, close).
 */
jQuery(document).ready(function ($) {
    'use strict';

    // =========================================================================
    // MODULE: CosyAlert
    // Premium custom confirm dialog and toast notification system.
    // Replaces all native browser confirm() and alert() calls.
    // =========================================================================
    const CosyAlert = {

        /**
         * confirm
         * Shows a beautiful confirm dialog.
         * @param {object} opts - { title, message, confirmText, cancelText, type, onConfirm }
         */
        confirm: function (opts) {
            const defaults = {
                title:       opts.title       || 'Are you sure?',
                message:     opts.message     || 'This action cannot be undone.',
                confirmText: opts.confirmText || 'Yes, proceed',
                cancelText:  opts.cancelText  || 'Cancel',
                type:        opts.type        || 'danger',   // warning | danger | info | success
                onConfirm:   opts.onConfirm   || function () {}
            };

            const iconMap = {
                warning: 'fa-solid fa-triangle-exclamation',
                danger:  'fa-solid fa-trash-can',
                info:    'fa-solid fa-circle-info',
                success: 'fa-solid fa-circle-check'
            };
            const iconClass  = iconMap[defaults.type] || iconMap.warning;
            const btnClass   = defaults.type === 'danger' ? '' : ' info-btn';

            const $overlay = $(`
                <div id="cosy-sweet-overlay">
                    <div id="cosy-sweet-dialog">
                        <div class="cosy-sweet-icon ${defaults.type}">
                            <i class="${iconClass}"></i>
                        </div>
                        <p class="cosy-sweet-title">${defaults.title}</p>
                        <p class="cosy-sweet-message">${defaults.message}</p>
                        <div class="cosy-sweet-actions">
                            <button class="cosy-sweet-btn cosy-sweet-btn-confirm${btnClass}" id="cosy-sweet-ok">${defaults.confirmText}</button>
                            <button class="cosy-sweet-btn cosy-sweet-btn-cancel" id="cosy-sweet-cancel">${defaults.cancelText}</button>
                        </div>
                    </div>
                </div>
            `);

            $('body').append($overlay);

            // Confirm click
            $overlay.find('#cosy-sweet-ok').on('click', function () {
                $overlay.fadeOut(150, function () { $overlay.remove(); });
                defaults.onConfirm();
            });

            // Cancel / overlay click
            $overlay.find('#cosy-sweet-cancel').on('click', function () {
                $overlay.fadeOut(150, function () { $overlay.remove(); });
            });
            $overlay.on('click', function (e) {
                if ($(e.target).is('#cosy-sweet-overlay')) {
                    $overlay.fadeOut(150, function () { $overlay.remove(); });
                }
            });
        },

        /**
         * prompt
         * Shows a prompt dialog matching the exact cosy-sweet-dialog design with a textarea input.
         * @param {object} opts - { title, message, placeholder, confirmText, cancelText, type, onConfirm }
         */
        prompt: function (opts) {
            const defaults = {
                title:       opts.title       || 'Reject Media',
                message:     opts.message     || 'Please enter a reason:',
                placeholder: opts.placeholder || 'Type rejection reason here...',
                confirmText: opts.confirmText || 'Reject & Send Email',
                cancelText:  opts.cancelText  || 'Cancel',
                type:        opts.type        || 'danger',
                onConfirm:   opts.onConfirm   || function () {}
            };

            const iconMap = {
                warning: 'fa-solid fa-triangle-exclamation',
                danger:  'fa-solid fa-trash-can',
                info:    'fa-solid fa-circle-info',
                success: 'fa-solid fa-circle-check'
            };
            const iconClass  = iconMap[defaults.type] || iconMap.danger;

            const $overlay = $(`
                <div id="cosy-sweet-overlay">
                    <div id="cosy-sweet-dialog" style="max-width: 460px;">
                        <div class="cosy-sweet-icon ${defaults.type}">
                            <i class="${iconClass}"></i>
                        </div>
                        <p class="cosy-sweet-title">${defaults.title}</p>
                        <p class="cosy-sweet-message" style="margin-bottom: 12px;">${defaults.message}</p>
                        <div style="margin-bottom: 18px;">
                            <textarea id="cosy-sweet-input" class="cosy-sweet-textarea" placeholder="${defaults.placeholder}" rows="3" style="width: 100%; border: 1px solid #e2e8f0; border-radius: 14px; padding: 12px 14px; font-size: 14px; outline: none; font-family: inherit; resize: vertical; box-shadow: inset 0 1px 3px rgba(0,0,0,0.03);"></textarea>
                            <span id="cosy-sweet-error" style="display: none; color: #ef4444; font-size: 12px; margin-top: 5px; text-align: left; font-weight: 600;">Please enter a reason before proceeding.</span>
                        </div>
                        <div class="cosy-sweet-actions">
                            <button class="cosy-sweet-btn cosy-sweet-btn-confirm" id="cosy-sweet-ok">${defaults.confirmText}</button>
                            <button class="cosy-sweet-btn cosy-sweet-btn-cancel" id="cosy-sweet-cancel">${defaults.cancelText}</button>
                        </div>
                    </div>
                </div>
            `);

            $('body').append($overlay);
            setTimeout(function () {
                $overlay.find('#cosy-sweet-input').focus();
            }, 100);

            // Confirm click
            $overlay.find('#cosy-sweet-ok').on('click', function () {
                const val = $overlay.find('#cosy-sweet-input').val().trim();
                if (!val) {
                    $overlay.find('#cosy-sweet-error').slideDown(150);
                    $overlay.find('#cosy-sweet-input').css('border-color', '#ef4444').focus();
                    return;
                }
                $overlay.fadeOut(150, function () { $overlay.remove(); });
                defaults.onConfirm(val);
            });

            // Cancel click
            $overlay.find('#cosy-sweet-cancel').on('click', function () {
                $overlay.fadeOut(150, function () { $overlay.remove(); });
            });

            $overlay.on('click', function (e) {
                if ($(e.target).is('#cosy-sweet-overlay')) {
                    $overlay.fadeOut(150, function () { $overlay.remove(); });
                }
            });
        },

        /**
         * toast
         * Shows a premium slide-in toast notification.
         * @param {string} message
         * @param {string} type - success | danger | warning
         * @param {number} duration - ms before auto-hide (default 4000)
         */
        toast: function (message, type, duration) {
            type     = type     || 'success';
            duration = duration || 4000;

            if (!$('#cosy-sweet-toast-container').length) {
                $('body').append('<div id="cosy-sweet-toast-container"></div>');
            }

            const iconMap = {
                success: 'fa-solid fa-circle-check',
                danger:  'fa-solid fa-circle-xmark',
                warning: 'fa-solid fa-triangle-exclamation'
            };
            const iconClass = iconMap[type] || iconMap.success;

            const $toast = $(`
                <div class="cosy-sweet-toast ${type}">
                    <i class="cosy-sweet-toast-icon ${iconClass}"></i>
                    <span class="cosy-sweet-toast-text">${message}</span>
                </div>
            `);

            $('#cosy-sweet-toast-container').append($toast);

            setTimeout(function () {
                $toast.fadeOut(350, function () { $toast.remove(); });
            }, duration);
        }
    };

    // Expose CosyAlert globally for other admin scripts (e.g. users-admin.js)
    window.CosyAlert = CosyAlert;


    // =========================================================================
    // MODULE: CosyMediaAdmin
    // Handles approve, reject and delete actions for provider-submitted media content.
    // Uses Event Delegation so it works even on dynamically loaded rows.
    // =========================================================================
    const CosyMediaAdmin = {

        /**
         * init
         * Binds all event listeners for the media admin module.
         */
        init: function () {
            // Use event delegation on document to support dynamically rendered rows
            $(document).on('click', '.approve-media', this.handleApprove);
            $(document).on('click', '.reject-media', this.handleReject);

            // Select all checkboxes
            $(document).on('change', '#cosy-select-all-media', function () {
                const checked = $(this).prop('checked');
                $('.cosy-media-checkbox').prop('checked', checked).trigger('change');
            });

            // Individual checkbox change
            $(document).on('change', '.cosy-media-checkbox', function () {
                const checkedCount = $('.cosy-media-checkbox:checked').length;
                const totalCount = $('.cosy-media-checkbox').length;
                
                $('#cosy-select-all-media').prop('checked', checkedCount === totalCount && totalCount > 0);
                $('#cosy-media-btn-delete-selected').prop('disabled', checkedCount === 0);
            });

            // Bulk Delete Click Handler
            $(document).on('click', '#cosy-media-btn-delete-selected', this.handleBulkDelete);
        },

        /**
         * showAlert
         * Renders a custom premium alert in the .admin-succes container.
         *
         * @param {string} message - The message to display.
         * @param {string} type    - Alert type: 'success' or 'danger'.
         */
        showAlert: function (message, type) {
            const alertClass = type === 'success' ? 'cosy-admin-alert-success' : 'cosy-admin-alert-danger';
            const iconClass = type === 'success' ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-xmark';
            
            const $alert = $(`
                <div class="cosy-admin-alert ${alertClass}">
                    <span class="cosy-admin-alert-icon ${iconClass}"></span>
                    <span style="vertical-align: middle;">${message}</span>
                </div>
            `);
            
            $('.admin-succes').html($alert);
            
            // Auto fade out after 4 seconds
            setTimeout(function() {
                $alert.fadeOut(400, function() {
                    $(this).remove();
                });
            }, 4000);
        },

        /**
         * handleApprove
         * Fires an AJAX request to approve a provider's media upload.
         * On success, updates the status badge and removes the approve button.
         */
        handleApprove: function () {
            const row    = $(this).closest('tr');
            const $btn   = $(this);
            const userId = $btn.data('id');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'video_approve',
                    user_id: userId,
                    nonce: $('#cosy_media_nonce_field').val()
                },
                beforeSend: function () {
                    $btn.prop('disabled', true).text('Approving...');
                },
                success: function (res) {
                    if (res.success) {
                        // Update status badge in the table row immediately (7th child is Status column)
                        row.find('td:nth-child(7)').html('<span class="cosy-badge cosy-badge-approved">Approved</span>');
                        row.find('.approve-media').remove();
                        CosyMediaAdmin.showAlert(res.data.message || 'Video approved successfully!', 'success');
                    } else {
                        CosyMediaAdmin.showAlert(res.data.message || 'Error approving video.', 'danger');
                    }
                },
                complete: function () {
                    $btn.prop('disabled', false).text('Approve');
                }
            });
        },

        /**
         * handleReject
         * Prompts admin for rejection reason, then fires AJAX request to reject & delete provider's media.
         */
        handleReject: function () {
            const row    = $(this).closest('tr');
            const $btn   = $(this);
            const userId = $btn.data('id');

            CosyAlert.prompt({
                title:       'Reject & Delete Media?',
                message:     'Please enter the reason for rejecting this video (this will be sent to the provider via email):',
                placeholder: 'Type rejection reason here...',
                confirmText: 'Reject & Send Email',
                cancelText:  'Cancel',
                type:        'danger',
                onConfirm:   function (reasonText) {
                    CosyMediaAdmin.executeReject(userId, reasonText, row, $btn);
                }
            });
        },

        executeReject: function (userId, reason, row, $btn) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'video_reject',
                    user_id: userId,
                    reason: reason,
                    nonce: $('#cosy_media_nonce_field').val()
                },
                beforeSend: function () {
                    $btn.prop('disabled', true).text('Rejecting...');
                },
                success: function (res) {
                    if (res.success) {
                        // Update UI to show video has been deleted and rejected
                        row.find('td:nth-child(2)').html('<span class="text-muted">Deleted</span>');
                        row.find('td:nth-child(7)').html('<span class="cosy-badge cosy-badge-rejected">Rejected</span>');
                        row.find('td:nth-child(8)').html('<span class="text-muted">No Action</span>');
                        CosyMediaAdmin.showAlert(res.data.message || 'Video rejected successfully!', 'danger');
                    } else {
                        CosyMediaAdmin.showAlert(res.data.message || 'Error rejecting video.', 'danger');
                    }
                },
                complete: function () {
                    $btn.prop('disabled', false).text('Reject');
                }
            });
        },

        /**
         * handleBulkDelete
         * Collects checked media IDs and sends an AJAX request to delete them.
         */
        handleBulkDelete: function () {
            const $btn = $(this);
            const selectedIds = [];
            $('.cosy-media-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length === 0) {
                return;
            }

            CosyAlert.confirm({
                title:       'Delete Selected Media?',
                message:     'You are about to permanently delete ' + selectedIds.length + ' media item(s) from the database and media library. This action cannot be undone.',
                confirmText: 'Yes, Delete',
                cancelText:  'Cancel',
                type:        'danger',
                onConfirm: function () { CosyMediaAdmin._doDelete($btn, selectedIds); }
            });
        },

        /**
         * _doDelete — internal helper called after confirmation.
         */
        _doDelete: function ($btn, selectedIds) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'cosy_delete_media',
                    nonce: $('#cosy_media_nonce_field').val(),
                    media_ids: selectedIds
                },
                beforeSend: function () {
                    $btn.prop('disabled', true);
                    $btn.find('.cosy-btn-text').text('Deleting...');
                },
                success: function (res) {
                    if (res.success) {
                        let animationCompleted = 0;
                        selectedIds.forEach(function(id) {
                            const $checkbox = $(`.cosy-media-checkbox[value="${id}"]`);
                            $checkbox.closest('tr').fadeOut(400, function() {
                                $(this).remove();
                                animationCompleted++;

                                if (animationCompleted === selectedIds.length) {
                                    const remainingCount = $('.cosy-media-checkbox').length;
                                    if (remainingCount === 0) {
                                        $('.cosy-media-table tbody').html(`
                                            <tr>
                                                <td colspan="8" class="text-center py-4 text-muted" style="text-align: center; padding: 40px; color: #64748b;">
                                                    No media approvals found.
                                                </td>
                                            </tr>
                                        `);
                                    }
                                }
                            });
                        });

                        // Reset select all state
                        $('#cosy-select-all-media').prop('checked', false);
                        $btn.prop('disabled', true);
                        $btn.find('.cosy-btn-text').text('Delete');
                        CosyAlert.toast(res.data.message || 'Media deleted successfully.', 'success');
                    } else {
                        CosyAlert.toast(res.data.message || 'Error deleting media.', 'danger');
                        $btn.prop('disabled', false);
                        $btn.find('.cosy-btn-text').text('Delete');
                    }
                },
                error: function () {
                    CosyAlert.toast('An unexpected error occurred during media deletion.', 'danger');
                    $btn.prop('disabled', false);
                    $btn.find('.cosy-btn-text').text('Delete');
                }
            });
        }
    };

    // =========================================================================
    // MODULE: CosyOrdersAdmin
    // Handles the order details modal on the admin Orders page.
    // Reads booking data from HTML data-* attributes and populates modal fields.
    // =========================================================================
    const CosyOrdersAdmin = {

        /**
         * init
         * Binds all event listeners for the orders modal module.
         */
        init: function () {
            $(document).on('click', '.btn-view-admin-order-details', this.openModal);
            $(document).on('click', '.cosy-admin-modal-close, .cosy-admin-modal-close-btn', this.closeModal);

            // Close modal when clicking outside its bounds (on the dark overlay)
            $(window).on('click', function (event) {
                if ($(event.target).is('#cosyAdminOrderModal')) {
                    CosyOrdersAdmin.closeModal();
                }
            });

            // Select all top & bottom checkboxes
            $(document).on('change', '#cosy-select-all-orders, #cosy-select-all-orders-footer', function () {
                const checked = $(this).prop('checked');
                $('#cosy-select-all-orders, #cosy-select-all-orders-footer').prop('checked', checked);
                $('.cosy-order-checkbox').prop('checked', checked).trigger('change');
            });

            // Monitor row checkboxes to toggle Delete button state
            $(document).on('change', '.cosy-order-checkbox', function () {
                const checkedCount = $('.cosy-order-checkbox:checked').length;
                const totalCount = $('.cosy-order-checkbox').length;
                
                // Keep select all state in sync
                $('#cosy-select-all-orders, #cosy-select-all-orders-footer').prop('checked', checkedCount === totalCount && totalCount > 0);
                
                $('#cosy-btn-delete-selected').prop('disabled', checkedCount === 0);
            });

            // Bulk Delete Click Handler
            $(document).on('click', '#cosy-btn-delete-selected', this.handleBulkDelete);
        },

        /**
         * handleBulkDelete
         * Collects checked order IDs and sends an AJAX request to delete them.
         */
        handleBulkDelete: function () {
            const $btn = $(this);
            const selectedIds = [];
            $('.cosy-order-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length === 0) {
                return;
            }

            CosyAlert.confirm({
                title:       'Delete Selected Orders?',
                message:     'You are about to permanently delete ' + selectedIds.length + ' order(s). This action cannot be undone.',
                confirmText: 'Yes, Delete',
                cancelText:  'Cancel',
                type:        'danger',
                onConfirm: function () { CosyOrdersAdmin._doDelete($btn, selectedIds); }
            });
        },

        /**
         * _doDelete — internal helper called after confirmation.
         */
        _doDelete: function ($btn, selectedIds) {

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'cosy_delete_orders',
                    nonce: $('#cosy_delete_orders_nonce_field').val(),
                    order_ids: selectedIds
                },
                beforeSend: function () {
                    $btn.prop('disabled', true);
                    $btn.find('.cosy-btn-text').text('Deleting...');
                },
                success: function (res) {
                    if (res.success) {
                        // Fade out and remove the deleted rows
                        let animationCompleted = 0;
                        selectedIds.forEach(function(id) {
                            const $checkbox = $(`.cosy-order-checkbox[value="${id}"]`);
                            $checkbox.closest('tr').fadeOut(400, function() {
                                $(this).remove();
                                animationCompleted++;

                                // Once all rows are removed, check count and show placeholder if empty
                                if (animationCompleted === selectedIds.length) {
                                    const remainingCount = $('.cosy-order-checkbox').length;
                                    const countSpan = $('.displaying-num');

                                    if (countSpan.length > 0) {
                                        countSpan.text(remainingCount + ' item(s)');
                                    }

                                    if (remainingCount === 0) {
                                        $('.cosy-orders-table tbody').html(`
                                            <tr>
                                                <td colspan="9" class="text-center" style="text-align: center; padding: 40px; color: #64748b;">
                                                    No orders found matching the filter criteria.
                                                </td>
                                            </tr>
                                        `);
                                    }
                                }
                            });
                        });

                        // Reset checkboxes and delete button state
                        $('#cosy-select-all-orders, #cosy-select-all-orders-footer').prop('checked', false);
                        $btn.prop('disabled', true);
                        $btn.find('.cosy-btn-text').text('Delete');
                        CosyAlert.toast(res.data.message || 'Orders deleted successfully.', 'success');
                    } else {
                        CosyAlert.toast(res.data.message || 'Error deleting orders.', 'danger');
                        $btn.prop('disabled', false);
                        $btn.find('.cosy-btn-text').text('Delete');
                    }
                },
                error: function () {
                    CosyAlert.toast('An unexpected error occurred during order deletion.', 'danger');
                    $btn.prop('disabled', false);
                    $btn.find('.cosy-btn-text').text('Delete');
                }
            });
        },

        /**
         * openModal
         * Reads data-* attributes from the clicked button and populates
         * all fields inside the order details modal, then fades it in.
         */
        openModal: function (e) {
            e.preventDefault();

            // Read all booking data from the HTML data attributes
            const id      = $(this).data('id');
            const customer = $(this).data('customer');
            const email   = $(this).data('customer-email');
            const provider = $(this).data('provider');
            const service  = $(this).data('service');
            const start   = $(this).data('start');
            const end     = $(this).data('end');
            const weekly  = $(this).data('weekly');
            const weeks   = $(this).data('weeks');
            const slots   = $(this).data('slots');
            const cost    = $(this).data('cost');
            const fee     = $(this).data('fee');
            const total   = $(this).data('total');
            const status  = $(this).data('status');
            const weekDays = $(this).data('week-days');
            const slotsTimeline = $(this).data('slots-timeline');
            const txnRef    = $(this).data('txn-ref') || 'N/A';
            const paymentId = $(this).data('payment-id') || 'N/A';
            const cardBrand = $(this).data('card-brand') || '';
            const cardLast4 = $(this).data('card-last4') || '';
            const authCode  = $(this).data('auth-code') || '';
            const lastEvent = $(this).data('last-event') || '';
            const paymentDate = $(this).data('payment-date') || 'N/A';

            // Populate modal text fields
            $('#modalAdminOrderTitle').text('Order Details - #' + id);
            $('#modalAdminCustomerName').text(customer);
            $('#modalAdminCustomerEmail').text(email || 'N/A');
            $('#modalAdminProviderName').text(provider);
            $('#modalAdminServiceName').text(service);
            $('#modalAdminSchedule').text(weekly);

            // Display date range or a single start date if end date is missing
            if (start && end) {
                $('#modalAdminDuration').text(start + ' to ' + end);
            } else {
                $('#modalAdminDuration').text(start || 'N/A');
            }

            $('#modalAdminWeeks').text(weeks + ' week(s) (' + slots + ' slots booked)');
            $('#modalAdminWeekDays').text(weekDays || 'N/A');
            $('#modalAdminSlotsTimeline').text(slotsTimeline || 'N/A');
            const currencySymbol = (typeof cosyAdmin !== 'undefined' && cosyAdmin.currencySymbol) ? cosyAdmin.currencySymbol : '£';
            $('#modalAdminCost').text(currencySymbol + cost);
            $('#modalAdminFee').text(currencySymbol + fee);
            $('#modalAdminTotal').text(currencySymbol + total);

            // Populate WorldPay Payment details for Admin
            $('#modalAdminTxnRef').text(txnRef);
            $('#modalAdminPaymentId').text(paymentId);
            $('#modalAdminCardInfo').text(cardBrand ? (cardBrand.toUpperCase() + (cardLast4 ? ' ending in ' + cardLast4 : '')) : 'N/A');
            $('#modalAdminAuthEvent').text((authCode || 'N/A') + (lastEvent ? ' (' + lastEvent + ')' : ''));
            $('#modalAdminPaymentDate').text(paymentDate);

            // Apply colour-coded status badge consistent with WordPress admin styles
            CosyOrdersAdmin.applyStatusStyle(status);

            $('#cosyAdminOrderModal').css('display', 'flex').hide().fadeIn(150);
        },

        /**
         * closeModal
         * Hides the order details modal with a smooth fade-out animation.
         */
        closeModal: function () {
            $('#cosyAdminOrderModal').fadeOut(120);
        },

        /**
         * applyStatusStyle
         * Sets the background, text, and border colour of the status indicator
         * inside the modal based on the order's current status value.
         *
         * @param {string} status - The booking status string (e.g. 'completed', 'cancelled', 'pending').
         */
        applyStatusStyle: function (status) {
            let bg, color, border;

            if (status === 'completed' || status === 'confirmed') {
                bg = '#dcfce7'; color = '#166534'; border = '#bbf7d0';
            } else if (status === 'cancelled') {
                bg = '#fee2e2'; color = '#991b1b'; border = '#fecaca';
            } else {
                // Default: pending
                bg = '#fef9c3'; color = '#854d0e'; border = '#fef08a';
            }

            $('#modalAdminStatusBg').css({ 'background-color': bg, 'color': color, 'border-color': border });
            $('#modalAdminStatusText').text(status.toUpperCase());
        }
    };

    // =========================================================================
    // MODULE: CosyLogsAdmin
    // Handles toggles for logs per page and log viewer actions.
    // =========================================================================
    const CosyLogsAdmin = {
        init: function () {
            // Page Logging Toggle event
            $(document).on('change', '.cosy-page-log-toggle', function () {
                CosyLogsAdmin.handlePageToggle.call(this);
            });
            
            // Clear logs event
            $(document).on('click', '#cosy-btn-clear-logs', function (e) {
                e.preventDefault();
                CosyLogsAdmin.handleClearLogs.call(this);
            });
        },

        handlePageToggle: function () {
            const $checkbox = $(this);
            const pageName = $checkbox.data('page');
            const nonce = $checkbox.data('nonce');
            const isChecked = $checkbox.is(':checked');
            const status = isChecked ? 1 : 0;
            const container = $checkbox.closest('.cosy-page-logger-toggle-container');
            const $spinner = container.find('.cosy-log-toggle-spinner');
            const $lbl = container.find('.cosy-log-status-lbl');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'cosy_toggle_page_logging',
                    page_name: pageName,
                    status: status,
                    nonce: nonce
                },
                beforeSend: function () {
                    $spinner.css('display', 'inline-block');
                    $checkbox.prop('disabled', true);
                },
                success: function (res) {
                    if (res.success) {
                        if (isChecked) {
                            $lbl.text('Active').css('color', '#10b981');
                        } else {
                            $lbl.text('Paused').css('color', '#64748b');
                        }
                        CosyAlert.toast(res.data.message || 'Logging setting updated.', 'success', 2500);
                    } else {
                        CosyAlert.toast(res.data.message || 'Error updating logging settings.', 'danger');
                        $checkbox.prop('checked', !isChecked); // revert checkbox state
                    }
                },
                error: function () {
                    CosyAlert.toast('Failed to update logging settings.', 'danger');
                    $checkbox.prop('checked', !isChecked); // revert checkbox state
                },
                complete: function () {
                    $spinner.hide();
                    $checkbox.prop('disabled', false);
                }
            });
        },

        handleClearLogs: function () {
            const $btn = $(this);
            const nonce = $btn.data('nonce');

            CosyAlert.confirm({
                title:       'Clear All Activity Logs?',
                message:     'All recorded activity logs will be permanently deleted. This action cannot be undone.',
                confirmText: 'Yes, Clear All',
                cancelText:  'Cancel',
                type:        'danger',
                onConfirm: function () {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'cosy_clear_activity_logs',
                            nonce: nonce
                        },
                        beforeSend: function () {
                            $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Clearing...');
                        },
                        success: function (res) {
                            if (res.success) {
                                CosyAlert.toast(res.data.message || 'All logs cleared successfully.', 'success');
                                setTimeout(function () { location.reload(); }, 1200);
                            } else {
                                CosyAlert.toast(res.data.message || 'Error clearing logs.', 'danger');
                                $btn.prop('disabled', false).html('<i class="fa-solid fa-trash-can"></i> Clear All Logs');
                            }
                        },
                        error: function () {
                            CosyAlert.toast('Failed to clear logs. Please try again.', 'danger');
                            $btn.prop('disabled', false).html('<i class="fa-solid fa-trash-can"></i> Clear All Logs');
                        }
                    });
                }
            });
        }
    };

    // =========================================================================
    // MODULE: CosyReviewsAdmin
    // Handles approve, reject and delete actions for provider reviews.
    // =========================================================================
    const CosyReviewsAdmin = {
        init: function () {
            // Select all checkboxes
            $(document).on('change', '#cosy-select-all-reviews, #cosy-select-all-reviews-footer', function () {
                const checked = $(this).prop('checked');
                $('.cosy-review-checkbox').prop('checked', checked);
                $('#cosy-select-all-reviews, #cosy-select-all-reviews-footer').prop('checked', checked);
                $('#cosy-reviews-btn-delete-selected').prop('disabled', $('.cosy-review-checkbox:checked').length === 0);
            });

            $(document).on('change', '.cosy-review-checkbox', function () {
                const checkedCount = $('.cosy-review-checkbox:checked').length;
                const totalCount = $('.cosy-review-checkbox').length;
                $('#cosy-select-all-reviews, #cosy-select-all-reviews-footer').prop('checked', checkedCount === totalCount && totalCount > 0);
                $('#cosy-reviews-btn-delete-selected').prop('disabled', checkedCount === 0);
            });

            // Handlers
            $(document).on('click', '.btn-approve-review', this.handleApprove);
            $(document).on('click', '.btn-reject-review', this.handleReject);
            $(document).on('click', '.btn-delete-review', this.handleDelete);
            $(document).on('click', '#cosy-reviews-btn-delete-selected', this.handleBulkDelete);
        },

        handleApprove: function () {
            const reviewId = $(this).data('id');
            const $row = $('#review-row-' + reviewId);
            const $actionCell = $row.find('td').last();
            
            CosyAlert.confirm({
                title: 'Approve Review?',
                message: 'This review will be published on the parent profile page and an email notification will be sent to the provider.',
                confirmText: 'Yes, Approve',
                cancelText: 'Cancel',
                type: 'info',
                onConfirm: function () {
                    const originalHtml = $actionCell.html();
                    $actionCell.html('<i class="fas fa-circle-notch fa-spin" style="color: #22c55e; font-size: 18px; margin: 4px;"></i>');

                    $.post(ajaxurl, { action: 'cosy_admin_approve_review', review_id: reviewId }, function (res) {
                        if (res.success) {
                            CosyAlert.toast(res.data.message || 'Review approved successfully.', 'success');
                            setTimeout(function () { location.reload(); }, 1000);
                        } else {
                            $actionCell.html(originalHtml);
                            CosyAlert.toast(res.data.message || 'Error approving review.', 'danger');
                        }
                    });
                }
            });
        },

        handleReject: function () {
            const reviewId = $(this).data('id');
            const $row = $('#review-row-' + reviewId);
            const $actionCell = $row.find('td').last();

            CosyAlert.confirm({
                title: 'Reject Review?',
                message: 'This review will be marked as rejected.',
                confirmText: 'Yes, Reject',
                cancelText: 'Cancel',
                type: 'warning',
                onConfirm: function () {
                    const originalHtml = $actionCell.html();
                    $actionCell.html('<i class="fas fa-circle-notch fa-spin" style="color: #eab308; font-size: 18px; margin: 4px;"></i>');

                    $.post(ajaxurl, { action: 'cosy_admin_reject_review', review_id: reviewId }, function (res) {
                        if (res.success) {
                            CosyAlert.toast(res.data.message || 'Review rejected.', 'warning');
                            setTimeout(function () { location.reload(); }, 1000);
                        } else {
                            $actionCell.html(originalHtml);
                            CosyAlert.toast(res.data.message || 'Error rejecting review.', 'danger');
                        }
                    });
                }
            });
        },

        handleDelete: function () {
            const reviewId = $(this).data('id');
            const $row = $('#review-row-' + reviewId);
            const $actionCell = $row.find('td').last();

            CosyAlert.confirm({
                title: 'Delete Review?',
                message: 'Are you sure you want to delete this review? An audit log notification will be sent to the provider dashboard.',
                confirmText: 'Yes, Delete',
                cancelText: 'Cancel',
                type: 'danger',
                onConfirm: function () {
                    const originalHtml = $actionCell.html();
                    $actionCell.html('<i class="fas fa-circle-notch fa-spin" style="color: #ef4444; font-size: 18px; margin: 4px;"></i>');

                    $.post(ajaxurl, { action: 'cosy_admin_delete_review', review_id: reviewId }, function (res) {
                        if (res.success) {
                            $row.fadeOut(300, function () { $(this).remove(); });
                            CosyAlert.toast(res.data.message || 'Review deleted successfully.', 'success');
                        } else {
                            $actionCell.html(originalHtml);
                            CosyAlert.toast(res.data.message || 'Error deleting review.', 'danger');
                        }
                    });
                }
            });
        },

        handleBulkDelete: function () {
            const selectedIds = [];
            $('.cosy-review-checkbox:checked').each(function () {
                selectedIds.push($(this).val());
            });
            if (selectedIds.length === 0) return;

            CosyAlert.confirm({
                title: 'Delete Selected Reviews?',
                message: 'Are you sure you want to delete ' + selectedIds.length + ' selected review(s)? An audit log notification will be recorded.',
                confirmText: 'Yes, Delete All',
                cancelText: 'Cancel',
                type: 'danger',
                onConfirm: function () {
                    let completed = 0;
                    selectedIds.forEach(function (id) {
                        $.post(ajaxurl, { action: 'cosy_admin_delete_review', review_id: id }, function (res) {
                            completed++;
                            if (res.success) {
                                $('#review-row-' + id).fadeOut(300, function () { $(this).remove(); });
                            }
                            if (completed === selectedIds.length) {
                                $('#cosy-reviews-btn-delete-selected').prop('disabled', true);
                                $('#cosy-select-all-reviews, #cosy-select-all-reviews-footer').prop('checked', false);
                                CosyAlert.toast('Selected reviews deleted successfully.', 'success');
                            }
                        });
                    });
                }
            });
        }
    };

    // =========================================================================
    // BOOT: Initialize all admin modules
    // =========================================================================
    CosyMediaAdmin.init();
    CosyOrdersAdmin.init();
    CosyLogsAdmin.init();
    CosyReviewsAdmin.init();

});