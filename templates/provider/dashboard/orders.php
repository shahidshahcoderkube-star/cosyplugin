<?php
if (!defined('ABSPATH')) {
    exit;
}

$current_provider_id = get_current_user_id();

// Dynamic WP Query to fetch provider appointments
$args = [
    'post_type'      => 'cosy_appointment',
    'posts_per_page' => -1, // Retrieve all appointments
    'post_status'    => 'publish',
    'meta_query'     => [
        [
            'key'     => 'cosy_provider_id',
            'value'   => $current_provider_id,
            'compare' => '='
        ]
    ],
    'orderby'        => 'date',
    'order'          => 'DESC'
];

$appointments_query = new WP_Query($args);
$appointments = $appointments_query->posts;
?>

<div class="card cosy-orders-card mb-4 border-0">
    <div class="card-body p-4">
        <div class="d-flex align-items-center gap-3 mb-2">
            <div class="header-icon-badge">
                <i class="fas fa-box-open"></i>
            </div>
            <h3 class="mb-0 fw-bold">Orders Management</h3>
        </div>
        <p class="text-muted mb-4 cosy-orders-subtitle">Track and manage your customer bookings and order status.</p>

        <!-- Search & Filter -->
        <div class="row mb-4 gx-3">
            <div class="col-md-6 mb-2 mb-md-0">
                <div class="position-relative">
                    <i class="fas fa-search position-absolute search-icon-abs"></i>
                    <input type="text" id="orderSearchInput" class="form-control rounded-4 border-0 bg-light cosy-orders-filter-input" placeholder="Search by Order ID or Customer...">
                </div>
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <select id="orderStatusFilter" class="form-select rounded-4 border-0 bg-light cosy-orders-filter-select">
                    <option value="">Filter by Status</option>
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div class="col-md-3">
                <button id="exportOrdersBtn" class="btn btn-primary custom-btn w-100 rounded-4 fw-bold d-flex align-items-center justify-content-center gap-2 cosy-orders-export-btn">
                    <i class="fas fa-file-export"></i> Export Orders
                </button>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="table-responsive">
            <table class="table align-middle" id="providerOrdersTable">
                <thead>
                    <tr>
                        <th class="pb-3">#Order ID</th>
                        <th class="pb-3">Customer</th>
                        <th class="pb-3">Service</th>
                        <th class="pb-3">Date</th>
                        <th class="pb-3">Status</th>
                        <th class="pb-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($appointments)) : ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <div class="mb-3">
                                    <i class="fas fa-folder-open fa-3x" style="color: #cbd5e1;"></i>
                                </div>
                                <h6 class="fw-bold mb-1">No Orders Found</h6>
                                <p class="small text-muted mb-0">Newly booked orders will appear automatically here.</p>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($appointments as $appt) : 
                            $appt_id = $appt->ID;
                            $customer_name   = get_post_meta($appt_id, 'cosy_customer_name', true);
                            $customer_email  = get_post_meta($appt_id, 'cosy_customer_email', true);
                            $service_name    = get_post_meta($appt_id, 'cosy_service_name', true);
                            $start_date      = get_post_meta($appt_id, 'cosy_start_date', true);
                            $end_date        = get_post_meta($appt_id, 'cosy_end_date', true);
                            $weekly_booking  = get_post_meta($appt_id, 'cosy_weekly_booking', true);
                            $number_of_weeks = get_post_meta($appt_id, 'cosy_number_of_weeks', true);
                            $number_of_slots = get_post_meta($appt_id, 'cosy_number_of_bookings', true);
                            $service_cost    = get_post_meta($appt_id, 'cosy_service_cost', true);
                            $total_payable   = get_post_meta($appt_id, 'cosy_total_payable', true);
                            $booking_status  = get_post_meta($appt_id, 'cosy_booking_status', true);
                            if (empty($booking_status)) {
                                $booking_status = 'pending';
                            }
                            
                            // Generate initials for avatar
                            $initials = '';
                            if (!empty($customer_name)) {
                                $parts = explode(' ', $customer_name);
                                foreach ($parts as $p) {
                                    $initials .= strtoupper(substr($p, 0, 1));
                                }
                                $initials = substr($initials, 0, 2);
                            }
                            if (empty($initials)) {
                                $initials = 'CU';
                            }

                            // Generate status badge HTML
                            $status_badge = '';
                            $badge_class = '';
                            if ($booking_status === 'completed') {
                                $status_badge = '<span class="badge badge-completed"><i class="fas fa-check-circle me-1"></i> Completed</span>';
                                $badge_class = 'completed';
                            } elseif ($booking_status === 'cancelled') {
                                $status_badge = '<span class="badge badge-cancelled"><i class="fas fa-times-circle me-1"></i> Cancelled</span>';
                                $badge_class = 'cancelled';
                            } else {
                                $status_badge = '<span class="badge badge-pending"><i class="fas fa-clock me-1"></i> Pending</span>';
                                $badge_class = 'pending';
                            }
                        ?>
                            <tr class="order-table-row" id="order-row-<?php echo $appt_id; ?>" data-search="<?php echo esc_attr(strtolower("#{$appt_id} {$customer_name}")); ?>" data-status="<?php echo esc_attr($booking_status); ?>">
                                <td class="fw-bold text-dark">#<?php echo $appt_id; ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center order-customer-avatar"><?php echo esc_html($initials); ?></div>
                                        <span class="fw-semibold order-customer-name"><?php echo esc_html($customer_name); ?></span>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark border-0 px-3 py-2 rounded-3 order-service-badge"><?php echo esc_html($service_name); ?></span></td>
                                <td class="order-date-cell"><?php echo esc_html($start_date); ?></td>
                                <td class="status-cell"><?php echo $status_badge; ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <?php if ($booking_status === 'pending') : ?>
                                            <button class="btn-action action-update-status bg-success text-white order-action-btn" 
                                                    data-id="<?php echo $appt_id; ?>" 
                                                    data-status="completed" 
                                                    title="Mark Completed">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn-action action-update-status bg-danger text-white order-action-btn" 
                                                    data-id="<?php echo $appt_id; ?>" 
                                                    data-status="cancelled" 
                                                    title="Cancel Order">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn-action btn-view-order-details bg-light text-secondary order-action-btn" 
                                                data-id="<?php echo $appt_id; ?>"
                                                data-customer="<?php echo esc_attr($customer_name); ?>"
                                                data-email="<?php echo esc_attr($customer_email); ?>"
                                                data-service="<?php echo esc_attr($service_name); ?>"
                                                data-start="<?php echo esc_attr($start_date); ?>"
                                                data-end="<?php echo esc_attr($end_date); ?>"
                                                data-weekly="<?php echo esc_attr($weekly_booking); ?>"
                                                data-weeks="<?php echo esc_attr($number_of_weeks); ?>"
                                                data-slots="<?php echo esc_attr($number_of_slots); ?>"
                                                data-cost="<?php echo esc_attr($service_cost); ?>"
                                                data-total="<?php echo esc_attr($total_payable); ?>"
                                                data-status="<?php echo esc_attr($booking_status); ?>"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#orderDetailsModal" 
                                                title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content cosy-modal-content border-0 shadow-lg">
            <div class="modal-header cosy-modal-header cosy-modal-header-gradient text-white p-4">
                <h5 class="modal-title fw-bold text-white cosy-modal-title" id="modalOrderTitle">Order Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 modal-info-box-primary">
                            <h6 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2 modal-info-title"><i class="fas fa-user text-primary modal-icon-primary"></i> Customer Info</h6>
                            <p class="mb-1 fw-bold text-slate modal-customer-name" id="modalCustomerName"></p>
                            <p class="mb-0 text-muted small" id="modalCustomerEmail"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 bg-light modal-info-box-secondary">
                            <h6 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2 modal-info-title"><i class="fas fa-concierge-bell text-primary modal-icon-primary"></i> Service Details</h6>
                            <p class="mb-1 fw-bold text-slate modal-customer-name" id="modalServiceName"></p>
                            <p class="mb-1 text-muted small modal-small-info" id="modalScheduleInfo"></p>
                            <p class="mb-1 text-muted small modal-small-info" id="modalWeeksInfo"></p>
                            <p class="mb-0 fw-bold text-primary modal-cost-info" id="modalCostInfo"></p>
                        </div>
                    </div>
                </div>

                <div class="mt-4 p-3 rounded-4 border modal-status-box">
                    <h6 class="fw-bold mb-2 text-slate modal-status-title">Current Status</h6>
                    <div id="modalStatusContainer"></div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0 justify-content-end gap-2" id="modalFooterActions">
                <!-- Actions populated dynamically -->
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // 1. Live Search functionality
    $('#orderSearchInput').on('keyup', function() {
        const value = $(this).val().toLowerCase().trim();
        $('#providerOrdersTable tbody tr.order-table-row').filter(function() {
            $(this).toggle($(this).attr('data-search').indexOf(value) > -1);
        });
    });

    // 2. Filter by status
    $('#orderStatusFilter').on('change', function() {
        const val = $(this).val();
        $('#providerOrdersTable tbody tr.order-table-row').filter(function() {
            if (val === '') {
                $(this).show();
            } else {
                $(this).toggle($(this).attr('data-status') === val);
            }
        });
    });

    // 3. Export to CSV functionality
    $('#exportOrdersBtn').on('click', function(e) {
        e.preventDefault();
        let csvContent = "data:text/csv;charset=utf-8,";
        csvContent += "Order ID,Customer,Service,Date,Status\n";

        $('#providerOrdersTable tbody tr.order-table-row').each(function() {
            if ($(this).is(':visible')) {
                const id = $(this).find('td:nth-child(1)').text().replace('#', '');
                const customer = $(this).find('td:nth-child(2)').text().trim();
                const service = $(this).find('td:nth-child(3)').text().trim();
                const date = $(this).find('td:nth-child(4)').text().trim();
                const status = $(this).attr('data-status').toUpperCase();
                csvContent += `"${id}","${customer}","${service}","${date}","${status}"\n`;
            }
        });

        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "provider_orders_" + new Date().toISOString().slice(0,10) + ".csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    // 4. Populate details modal dynamically
    $('.btn-view-order-details').on('click', function() {
        const id = $(this).data('id');
        const customer = $(this).data('customer');
        const email = $(this).data('email');
        const service = $(this).data('service');
        const start = $(this).data('start');
        const end = $(this).data('end');
        const weekly = $(this).data('weekly');
        const weeks = $(this).data('weeks');
        const slots = $(this).data('slots');
        const cost = $(this).data('cost');
        const total = $(this).data('total');
        const status = $(this).data('status');

        $('#modalOrderTitle').text('Order Details - #' + id);
        $('#modalCustomerName').text(customer);
        $('#modalCustomerEmail').text(email);
        $('#modalServiceName').text(service);
        $('#modalScheduleInfo').html('<strong>Schedule:</strong> ' + weekly + '<br><strong>Duration:</strong> ' + start + ' to ' + end);
        $('#modalWeeksInfo').html('<strong>Weeks:</strong> ' + weeks + ' week(s) (' + slots + ' slots)');
        $('#modalCostInfo').text('£' + cost + ' (Total Paid: £' + total + ')');

        let badge = '';
        if (status === 'completed') {
            badge = '<span class="badge badge-completed"><i class="fas fa-check-circle me-1"></i> Completed</span>';
            $('#modalFooterActions').html('<button type="button" class="btn btn-secondary rounded-4 px-4 py-2 fw-bold btn-modal-close" data-bs-dismiss="modal">Close</button>');
        } else if (status === 'cancelled') {
            badge = '<span class="badge badge-cancelled"><i class="fas fa-times-circle me-1"></i> Cancelled</span>';
            $('#modalFooterActions').html('<button type="button" class="btn btn-secondary rounded-4 px-4 py-2 fw-bold btn-modal-close" data-bs-dismiss="modal">Close</button>');
        } else {
            badge = '<span class="badge badge-pending"><i class="fas fa-clock me-1"></i> Pending</span>';
            $('#modalFooterActions').html(`
                <button type="button" class="btn btn-secondary rounded-4 px-4 py-2 fw-bold btn-modal-close" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success rounded-4 px-4 py-2 fw-bold action-update-status btn-modal-complete" data-id="${id}" data-status="completed" data-bs-dismiss="modal">Mark Completed</button>
                <button type="button" class="btn btn-danger rounded-4 px-4 py-2 fw-bold action-update-status btn-modal-cancel" data-id="${id}" data-status="cancelled" data-bs-dismiss="modal">Cancel Order</button>
            `);
        }
        $('#modalStatusContainer').html(badge);
    });

    // 5. Handle Status Update AJAX
    $(document).on('click', '.action-update-status', function(e) {
        e.preventDefault();
        const btn = $(this);
        const orderId = btn.data('id');
        const newStatus = btn.data('status');

        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to mark this booking as " + newStatus + "?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#a44390',
            cancelButtonColor: '#cbd5e1',
            confirmButtonText: 'Yes, change it!',
            background: '#ffffff',
            customClass: {
                popup: 'swal2-bento-popup',
                title: 'swal2-bento-title',
                htmlContainer: 'swal2-bento-text',
                confirmButton: 'swal2-bento-btn'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'cosy_update_booking_status',
                        appointment_id: orderId,
                        status: newStatus
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.data.message,
                                confirmButtonColor: '#a44390',
                                background: '#ffffff',
                                customClass: {
                                    popup: 'swal2-bento-popup',
                                    title: 'swal2-bento-title',
                                    htmlContainer: 'swal2-bento-text',
                                    confirmButton: 'swal2-bento-btn'
                                }
                            });

                            // Dynamically update the row and close modal
                            const row = $('#order-row-' + orderId);
                            if (row.length) {
                                // 1. Update data-status on the row
                                row.attr('data-status', newStatus);

                                // 2. Update status badge HTML
                                let badgeHtml = '';
                                if (newStatus === 'completed') {
                                    badgeHtml = '<span class="badge badge-completed"><i class="fas fa-check-circle me-1"></i> Completed</span>';
                                } else if (newStatus === 'cancelled') {
                                    badgeHtml = '<span class="badge badge-cancelled"><i class="fas fa-times-circle me-1"></i> Cancelled</span>';
                                } else {
                                    badgeHtml = '<span class="badge badge-pending"><i class="fas fa-clock me-1"></i> Pending</span>';
                                }
                                row.find('.status-cell').html(badgeHtml);

                                // 3. Update view details button data status
                                row.find('.btn-view-order-details').attr('data-status', newStatus);

                                // 4. Remove update status action buttons dynamically
                                row.find('.action-update-status').remove();
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed',
                                text: response.data.message,
                                confirmButtonColor: '#a44390',
                                background: '#ffffff'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to communicate with the server.',
                            confirmButtonColor: '#a44390',
                            background: '#ffffff'
                        });
                    }
                });
            }
        });
    });
});
</script>