<?php
/**
 * Users Page Template
 * 
 * Renders the admin Manage Users page with table, filters, and modal.
 * Included by UsersAdmin::render_users_page()
 *
 * Available variables:
 * @var array       $users         Array of WP_User objects
 * @var int         $total_users   Total user count
 * @var int         $total_pages   Total pagination pages
 * @var int         $paged         Current page number
 * @var string      $role_filter   Current role filter (all/provider/customer)
 * @var string      $service_filter Current service filter
 * @var string      $search_query  Current search query
 * @var UsersAdmin  $controller    Reference to the UsersAdmin instance
 */

defined('ABSPATH') || exit;
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
                <th scope="col" class="manage-column" style="width: 320px;"><?php esc_html_e('Experiences', 'cosy-appointments'); ?></th>
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

                    // 3. Fetch appointments using shared helper
                    $appointments = $controller->get_user_appointments($user_id, $primary_role);
                    $appt_booking_numbers = $controller->calculate_booking_ordinals($appointments);
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
                            if (!empty($appointments)) :
                                $count_appt = 0;
                                foreach ($appointments as $appt) :
                                    $count_appt++;
                                    if ($count_appt > 2) {
                                        echo '<div style="font-size: 10px; color: #94a3b8; font-style: italic; margin-top: 4px;">' . sprintf(__('& %d more...', 'cosy-appointments'), count($appointments) - 2) . '</div>';
                                        break;
                                    }
                                    $badge_class = ($primary_role === 'provider') ? 'badge-provider-service' : 'badge-customer-service';
                                    $status_info = $controller->get_appointment_status_info($appt);
                                    $label_suffix = $controller->get_ordinal_label($appt_booking_numbers[$appt->ID] ?? 1, $primary_role);
                            ?>
                                    <div class="cosy-appt-info-block" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 8px; margin-bottom: 5px; font-size: 11px;">
                                        <div style="font-weight: 600; margin-bottom: 4px; display: flex; align-items: center; justify-content: space-between; gap: 4px;">
                                            <span class="badge <?php echo esc_attr($badge_class); ?>" style="margin: 0; padding: 2px 6px; font-size: 9px; font-weight: bold;">
                                                <?php echo esc_html($appt->service_name); ?> <span style="opacity: 0.8; font-weight: normal; font-size: 8px;">(<?php echo esc_html($label_suffix); ?>)</span>
                                            </span>
                                            <span style="font-size: 9px; font-weight: 700; text-transform: uppercase; color: <?php echo $status_info['color']; ?>;">
                                                <?php echo esc_html($status_info['label']); ?>
                                            </span>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 4px; color: #475569; font-size: 10px; margin-top: 2px;">
                                            <span class="dashicons dashicons-calendar-alt" style="font-size: 12px; width: 12px; height: 12px; color: #64748b; line-height: 12px;"></span>
                                            <span><?php echo esc_html(cosy_format_date($appt->start_date)); ?></span>
                                        </div>
                                        <?php if (!empty($appt->slots_timeline)) : ?>
                                            <div style="display: flex; align-items: center; gap: 4px; color: #475569; font-size: 10px; margin-top: 2px; word-break: break-all;">
                                                <span class="dashicons dashicons-clock" style="font-size: 12px; width: 12px; height: 12px; color: #64748b; line-height: 12px;"></span>
                                                <span><?php echo cosy_clean_slots_timeline($appt->slots_timeline, $appt->start_date); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                            <?php
                                endforeach;
                            else :
                                if ($primary_role === 'provider') {
                                    $services = $controller->get_provider_offered_services($user_id);
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
                                <button type="button" class="button button-small btn-view-cosy-user-details" data-user-id="<?php echo $user_id; ?>" style="padding: 6px 16px; font-size: 13px;">
                                    <?php esc_html_e('View Details', 'cosy-appointments'); ?>
                                </button>

                                <?php if ($email_status === 'pending') : ?>
                                    <button type="button" class="button button-small cosy-btn-resend-verification" data-user-id="<?php echo $user_id; ?>" data-role="<?php echo $primary_role; ?>" style="padding: 6px 16px; font-size: 13px;">
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
