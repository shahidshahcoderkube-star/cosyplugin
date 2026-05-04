<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <h3 class="text-primary">💳 Invoices</h3>
        <p class="text-muted">Generate, view and manage your invoices below.</p>

        <!-- Invoice Table -->
        <table class="table table-hover align-middle">
            <thead class="table-primary">
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
                    <td>INV-001</td>
                    <td>Rahul Sharma</td>
                    <td>Haircut</td>
                    <td>01 Jan 2026</td>
                    <td>₹500</td>
                    <td><span class="badge bg-success">Paid</span></td>
                    <td>
                        <button class="btn btn-sm btn-secondary" title="View" data-bs-toggle="modal" data-bs-target="#invoiceModal">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-primary" title="Download">
                            <i class="bi bi-download"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>INV-002</td>
                    <td>Priya Patel</td>
                    <td>Massage Therapy</td>
                    <td>02 Jan 2026</td>
                    <td>₹1200</td>
                    <td><span class="badge bg-warning text-dark">Pending</span></td>
                    <td>
                        <button class="btn btn-sm btn-secondary" title="View" data-bs-toggle="modal" data-bs-target="#invoiceModal">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-primary" title="Download">
                            <i class="bi bi-download"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Create Invoice Button -->
        <div class="text-center mt-3">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createInvoiceModal">
                ➕ Create Invoice
            </button>
        </div>
    </div>
</div>

<!-- Invoice Details Modal -->
<div class="modal fade" id="invoiceModal" tabindex="-1" aria-labelledby="invoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="invoiceModalLabel">Invoice Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>Customer Information</h6>
                <p>Name: Rahul Sharma<br>Email: rahul@example.com<br>Phone: +91 9876543210</p>

                <h6>Service Information</h6>
                <p>Service: Haircut<br>Date: 01 Jan 2026<br>Amount: ₹500</p>

                <h6>Status</h6>
                <p><span class="badge bg-success">Paid</span></p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary">Download PDF</button>
            </div>
        </div>
    </div>
</div>

<!-- Create Invoice Modal -->
<div class="modal fade" id="createInvoiceModal" tabindex="-1" aria-labelledby="createInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="createInvoiceModalLabel">Create New Invoice</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Customer Name</label>
                        <input type="text" name="customer_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Service</label>
                        <input type="text" name="service_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="invoice_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" name="invoice_amount" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="invoice_status" class="form-select">
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="create_invoice" class="btn btn-primary">Save Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>