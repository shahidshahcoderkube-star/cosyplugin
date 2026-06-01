/**
 * users-admin.js
 * Admin JavaScript controller for the Cosy Appointments Users Management page.
 *
 * Follows the same Object-Literal Namespace pattern as admin.js.
 * Nonces and i18n strings are passed via wp_localize_script (cosyUsersAdmin object).
 *
 * Modules:
 *  - CosyUsersAdmin : Handles status updates, verification emails, user details modal, and bulk delete.
 */
jQuery(document).ready(function ($) {
    'use strict';

    // Exit early if we are not on the users page
    if (typeof cosyUsersAdmin === 'undefined') {
        return;
    }

    // Convenience references to localized data
    var nonces = cosyUsersAdmin.nonces;
    var i18n   = cosyUsersAdmin.i18n;

    // =========================================================================
    // MODULE: CosyUsersAdmin
    // =========================================================================
    var CosyUsersAdmin = {

        /**
         * init
         * Binds all event listeners for the users admin module.
         */
        init: function () {
            // Status dropdown change
            $(document).on('change', '.cosy-admin-status-dropdown', this.handleStatusChange);

            // Resend verification email
            $(document).on('click', '.cosy-btn-resend-verification', this.handleResendVerification);

            // View Details modal
            $(document).on('click', '.btn-view-cosy-user-details', this.handleViewDetails);

            // Modal filter tabs
            $(document).on('click', '.cosy-modal-tab', this.handleModalTabFilter);

            // Close modal
            $(document).on('click', '.cosy-user-modal-close, .cosy-user-modal-close-btn', this.closeModal);
            $(window).on('click', function (event) {
                if (event.target === $('#cosyAdminUserModal')[0]) {
                    CosyUsersAdmin.closeModal();
                }
            });

            // Select all checkboxes
            $('#cosy-select-all-users').on('change', function () {
                var checked = $(this).prop('checked');
                $('.cosy-user-checkbox').prop('checked', checked);
                CosyUsersAdmin.toggleDeleteButton();
            });

            // Individual checkbox change
            $(document).on('change', '.cosy-user-checkbox', function () {
                var allChecked = $('.cosy-user-checkbox:checked').length === $('.cosy-user-checkbox').length;
                $('#cosy-select-all-users').prop('checked', allChecked);
                CosyUsersAdmin.toggleDeleteButton();
            });

            // Bulk delete
            $(document).on('click', '#cosy-btn-delete-selected', this.handleBulkDelete);
        },

        // ----- Status Update -----
        handleStatusChange: function () {
            var select  = $(this);
            var userId  = select.data('user-id');
            var role    = select.data('role');
            var status  = select.val();
            var spinner = select.next('.spinner');

            select.prop('disabled', true);
            spinner.addClass('is-active');

            $.post(ajaxurl, {
                action:   'cosy_admin_update_user_status',
                security: nonces.status,
                user_id:  userId,
                role:     role,
                status:   status
            }, function (response) {
                select.prop('disabled', false);
                spinner.removeClass('is-active');

                if (response.success) {
                    var originalColor = select.css('border-color');
                    select.css('border-color', '#46b450');
                    setTimeout(function () {
                        select.css('border-color', originalColor);
                    }, 1500);
                } else {
                    alert(response.data || i18n.statusFailed);
                }
            });
        },

        // ----- Resend Verification Email -----
        handleResendVerification: function () {
            var btn    = $(this);
            var userId = btn.data('user-id');
            var role   = btn.data('role');

            if (!confirm(i18n.confirmResend)) {
                return;
            }

            btn.prop('disabled', true).text(i18n.sending);

            $.post(ajaxurl, {
                action:   'cosy_admin_resend_verification',
                security: nonces.email,
                user_id:  userId,
                role:     role
            }, function (response) {
                btn.prop('disabled', false).text(i18n.resendEmail);
                if (response.success) {
                    alert(response.data || i18n.emailSent);
                } else {
                    alert(response.data || i18n.emailFailed);
                }
            });
        },

        // ----- View Details Modal -----
        handleViewDetails: function () {
            var userId = $(this).data('user-id');
            var modal  = $('#cosyAdminUserModal');
            modal.show();

            // Loading state
            $('#modalAdminUserBody').html(
                '<div style="text-align: center; padding: 30px;">' +
                '<span class="spinner is-active" style="float: none; margin: 0 auto;"></span>' +
                '<p style="margin-top: 10px; color: #64748b;">' + i18n.loadingDetails + '</p>' +
                '</div>'
            );

            $.post(ajaxurl, {
                action:   'cosy_admin_get_user_details',
                security: nonces.details,
                user_id:  userId
            }, function (response) {
                if (response.success) {
                    $('#modalAdminUserBody').html(response.data.html);
                    $('#modalAdminUserTitle').text(response.data.title);
                } else {
                    $('#modalAdminUserBody').html(
                        '<div style="color: #c53030; padding: 15px; border-radius: 8px; background: #fff5f5;">' +
                        (response.data || i18n.detailsFailed) +
                        '</div>'
                    );
                }
            });
        },

        // ----- Modal Filter Tabs -----
        handleModalTabFilter: function () {
            var tab    = $(this);
            var status = tab.data('status');

            $('.cosy-modal-tab').removeClass('active');
            tab.addClass('active');

            if (status === 'all') {
                $('.cosy-modal-appt-card').show();
            } else {
                $('.cosy-modal-appt-card').hide();
                $('.cosy-modal-appt-card[data-status="' + status + '"]').show();
            }
        },

        // ----- Close Modal -----
        closeModal: function () {
            $('#cosyAdminUserModal').hide();
        },

        // ----- Toggle Delete Button -----
        toggleDeleteButton: function () {
            var checkedCount = $('.cosy-user-checkbox:checked').length;
            $('#cosy-btn-delete-selected').prop('disabled', checkedCount === 0);
        },

        // ----- Bulk Delete -----
        handleBulkDelete: function () {
            var selectedIds = [];
            $('.cosy-user-checkbox:checked').each(function () {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length === 0) {
                return;
            }

            if (!confirm(i18n.confirmDelete)) {
                return;
            }

            var btn = $(this);
            btn.prop('disabled', true);
            btn.find('.cosy-btn-text').text(i18n.deleting);

            $.post(ajaxurl, {
                action:   'cosy_admin_delete_users',
                security: nonces.delete,
                user_ids: selectedIds
            }, function (response) {
                btn.find('.cosy-btn-text').text(i18n.deleteBtn);
                if (response.success) {
                    alert(response.data);
                    // Remove deleted rows from DOM
                    selectedIds.forEach(function (id) {
                        $('#user-row-' + id).fadeOut(400, function () {
                            $(this).remove();
                        });
                    });
                    $('#cosy-select-all-users').prop('checked', false);
                    CosyUsersAdmin.toggleDeleteButton();
                } else {
                    alert(response.data || i18n.deleteFailed);
                    btn.prop('disabled', false);
                    CosyUsersAdmin.toggleDeleteButton();
                }
            });
        }
    };

    // =========================================================================
    // BOOT
    // =========================================================================
    CosyUsersAdmin.init();
});
