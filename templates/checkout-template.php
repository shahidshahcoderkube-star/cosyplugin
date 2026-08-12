<?php
if (!defined('ABSPATH')) {
    exit;
}

$provider_id = isset($_GET['provider_id']) ? intval($_GET['provider_id']) : (isset($_COOKIE['cosy_selected_provider_id']) ? intval($_COOKIE['cosy_selected_provider_id']) : 0);
$availability = [];
$holiday_dates = [];
$holiday_reasons = [];
$provider_name = 'Verified Parent';

if ($provider_id > 0) {
    if (!isset($common) || !method_exists($common, 'get_provider_availability_data')) {
        $common = new class {
            use \Cosy\Appointments\Common\GlobalCommonFunctions;
        };
    }
    $avail_data           = $common->get_provider_availability_data($provider_id);
    $availability         = $avail_data['availability'];
    $holiday_dates        = $avail_data['holiday_dates'];
    $holiday_reasons      = $avail_data['holiday_reasons'] ?? [];
    $provider_profile_url = get_author_posts_url($provider_id);

    $provider_user = get_userdata($provider_id);
    if ($provider_user && !empty($provider_user->display_name)) {
        $provider_name = $provider_user->display_name;
    }
}

$start_date_param = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '';
$step = isset($_GET['step']) ? sanitize_text_field($_GET['step']) : 'schedule';

// Pre-render Call Schedule HTML server-side for Instant Load (0ms delay)
$pre_rendered_schedule_html = '';
if ($step === 'schedule' || !empty($start_date_param)) {
    $base_date = null;
    if (!empty($start_date_param)) {
        if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $start_date_param, $m)) {
            $base_date = strtotime("{$m[3]}-{$m[2]}-{$m[1]}");
        } else {
            $base_date = strtotime($start_date_param);
        }
    }
    if (!$base_date) {
        $base_date = time();
    }

    $formatted_start_date = date('F j, Y', $base_date);
    $slots_rows_html = '';
    $day_offset = 0;

    while ($day_offset < 7) {
        $next_time = strtotime("+$day_offset day", $base_date);
        $day_name = date('l', $next_time);
        $date_iso = date('Y-m-d', $next_time);
        $date_str = date('D M d Y', $next_time);
        $formatted_day_date = date('M j', $next_time);
        $day_offset++;

        $is_holiday = in_array($date_iso, $holiday_dates);
        $is_day_off = false;
        if (!empty($availability) && is_array($availability)) {
            $day_config = $availability[$day_name] ?? null;
            if (!$day_config || (empty($day_config['start_time']) && empty($day_config['end_time']))) {
                $is_day_off = true;
            }
        }

        if ($is_day_off) {
            continue;
        }

        $safe_id_key = preg_replace('/[^a-zA-Z0-9]/', '-', $date_str);

        if ($is_holiday) {
            $slots_rows_html .= '
                <div class="d-flex align-items-center justify-content-between p-3 mb-3 rounded-4 border bg-light opacity-75 shadow-sm" style="border-color: #f1f5f9 !important;">
                    <div class="text-start">
                        <h6 class="fw-bold mb-1 text-muted" style="font-size: 0.95rem;">' . esc_html($day_name . ' (' . $formatted_day_date . ')') . '</h6>
                        <p class="small text-danger mb-0">🚫 Holiday / Unavailable</p>
                    </div>
                    <button type="button" disabled class="btn btn-sm px-3 py-2 fw-semibold text-muted bg-white border" style="border-radius: 12px; font-size: 0.82rem; cursor: not-allowed;">
                        Unavailable
                    </button>
                </div>';
        } else {
            $slots_rows_html .= '
                <div class="d-flex align-items-center justify-content-between p-3 mb-3 rounded-4 border bg-white shadow-sm" style="border-color: #f1f5f9 !important;">
                    <div class="text-start">
                        <h6 class="fw-bold mb-1" style="color: #1e293b; font-size: 0.95rem;">' . esc_html($day_name . ' (' . $formatted_day_date . ')') . '</h6>
                        <p class="small text-muted mb-0" id="duration-' . esc_attr($safe_id_key) . '">0 minutes Call Duration</p>
                    </div>
                    <button type="button" id="btn-time-' . esc_attr($safe_id_key) . '" class="btn btn-sm px-3 py-2 fw-bold text-white shadow-sm btn-open-time-modal" data-date="' . esc_attr($date_str) . '" style="background: #a44390; border-radius: 12px; font-size: 0.82rem;">
                        Select Time
                    </button>
                </div>';
        }
    }

    $pre_rendered_schedule_html = '
        <div class="cosy-checkout-header d-flex align-items-center justify-content-between mb-4">
            <button id="cosyCheckoutBackBtn" class="cosy-checkout-back-btn btn border-0 fw-bold px-0 py-2 d-inline-flex align-items-center gap-2" style="background: transparent !important; color: #a44390; box-shadow: none; border-radius: 0; font-size: 0.95rem; line-height: 1;">
                <i class="fas fa-arrow-left" style="color: #a44390 !important; font-size: 0.95rem;"></i> <span>Back to Profile</span>
            </button>
            <h2 class="cosy-checkout-title h4 fw-bold mb-0 d-inline-flex align-items-center gap-2" style="color: #a44390; font-size: 1.25rem;">
                <i class="fas fa-calendar-check" style="color: #a44390;"></i> <span>Call Schedule</span>
            </h2>
        </div>

        <!-- Header Card -->
        <div class="cosy-card-rounded card border-0 shadow-sm mb-4 p-4" style="border-radius: 20px; background: #ffffff;">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pb-3 border-bottom">
                <div class="d-flex align-items-center gap-3">
                    <div class="cosy-icon-box" style="width: 42px; height: 42px; background: #fdf5fc; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #a44390;">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0" style="color: #1e293b;">' . esc_html($provider_name) . '</h6>
                        <small class="text-muted">Start Date: <span class="fw-bold text-dark">' . esc_html($formatted_start_date) . '</span></small>
                    </div>
                </div>
            </div>
            
            <div class="pt-4">
                <p class="small text-muted fw-bold text-uppercase mb-3" style="letter-spacing: 0.5px;">Choose Your Time Slots:</p>
                <div id="callScheduleSlotsContainer">
                    ' . $slots_rows_html . '
                </div>
            </div>
        </div>';
}
?>
<script>
    window.providerAvailability = <?php echo wp_json_encode($availability); ?>;
    window.providerHolidays = <?php echo wp_json_encode($holiday_dates); ?>;
    window.providerHolidayReasons = <?php echo wp_json_encode($holiday_reasons); ?>;
    window.providerProfileUrl = <?php echo wp_json_encode($provider_profile_url); ?>;
