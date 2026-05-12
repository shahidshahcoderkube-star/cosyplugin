<style>
.cosy-holidays-card {
    background: #ffffff;
    border-radius: 20px !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05) !important;
    border: none !important;
    padding: 30px;
}

.cosy-holidays-card h3 {
    font-family: 'Poppins', sans-serif;
    font-weight: 700;
    font-size: 1.5rem;
    color: #1e293b;
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.cosy-holidays-card .holiday-item {
    background: #f8fafc;
    border: 1.5px solid #f1f5f9;
    border-radius: 15px;
    padding: 15px 20px;
    margin-bottom: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.cosy-holidays-card .holiday-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.cosy-holidays-card .holiday-info i {
    color: #a44390;
}

.cosy-holidays-card .holiday-date {
    font-weight: 600;
    color: #1e293b;
}

.cosy-holidays-card .holiday-badge {
    background: rgba(164, 67, 144, 0.1) !important;
    color: #a44390 !important;
    padding: 6px 12px !important;
    border-radius: 8px !important;
    font-weight: 600;
}

.cosy-holidays-card .custom-btn {
    background: #a44390 !important;
    border: none !important;
    border-radius: 12px !important;
    padding: 12px 40px !important;
    font-weight: 600;
    color: #fff !important;
}

/* MODAL FIXES - COMPACT & CENTERED */
.cosy-modal-content {
    border-radius: 20px !important;
    border: none !important;
    background: #ffffff !important;
    display: block !important;
    overflow: visible !important;
    box-shadow: 0 25px 60px rgba(0,0,0,0.3) !important;
}

.cosy-modal-header {
    background: #a44390 !important;
    color: #ffffff !important;
    border-radius: 20px 20px 0 0 !important;
    padding: 20px 25px !important;
    border: none !important;
}

.cosy-modal-body {
    padding: 25px 30px !important;
    background: #ffffff !important;
    border-radius: 0 0 20px 20px !important;
}

.save-holiday-btn {
    background: #a44390 !important;
    color: #ffffff !important;
    border: none !important;
    border-radius: 12px !important;
    padding: 12px 40px !important;
    font-weight: 700 !important;
    font-size: 0.95rem !important;
    box-shadow: 0 6px 15px rgba(164, 67, 144, 0.2) !important;
    transition: all 0.3s ease;
}

.save-holiday-btn:hover {
    transform: translateY(-2px);
    background: #833573 !important;
}
</style>

<div class="card cosy-holidays-card mb-4">
    <div class="card-body p-0">
        <div class="d-flex align-items-center gap-3 mb-2">
            <div class="header-icon-badge" style="background: rgba(164, 67, 144, 0.1); width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-calendar-times" style="color: #a44390; font-size: 1.2rem;"></i>
            </div>
            <h3 class="mb-0">Non Working Days</h3>
        </div>
        <p class="text-muted mb-4" style="margin-left: 58px;">Mark your holidays or off days below.</p>

        <!-- Holiday List -->
        <div class="holiday-list mt-2">
            <div class="holiday-item">
                <div class="holiday-info">
                    <i class="fas fa-calendar-day"></i>
                    <div>
                        <span class="holiday-date">01 Jan 2026</span>
                        <span class="mx-2 text-muted">|</span>
                        <span class="holiday-reason text-muted small">New Year</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-sm btn-outline-danger border-0 rounded-circle" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;" title="Remove Holiday">
                        <i class="fas fa-trash-alt" style="font-size: 0.85rem;"></i>
                    </button>
                    <span class="badge holiday-badge">Holiday</span>
                </div>
            </div>
            
            <div class="holiday-item">
                <div class="holiday-info">
                    <i class="fas fa-calendar-day"></i>
                    <div>
                        <span class="holiday-date">26 Jan 2026</span>
                        <span class="mx-2 text-muted">|</span>
                        <span class="holiday-reason text-muted small">Republic Day</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-sm btn-outline-danger border-0 rounded-circle" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;" title="Remove Holiday">
                        <i class="fas fa-trash-alt" style="font-size: 0.85rem;"></i>
                    </button>
                    <span class="badge holiday-badge">Holiday</span>
                </div>
            </div>
        </div>

        <!-- Add Holiday Button -->
        <div class="text-center mt-4">
            <button class="btn custom-btn" data-bs-toggle="modal" data-bs-target="#addHolidayModal">
                <i class="fas fa-plus-circle me-2"></i> Add Holiday
            </button>
        </div>
    </div>
</div>

<!-- Add Holiday Modal -->
<div class="modal fade" id="addHolidayModal" tabindex="-1" aria-hidden="true" style="z-index: 99999;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content cosy-modal-content">
            <div class="modal-header cosy-modal-header">
                <h5 class="modal-title fw-bold text-white mb-0">Add Non Working Day</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="cosy-modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold d-block mb-2">Date</label>
                        <input type="date" name="holiday_date" class="form-control" style="border-radius: 12px; padding: 12px; background: #f8fafc; border: 1.5px solid #e2e8f0;" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold d-block mb-2">Reason / Occasion</label>
                        <input type="text" name="holiday_reason" class="form-control" style="border-radius: 12px; padding: 12px; background: #f8fafc; border: 1.5px solid #e2e8f0;" placeholder="e.g. Independence Day" required>
                    </div>

                    <!-- SAVE BUTTON -->
                    <div class="text-center mt-2">
                        <button type="submit" name="add_holiday" class="btn save-holiday-btn">SAVE HOLIDAY</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>