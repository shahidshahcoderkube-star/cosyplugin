<?php

namespace Cosy\Appointments\Admin;

use Cosy\Appointments\Admin\OrdersAdmin;
use Cosy\Appointments\Admin\DashboardAdmin;
use Cosy\Appointments\Admin\UsersAdmin;
use Cosy\Appointments\Loader;
use Cosy\Appointments\Common\GlobalCommonFunctions;

class Admin
{
    //------- Common functions like get_user_data() can be used here ------//
    use GlobalCommonFunctions;

    private $ordersAdmin;
    // private $mediaApprove;
    private $dashboardAdmin;
    private $usersAdmin;

    public function __construct()
    {
        $this->ordersAdmin = new OrdersAdmin();
        // $this->mediaApprove = new MediaApprove();
        $this->dashboardAdmin = new DashboardAdmin();
        $this->usersAdmin = new UsersAdmin();

        // //------ Register all AJAX handlers dynamically-----//
        // $actions = [
        //     'video_approve'   => 'ajax_approve_video',
        //     'video_reject'   => 'ajax_reject_video',
        // ];

        // //------ Register AJAX handlers -----//
        // $this->register_ajax_handlers($actions, $this);
    }

    public function register(Loader $loader): void
    {
        $loader->add_action('admin_menu', $this, 'admin_add_menus');
        $loader->add_action('save_post_cosy_service', $this, 'log_admin_service_save', 10, 3);
        $loader->add_action('wp_trash_post', $this, 'log_admin_service_trash');
        $loader->add_action('updated_option', $this, 'log_admin_settings_update', 10, 3);
    }


    //----------- Admin Menus ----------------//
    public function admin_add_menus(): void
    {
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
            __('Services', 'cosy-appointments'),
            __('Services', 'cosy-appointments'),
            'manage_cosy_appointments',
            'edit.php?post_type=cosy_service'
        );

        add_submenu_page(
            'cosy-booking-dashboard',
            __('Orders', 'cosy-appointments'),
            __('Orders', 'cosy-appointments'),
            'manage_cosy_appointments',
            'cosy-orders',
            [$this->ordersAdmin, 'render_booking_orders']
        );

        add_submenu_page(
            'cosy-booking-dashboard',
            __('Media Approve', 'cosy-appointments'),
            __('Media Approve', 'cosy-appointments'),
            'approve_cosy_media',
            'cosy-media-approve',
            [$this, 'render_media_approve']
        );

        add_submenu_page(
            'cosy-booking-dashboard',
            __('Users', 'cosy-appointments'),
            __('Users', 'cosy-appointments'),
            'manage_cosy_appointments',
            'cosy-users',
            [$this->usersAdmin, 'render_users_page']
        );

        add_submenu_page(
            'cosy-booking-dashboard',
            __('Logs', 'cosy-appointments'),
            __('Logs', 'cosy-appointments'),
            'manage_options',
            'cosy-logs',
            [$this, 'render_logs_page']
        );
    }

    //--------------- Logs Page Render Function ----------------//
    public function render_logs_page(): void
    {
        ob_start();
        include COSY_APPT_PATH . 'src/Admin/Backend/logs.php';
        echo ob_get_clean(); // echo instead of return
    }
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

    public function log_admin_settings_update($option, $old_value, $value): void
    {
        $cosy_settings_options = [
            'cosy_stripe_test_mode',
            'cosy_stripe_key',
            'cosy_stripe_publishable_key',
            'cosy_charge_type',
            'cosy_provider_percentage',
            'cosy_fixed_charge',
            'cosy_stripe_enabled',
            'cosy_worldpay_enabled'
        ];
        if (in_array($option, $cosy_settings_options)) {
            // Avoid logging exact secret/publishable key values
            $logged_val = $value;
            if (in_array($option, ['cosy_stripe_key', 'cosy_stripe_publishable_key']) && !empty($value)) {
                $logged_val = substr($value, 0, 7) . '...';
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
        echo ob_get_clean(); // echo instead of return
    }


    //--------------- Approve video ---------------//
    // public function ajax_approve_video()
    // {
    //     $user_id = intval($_POST['user_id']);
    //     if (!$user_id) {
    //         wp_send_json_error(['message' => 'Invalid user ID']);
    //     }

    //     // Update status
    //     update_user_meta($user_id, 'video_status', 'approved');

    //     // Send email to provider
    //     $user = get_userdata($user_id);
    //     wp_mail(
    //         $user->user_email,
    //         'Your Video is Approved',
    //         'Hello ' . $user->display_name . ', your video has been approved by admin.'
    //     );

    //     wp_send_json_success([
    //         'message' => 'Video approved successfully!',
    //         'status'  => 'approved'
    //     ]);
    // }

    // //--------------- Reject video ---------------//
    // public function ajax_reject_video()
    // {
    //     $user_id = intval($_POST['user_id']);
    //     if (!$user_id) {
    //         wp_send_json_error(['message' => 'Invalid user ID']);
    //     }

    //     // Delete video + update status
    //     delete_user_meta($user_id, 'introduction_video');
    //     update_user_meta($user_id, 'video_status', 'rejected');

    //     wp_send_json_success([
    //         'message' => 'Video rejected and deleted!',
    //         'status'  => 'rejected'
    //     ]);
    // }
}
