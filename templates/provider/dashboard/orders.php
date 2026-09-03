<?php
if (!defined('ABSPATH')) {
    exit;
}

$current_provider_id = get_current_user_id();

// Fetch appointments using the centralized OOP method from the Dashboard class
$appointments = \Cosy\Appointments\Frontend\Dashboard::get_provider_appointments($current_provider_id);
?>

<div class="card cosy-orders-card mb-4 border-0 shadow-sm rounded-4">
    <div class="card-body p-3 p-sm-4">
        <div class="d-flex align-items-center gap-3 mb-2">
            <div class="header-icon-badge" style="background: rgba(164, 67, 144, 0.1); width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-box-open" style="color: #a44390; font-size: 1.2rem;"></i>
            </div>
            <h3 class="mb-0 fw-bold" style="color: #1e293b; font-family: 'Outfit', sans-serif; font-size: 1.75rem;"><?php esc_html_e('Orders Management', 'cosy-appointments'); ?></h3>
        </div>
        <p class="text-muted mb-4 cosy-orders-subtitle" style="font-size: 0.95rem; font-weight: 500;"><?php esc_html_e('Track and manage your customer bookings and order status.', 'cosy-appointments'); ?></p>

        <!-- Search & Filter -->
        <div class="row mb-4 g-3 align-items-center">
            <div class="col-12 col-xl-6">
                <div class="position-relative">
                    <i class="fas fa-search position-absolute text-muted search-icon-abs" style="top: 50%; left: 18px; transform: translateY(-50%); pointer-events: none; z-index: 10;"></i>
                    <input type="text" id="orderSearchInput" class="form-control rounded-4 border-0 bg-light cosy-orders-filter-input" style="padding-left: 48px !important; height: 46px; font-size: 0.9rem;" placeholder="<?php esc_attr_e('Search by Order ID or Customer...', 'cosy-appointments'); ?>">
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <select id="orderStatusFilter" class="form-select rounded-4 border-0 bg-light cosy-orders-filter-select" style="height: 46px; font-size: 0.9rem;">
                    <option value=""><?php esc_html_e('Filter by Status', 'cosy-appointments'); ?></option>
                    <option value="pending"><?php esc_html_e('Pending', 'cosy-appointments'); ?></option>
                    <option value="completed"><?php esc_html_e('Completed', 'cosy-appointments'); ?></option>
                    <option value="cancelled"><?php esc_html_e('Cancelled', 'cosy-appointments'); ?></option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <button id="exportOrdersBtn" class="btn custom-btn w-100 fw-bold d-flex align-items-center justify-content-center gap-2 cosy-orders-export-btn">
                    <i class="fas fa-file-export"></i> <?php esc_html_e('Export Orders', 'cosy-appointments'); ?>
                </button>
            </div>
        </div>

        <!-- Orders Table Card -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="providerOrdersTable" style="border-collapse: separate; min-width: 880px;">
                    <thead class="table-light">
                        <tr style="border-bottom: 2px solid #edf2f7;">
                            <th class="ps-4 py-3 text-nowrap" style="font-weight: 700; color: #475569; font-size: 0.85rem; text-transform: uppercase; white-space: nowrap; min-width: 155px; width: 155px;"><?php esc_html_e('#Order ID', 'cosy-appointments'); ?></th>
                            <th class="py-3 text-nowrap" style="font-weight: 700; color: #475569; font-size: 0.85rem; text-transform: uppercase; white-space: nowrap; min-width: 170px;"><?php esc_html_e('Customer', 'cosy-appointments'); ?></th>
                            <th class="py-3 text-nowrap" style="font-weight: 700; color: #475569; font-size: 0.85rem; text-transform: uppercase; white-space: nowrap; min-width: 130px;"><?php esc_html_e('Service', 'cosy-appointments'); ?></th>
                            <th class="py-3 text-nowrap" style="font-weight: 700; color: #475569; font-size: 0.85rem; text-transform: uppercase; white-space: nowrap; min-width: 125px;"><?php esc_html_e('Date', 'cosy-appointments'); ?></th>
                            <th class="py-3 text-nowrap" style="font-weight: 700; color: #475569; font-size: 0.85rem; text-transform: uppercase; white-space: nowrap; min-width: 130px;"><?php esc_html_e('Status', 'cosy-appointments'); ?></th>
                            <th class="py-3 text-nowrap text-center pe-4" style="font-weight: 700; color: #475569; font-size: 0.85rem; text-transform: uppercase; white-space: nowrap; min-width: 135px;"><?php esc_html_e('Action', 'cosy-appointments'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($appointments)) : ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted" style="font-size: 0.95rem;">
                                    <i class="fas fa-calendar-times mb-3 d-block" style="font-size: 2.2rem; color: #cbd5e1;"></i>
                                    <h6 class="fw-bold mb-1 text-dark" style="font-size: 1rem;"><?php esc_html_e('No Orders Found', 'cosy-appointments'); ?></h6>
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
                                $slots_timeline  = cosy_clean_slots_timeline(get_post_meta($appt_id, 'cosy_slots_timeline', true), $start_date, $week_days);
                                $is_gift         = get_post_meta($appt_id, 'cosy_is_gift', true);
                                $recipient_name  = get_post_meta($appt_id, 'cosy_recipient_name', true);
                                $recipient_email = get_post_meta($appt_id, 'cosy_recipient_email', true);
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
                                if ($booking_status === 'completed') {
                                    $status_badge = '<span class="badge badge-completed"><i class="fas fa-check-circle me-1"></i> ' . esc_html__('Completed', 'cosy-appointments') . '</span>';
                                } elseif ($booking_status === 'cancelled') {
                                    $status_badge = '<span class="badge badge-cancelled"><i class="fas fa-times-circle me-1"></i> ' . esc_html__('Cancelled', 'cosy-appointments') . '</span>';
                                } else {
                                    $status_badge = '<span class="badge badge-pending"><i class="fas fa-clock me-1"></i> ' . esc_html__('Pending', 'cosy-appointments') . '</span>';
                                }
                            ?>
                                <tr class="order-table-row" id="order-row-<?php echo esc_attr($appt_id); ?>" data-search="<?php echo esc_attr(strtolower("#{$appt_id} {$customer_name}")); ?>" data-status="<?php echo esc_attr($booking_status); ?>" style="border-bottom: 1px solid #edf2f7; transition: all 0.2s;">
                                    <td class="ps-4 py-3 fw-bold text-dark text-nowrap" style="font-size: 0.9rem; white-space: nowrap; min-width: 155px; width: 155px;">
                                        <span class="d-inline-flex align-items-center gap-2" style="white-space: nowrap;">
                                            <span>#<?php echo esc_html($appt_id); ?></span>
                                            <?php if (!empty($is_gift)): ?>
                                                <span class="badge" style="background: #a44390; color: #fff; font-size: 0.72rem; font-weight: 700; border-radius: 12px; padding: 4px 8px; vertical-align: middle; white-space: nowrap; letter-spacing: 0.2px;" title="<?php esc_attr_e('Gifted Booking', 'cosy-appointments'); ?>">🎁 Gift</span>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td class="py-3 text-nowrap" style="min-width: 170px;">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center order-customer-avatar flex-shrink-0"><?php echo esc_html($initials); ?></div>
                                            <span class="fw-semibold order-customer-name text-dark" style="font-size: 0.9rem;"><?php echo esc_html($customer_name); ?></span>
                                        </div>
                                    </td>
                                    <td class="py-3 text-nowrap" style="min-width: 130px;">
                                        <span class="badge bg-light text-dark border-0 px-3 py-2 rounded-3 order-service-badge" style="font-size: 0.8rem; font-weight: 600;"><?php echo esc_html($service_name); ?></span>
                                    </td>
                                    <td class="py-3 text-muted text-nowrap order-date-cell" style="font-size: 0.85rem; min-width: 125px;">
                                        <?php echo esc_html(cosy_format_date($start_date)); ?>
                                    </td>
                                    <td class="py-3 text-nowrap status-cell" style="min-width: 130px;">
                                        <?php echo $status_badge; ?>
                                    </td>
                                    <td class="py-3 text-center text-nowrap pe-4" style="min-width: 135px;">
                                        <div class="d-inline-flex align-items-center justify-content-center gap-2">
                                            <?php if ($booking_status === 'pending') : ?>
                                                <button class="btn-action action-update-status bg-success text-white order-action-btn"
                                                    data-id="<?php echo esc_attr($appt_id); ?>"
                                                    data-status="completed"
                                                    title="<?php esc_attr_e('Mark Completed', 'cosy-appointments'); ?>">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button class="btn-action action-update-status bg-danger text-white order-action-btn"
                                                    data-id="<?php echo esc_attr($appt_id); ?>"
                                                    data-status="cancelled"
                                                    title="<?php esc_attr_e('Cancel Order', 'cosy-appointments'); ?>">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn-action btn-view-order-details bg-light text-secondary order-action-btn border"
                                                data-id="<?php echo esc_attr($appt_id); ?>"
                                                data-customer="<?php echo esc_attr($customer_name); ?>"
                                                data-email="<?php echo esc_attr($customer_email); ?>"
                                                data-service="<?php echo esc_attr($service_name); ?>"
                                                data-start="<?php echo esc_attr(cosy_format_date($start_date)); ?>"
                                                data-end="<?php echo esc_attr(cosy_format_date($end_date)); ?>"
                                                data-weekly="<?php echo esc_attr($weekly_booking); ?>"
                                                data-weeks="<?php echo esc_attr($number_of_weeks); ?>"
                                                data-slots="<?php echo esc_attr($number_of_slots); ?>"
                                                data-cost="<?php echo esc_attr($service_cost); ?>"
                                                data-fee="<?php echo esc_attr($service_fee); ?>"
                                                data-total="<?php echo esc_attr($total_payable); ?>"
                                                data-status="<?php echo esc_attr($booking_status); ?>"
                                                data-week-days="<?php echo esc_attr($week_days); ?>"
                                                data-slots-timeline="<?php echo esc_attr($slots_timeline); ?>"
                                                data-is-gift="<?php echo esc_attr(!empty($is_gift) ? '1' : '0'); ?>"
                                                data-recipient-name="<?php echo esc_attr($recipient_name); ?>"
                                                data-recipient-email="<?php echo esc_attr($recipient_email); ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#orderDetailsModal"
                                                title="<?php esc_attr_e('View Details', 'cosy-appointments'); ?>">
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
            <h6 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2 modal-info-title" style="font-family: 'Outfit', sans-serif; font-size: 0.95rem; margin-bottom: 12px !important;"><i class="fas fa-calendar-alt modal-icon-primary" style="color: var(--cosy-brand-purple) !important;"></i> <?php esc_html_e('Booking Information', 'cosy-appointments'); ?></h6>

            <div class="d-flex flex-column gap-2" style="font-size: 0.88rem; color: #475569;">
                <div class="d-flex align-items-baseline gap-1">
                    <span class="text-muted" style="min-width: 130px; font-weight: 500;">Start Date:</span>
                    <span id="modalStartDateInfo" class="text-dark fw-semibold"></span>
                </div>
                <div class="d-flex align-items-baseline gap-1">
                    <span class="text-muted" style="min-width: 130px; font-weight: 500;">Number of Weeks:</span>
                    <span id="modalWeeksInfo" class="text-dark fw-semibold"></span>
                </div>
                <div class="d-flex align-items-baseline gap-1">
                    <span class="text-muted" style="min-width: 130px; font-weight: 500;">Booking Days:</span>
                    <span id="modalSlotsTimelineInfo" class="text-dark fw-semibold" style="word-break: break-word; line-height: 1.6;"></span>
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
                <span>Service Fee*:</span>
                <strong id="modalServiceFee" class="text-dark fw-bold" style="font-size: 0.95rem;"></strong>
            </div>
            <div class="d-flex justify-content-between border-top pt-2 mt-2" style="font-size: 0.95rem;">
                <span class="fw-bold text-dark">Total Paid:</span>
                <strong id="modalTotalPaid" style="color: var(--cosy-brand-purple); font-size: 1.15rem; font-weight: 800;"></strong>
            </div>
            <p class="mb-0 mt-2 text-muted" style="font-size: 0.78rem; font-style: italic;">* <?php esc_html_e('Service Charge – helps us provide and continually improve the CosyChats platform, including secure bookings, payment processing and customer support.', 'cosy-appointments'); ?></p>
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