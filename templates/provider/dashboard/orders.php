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
            <h3 class="mb-0 fw-bold"><?php esc_html_e('Orders Management', 'cosy-appointments'); ?></h3>
        </div>
        <p class="text-muted mb-4 cosy-orders-subtitle"><?php esc_html_e('Track and manage your customer bookings and order status.', 'cosy-appointments'); ?></p>

        <!-- Search & Filter -->
        <div class="row mb-4 gx-3">
            <div class="col-md-6 mb-2 mb-md-0">
                <div class="position-relative">
                    <i class="fas fa-search position-absolute text-muted search-icon-abs" style="top: 50%; left: 18px; transform: translateY(-50%); pointer-events: none; z-index: 10;"></i>
                    <input type="text" id="orderSearchInput" class="form-control rounded-4 border-0 bg-light cosy-orders-filter-input" style="padding-left: 48px !important;" placeholder="<?php esc_attr_e('Search by Order ID or Customer...', 'cosy-appointments'); ?>">
                </div>
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <select id="orderStatusFilter" class="form-select rounded-4 border-0 bg-light cosy-orders-filter-select">
                    <option value=""><?php esc_html_e('Filter by Status', 'cosy-appointments'); ?></option>
                    <option value="pending"><?php esc_html_e('Pending', 'cosy-appointments'); ?></option>
                    <option value="completed"><?php esc_html_e('Completed', 'cosy-appointments'); ?></option>
                    <option value="cancelled"><?php esc_html_e('Cancelled', 'cosy-appointments'); ?></option>
                </select>
            </div>
            <div class="col-md-3">
                <button id="exportOrdersBtn" class="btn btn-primary custom-btn w-100 rounded-4 fw-bold d-flex align-items-center justify-content-center gap-2 cosy-orders-export-btn">
                    <i class="fas fa-file-export"></i> <?php esc_html_e('Export Orders', 'cosy-appointments'); ?>
                </button>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="table-responsive">
            <table class="table align-middle" id="providerOrdersTable">
                <thead>
                    <tr>
                        <th class="pb-3"><?php esc_html_e('#Order ID', 'cosy-appointments'); ?></th>
                        <th class="pb-3"><?php esc_html_e('Customer', 'cosy-appointments'); ?></th>
                        <th class="pb-3"><?php esc_html_e('Service', 'cosy-appointments'); ?></th>
                        <th class="pb-3"><?php esc_html_e('Date', 'cosy-appointments'); ?></th>
                        <th class="pb-3"><?php esc_html_e('Status', 'cosy-appointments'); ?></th>
                        <th class="pb-3"><?php esc_html_e('Action', 'cosy-appointments'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($appointments)) : ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <div class="mb-3">
                                    <i class="fas fa-folder-open fa-3x order-empty-icon"></i>
                                </div>
                                <h6 class="fw-bold mb-1"><?php esc_html_e('No Orders Found', 'cosy-appointments'); ?></h6>
                                <p class="small text-muted mb-0"><?php esc_html_e('Newly booked orders will appear automatically here.', 'cosy-appointments'); ?></p>
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
                            $service_fee     = get_post_meta($appt_id, 'cosy_service_fee', true);
                            $total_payable   = get_post_meta($appt_id, 'cosy_total_payable', true);
                            $booking_status  = get_post_meta($appt_id, 'cosy_booking_status', true);
                            $week_days       = get_post_meta($appt_id, 'cosy_week_days', true);
                            $slots_timeline  = get_post_meta($appt_id, 'cosy_slots_timeline', true);
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
                                $status_badge = '<span class="badge badge-completed"><i class="fas fa-check-circle me-1"></i> ' . esc_html__('Completed', 'cosy-appointments') . '</span>';
                                $badge_class = 'completed';
                            } elseif ($booking_status === 'cancelled') {
                                $status_badge = '<span class="badge badge-cancelled"><i class="fas fa-times-circle me-1"></i> ' . esc_html__('Cancelled', 'cosy-appointments') . '</span>';
                                $badge_class = 'cancelled';
                            } else {
                                $status_badge = '<span class="badge badge-pending"><i class="fas fa-clock me-1"></i> ' . esc_html__('Pending', 'cosy-appointments') . '</span>';
                                $badge_class = 'pending';
                            }
                        ?>
                            <tr class="order-table-row" id="order-row-<?php echo esc_attr($appt_id); ?>" data-search="<?php echo esc_attr(strtolower("#{$appt_id} {$customer_name}")); ?>" data-status="<?php echo esc_attr($booking_status); ?>">
                                <td class="fw-bold text-dark">#<?php echo esc_html($appt_id); ?></td>
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
                                                data-id="<?php echo esc_attr($appt_id); ?>"
                                                data-status="completed"
                                                title="Mark Completed">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn-action action-update-status bg-danger text-white order-action-btn"
                                                data-id="<?php echo esc_attr($appt_id); ?>"
                                                data-status="cancelled"
                                                title="Cancel Order">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn-action btn-view-order-details bg-light text-secondary order-action-btn"
                                            data-id="<?php echo esc_attr($appt_id); ?>"
                                            data-customer="<?php echo esc_attr($customer_name); ?>"
                                            data-email="<?php echo esc_attr($customer_email); ?>"
                                            data-service="<?php echo esc_attr($service_name); ?>"
                                            data-start="<?php echo esc_attr($start_date); ?>"
                                            data-end="<?php echo esc_attr($end_date); ?>"
                                            data-weekly="<?php echo esc_attr($weekly_booking); ?>"
                                            data-weeks="<?php echo esc_attr($number_of_weeks); ?>"
                                            data-slots="<?php echo esc_attr($number_of_slots); ?>"
                                            data-cost="<?php echo esc_attr($service_cost); ?>"
                                            data-fee="<?php echo esc_attr($service_fee); ?>"
                                            data-total="<?php echo esc_attr($total_payable); ?>"
                                            data-status="<?php echo esc_attr($booking_status); ?>"
                                            data-week-days="<?php echo esc_attr($week_days); ?>"
                                            data-slots-timeline="<?php echo esc_attr($slots_timeline); ?>"
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
<div class="row g-3">
    <!-- Customer Info -->
    <div class="col-md-12">
        <div class="p-3 rounded-4 modal-info-box-primary" style="background-color: var(--cosy-brand-lightest); border: 1px solid var(--cosy-brand-light);">
            <h6 class="fw-bold mb-2 text-dark d-flex align-items-center gap-2 modal-info-title" style="font-family: 'Outfit', sans-serif; font-size: 0.95rem; margin-bottom: 12px !important;"><i class="fas fa-user modal-icon-primary" style="color: var(--cosy-brand-purple) !important;"></i> <?php esc_html_e('Customer Info', 'cosy-appointments'); ?></h6>
            <div class="d-flex align-items-center gap-2">
                <span class="fw-bold text-dark" style="font-size: 1.05rem;" id="modalCustomerName"></span>
                <span class="text-muted small" id="modalCustomerEmail"></span>
            </div>
        </div>
    </div>

    <!-- Service Details -->
    <div class="col-md-12">
        <div class="p-3 rounded-4 bg-light modal-info-box-secondary" style="border: 1px solid var(--cosy-border);">
            <h6 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2 modal-info-title" style="font-family: 'Outfit', sans-serif; font-size: 0.95rem; margin-bottom: 12px !important;"><i class="fas fa-concierge-bell modal-icon-primary" style="color: var(--cosy-brand-purple) !important;"></i> <?php esc_html_e('Service Details', 'cosy-appointments'); ?></h6>
            <p class="mb-3 fw-bold text-slate" style="font-size: 1.1rem; color: var(--cosy-brand-dark);" id="modalServiceName"></p>

            <div class="d-flex flex-column gap-2" style="font-size: 0.88rem; color: #475569;">
                <div class="d-flex align-items-baseline gap-1">
                    <span class="text-muted" style="min-width: 100px; font-weight: 500;">Schedule:</span>
                    <span id="modalScheduleInfo" class="text-dark fw-semibold"></span>
                </div>
                <div class="d-flex align-items-baseline gap-1">
                    <span class="text-muted" style="min-width: 100px; font-weight: 500;">Duration:</span>
                    <span id="modalDurationInfo" class="text-dark fw-semibold"></span>
                </div>
                <div class="d-flex align-items-baseline gap-1">
                    <span class="text-muted" style="min-width: 100px; font-weight: 500;">Weeks:</span>
                    <span id="modalWeeksInfo" class="text-dark fw-semibold"></span>
                </div>
                <div class="d-flex align-items-baseline gap-1">
                    <span class="text-muted" style="min-width: 100px; font-weight: 500;">Week Days:</span>
                    <span id="modalWeekDaysInfo" class="text-dark fw-semibold"></span>
                </div>
                <div class="d-flex align-items-baseline gap-1">
                    <span class="text-muted" style="min-width: 100px; font-weight: 500;">Selected slots:</span>
                    <span id="modalSlotsTimelineInfo" class="text-dark fw-semibold" style="word-break: break-word;"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Statement -->
    <div class="col-md-12">
        <div class="p-3 rounded-4 border bg-white" style="border-color: var(--cosy-brand-light) !important;">
            <h6 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2 modal-info-title" style="font-family: 'Outfit', sans-serif; font-size: 0.95rem; margin-bottom: 12px !important;"><i class="fas fa-receipt modal-icon-primary" style="color: var(--cosy-brand-purple) !important;"></i> <?php esc_html_e('Payment Summary', 'cosy-appointments'); ?></h6>
            <div class="d-flex justify-content-between mb-2 small text-muted" style="font-size: 0.88rem;">
                <span>Provider Share:</span>
                <strong id="modalProviderShare" class="text-dark fw-bold" style="font-size: 0.95rem;"></strong>
            </div>
            <div class="d-flex justify-content-between mb-2 small text-muted" style="font-size: 0.88rem;">
                <span>Service Fee:</span>
                <strong id="modalServiceFee" class="text-dark fw-bold" style="font-size: 0.95rem;"></strong>
            </div>
            <div class="d-flex justify-content-between border-top pt-2 mt-2" style="font-size: 0.95rem;">
                <span class="fw-bold text-dark">Total Paid:</span>
                <strong id="modalTotalPaid" style="color: var(--cosy-brand-purple); font-size: 1.15rem; font-weight: 800;"></strong>
            </div>
        </div>
    </div>
</div>

<div class="mt-4 p-3 rounded-4 border modal-status-box">
    <h6 class="fw-bold mb-2 text-slate modal-status-title"><?php esc_html_e('Current Status', 'cosy-appointments'); ?></h6>
    <div id="modalStatusContainer"></div>
</div>
<?php
$modal_body = ob_get_clean();

// Note: Footer actions are populated dynamically via JS, so we pass an empty footer that the JS will target.
echo cosy_render_popup(
    'orderDetailsModal',
    __('Order Details', 'cosy-appointments'),
    $modal_body,
    [
        'dialog_class' => 'modal-lg',
        'max_width'    => '', // rely on modal-lg
        'header_class' => 'cosy-modal-header-gradient p-4',
        'footer_html'  => '<!-- Actions populated dynamically -->' // This ensures the footer div is created
    ]
);
?>