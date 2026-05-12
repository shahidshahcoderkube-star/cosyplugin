<style>
.cosy-invoices-card {
    background: #ffffff;
    border-radius: 20px !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05) !important;
    border: none !important;
    padding: 30px;
}

.cosy-invoices-card h3 {
    font-family: 'Poppins', sans-serif;
    font-weight: 700;
    font-size: 1.5rem;
    color: #1e293b;
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.cosy-invoices-card .table {
    border-collapse: separate;
    border-spacing: 0 10px;
}

.cosy-invoices-card .table thead th {
    background: #f1f5f9 !important;
    color: #475569 !important;
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
    padding: 15px !important;
    border: none !important;
}

.cosy-invoices-card .table tbody tr {
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.02);
}

.cosy-invoices-card .table tbody td {
    background: #fff !important;
    padding: 15px !important;
    border-top: 1px solid #f1f5f9 !important;
    border-bottom: 1px solid #f1f5f9 !important;
}

.cosy-invoices-card .table tbody td:first-child {
    border-left: 1px solid #f1f5f9 !important;
    border-top-left-radius: 12px;
    border-bottom-left-radius: 12px;
}

.cosy-invoices-card .table tbody td:last-child {
    border-right: 1px solid #f1f5f9 !important;
    border-top-right-radius: 12px;
    border-bottom-right-radius: 12px;
}

