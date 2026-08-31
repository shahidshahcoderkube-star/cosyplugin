<?php

namespace Cosy\Appointments\Admin;

use Cosy\Appointments\Admin\OrdersAdmin;
use Cosy\Appointments\Admin\DashboardAdmin;
use Cosy\Appointments\Admin\UsersAdmin;
use Cosy\Appointments\Email\EmailTemplatesAdmin;
use Cosy\Appointments\Loader;
use Cosy\Appointments\Common\GlobalCommonFunctions;

class Admin
{
    //------- Common functions like get_user_data() can be used here ------//
    use GlobalCommonFunctions;

    private $ordersAdmin;
    private $dashboardAdmin;
    private $usersAdmin;
    private $emailTemplatesAdmin;

    /**
     * CONSTRUCTS ADMIN CONTROLLER & SUB-MODULES
     * 
     * USE CASE:
     * Instantiated during plugin load sequence to setup admin menu controllers.
     * 
     * HOW TO USE:
     * $admin = new Admin();
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Instantiates OrdersAdmin, DashboardAdmin, UsersAdmin, and EmailTemplatesAdmin controller instances.
     */
    public function __construct()
    {
        $this->ordersAdmin = new OrdersAdmin();
        $this->dashboardAdmin = new DashboardAdmin();
        $this->usersAdmin = new UsersAdmin();
        $this->emailTemplatesAdmin = new EmailTemplatesAdmin();
    }

    /**
     * REGISTERS ADMIN MENU HOOKS & AUDIT LOGGERS
     * 
     * USE CASE:
     * Registers admin menu pages and post/option modification audit hooks into WordPress.
     * 
     * HOW TO USE:
     * (new Admin())->register($loader);
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Attaches admin_add_menus to 'admin_menu' hook.
     * 2. Attaches CPT save/trash loggers to 'save_post_cosy_service' and 'wp_trash_post'.
     * 3. Attaches settings update logger to 'updated_option'.
     * 
     * @param Loader $loader Plugin loader instance.
     */
    public function register(Loader $loader): void
    {
        $loader->add_action('admin_menu', $this, 'admin_add_menus');
        $loader->add_action('save_post_cosy_service', $this, 'log_admin_service_save', 10, 3);
        $loader->add_action('wp_trash_post', $this, 'log_admin_service_trash');
        $loader->add_action('updated_option', $this, 'log_admin_settings_update', 10, 3);
        $this->emailTemplatesAdmin->register($loader);
    }

    /**
     * REGISTERS ADMIN DASHBOARD MENU & SUBMENUS
     * 
     * USE CASE:
     * Registers main "CC Booking" WP Admin menu and notification bubble badges for pending items.
     * 
     * HOW TO USE:
     * Triggered automatically during WordPress 'admin_menu' hook.
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Dynamically queries counts for pending orders, unapproved videos, pending provider verifications, and unreplied reviews.
     * 2. Registers main menu page via add_menu_page().
     * 3. Registers submenus for Dashboard, Experiences CPT, Orders, Media Approve, Users, Reviews, and Logs.
     * 4. Formats notification bubble HTML badges next to menu titles.
     */
    public function admin_add_menus(): void
    {
        global $wpdb;

        // 1. Pending Orders Count
        $orders_count = count(get_posts([
            'post_type'      => 'cosy_appointment',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_key'       => 'cosy_booking_status',
            'meta_value'     => 'pending',
            'fields'         => 'ids'
        ]));

        // 2. Pending Media Approvals Count
        $media_table = $wpdb->prefix . 'cosy_media_approvals';
        $media_count = 0;
        if ($wpdb->get_var("SHOW TABLES LIKE '$media_table'") === $media_table) {
            $media_count = (int) $wpdb->get_var("SELECT COUNT(*) FROM $media_table WHERE status = 'pending'");
        }

        // 3. Pending / Unverified Users & Providers Badge Count
        $pending_email_users = get_users([
            'meta_key'     => 'account_status',
            'meta_value'   => 'pending',
            'fields'       => 'ID',
        ]);

        $pending_approval_providers = get_users([
            'role'         => 'provider',
            'meta_key'     => 'cosy_provider_status',
            'meta_value'   => 'deactive',
            'meta_compare' => 'LIKE',
            'fields'       => 'ID',
        ]);

        $users_count = count(array_unique(array_merge($pending_email_users, $pending_approval_providers)));

        // 4. Pending Parent Reviews Count
        $reviews_table = $wpdb->prefix . 'cosy_provider_reviews';
        $reviews_count = 0;
        if ($wpdb->get_var("SHOW TABLES LIKE '$reviews_table'") === $reviews_table) {
            $reviews_count = (int) $wpdb->get_var("SELECT COUNT(*) FROM $reviews_table WHERE status = 'pending'");
        }

        // 5. System Logs Count
        $logs_table = $wpdb->prefix . 'cosy_activity_logs';
        $logs_count = 0;
        if ($wpdb->get_var("SHOW TABLES LIKE '$logs_table'") === $logs_table) {
            $logs_count = (int) $wpdb->get_var(
                "SELECT COUNT(*) FROM $logs_table WHERE action LIKE '%error%' OR action LIKE '%failed%' OR description LIKE '%error%' OR description LIKE '%failed%'"
            );
        }

        add_menu_page(
            __('Booking Dashboard', 'cosy-appointments'),
            __('CC Booking', 'cosy-appointments'),
            'manage_cosy_appointments',
            'cosy-booking-dashboard',
            [$this->dashboardAdmin, 'render_booking_dashboard'],
            'dashicons-calendar-alt',
            6
        );

        add_submenu_page(
            'cosy-booking-dashboard',
            __('Experiences', 'cosy-appointments'),
            __('Experiences', 'cosy-appointments'),
            'manage_cosy_appointments',
            'edit.php?post_type=cosy_service'
        );

        add_submenu_page(
            'cosy-booking-dashboard',
            __('Orders', 'cosy-appointments'),
            $this->format_menu_badge(__('Orders', 'cosy-appointments'), $orders_count),
            'manage_cosy_appointments',
            'cosy-orders',
            [$this->ordersAdmin, 'render_booking_orders']
        );

        add_submenu_page(
            'cosy-booking-dashboard',
            __('Media', 'cosy-appointments'),
            $this->format_menu_badge(__('Media', 'cosy-appointments'), $media_count),
            'approve_cosy_media',
            'cosy-media-approve',
            [$this, 'render_media_approve']
        );

        add_submenu_page(
            'cosy-booking-dashboard',
            __('Users', 'cosy-appointments'),
            $this->format_menu_badge(__('Users', 'cosy-appointments'), $users_count),
            'manage_cosy_appointments',
            'cosy-users',
            [$this->usersAdmin, 'render_users_page']
        );

        add_submenu_page(
            'cosy-booking-dashboard',
            __('Reviews', 'cosy-appointments'),
            $this->format_menu_badge(__('Reviews', 'cosy-appointments'), $reviews_count),
            'manage_cosy_appointments',
            'cosy-reviews',
            [$this, 'render_reviews_page']
        );

        add_submenu_page(
            'cosy-booking-dashboard',
            __('Email Templates', 'cosy-appointments'),
            __('Email Templates', 'cosy-appointments'),
            'manage_cosy_appointments',
            'cosy-email-templates',
            [$this->emailTemplatesAdmin, 'render_email_templates_page']
        );

        add_submenu_page(
            'cosy-booking-dashboard',
            __('Logs', 'cosy-appointments'),
            $this->format_menu_badge(__('Logs', 'cosy-appointments'), $logs_count),
            'manage_options',
            'cosy-logs',
            [$this, 'render_logs_page']
        );
    }

