<?php
if (!defined('ABSPATH')) {
	exit;
}

$current_user = wp_get_current_user();
$appointments = [];

if ($current_user->exists()) {
	$appointments = get_posts([
		'post_type' => 'cosy_appointment',
		'post_status' => 'publish',
		'author' => $current_user->ID,
		'posts_per_page' => -1,
	]);
}
?>

<div class="container my-orders mt-5 mb-5" style="font-family: 'Plus Jakarta Sans', sans-serif;">
	<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
		<div>
			<div class="d-flex align-items-center gap-3 mb-2">
				<div class="header-icon-badge" style="background: rgba(164, 67, 144, 0.1); width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
					<i class="fas fa-calendar-check" style="color: #a44390; font-size: 1.2rem;"></i>
				</div>
				<h3 class="mb-0 fw-bold" style="color: #1e293b; font-family: 'Outfit', sans-serif; font-size: 1.75rem;"><?php esc_html_e('My Bookings & Orders', 'cosy-appointments'); ?></h3>
			</div>
			<p class="text-muted mb-0" style="margin-left: 58px; font-size: 0.95rem; font-weight: 500;"><?php esc_html_e('Manage and track your appointment bookings and secure payments.', 'cosy-appointments'); ?></p>
		</div>
		<a href="<?php echo esc_url(cosy_get_page_url('service-provider')); ?>" class="btn px-4 text-white fw-bold shadow-sm mt-3 mt-md-0"
			style="background: linear-gradient(135deg, #a44390, #6d2e67); border-radius: 10px; border: none; font-size: 0.95rem; display: inline-flex; align-items: center; justify-content: center; height: 44px;">
			<i class="fas fa-plus me-2"></i> <?php esc_html_e('Book Another Service', 'cosy-appointments'); ?>
		</a>
	</div>

	<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
		<div class="table-responsive">
			<table class="table table-hover align-middle mb-0" style="border-collapse: separate;">
				<thead class="table-light">
					<tr style="border-bottom: 2px solid #edf2f7;">
						<th class="ps-4 py-3" style="font-weight: 700; color: #475569; font-size: 0.85rem; text-transform: uppercase;"><?php esc_html_e('Order ID', 'cosy-appointments'); ?></th>
						<th class="py-3" style="font-weight: 700; color: #475569; font-size: 0.85rem; text-transform: uppercase;"><?php esc_html_e('Service', 'cosy-appointments'); ?></th>
						<th class="py-3" style="font-weight: 700; color: #475569; font-size: 0.85rem; text-transform: uppercase;"><?php esc_html_e('Provider', 'cosy-appointments'); ?></th>
						<th class="py-3" style="font-weight: 700; color: #475569; font-size: 0.85rem; text-transform: uppercase;"><?php esc_html_e('Weekly Booking', 'cosy-appointments'); ?></th>
						<th class="py-3" style="font-weight: 700; color: #475569; font-size: 0.85rem; text-transform: uppercase;"><?php esc_html_e('Payment Status', 'cosy-appointments'); ?></th>
						<th class="py-3" style="font-weight: 700; color: #475569; font-size: 0.85rem; text-transform: uppercase;"><?php esc_html_e('Total Price', 'cosy-appointments'); ?></th>
						<th class="py-3" style="font-weight: 700; color: #475569; font-size: 0.85rem; text-transform: uppercase;"><?php esc_html_e('Date Booked', 'cosy-appointments'); ?></th>
						<th class="py-3" style="font-weight: 700; color: #475569; font-size: 0.85rem; text-transform: uppercase;"><?php esc_html_e('Status', 'cosy-appointments'); ?></th>
						<th class="py-3" style="font-weight: 700; color: #475569; font-size: 0.85rem; text-transform: uppercase;"><?php esc_html_e('Action', 'cosy-appointments'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if (!empty($appointments)): ?>
						<?php foreach ($appointments as $appt):
							$service = get_post_meta($appt->ID, 'cosy_service_name', true);
							$provider = get_post_meta($appt->ID, 'cosy_provider_name', true);
							$weekly_booking = get_post_meta($appt->ID, 'cosy_weekly_booking', true);
							$payment_status = get_post_meta($appt->ID, 'cosy_payment_status', true);
							$total_price = get_post_meta($appt->ID, 'cosy_total_payable', true);
							$start_date = get_post_meta($appt->ID, 'cosy_start_date', true);
							$status = get_post_meta($appt->ID, 'cosy_booking_status', true);
							$end_date = get_post_meta($appt->ID, 'cosy_end_date', true);
							$weeks = get_post_meta($appt->ID, 'cosy_number_of_weeks', true);
							$slots = get_post_meta($appt->ID, 'cosy_number_of_bookings', true);
							$cost = get_post_meta($appt->ID, 'cosy_service_cost', true);
							$fee = get_post_meta($appt->ID, 'cosy_service_fee', true);
							$week_days = get_post_meta($appt->ID, 'cosy_week_days', true);
							$slots_timeline = get_post_meta($appt->ID, 'cosy_slots_timeline', true);
						?>
							<tr style="border-bottom: 1px solid #edf2f7; transition: all 0.2s;">
								<td class="ps-4 py-3 fw-bold text-dark" style="font-size: 0.9rem;">#<?php echo esc_html($appt->ID); ?></td>
								<td class="py-3 fw-semibold text-dark" style="font-size: 0.9rem;"><?php echo esc_html($service); ?></td>
								<td class="py-3" style="font-size: 0.9rem; color: #475569;"><?php echo esc_html($provider); ?></td>
								<td class="py-3" style="font-size: 0.85rem; color: #64748b;"><?php echo esc_html($weekly_booking); ?></td>
								<td class="py-3">
									<span class="badge px-3 py-2 rounded-3 text-success"
										style="background-color: rgba(34, 197, 94, 0.1); font-weight: 600; font-size: 0.75rem;">
										<i class="fas fa-check-circle me-1"></i> <?php echo esc_html__($payment_status ?: 'Paid', 'cosy-appointments'); ?>
									</span>
								</td>
								<td class="py-3 fw-bold text-dark" style="font-size: 0.95rem;"><?php echo esc_html(cosy_get_currency_symbol()); ?><?php echo esc_html($total_price); ?></td>
								<td class="py-3 text-muted" style="font-size: 0.85rem;"><?php echo esc_html($start_date); ?></td>
								<td class="py-3">
									<?php if ($status === 'completed'): ?>
										<span class="badge px-3 py-2 rounded-3 text-success" style="background-color: rgba(34, 197, 94, 0.1); font-weight: 600; font-size: 0.75rem;">
											<?php esc_html_e('Completed', 'cosy-appointments'); ?>
										</span>
									<?php elseif ($status === 'cancelled'): ?>
										<span class="badge px-3 py-2 rounded-3 text-danger" style="background-color: rgba(239, 68, 68, 0.1); font-weight: 600; font-size: 0.75rem;">
											<?php esc_html_e('Cancelled', 'cosy-appointments'); ?>
										</span>
									<?php else: ?>
										<span class="badge px-3 py-2 rounded-3 text-warning" style="background-color: rgba(245, 158, 11, 0.1); font-weight: 600; font-size: 0.75rem; color: #d97706 !important;">
											<?php esc_html_e('Pending', 'cosy-appointments'); ?>
										</span>
									<?php endif; ?>
								</td>
								<td class="py-3">
									<button class="btn btn-sm btn-light btn-view-customer-order-details border"
										data-id="<?php echo esc_attr($appt->ID); ?>"
										data-service="<?php echo esc_attr($service); ?>"
										data-provider="<?php echo esc_attr($provider); ?>"
										data-weekly="<?php echo esc_attr($weekly_booking); ?>"
										data-total="<?php echo esc_attr($total_price); ?>"
										data-cost="<?php echo esc_attr($cost); ?>"
										data-fee="<?php echo esc_attr($fee); ?>"
										data-start="<?php echo esc_attr($start_date); ?>"
										data-end="<?php echo esc_attr($end_date); ?>"
										data-weeks="<?php echo esc_attr($weeks); ?>"
										data-slots="<?php echo esc_attr($slots); ?>"
										data-week-days="<?php echo esc_attr($week_days); ?>"
										data-slots-timeline="<?php echo esc_attr($slots_timeline); ?>"
										data-status="<?php echo esc_attr($status); ?>"
										data-bs-toggle="modal"
										data-bs-target="#customerOrderDetailsModal"
										title="View Details"
										style="padding: 4px 8px; border-radius: 6px;">
										<i class="fas fa-eye"></i>
									</button>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php else: ?>
						<tr>
							<td colspan="9" class="text-center py-5 text-muted" style="font-size: 0.95rem;">
								<i class="fas fa-calendar-times mb-3 d-block" style="font-size: 2rem; color: #cbd5e1;"></i>
								<?php esc_html_e('No bookings or orders found.', 'cosy-appointments'); ?>
							</td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<?php
ob_start();
?>
<div class="row g-3" style="font-family: 'Plus Jakarta Sans', sans-serif;">
    <!-- Booking & Service Details -->
    <div class="col-md-12">
        <div class="p-3 rounded-4 bg-light" style="border: 1px solid #e2e8f0;">
            <h6 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2" style="font-family: 'Outfit', sans-serif; font-size: 0.95rem; margin-bottom: 12px !important;"><i class="fas fa-comments" style="color: #a44390;"></i> <?php esc_html_e('Conversations', 'cosy-appointments'); ?></h6>
            <p class="mb-3 fw-bold text-slate" style="font-size: 1.1rem; color: #6d2e67;" id="modalCustServiceName"></p>
            
            <div class="d-flex flex-column gap-2" style="font-size: 0.88rem; color: #475569;">
                <div class="d-flex align-items-baseline gap-1">
                    <span class="text-muted" style="min-width: 120px; font-weight: 500;">Provider:</span>
                    <span id="modalCustProviderName" class="text-dark fw-semibold"></span>
                </div>
                <div class="d-flex align-items-baseline gap-1">
                    <span class="text-muted" style="min-width: 120px; font-weight: 500;">Schedule:</span>
                    <span id="modalCustWeeklyBooking" class="text-dark fw-semibold"></span>
                </div>
                <div class="d-flex align-items-baseline gap-1">
                    <span class="text-muted" style="min-width: 120px; font-weight: 500;">Duration:</span>
                    <span id="modalCustDurationInfo" class="text-dark fw-semibold"></span>
                </div>
                <div class="d-flex align-items-baseline gap-1">
                    <span class="text-muted" style="min-width: 120px; font-weight: 500;">Weeks Booked:</span>
                    <span id="modalCustWeeks" class="text-dark fw-semibold"></span>
                </div>
                <div class="d-flex align-items-baseline gap-1">
                    <span class="text-muted" style="min-width: 120px; font-weight: 500;">Week Days:</span>
                    <span id="modalCustWeekDays" class="text-dark fw-semibold"></span>
                </div>
                <div class="d-flex align-items-baseline gap-1">
                    <span class="text-muted" style="min-width: 120px; font-weight: 500;">Selected Slots:</span>
                    <span id="modalCustSlotsTimeline" class="text-dark fw-semibold" style="word-break: break-word;"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Statement -->
    <div class="col-md-12">
        <div class="p-3 rounded-4 border bg-white" style="border-color: rgba(164, 67, 144, 0.2) !important;">
            <h6 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2" style="font-family: 'Outfit', sans-serif; font-size: 0.95rem; margin-bottom: 12px !important;"><i class="fas fa-receipt" style="color: #a44390;"></i> <?php esc_html_e('Payment Summary', 'cosy-appointments'); ?></h6>
            <div class="d-flex justify-content-between mb-2 small text-muted" style="font-size: 0.88rem;">
                <span>Service Cost:</span>
                <strong id="modalCustCost" class="text-dark fw-bold" style="font-size: 0.95rem;"></strong>
            </div>
            <div class="d-flex justify-content-between mb-2 small text-muted" style="font-size: 0.88rem;">
                <span>Service Fee*:</span>
                <strong id="modalCustFee" class="text-dark fw-bold" style="font-size: 0.95rem;"></strong>
            </div>
            <div class="d-flex justify-content-between border-top pt-2 mt-2" style="font-size: 0.95rem;">
                <span class="fw-bold text-dark">Total Paid:</span>
                <strong id="modalCustTotal" style="color: #a44390; font-size: 1.15rem; font-weight: 800;"></strong>
            </div>
            <p class="mb-0 mt-2 text-muted" style="font-size: 0.78rem; font-style: italic;">* <?php esc_html_e('Service Charge – helps us provide and continually improve the CosyChats platform, including secure bookings, payment processing and customer support.', 'cosy-appointments'); ?></p>
        </div>
    </div>
</div>

<div class="mt-4 p-3 rounded-4 border text-center" id="modalCustStatusBg" style="border-radius: 12px;">
	<strong class="small text-muted" style="letter-spacing: 0.5px;"><?php esc_html_e('ORDER STATUS:', 'cosy-appointments'); ?></strong>
	<span id="modalCustStatusText" class="fw-bold ms-2" style="font-size: 0.95rem;"></span>
</div>
<?php
$customer_modal_body = ob_get_clean();

echo cosy_render_popup(
	'customerOrderDetailsModal',
	__('Order Details', 'cosy-appointments'),
	$customer_modal_body,
	[
		'dialog_class' => 'modal-lg',
		'max_width'    => '',
		'header_class' => 'cosy-modal-header-gradient p-4',
		'footer_html'  => '<button type="button" class="btn btn-secondary rounded-4 px-4 py-2 fw-bold" data-bs-dismiss="modal">' . esc_html__('Close', 'cosy-appointments') . '</button>'
	]
);
?>