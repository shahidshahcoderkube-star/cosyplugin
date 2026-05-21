<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <h3><?php esc_html_e('Non-Working Days', 'cosy-appointments'); ?></h3>
        <p class="text-muted"><?php esc_html_e('Mark days when you are unavailable for appointments.', 'cosy-appointments'); ?></p>

        <!-- Date Picker -->
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label"><?php esc_html_e('Select Date', 'cosy-appointments'); ?></label>
                <input type="date" id="nonWorkingDate" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label"><?php esc_html_e('Reason (optional)', 'cosy-appointments'); ?></label>
                <input type="text" id="nonWorkingReason" class="form-control" placeholder="<?php esc_attr_e('Holiday, Personal, etc.', 'cosy-appointments'); ?>">
            </div>
        </div>

        <!-- Add Button -->
        <button class="btn btn-danger mb-3" id="add_non_working_day_btn">
            <i class="bi bi-plus-circle"></i> <?php esc_html_e('Add Non-Working Day', 'cosy-appointments'); ?>
        </button>

        <!-- Selected Days Counter -->
        <div class="alert alert-info" id="daysCount"><?php esc_html_e('No non-working days added yet.', 'cosy-appointments'); ?></div>

        <!-- Non‑Working Days Table -->
        <table class="table table-bordered align-middle" id="daysTable">
            <thead class="table-danger">
                <tr>
                    <th><?php esc_html_e('Date', 'cosy-appointments'); ?></th>
                    <th><?php esc_html_e('Reason', 'cosy-appointments'); ?></th>
                    <th><?php esc_html_e('Action', 'cosy-appointments'); ?></th>
                </tr>
            </thead>
            <tbody>
                <!-- Dynamic rows will appear here -->
            </tbody>
        </table>
    </div>
</div>