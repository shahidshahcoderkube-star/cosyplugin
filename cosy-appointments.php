<?php

/**
 * Plugin Name: Cosy Appointments
 * Description: A complete multi-provider appointment booking and scheduling solution for WordPress. Manage providers, services, availability, and Stripe payments — all in one place.
 * Version: 1.0.5
 * Author: Shahid Shah — Coderkube Technology
 * Author URI: https://coderkube.com
 * Text Domain: cosy-appointments
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Main Plugin Constants
 * These constants define the paths and version of the plugin for easy access.
 */
define('COSY_APPT_PATH', plugin_dir_path(__FILE__));   // Absolute path to the plugin folder
define('COSY_APPT_URL', plugin_dir_url(__FILE__));     // URL to the plugin folder
define('COSY_APPT_VER', '1.0.5');  // Current version

/**
 * register_role
 * 
 * Creates custom user roles for the plugin:
 * 1. Customer: Can book appointments.
 * 2. Provider: Can offer services, set availability, and manage their dashboard.
 * Also adds 'manage_cosy_appointments' capability to the Admin.
 */
function register_role()
{
    // CUSTOMER role
    if (!get_role('customer')) {
        add_role(
            'customer',          // Role slug
            'Customer',          // Display name
            [
                'read' => true,
            ]
        );
    }

    // PROVIDER role
    if (!get_role('provider')) {
        add_role(
            'provider',          // Role slug
            'Provider',          // Display name
            [
                'read' => true,
                'edit_posts' => true,        // Can edit their own posts
                'edit_published_posts' => true,
                'delete_posts' => true,      // Can delete their own posts
                'manage_cosy_appointments' => true,
            ]
        );
    }

    // Give Administrator full control over the plugin
    $admin = get_role('administrator');
    if ($admin) {
        $admin->add_cap('manage_cosy_appointments');
        $admin->add_cap('approve_cosy_media');
    }
}



/**
 * cosy_create_pages_on_activation
 * 
 * Automatically creates the required pages (like Login, Dashboard, Profile) 
 * when the plugin is activated. This ensures the user doesn't have to 
 * create them manually.
 */
function cosy_create_pages_on_activation()
{
    // List of pages to be created with their respective shortcodes
    $pages = [
        ['title' => 'Appointments',             'slug' => 'appointments',        'content' => '[cosy_appointments]'],
        ['title' => 'Orders',                   'slug' => 'orders',              'content' => '[cosy_orders]'],
        ['title' => 'Customer Registration',    'slug' => 'user-registration',   'content' => '[cosy_customer_registration]'],
        ['title' => 'Provider Registration',    'slug' => 'provider-registration', 'content' => '[cosy_provider_registration]'],
        ['title' => 'Login',                    'slug' => 'login',               'content' => '[cosy_login_form]'],
        ['title' => 'Customer Profile',         'slug' => 'customer-profile',    'content' => '[customer_profile]'],
        ['title' => 'My Orders',                'slug' => 'customer-order',      'content' => '[cosy_customer_order]'],
        ['title' => 'Provider Dashboard',       'slug' => 'provider-dashboard',  'content' => '[cosy_provider_dashboard]'],
        ['title' => 'Provider Verification',    'slug' => 'provider-verify',     'content' => '[cosy_verify_provider]'],
        ['title' => 'Service Provider Listing', 'slug' => 'service-provider',    'content' => '[cosy_service_provider_list]'],
        ['title' => 'Provider Profile',         'slug' => 'provider-profile',    'content' => '[cosy_profile_dashboard]'],
        ['title' => 'Checkout',                 'slug' => 'cosy-checkout',       'content' => '[cosy_checkout]'],
    ];

    foreach ($pages as $page) {
        $existing = get_page_by_path($page['slug']);

        if (!$existing) {
            // Page does not exist — create it fresh
            wp_insert_post([
                'post_title'   => $page['title'],
                'post_name'    => $page['slug'],
                'post_content' => $page['content'],
                'post_status'  => 'publish',
                'post_type'    => 'page',
            ]);
        } else {
            // Page already exists — update title if it has changed
            wp_update_post([
                'ID'         => $existing->ID,
                'post_title' => $page['title'],
            ]);
        }
    }
}

// Custom robust PSR-4 autoloader with dual-mode fail-safe for Windows-to-Linux ZIP extraction bugs
spl_autoload_register(function ($class) {
    $prefix = 'Cosy\\Appointments\\';
    $base_dir = COSY_APPT_PATH . 'src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);

    // 1. Standard cross-platform path (using forward slashes)
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require_once $file;
        return;
    }

    // 2. Fail-safe backup for Windows ZIP extraction bug on Linux hosts
    // If files are extracted flat in the root directory with literal backslashes in their names
    $flat_file = COSY_APPT_PATH . 'src\\' . $relative_class . '.php';
    if (file_exists($flat_file)) {
        require_once $flat_file;
        return;
    }
});

