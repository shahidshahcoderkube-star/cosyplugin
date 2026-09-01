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
                <div class="cosy-detail-val cosy-detail-val-uppercase"><?php echo esc_html($video_status); ?></div>
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
<div class="cosy-detail-section cosy-detail-section-spaced">
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
            <div class="cosy-modal-appt-list">
                <?php foreach ($appointments as $appt) :
                    $status_info = $controller->get_appointment_status_info($appt, true);
                    $label_suffix = $controller->get_ordinal_label($appt_booking_numbers[$appt->ID] ?? 1, $role);
                    $badge_class = ($role === 'provider') ? 'badge-provider-service' : 'badge-customer-service';
                ?>
                    <div class="cosy-modal-appt-card" data-status="<?php echo esc_attr($status_info['slug']); ?>">
                        <div class="cosy-modal-appt-card-left">
                            <div class="cosy-modal-appt-header">
                                <span class="badge <?php echo esc_attr($badge_class); ?> cosy-modal-appt-badge">
                                    <?php echo esc_html($appt->service_name); ?> <span class="cosy-appt-ordinal-suffix">(<?php echo esc_html($label_suffix); ?>)</span>
                                </span>
                                <span class="cosy-modal-appt-total"><?php echo esc_html(cosy_get_currency_symbol()); ?><?php echo esc_html($appt->total_payable ?: '0'); ?></span>
                            </div>
                            <div class="cosy-modal-appt-meta-list">
                                <span class="cosy-modal-appt-meta-item">
                                    <span class="dashicons dashicons-calendar-alt cosy-modal-appt-meta-icon"></span>
                                    <span><?php echo esc_html(cosy_format_date($appt->start_date)); ?></span>
                                </span>
                                <?php if (!empty($appt->slots_timeline)) : ?>
                                    <span class="cosy-modal-appt-meta-item">
                                        <span class="dashicons dashicons-clock cosy-modal-appt-meta-icon"></span>
                                        <span><?php echo cosy_clean_slots_timeline($appt->slots_timeline, $appt->start_date); ?></span>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="cosy-modal-appt-card-right">
                            <span class="cosy-modal-appt-status-badge cosy-modal-status-<?php echo esc_attr($status_info['slug']); ?>">
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
                echo '<div class="cosy-detail-section-spaced">';
                echo '<p class="cosy-logs-section-sub">' . esc_html__('This provider currently has no active bookings but offers the following services:', 'cosy-appointments') . '</p>';
                foreach ($services as $srv) {
                    echo '<span class="badge badge-provider-service">' . esc_html($srv) . '</span> ';
                }
                echo '</div>';
            } else {
                echo '<p class="cosy-muted-empty-text cosy-detail-section-spaced">' . esc_html__('No offered services or active bookings found.', 'cosy-appointments') . '</p>';
            }
        } else {
            echo '<p class="cosy-muted-empty-text cosy-detail-section-spaced">' . esc_html__('No active bookings found for this customer.', 'cosy-appointments') . '</p>';
        }
    endif;
    ?>
</div>

