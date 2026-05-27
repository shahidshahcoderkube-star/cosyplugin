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
		<a href="<?php echo site_url('/service-provider'); ?>" class="btn px-4 text-white fw-bold shadow-sm mt-3 mt-md-0"
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
								<td class="py-3 fw-bold text-dark" style="font-size: 0.95rem;">£<?php echo esc_html($total_price); ?></td>
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
							</tr>
						<?php endforeach; ?>
					<?php else: ?>
						<tr>
							<td colspan="8" class="text-center py-5 text-muted" style="font-size: 0.95rem;">
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