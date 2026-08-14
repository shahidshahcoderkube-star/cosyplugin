<?php

namespace Cosy\Appointments\Common;

if (!defined('ABSPATH')) {
    exit;
}

class Database
{
    /**
     * CHECKS AND RUNS DATABASE TABLE MIGRATIONS
     * 
     * USE CASE:
     * Triggered on plugin activation or admin load to verify and create custom plugin database tables.
     * 
     * HOW TO USE:
     * (new Database())->run_db_migrations();
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Calls individual table creation routines for services, bookings, worldpay payments, media, reviews, and logs.
     * 2. Updates 'cosy_db_version' WordPress option to current plugin version.
     */
    public function run_db_migrations(): void
    {
        $this->create_services_table();
        $this->create_bookings_table();
        $this->create_worldpay_payments_table();
        $this->create_media_table();
        $this->create_reviews_table();
        $this->create_review_replies_table();
        $this->create_review_tokens_table();
        $this->create_activity_logs_table();
        update_option('cosy_db_version', COSY_APPT_VER);
    }

    /**
     * CREATES PROVIDER SERVICES TABLE
     * 
     * USE CASE:
     * Creates custom DB table wp_provider_services to store provider-specific services and pricing.
     * 
     * HOW TO USE:
     * $database->create_services_table();
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Prepares table name prefix + 'provider_services'.
     * 2. Checks if table exists in MySQL database via SHOW TABLES query.
     * 3. Executes CREATE TABLE schema via dbDelta() if table is missing.
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
     * Create the review replies table for multi-level conversation threads.
     */
    public function create_review_replies_table(): void
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cosy_review_replies';
        $charset_collate = $wpdb->get_charset_collate();

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            review_id BIGINT(20) UNSIGNED NOT NULL,
            sender_id BIGINT(20) UNSIGNED NOT NULL,
            sender_role VARCHAR(20) NOT NULL DEFAULT 'provider',
            sender_name VARCHAR(255) NOT NULL,
            reply_text TEXT NOT NULL,
            reply_level TINYINT(2) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY review_id (review_id)
        ) $charset_collate;";

        dbDelta($sql);
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

    /**
     * Create the review tokens table for secure one-time review invitations.
     */
    public function create_review_tokens_table(): void
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cosy_review_tokens';
        $charset_collate = $wpdb->get_charset_collate();

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            token VARCHAR(64) NOT NULL,
            order_id BIGINT(20) UNSIGNED NOT NULL,
            provider_id BIGINT(20) UNSIGNED NOT NULL,
            customer_id BIGINT(20) UNSIGNED NOT NULL,
            customer_email VARCHAR(255) NOT NULL,
            used TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY token (token),
            KEY order_id (order_id)
        ) $charset_collate;";

        dbDelta($sql);
    }

    /**
     * Create the custom bookings table (wp_cosy_bookings).
     */
    public function create_bookings_table(): void
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cosy_bookings';
        $charset_collate = $wpdb->get_charset_collate();

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            order_id BIGINT(20) UNSIGNED NOT NULL,
            customer_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            customer_name VARCHAR(255) NOT NULL DEFAULT '',
            customer_email VARCHAR(255) NOT NULL DEFAULT '',
            provider_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            provider_name VARCHAR(255) NOT NULL DEFAULT '',
            service_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            service_name VARCHAR(255) NOT NULL DEFAULT '',
            booking_date VARCHAR(100) NOT NULL DEFAULT '',
            booking_time VARCHAR(255) NOT NULL DEFAULT '',
            total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            booking_status VARCHAR(50) NOT NULL DEFAULT 'pending',
            payment_status VARCHAR(50) NOT NULL DEFAULT 'Pending',
            gateway VARCHAR(50) NOT NULL DEFAULT 'worldpay',
            transaction_ref_id VARCHAR(255) NOT NULL DEFAULT '',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY order_id (order_id),
            KEY customer_id (customer_id),
            KEY provider_id (provider_id)
        ) $charset_collate;";

        dbDelta($sql);

        // Ensure column is renamed from transaction_ref to transaction_ref_id if old column exists
        $has_old_col = $wpdb->get_results("SHOW COLUMNS FROM `$table_name` LIKE 'transaction_ref'");
        $has_new_col = $wpdb->get_results("SHOW COLUMNS FROM `$table_name` LIKE 'transaction_ref_id'");
        if (!empty($has_old_col) && empty($has_new_col)) {
            $wpdb->query("ALTER TABLE `$table_name` CHANGE `transaction_ref` `transaction_ref_id` VARCHAR(255) NOT NULL DEFAULT ''");
        }
    }

    /**
     * Syncs an appointment order into the custom wp_cosy_bookings table.
     */
    public static function sync_booking_record(int $order_id): void
    {
        global $wpdb;
        if ($order_id <= 0) return;

        $post = get_post($order_id);
        if (!$post || $post->post_type !== 'cosy_appointment') return;

        $table_name = $wpdb->prefix . 'cosy_bookings';

        $customer_id        = $post->post_author;
        $customer_user      = get_userdata($customer_id);
        $customer_name      = $customer_user ? $customer_user->display_name : 'Guest';
        $customer_email     = $customer_user ? $customer_user->user_email : '';

        $provider_id        = intval(get_post_meta($order_id, 'cosy_provider_id', true));
        $provider_name      = sanitize_text_field(get_post_meta($order_id, 'cosy_provider_name', true));
        $service_id         = intval(get_post_meta($order_id, 'cosy_service_id', true));
        $service_name       = sanitize_text_field(get_post_meta($order_id, 'cosy_service', true));
        $booking_date       = sanitize_text_field(get_post_meta($order_id, 'cosy_start_date', true));
        $booking_time       = sanitize_text_field(get_post_meta($order_id, 'cosy_slots_timeline', true));
        $total_amount       = floatval(get_post_meta($order_id, 'cosy_total_payable', true));
        $booking_status     = sanitize_text_field(get_post_meta($order_id, 'cosy_booking_status', true)) ?: 'pending';
        $payment_status     = sanitize_text_field(get_post_meta($order_id, 'cosy_payment_status', true)) ?: 'Pending';
        $gateway            = sanitize_text_field(get_post_meta($order_id, 'cosy_payment_gateway', true)) ?: 'worldpay';
        
        $transaction_ref_id = sanitize_text_field(get_post_meta($order_id, 'cosy_transaction_ref', true));
        if (empty($transaction_ref_id)) {
            $transaction_ref_id = sanitize_text_field(get_post_meta($order_id, 'cosy_worldpay_last_event', true));
        }

        $existing_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE order_id = %d", $order_id));

        $data = [
            'order_id'           => $order_id,
            'customer_id'        => $customer_id,
            'customer_name'      => $customer_name,
            'customer_email'     => $customer_email,
            'provider_id'        => $provider_id,
            'provider_name'      => $provider_name,
            'service_id'         => $service_id,
            'service_name'       => $service_name,
            'booking_date'       => $booking_date,
            'booking_time'       => $booking_time,
            'total_amount'       => $total_amount,
            'booking_status'     => $booking_status,
            'payment_status'     => $payment_status,
            'gateway'            => $gateway,
            'transaction_ref_id' => $transaction_ref_id,
            'updated_at'         => current_time('mysql'),
        ];

        if ($existing_id) {
            $wpdb->update($table_name, $data, ['order_id' => $order_id]);
        } else {
            $data['created_at'] = current_time('mysql');
            $wpdb->insert($table_name, $data);
        }
    }

    /**
     * Create the dedicated WorldPay payments table (wp_cosy_worldpay_payments).
     */
    public function create_worldpay_payments_table(): void
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cosy_worldpay_payments';
        $charset_collate = $wpdb->get_charset_collate();

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            order_id BIGINT(20) UNSIGNED NOT NULL,
            customer_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            customer_name VARCHAR(255) NOT NULL DEFAULT '',
            customer_email VARCHAR(255) NOT NULL DEFAULT '',
            payment_id VARCHAR(255) NOT NULL DEFAULT '',
            transaction_ref_id VARCHAR(255) NOT NULL DEFAULT '',
            amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            currency VARCHAR(10) NOT NULL DEFAULT 'GBP',
            payment_status VARCHAR(50) NOT NULL DEFAULT 'Pending',
            last_event VARCHAR(100) NOT NULL DEFAULT '',
            auth_code VARCHAR(50) NOT NULL DEFAULT '',
            card_brand VARCHAR(50) NOT NULL DEFAULT '',
            card_last4 VARCHAR(10) NOT NULL DEFAULT '',
            card_funding_type VARCHAR(50) NOT NULL DEFAULT '',
            raw_response LONGTEXT DEFAULT NULL,
            ip_address VARCHAR(45) NOT NULL DEFAULT '',
            payment_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY order_id (order_id),
            KEY customer_id (customer_id),
            KEY transaction_ref_id (transaction_ref_id)
        ) $charset_collate;";

        dbDelta($sql);

        // Ensure columns exist on existing installs
        $wpdb->query("ALTER TABLE `$table_name` ADD COLUMN IF NOT EXISTS `payment_id` VARCHAR(255) NOT NULL DEFAULT '' AFTER `customer_email`");
        $wpdb->query("ALTER TABLE `$table_name` ADD COLUMN IF NOT EXISTS `last_event` VARCHAR(100) NOT NULL DEFAULT '' AFTER `payment_status`");
        $wpdb->query("ALTER TABLE `$table_name` ADD COLUMN IF NOT EXISTS `auth_code` VARCHAR(50) NOT NULL DEFAULT '' AFTER `last_event`");
        $wpdb->query("ALTER TABLE `$table_name` ADD COLUMN IF NOT EXISTS `card_brand` VARCHAR(50) NOT NULL DEFAULT '' AFTER `auth_code`");
        $wpdb->query("ALTER TABLE `$table_name` ADD COLUMN IF NOT EXISTS `card_funding_type` VARCHAR(50) NOT NULL DEFAULT '' AFTER `card_last4`");
    }

    /**
     * Records or updates a WorldPay payment entry into wp_cosy_worldpay_payments table.
     */
    public static function record_worldpay_payment_entry(int $order_id, string $status = '', array $extra = []): void
    {
        global $wpdb;
        if ($order_id <= 0) return;

        $post = get_post($order_id);
        if (!$post || $post->post_type !== 'cosy_appointment') return;

        $table_name = $wpdb->prefix . 'cosy_worldpay_payments';

        $customer_id        = $post->post_author;
        $customer_user      = get_userdata($customer_id);
        $customer_name      = $customer_user ? $customer_user->display_name : 'Guest';
        $customer_email     = $customer_user ? $customer_user->user_email : '';

        $total_amount       = floatval(get_post_meta($order_id, 'cosy_total_payable', true));
        $currency           = strtoupper(cosy_get_currency_code());
        
        $transaction_ref_id = sanitize_text_field(get_post_meta($order_id, 'cosy_transaction_ref', true));
        if (empty($transaction_ref_id)) {
            $transaction_ref_id = sanitize_text_field(get_post_meta($order_id, 'cosy_worldpay_last_event', true));
        }

        $payment_status     = !empty($status) ? $status : (sanitize_text_field(get_post_meta($order_id, 'cosy_payment_status', true)) ?: 'Pending');
        $ip_address         = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : '';
        
        // Deep parsing of WorldPay Access JSON response structure
        $raw_res = isset($extra['raw_response']) ? $extra['raw_response'] : [];
        if (is_string($raw_res)) {
            $raw_res = json_decode($raw_res, true) ?: [];
        }

        $payment_id   = isset($raw_res['paymentId']) ? sanitize_text_field($raw_res['paymentId']) : (isset($extra['payment_id']) ? sanitize_text_field($extra['payment_id']) : '');
        $auth_code    = isset($raw_res['issuer']['authorizationCode']) ? sanitize_text_field($raw_res['issuer']['authorizationCode']) : '';
        $last_event   = isset($raw_res['lastEvent']) ? sanitize_text_field($raw_res['lastEvent']) : (isset($raw_res['event']) ? sanitize_text_field($raw_res['event']) : '');
        
        $card_brand   = isset($raw_res['paymentInstrument']['card']['brand']) ? sanitize_text_field($raw_res['paymentInstrument']['card']['brand']) : (isset($extra['card_type']) ? sanitize_text_field($extra['card_type']) : 'visa');
        $card_last4   = isset($raw_res['paymentInstrument']['card']['number']['last4Digits']) ? sanitize_text_field($raw_res['paymentInstrument']['card']['number']['last4Digits']) : (isset($extra['card_last4']) ? sanitize_text_field($extra['card_last4']) : '');
        $funding_type = isset($raw_res['paymentInstrument']['card']['fundingType']) ? sanitize_text_field($raw_res['paymentInstrument']['card']['fundingType']) : '';

        if (isset($raw_res['transactionReference']) && !empty($raw_res['transactionReference'])) {
            $transaction_ref_id = sanitize_text_field($raw_res['transactionReference']);
        }

        $existing_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE order_id = %d", $order_id));

        $data = [
            'order_id'           => $order_id,
            'customer_id'        => $customer_id,
            'customer_name'      => $customer_name,
            'customer_email'     => $customer_email,
            'payment_id'         => $payment_id,
            'transaction_ref_id' => $transaction_ref_id,
            'amount'             => $total_amount,
            'currency'           => $currency,
            'payment_status'     => $payment_status,
            'last_event'         => $last_event,
            'auth_code'          => $auth_code,
            'card_brand'         => $card_brand,
            'card_last4'         => $card_last4,
            'card_funding_type'  => $funding_type,
            'raw_response'       => !empty($raw_res) ? json_encode($raw_res) : '',
            'ip_address'         => $ip_address,
            'payment_date'       => current_time('mysql'),
        ];

        if ($existing_id) {
            $wpdb->update($table_name, $data, ['order_id' => $order_id]);
        } else {
            $data['created_at'] = current_time('mysql');
            $wpdb->insert($table_name, $data);
        }
    }
   
}
