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
<style>
    .cosy-availability-card {
        background: #ffffff;
        border-radius: 20px !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05) !important;
        border: none !important;
        padding: 30px;
    }

    .cosy-availability-card h3 {
        font-family: 'Poppins', sans-serif;
        font-weight: 700;
        font-size: 1.5rem;
        color: #1e293b;
        margin-bottom: 5px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .cosy-availability-card .form-label {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        color: #475569;
        font-size: 0.9rem;
        margin-bottom: 10px;
    }

    .cosy-availability-card .form-control,
    .cosy-availability-card .form-select {
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 12px !important;
        background-color: #f8fafc !important;
        font-family: 'Poppins', sans-serif;
        font-size: 0.95rem;
        padding: 10px 15px !important;
        transition: all 0.3s ease;
    }

    .cosy-availability-card .form-control:focus,
    .cosy-availability-card .form-select:focus {
        border-color: #a44390 !important;
        background-color: #fff !important;
        box-shadow: 0 0 0 4px rgba(164, 67, 144, 0.1) !important;
    }

    .cosy-availability-card .preview-container {
        background: #f8fafc;
        border-radius: 15px;
        border: 1px solid #e2e8f0;
        padding: 25px;
    }

    .cosy-availability-card .preview-container h5 {
        font-family: 'Poppins', sans-serif;
        font-weight: 700;
        font-size: 1rem;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 15px;
    }

    .cosy-availability-card .availability-badge {
        background: #fff !important;
        color: #a44390 !important;
        border: 1px solid rgba(164, 67, 144, 0.2) !important;
        padding: 8px 15px !important;
        border-radius: 10px !important;
        font-weight: 600;
        font-size: 0.85rem;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
    }

    .cosy-availability-card .custom-btn {
        background: linear-gradient(135deg, #a44390 0%, #833573 100%) !important;
        border: none !important;
        border-radius: 12px !important;
        padding: 14px 45px !important;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(164, 67, 144, 0.2) !important;
    }

    /* SweetAlert2 Font Customization */
    .swal2-popup {
        font-family: 'Poppins', sans-serif !important;
        border-radius: 20px !important;
    }
    
    .swal2-title {
        font-weight: 700 !important;
        color: #1e293b !important;
    }
    
    .swal2-html-container {
        color: #64748b !important;
    }

    .swal2-confirm,
    .swal2-cancel {
        box-shadow: none !important;
        outline: none !important;
        border: none !important;
        border-radius: 12px !important;
        padding: 10px 25px !important;
        font-weight: 600 !important;
    }

    .swal2-styled:focus {
        box-shadow: none !important;
    }
</style>

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

            /**
             * formatTimeForBadge
             * 
             * Converts 24-hour time (e.g., 14:00) to 12-hour format with AM/PM (e.g., 02:00 PM).
             * This makes the 'Weekly Preview' badges easy to read.
             */
            function formatTimeForBadge(timeStr) {
                if (!timeStr) return '';
                const [hour, minute] = timeStr.split(':');
                const h = parseInt(hour);
                const ampm = h >= 12 ? 'PM' : 'AM';
                const h12 = h % 12 || 12;
                return `${h12}:${minute} ${ampm}`;
            }

            /**
             * renderAvailabilityBadges
             * 
             * This function updates the 'Weekly Preview' section.
             * It clears the current badges and re-creates them based on the 'savedAvailability' object.
             * Called whenever a new schedule is saved.
             */
            function renderAvailabilityBadges() {
                const container = jQuery('#weekly_preview_badges');
                if (!container.length) return;
                
                container.empty();
                const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                
                days.forEach(day => {
                    const avail = window.savedAvailability[day];
                    if (avail && avail.start_time && avail.end_time) {
                        const s = formatTimeForBadge(avail.start_time);
                        const e = formatTimeForBadge(avail.end_time);
                        const shortDay = day.substring(0, 3);
                        container.append(`<span class="badge availability-badge">${shortDay}: ${s} - ${e}</span>`);
                    }
                });
            }

            jQuery(document).ready(function($) {
                /**
                 * Day Selection Handler
                 * 
                 * When a provider selects a day from the dropdown, this script checks if
                 * there's already a saved schedule for that day and fills the inputs automatically.
                 */
                $('#availability_day').on('change', function() {
                    const day = $(this).val();
                    if (!day) return;

                    const data = window.savedAvailability[day];
                    if (data && typeof data === 'object') {
                        $('#start_time').val(data.start_time || '');
                        $('#end_time').val(data.end_time || '');
                        $('#slot_duration').val(data.slot_duration || '10');
                        $('#break_start_time').val(data.break_start || '');
                        $('#break_end_time').val(data.break_end || '');
                    } else {
                        // Clear fields if no schedule exists for the selected day
                        $('#start_time').val('');
                        $('#end_time').val('');
                        $('#slot_duration').val('10');
                        $('#break_start_time').val('');
                        $('#break_end_time').val('');
                    }
                });
            });
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