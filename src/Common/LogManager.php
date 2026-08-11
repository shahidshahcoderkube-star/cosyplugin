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

        // If WP_DEBUG is enabled, also write entry to wp-content/debug.log
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf('[CosyLog] [%s] [%s]: %s', $page, $action, $description));
        }

        return $inserted !== false;
    }

    /**
     * Log a PHP Exception with full file basename, line number, and error message.
     * 
     * USE CASE:
     * Call inside any catch (\Throwable $e) block throughout the plugin.
     * 
     * WHAT IT DOES:
     * Extracts the target script filename, exact line number, and error message from the exception,
     * formats a clean trace string, and writes a log row to the wp_cosy_activity_logs database table.
     * 
     * @param string     $context   Category / feature area name (e.g. 'stripe_checkout', 'reviews', 'orders').
     * @param \Throwable $exception The caught PHP Exception or Throwable object.
     * @param int|null   $user_id   Optional User ID associated with the action (defaults to current user).
     * @return bool                 True if successfully inserted into log table, false otherwise.
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
     * Log a Database Query Error with $wpdb->last_error details.
     * 
     * USE CASE:
     * Call whenever $wpdb->insert(), $wpdb->update(), or $wpdb->query() returns false or fails.
     * 
     * WHAT IT DOES:
     * Captures the MySQL error string from $wpdb->last_error, pairs it with the action title,
     * and logs it to the wp_cosy_activity_logs database table for instant admin dashboard visibility.
     * 
     * @param string   $context      Category / feature area name (e.g. 'database', 'media', 'reviews').
     * @param string   $action_title Brief description of the SQL action that failed (e.g. 'Approve Video', 'Insert Order').
     * @param string   $sql_error    The raw MySQL error string ($wpdb->last_error).
     * @param int|null $user_id      Optional User ID associated with the action (defaults to current user).
     * @return bool                  True if successfully inserted into log table, false otherwise.
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
