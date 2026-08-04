<?php

/**
 * AVAILABILITY TEMPLATE
 * 
 * This file renders the 'Availability' tab in the Provider Dashboard.
 * It allows providers to set their working hours, slot durations, and breaks.
 */

// If availability data is not already provided, fetch it for all 7 days from User Meta.
if (!isset($availability)) {
    $user_id = get_current_user_id();
    $days_list = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    $availability = [];
    foreach ($days_list as $day) {
        $availability[$day] = get_user_meta($user_id, "cosy_availability_{$day}", true);
    }
}
?>


<div class="card cosy-availability-card mb-4">
    <div class="card-body p-0">
        <div class="d-flex align-items-center gap-3 mb-2">
            <div class="header-icon-badge" style="background: rgba(164, 67, 144, 0.1); width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-calendar-alt" style="color: #a44390; font-size: 1.2rem;"></i>
            </div>
            <h3 class="mb-0"><?php esc_html_e('Availability', 'cosy-appointments'); ?></h3>
        </div>
        <p class="text-muted mb-4" style="margin-left: 58px;"><?php esc_html_e('Set your working hours and available slots for booking.', 'cosy-appointments'); ?></p>

        <!-- Weekday Availability -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <label class="form-label"><?php esc_html_e('Day', 'cosy-appointments'); ?></label>
                <select class="form-select" id="availability_day">
                    <option value=""><?php esc_html_e('Select Day', 'cosy-appointments'); ?></option>
                    <option value="Monday"><?php esc_html_e('Monday', 'cosy-appointments'); ?></option>
                    <option value="Tuesday"><?php esc_html_e('Tuesday', 'cosy-appointments'); ?></option>
                    <option value="Wednesday"><?php esc_html_e('Wednesday', 'cosy-appointments'); ?></option>
                    <option value="Thursday"><?php esc_html_e('Thursday', 'cosy-appointments'); ?></option>
                    <option value="Friday"><?php esc_html_e('Friday', 'cosy-appointments'); ?></option>
                    <option value="Saturday"><?php esc_html_e('Saturday', 'cosy-appointments'); ?></option>
                    <option value="Sunday"><?php esc_html_e('Sunday', 'cosy-appointments'); ?></option>
                </select>
            </div>
            <div class="col-md-3 mb-4">
                <label class="form-label"><?php esc_html_e('Start Time', 'cosy-appointments'); ?></label>
                <input type="time" class="form-control" id="start_time">
                <div id="start_time_badge" class="mt-1" style="font-size: 0.8rem; font-weight: 600; color: #a44390; display: none;"></div>
            </div>
            <div class="col-md-3 mb-4">
                <label class="form-label"><?php esc_html_e('End Time', 'cosy-appointments'); ?></label>
                <input type="time" class="form-control" id="end_time">
                <div id="end_time_badge" class="mt-1" style="font-size: 0.8rem; font-weight: 600; color: #a44390; display: none;"></div>
            </div>
        </div>

        <!-- Fixed 10-Minute Slot Duration & Break Times -->
        <input type="hidden" id="slot_duration" value="10">
        <div class="row">
            <div class="col-md-6 mb-4">
                <label class="form-label"><?php esc_html_e('Break Start Time', 'cosy-appointments'); ?></label>
                <input type="time" class="form-control" id="break_start_time">
                <div id="break_start_time_badge" class="mt-1" style="font-size: 0.8rem; font-weight: 600; color: #a44390; display: none;"></div>
            </div>
            <div class="col-md-6 mb-4">
                <label class="form-label"><?php esc_html_e('Break End Time', 'cosy-appointments'); ?></label>
                <input type="time" class="form-control" id="break_end_time">
                <div id="break_end_time_badge" class="mt-1" style="font-size: 0.8rem; font-weight: 600; color: #a44390; display: none;"></div>
            </div>
        </div>

        <!-- Apply to Multiple Days Options -->
        <div class="mb-4" id="apply_days_container" style="display: none; background: #fdfafc; border: 1px dashed rgba(164, 67, 144, 0.2); padding: 15px 20px; border-radius: 12px;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label mb-0 fw-semibold text-dark" style="font-size: 0.95rem;"><?php esc_html_e('Apply this same schedule to:', 'cosy-appointments'); ?></label>
                <div>
                    <button type="button" class="btn btn-sm py-0 px-2 btn-link text-decoration-none" id="select_all_weekdays" style="color: #a44390; font-size: 0.8rem; font-weight: 600;"><?php esc_html_e('Weekdays (Mon-Fri)', 'cosy-appointments'); ?></button>
                    <span class="text-muted" style="font-size: 0.8rem;">|</span>
                    <button type="button" class="btn btn-sm py-0 px-2 btn-link text-decoration-none" id="select_all_days" style="color: #a44390; font-size: 0.8rem; font-weight: 600;"><?php esc_html_e('All Days', 'cosy-appointments'); ?></button>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-3 mt-2">
                <?php
                $days_of_week = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                foreach ($days_of_week as $d) {
                    ?>
                    <div class="form-check">
                        <input class="form-check-input apply-day-checkbox" type="checkbox" id="apply_day_<?php echo esc_attr($d); ?>" value="<?php echo esc_attr($d); ?>">
                        <label class="form-check-label text-secondary" style="font-size: 0.9rem;" for="apply_day_<?php echo esc_attr($d); ?>"><?php echo esc_html($d); ?></label>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>

        <script>
            /**
             * window.savedAvailability
             * 
             * This global object holds the provider's schedule for all days.
             * It's used to auto-fill the form and update the 'Weekly Preview' in real-time.
             */
            window.savedAvailability = <?php echo wp_json_encode($availability); ?> || {};
            if (Array.isArray(window.savedAvailability)) window.savedAvailability = {}; // Ensure it's always an object
        </script>

        <!-- Calendar Preview -->
        <div class="preview-container mt-2">
            <h5><i class="fas fa-eye" style="color: #a44390; font-size: 0.9rem;"></i> <?php esc_html_e('Weekly Preview', 'cosy-appointments'); ?></h5>
            <p class="text-muted small mb-3"><?php esc_html_e('Your selected availability will appear here as slots.', 'cosy-appointments'); ?></p>
            <div class="d-flex flex-wrap gap-3" id="weekly_preview_badges">
                <?php
                foreach ($availability as $day_name => $avail) {
                    if (!empty($avail['start_time']) && !empty($avail['end_time'])) {
                        $s = date("h:i A", strtotime($avail['start_time']));
                        $e = date("h:i A", strtotime($avail['end_time']));
                        $short_day = substr($day_name, 0, 3);
                        echo '<span class="badge availability-badge d-inline-flex align-items-center gap-2" id="avail-badge-' . esc_attr($day_name) . '" style="background: rgba(164, 67, 144, 0.08); color: #a44390; border: 1px solid rgba(164, 67, 144, 0.2); padding: 8px 14px; border-radius: 20px; font-size: 0.88rem; font-weight: 600;">';
                        echo esc_html("$short_day: $s - $e");
                        echo ' <button type="button" class="btn-close remove-availability-day-btn ms-1" data-day="' . esc_attr($day_name) . '" style="font-size: 0.65rem; opacity: 0.7; cursor: pointer;" title="' . esc_attr__('Remove this day\'s availability', 'cosy-appointments') . '"></button>';
                        echo '</span>';
                    }
                }
                ?>
            </div>
        </div>

        <!-- Action Button -->
        <div class="text-center mt-4">
            <button class="btn btn-primary custom-btn" id="save_availability_btn"><?php esc_html_e('Save Availability', 'cosy-appointments'); ?></button>
        </div>
    </div>
</div>