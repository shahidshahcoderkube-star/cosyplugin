<?php

namespace Cosy\Appointments\Admin;

use Cosy\Appointments\Loader;
use Cosy\Appointments\Common\GlobalCommonFunctions;
use WP_User_Query;

class UsersAdmin
{
    use GlobalCommonFunctions;

    /**
     * Register hooks with the plugin loader.
     */
    public function register(Loader $loader): void
    {
        // AJAX handler for updating user status
        $loader->add_action('wp_ajax_cosy_admin_update_user_status', $this, 'handle_ajax_update_user_status');

        // AJAX handler for resending verification email
        $loader->add_action('wp_ajax_cosy_admin_resend_verification', $this, 'handle_ajax_resend_verification');

        // AJAX handler for fetching full user details
        $loader->add_action('wp_ajax_cosy_admin_get_user_details', $this, 'handle_ajax_get_user_details');

        // AJAX handler for bulk deleting users
        $loader->add_action('wp_ajax_cosy_admin_delete_users', $this, 'handle_ajax_delete_users');

        // Clear user appointments cache when appointment is updated or deleted
        $loader->add_action('save_post_cosy_appointment', $this, 'clear_user_appointments_cache', 10, 2);
        $loader->add_action('before_delete_post', $this, 'clear_user_appointments_cache_before_delete');
    }

    /**
     * Renders the unified Users management page.
     */
    public function render_users_page(): void
    {
        if (!current_user_can('manage_options') && !current_user_can('manage_cosy_appointments')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'cosy-appointments'));
        }

        // Filters and Search
        $role_filter = isset($_GET['role']) ? sanitize_text_field($_GET['role']) : 'all';
        $service_filter = isset($_GET['service']) ? sanitize_text_field($_GET['service']) : '';
        $search_query = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $number = 20; // Users per page

        $args = [
            'orderby'      => 'registered',
            'order'        => 'DESC',
            'number'       => $number,
            'offset'       => ($paged - 1) * $number,
            'count_total'  => true,
        ];

        if ($role_filter === 'provider') {
            $args['role'] = 'provider';
        } elseif ($role_filter === 'customer') {
            $args['role'] = 'customer';
        } else {
            $args['role__in'] = ['provider', 'customer'];
        }

        if (!empty($search_query)) {
            $args['search'] = '*' . esc_attr($search_query) . '*';
            $args['search_columns'] = ['user_login', 'user_email', 'user_nicename', 'display_name'];
        }

        // Service Filter Logic
        $matching_user_ids = null;
        if (!empty($service_filter)) {
            global $wpdb;

            // 1. Get user IDs from appointments with this service
            $appt_user_ids = $wpdb->get_col($wpdb->prepare(
                "SELECT DISTINCT pm.meta_value 
                 FROM {$wpdb->postmeta} pm
                 JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                 WHERE p.post_type = 'cosy_appointment'
                   AND pm.meta_key IN ('cosy_provider_id', 'cosy_customer_id')
                   AND p.ID IN (
                       SELECT post_id 
                       FROM {$wpdb->postmeta} 
                       WHERE meta_key = 'cosy_service_name' AND meta_value = %s
                   )",
                $service_filter
            ));

            // 2. Get provider IDs offering this service
            $prov_table = $wpdb->prefix . 'provider_services';
            $provider_user_ids = $wpdb->get_col($wpdb->prepare(
                "SELECT DISTINCT ps.provider_id 
                 FROM $prov_table ps
                 JOIN {$wpdb->posts} p ON ps.service_id = p.ID
                 WHERE ps.checkbox_status = 'yes' AND p.post_title = %s",
                $service_filter
            ));

            $merged_ids = array_unique(array_merge(array_map('intval', $appt_user_ids), array_map('intval', $provider_user_ids)));
            $matching_user_ids = !empty($merged_ids) ? $merged_ids : [0];
        }

        if ($matching_user_ids !== null) {
            $args['include'] = $matching_user_ids;
        }

        $user_query = new WP_User_Query($args);
        $users = $user_query->get_results();
        $total_users = $user_query->get_total();
        $total_pages = ceil($total_users / $number);

        // Security nonces
        $status_nonce = wp_create_nonce('cosy_admin_status_nonce');
        $email_nonce  = wp_create_nonce('cosy_admin_email_nonce');
        $details_nonce = wp_create_nonce('cosy_admin_details_nonce');
        $delete_nonce = wp_create_nonce('cosy_admin_delete_nonce');

