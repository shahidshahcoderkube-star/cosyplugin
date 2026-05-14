<?php

/**
 * Plugin Name: Cosy Appointments
 * Description: Multi-provider appointment booking plugin.
 * Version: 1.0.0
 * Author: Shahid
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Constants define karo
define('COSY_APPT_PATH', plugin_dir_path(__FILE__));   // Plugin folder ka path
define('COSY_APPT_URL', plugin_dir_url(__FILE__));     // Plugin folder ka URL
define('COSY_APPT_VER', '1.0.0');  // Plugin version

//------------ Register role----------------//
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
                'edit_posts' => true,        // apni post edit
                'edit_published_posts' => true,
                'delete_posts' => true,      // apni post delete
                'manage_cosy_appointments' => true,
            ]
        );
    }

    // Add capabilities to Administrator
    $admin = get_role('administrator');
    if ($admin) {
        $admin->add_cap('manage_cosy_appointments');
        $admin->add_cap('approve_cosy_media');
    }
}



// Activation hook: create pages
function cosy_create_pages_on_activation()
{
    // Pages to create and their shortcodes 
    $pages = [
        ['title' => 'Appointments', 'slug' => 'appointments', 'content' => '[cosy_appointments]'],
        ['title' => 'Orders', 'slug' => 'orders', 'content' => '[cosy_orders]'],
        ['title' => 'Customer Registration', 'slug' => 'user-registration', 'content' => '[cosy_customer_registration]'],
        ['title' => 'Provider Registration', 'slug' => 'provider-registration', 'content' => '[cosy_provider_registration]'],
        ['title' => 'Login form', 'slug' => 'login', 'content' => '[cosy_login_form]'],
        ['title' => 'Customer Profile', 'slug' => 'customer-profile', 'content' => '[customer_profile]'],
        ['title' => 'Customer Order', 'slug' => 'customer-order', 'content' => '[cosy_customer_order]'],
        ['title' => 'Provider Dashboard', 'slug' => 'provider-dashboard', 'content' => '[cosy_provider_dashboard]'],
        ['title' => 'Provider Verify', 'slug' => 'provider-verify', 'content' => '[cosy_verify_provider]'],
        ['title' => 'Service Provider', 'slug' => 'service-provider', 'content' => '[cosy_service_provider_list]'],
        ['title' => 'Provider Profile', 'slug' => 'provider-profile', 'content' => '[cosy_profile_dashboard]'],
    ];

    // Check if page already exists by slug
    foreach ($pages as $page) {
        $existing = get_page_by_path($page['slug']);
        if (!$existing) {
            wp_insert_post([
                'post_title' => $page['title'],
                'post_name' => $page['slug'],
                'post_content' => $page['content'],
                'post_status' => 'publish',
                'post_type' => 'page',
            ]);
        }
    }
}

// Require classes (no autoloader, so manual requires)
// require_once COSY_APPT_PATH . 'src/PostTypes/AppointmentCPT.php';
require_once COSY_APPT_PATH . 'src/Frontend/Class-Common.php';
require_once COSY_APPT_PATH . 'src/Loader.php';
require_once COSY_APPT_PATH . 'src/Admin/Class/Class_Admin.php';
require_once COSY_APPT_PATH . 'src/Admin/Class/MediaApprove.php';
require_once COSY_APPT_PATH . 'src/Admin/Class/Class_Backend.php';
require_once COSY_APPT_PATH . 'src/Admin/Class/SettingsAdmin.php';
require_once COSY_APPT_PATH . 'src/Admin/Class/OrdersAdmin.php';
require_once COSY_APPT_PATH . 'src/Admin/Class/DashboardAdmin.php';
require_once COSY_APPT_PATH . 'src/Admin/Class/Class_Provider_Verification.php';
require_once COSY_APPT_PATH . 'src/PostTypes/ServiceCPT.php';
require_once COSY_APPT_PATH . 'src/Assets/Assets.php';
require_once COSY_APPT_PATH . 'src/Frontend/Class_Frontend.php';
require_once COSY_APPT_PATH . 'src/Frontend/Class_Provider_Dashboard.php';
require_once COSY_APPT_PATH . 'src/Frontend/Class_Forms.php';
require_once COSY_APPT_PATH . 'src/Rest/Routes.php';
require_once COSY_APPT_PATH . 'src/Rest/Class_Service_Provider.php';
require_once COSY_APPT_PATH . 'src/Frontend/Class_Header_Menu.php';
require_once COSY_APPT_PATH . 'src/Plugin.php';

//-------Create tables--------//
function cosy_create_services_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'provider_services';
    $charset_collate = $wpdb->get_charset_collate();

    // Check if table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            service_id BIGINT(20) UNSIGNED NOT NULL,
            service VARCHAR(255) NOT NULL,
            description TEXT,
            price DECIMAL(10,2),
            duration VARCHAR(50),
            provider_id BIGINT(20) UNSIGNED NOT NULL,
            provider VARCHAR(100),
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

//-------Create Media Approvals table--------//
function cosy_create_media_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'cosy_media_approvals';
    $charset_collate = $wpdb->get_charset_collate();

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
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

//-------Register activation hook--------//
register_activation_hook(__FILE__, 'cosy_plugin_activate');

//-------Register roles--------//
function cosy_plugin_activate()
{
    // Call all activation tasks here
    register_role();
    cosy_create_pages_on_activation();
    cosy_create_services_table();
    cosy_create_media_table();
    update_option('cosy_plugin_version', COSY_APPT_VER);
}

//---------Start the plugin--------//
function cosy_appt_start()
{
    $plugin = new \Cosy\Appointments\Plugin();
    new \Cosy\Appointments\Admin\Backend_Actions_Handler();
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
