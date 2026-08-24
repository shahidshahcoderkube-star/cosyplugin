/**
 * dashboard.js
 *
 * Centralized, Modular JavaScript for the Provider Dashboard tabs.
 * Built using the Object-Literal Modular Namespace Pattern.
 * Enqueued via Assets.php on the frontend.
 *
 * Design Architecture Choice:
 * We use a single global wrapper `jQuery(document).ready` to encapsulate all modules.
 * This guarantees no global scope namespace pollution and prevents conflicts with other plugins.
 *
 * Event Delegation Strategy:
 * Because dashboard tabs load content dynamically via AJAX, directly binding to elements 
 * (e.g. $('.btn').on('click')) will fail when tabs switch. 
 * We bind all listeners using event delegation $(document).on('click', '.selector', ...)
 * which ensures handlers work perfectly for dynamic DOM elements.
 */

jQuery(document).ready(function ($) {
    'use strict';

    // ============================================================
    // 1. GLOBAL CONFIG & SHARED HELPERS
    // ============================================================
    var ajaxUrl = (typeof cosyDashboard !== 'undefined') ? cosyDashboard.ajax_url
        : (typeof cosyAjax !== 'undefined') ? cosyAjax.ajax_url
            : '';

    var nonce = (typeof cosyDashboard !== 'undefined') ? cosyDashboard.nonce
        : (typeof cosyAjax !== 'undefined') ? cosyAjax.nonce
            : '';

    var currencySymbol = (typeof cosyDashboard !== 'undefined' && cosyDashboard.currencySymbol) ? cosyDashboard.currencySymbol : '£';

    /**
     * Helper: Escape a string to prevent XSS when inserting dynamic text into innerHTML.
     */
    function escHtml(str) {
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(str));
        return d.innerHTML;
    }

    // ============================================================
    // 2. HOLIDAYS & REVIEWS MODULE
    // ============================================================
    /**
     * CosyHolidays: Handles calendar holiday configurations and customer reviews.
     * Functions are encapsulated within this namespace to isolate state and logic.
     */
    const CosyHolidays = {
        init() {
            // Bind all event handlers inside the Holidays module
            $(document).on('click', '#cosySaveHolidayBtn', this.saveHoliday);
            $(document).on('click', '.cosy-delete-holiday-btn', this.deleteHoliday);
            $(document).on('click', '.approve-review-btn', this.approveReview);
            $(document).on('click', '.delete-review-btn', this.deleteReview);
            $(document).on('hidden.bs.modal', '#addHolidayModal', this.handleModalClose);
        },

        /**
         * Dynamically builds the HTML markup for a new holiday row.
         * Used to instantly insert a row into the DOM after a successful AJAX addition.
         */
        buildHolidayRow(date, displayDate, reason) {
            return '<div class="holiday-item" id="holiday-' + date + '">' +
                '<div class="holiday-info">' +
                '<i class="fas fa-calendar-day"></i>' +
                '<div>' +
                '<span class="holiday-date">' + displayDate + '</span>' +
                '<span class="mx-2 text-muted">|</span>' +
                '<span class="holiday-reason text-muted small">' + escHtml(reason) + '</span>' +
                '</div>' +
                '</div>' +
                '<div class="d-flex align-items-center gap-3">' +
                '<button class="cosy-delete-holiday-btn" data-date="' + date + '" title="Remove Holiday">' +
                '<i class="fas fa-trash-alt" style="font-size:0.85rem;"></i>' +
                '</button>' +
                '<span class="badge holiday-badge">Holiday</span>' +
                '</div>' +
                '</div>';
        },

        /**
         * Syncs the empty-state placeholder in the Holidays tab list.
         * Adds a friendly prompt if no holidays are registered; removes it when one is added.
         */
        syncEmptyState() {
            var list = document.getElementById('cosyHolidayList');
            if (!list) return;

            var items = list.querySelectorAll('.holiday-item');
            var empty = document.getElementById('cosyHolidaysEmpty');

            if (items.length === 0) {
                if (!empty) {
                    var div = document.createElement('div');
                    div.className = 'holidays-empty-state';
                    div.id = 'cosyHolidaysEmpty';
                    div.innerHTML =
                        '<i class="fas fa-calendar-check d-block"></i>' +
                        '<p>No holidays added yet. Click <strong>Add Holiday</strong> to get started.</p>';
                    list.appendChild(div);
                }
            } else {
                if (empty) {
                    empty.remove();
                }
            }
        },

        /**
         * Syncs the empty-state placeholder in the customer reviews tab list.
         * Runs when a provider deletes or rejects a review to update the UI without reloading.
         */
        syncReviewsEmptyState() {
            var list = document.querySelector('.reviews-list');
            if (!list) return;

            var items = list.querySelectorAll('.review-item');
            if (items.length === 0) {
                list.innerHTML =
                    '<div class="text-center py-5 rounded-4" style="background: #f8fafc; border: 1.5px dashed #cbd5e1;">' +
                    '<i class="far fa-comments text-muted mb-3" style="font-size: 2.5rem;"></i>' +
                    '<p class="text-muted mb-0">No customer reviews found for your profile yet.</p>' +
                    '</div>';
            }
        },

        /**
         * Resets the save button back to its standard clickable state.
         * Used to restore button visual feedback when an API call finishes or fails.
         */
        resetSaveBtn() {
            var btn = document.getElementById('cosySaveHolidayBtn');
            if (!btn) return;
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-calendar-check me-2"></i> SAVE HOLIDAY';
        },

        /**
         * Event: Adds a new custom holiday.
         * Validates inputs locally, runs a WP AJAX action, updates the DOM with a 
         * smooth animation, and shows a premium success popup.
         */
        saveHoliday(e) {
            e.preventDefault();
            var saveBtn = $(this);
            var dateInput = $('#cosyHolidayDate');
            var reasonInput = $('#cosyHolidayReason');
            if (!dateInput.length || !reasonInput.length) return;

            var date = dateInput.val().trim();
            var reason = reasonInput.val().trim();

            if (!date) {
                CosyAlert.warning('Date Required', 'Please select a date before saving.');
                return;
            }

            if (!reason) {
                CosyAlert.warning('Reason Required', 'Please enter a reason or occasion for this holiday.');
                reasonInput.focus();
                return;
            }

            if (document.getElementById('holiday-' + date)) {
                CosyAlert.error('Already Marked', 'This date is already marked as a holiday. Please choose another date.');
                return;
            }

            saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Saving...');

            var formData = new FormData();
            formData.append('action', 'cosy_add_holiday');
            formData.append('nonce', nonce);
            formData.append('holiday_date', date);
            formData.append('holiday_reason', reason);

            fetch(ajaxUrl, { method: 'POST', body: formData })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (res.success) {
                        var modalEl = document.getElementById('addHolidayModal');
                        if (modalEl) {
                            var bsModal = bootstrap.Modal.getInstance(modalEl);
                            if (bsModal) bsModal.hide();
                        }

                        var list = document.getElementById('cosyHolidayList');
                        if (list) {
                            list.insertAdjacentHTML('afterbegin', CosyHolidays.buildHolidayRow(
                                res.data.date,
                                res.data.display_date,
                                res.data.reason
                            ));
                        }

                        CosyHolidays.syncEmptyState();

                        CosyAlert.success('Holiday Added!', res.data.message);
                    } else {
                        CosyHolidays.resetSaveBtn();
                        CosyAlert.error('Could Not Add', res.data.message || 'Something went wrong.');
                    }
                })
                .catch(function () {
                    CosyHolidays.resetSaveBtn();
                    CosyAlert.error('Network Error', 'Could not reach the server. Please try again.');
                });
        },

        /**
         * Event: Deletes an existing holiday.
         * Asks for confirmation using SweetAlert, sends an AJAX call to delete metadata, 
         * and fades out the row cleanly from the active list.
         */
        deleteHoliday(e) {
            e.preventDefault();
            var deleteBtn = $(this);
            var dateToRemove = deleteBtn.attr('data-date');

            CosyAlert.confirm(
                'Remove Holiday?',
                'Are you sure you want to remove this holiday from your schedule?',
                'Yes, Remove It'
            ).then(function () {

                var formData = new FormData();
                formData.append('action', 'cosy_delete_holiday');
                formData.append('nonce', nonce);
                formData.append('holiday_date', dateToRemove);

                fetch(ajaxUrl, { method: 'POST', body: formData })
                    .then(function (r) { return r.json(); })
                    .then(function (res) {
                        if (res.success) {
                            var row = document.getElementById('holiday-' + dateToRemove);
                            if (row) {
                                row.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                                row.style.opacity = '0';
                                row.style.transform = 'translateX(20px)';
                                setTimeout(function () {
                                    row.remove();
                                    CosyHolidays.syncEmptyState();
                                }, 300);
                            }
                            CosyAlert.success('Removed!', res.data.message);
                        } else {
                            CosyAlert.error('Error', res.data.message || 'Could not remove the holiday.');
                        }
                    })
                    .catch(function () {
                        CosyAlert.error('Network Error', 'Could not reach the server. Please try again.');
                    });
            }).catch(function() { /* Cancelled */ });
        },

        /**
         * Event: Approves a pending customer review.
         * Sends AJAX request, updates review borders, and replaces moderation actions 
         * directly in the DOM without requiring a full page refresh.
         */
        approveReview(e) {
            e.preventDefault();
            var approveBtn = $(this);
            var reviewId = approveBtn.attr('data-id');

            approveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Approving...');

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cosy_approve_provider_review',
                    nonce: nonce,
                    review_id: reviewId
                },
                success: function (response) {
                    if (response.success) {
                        CosyAlert.success('Approved!', 'Review successfully approved and displayed on your profile.').then(function () {
                            var card = document.getElementById('cosy-review-' + reviewId);
                            if (card) {
                                card.className = card.className.replace('border-start-warning', 'border-start-success');
                                card.style.setProperty('border-left-color', '#22c55e', 'important');

                                var badge = card.querySelector('.badge');
                                if (badge) {
                                    badge.className = 'badge bg-success text-white ms-2';
                                    badge.innerHTML = '<i class="fas fa-check-circle me-1"></i> Approved';
                                }

                                var controls = card.querySelector('.d-flex.gap-2');
                                if (controls) {
                                    controls.innerHTML =
                                        '<button class="btn btn-sm btn-outline-danger delete-review-btn" data-id="' + reviewId + '" style="border-radius: 8px; font-weight: 600; font-size: 0.8rem;">' +
                                        '<i class="fas fa-trash-alt me-1"></i> Delete' +
                                        '</button>';
                                }
                            }
                        });
                    } else {
                        approveBtn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Approve');
                        CosyAlert.error('Error', (response.data && response.data.message) || 'Failed to approve review.');
                    }
                },
                error: function () {
                    approveBtn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Approve');
                    CosyAlert.error('Error', 'Failed to communicate with server.');
                }
            });
        },

        /**
         * Event: Deletes or rejects a customer review.
         * Deletes review post meta dynamically and triggers empty state checks.
         */
        deleteReview(e) {
            e.preventDefault();
            var deleteBtn = $(this);
            var reviewId = deleteBtn.attr('data-id');

            CosyAlert.confirm(
                'Are you sure?',
                'Do you want to delete or reject this review? This action cannot be undone.'
            ).then(function () {

                deleteBtn.prop('disabled', true);
                var originalHTML = deleteBtn.html();
                deleteBtn.html('<i class="fas fa-spinner fa-spin me-1"></i> Deleting...');

                $.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'cosy_delete_provider_review',
                        nonce: nonce,
                        review_id: reviewId
                    },
                    success: function (response) {
                        if (response.success) {
                            CosyAlert.success('Deleted!', 'Review has been removed.').then(function () {
                                var card = document.getElementById('cosy-review-' + reviewId);
                                if (card) {
                                    card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                                    card.style.opacity = '0';
                                    card.style.transform = 'translateY(10px)';
                                    setTimeout(function () {
                                        card.remove();
                                        CosyHolidays.syncReviewsEmptyState();
                                    }, 300);
                                }
                            });
                        } else {
                            deleteBtn.prop('disabled', false).html(originalHTML);
                            CosyAlert.error('Error', (response.data && response.data.message) || 'Failed to delete review.');
                        }
                    },
                    error: function () {
                        deleteBtn.prop('disabled', false).html(originalHTML);
                        CosyAlert.error('Error', 'Failed to communicate with server.');
                    }
                });
            }).catch(function() { /* Cancelled */ });
        },

        /**
         * Event: Modal Reset on Close.
         * Automatically flushes input fields in the add holiday modal upon closure.
         */
        handleModalClose() {
            var dateInput = document.getElementById('cosyHolidayDate');
            var reasonInput = document.getElementById('cosyHolidayReason');
            if (dateInput) dateInput.value = '';
            if (reasonInput) reasonInput.value = '';
            CosyHolidays.resetSaveBtn();
        }
    };

    // ============================================================
    // 3. PROVIDER ORDERS MODULE
    // ============================================================
    /**
     * CosyOrders: Manages live filtering, search, CSV exports, modal detail mapping,
     * and status update actions for provider appointments.
     */
    const CosyOrders = {
        init() {
            $(document).on('keyup', '#orderSearchInput', this.search);
            $(document).on('change', '#orderStatusFilter', this.filter);
            $(document).on('click', '#exportOrdersBtn', this.export);
            $(document).on('click', '.btn-view-order-details', this.viewDetails);
            $(document).on('click', '.action-update-status', this.updateStatus);
        },

        /**
         * Keyup Search: Filters the table rows instantly matching user inputs against 
         * pre-compiled searchable row attributes.
         */
        search() {
            var value = $(this).val().toLowerCase().trim();
            $('#providerOrdersTable tbody tr.order-table-row').filter(function () {
                $(this).toggle($(this).attr('data-search').indexOf(value) > -1);
            });
        },

        /**
         * Change Filter: Shows or hides order table rows based on active appointment status.
         */
        filter() {
            var val = $(this).val();
            $('#providerOrdersTable tbody tr.order-table-row').filter(function () {
                if (val === '') {
                    $(this).show();
                } else {
                    $(this).toggle($(this).attr('data-status') === val);
                }
            });
        },

        /**
         * Export CSV: Parses active table rows and generates an instant, downloadable 
         * CSV file without hitting the database.
         */
        export(e) {
            e.preventDefault();
            var csvContent = 'data:text/csv;charset=utf-8,';
            csvContent += 'Order ID,Customer,Service,Date,Status\n';

            $('#providerOrdersTable tbody tr.order-table-row').each(function () {
                if ($(this).is(':visible')) {
                    var id = $(this).find('td:nth-child(1)').text().replace('#', '');
                    var customer = $(this).find('td:nth-child(2)').text().trim();
                    var service = $(this).find('td:nth-child(3)').text().trim();
                    var date = $(this).find('td:nth-child(4)').text().trim();
                    var status = $(this).attr('data-status').toUpperCase();
                    csvContent += '"' + id + '","' + customer + '","' + service + '","' + date + '","' + status + '"\n';
                }
            });

            var encodedUri = encodeURI(csvContent);
            var link = document.createElement('a');
            link.setAttribute('href', encodedUri);
            link.setAttribute('download', 'provider_orders_' + new Date().toISOString().slice(0, 10) + '.csv');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        },

        /**
         * Populator: Maps appointment data-attributes to elements inside the Details Modal.
         * Ensures that status indicators show clean bootstrap badges.
         */
        viewDetails() {
            var id = $(this).data('id');
            var customer = $(this).data('customer');
            var email = $(this).data('email');
            var service = $(this).data('service');
            var start = $(this).data('start');
            var end = $(this).data('end');
            var weekly = $(this).data('weekly');
            var weeks = $(this).data('weeks');
            var slots = $(this).data('slots');
            var cost = $(this).data('cost');
            var fee = $(this).data('fee') || '0.00';
            var total = $(this).data('total');
            var status = $(this).attr('data-status');
            var weekDays = $(this).data('week-days') || '';
            var slotsTimeline = $(this).data('slots-timeline') || '';

            var isGift = String($(this).data('is-gift') || '0');
            var recipientName = $(this).data('recipient-name') || '';
            var recipientEmail = $(this).data('recipient-email') || '';

            if (isGift === '1' || (recipientEmail && recipientEmail.trim() !== '')) {
                $('#modalOrderTitle').html('Order Details - #' + id + ' <span class="badge ms-2" style="background: #a44390; color: #fff; font-size: 0.75rem; vertical-align: middle;">🎁 Gifted Order</span>');
                var custHtml = '<div class="d-flex flex-column gap-1">';
                custHtml += '<div><span class="badge bg-warning text-dark fw-bold me-1">GIFTEE (Contact Person)</span> <strong>' + (recipientName || 'Gift Recipient') + '</strong> <span class="text-primary fw-semibold">(' + recipientEmail + ')</span></div>';
                custHtml += '<div class="small text-muted mt-1"><i class="fas fa-credit-card me-1"></i> Paying Customer: <strong>' + customer + '</strong> (' + email + ')</div>';
                custHtml += '</div>';
                $('#modalCustomerName').html(custHtml);
                $('#modalCustomerEmail').text('');
            } else {
                $('#modalOrderTitle').text('Order Details - #' + id);
                $('#modalCustomerName').text(customer);
                $('#modalCustomerEmail').text('(' + email + ')');
            }

            $('#modalStartDateInfo').text(start);
            $('#modalWeeksInfo').text(weeks + (parseInt(weeks) === 1 ? ' week' : ' weeks'));
            $('#modalSlotsTimelineInfo').html(slotsTimeline);
            $('#modalProviderShare').text(currencySymbol + cost);
            $('#modalServiceFee').text(currencySymbol + fee);
            $('#modalTotalPaid').text(currencySymbol + total);

            var badge = '';
            if (status === 'completed') {
                badge = '<span class="badge badge-completed"><i class="fas fa-check-circle me-1"></i> Completed</span>';
                $('#modalFooterActions').html('<button type="button" class="btn btn-secondary rounded-4 px-4 py-2 fw-bold btn-modal-close" data-bs-dismiss="modal">Close</button>');
            } else if (status === 'cancelled') {
                badge = '<span class="badge badge-cancelled"><i class="fas fa-times-circle me-1"></i> Cancelled</span>';
                $('#modalFooterActions').html('<button type="button" class="btn btn-secondary rounded-4 px-4 py-2 fw-bold btn-modal-close" data-bs-dismiss="modal">Close</button>');
            } else if (status === 'confirmed') {
                badge = '<span class="badge badge-confirmed" style="background:#0ea5e9;color:#fff;"><i class="fas fa-thumbs-up me-1"></i> Confirmed</span>';
                $('#modalFooterActions').html(
                    '<button type="button" class="btn btn-secondary rounded-4 px-4 py-2 fw-bold btn-modal-close" data-bs-dismiss="modal">Close</button>' +
                    '<button type="button" class="btn btn-success rounded-4 px-4 py-2 fw-bold action-update-status" data-id="' + id + '" data-status="completed" data-bs-dismiss="modal">Mark Completed</button>' +
                    '<button type="button" class="btn btn-danger rounded-4 px-4 py-2 fw-bold action-update-status" data-id="' + id + '" data-status="cancelled" data-bs-dismiss="modal">Cancel Order</button>'
                );
            } else {
                badge = '<span class="badge badge-pending"><i class="fas fa-clock me-1"></i> Pending</span>';
                $('#modalFooterActions').html(
                    '<button type="button" class="btn btn-secondary rounded-4 px-4 py-2 fw-bold btn-modal-close" data-bs-dismiss="modal">Close</button>' +
                    '<button type="button" class="btn btn-primary rounded-4 px-4 py-2 fw-bold action-update-status btn-modal-confirm" data-id="' + id + '" data-status="confirmed" data-bs-dismiss="modal">Confirm Order</button>' +
                    '<button type="button" class="btn btn-success rounded-4 px-4 py-2 fw-bold action-update-status btn-modal-complete" data-id="' + id + '" data-status="completed" data-bs-dismiss="modal">Mark Completed</button>' +
                    '<button type="button" class="btn btn-danger rounded-4 px-4 py-2 fw-bold action-update-status btn-modal-cancel" data-id="' + id + '" data-status="cancelled" data-bs-dismiss="modal">Cancel Order</button>'
                );
            }
            $('#modalStatusContainer').html(badge);
        },

        /**
         * AJAX Action: Updates an appointment's status (completed / cancelled / pending).
         * Runs secure background updates, updates status badges, and deletes action controls
         * immediately upon validation.
         */
        updateStatus(e) {
            e.preventDefault();
            var btn = $(this);
            var orderId = btn.data('id');
            var newStatus = btn.data('status');

            var confirmBtnText = (newStatus === 'completed') ? 'Yes, Accept it!' : (newStatus === 'cancelled' ? 'Yes, cancel it!' : 'Yes, change it!');
            var confirmColor   = (newStatus === 'cancelled') ? '#ef4444' : '#22c55e'; // Green for Accept, Red for Cancelled
            var cancelColor    = (newStatus === 'cancelled') ? '#22c55e' : '#ef4444'; // Red for Cancel button

            CosyAlert.confirm(
                'Are you sure?',
                'Do you want to mark this booking as ' + newStatus + '?',
                confirmBtnText,
                'Cancel',
                confirmColor,
                cancelColor
            ).then(function () {
                var row = $('#order-row-' + orderId);
                var rowBtns = row.find('.action-update-status');

                // Find the specific button to show spinner
                var targetBtn = btn.hasClass('order-action-btn') ? btn : row.find('.action-update-status[data-status="' + newStatus + '"]');
                if (!targetBtn.length) {
                    targetBtn = btn;
                }
                var originalIconHtml = targetBtn.html();

                // Set spinning loader icon on target button & disable row action buttons
                targetBtn.html('<i class="fas fa-spinner fa-spin"></i>');
                rowBtns.prop('disabled', true).css({ 'pointer-events': 'none', 'opacity': '0.7' });

                if (btn.parents('#orderDetailsModal').length) {
                    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
                }

                $.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'cosy_update_booking_status',
                        nonce: nonce,
                        order_id: orderId,
                        appointment_id: orderId,
                        status: newStatus
                    },
                    success: function (response) {
                        if (response.success) {
                            CosyAlert.success('Success', response.data.message);

                            if (row.length) {
                                row.attr('data-status', newStatus);

                                var badgeHtml = '';
                                if (newStatus === 'confirmed') {
                                    badgeHtml = '<span class="badge badge-confirmed" style="background:#0ea5e9;color:#fff;"><i class="fas fa-thumbs-up me-1"></i> Confirmed</span>';
                                } else if (newStatus === 'completed') {
                                    badgeHtml = '<span class="badge badge-completed"><i class="fas fa-check-circle me-1"></i> Completed</span>';
                                } else if (newStatus === 'cancelled') {
                                    badgeHtml = '<span class="badge badge-cancelled"><i class="fas fa-times-circle me-1"></i> Cancelled</span>';
                                } else {
                                    badgeHtml = '<span class="badge badge-pending"><i class="fas fa-clock me-1"></i> Pending</span>';
                                }
                                row.find('.status-cell').html(badgeHtml);
                                var detailsBtn = row.find('.btn-view-order-details');
                                detailsBtn.attr('data-status', newStatus);
                                detailsBtn.data('status', newStatus);
                                row.find('.action-update-status').remove();
                            }
                        } else {
                            targetBtn.html(originalIconHtml);
                            rowBtns.prop('disabled', false).css({ 'pointer-events': 'auto', 'opacity': '1' });
                            CosyAlert.error('Failed', response.data.message);
                        }
                    },
                    error: function () {
                        targetBtn.html(originalIconHtml);
                        rowBtns.prop('disabled', false).css({ 'pointer-events': 'auto', 'opacity': '1' });
                        CosyAlert.error('Error', 'Failed to communicate with the server.');
                    }
                });
            }).catch(function() { /* Cancelled */ });
        }
    };

    // ============================================================
    // 5. INITIALIZE ALL MODULES
    // ============================================================
    // Bootstraps all registered dashboard modules upon document ready
    CosyHolidays.init();
    CosyOrders.init();

});
