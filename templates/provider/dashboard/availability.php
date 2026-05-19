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
            <h3 class="mb-0">Availability</h3>
        </div>
        <p class="text-muted mb-4" style="margin-left: 58px;">Set your working hours and available slots for booking.</p>

        <!-- Weekday Availability -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <label class="form-label">Day</label>
                <select class="form-select" id="availability_day">
                    <option value="">Select Day</option>
                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                    <option value="Saturday">Saturday</option>
                    <option value="Sunday">Sunday</option>
                </select>
            </div>
            <div class="col-md-3 mb-4">
                <label class="form-label">Start Time</label>
                <input type="time" class="form-control" id="start_time">
            </div>
            <div class="col-md-3 mb-4">
                <label class="form-label">End Time</label>
                <input type="time" class="form-control" id="end_time">
            </div>
        </div>

        <!-- Slot Duration -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <label class="form-label">Slot Duration</label>
                <select class="form-select" id="slot_duration">
                    <option value="10" selected>10 Minutes</option>
                    <option value="20">20 Minutes</option>
                    <option value="30">30 Minutes</option>
                    <option value="40">40 Minutes</option>
                    <option value="50">50 Minutes</option>
                    <option value="60">60 Minutes</option>
                </select>
            </div>
            <div class="col-md-3 mb-4">
                <label class="form-label">Break Start Time</label>
                <input type="time" class="form-control" id="break_start_time">
            </div>
            <div class="col-md-3 mb-4">
                <label class="form-label">Break End Time</label>
                <input type="time" class="form-control" id="break_end_time">
            </div>
        </div>

        <script>
            /**
             * window.savedAvailability
             * 
             * This global object holds the provider's schedule for all days.
             * It's used to auto-fill the form and update the 'Weekly Preview' in real-time.
             */
            window.savedAvailability = <?php echo json_encode($availability); ?> || {};
            if (Array.isArray(window.savedAvailability)) window.savedAvailability = {}; // Ensure it's always an object
        </script>

        <!-- Calendar Preview -->
        <div class="preview-container mt-2">
            <h5><i class="fas fa-eye" style="color: #a44390; font-size: 0.9rem;"></i> Weekly Preview</h5>
            <p class="text-muted small mb-3">Your selected availability will appear here as slots.</p>
            <div class="d-flex flex-wrap gap-3" id="weekly_preview_badges">
                <?php
                foreach ($availability as $day_name => $avail) {
                    if (!empty($avail['start_time']) && !empty($avail['end_time'])) {
                        $s = date("h:i A", strtotime($avail['start_time']));
                        $e = date("h:i A", strtotime($avail['end_time']));
                        $short_day = substr($day_name, 0, 3);
                        echo '<span class="badge availability-badge">' . esc_html("$short_day: $s - $e") . '</span>';
                    }
                }
                ?>
            </div>
        </div>

        <!-- Action Button -->
        <div class="text-center mt-4">
            <button class="btn btn-primary custom-btn" id="save_availability_btn">Save Availability</button>
        </div>
    </div>
</div>