    private function format_menu_badge(string $title, int $count): string
    {
        if ($count <= 0) {
            return esc_html($title);
        }

        return sprintf(
            '%1$s <span class="update-plugins count-%2$d"><span class="plugin-count">%2$d</span></span>',
            esc_html($title),
            $count
        );
    }

    //--------------- Reviews Page Render Function ----------------//
    public function render_reviews_page(): void
    {
        ob_start();
        include COSY_APPT_PATH . 'src/Admin/Backend/reviews-page.php';
        echo ob_get_clean();
    }

    //--------------- Logs Page Render Function ----------------//
    public function render_logs_page(): void
    {
        ob_start();
        include COSY_APPT_PATH . 'src/Admin/Backend/logs.php';
        echo ob_get_clean(); // echo instead of return
    }
    /**
     * Log activity whenever an administrator creates or updates an Experience (service CPT).
     * 
     * USE CASE:
     * Triggered on WordPress 'save_post_cosy_service' action hook.
     */
    public function log_admin_service_save($post_id, $post, $update): void
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if ($post->post_status === 'auto-draft' || $post->post_status === 'trash') {
            return;
        }

        $action = $update ? 'service_updated' : 'service_created';
        $desc = $update
            ? sprintf(__('Admin updated service CPT: %s (ID: %d).', 'cosy-appointments'), $post->post_title, $post_id)
            : sprintf(__('Admin created service CPT: %s (ID: %d).', 'cosy-appointments'), $post->post_title, $post_id);

        \Cosy\Appointments\Common\LogManager::log('services', $action, $desc);
    }

    /**
     * Log activity whenever an administrator trashes/deletes an Experience (service CPT).
     * 
     * USE CASE:
     * Triggered on WordPress 'wp_trash_post' action hook.
     */
    public function log_admin_service_trash($post_id): void
    {
        $post = get_post($post_id);
        if ($post && $post->post_type === 'cosy_service') {
            \Cosy\Appointments\Common\LogManager::log(
                'services',
                'service_deleted',
                sprintf(__('Admin trashed service CPT: %s (ID: %d).', 'cosy-appointments'), $post->post_title, $post_id)
            );
        }
    }

    /**
     * Log activity whenever payment gateway settings options are updated in admin.
     * 
     * USE CASE:
     * Triggered on WordPress 'updated_option' action hook.
     */
    public function log_admin_settings_update($option, $old_value, $value): void
    {
        $cosy_settings_options = [
            'cosy_worldpay_test_mode',
            'cosy_worldpay_inst_id',
            'cosy_worldpay_token',
            'cosy_worldpay_password',
            'cosy_worldpay_client_key',
            'cosy_worldpay_charge',
            'cosy_charge_type',
            'cosy_provider_percentage',
            'cosy_fixed_charge',
            'cosy_worldpay_enabled'
        ];
        if (in_array($option, $cosy_settings_options)) {
            // Avoid logging exact secret/token values
            $logged_val = $value;
            if (in_array($option, ['cosy_worldpay_token', 'cosy_worldpay_password']) && !empty($value)) {
                $logged_val = '***HIDDEN***';
            }
            if ($old_value !== $value) {
                \Cosy\Appointments\Common\LogManager::log(
                    'settings',
                    'setting_updated',
                    sprintf(__('Admin updated payment setting "%s" to "%s".', 'cosy-appointments'), $option, is_array($logged_val) ? json_encode($logged_val) : $logged_val)
                );
            }
        }
    }


    //--------------- Media Approve Page Render Function ----------------//
    public function render_media_approve(): void
    {
        ob_start();
        include COSY_APPT_PATH . 'src/Admin/Backend/media-approve.php';
        echo ob_get_clean();
    }
}
