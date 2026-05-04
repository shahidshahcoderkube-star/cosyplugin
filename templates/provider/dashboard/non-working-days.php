<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

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
        <button class="btn btn-danger mb-3" onclick="addNonWorkingDay()">
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

<script>
    function addNonWorkingDay() {
        const date = document.getElementById('nonWorkingDate').value;
        const reason = document.getElementById('nonWorkingReason').value || 'N/A';
        const tableBody = document.querySelector('#daysTable tbody');

        if (!date) {
            alert("Please select a date!");
            return;
        }

        // Prevent duplicate dates
        if (document.getElementById('day-' + date)) {
            alert("This date is already marked!");
            return;
        }

        const row = document.createElement('tr');
        row.id = 'day-' + date;
        row.innerHTML = `
    <td>${date}</td>
    <td>${reason}</td>
    <td>
      <button class="btn btn-success btn-sm" title="Update">
        <i class="bi bi-check-circle"></i>
      </button>
      <button class="btn btn-danger btn-sm" title="Remove" onclick="removeDay('${date}')">
        <i class="bi bi-x-circle"></i>
      </button>
    </td>
  `;
        tableBody.appendChild(row);

        updateDaysCount();
    }

    function removeDay(date) {
        const row = document.getElementById('day-' + date);
        if (row) row.remove();
        updateDaysCount();
    }

    function updateDaysCount() {
        const count = document.querySelectorAll('#daysTable tbody tr').length;
        const counter = document.getElementById('daysCount');
        counter.innerHTML = count === 0 ? "No non‑working days added yet." : `❌ ${count} non‑working day(s) marked.`;
    }
</script>