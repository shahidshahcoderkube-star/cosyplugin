<?php
if (!defined('ABSPATH')) {
    exit;
}

$current_provider_id = get_current_user_id();

// Fetch appointments using the centralized OOP method from the Dashboard class
$appointments = \Cosy\Appointments\Frontend\Dashboard::get_provider_appointments($current_provider_id);
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
                    <i class="fas fa-search position-absolute text-muted search-icon-abs" style="top: 50%; left: 18px; transform: translateY(-50%); pointer-events: none; z-index: 10;"></i>
                    <input type="text" id="orderSearchInput" class="form-control rounded-4 border-0 bg-light cosy-orders-filter-input" style="padding-left: 48px !important;" placeholder="Search by Order ID or Customer...">
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
                                    <i class="fas fa-folder-open fa-3x order-empty-icon"></i>
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

<?php
ob_start();
?>
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 modal-info-box-primary">
                            <h6 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2 modal-info-title"><i class="fas fa-user modal-icon-primary"></i> Customer Info</h6>
                            <p class="mb-1 fw-bold text-slate modal-customer-name" id="modalCustomerName"></p>
                            <p class="mb-0 text-muted small" id="modalCustomerEmail"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 bg-light modal-info-box-secondary">
                            <h6 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2 modal-info-title"><i class="fas fa-concierge-bell modal-icon-primary"></i> Service Details</h6>
                            <p class="mb-1 fw-bold text-slate modal-customer-name" id="modalServiceName"></p>
                            <p class="mb-1 text-muted small modal-small-info" id="modalScheduleInfo"></p>
                            <p class="mb-1 text-muted small modal-small-info" id="modalWeeksInfo"></p>
                            <p class="mb-0 fw-bold modal-cost-info" id="modalCostInfo"></p>
                        </div>
                    </div>
                </div>

                <div class="mt-4 p-3 rounded-4 border modal-status-box">
                    <h6 class="fw-bold mb-2 text-slate modal-status-title">Current Status</h6>
                    <div id="modalStatusContainer"></div>
                </div>
<?php
$modal_body = ob_get_clean();

// Note: Footer actions are populated dynamically via JS, so we pass an empty footer that the JS will target.
echo cosy_render_popup(
    'orderDetailsModal',
    'Order Details',
    $modal_body,
    [
        'dialog_class' => 'modal-lg',
        'max_width'    => '', // rely on modal-lg
        'header_class' => 'cosy-modal-header-gradient p-4',
        'footer_html'  => '<!-- Actions populated dynamically -->' // This ensures the footer div is created
    ]
);
?>