<div class="card cosy-orders-card mb-4">
    <div class="card-body p-0">
        <div class="d-flex align-items-center gap-3 mb-2">
            <div class="header-icon-badge" style="background: rgba(164, 67, 144, 0.1); width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-box-open" style="color: #a44390; font-size: 1.2rem;"></i>
            </div>
            <h3 class="mb-0">Orders Management</h3>
        </div>
        <p class="text-muted mb-4" style="margin-left: 58px;">Track and manage your customer bookings and order status.</p>

        <!-- Search & Filter -->
        <div class="row mb-4 gx-3">
            <div class="col-md-6">
                <div class="position-relative">
                    <i class="fas fa-search position-absolute" style="top: 13px; left: 18px; color: #94a3b8; font-size: 0.9rem;"></i>
                    <input type="text" class="form-control" style="padding-left: 45px !important;" placeholder="Search by Order ID or Customer...">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select">
                    <option value="">Filter by Status</option>
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div class="col-md-3 text-end">
                <button class="btn btn-primary custom-btn w-100"><i class="fas fa-file-export me-2"></i> Export Orders</button>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>#Order ID</th>
                        <th>Customer</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Example Row -->
                    <tr>
                        <td class="fw-bold text-dark">#1001</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.8rem; font-weight: 600; color: #a44390;">RS</div>
                                Rahul Sharma
                            </div>
                        </td>
                        <td><span class="badge bg-light text-dark border">Haircut</span></td>
                        <td>01 Jan 2026</td>
                        <td><span class="badge badge-pending"><i class="fas fa-clock"></i> Pending</span></td>
                        <td>
                            <div class="d-flex gap-2">
                                <button class="btn-action bg-success text-white" title="Mark Completed"><i class="fas fa-check"></i></button>
                                <button class="btn-action bg-danger text-white" title="Cancel Order"><i class="fas fa-times"></i></button>
                                <button class="btn-action btn-view" data-bs-toggle="modal" data-bs-target="#orderDetailsModal" title="View Details"><i class="fas fa-eye"></i></button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-dark">#1002</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.8rem; font-weight: 600; color: #a44390;">PP</div>
                                Priya Patel
                            </div>
                        </td>
                        <td><span class="badge bg-light text-dark border">Massage Therapy</span></td>
                        <td>02 Jan 2026</td>
                        <td><span class="badge badge-completed"><i class="fas fa-check-circle"></i> Completed</span></td>
                        <td>
                            <div class="d-flex gap-2">
                                <button class="btn-action btn-view" data-bs-toggle="modal" data-bs-target="#orderDetailsModal" title="View Details"><i class="fas fa-eye"></i></button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-dark">#1003</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.8rem; font-weight: 600; color: #a44390;">AV</div>
                                Amit Verma
                            </div>
                        </td>
                        <td><span class="badge bg-light text-dark border">Facial Treatment</span></td>
                        <td>03 Jan 2026</td>
                        <td><span class="badge badge-cancelled"><i class="fas fa-times-circle"></i> Cancelled</span></td>
                        <td>
                            <div class="d-flex gap-2">
                                <button class="btn-action btn-view" data-bs-toggle="modal" data-bs-target="#orderDetailsModal" title="View Details"><i class="fas fa-eye"></i></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <nav aria-label="Orders pagination" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item disabled"><a class="page-link" href="#"><i class="fas fa-chevron-left"></i></a></li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item"><a class="page-link" href="#"><i class="fas fa-chevron-right"></i></a></li>
            </ul>
        </nav>
    </div>
</div>

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content cosy-modal-content shadow-lg">
            <div class="modal-header cosy-modal-header">
                <h5 class="modal-title fw-bold">Order Details - #1001</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
                            <h6 class="fw-bold mb-3 text-dark"><i class="fas fa-concierge-bell me-2 text-primary"></i> Service Details</h6>
                            <p class="mb-1 fw-bold">Haircut</p>
                            <p class="mb-1 text-muted small">Duration: 1 Hour</p>
                            <p class="mb-0 fw-bold text-primary">₹500.00</p>
                        </div>
                    </div>
                </div>

                <div class="mt-4 p-3 rounded-4 border">
                    <h6 class="fw-bold mb-2">Current Status</h6>
                    <span class="badge badge-pending"><i class="fas fa-clock"></i> Pending</span>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-success rounded-4 px-4 py-2 fw-bold">Mark Completed</button>
                <button type="button" class="btn btn-danger rounded-4 px-4 py-2 fw-bold">Cancel Order</button>
            </div>
        </div>
    </div>
</div>