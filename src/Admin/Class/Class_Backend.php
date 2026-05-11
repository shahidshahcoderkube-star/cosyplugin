<?php

namespace Cosy\Appointments\Admin;

use Cosy\Appointments\Loader;

use Cosy\Appointments\Common\GlobalCommonFunctions;

class Backend_Actions_Handler
{
    use GlobalCommonFunctions;

    //--------------- Constructor ----------------//
    public function __construct()
    {
        //------ Register all AJAX handlers dynamically-----//
        $actions = [
            'video_approve' => 'ajax_approve_video',
            'video_reject' => 'ajax_reject_video',
        ];

        //------ Register AJAX handlers -----//
        $this->register_ajax_handlers($actions, $this);
        // var_dump($_POST);
        // die;
    }

    public function register(Loader $loader): void
    {
        // No need to add actions here as they are registered in the constructor
    }
    //--------------- Approve video ---------------//
    public function ajax_approve_video()
    {
        // ✅ Security check
        check_ajax_referer('cosy_media_nonce', 'nonce');
        if (!current_user_can('approve_cosy_media')) {
            wp_send_json_error(['message' => 'Unauthorized access']);
        }

        $user_id = intval($_POST['user_id']);
        if (!$user_id) {
            wp_send_json_error(['message' => 'Invalid user ID']);
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'cosy_media_approvals';

        // ✅ Update DB table status
        $wpdb->update(
            $table_name,
            ['status' => 'approved', 'reviewed_at' => current_time('mysql')],
            ['user_id' => $user_id],
            ['%s', '%s'],
            ['%d']
        );

        // Update status in meta
        update_user_meta($user_id, 'video_status', 'approved');

        // Send email to provider
        $user = get_userdata($user_id);
        if ($user) {
            wp_mail(
                $user->user_email,
                'Your Video is Approved',
                'Hello ' . $user->display_name . ', your video has been approved by admin.'
            );
        }

        wp_send_json_success([
            'message' => 'Video approved successfully!',
            'status' => 'approved'
        ]);
    }

    //--------------- Reject video ---------------//
    public function ajax_reject_video()
    {
        // ✅ Security check
        check_ajax_referer('cosy_media_nonce', 'nonce');
        if (!current_user_can('approve_cosy_media')) {
            wp_send_json_error(['message' => 'Unauthorized access']);
        }

        $user_id = intval($_POST['user_id']);
        if (!$user_id) {
            wp_send_json_error(['message' => 'Invalid user ID']);
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'cosy_media_approvals';

        // ✅ Update DB table status
        $wpdb->update(
            $table_name,
            ['status' => 'rejected', 'reviewed_at' => current_time('mysql')],
            ['user_id' => $user_id],
            ['%s', '%s'],
            ['%d']
        );

        // Delete video + update status in meta
        delete_user_meta($user_id, 'introduction_video');
        update_user_meta($user_id, 'video_status', 'rejected');

        wp_send_json_success([
            'message' => 'Video rejected and deleted!',
            'status' => 'rejected'
        ]);
    }
}
