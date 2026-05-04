<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <h3 class="text-info">📅 Availability</h3>
        <p class="text-muted">Set your working hours and available slots.</p>

        <!-- Weekday Availability -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Day</label>
                <select class="form-select">
                    <option value="">--Select Day--</option>
                    <option>Monday</option>
                    <option>Tuesday</option>
                    <option>Wednesday</option>
                    <option>Thursday</option>
                    <option>Friday</option>
                    <option>Saturday</option>
                    <option>Sunday</option>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label fw-bold">Start Time</label>
                <input type="time" class="form-control">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label fw-bold">End Time</label>
                <input type="time" class="form-control">
            </div>
        </div>

        <!-- Slot Duration -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Slot Duration</label>
                <select class="form-select">
                    <option value="30">30 Minutes</option>
                    <option value="60">1 Hour</option>
                    <option value="90">1.5 Hours</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Break Between Slots</label>
                <select class="form-select">
                    <option value="0">No Break</option>
                    <option value="10">10 Minutes</option>
                    <option value="15">15 Minutes</option>
                    <option value="30">30 Minutes</option>
                </select>
            </div>
        </div>

        <!-- Calendar Preview -->
        <div class="border rounded p-3 mt-4 bg-light">
            <h5 class="text-info">🗓 Weekly Preview</h5>
            <p class="text-muted">Your selected availability will appear here.</p>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge bg-info">Mon: 9:00 AM - 5:00 PM</span>
                <span class="badge bg-info">Tue: 10:00 AM - 4:00 PM</span>
                <!-- dynamically generated slots -->
            </div>
        </div>

        <!-- Action Button -->
        <div class="text-center mt-4">
            <button class="btn btn-info px-4">Save Availability</button>
        </div>
    </div>
</div>