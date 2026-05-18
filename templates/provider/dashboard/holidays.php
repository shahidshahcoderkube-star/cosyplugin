<?php
/**
 * Template: Provider Dashboard - Non Working Days (Holidays)
 *
 * Displays the provider's holiday/non-working-day list.
 * - Loads saved holidays dynamically from user meta (cosy_provider_holidays).
 * - Allows adding new holidays via AJAX (no page reload).
 * - Allows deleting holidays via AJAX with SweetAlert2 confirmation.
 *
 * Storage: wp_usermeta key = 'cosy_provider_holidays' (JSON array)
 * Each entry: { "date": "YYYY-MM-DD", "reason": "string" }
 */

$user_id  = get_current_user_id();
$raw      = get_user_meta($user_id, 'cosy_provider_holidays', true);
$holidays = (!empty($raw)) ? json_decode($raw, true) : [];
if (!is_array($holidays)) {
    $holidays = [];
}
?>

<!-- ============================================================
     NON WORKING DAYS CARD
     CSS: src/assets/css/style.css (Non Working Days section)
     ============================================================ -->
<div class="card cosy-holidays-card mb-4">
    <div class="card-body p-0">

        <!-- Section Header -->
        <div class="d-flex align-items-center gap-3 mb-2">
            <div style="background: rgba(164, 67, 144, 0.1); width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-calendar-times" style="color: #a44390; font-size: 1.2rem;"></i>
            </div>
            <h3 class="mb-0">Non Working Days</h3>
        </div>
        <p class="text-muted mb-4" style="margin-left: 58px;">Mark your holidays or off days below.</p>

        <!-- Dynamic Holiday List -->
        <div class="holiday-list mt-2" id="cosyHolidayList">
            <?php if (!empty($holidays)) : ?>
                <?php foreach ($holidays as $holiday) :
                    $display_date = date('d M Y', strtotime($holiday['date']));
                    $reason       = esc_html($holiday['reason'] ?? 'Holiday');
                    $date_raw     = esc_attr($holiday['date']);
                ?>
                    <div class="holiday-item" id="holiday-<?php echo $date_raw; ?>">
                        <div class="holiday-info">
                            <i class="fas fa-calendar-day"></i>
                            <div>
                                <span class="holiday-date"><?php echo esc_html($display_date); ?></span>
                                <span class="mx-2 text-muted">|</span>
                                <span class="holiday-reason text-muted small"><?php echo $reason; ?></span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <button
                                class="cosy-delete-holiday-btn"
                                data-date="<?php echo $date_raw; ?>"
                                title="Remove Holiday">
                                <i class="fas fa-trash-alt" style="font-size: 0.85rem;"></i>
                            </button>
                            <span class="badge holiday-badge">Holiday</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <!-- Empty State -->
                <div class="holidays-empty-state" id="cosyHolidaysEmpty">
                    <i class="fas fa-calendar-check d-block"></i>
                    <p>No holidays added yet. Click <strong>Add Holiday</strong> to get started.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Add Holiday Button -->
        <div class="text-center mt-4">
            <button class="btn custom-btn" data-bs-toggle="modal" data-bs-target="#addHolidayModal">
                <i class="fas fa-plus-circle me-2"></i> Add Holiday
            </button>
        </div>
    </div>
</div>

<!-- ============================================================
     ADD HOLIDAY MODAL
     ============================================================ -->
<div class="modal fade" id="addHolidayModal" tabindex="-1" aria-hidden="true" style="z-index: 99999;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
        <div class="modal-content cosy-modal-content">

            <!-- Modal Header -->
            <div class="modal-header cosy-modal-header">
                <h5 class="modal-title fw-bold text-white mb-0">
                    <i class="fas fa-calendar-plus me-2"></i> Add Non Working Day
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="cosy-modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold d-block mb-2">Date</label>
                    <input
                        type="date"
                        id="cosyHolidayDate"
                        class="form-control"
                        min="<?php echo date('Y-m-d'); ?>"
                        required>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold d-block mb-2">Reason / Occasion</label>
                    <input
                        type="text"
                        id="cosyHolidayReason"
                        class="form-control"
                        placeholder="e.g. Independence Day"
                        maxlength="100">
                </div>
                <div class="text-center mt-2">
                    <button type="button" id="cosySaveHolidayBtn" class="btn save-holiday-btn">
                        <i class="fas fa-calendar-check me-2"></i> SAVE HOLIDAY
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript: src/assets/js/dashboard.js (Non Working Days section) -->