<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <h3 class="text-warning">📦 Orders</h3>
        <p class="text-muted">Manage and track your customer orders below.</p>

        <!-- Search & Filter -->
        <div class="row mb-3">
            <div class="col-md-6">
                <input type="text" class="form-control" placeholder="🔍 Search by Order ID or Customer">
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
                <button class="btn btn-warning">Export Orders</button>
            </div>
        </div>

        <!-- Orders Table -->
        <table class="table table-hover align-middle">
            <thead class="table-warning">
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
                    <td>#1001</td>
                    <td>Rahul Sharma</td>
                    <td>Haircut</td>
                    <td>01 Jan 2026</td>
                    <td><span class="badge bg-info"><i class="bi bi-hourglass-split"></i> Pending</span></td>
                    <td>
                        <button class="btn btn-sm btn-success" title="Mark Completed">
                            <i class="bi bi-check-circle"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" title="Cancel Order">
                            <i class="bi bi-x-circle"></i>
                        </button>
                        <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#orderDetailsModal" title="View Details">
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>#1002</td>
                    <td>Priya Patel</td>
                    <td>Massage Therapy</td>
                    <td>02 Jan 2026</td>
                    <td><span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Completed</span></td>
                    <td>
                        <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#orderDetailsModal" title="View Details">
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>#1003</td>
                    <td>Amit Verma</td>
                    <td>Facial Treatment</td>
                    <td>03 Jan 2026</td>
                    <td><span class="badge bg-danger"><i class="bi bi-x-circle-fill"></i> Cancelled</span></td>
                    <td>
                        <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#orderDetailsModal" title="View Details">
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Pagination -->
        <nav aria-label="Orders pagination">
            <ul class="pagination justify-content-center">
                <li class="page-item disabled"><a class="page-link">Previous</a></li>
                <li class="page-item active"><a class="page-link">1</a></li>
                <li class="page-item"><a class="page-link">2</a></li>
                <li class="page-item"><a class="page-link">3</a></li>
                <li class="page-item"><a class="page-link">Next</a></li>
            </ul>
        </nav>
    </div>
</div>

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="orderDetailsModalLabel">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>Customer Information</h6>
                <p>Name: Rahul Sharma<br>Email: rahul@example.com<br>Phone: +91 9876543210</p>

                <h6>Service Information</h6>
                <p>Service: Haircut<br>Duration: 1 Hour<br>Price: ₹500</p>

                <h6>Payment Status</h6>
                <p><span class="badge bg-info">Pending</span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success">Mark Completed</button>
                <button type="button" class="btn btn-danger">Cancel Order</button>
            </div>
        </div>
    </div>
</div>