</script>

<div class="cosy-checkout-root" style="padding-top: 50px; padding-bottom: 50px;">
    <div class="cosy-checkout-container" id="cosyCheckoutContainer">
        <?php echo $pre_rendered_schedule_html; ?>
    </div>
</div>

<!-- Time Slot Selection Modal for Call Schedule Step -->
<div class="modal fade" id="timeSlotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="cosy-card-rounded modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-header border-0 pb-0 justify-content-between align-items-center p-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="cosy-modal-icon-box" style="width: 42px; height: 42px; background: #fdf5fc; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #a44390;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0" style="color: #1e293b;"><?php esc_html_e('Select Call Start Time', 'cosy-appointments'); ?></h5>
                        <small class="text-muted fw-medium"><?php esc_html_e('10-minute slot blocks can be selected', 'cosy-appointments'); ?></small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex gap-4 mb-4 small fw-medium justify-content-center">
                    <span class="d-flex align-items-center gap-2">
                        <span style="width: 12px; height: 12px; background: #fff; border: 1.5px solid #edf2f7; border-radius: 3px;"></span>
                        <?php esc_html_e('Available', 'cosy-appointments'); ?>
                    </span>
                    <span class="d-flex align-items-center gap-2">
                        <span style="width: 12px; height: 12px; background: #a44390; border-radius: 3px;"></span>
                        <?php esc_html_e('Selected', 'cosy-appointments'); ?>
                    </span>
                    <span class="d-flex align-items-center gap-2">
                        <span style="width: 12px; height: 12px; background: #e2e8f0; border-radius: 3px;"></span> <?php esc_html_e('Booked', 'cosy-appointments'); ?>
                    </span>
                </div>
                <!-- Time blocks generated dynamically -->
                <div id="timeGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(105px, 1fr)); gap: 10px;"></div>
            </div>
            <div class="modal-footer border-0 p-4 pt-2">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <div class="text-start">
                        <small class="text-muted d-block fw-bold text-uppercase" style="font-size: 0.72rem;"><?php esc_html_e('Total Duration', 'cosy-appointments'); ?></small>
                        <span id="modalTotalDuration" class="fw-bold" style="font-size: 1.1rem; color: #a44390;">0 <?php esc_html_e('minutes', 'cosy-appointments'); ?></span>
                    </div>
                    <button type="button" class="btn px-4 py-2 fw-bold text-white shadow-sm" id="btnConfirmTimeSlotsModal" style="background: #a44390; border-radius: 12px; font-size: 0.9rem;">
                        <?php esc_html_e('Confirm', 'cosy-appointments'); ?>
                    </button>
                </div>  
            </div>
        </div>
    </div>
</div>