?>
        <div class="wrap cosy-users-admin">
            <h1 class="wp-heading-inline"><?php esc_html_e('Manage Users', 'cosy-appointments'); ?></h1>
            <hr class="wp-header-end">

            <!-- Premium Control Bar -->
            <div class="cosy-control-bar">
                <div class="cosy-control-left">
                    <div class="cosy-role-tabs">
                        <?php
                        $service_param = !empty($service_filter) ? '&service=' . urlencode($service_filter) : '';
                        $search_param = !empty($search_query) ? '&s=' . urlencode($search_query) : '';
                        ?>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=cosy-users' . $service_param . $search_param)); ?>" class="cosy-tab-pill <?php echo $role_filter === 'all' ? 'active' : ''; ?>">
                            <?php esc_html_e('All Roles', 'cosy-appointments'); ?>
                        </a>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=cosy-users&role=provider' . $service_param . $search_param)); ?>" class="cosy-tab-pill <?php echo $role_filter === 'provider' ? 'active' : ''; ?>">
                            <?php esc_html_e('Providers', 'cosy-appointments'); ?>
                        </a>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=cosy-users&role=customer' . $service_param . $search_param)); ?>" class="cosy-tab-pill <?php echo $role_filter === 'customer' ? 'active' : ''; ?>">
                            <?php esc_html_e('Customers', 'cosy-appointments'); ?>
                        </a>
                    </div>

                    <button type="button" class="cosy-btn-delete-selected-modern" id="cosy-btn-delete-selected" disabled>
                        <span class="dashicons dashicons-trash" style="font-size: 16px; width: 16px; height: 16px; margin-right: 6px; display: inline-block; vertical-align: middle;"></span>
                        <span class="cosy-btn-text" style="vertical-align: middle;"><?php esc_html_e('Delete', 'cosy-appointments'); ?></span>
                    </button>
                </div>

                <div class="cosy-control-right">
                    <form method="get" class="cosy-search-form-modern" style="display: flex; gap: 10px; align-items: center;">
                        <input type="hidden" name="page" value="cosy-users">
                        <?php if ($role_filter !== 'all'): ?>
                            <input type="hidden" name="role" value="<?php echo esc_attr($role_filter); ?>">
                        <?php endif; ?>

                        <!-- Service Filter Dropdown -->
                        <div class="cosy-filter-wrapper" style="position: relative;">
                            <select name="service" onchange="this.form.submit()" style="border-radius: 8px; border: 1.5px solid #cbd5e1; height: 34px; padding: 0 32px 0 12px; font-size: 13px; color: #334155; background: #ffffff url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%2523475569%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E') no-repeat right 12px center; background-size: 8px auto; -webkit-appearance: none; -moz-appearance: none; appearance: none; outline: none; cursor: pointer; min-width: 160px; transition: border-color 0.2s ease;">
                                <option value=""><?php esc_html_e('All Services', 'cosy-appointments'); ?></option>
                                <?php
                                $all_services = get_posts([
                                    'post_type'      => 'cosy_service',
                                    'post_status'    => 'publish',
                                    'posts_per_page' => -1,
                                    'orderby'        => 'title',
                                    'order'          => 'ASC'
                                ]);
                                foreach ($all_services as $srv) :
                                    ?>
                                    <option value="<?php echo esc_attr($srv->post_title); ?>" <?php selected($service_filter, $srv->post_title); ?>>
                                        <?php echo esc_html($srv->post_title); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="cosy-search-input-wrapper">
                            <span class="dashicons dashicons-search" style="color: #94a3b8; margin-left: 10px; margin-right: 2px;"></span>
                            <input type="search" id="user-search-input" name="s" value="<?php echo esc_attr($search_query); ?>" placeholder="<?php esc_attr_e('Search users...', 'cosy-appointments'); ?>">
                            <button type="submit" class="cosy-search-btn">
                                <?php esc_html_e('Search', 'cosy-appointments'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Users Table -->
            <table class="wp-list-table widefat fixed striped table-view-list cosy-users-table">
                <thead>
                    <tr>
                        <td id="cb" class="manage-column column-cb check-column" style="width: 45px; padding: 8px 20px 8px 15px; vertical-align: middle;">
                            <input type="checkbox" id="cosy-select-all-users">
                        </td>
                        <th scope="col" class="manage-column" style="width: 220px;"><?php esc_html_e('Username & Name', 'cosy-appointments'); ?></th>
                        <th scope="col" class="manage-column" style="width: 100px;"><?php esc_html_e('Role', 'cosy-appointments'); ?></th>
                        <th scope="col" class="manage-column" style="width: 170px;"><?php esc_html_e('Email', 'cosy-appointments'); ?></th>
                        <th scope="col" class="manage-column" style="width: 320px;"><?php esc_html_e('Services', 'cosy-appointments'); ?></th>
                        <th scope="col" class="manage-column" style="width: 130px;"><?php esc_html_e('Email Verify', 'cosy-appointments'); ?></th>
                        <th scope="col" class="manage-column" style="width: 120px;"><?php esc_html_e('Status', 'cosy-appointments'); ?></th>
                        <th scope="col" class="manage-column" style="width: 170px;"><?php esc_html_e('Actions', 'cosy-appointments'); ?></th>
                    </tr>
                </thead>
                <tbody id="the-list">
                    <?php if (empty($users)) : ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: #64748b;">
                                <?php esc_html_e('No users found matching the criteria.', 'cosy-appointments'); ?>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($users as $user) :
                            $user_id = $user->ID;
                            $roles = (array) $user->roles;
                            $primary_role = in_array('provider', $roles) ? 'provider' : (in_array('customer', $roles) ? 'customer' : 'other');

                            // 1. Email verification status from 'account_status'
                            $email_status = get_user_meta($user_id, 'account_status', true);
                            if (empty($email_status)) {
                                $email_status = 'active'; // Default active if empty
                            }

                            // 2. Account activation status
                            if ($primary_role === 'provider') {
                                $account_status = get_user_meta($user_id, 'cosy_provider_status', true);
                                if (empty($account_status)) {
                                    $account_status = 'active';
                                }
                            } else {
                                // For customers, we use account_status value (active vs deactive)
                                $account_status = ($email_status === 'deactive') ? 'deactive' : 'active';
                            }
                        ?>
                            <tr id="user-row-<?php echo $user_id; ?>">
                                <th scope="row" class="check-column" style="padding: 8px 20px 8px 15px; vertical-align: middle; width: 45px;">
                                    <input type="checkbox" class="cosy-user-checkbox" value="<?php echo $user_id; ?>">
                                </th>
                                <td>
                                    <strong><?php echo esc_html($user->display_name); ?></strong>
                                    <div style="font-size: 11px; color: #64748b;">@<?php echo esc_html($user->user_login); ?></div>
                                </td>
                                <td>
                                    <?php if ($primary_role === 'provider'): ?>
                                        <span class="badge badge-provider"><?php esc_html_e('Provider', 'cosy-appointments'); ?></span>
                                    <?php elseif ($primary_role === 'customer'): ?>
                                        <span class="badge badge-customer"><?php esc_html_e('Customer', 'cosy-appointments'); ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-other"><?php echo esc_html(ucfirst($primary_role)); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="mailto:<?php echo esc_attr($user->user_email); ?>"><?php echo esc_html($user->user_email); ?></a>
                                </td>
                                <td>
                                    <?php
                                    global $wpdb;
                                    $meta_key_user = ($primary_role === 'provider') ? 'cosy_provider_id' : 'cosy_customer_id';
                                    $transient_key = 'cosy_user_appts_' . $user_id . '_' . $primary_role;
                                    $appointments = get_transient($transient_key);
                                    if (false === $appointments) {
                                        $appointments = $wpdb->get_results(
                                            $wpdb->prepare(
                                                "SELECT p.ID, 
                                                       (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = p.ID AND meta_key = 'cosy_service_name' LIMIT 1) as service_name,
                                                       (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = p.ID AND meta_key = 'cosy_start_date' LIMIT 1) as start_date,
                                                       (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = p.ID AND meta_key = 'cosy_slots_timeline' LIMIT 1) as slots_timeline,
                                                       (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = p.ID AND meta_key = 'cosy_booking_status' LIMIT 1) as booking_status,
                                                       (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = p.ID AND meta_key = 'cosy_total_payable' LIMIT 1) as total_payable
                                                FROM {$wpdb->posts} p
                                                JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                                                WHERE p.post_type = 'cosy_appointment'
                                                  AND p.post_status = 'publish'
                                                  AND pm.meta_key = %s
                                                  AND pm.meta_value = %d
                                                ORDER BY p.ID DESC",
                                                $meta_key_user,
                                                $user_id
                                            )
                                        );
                                        set_transient($transient_key, $appointments, 10 * MINUTE_IN_SECONDS);
                                    }

                                    if (!empty($appointments)) :
                                        // Calculate service booking ordinal numbers (chronological order)
                                        $chrono_appts = array_reverse($appointments);
                                        $service_counts = [];
                                        $appt_booking_numbers = [];
                                        foreach ($chrono_appts as $a) {
                                            $srv_name = $a->service_name;
                                            if (!isset($service_counts[$srv_name])) {
                                                $service_counts[$srv_name] = 0;
                                            }
                                            $service_counts[$srv_name]++;
                                            $appt_booking_numbers[$a->ID] = $service_counts[$srv_name];
                                        }

                                        $count_appt = 0;
                                        foreach ($appointments as $appt) :
                                            $count_appt++;
                                            if ($count_appt > 2) {
                                                echo '<div style="font-size: 10px; color: #94a3b8; font-style: italic; margin-top: 4px;">' . sprintf(__('& %d more...', 'cosy-appointments'), count($appointments) - 2) . '</div>';
                                                break;
                                            }
                                            $badge_class = ($primary_role === 'provider') ? 'badge-provider-service' : 'badge-customer-service';

                                            // Determine execution status based on date comparison
                                            $status = !empty($appt->booking_status) ? $appt->booking_status : 'pending';
                                            if ($status === 'cancelled') {
                                                $status_label = __('Cancelled', 'cosy-appointments');
                                                $status_color = '#991b1b';
                                            } else {
                                                $appt_time = strtotime($appt->start_date);
                                                $today_time = strtotime('today');
                                                if ($appt_time < $today_time) {
                                                    $status_label = __('Completed', 'cosy-appointments');
                                                    $status_color = '#166534';
                                                } elseif ($appt_time === $today_time) {
                                                    $status_label = __('In Progress', 'cosy-appointments');
                                                    $status_color = '#7c3aed';
                                                } else {
                                                    $status_label = __('Upcoming', 'cosy-appointments');
                                                    $status_color = '#1e40af';
                                                }
                                            }

                                            // Determine booking ordinal suffix (1st, 2nd, 3rd, etc.)
                                            $booking_num = $appt_booking_numbers[$appt->ID] ?? 1;
                                            $ends = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');
                                            if ((($booking_num % 100) >= 11) && (($booking_num % 100) <= 13)) {
                                                $ordinal = $booking_num . 'th';
                                            } else {
                                                $ordinal = $booking_num . $ends[$booking_num % 10];
                                            }
                                            $label_suffix = ($primary_role === 'provider') ? sprintf(__('%s Session', 'cosy-appointments'), $ordinal) : sprintf(__('%s Booking', 'cosy-appointments'), $ordinal);
                                    ?>
                                            <div class="cosy-appt-info-block" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 8px; margin-bottom: 5px; font-size: 11px;">
                                                <div style="font-weight: 600; margin-bottom: 4px; display: flex; align-items: center; justify-content: space-between; gap: 4px;">
                                                    <span class="badge <?php echo esc_attr($badge_class); ?>" style="margin: 0; padding: 2px 6px; font-size: 9px; font-weight: bold;">
                                                        <?php echo esc_html($appt->service_name); ?> <span style="opacity: 0.8; font-weight: normal; font-size: 8px;">(<?php echo esc_html($label_suffix); ?>)</span>
                                                    </span>
                                                    <span style="font-size: 9px; font-weight: 700; text-transform: uppercase; color: <?php echo $status_color; ?>;">
                                                        <?php echo esc_html($status_label); ?>
                                                    </span>
                                                </div>
                                                <div style="display: flex; align-items: center; gap: 4px; color: #475569; font-size: 10px; margin-top: 2px;">
                                                    <span class="dashicons dashicons-calendar-alt" style="font-size: 12px; width: 12px; height: 12px; color: #64748b; line-height: 12px;"></span>
                                                    <span><?php echo esc_html($appt->start_date); ?></span>
                                                </div>
                                                <?php if (!empty($appt->slots_timeline)) : ?>
                                                    <div style="display: flex; align-items: center; gap: 4px; color: #475569; font-size: 10px; margin-top: 2px; word-break: break-all;">
                                                        <span class="dashicons dashicons-clock" style="font-size: 12px; width: 12px; height: 12px; color: #64748b; line-height: 12px;"></span>
                                                        <span><?php echo esc_html($appt->slots_timeline); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                    <?php
                                        endforeach;
                                    else :
                                        if ($primary_role === 'provider') {
                                            $services_table = $wpdb->prefix . 'provider_services';
                                            $services = $wpdb->get_col(
                                                $wpdb->prepare(
                                                    "SELECT DISTINCT p.post_title 
                                                     FROM $services_table ps
                                                     JOIN {$wpdb->posts} p ON ps.service_id = p.ID
                                                     WHERE ps.provider_id = %d AND ps.checkbox_status = 'yes'",
                                                    $user_id
                                                )
                                            );
                                            if (!empty($services)) {
                                                echo '<div style="font-size: 10px; color: #64748b; font-style: italic; margin-bottom: 4px;">' . __('Offers:', 'cosy-appointments') . '</div>';
                                                foreach ($services as $srv) {
                                                    echo '<span class="badge badge-provider-service">' . esc_html($srv) . '</span> ';
                                                }
                                            } else {
                                                echo '<span style="color: #94a3b8; font-style: italic; font-size: 11px;">' . __('No Services/Bookings', 'cosy-appointments') . '</span>';
                                            }
                                        } else {
                                            echo '<span style="color: #94a3b8; font-style: italic; font-size: 11px;">' . __('No Bookings Yet', 'cosy-appointments') . '</span>';
                                        }
                                    endif;
                                    ?>
                                </td>
                                <td>
                                    <?php if ($email_status === 'pending') : ?>
                                        <span class="badge badge-pending email-verify-badge-<?php echo $user_id; ?>">
                                            <span class="dashicons dashicons-clock" style="font-size: 14px; width: 14px; height: 14px; vertical-align: text-bottom; margin-right: 3px;"></span>
                                            <?php esc_html_e('Pending', 'cosy-appointments'); ?>
                                        </span>
                                    <?php else : ?>
                                        <span class="badge badge-verified email-verify-badge-<?php echo $user_id; ?>">
                                            <span class="dashicons dashicons-yes" style="font-size: 14px; width: 14px; height: 14px; vertical-align: text-bottom; margin-right: 3px;"></span>
                                            <?php esc_html_e('Verified', 'cosy-appointments'); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($primary_role === 'customer') : ?>
                                        <span style="color: #94a3b8; font-weight: 600;"><?php esc_html_e('N/A', 'cosy-appointments'); ?></span>
                                    <?php else : ?>
                                        <select class="cosy-admin-status-dropdown" data-user-id="<?php echo $user_id; ?>" data-role="<?php echo $primary_role; ?>">
                                            <option value="active" <?php selected($account_status, 'active'); ?>><?php esc_html_e('Active', 'cosy-appointments'); ?></option>
                                            <option value="deactive" <?php selected($account_status, 'deactive'); ?>><?php esc_html_e('Deactive', 'cosy-appointments'); ?></option>
                                        </select>
                                        <span class="cosy-status-spinner spinner" style="float: none; margin: 0 0 0 5px; vertical-align: middle;"></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 6px; flex-wrap: wrap; align-items: center;">
                                        <button type="button" class="button button-small btn-view-cosy-user-details" data-user-id="<?php echo $user_id; ?>">
                                            <?php esc_html_e('View Details', 'cosy-appointments'); ?>
                                        </button>

                                        <?php if ($email_status === 'pending') : ?>
                                            <button type="button" class="button button-small cosy-btn-resend-verification" data-user-id="<?php echo $user_id; ?>" data-role="<?php echo $primary_role; ?>">
                                                <?php esc_html_e('Resend Email', 'cosy-appointments'); ?>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination Nav -->
            <?php if ($total_pages > 1): ?>
                <div class="tablenav bottom">
                    <div class="tablenav-pages">
                        <span class="displaying-num"><?php printf(esc_html(_n('%s user', '%s users', $total_users, 'cosy-appointments')), number_format_i18n($total_users)); ?></span>
                        <span class="pagination-links">
                            <?php if ($paged > 1): ?>
                                <a class="prev-page button" href="<?php echo esc_url(add_query_arg('paged', $paged - 1)); ?>">&lsaquo;</a>
                            <?php endif; ?>
                            <span class="paging-input">
                                <span class="tablenav-paging-text"><?php printf(esc_html__('%1$s of %2$s', 'cosy-appointments'), $paged, $total_pages); ?></span>
                            </span>
                            <?php if ($paged < $total_pages): ?>
                                <a class="next-page button" href="<?php echo esc_url(add_query_arg('paged', $paged + 1)); ?>">&rsaquo;</a>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- HTML Modal for User Details -->
        <div id="cosyAdminUserModal" class="cosy-user-modal">
            <div class="cosy-user-modal-content">
                <div class="cosy-user-modal-header">
                    <h2 id="modalAdminUserTitle"><?php esc_html_e('User Details', 'cosy-appointments'); ?></h2>
                    <span class="cosy-user-modal-close">&times;</span>
                </div>
                <div class="cosy-user-modal-body" id="modalAdminUserBody">
                    <div style="text-align: center; padding: 30px;">
                        <span class="spinner is-active" style="float: none; margin: 0 auto;"></span>
                        <p style="margin-top: 10px; color: #64748b;"><?php esc_html_e('Loading details...', 'cosy-appointments'); ?></p>
                    </div>
                </div>
                <div class="cosy-user-modal-footer">
                    <button type="button" class="cosy-modal-btn-close cosy-user-modal-close-btn"><?php esc_html_e('Close Details', 'cosy-appointments'); ?></button>
                </div>
            </div>
        </div>

        <!-- AJAX Script -->
        <script>
            jQuery(document).ready(function($) {
                // 1. Update status
                $('.cosy-admin-status-dropdown').on('change', function() {
                    var select = $(this);
                    var userId = select.data('user-id');
                    var role = select.data('role');
                    var status = select.val();
                    var spinner = select.next('.spinner');

                    select.prop('disabled', true);
                    spinner.addClass('is-active');

                    $.post(ajaxurl, {
                        action: 'cosy_admin_update_user_status',
                        security: <?php echo wp_json_encode($status_nonce); ?>,
                        user_id: userId,
                        role: role,
                        status: status
                    }, function(response) {
                        select.prop('disabled', false);
                        spinner.removeClass('is-active');

                        if (response.success) {
                            var originalColor = select.css('border-color');
                            select.css('border-color', '#46b450');
                            setTimeout(function() {
                                select.css('border-color', originalColor);
                            }, 1500);
                        } else {
                            alert(response.data || <?php echo wp_json_encode(__('Failed to update status.', 'cosy-appointments')); ?>);
                        }
                    });
                });

                // 2. Resend verification email
                $('.cosy-btn-resend-verification').on('click', function() {
                    var btn = $(this);
                    var userId = btn.data('user-id');
                    var role = btn.data('role');

                    if (!confirm(<?php echo wp_json_encode(__('Are you sure you want to resend the verification email to this user?', 'cosy-appointments')); ?>)) {
                        return;
                    }

                    btn.prop('disabled', true).text(<?php echo wp_json_encode(__('Sending...', 'cosy-appointments')); ?>);

                    $.post(ajaxurl, {
                        action: 'cosy_admin_resend_verification',
                        security: <?php echo wp_json_encode($email_nonce); ?>,
                        user_id: userId,
                        role: role
                    }, function(response) {
                        btn.prop('disabled', false).text(<?php echo wp_json_encode(__('Resend Email', 'cosy-appointments')); ?>);
                        if (response.success) {
                            alert(response.data || <?php echo wp_json_encode(__('Verification email sent successfully.', 'cosy-appointments')); ?>);
                        } else {
                            alert(response.data || <?php echo wp_json_encode(__('Failed to send email.', 'cosy-appointments')); ?>);
                        }
                    });
                });

                // 3. Modal open / View Details
                var modal = $('#cosyAdminUserModal');
                $('.btn-view-cosy-user-details').on('click', function() {
                    var userId = $(this).data('user-id');
                    modal.show();

                    // Put loading screen inside body
                    $('#modalAdminUserBody').html(
                        '<div style="text-align: center; padding: 30px;">' +
                        '<span class="spinner is-active" style="float: none; margin: 0 auto;"></span>' +
                        '<p style="margin-top: 10px; color: #64748b;">' + <?php echo wp_json_encode(__('Loading details...', 'cosy-appointments')); ?> + '</p>' +
                        '</div>'
                    );

                    $.post(ajaxurl, {
                        action: 'cosy_admin_get_user_details',
                        security: <?php echo wp_json_encode($details_nonce); ?>,
                        user_id: userId
                    }, function(response) {
                        if (response.success) {
                            $('#modalAdminUserBody').html(response.data.html);
                            $('#modalAdminUserTitle').text(response.data.title);
                        } else {
                            $('#modalAdminUserBody').html(
                                '<div style="color: #c53030; padding: 15px; border-radius: 8px; background: #fff5f5;">' +
                                (response.data || <?php echo wp_json_encode(__('Failed to load details.', 'cosy-appointments')); ?>) +
                                '</div>'
                            );
                        }
                    });
                });

                // Close Modal on Close button / background click
                $('.cosy-user-modal-close, .cosy-user-modal-close-btn').on('click', function() {
                    modal.hide();
                });
                $(window).on('click', function(event) {
                    if (event.target == modal[0]) {
                        modal.hide();
                    }
                });

                // 4. Bulk Delete Logic
                $('#cosy-select-all-users').on('change', function() {
                    var checked = $(this).prop('checked');
                    $('.cosy-user-checkbox').prop('checked', checked);
                    toggleDeleteButton();
                });

                $(document).on('change', '.cosy-user-checkbox', function() {
                    var allChecked = $('.cosy-user-checkbox:checked').length === $('.cosy-user-checkbox').length;
                    $('#cosy-select-all-users').prop('checked', allChecked);
                    toggleDeleteButton();
                });

                function toggleDeleteButton() {
                    var checkedCount = $('.cosy-user-checkbox:checked').length;
                    $('#cosy-btn-delete-selected').prop('disabled', checkedCount === 0);
                }

                $('#cosy-btn-delete-selected').on('click', function() {
                    var selectedIds = [];
                    $('.cosy-user-checkbox:checked').each(function() {
                        selectedIds.push($(this).val());
                    });

                    if (selectedIds.length === 0) return;

                    var confirmMsg = <?php echo wp_json_encode(__('Are you sure you want to delete the selected users? This action cannot be undone and will permanently remove their profile data.', 'cosy-appointments')); ?>;
                    if (!confirm(confirmMsg)) {
                        return;
                    }

                    var btn = $(this);
                    btn.prop('disabled', true);
                    btn.find('.cosy-btn-text').text(<?php echo wp_json_encode(__('Deleting...', 'cosy-appointments')); ?>);

                    $.post(ajaxurl, {
                        action: 'cosy_admin_delete_users',
                        security: <?php echo wp_json_encode($delete_nonce); ?>,
                        user_ids: selectedIds
                    }, function(response) {
                        btn.find('.cosy-btn-text').text(<?php echo wp_json_encode(__('Delete', 'cosy-appointments')); ?>);
                        if (response.success) {
                            alert(response.data);
                            // Remove deleted rows from DOM
                            selectedIds.forEach(function(id) {
                                $('#user-row-' + id).fadeOut(400, function() {
                                    $(this).remove();
                                });
                            });
                            $('#cosy-select-all-users').prop('checked', false);
                            toggleDeleteButton();
                        } else {
                            alert(response.data || <?php echo wp_json_encode(__('Failed to delete users.', 'cosy-appointments')); ?>);
                            btn.prop('disabled', false);
                            toggleDeleteButton();
                        }
                    });
                });
            });
        </script>
    <?php
    }

    /**
     * AJAX handler: Updates user status.
     */
    public function handle_ajax_update_user_status(): void
    {
        check_ajax_referer('cosy_admin_status_nonce', 'security');

        if (!current_user_can('manage_options') && !current_user_can('manage_cosy_appointments')) {
            wp_send_json_error(__('Permission denied.', 'cosy-appointments'));
        }

        $user_id = intval($_POST['user_id'] ?? 0);
        $role    = sanitize_text_field($_POST['role'] ?? '');
        $status  = sanitize_text_field($_POST['status'] ?? '');

        if (!$user_id || !in_array($status, ['active', 'deactive'])) {
            wp_send_json_error(__('Invalid data provided.', 'cosy-appointments'));
        }

        if ($role === 'provider') {
            // For providers, we update cosy_provider_status (which controls profile visibility on the frontend)
            $old_status = get_user_meta($user_id, 'cosy_provider_status', true);
            if (empty($old_status)) {
                $old_status = 'active';
            }

            update_user_meta($user_id, 'cosy_provider_status', $status);

            // Send notification email to provider if status changed
            if ($old_status !== $status) {
                $user = get_userdata($user_id);
                if ($user) {
                    if ($status === 'active') {
                        $subject = __("Your Provider Account is Now Active!", 'cosy-appointments');
                        $html_content = "
                            <p>Hello <strong>" . esc_html($user->display_name ?: $user->user_login) . "</strong>,</p>
                            <p>Congratulations! Your account has been reviewed and approved by the administrator. Your profile is now live and visible to parents.</p>
                            <p style='text-align: center; margin: 30px 0;'>
                                <a href='" . esc_url(home_url('/login')) . "' style='background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%); color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 50px; font-weight: 600; display: inline-block; box-shadow: 0 4px 12px rgba(164, 67, 144, 0.2);'>Login to Your Account</a>
                            </p>
                            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 25px;'>Thank you,<br><strong>Cosy Appointments Team</strong></p>
                        ";
                    } else {
                        $subject = __("Your Provider Account is Temporarily Deactivated", 'cosy-appointments');
                        $html_content = "
                            <p>Hello <strong>" . esc_html($user->display_name ?: $user->user_login) . "</strong>,</p>
                            <p>Your provider account has been temporarily deactivated by the site administrator. During this time, your services will not be bookable and your profile will not be visible to customers.</p>
                            <p>If you believe this is a mistake or have questions, please reach out to our administration/support team.</p>
                            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 25px;'>Thank you,<br><strong>Cosy Appointments Team</strong></p>
                        ";
                    }
                    cosy_send_html_email($user->user_email, $subject, $subject, $html_content);
                }
            }
        } else {
            // For customers, deactive status suspends them from logging in (saved inside account_status)
            $old_status = get_user_meta($user_id, 'account_status', true);
            if (empty($old_status)) {
                $old_status = 'active';
            }

            update_user_meta($user_id, 'account_status', $status);

            // Send notification email to customer if status changed
            if ($old_status !== $status) {
                $user = get_userdata($user_id);
                if ($user) {
                    if ($status === 'active') {
                        $subject = __("Your Customer Account has been Re-activated", 'cosy-appointments');
                        $html_content = "
                            <p>Hello <strong>" . esc_html($user->display_name ?: $user->user_login) . "</strong>,</p>
                            <p>Your customer account has been re-activated by the administrator. You can now log in and book appointments as usual.</p>
                            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 25px;'>Thank you,<br><strong>Cosy Appointments Team</strong></p>
                        ";
                    } else {
                        $subject = __("Your Customer Account has been Deactivated", 'cosy-appointments');
                        $html_content = "
                            <p>Hello <strong>" . esc_html($user->display_name ?: $user->user_login) . "</strong>,</p>
                            <p>Your customer account has been temporarily deactivated by the administrator. During this time, you will not be able to log in to book appointments.</p>
                            <p>For questions or support, please reach out to our team.</p>
                            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 25px;'>Thank you,<br><strong>Cosy Appointments Team</strong></p>
                        ";
                    }
                    cosy_send_html_email($user->user_email, $subject, $subject, $html_content);
                }
            }
        }

        $user = get_userdata($user_id);
        if ($user) {
            \Cosy\Appointments\Common\LogManager::log(
                'users',
                'user_status_updated',
                sprintf(__('Admin updated user "%s" (ID: %d, Role: %s) status from "%s" to "%s".', 'cosy-appointments'), $user->display_name ?: $user->user_login, $user_id, ucfirst($role), $old_status, $status)
            );
        }

        wp_send_json_success(__('Status updated successfully.', 'cosy-appointments'));
    }

    /**
     * AJAX handler: Resends verification email.
     */
    public function handle_ajax_resend_verification(): void
    {
        check_ajax_referer('cosy_admin_email_nonce', 'security');

        if (!current_user_can('manage_options') && !current_user_can('manage_cosy_appointments')) {
            wp_send_json_error(__('Permission denied.', 'cosy-appointments'));
        }

        $user_id = intval($_POST['user_id'] ?? 0);
        $role    = sanitize_text_field($_POST['role'] ?? '');

        if (!$user_id) {
            wp_send_json_error(__('Invalid user ID.', 'cosy-appointments'));
        }

        $sent = $this->send_verification_email($user_id, $role);

        if ($sent) {
            $user = get_userdata($user_id);
            if ($user) {
                \Cosy\Appointments\Common\LogManager::log(
                    'users',
                    'verification_resent',
                    sprintf(__('Admin resent verification/activation email to user "%s" (ID: %d).', 'cosy-appointments'), $user->display_name ?: $user->user_login, $user_id)
                );
            }
            wp_send_json_success(__('Verification email resent successfully.', 'cosy-appointments'));
        } else {
            wp_send_json_error(__('Failed to send verification email. Please check SMTP settings.', 'cosy-appointments'));
        }
    }

    /**
     * AJAX handler: Fetches full user details HTML for modal display.
     */
    public function handle_ajax_get_user_details(): void
    {
        check_ajax_referer('cosy_admin_details_nonce', 'security');

        if (!current_user_can('manage_options') && !current_user_can('manage_cosy_appointments')) {
            wp_send_json_error(__('Permission denied.', 'cosy-appointments'));
        }

        $user_id = intval($_POST['user_id'] ?? 0);
        if (!$user_id) {
            wp_send_json_error(__('Invalid user ID.', 'cosy-appointments'));
        }

        $user = get_userdata($user_id);
        if (!$user) {
            wp_send_json_error(__('User not found.', 'cosy-appointments'));
        }

        $roles = (array) $user->roles;
        $role = in_array('provider', $roles) ? 'provider' : (in_array('customer', $roles) ? 'customer' : 'other');

        ob_start();
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
            $dob     = get_user_meta($user_id, 'dob', true);
            $address = get_user_meta($user_id, 'prov_address', true);
            $gender  = get_user_meta($user_id, 'gender', true);
            $description = get_user_meta($user_id, 'description', true);
            $video_status = $this->get_provider_video_status($user_id);
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
            // Fetch potential meta keys for customer details (e.g. phone/address/dob if ever collected)
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
            global $wpdb;
            $meta_key_user = ($role === 'provider') ? 'cosy_provider_id' : 'cosy_customer_id';
            $transient_key = 'cosy_user_appts_' . $user_id . '_' . $role;
            $appointments = get_transient($transient_key);
            if (false === $appointments) {
                $appointments = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT p.ID, 
                               (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = p.ID AND meta_key = 'cosy_service_name' LIMIT 1) as service_name,
                               (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = p.ID AND meta_key = 'cosy_start_date' LIMIT 1) as start_date,
                               (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = p.ID AND meta_key = 'cosy_slots_timeline' LIMIT 1) as slots_timeline,
                               (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = p.ID AND meta_key = 'cosy_booking_status' LIMIT 1) as booking_status,
                               (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = p.ID AND meta_key = 'cosy_total_payable' LIMIT 1) as total_payable
                        FROM {$wpdb->posts} p
                        JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                        WHERE p.post_type = 'cosy_appointment'
                          AND p.post_status = 'publish'
                          AND pm.meta_key = %s
                          AND pm.meta_value = %d
                        ORDER BY p.ID DESC",
                        $meta_key_user,
                        $user_id
                    )
                );
                set_transient($transient_key, $appointments, 10 * MINUTE_IN_SECONDS);
            }

            if (!empty($appointments)) :
                // Calculate service booking ordinal numbers (chronological order)
                $chrono_appts = array_reverse($appointments);
                $service_counts = [];
                $appt_booking_numbers = [];
                foreach ($chrono_appts as $a) {
                    $srv_name = $a->service_name;
                    if (!isset($service_counts[$srv_name])) {
                        $service_counts[$srv_name] = 0;
                    }
                    $service_counts[$srv_name]++;
                    $appt_booking_numbers[$a->ID] = $service_counts[$srv_name];
                }
            ?>
                <div class="cosy-modal-appt-list" style="margin-top: 12px; display: flex; flex-direction: column; gap: 10px;">
                    <?php foreach ($appointments as $appt) :
                        $status = !empty($appt->booking_status) ? $appt->booking_status : 'pending';
                        if ($status === 'cancelled') {
                            $status_label = __('Cancelled', 'cosy-appointments');
                            $status_color = '#991b1b';
                            $status_bg = '#fee2e2';
                        } else {
                            $appt_time = strtotime($appt->start_date);
                            $today_time = strtotime('today');
                            if ($appt_time < $today_time) {
                                $status_label = __('Completed', 'cosy-appointments');
                                $status_color = '#166534';
                                $status_bg = '#dcfce7';
                            } elseif ($appt_time === $today_time) {
                                $status_label = __('In Progress', 'cosy-appointments');
                                $status_color = '#7c3aed';
                                $status_bg = '#f5f3ff';
                            } else {
                                $status_label = __('Upcoming', 'cosy-appointments');
                                $status_color = '#1e40af';
                                $status_bg = '#dbeafe';
                            }
                        }

                        // Determine booking ordinal suffix (1st, 2nd, 3rd, etc.)
                        $booking_num = $appt_booking_numbers[$appt->ID] ?? 1;
                        $ends = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');
                        if ((($booking_num % 100) >= 11) && (($booking_num % 100) <= 13)) {
                            $ordinal = $booking_num . 'th';
                        } else {
                            $ordinal = $booking_num . $ends[$booking_num % 10];
                        }
                        $label_suffix = ($role === 'provider') ? sprintf(__('%s Session', 'cosy-appointments'), $ordinal) : sprintf(__('%s Booking', 'cosy-appointments'), $ordinal);

                        $badge_class = ($role === 'provider') ? 'badge-provider-service' : 'badge-customer-service';
                    ?>
                        <div class="cosy-modal-appt-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; display: flex; align-items: center; justify-content: space-between; gap: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; flex-wrap: wrap;">
                                    <span class="badge <?php echo esc_attr($badge_class); ?>" style="margin: 0; font-size: 10px; font-weight: bold;">
                                        <?php echo esc_html($appt->service_name); ?> <span style="opacity: 0.8; font-weight: normal; font-size: 8px;">(<?php echo esc_html($label_suffix); ?>)</span>
                                    </span>
                                    <span style="font-size: 11px; font-weight: 700; color: #1e293b;">£<?php echo esc_html($appt->total_payable ?: '0'); ?></span>
                                </div>
                                <div style="display: flex; flex-wrap: wrap; gap: 12px; color: #64748b; font-size: 11px;">
                                    <span style="display: flex; align-items: center; gap: 4px;">
                                        <span class="dashicons dashicons-calendar-alt" style="font-size: 14px; width: 14px; height: 14px; color: #94a3b8; line-height: 14px;"></span>
                                        <span><?php echo esc_html($appt->start_date); ?></span>
                                    </span>
                                    <?php if (!empty($appt->slots_timeline)) : ?>
                                        <span style="display: flex; align-items: center; gap: 4px;">
                                            <span class="dashicons dashicons-clock" style="font-size: 14px; width: 14px; height: 14px; color: #94a3b8; line-height: 14px;"></span>
                                            <span><?php echo esc_html($appt->slots_timeline); ?></span>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div style="text-align: right; flex-shrink: 0; display: flex; flex-direction: column; align-items: flex-end; gap: 6px;">
                                <span style="display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 700; text-transform: uppercase; color: <?php echo $status_color; ?>; background-color: <?php echo $status_bg; ?>; border: 1px solid <?php echo $status_color; ?>33;">
                                    <?php echo esc_html($status_label); ?>
                                </span>
                                <a href="<?php echo esc_url(admin_url('post.php?post=' . $appt->ID . '&action=edit')); ?>" target="_blank" style="font-size: 10px; color: #a44390; text-decoration: none; display: flex; align-items: center; gap: 2px; font-weight: 600;" class="cosy-edit-appt-link">
                                    <span class="dashicons dashicons-edit" style="font-size: 12px; width: 12px; height: 12px; line-height: 12px;"></span>
                                    <?php esc_html_e('Manage', 'cosy-appointments'); ?>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php
            else :
                if ($role === 'provider') {
                    $services_table = $wpdb->prefix . 'provider_services';
                    $services = $wpdb->get_col(
                        $wpdb->prepare(
                            "SELECT DISTINCT p.post_title 
                             FROM $services_table ps
                             JOIN {$wpdb->posts} p ON ps.service_id = p.ID
                             WHERE ps.provider_id = %d AND ps.checkbox_status = 'yes'",
                            $user_id
                        )
                    );
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

<?php
        $html = ob_get_clean();

        $title = sprintf(
            __('%1$s Details - %2$s', 'cosy-appointments'),
            $role === 'provider' ? __('Provider', 'cosy-appointments') : __('Customer', 'cosy-appointments'),
            $user->display_name
        );

        wp_send_json_success([
            'title' => $title,
            'html'  => $html
        ]);
    }

    /**
     * AJAX handler: Bulk deletes selected users.
     */
    public function handle_ajax_delete_users(): void
    {
        check_ajax_referer('cosy_admin_delete_nonce', 'security');

        if (!current_user_can('manage_options') && !current_user_can('manage_cosy_appointments')) {
            wp_send_json_error(__('Permission denied.', 'cosy-appointments'));
        }

        $user_ids = isset($_POST['user_ids']) ? array_map('intval', $_POST['user_ids']) : [];
        if (empty($user_ids)) {
            wp_send_json_error(__('No users selected.', 'cosy-appointments'));
        }

        $current_user_id = get_current_user_id();
        $deleted_count = 0;
        $errors = [];
        $deleted_user_infos = [];

        // Required for wp_delete_user() function
        require_once ABSPATH . 'wp-admin/includes/user.php';

        foreach ($user_ids as $id) {
            if ($id === $current_user_id) {
                $errors[] = __('You cannot delete your own account.', 'cosy-appointments');
                continue;
            }

            $user = get_userdata($id);
            if (!$user) {
                continue;
            }

            // Verify role is manageable by our plugin
            $roles = (array) $user->roles;
            if (!in_array('provider', $roles) && !in_array('customer', $roles)) {
                $errors[] = sprintf(__('User ID %d does not have a manageable role.', 'cosy-appointments'), $id);
                continue;
            }

            $user_display = $user->display_name ?: $user->user_login;
            $user_role = implode(', ', $roles);

            $deleted = wp_delete_user($id);
            if ($deleted) {
                $deleted_count++;
                $deleted_user_infos[] = sprintf('%s (ID: %d, Role: %s)', $user_display, $id, $user_role);
            } else {
                $errors[] = sprintf(__('Failed to delete user ID %d.', 'cosy-appointments'), $id);
            }
        }

        if ($deleted_count > 0) {
            \Cosy\Appointments\Common\LogManager::log(
                'users',
                'user_deleted',
                sprintf(__('Admin bulk deleted %d user(s): %s.', 'cosy-appointments'), $deleted_count, implode('; ', $deleted_user_infos))
            );
            $msg = sprintf(_n('Successfully deleted %d user.', 'Successfully deleted %d users.', $deleted_count, 'cosy-appointments'), $deleted_count);
            if (!empty($errors)) {
                $msg .= ' ' . implode(' ', $errors);
            }
            wp_send_json_success($msg);
        } else {
            wp_send_json_error(implode(' ', $errors) ?: __('Failed to delete selected users.', 'cosy-appointments'));
        }
    }

    /**
     * Clears user appointments cache transients when a cosy_appointment post is updated/saved.
     */
    public function clear_user_appointments_cache(int $post_id, \WP_Post $post): void
    {
        $provider_id = get_post_meta($post_id, 'cosy_provider_id', true);
        $customer_id = get_post_meta($post_id, 'cosy_customer_id', true);
        if ($provider_id) {
            delete_transient('cosy_user_appts_' . $provider_id . '_provider');
        }
        if ($customer_id) {
            delete_transient('cosy_user_appts_' . $customer_id . '_customer');
        }
    }

    /**
     * Clears user appointments cache transients before deleting a cosy_appointment post.
     */
    public function clear_user_appointments_cache_before_delete(int $post_id): void
    {
        $post = get_post($post_id);
        if ($post && $post->post_type === 'cosy_appointment') {
            $this->clear_user_appointments_cache($post_id, $post);
        }
    }
}
