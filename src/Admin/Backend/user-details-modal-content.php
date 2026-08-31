<?php
/**
 * User Details Modal Content Template
 *
 * Renders the HTML returned via AJAX for the user details modal.
 * Included by UsersAdmin::handle_ajax_get_user_details()
 *
 * Available variables:
 * @var WP_User     $user       WordPress user object
 * @var int         $user_id    User ID
 * @var string      $role       User role (provider/customer)
 * @var UsersAdmin  $controller Reference to the UsersAdmin instance
 */

defined('ABSPATH') || exit;
?>

<!-- Basic info -->
<div class="cosy-detail-section section-primary">
    <h3><span class="dashicons dashicons-admin-users"></span> <?php esc_html_e('Basic Information', 'cosy-appointments'); ?></h3>
    <div class="cosy-detail-row">
        <div class="cosy-detail-label"><?php esc_html_e('Username:', 'cosy-appointments'); ?></div>
        <div class="cosy-detail-val"><?php echo esc_html($user->user_login); ?></div>
    </div>
    <div class="cosy-detail-row">
        <div class="cosy-detail-label"><?php esc_html_e('Display Name:', 'cosy-appointments'); ?></div>
        <div class="cosy-detail-val"><?php echo esc_html($user->display_name); ?></div>
    </div>
    <div class="cosy-detail-row">
        <div class="cosy-detail-label"><?php esc_html_e('Registered Date:', 'cosy-appointments'); ?></div>
        <div class="cosy-detail-val"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($user->user_registered))); ?></div>
    </div>
</div>

<?php if ($role === 'provider'):
    $mname   = get_user_meta($user_id, 'prov_mname', true);
    $phone   = get_user_meta($user_id, 'prov_phone', true);
    $dob_raw = get_user_meta($user_id, 'dob', true);
    $dob     = (!empty($dob_raw) && strtotime($dob_raw)) ? date('d-m-Y', strtotime($dob_raw)) : '';
    $address = get_user_meta($user_id, 'prov_address', true);
    $gender  = get_user_meta($user_id, 'gender', true);
    $description = get_user_meta($user_id, 'description', true);
    $video_status = $controller->get_provider_video_status($user_id);
?>
    <!-- Provider Extra Info -->
    <div class="cosy-detail-section">
        <h3><span class="dashicons dashicons-businessman"></span> <?php esc_html_e('Provider Details', 'cosy-appointments'); ?></h3>
        <?php if (!empty($mname)): ?>
            <div class="cosy-detail-row">
                <div class="cosy-detail-label"><?php esc_html_e('Middle Name:', 'cosy-appointments'); ?></div>
                <div class="cosy-detail-val"><?php echo esc_html($mname); ?></div>
            </div>
        <?php endif; ?>
        <div class="cosy-detail-row">
            <div class="cosy-detail-label"><?php esc_html_e('Phone:', 'cosy-appointments'); ?></div>
            <div class="cosy-detail-val"><?php echo esc_html($phone ?: __('Not Provided', 'cosy-appointments')); ?></div>
        </div>
        <div class="cosy-detail-row">
            <div class="cosy-detail-label"><?php esc_html_e('Date of Birth:', 'cosy-appointments'); ?></div>
            <div class="cosy-detail-val"><?php echo esc_html($dob ?: __('Not Provided', 'cosy-appointments')); ?></div>
        </div>
        <div class="cosy-detail-row">
            <div class="cosy-detail-label"><?php esc_html_e('Gender:', 'cosy-appointments'); ?></div>
            <div class="cosy-detail-val"><?php echo esc_html($gender ?: __('Not Provided', 'cosy-appointments')); ?></div>
        </div>
        <div class="cosy-detail-row">
            <div class="cosy-detail-label"><?php esc_html_e('Address:', 'cosy-appointments'); ?></div>
            <div class="cosy-detail-val"><?php echo nl2br(esc_html($address ?: __('Not Provided', 'cosy-appointments'))); ?></div>
        </div>
        <?php if (!empty($video_status)): ?>
            <div class="cosy-detail-row">
                <div class="cosy-detail-label"><?php esc_html_e('Intro Video Status:', 'cosy-appointments'); ?></div>
                <div class="cosy-detail-val" style="text-transform: uppercase; font-weight: bold;"><?php echo esc_html($video_status); ?></div>
            </div>
        <?php endif; ?>
        <?php if (!empty($description)): ?>
            <div class="cosy-detail-row">
                <div class="cosy-detail-label"><?php esc_html_e('Bio/Description:', 'cosy-appointments'); ?></div>
                <div class="cosy-detail-val"><?php echo nl2br(esc_html($description)); ?></div>
            </div>
        <?php endif; ?>
    </div>
<?php else:
    // Fetch potential meta keys for customer details
    $phone   = get_user_meta($user_id, 'cust_phone', true) ?: get_user_meta($user_id, 'phone', true);
    $address = get_user_meta($user_id, 'cust_address', true) ?: get_user_meta($user_id, 'address', true);
?>
    <!-- Customer Extra Info -->
    <div class="cosy-detail-section">
        <h3><span class="dashicons dashicons-id"></span> <?php esc_html_e('Customer Details', 'cosy-appointments'); ?></h3>
        <div class="cosy-detail-row">
            <div class="cosy-detail-label"><?php esc_html_e('Phone:', 'cosy-appointments'); ?></div>
            <div class="cosy-detail-val"><?php echo esc_html($phone ?: __('Not Provided', 'cosy-appointments')); ?></div>
        </div>
        <div class="cosy-detail-row">
            <div class="cosy-detail-label"><?php esc_html_e('Address:', 'cosy-appointments'); ?></div>
            <div class="cosy-detail-val"><?php echo nl2br(esc_html($address ?: __('Not Provided', 'cosy-appointments'))); ?></div>
        </div>
    </div>
