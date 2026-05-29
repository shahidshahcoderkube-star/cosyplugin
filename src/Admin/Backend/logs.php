<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'cosy_activity_logs';

// Create table if it doesn't exist (fail-safe)
if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) !== $table_name) {
    if (function_exists('cosy_create_activity_logs_table')) {
        cosy_create_activity_logs_table();
    }
}

// Fetch KPI counts
$total_logs = intval($wpdb->get_var("SELECT COUNT(*) FROM $table_name"));
$today_logs = intval($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE timestamp >= %s", current_time('Y-m-d 00:00:00'))));
$provider_logs = intval($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE role = %s", 'provider')));
$customer_logs = intval($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE role = %s", 'customer')));

// Pagination setup
$limit = 20;
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($current_page - 1) * $limit;

// Filters
$where_clauses = [];
$params = [];

$filter_role = isset($_GET['filter_role']) ? sanitize_text_field($_GET['filter_role']) : '';
if (!empty($filter_role)) {
    $where_clauses[] = 'role = %s';
    $params[] = $filter_role;
}

$filter_page = isset($_GET['filter_page']) ? sanitize_text_field($_GET['filter_page']) : '';
if (!empty($filter_page)) {
    $where_clauses[] = 'page = %s';
    $params[] = $filter_page;
}

$search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
if (!empty($search)) {
    $where_clauses[] = '(user_name LIKE %s OR description LIKE %s OR action LIKE %s)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

$where_sql = '';
if (!empty($where_clauses)) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}

// Execute paginated queries
if (!empty($params)) {
    $total_filtered = intval($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name $where_sql", $params)));
    $query = $wpdb->prepare("SELECT * FROM $table_name $where_sql ORDER BY id DESC LIMIT %d OFFSET %d", array_merge($params, [$limit, $offset]));
} else {
    $total_filtered = $total_logs;
    $query = $wpdb->prepare("SELECT * FROM $table_name ORDER BY id DESC LIMIT %d OFFSET %d", [$limit, $offset]);
}

$logs = $wpdb->get_results($query);
$total_pages = ceil($total_filtered / $limit);

$logging_enabled = \Cosy\Appointments\Common\LogManager::is_logging_enabled('logs');
$toggle_nonce = wp_create_nonce('cosy_log_toggle_nonce');
$clear_nonce = wp_create_nonce('cosy_clear_logs_nonce');

?>

<div class="wrap cosy-orders cosy-users-admin py-4 pe-4">
    <!-- Header Controls -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center">
            <div class="bg-primary bg-gradient text-white rounded-3 p-3 me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px; background: linear-gradient(135deg, #a44390 0%, #8f357b 100%) !important;">
                <i class="fa-solid fa-list-check fs-4"></i>
            </div>
            <div>
                <h1 class="wp-heading-inline m-0 fs-2 fw-bold text-dark" style="font-family: 'Outfit', sans-serif;"><?php _e('Activity Logs', 'cosy-appointments'); ?></h1>
                <p class="text-muted m-0 mt-1" style="font-family: 'Plus Jakarta Sans', sans-serif;"><?php _e('Monitor system events, provider edits, and booking actions in real-time.', 'cosy-appointments'); ?></p>
            </div>
        </div>
        
        <div class="d-flex align-items-center gap-3">
            <!-- Clear Action Button -->
            <?php if ($total_logs > 0) : ?>
                <button type="button" id="cosy-btn-clear-logs" data-nonce="<?php echo esc_attr($clear_nonce); ?>" class="cosy-btn-clear">
                    <i class="fa-solid fa-trash-can"></i>
                    <?php _e('Clear All Logs', 'cosy-appointments'); ?>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Logging Config Panel -->
    <div class="bg-white rounded-4 border border-secondary-subtle shadow-sm p-4 mb-4">
        <h3 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2" style="font-family: 'Outfit', sans-serif; font-size: 16px;">
            <i class="fa-solid fa-sliders text-primary" style="color: #a44390 !important; font-size: 18px;"></i>
            <?php _e('Log Settings by Section', 'cosy-appointments'); ?>
        </h3>
        <p class="text-muted mb-4" style="font-size: 13px; font-family: 'Plus Jakarta Sans', sans-serif;">
            <?php _e('Enable or disable activity tracking for specific sections of the plugin. Active sections will record actions to the log table below.', 'cosy-appointments'); ?>
        </p>
        
        <div class="row g-3">
            <?php
            $sections = [
                'dashboard'     => __('Dashboard', 'cosy-appointments'),
                'services'      => __('Services CPT', 'cosy-appointments'),
                'orders'        => __('Orders', 'cosy-appointments'),
                'media_approve' => __('Media Approve', 'cosy-appointments'),
                'users'         => __('Users', 'cosy-appointments'),
                'settings'      => __('Settings', 'cosy-appointments'),
                'documentation' => __('Documentation', 'cosy-appointments'),
                'logs'          => __('Logs Screen', 'cosy-appointments'),
            ];
            
            foreach ($sections as $sec_key => $sec_label) :
                $is_enabled = \Cosy\Appointments\Common\LogManager::is_logging_enabled($sec_key);
            ?>
                <div class="col-md-3 col-sm-6">
                    <div class="cosy-page-logger-toggle-container d-flex align-items-center justify-content-between p-3 bg-light rounded-3 border border-secondary-subtle">
                        <span class="fw-semibold text-secondary" style="font-size: 13px; font-family: 'Plus Jakarta Sans', sans-serif;"><?php echo esc_html($sec_label); ?></span>
                        <div class="d-flex align-items-center gap-2">
                            <label class="cosy-switch">
                                <input type="checkbox" class="cosy-page-log-toggle" data-page="<?php echo esc_attr($sec_key); ?>" data-nonce="<?php echo esc_attr($toggle_nonce); ?>" value="1" <?php checked($is_enabled, true); ?>>
                                <span class="cosy-slider round"></span>
                            </label>
                            <span class="cosy-log-status-lbl fw-bold text-uppercase" style="font-size: 10px; font-family: 'Plus Jakarta Sans', sans-serif; color: <?php echo $is_enabled ? '#10b981' : '#64748b'; ?>;">
                                <?php echo $is_enabled ? __('Active', 'cosy-appointments') : __('Paused', 'cosy-appointments'); ?>
                            </span>
                            <span class="spinner cosy-log-toggle-spinner" style="float: none; margin: 0; display: none; vertical-align: middle;"></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Stats Cards (KPI Grid) -->
    <div class="row g-4 mb-4">
        <!-- Card 1: Total Logs -->
        <div class="col-md-3 col-sm-6">
            <div class="cosy-kpi-card">
                <div class="cosy-kpi-icon text-primary bg-primary-subtle" style="color: #a44390 !important; background-color: rgba(164, 67, 144, 0.1) !important;">
                    <i class="fa-solid fa-database"></i>
                </div>
                <div class="cosy-kpi-info">
                    <h4><?php _e('Total Logs', 'cosy-appointments'); ?></h4>
                    <div class="kpi-number"><?php echo number_format($total_logs); ?></div>
                </div>
            </div>
        </div>

        <!-- Card 2: Today's Logs -->
        <div class="col-md-3 col-sm-6">
            <div class="cosy-kpi-card">
                <div class="cosy-kpi-icon text-success bg-success-subtle" style="color: #10b981 !important; background-color: rgba(16, 185, 129, 0.1) !important;">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div class="cosy-kpi-info">
                    <h4><?php _e('Today\'s Actions', 'cosy-appointments'); ?></h4>
                    <div class="kpi-number"><?php echo number_format($today_logs); ?></div>
                </div>
            </div>
        </div>

        <!-- Card 3: Provider Logs -->
        <div class="col-md-3 col-sm-6">
            <div class="cosy-kpi-card">
                <div class="cosy-kpi-icon text-info bg-info-subtle" style="color: #06b6d4 !important; background-color: rgba(6, 182, 212, 0.1) !important;">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
                <div class="cosy-kpi-info">
                    <h4><?php _e('Provider Logs', 'cosy-appointments'); ?></h4>
                    <div class="kpi-number"><?php echo number_format($provider_logs); ?></div>
                </div>
            </div>
        </div>

        <!-- Card 4: Customer Logs -->
        <div class="col-md-3 col-sm-6">
            <div class="cosy-kpi-card">
                <div class="cosy-kpi-icon text-warning bg-warning-subtle" style="color: #f59e0b !important; background-color: rgba(245, 158, 11, 0.1) !important;">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div class="cosy-kpi-info">
                    <h4><?php _e('Customer Logs', 'cosy-appointments'); ?></h4>
                    <div class="kpi-number"><?php echo number_format($customer_logs); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Premium Filters Bar -->
    <div class="cosy-control-bar" style="margin-top: 0; margin-bottom: 24px;">
        <div class="cosy-control-left">
            <form method="get" class="cosy-filter-form-modern" style="margin: 0; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <input type="hidden" name="page" value="cosy-logs">
                
                <!-- Filter by Role -->
                <div class="cosy-select-wrapper">
                    <span class="dashicons dashicons-admin-users" style="color: #94a3b8; margin-left: 10px; margin-right: 2px;"></span>
                    <select name="filter_role" id="filter-role">
                        <option value=""><?php esc_html_e('All Roles', 'cosy-appointments'); ?></option>
                        <option value="admin" <?php selected($filter_role, 'admin'); ?>><?php esc_html_e('Admin', 'cosy-appointments'); ?></option>
                        <option value="provider" <?php selected($filter_role, 'provider'); ?>><?php esc_html_e('Provider', 'cosy-appointments'); ?></option>
                        <option value="customer" <?php selected($filter_role, 'customer'); ?>><?php esc_html_e('Customer', 'cosy-appointments'); ?></option>
                        <option value="guest" <?php selected($filter_role, 'guest'); ?>><?php esc_html_e('Guest', 'cosy-appointments'); ?></option>
                    </select>
                </div>

                <!-- Filter by Page -->
                <div class="cosy-select-wrapper">
                    <span class="dashicons dashicons-media-text" style="color: #94a3b8; margin-left: 10px; margin-right: 2px;"></span>
                    <select name="filter_page" id="filter-page">
                        <option value=""><?php esc_html_e('All Sections', 'cosy-appointments'); ?></option>
                        <option value="dashboard" <?php selected($filter_page, 'dashboard'); ?>><?php esc_html_e('Dashboard', 'cosy-appointments'); ?></option>
                        <option value="services" <?php selected($filter_page, 'services'); ?>><?php esc_html_e('Services', 'cosy-appointments'); ?></option>
                        <option value="orders" <?php selected($filter_page, 'orders'); ?>><?php esc_html_e('Orders', 'cosy-appointments'); ?></option>
                        <option value="media_approve" <?php selected($filter_page, 'media_approve'); ?>><?php esc_html_e('Media Approve', 'cosy-appointments'); ?></option>
                        <option value="users" <?php selected($filter_page, 'users'); ?>><?php esc_html_e('Users', 'cosy-appointments'); ?></option>
                        <option value="settings" <?php selected($filter_page, 'settings'); ?>><?php esc_html_e('Settings', 'cosy-appointments'); ?></option>
                        <option value="frontend" <?php selected($filter_page, 'frontend'); ?>><?php esc_html_e('Frontend', 'cosy-appointments'); ?></option>
                        <option value="logs" <?php selected($filter_page, 'logs'); ?>><?php esc_html_e('Logs', 'cosy-appointments'); ?></option>
                    </select>
                </div>

                <!-- Text Search -->
                <div class="cosy-select-wrapper" style="padding-right: 8px;">
                    <span class="dashicons dashicons-search" style="color: #94a3b8; margin-left: 10px; margin-right: 6px;"></span>
                    <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php esc_attr_e('Search logs...', 'cosy-appointments'); ?>" style="border: none; outline: none; font-size: 13px; font-weight: 600; color: #475569; width: 200px; height: 100%;">
                </div>

                <button type="submit" class="cosy-filter-btn" style="height: 34px;">
                    <?php esc_html_e('Filter Logs', 'cosy-appointments'); ?>
                </button>
                
                <?php if (!empty($filter_role) || !empty($filter_page) || !empty($search)) : ?>
                    <a href="admin.php?page=cosy-logs" class="btn btn-link text-decoration-none fw-semibold text-secondary p-0 ms-2" style="font-size: 13px;"><?php esc_html_e('Reset Filters', 'cosy-appointments'); ?></a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-4 border border-secondary-subtle shadow-sm overflow-hidden mb-4">
        <table class="wp-list-table widefat fixed striped table-view-list cosy-orders-table m-0 border-0 shadow-none">
            <thead>
                <tr>
                    <th scope="col" style="width: 70px; text-align: center;"><?php _e('No.', 'cosy-appointments'); ?></th>
                    <th scope="col" style="width: 170px;"><?php _e('Date & Time', 'cosy-appointments'); ?></th>
                    <th scope="col" style="width: 180px;"><?php _e('User', 'cosy-appointments'); ?></th>
                    <th scope="col" style="width: 140px;"><?php _e('Section', 'cosy-appointments'); ?></th>
                    <th scope="col" style="width: 150px;"><?php _e('Action', 'cosy-appointments'); ?></th>
                    <th scope="col"><?php _e('Activity Description', 'cosy-appointments'); ?></th>
                    <th scope="col" style="width: 140px;"><?php _e('IP Address', 'cosy-appointments'); ?></th>
                </tr>
            </thead>
            <tbody id="the-list">
                <?php if (empty($logs)) : ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                            <i class="fa-solid fa-folder-open fs-2 mb-3 d-block text-secondary opacity-50"></i>
                            <?php _e('No activity logs found matching the filter criteria.', 'cosy-appointments'); ?>
                        </td>
                    </tr>
                <?php else : ?>
                    <?php 
                    $counter = $offset + 1;
                    foreach ($logs as $log) : 
                        $badge_class = 'cosy-log-badge-' . esc_attr($log->role);
                        $page_clean = str_replace('_', ' ', $log->page);
                    ?>
                        <tr>
                            <td style="text-align: center; color: #94a3b8; font-weight: bold;"><?php echo $counter++; ?></td>
                            <td class="text-secondary" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 12.5px;">
                                <?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log->timestamp)); ?>
                            </td>
                            <td>
                                <strong class="text-dark" style="font-family: 'Plus Jakarta Sans', sans-serif;"><?php echo esc_html($log->user_name); ?></strong>
                                <div class="mt-1">
                                    <span class="cosy-log-badge <?php echo $badge_class; ?>">
                                        <?php echo esc_html($log->role); ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span class="cosy-log-page-badge">
                                    <?php echo esc_html($page_clean); ?>
                                </span>
                            </td>
                            <td>
                                <code style="font-size: 11px; font-weight: bold; color: #a44390; background-color: rgba(164, 67, 144, 0.05); padding: 2px 6px; border-radius: 4px;">
                                    <?php echo esc_html($log->action); ?>
                                </code>
                            </td>
                            <td class="text-dark" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 13px; line-height: 1.4;">
                                <?php echo esc_html($log->description); ?>
                            </td>
                            <td style="font-family: monospace; font-size: 12px; color: #64748b;">
                                <?php echo esc_html($log->ip_address ?: 'N/A'); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1) : ?>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <span class="text-muted" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 13px;">
                Showing <?php echo count($logs); ?> of <?php echo $total_filtered; ?> entries
            </span>
            <div class="tablenav bottom m-0 border-0 p-0 shadow-none">
                <div class="tablenav-pages">
                    <span class="pagination-links">
                        <?php if ($current_page > 1) : ?>
                            <a class="prev-page button" href="<?php echo esc_url(add_query_arg('paged', $current_page - 1)); ?>">&lsaquo;</a>
                        <?php endif; ?>
                        
                        <span class="paging-input">
                            <span class="tablenav-paging-text">
                                <?php echo $current_page; ?> of <span class="total-pages"><?php echo $total_pages; ?></span>
                            </span>
                        </span>
                        
                        <?php if ($current_page < $total_pages) : ?>
                            <a class="next-page button" href="<?php echo esc_url(add_query_arg('paged', $current_page + 1)); ?>">&rsaquo;</a>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
