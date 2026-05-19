<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <h3 class="text-danger">📅 Non‑Working Days</h3>
        <p class="text-muted">Mark days when you are unavailable for appointments.</p>

        <!-- Date Picker -->
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Select Date</label>
                <input type="date" id="nonWorkingDate" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Reason (optional)</label>
                <input type="text" id="nonWorkingReason" class="form-control" placeholder="Holiday, Personal, etc.">
            </div>
        </div>

        <!-- Add Button -->
        <button class="btn btn-danger mb-3" id="add_non_working_day_btn">
            <i class="bi bi-plus-circle"></i> Add Non‑Working Day
        </button>

        <!-- Selected Days Counter -->
        <div class="alert alert-info" id="daysCount">No non‑working days added yet.</div>

        <!-- Non‑Working Days Table -->
        <table class="table table-bordered align-middle" id="daysTable">
            <thead class="table-danger">
                <tr>
                    <th>Date</th>
                    <th>Reason</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- Dynamic rows will appear here -->
            </tbody>
        </table>
    </div>
</div>