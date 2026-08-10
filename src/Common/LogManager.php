<?php

namespace Cosy\Appointments\Common;

if (!defined('ABSPATH')) {
    exit;
}

class LogManager
{
    /**
     * Get the option key for a page's logging state.
     */
    public static function get_toggle_key(string $page): string
    {
        return 'cosy_log_enabled_' . sanitize_key($page);
    }

    /**
     * Check if logging is enabled for a specific page.
     */
    public static function is_logging_enabled(string $page): bool
    {
        return get_option(self::get_toggle_key($page), '1') === '1';
    }

    /**
     * Log an activity.
     */
    public static function log(string $page, string $action, string $description, ?int $user_id = null): bool
    {
        // 1. Check if logging is active for this page
        if (!self::is_logging_enabled($page)) {
            return false;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'cosy_activity_logs';

        // Check if table exists (fail-safe)
        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) !== $table_name) {
            return false;
        }

        // Determine user ID
        $current_user_id = $user_id !== null ? $user_id : get_current_user_id();
        $user_name = __('Guest', 'cosy-appointments');
        $role = 'guest';

        if ($current_user_id > 0) {
            $user = get_userdata($current_user_id);
            if ($user) {
                $user_name = $user->display_name ?: $user->user_login;
                $roles = (array) $user->roles;
                if (in_array('administrator', $roles)) {
                    $role = 'admin';
                } elseif (in_array('provider', $roles)) {
                    $role = 'provider';
                } elseif (in_array('customer', $roles)) {
                    $role = 'customer';
                } else {
                    $role = 'user';
                }
            }
        }

        // Insert into DB
        $inserted = $wpdb->insert(
            $table_name,
            [
                'timestamp'   => current_time('mysql'),
                'user_id'     => $current_user_id,
                'user_name'   => sanitize_text_field($user_name),
                'role'        => sanitize_text_field($role),
                'page'        => sanitize_text_field($page),
                'action'      => sanitize_text_field($action),
                'description' => sanitize_textarea_field($description),
                'ip_address'  => sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? ''),
            ],
            [
                '%s', // timestamp
                '%d', // user_id
                '%s', // user_name
                '%s', // role
                '%s', // page
                '%s', // action
                '%s', // description
                '%s', // ip_address
            ]
        );

        return $inserted !== false;
    }

    /**
     * Log a PHP Exception with full file, line, and trace details.
     */
    public static function log_exception(string $context, \Throwable $exception, ?int $user_id = null): bool
    {
        $file = basename($exception->getFile());
        $line = $exception->getLine();
        $msg  = $exception->getMessage();
        $desc = sprintf("EXCEPTION in %s:L%d | %s", $file, $line, $msg);

        return self::log($context, 'exception_error', $desc, $user_id);
    }

    /**
     * Log a Database Error with last_error SQL details.
     */
    public static function log_db_error(string $context, string $action_title, string $sql_error, ?int $user_id = null): bool
    {
        $desc = sprintf("DATABASE ERROR during '%s': %s", $action_title, !empty($sql_error) ? $sql_error : 'Failed DB constraint/query');

        return self::log($context, 'database_error', $desc, $user_id);
    }

    /**
     * Render the logging toggle switch HTML.
     */
    public static function render_toggle_switch(string $page_name): string
    {
        $enabled = self::is_logging_enabled($page_name);
        $nonce = wp_create_nonce('cosy_log_toggle_nonce');
        
        ob_start();
        ?>
        <div class="cosy-page-logger-toggle-container d-inline-flex align-items-center gap-2" style="vertical-align: middle;">
            <span class="fw-bold" style="font-size: 13px; font-family: 'Plus Jakarta Sans', sans-serif;">
                <?php esc_html_e('Log Actions:', 'cosy-appointments'); ?>
            </span>
            <label class="cosy-switch">
                <input type="checkbox" class="cosy-page-log-toggle" data-page="<?php echo esc_attr($page_name); ?>" data-nonce="<?php echo esc_attr($nonce); ?>" value="1" <?php checked($enabled, true); ?>>
                <span class="cosy-slider round"></span>
            </label>
            <span class="cosy-log-status-lbl fw-bold text-uppercase" style="font-size: 11px; font-family: 'Plus Jakarta Sans', sans-serif; color: <?php echo $enabled ? '#10b981' : '#64748b'; ?>;">
                <?php echo $enabled ? __('Active', 'cosy-appointments') : __('Paused', 'cosy-appointments'); ?>
            </span>
            <span class="spinner cosy-log-toggle-spinner" style="float: none; margin: 0; display: none; vertical-align: middle;"></span>
        </div>
        <?php
        return ob_get_clean();
    }
}
