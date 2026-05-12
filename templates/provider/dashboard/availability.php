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
    box-shadow: 0 2px 5px rgba(0,0,0,0.02);
}

.cosy-availability-card .custom-btn {
    background: linear-gradient(135deg, #a44390 0%, #833573 100%) !important;
    border: none !important;
    border-radius: 12px !important;
    padding: 14px 45px !important;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(164, 67, 144, 0.2) !important;
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
            <div class="col-md-3 mb-4">
                <label class="form-label">Start Time</label>
                <input type="time" class="form-control">
            </div>
            <div class="col-md-3 mb-4">
                <label class="form-label">End Time</label>
                <input type="time" class="form-control">
            </div>
        </div>

        <!-- Slot Duration -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <label class="form-label">Slot Duration</label>
                <select class="form-select">
                    <option value="10">10 Minutes</option>
                    <option value="20">20 Minutes</option>
                    <option value="30" selected>30 Minutes</option>
                    <option value="40">40 Minutes</option>
                    <option value="50">50 Minutes</option>
                    <option value="60">60 Minutes</option>
                </select>
            </div>
            <div class="col-md-6 mb-4">
                <label class="form-label">Break Between Slots</label>
                <select class="form-select">
                    <option value="0">No Break</option>
                    <option value="10">10 Minutes</option>
                    <option value="15">15 Minutes</option>
                    <option value="30">30 Minutes</option>
                </select>
            </div>
        </div>

        <!-- Calendar Preview -->
        <div class="preview-container mt-2">
            <h5><i class="fas fa-eye" style="color: #a44390; font-size: 0.9rem;"></i> Weekly Preview</h5>
            <p class="text-muted small mb-3">Your selected availability will appear here as slots.</p>
            <div class="d-flex flex-wrap gap-3">
                <span class="badge availability-badge">Mon: 9:00 AM - 5:00 PM</span>
                <span class="badge availability-badge">Tue: 10:00 AM - 4:00 PM</span>
                <!-- dynamically generated slots -->
            </div>
        </div>

        <!-- Action Button -->
        <div class="text-center mt-4">
            <button class="btn btn-primary custom-btn">Save Availability</button>
        </div>
    </div>
</div>