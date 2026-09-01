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
                <span class="dashicons dashicons-trash cosy-btn-icon-trash"></span>
                <span class="cosy-btn-text cosy-btn-text-vmiddle"><?php esc_html_e('Delete', 'cosy-appointments'); ?></span>
            </button>
        </div>

        <div class="cosy-control-right">
            <form method="get" class="cosy-search-form-modern">
                <input type="hidden" name="page" value="cosy-users">
                <?php if ($role_filter !== 'all'): ?>
                    <input type="hidden" name="role" value="<?php echo esc_attr($role_filter); ?>">
                <?php endif; ?>

                <!-- Service Filter Dropdown -->
                <div class="cosy-filter-wrapper">
                    <select name="service" onchange="this.form.submit()" class="cosy-filter-select-modern">
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
                    <span class="dashicons dashicons-search cosy-search-input-icon"></span>
                    <input type="search" id="user-search-input" name="s" value="<?php echo esc_attr($search_query); ?>" placeholder="<?php esc_attr_e('Search users...', 'cosy-appointments'); ?>" class="cosy-search-input-field">
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
                <td id="cb" class="manage-column column-cb check-column">
                    <input type="checkbox" id="cosy-select-all-users">
                </td>
                <th scope="col" class="manage-column cosy-col-name"><?php esc_html_e('Name', 'cosy-appointments'); ?></th>
                <th scope="col" class="manage-column cosy-col-role"><?php esc_html_e('Role', 'cosy-appointments'); ?></th>
                <th scope="col" class="manage-column cosy-col-email"><?php esc_html_e('Email', 'cosy-appointments'); ?></th>
                <th scope="col" class="manage-column cosy-col-experiences"><?php esc_html_e('Experiences', 'cosy-appointments'); ?></th>
                <th scope="col" class="manage-column cosy-col-verify"><?php esc_html_e('Email Verify', 'cosy-appointments'); ?></th>
                <th scope="col" class="manage-column cosy-col-status"><?php esc_html_e('Status', 'cosy-appointments'); ?></th>
                <th scope="col" class="manage-column cosy-col-actions"><?php esc_html_e('Actions', 'cosy-appointments'); ?></th>
            </tr>
        </thead>
        <tbody id="the-list">
            <?php if (empty($users)) : ?>
                <tr>
                    <td colspan="8" class="cosy-table-empty-cell">
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
                        <th scope="row" class="check-column">
                            <input type="checkbox" class="cosy-user-checkbox" value="<?php echo $user_id; ?>">
                        </th>
                        <td>
                            <strong><?php echo esc_html($user->display_name); ?></strong>
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
                                        echo '<div class="cosy-appt-more-text">' . sprintf(__('& %d more...', 'cosy-appointments'), count($appointments) - 2) . '</div>';
                                        break;
                                    }
                                    $badge_class = ($primary_role === 'provider') ? 'badge-provider-service' : 'badge-customer-service';
                                    $status_info = $controller->get_appointment_status_info($appt);
                                    $label_suffix = $controller->get_ordinal_label($appt_booking_numbers[$appt->ID] ?? 1, $primary_role);
                            ?>
                                    <div class="cosy-appt-info-block">
                                        <div class="cosy-appt-info-header">
                                            <span class="badge <?php echo esc_attr($badge_class); ?> cosy-appt-service-badge">
                                                <?php echo esc_html($appt->service_name); ?> <span class="cosy-appt-ordinal-suffix">(<?php echo esc_html($label_suffix); ?>)</span>
                                            </span>
                                            <span class="cosy-appt-status-text cosy-appt-status-<?php echo esc_attr($status_info['slug']); ?>">
                                                <?php echo esc_html($status_info['label']); ?>
                                            </span>
                                        </div>
                                        <div class="cosy-appt-meta-row">
                                            <span class="dashicons dashicons-calendar-alt cosy-appt-meta-icon"></span>
                                            <span><?php echo esc_html(cosy_format_date($appt->start_date)); ?></span>
                                        </div>
                                        <?php if (!empty($appt->slots_timeline)) : ?>
                                            <div class="cosy-appt-meta-row cosy-appt-meta-row-wrap">
                                                <span class="dashicons dashicons-clock cosy-appt-meta-icon"></span>
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
                                        echo '<div class="cosy-muted-empty-text">' . __('Offers:', 'cosy-appointments') . '</div>';
                                        foreach ($services as $srv) {
                                            echo '<span class="badge badge-provider-service">' . esc_html($srv) . '</span> ';
                                        }
                                    } else {
                                        echo '<span class="cosy-muted-empty-text">' . __('No Services/Bookings', 'cosy-appointments') . '</span>';
                                    }
                                } else {
                                    echo '<span class="cosy-muted-empty-text">' . __('No Bookings Yet', 'cosy-appointments') . '</span>';
                                }
                            endif;
                            ?>
                        </td>
                        <td>
                            <?php if ($email_status === 'pending') : ?>
                                <span class="badge badge-pending email-verify-badge-<?php echo $user_id; ?>">
                                    <span class="dashicons dashicons-clock cosy-badge-icon-verified"></span>
                                    <?php esc_html_e('Pending', 'cosy-appointments'); ?>
                                </span>
                            <?php else : ?>
                                <span class="badge badge-verified email-verify-badge-<?php echo $user_id; ?>">
                                    <span class="dashicons dashicons-yes cosy-badge-icon-verified"></span>
                                    <?php esc_html_e('Verified', 'cosy-appointments'); ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($primary_role === 'customer') : ?>
                                <span class="cosy-na-text"><?php esc_html_e('N/A', 'cosy-appointments'); ?></span>
                            <?php else : ?>
                                <select class="cosy-admin-status-dropdown" data-user-id="<?php echo $user_id; ?>" data-role="<?php echo $primary_role; ?>">
                                    <option value="active" <?php selected($account_status, 'active'); ?>><?php esc_html_e('Active', 'cosy-appointments'); ?></option>
                                    <option value="deactive" <?php selected($account_status, 'deactive'); ?>><?php esc_html_e('Deactive', 'cosy-appointments'); ?></option>
                                </select>
                                <span class="cosy-status-spinner spinner cosy-status-spinner-vmiddle"></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="cosy-actions-cell-wrapper">
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
        <div class="tablenav bottom cosy-tablenav-bottom">
            <div class="tablenav-pages">
                <span class="displaying-num cosy-displaying-num"><?php printf(esc_html(_n('%s user', '%s users', $total_users, 'cosy-appointments')), number_format_i18n($total_users)); ?></span>
                <span class="pagination-links cosy-pagination-links">
                    <?php if ($paged > 1): ?>
                        <a class="prev-page button" href="<?php echo esc_url(add_query_arg('paged', $paged - 1)); ?>">&lsaquo;</a>
                    <?php endif; ?>
                    <span class="paging-input cosy-paging-input">
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
            <div class="cosy-modal-loading-box">
                <span class="spinner is-active cosy-modal-spinner"></span>
                <p class="cosy-modal-loading-text"><?php esc_html_e('Loading details...', 'cosy-appointments'); ?></p>
            </div>
        </div>
        <div class="cosy-user-modal-footer">
            <button type="button" class="cosy-modal-btn-close cosy-user-modal-close-btn"><?php esc_html_e('Close Details', 'cosy-appointments'); ?></button>
        </div>
    </div>
</div>