<?php endif; ?>

<!-- Bookings & Appointments Section -->
<div class="cosy-detail-section" style="margin-top: 15px;">
    <h3><span class="dashicons dashicons-calendar-alt"></span> <?php echo $role === 'provider' ? esc_html__('Provider Appointments & Bookings', 'cosy-appointments') : esc_html__('Customer Appointments & Bookings', 'cosy-appointments'); ?></h3>
    <?php
    $appointments = $controller->get_user_appointments($user_id, $role);
    $appt_booking_numbers = $controller->calculate_booking_ordinals($appointments);

    if (!empty($appointments)) :
    ?>
        <!-- Filter Tabs inside Modal -->
        <div class="cosy-modal-filter-tabs">
            <button type="button" class="cosy-modal-tab active" data-status="all"><?php esc_html_e('All', 'cosy-appointments'); ?></button>
            <button type="button" class="cosy-modal-tab" data-status="upcoming"><?php esc_html_e('Upcoming', 'cosy-appointments'); ?></button>
            <button type="button" class="cosy-modal-tab" data-status="in-progress"><?php esc_html_e('In Progress', 'cosy-appointments'); ?></button>
            <button type="button" class="cosy-modal-tab" data-status="completed"><?php esc_html_e('Completed', 'cosy-appointments'); ?></button>
            <button type="button" class="cosy-modal-tab" data-status="cancelled"><?php esc_html_e('Cancelled', 'cosy-appointments'); ?></button>
        </div>

        <div class="cosy-modal-appt-list-wrapper">
            <div class="cosy-modal-appt-list" style="display: flex; flex-direction: column; gap: 10px;">
                <?php foreach ($appointments as $appt) :
                    $status_info = $controller->get_appointment_status_info($appt, true);
                    $label_suffix = $controller->get_ordinal_label($appt_booking_numbers[$appt->ID] ?? 1, $role);
                    $badge_class = ($role === 'provider') ? 'badge-provider-service' : 'badge-customer-service';
                ?>
                    <div class="cosy-modal-appt-card" data-status="<?php echo esc_attr($status_info['slug']); ?>" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; display: flex; align-items: center; justify-content: space-between; gap: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; flex-wrap: wrap;">
                                <span class="badge <?php echo esc_attr($badge_class); ?>" style="margin: 0; font-size: 10px; font-weight: bold;">
                                    <?php echo esc_html($appt->service_name); ?> <span style="opacity: 0.8; font-weight: normal; font-size: 8px;">(<?php echo esc_html($label_suffix); ?>)</span>
                                </span>
                                <span style="font-size: 11px; font-weight: 700; color: #1e293b;"><?php echo esc_html(cosy_get_currency_symbol()); ?><?php echo esc_html($appt->total_payable ?: '0'); ?></span>
                            </div>
                            <div style="display: flex; flex-wrap: wrap; gap: 12px; color: #64748b; font-size: 11px;">
                                <span style="display: flex; align-items: center; gap: 4px;">
                                    <span class="dashicons dashicons-calendar-alt" style="font-size: 14px; width: 14px; height: 14px; color: #94a3b8; line-height: 14px;"></span>
                                    <span><?php echo esc_html(cosy_format_date($appt->start_date)); ?></span>
                                </span>
                                <?php if (!empty($appt->slots_timeline)) : ?>
                                    <span style="display: flex; align-items: center; gap: 4px;">
                                        <span class="dashicons dashicons-clock" style="font-size: 14px; width: 14px; height: 14px; color: #94a3b8; line-height: 14px;"></span>
                                        <span><?php echo cosy_clean_slots_timeline($appt->slots_timeline, $appt->start_date); ?></span>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div style="text-align: right; flex-shrink: 0; display: flex; flex-direction: column; align-items: flex-end; gap: 6px;">
                            <span style="display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 700; text-transform: uppercase; color: <?php echo $status_info['color']; ?>; background-color: <?php echo $status_info['bg']; ?>; border: 1px solid <?php echo $status_info['color']; ?>33;">
                                <?php echo esc_html($status_info['label']); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php
    else :
        if ($role === 'provider') {
            $services = $controller->get_provider_offered_services($user_id);
            if (!empty($services)) {
                echo '<div style="margin-top: 10px;">';
                echo '<p style="font-size: 12px; color: #64748b; margin-bottom: 6px;">' . esc_html__('This provider currently has no active bookings but offers the following services:', 'cosy-appointments') . '</p>';
                foreach ($services as $srv) {
                    echo '<span class="badge badge-provider-service">' . esc_html($srv) . '</span> ';
                }
                echo '</div>';
            } else {
                echo '<p style="color: #94a3b8; font-style: italic; font-size: 12px; margin-top: 10px;">' . esc_html__('No offered services or active bookings found.', 'cosy-appointments') . '</p>';
            }
        } else {
            echo '<p style="color: #94a3b8; font-style: italic; font-size: 12px; margin-top: 10px;">' . esc_html__('No active bookings found for this customer.', 'cosy-appointments') . '</p>';
        }
    endif;
    ?>
</div>