.cosy-invoices-card .badge {
    padding: 8px 12px !important;
    border-radius: 8px !important;
    font-weight: 600 !important;
    font-size: 0.75rem !important;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.cosy-invoices-card .badge-paid { background: rgba(34, 197, 94, 0.1) !important; color: #22c55e !important; }
.cosy-invoices-card .badge-pending { background: rgba(164, 67, 144, 0.1) !important; color: #a44390 !important; }

.cosy-invoices-card .btn-action {
    width: 35px !important;
    height: 35px !important;
    min-width: 35px !important;
    min-height: 35px !important;
    border-radius: 50% !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    border: none !important;
    transition: all 0.2s ease;
    flex-shrink: 0;
    padding: 0 !important;
}

.cosy-invoices-card .btn-view { background: #f1f5f9 !important; color: #64748b !important; }
.cosy-invoices-card .btn-download { background: rgba(164, 67, 144, 0.1) !important; color: #a44390 !important; }

.cosy-invoices-card .custom-btn {
    background: linear-gradient(135deg, #a44390 0%, #833573 100%) !important;
    border: none !important;
    border-radius: 12px !important;
    padding: 14px 45px !important;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(164, 67, 144, 0.2) !important;
}

.cosy-modal-content {
    border-radius: 20px !important;
    border: none !important;
    overflow: hidden;
}

.cosy-modal-header {
    background: #a44390 !important;
    color: #fff !important;
    padding: 20px 30px !important;
}
</style>

<div class="card cosy-invoices-card mb-4">
    <div class="card-body p-0">
        <div class="d-flex align-items-center gap-3 mb-2">
            <div class="header-icon-badge" style="background: rgba(164, 67, 144, 0.1); width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-file-invoice-dollar" style="color: #a44390; font-size: 1.2rem;"></i>
            </div>
            <h3 class="mb-0">Invoices</h3>
        </div>
        <p class="text-muted mb-4" style="margin-left: 58px;">Generate, view and manage your invoices below.</p>

        <!-- Invoice Table -->
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>#Invoice ID</th>
                        <th>Customer</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-bold text-dark">INV-001</td>
                        <td>Rahul Sharma</td>
                        <td><span class="badge bg-light text-dark border">Haircut</span></td>
                        <td>01 Jan 2026</td>
                        <td class="fw-bold">₹500</td>
                        <td><span class="badge badge-paid"><i class="fas fa-check-circle"></i> Paid</span></td>
                        <td>
                            <div class="d-flex gap-2">
                                <button class="btn-action btn-view" title="View" data-bs-toggle="modal" data-bs-target="#invoiceModal"><i class="fas fa-eye"></i></button>
                                <button class="btn-action btn-download" title="Download"><i class="fas fa-download"></i></button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-dark">INV-002</td>
                        <td>Priya Patel</td>
                        <td><span class="badge bg-light text-dark border">Massage Therapy</span></td>
                        <td>02 Jan 2026</td>
                        <td class="fw-bold">₹1200</td>
                        <td><span class="badge badge-pending"><i class="fas fa-clock"></i> Pending</span></td>
                        <td>
                            <div class="d-flex gap-2">
                                <button class="btn-action btn-view" title="View" data-bs-toggle="modal" data-bs-target="#invoiceModal"><i class="fas fa-eye"></i></button>
                                <button class="btn-action btn-download" title="Download"><i class="fas fa-download"></i></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Create Invoice Button -->
        <div class="text-center mt-4">
            <button class="btn btn-primary custom-btn" data-bs-toggle="modal" data-bs-target="#createInvoiceModal">
                <i class="fas fa-plus-circle me-2"></i> Create Invoice
            </button>
        </div>
    </div>
</div>

<!-- Invoice Details Modal -->
<div class="modal fade" id="invoiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content cosy-modal-content shadow-lg">
            <div class="modal-header cosy-modal-header">
                <h5 class="modal-title fw-bold">Invoice Details - INV-001</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 bg-light">
                            <h6 class="fw-bold mb-3 text-dark"><i class="fas fa-user me-2 text-primary"></i> Customer Info</h6>
                            <p class="mb-1 fw-bold">Rahul Sharma</p>
                            <p class="mb-1 text-muted small">rahul@example.com</p>
                            <p class="mb-0 text-muted small">+91 9876543210</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 bg-light">
                            <h6 class="fw-bold mb-3 text-dark"><i class="fas fa-info-circle me-2 text-primary"></i> Billing Details</h6>
                            <p class="mb-1 fw-bold">Service: Haircut</p>
                            <p class="mb-1 text-muted small">Date: 01 Jan 2026</p>
                            <p class="mb-0 fw-bold text-primary">Amount: ₹500</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button class="btn btn-primary w-100 rounded-4 py-2 fw-bold" style="background: #a44390; border: none;">Download PDF</button>
            </div>
        </div>
    </div>
</div>

<!-- Create Invoice Modal -->
<div class="modal fade" id="createInvoiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content cosy-modal-content shadow-lg">
            <div class="modal-header cosy-modal-header">
                <h5 class="modal-title fw-bold text-white mb-0">Create New Invoice</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body p-4 pb-0">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold small">Customer Name</label>
                            <input type="text" name="customer_name" class="form-control" style="border-radius: 12px; padding: 10px; background: #f8fafc; border: 1.5px solid #e2e8f0;" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold small">Service</label>
                            <input type="text" name="service_name" class="form-control" style="border-radius: 12px; padding: 10px; background: #f8fafc; border: 1.5px solid #e2e8f0;" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Date</label>
                            <input type="date" name="invoice_date" class="form-control" style="border-radius: 12px; padding: 10px; background: #f8fafc; border: 1.5px solid #e2e8f0;" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Amount</label>
                            <input type="number" name="invoice_amount" class="form-control" style="border-radius: 12px; padding: 10px; background: #f8fafc; border: 1.5px solid #e2e8f0;" required>
                        </div>
                        <div class="col-md-12 mb-4">
                            <label class="form-label fw-bold small">Status</label>
                            <select name="invoice_status" class="form-select" style="border-radius: 12px; padding: 10px; background: #f8fafc; border: 1.5px solid #e2e8f0;">
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <!-- SAVE BUTTON -->
                    <div class="text-center mb-4">
                        <button type="submit" name="create_invoice" class="btn btn-primary rounded-4 px-5 py-2 fw-bold" style="background: #a44390; border: none; box-shadow: 0 4px 12px rgba(164, 67, 144, 0.2);">Save Invoice</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>