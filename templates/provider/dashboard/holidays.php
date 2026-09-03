<?php
/**
 * PROVIDER DASHBOARD HOLIDAYS TEMPLATE
 * 
 * USE CASE:
 * Displays the provider's holiday / non-working-days list in Provider Dashboard.
 * 
 * HOW TO USE:
 * Loaded dynamically via AJAX when provider selects the "Holidays" tab.
 * 
 * WHAT IT DOES INTERNALLY:
 * 1. Reads saved holidays array from usermeta ('cosy_provider_holidays').
 * 2. Renders dynamic list of registered holidays with delete actions.
 * 3. Interactions handled by dashboard.js (CosyHolidays).
 */
if (!defined('ABSPATH')) {
    exit;
}
$user_id  = get_current_user_id();
$raw      = get_user_meta($user_id, 'cosy_provider_holidays', true);
$holidays = [];
if (is_array($raw)) {
    $holidays = $raw;
} elseif (is_string($raw) && !empty($raw)) {
    $raw_clean = stripslashes($raw);
    $decoded   = json_decode($raw_clean, true);
    if (!is_array($decoded)) {
        $decoded = json_decode($raw, true);
    }
    if (is_array($decoded)) {
        $holidays = $decoded;
    }
}
?>

<!-- ============================================================
     NON WORKING DAYS CARD
     CSS: src/assets/css/style.css (Non Working Days section)
     ============================================================ -->
<div class="card cosy-holidays-card mb-4 border-0 shadow-sm rounded-4">
    <div class="card-body p-3 p-sm-4">

        <!-- Section Header -->
        <div class="d-flex align-items-center gap-3 mb-2">
            <div style="background: rgba(164, 67, 144, 0.1); width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-calendar-times" style="color: #a44390; font-size: 1.2rem;"></i>
            </div>
            <h3 class="mb-0 fw-bold" style="color: #1e293b; font-family: 'Outfit', sans-serif; font-size: 1.75rem;"><?php esc_html_e('Non Working Days', 'cosy-appointments'); ?></h3>
        </div>
        <p class="text-muted mb-4" style="font-size: 0.95rem; font-weight: 500;"><?php esc_html_e('Mark your holidays or off days below.', 'cosy-appointments'); ?></p>

        <!-- Dynamic Holiday List -->
        <div class="holiday-list mt-2" id="cosyHolidayList">
            <?php if (!empty($holidays)) : ?>
                <?php foreach ($holidays as $holiday) :
                    $display_date = date('d M Y', strtotime($holiday['date']));
                    $reason       = esc_html($holiday['reason'] ?? 'Holiday');
                    $date_raw     = esc_attr($holiday['date']);
                ?>
                    <div class="holiday-item d-flex justify-content-between align-items-center gap-2 p-3 rounded-4 mb-3" id="holiday-<?php echo esc_attr($date_raw); ?>">
                        <div class="d-flex align-items-center gap-2 gap-sm-3 min-w-0">
                            <div class="holiday-icon-box d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; border-radius: 12px; background: rgba(164, 67, 144, 0.08); color: #a44390;">
                                <i class="fas fa-calendar-day" style="font-size: 1rem;"></i>
                            </div>
                            <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-1 gap-sm-2 min-w-0">
                                <span class="holiday-date fw-bold text-dark text-nowrap" style="font-size: 0.95rem;"><?php echo esc_html($display_date); ?></span>
                                <span class="text-muted d-none d-sm-inline" style="opacity: 0.5;">•</span>
                                <span class="holiday-reason text-muted small text-truncate" style="font-size: 0.85rem; max-width: 250px;"><?php echo $reason; ?></span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-auto">
                            <span class="badge holiday-badge px-3 py-2 rounded-3" style="background: rgba(164, 67, 144, 0.1); color: #a44390; font-weight: 600; font-size: 0.75rem;"><?php esc_html_e('Holiday', 'cosy-appointments'); ?></span>
                            <button
                                class="cosy-delete-holiday-btn"
                                data-date="<?php echo esc_attr($date_raw); ?>"
                                title="<?php esc_attr_e('Remove Holiday', 'cosy-appointments'); ?>">
                                <i class="fas fa-trash-alt" style="font-size: 0.85rem;"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <!-- Empty State -->
                <div class="holidays-empty-state" id="cosyHolidaysEmpty">
                    <i class="fas fa-calendar-check d-block"></i>
                    <p><?php echo wp_kses(__('No holidays added yet. Click <strong>Add Holiday</strong> to get started.', 'cosy-appointments'), ['strong' => []]); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Add Holiday Button -->
        <div class="text-center mt-4">
            <button class="btn custom-btn fw-bold d-inline-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#addHolidayModal" style="min-width: 200px;">
                <i class="fas fa-plus-circle me-1"></i> <?php esc_html_e('Add Holiday', 'cosy-appointments'); ?>
            </button>
        </div>
    </div>
</div>

<!-- ============================================================
     ADD HOLIDAY MODAL
     ============================================================ -->
<?php
ob_start();
?>
<div class="mb-3">
    <label class="form-label fw-bold d-block mb-2"><?php esc_html_e('Date', 'cosy-appointments'); ?></label>
    <input
        type="date"
        id="cosyHolidayDate"
        class="form-control"
        min="<?php echo date('Y-m-d'); ?>"
        required>
</div>
<div class="mb-4">
    <label class="form-label fw-bold d-block mb-2"><?php esc_html_e('Reason / Occasion', 'cosy-appointments'); ?></label>
    <input
        type="text"
        id="cosyHolidayReason"
        class="form-control"
        placeholder="<?php esc_attr_e('e.g. Independence Day', 'cosy-appointments'); ?>"
        maxlength="100">
</div>
<div class="text-center mt-2">
    <button type="button" id="cosySaveHolidayBtn" class="btn save-holiday-btn">
        <i class="fas fa-calendar-check me-2"></i> <?php esc_html_e('SAVE HOLIDAY', 'cosy-appointments'); ?>
    </button>
</div>
<?php
$modal_body = ob_get_clean();
echo cosy_render_popup(
    'addHolidayModal',
    '<i class="fas fa-calendar-plus me-2"></i> Add Non Working Day',
    $modal_body
);
?>

<!-- JavaScript: src/assets/js/dashboard.js (Non Working Days section) -->