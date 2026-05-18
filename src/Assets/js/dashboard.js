/**
 * dashboard.js
 *
 * Centralized JavaScript for the Provider Dashboard tabs.
 * Enqueued via Assets.php on the frontend.
 *
 * Sections:
 * 1. Non Working Days (Holidays) — AJAX add & delete
 */

(function () {
    'use strict';

    /* ============================================================
       HELPERS — shared across all dashboard sections
       ============================================================ */

    /**
     * Read AJAX URL and nonce from the global cosyAjax object
     * (localized by Assets.php via wp_localize_script).
     * Falls back to cosyDashboard if both are available.
     */
    var ajaxUrl = (typeof cosyDashboard !== 'undefined') ? cosyDashboard.ajax_url
        : (typeof cosyAjax !== 'undefined') ? cosyAjax.ajax_url
            : '';

    var nonce = (typeof cosyDashboard !== 'undefined') ? cosyDashboard.nonce
        : (typeof cosyAjax !== 'undefined') ? cosyAjax.nonce
            : '';

    /* ============================================================
       NON WORKING DAYS (HOLIDAYS)
       ============================================================ */

    /**
     * Build a single holiday-item HTML row for dynamic DOM insertion.
     * Called after a successful AJAX add.
     *
     * @param {string} date        Raw date string  (YYYY-MM-DD)
     * @param {string} displayDate Formatted date   (01 Jan 2026)
     * @param {string} reason      Occasion / reason text
     * @returns {string} HTML string
     */
    function buildHolidayRow(date, displayDate, reason) {
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
    }

    /**
     * Escape a string to prevent XSS when inserting into innerHTML.
     *
     * @param {string} str
     * @returns {string}
     */
    function escHtml(str) {
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(str));
        return d.innerHTML;
    }

    /**
     * Show or hide the empty-state placeholder based on how many
     * holiday rows currently exist in the list.
     */
    function syncEmptyState() {
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
    }

    /**
     * Reset the Save Holiday button back to its default enabled state.
     */
    function resetSaveBtn() {
        var btn = document.getElementById('cosySaveHolidayBtn');
        if (!btn) return;
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-calendar-check me-2"></i> SAVE HOLIDAY';
    }

    /* ============================================================
       EVENT DELEGATION (Works with AJAX-loaded tabs)
       ============================================================ */

    document.addEventListener('click', function (e) {

        /* ---------- ADD HOLIDAY ---------- */
        var saveBtn = e.target.closest('#cosySaveHolidayBtn');
        if (saveBtn) {
            e.preventDefault();

            var dateInput = document.getElementById('cosyHolidayDate');
            var reasonInput = document.getElementById('cosyHolidayReason');
            if (!dateInput || !reasonInput) return;

            var date = dateInput.value.trim();
            var reason = reasonInput.value.trim();

            // --- Validation ---
            if (!date) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Date Required',
                    text: 'Please select a date before saving.',
                    confirmButtonColor: '#a44390',
                });
                return;
            }

            if (!reason) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Reason Required',
                    text: 'Please enter a reason or occasion for this holiday.',
                    confirmButtonColor: '#a44390',
                });
                reasonInput.focus();
                return;
            }

            // --- Duplicate Date Check (Frontend) ---
            if (document.getElementById('holiday-' + date)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Already Marked',
                    text: 'This date is already marked as a holiday. Please choose another date.',
                    confirmButtonColor: '#a44390',
                });
                return;
            }

            // Disable button while request is in flight
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Saving...';

            var formData = new FormData();
            formData.append('action', 'cosy_add_holiday');
            formData.append('nonce', nonce);
            formData.append('holiday_date', date);
            formData.append('holiday_reason', reason);

            fetch(ajaxUrl, { method: 'POST', body: formData })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (res.success) {
                        // Close modal — hidden.bs.modal event will call resetSaveBtn()
                        var modalEl = document.getElementById('addHolidayModal');
                        if (modalEl) {
                            var bsModal = bootstrap.Modal.getInstance(modalEl);
                            if (bsModal) bsModal.hide();
                        }

                        // Insert new row at the top of the list
                        var list = document.getElementById('cosyHolidayList');
                        if (list) {
                            list.insertAdjacentHTML('afterbegin', buildHolidayRow(
                                res.data.date,
                                res.data.display_date,
                                res.data.reason
                            ));
                        }

                        syncEmptyState();

                        Swal.fire({
                            icon: 'success',
                            title: 'Holiday Added!',
                            text: res.data.message,
                            confirmButtonColor: '#a44390',
                            timer: 2500,
                            timerProgressBar: true,
                            showConfirmButton: false,
                        });
                    } else {
                        // Keep modal open on error — re-enable button
                        resetSaveBtn();
                        Swal.fire({
                            icon: 'error',
                            title: 'Could Not Add',
                            text: res.data.message || 'Something went wrong.',
                            confirmButtonColor: '#a44390',
                        });
                    }
                })
                .catch(function () {
                    resetSaveBtn();
                    Swal.fire({
                        icon: 'error',
                        title: 'Network Error',
                        text: 'Could not reach the server. Please try again.',
                        confirmButtonColor: '#a44390',
                    });
                });
            return;
        }

        /* ---------- DELETE HOLIDAY ---------- */
        var deleteBtn = e.target.closest('.cosy-delete-holiday-btn');
        if (deleteBtn) {
            e.preventDefault();
            var dateToRemove = deleteBtn.getAttribute('data-date');

            Swal.fire({
                title: 'Remove Holiday?',
                text: 'Are you sure you want to remove this holiday from your schedule?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, Remove It',
                cancelButtonText: 'Cancel',
            }).then(function (result) {
                if (!result.isConfirmed) return;

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
                                    syncEmptyState();
                                }, 300);
                            }
                            Swal.fire({
                                icon: 'success',
                                title: 'Removed!',
                                text: res.data.message,
                                confirmButtonColor: '#a44390',
                                timer: 2000,
                                timerProgressBar: true,
                                showConfirmButton: false,
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: res.data.message || 'Could not remove the holiday.',
                                confirmButtonColor: '#a44390',
                            });
                        }
                    })
                    .catch(function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Network Error',
                            text: 'Could not reach the server. Please try again.',
                            confirmButtonColor: '#a44390',
                        });
                    });
            });
        }
    });

    /* ---------- MODAL RESET on close ---------- */
    // Bootstrap events bubble up, so we can listen on document
    document.addEventListener('hidden.bs.modal', function (e) {
        if (e.target && e.target.id === 'addHolidayModal') {
            var dateInput = document.getElementById('cosyHolidayDate');
            var reasonInput = document.getElementById('cosyHolidayReason');
            if (dateInput) dateInput.value = '';
            if (reasonInput) reasonInput.value = '';
            resetSaveBtn();
        }
    });

}());
