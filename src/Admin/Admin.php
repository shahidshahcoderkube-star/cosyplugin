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
        // $loader->add_action('admin_enqueue_scripts', $this, 'enqueue_admin_assets');
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
