<?php

namespace Cosy\Appointments\Common;

if (!defined('ABSPATH')) {
    exit;
}

class Activator
{
    /**
     * EXECUTES ACTIVATION ROUTINES
     * 
     * USE CASE:
     * Triggered when plugin is activated in WP Admin.
     * 
     * HOW TO USE:
     * (new Activator())->activate();
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Registers 'customer' and 'provider' user roles.
     * 2. Auto-creates required frontend booking pages.
     * 3. Executes custom database migrations (services, media, reviews, logs, AI vector embeddings).
     * 4. Schedules daily activity log cleanup cron job.
     * 5. Flushes WordPress rewrite rules.
     */
    public function activate(): void
    {
        $this->register_role();
        $this->create_pages();

        // Create Database tables
        $db = new Database();
        $db->create_services_table();
        $db->create_media_table();
        $db->create_reviews_table();
        $db->create_activity_logs_table();

        // Create AI Search Vector & Cache tables
        \Cosy\Appointments\AI\DatabaseSetup::create_tables();

        update_option('cosy_plugin_version', COSY_APPT_VER);

        // Schedule daily logs cleanup cron
        if (!wp_next_scheduled('cosy_cleanup_activity_logs_cron')) {
            wp_schedule_event(time(), 'daily', 'cosy_cleanup_activity_logs_cron');
        }

        // Flush rewrite rules to ensure custom endpoints/slugs work immediately
        $this->author_rewrite();
        flush_rewrite_rules();
    }

    /**
     * EXECUTES DEACTIVATION ROUTINES
     * 
     * USE CASE:
     * Triggered when plugin is deactivated in WP Admin.
     * 
     * HOW TO USE:
     * (new Activator())->deactivate();
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Flushes WordPress rewrite rules.
     * 2. Clears scheduled activity log cleanup cron job.
     */
    public function deactivate(): void
    {
        flush_rewrite_rules();
        wp_clear_scheduled_hook('cosy_cleanup_activity_logs_cron');
    }

    /**
     * REGISTERS CUSTOM USER ROLES
     * 
     * USE CASE:
     * Creates 'customer' and 'provider' roles with dedicated capabilities during activation.
     * 
     * HOW TO USE:
     * $activator->register_role();
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Checks if 'customer' role exists; creates it with read capabilities if missing.
     * 2. Checks if 'provider' role exists; creates it with upload and edit capabilities if missing.
     */
    public function register_role(): void
    {
        // CUSTOMER role
        if (!get_role('customer')) {
            add_role(
                'customer',
                'Customer',
                [
                    'read' => true,
                ]
            );
        }

        // PROVIDER role
        if (!get_role('provider')) {
            add_role(
                'provider',
                'Provider',
                [
                    'read' => true,
                    'edit_posts' => true,
                    'edit_published_posts' => true,
                    'delete_posts' => true,
                    'manage_cosy_appointments' => true,
                ]
            );
        }

        // Give Administrator full control over the plugin capabilities
        $admin = get_role('administrator');
        if ($admin) {
            $admin->add_cap('manage_cosy_appointments');
            $admin->add_cap('approve_cosy_media');
        }
    }

    /**
     * Automatically creates default pages with shortcodes.
     */
    public function create_pages(): void
    {
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
            ['title' => 'Leave a Review',           'slug' => 'cosy-leave-review',   'content' => '[cosy_leave_review]'],
        ];

        foreach ($pages as $page) {
            $existing = get_page_by_path($page['slug']);
            $opt_key = 'cosy_page_id_' . str_replace('-', '_', $page['slug']);

            if (!$existing) {
                $page_id = wp_insert_post([
                    'post_title'   => $page['title'],
                    'post_name'    => $page['slug'],
                    'post_content' => $page['content'],
                    'post_status'  => 'publish',
                    'post_type'    => 'page',
                ]);
                if (!is_wp_error($page_id)) {
                    update_option($opt_key, $page_id);
                }
            } else {
                wp_update_post([
                    'ID'         => $existing->ID,
                    'post_title' => $page['title'],
                ]);
                update_option($opt_key, $existing->ID);
            }
        }
    }

    /**
     * Register rewrite rule for provider profiles.
     */
    public function author_rewrite(): void
    {
        add_rewrite_rule(
            '^service-provider/([^/]*)/?',
            'index.php?pagename=service-provider&service_category=$matches[1]',
            'top'
        );
    }

    /**
     * Register query variables for the custom rewrite rules.
     */
    public function register_query_vars(array $vars): array
    {
        $vars[] = 'service_category';
        return $vars;
    }

    /**
     * Check if default pages exist, otherwise create them.
     */
    public function check_and_create_missing_pages(): void
    {
        $checkout_page = get_page_by_path('cosy-checkout');
        if (!$checkout_page) {
            $this->create_pages();
            flush_rewrite_rules();
        }
    }
}
