<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <h3 class="text-dark">🚫 Non Working Days</h3>
        <p class="text-muted">Mark your holidays or off days below.</p>

        <!-- Holiday List -->
        <ul class="list-group mb-3">
            <li class="list-group-item d-flex justify-content-between align-items-center">
                01 Jan 2026 - New Year
                <span class="badge bg-dark">Holiday</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                26 Jan 2026 - Republic Day
                <span class="badge bg-dark">Holiday</span>
            </li>
        </ul>

        <!-- Add Holiday Button -->
        <div class="text-center">
            <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addHolidayModal">
                ➕ Add Holiday
            </button>
        </div>
    </div>
</div>

<!-- Add Holiday Modal -->
<div class="modal fade" id="addHolidayModal" tabindex="-1" aria-labelledby="addHolidayModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="addHolidayModalLabel">Add Non Working Day</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="holiday_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason / Occasion</label>
                        <input type="text" name="holiday_reason" class="form-control" placeholder="e.g. Independence Day" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_holiday" class="btn btn-dark">Save Holiday</button>
                </div>
            </form>
        </div>
    </div>
</div>