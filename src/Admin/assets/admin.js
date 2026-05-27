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
    // MODULE: CosyMediaAdmin
    // Handles approve and reject actions for provider-submitted media content.
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
        },

        /**
         * showAlert
         * Renders a Bootstrap dismissible alert in the .admin-succes container.
         *
         * @param {string} message - The message to display.
         * @param {string} type    - Bootstrap alert type: 'success' or 'danger'.
         */
        showAlert: function (message, type) {
            $('.admin-succes').html(`
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);
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
                        // Update status badge in the table row immediately
                        row.find('td:nth-child(7)').html('<span class="badge bg-success status-badge">Approved</span>');
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
         * Fires an AJAX request to reject a provider's media upload.
         * On success, updates the row to reflect rejection and media deletion.
         */
        handleReject: function () {
            const row    = $(this).closest('tr');
            const $btn   = $(this);
            const userId = $btn.data('id');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'video_reject',
                    user_id: userId,
                    nonce: $('#cosy_media_nonce_field').val()
                },
                beforeSend: function () {
                    $btn.prop('disabled', true).text('Rejecting...');
                },
                success: function (res) {
                    if (res.success) {
                        // Update UI to show video has been deleted and rejected
                        row.find('td:nth-child(2)').html('<span class="text-muted">Deleted</span>');
                        row.find('td:nth-child(7)').html('<span class="badge bg-danger status-badge">Rejected</span>');
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

            if (!confirm('Are you sure you want to delete the selected orders? This action cannot be undone.')) {
                return;
            }

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
                    $btn.prop('disabled', true).text('Deleting...');
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
                        $btn.prop('disabled', true).text('Delete Selected');
                    } else {
                        alert(res.data.message || 'Error deleting orders.');
                        $btn.prop('disabled', false).text('Delete Selected');
                    }
                },
                error: function () {
                    alert('An unexpected error occurred during order deletion.');
                    $btn.prop('disabled', false).text('Delete Selected');
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
            $('#modalAdminCost').text('£' + cost);
            $('#modalAdminFee').text('£' + fee);
            $('#modalAdminTotal').text('£' + total);

            // Apply colour-coded status badge consistent with WordPress admin styles
            CosyOrdersAdmin.applyStatusStyle(status);

            $('#cosyAdminOrderModal').fadeIn(150);
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
                bg = '#e6fcf5'; color = '#0ca678'; border = '#c3fae8';
            } else if (status === 'cancelled') {
                bg = '#fff5f5'; color = '#fa5252'; border = '#ffe3e3';
            } else {
                // Default: pending
                bg = '#fcf0e1'; color = '#d97706'; border = '#fcd34d';
            }

            $('#modalAdminStatusBg').css({ 'background-color': bg, 'color': color, 'border-color': border });
            $('#modalAdminStatusText').text(status.toUpperCase());
        }
    };

    // =========================================================================
    // BOOT: Initialize all admin modules
    // =========================================================================
    CosyMediaAdmin.init();
    CosyOrdersAdmin.init();

});