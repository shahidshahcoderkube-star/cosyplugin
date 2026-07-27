<?php

namespace Cosy\Appointments\Common;

if (!defined('ABSPATH')) {
    exit;
}

class Database
{
    /**
     * Check if database needs an upgrade and run migrations.
     */
    public function run_db_migrations(): void
    {
        $this->create_services_table();
        $this->create_media_table();
        $this->create_reviews_table();
        $this->create_activity_logs_table();
        update_option('cosy_db_version', COSY_APPT_VER);
    }

    /**
     * Create the provider services table.
     */
    public function create_services_table(): void
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'provider_services';
        $charset_collate = $wpdb->get_charset_collate();

        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) != $table_name) {
            $sql = "CREATE TABLE $table_name (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                service_id BIGINT(20) UNSIGNED NOT NULL,
                service VARCHAR(255) NOT NULL,
                description TEXT,
                price DECIMAL(10,2),
                duration VARCHAR(50),
                provider_id BIGINT(20) UNSIGNED NOT NULL,
                provider VARCHAR(100),
                checkbox_status VARCHAR(10) NOT NULL DEFAULT 'no',
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY  (id)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        } else {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            $sql = "CREATE TABLE $table_name (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                service_id BIGINT(20) UNSIGNED NOT NULL,
                service VARCHAR(255) NOT NULL,
                description TEXT,
                price DECIMAL(10,2),
                duration VARCHAR(50),
                provider_id BIGINT(20) UNSIGNED NOT NULL,
                provider VARCHAR(100),
                checkbox_status VARCHAR(10) NOT NULL DEFAULT 'no',
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY  (id)
            ) $charset_collate;";
            dbDelta($sql);
        }

        $checkbox_check = $wpdb->get_results("SHOW COLUMNS FROM `$table_name` LIKE 'checkbox_status'");
        if (empty($checkbox_check)) {
            $wpdb->query("ALTER TABLE `$table_name` ADD `checkbox_status` VARCHAR(10) NOT NULL DEFAULT 'no'");
        }
        $created_at_check = $wpdb->get_results("SHOW COLUMNS FROM `$table_name` LIKE 'created_at'");
        if (empty($created_at_check)) {
            $wpdb->query("ALTER TABLE `$table_name` ADD `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");
        }
    }

    /**
     * Create the media approvals table.
     */
    public function create_media_table(): void
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cosy_media_approvals';
        $charset_collate = $wpdb->get_charset_collate();

        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) != $table_name) {
            $sql = "CREATE TABLE $table_name (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id BIGINT(20) UNSIGNED NOT NULL,
                media_url TEXT NOT NULL,
                status VARCHAR(20) DEFAULT 'pending',
                uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                reviewed_at DATETIME DEFAULT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }

    /**
     * Create the provider reviews table.
     */
    public function create_reviews_table(): void
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cosy_provider_reviews';
        $charset_collate = $wpdb->get_charset_collate();

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            provider_id BIGINT(20) UNSIGNED NOT NULL,
            customer_id BIGINT(20) UNSIGNED NOT NULL,
            customer_name VARCHAR(255) NOT NULL,
            rating TINYINT(1) UNSIGNED NOT NULL,
            review TEXT NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            provider_reply TEXT DEFAULT NULL,
            reply_date DATETIME DEFAULT NULL,
            is_audit_logged TINYINT(1) DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        dbDelta($sql);

        // Ensure columns exist on existing installs
        $reply_check = $wpdb->get_results("SHOW COLUMNS FROM `$table_name` LIKE 'provider_reply'");
        if (empty($reply_check)) {
            $wpdb->query("ALTER TABLE `$table_name` ADD `provider_reply` TEXT DEFAULT NULL");
        }
        $reply_date_check = $wpdb->get_results("SHOW COLUMNS FROM `$table_name` LIKE 'reply_date'");
        if (empty($reply_date_check)) {
            $wpdb->query("ALTER TABLE `$table_name` ADD `reply_date` DATETIME DEFAULT NULL");
        }
        $audit_check = $wpdb->get_results("SHOW COLUMNS FROM `$table_name` LIKE 'is_audit_logged'");
        if (empty($audit_check)) {
            $wpdb->query("ALTER TABLE `$table_name` ADD `is_audit_logged` TINYINT(1) DEFAULT 0");
        }
    }

    /**
     * Create the activity logs table.
     */
    public function create_activity_logs_table(): void
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cosy_activity_logs';
        $charset_collate = $wpdb->get_charset_collate();

        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) != $table_name) {
            $sql = "CREATE TABLE $table_name (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                timestamp DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
                user_id BIGINT(20) DEFAULT 0 NOT NULL,
                user_name VARCHAR(100) DEFAULT 'Guest' NOT NULL,
                role VARCHAR(50) DEFAULT 'guest' NOT NULL,
                page VARCHAR(50) NOT NULL,
                action VARCHAR(50) NOT NULL,
                description TEXT NOT NULL,
                ip_address VARCHAR(45) DEFAULT '' NOT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }
}