// Manually load the Helpers file with fail-safe check
if (file_exists(COSY_APPT_PATH . 'src/Helpers.php')) {
    require_once COSY_APPT_PATH . 'src/Helpers.php';
} elseif (file_exists(COSY_APPT_PATH . 'src\\Helpers.php')) {
    require_once COSY_APPT_PATH . 'src\\Helpers.php';
}




//-------DB Migration: Run on every version update--------//
add_action('plugins_loaded', 'cosy_run_db_migrations');
function cosy_run_db_migrations()
{
    $installed_ver = get_option('cosy_db_version', '0.0.0');
    if (version_compare($installed_ver, COSY_APPT_VER, '<')) {
        cosy_create_services_table();
        cosy_create_media_table();
        cosy_create_reviews_table();
        update_option('cosy_db_version', COSY_APPT_VER);
    }
}

//-------Create tables--------//
function cosy_create_services_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'provider_services';
    $charset_collate = $wpdb->get_charset_collate();

    // Check if table exists
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
        // Safe column migration: add missing columns to existing live tables
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

    // Fail-safe direct column addition (in case dbDelta parses it incorrectly)
    $checkbox_check = $wpdb->get_results("SHOW COLUMNS FROM `$table_name` LIKE 'checkbox_status'");
    if (empty($checkbox_check)) {
        $wpdb->query("ALTER TABLE `$table_name` ADD `checkbox_status` VARCHAR(10) NOT NULL DEFAULT 'no'");
    }
    $created_at_check = $wpdb->get_results("SHOW COLUMNS FROM `$table_name` LIKE 'created_at'");
    if (empty($created_at_check)) {
        $wpdb->query("ALTER TABLE `$table_name` ADD `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");
    }
}

//-------Create Media Approvals table--------//
function cosy_create_media_table()
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

//-------Create Reviews table--------//
/**
 * Database Schema Builder: Creates the provider reviews custom table.
 * 
 * Purpose:
 * - This table ('wp_cosy_provider_reviews') is used to persist customer feedback for service providers.
 * - By using a dedicated database table instead of standard WordPress comments, we achieve 
 *   high performance, easier scoping, and clean administration of reviews within the Bento dashboard.
 * 
 * Safety:
 * - Checks if the table already exists via "SHOW TABLES LIKE..." to prevent overwriting or deleting
 *   existing customer reviews during plugin updates/reactivations.
 */
function cosy_create_reviews_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'cosy_provider_reviews';
    $charset_collate = $wpdb->get_charset_collate();

    // Verify if table does not exist yet to prevent overwriting data
    if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) != $table_name) {
        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            provider_id BIGINT(20) UNSIGNED NOT NULL,
            customer_id BIGINT(20) UNSIGNED NOT NULL,
            customer_name VARCHAR(255) NOT NULL,
            rating TINYINT(1) UNSIGNED NOT NULL,
            review TEXT NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // Use WordPress dbDelta function to securely install/modify database tables
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}


//-------Register activation hook--------//
register_activation_hook(__FILE__, 'cosy_plugin_activate');

//-------Register deactivation hook--------//
register_deactivation_hook(__FILE__, 'cosy_plugin_deactivate');

//-------Register roles & database tables on activation--------//
function cosy_plugin_activate()
{
    // Call all activation tasks here
    register_role();
    cosy_create_pages_on_activation();
    cosy_create_services_table();
    cosy_create_media_table();
    cosy_create_reviews_table();
    update_option('cosy_plugin_version', COSY_APPT_VER);

    // Flush rewrite rules on activation to ensure custom links work immediately
    cosyplugin_author_rewrite();
    flush_rewrite_rules();
}

//-------Clean up rewrite rules on deactivation--------//
function cosy_plugin_deactivate()
{
    // Clean up custom rewrite rules on deactivation to prevent conflicts
    flush_rewrite_rules();
}

/**
 * cosy_appt_start
 * 
 * This is the entry point of the plugin.
 * It initializes the main Plugin class and triggers the run() method.
 */
function cosy_appt_start()
{
    $plugin = new \Cosy\Appointments\Plugin();
    new \Cosy\Appointments\Admin\DeactivationHandler();
    $plugin->run();
}
cosy_appt_start();


//--------Rewrite rules--------//
function cosyplugin_author_rewrite()
{
    // Rule for provider profile
    // Rule for service-provider category filtering
    add_rewrite_rule(
        '^service-provider/([^/]*)/?',
        'index.php?pagename=service-provider&service_category=$matches[1]',
        'top'
    );
}
add_action('init', 'cosyplugin_author_rewrite');

// Register query variable
function cosy_register_query_vars($vars)
{
    $vars[] = 'service_category';
    return $vars;
}
add_filter('query_vars', 'cosy_register_query_vars');
