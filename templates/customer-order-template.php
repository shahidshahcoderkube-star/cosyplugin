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
                <h3 class="mb-0 fw-bold" style="color: #1e293b; font-family: 'Outfit', sans-serif; font-size: 1.75rem;">My Bookings & Orders</h3>
            </div>
            <p class="text-muted mb-0" style="margin-left: 58px; font-size: 0.95rem; font-weight: 500;">Manage and track your appointment bookings and secure payments.</p>
        </div>
        <a href="<?php echo site_url('/service-provider'); ?>" class="btn px-4 text-white fw-bold shadow-sm mt-3 mt-md-0"
           style="background: linear-gradient(135deg, #a44390, #6d2e67); border-radius: 10px; border: none; font-size: 0.95rem; display: inline-flex; align-items: center; justify-content: center; height: 44px;">
           <i class="fas fa-plus me-2"></i> Book Another Service
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="border-collapse: separate;">
                <thead class="table-light">
                    <tr style="border-bottom: 2px solid #edf2f7;">
                        <th class="ps-4 py-3" style="font-weight: 700; color: #475569; font-size: 0.85rem; text-transform: uppercase;">Order ID</th>
                        <th class="py-3" style="font-weight: 700; color: #475569; font-size: 0.85rem; text-transform: uppercase;">Service</th>
                        <th class="py-3" style="font-weight: 700; color: #475569; font-size: 0.85rem; text-transform: uppercase;">Provider</th>
                        <th class="py-3" style="font-weight: 700; color: #475569; font-size: 0.85rem; text-transform: uppercase;">Weekly Booking</th>
                        <th class="py-3" style="font-weight: 700; color: #475569; font-size: 0.85rem; text-transform: uppercase;">Payment Status</th>
                        <th class="py-3" style="font-weight: 700; color: #475569; font-size: 0.85rem; text-transform: uppercase;">Total Price</th>
                        <th class="py-3" style="font-weight: 700; color: #475569; font-size: 0.85rem; text-transform: uppercase;">Date Booked</th>
                        <th class="py-3" style="font-weight: 700; color: #475569; font-size: 0.85rem; text-transform: uppercase;">Status</th>
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
                                <td class="ps-4 py-3 fw-bold text-dark" style="font-size: 0.9rem;">#<?php echo $appt->ID; ?></td>
                                <td class="py-3 fw-semibold text-dark" style="font-size: 0.9rem;"><?php echo esc_html($service); ?></td>
                                <td class="py-3" style="font-size: 0.9rem; color: #475569;"><?php echo esc_html($provider); ?></td>
                                <td class="py-3" style="font-size: 0.85rem; color: #64748b;"><?php echo esc_html($weekly_booking); ?></td>
                                <td class="py-3">
                                    <span class="badge px-3 py-2 rounded-3 text-success" 
                                          style="background-color: rgba(34, 197, 94, 0.1); font-weight: 600; font-size: 0.75rem;">
                                        <i class="fas fa-check-circle me-1"></i> <?php echo esc_html($payment_status ?: 'Paid'); ?>
                                    </span>
                                </td>
                                <td class="py-3 fw-bold text-dark" style="font-size: 0.95rem;">£<?php echo esc_html($total_price); ?></td>
                                <td class="py-3 text-muted" style="font-size: 0.85rem;"><?php echo esc_html($start_date); ?></td>
                                <td class="py-3">
                                    <?php if ($status === 'completed'): ?>
                                        <span class="badge px-3 py-2 rounded-3 text-success" style="background-color: rgba(34, 197, 94, 0.1); font-weight: 600; font-size: 0.75rem;">
                                            Completed
                                        </span>
                                    <?php elseif ($status === 'cancelled'): ?>
                                        <span class="badge px-3 py-2 rounded-3 text-danger" style="background-color: rgba(239, 68, 68, 0.1); font-weight: 600; font-size: 0.75rem;">
                                            Cancelled
                                        </span>
                                    <?php else: ?>
                                        <span class="badge px-3 py-2 rounded-3 text-warning" style="background-color: rgba(245, 158, 11, 0.1); font-weight: 600; font-size: 0.75rem; color: #d97706 !important;">
                                            Pending
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Mock data for demonstration if no custom post exists yet -->
                        <tr style="border-bottom: 1px solid #edf2f7;">
                            <td class="ps-4 py-3 fw-bold text-dark">#1234</td>
                            <td class="py-3 fw-semibold text-dark">Kids Lesson</td>
                            <td class="py-3">Amanda</td>
                            <td class="py-3 text-muted">Wednesday 9:00 AM 10 Minutes</td>
                            <td class="py-3">
                                <span class="badge px-3 py-2 rounded-3 text-success" style="background-color: rgba(34, 197, 94, 0.1); font-weight: 600; font-size: 0.75rem;">
                                    Paid
                                </span>
                            </td>
                            <td class="py-3 fw-bold text-dark">£3.43</td>
                            <td class="py-3 text-muted">May 20, 2026</td>
                            <td class="py-3">
                                <span class="badge px-3 py-2 rounded-3 text-warning" style="background-color: rgba(245, 158, 11, 0.1); font-weight: 600; font-size: 0.75rem; color: #d97706 !important;">
                                    Pending
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-4 py-3 fw-bold text-dark">#1235</td>
                            <td class="py-3 fw-semibold text-dark">Math Consultation</td>
                            <td class="py-3">Dr. Smith</td>
                            <td class="py-3 text-muted">Friday 2:00 PM 30 Minutes</td>
                            <td class="py-3">
                                <span class="badge px-3 py-2 rounded-3 text-success" style="background-color: rgba(34, 197, 94, 0.1); font-weight: 600; font-size: 0.75rem;">
                                    Paid
                                </span>
                            </td>
                            <td class="py-3 fw-bold text-dark">£45.00</td>
                            <td class="py-3 text-muted">May 22, 2026</td>
                            <td class="py-3">
                                <span class="badge px-3 py-2 rounded-3 text-success" style="background-color: rgba(34, 197, 94, 0.1); font-weight: 600; font-size: 0.75rem;">
                                    Completed
                                </span>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>