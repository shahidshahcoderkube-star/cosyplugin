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
        // Security check
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

        // Update DB table status
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
            $subject = __('Your Introduction Video is Approved!', 'cosy-appointments');
            $dashboard_url = esc_url(home_url('/provider-dashboard/'));
            
            $html_content = "
                <p>Hello <strong>" . esc_html($user->display_name) . "</strong>,</p>
                <p>Great news! Your profile's introductory video has been reviewed and successfully approved by our administration team.</p>
                <p>Your video is now live on your public profile page, allowing customers to see your introduction and learn more about your services.</p>
                <p style='text-align: center; margin: 30px 0;'>
                    <a href='" . esc_url($dashboard_url) . "' style='background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%); color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 50px; font-weight: 600; display: inline-block; box-shadow: 0 4px 12px rgba(164, 67, 144, 0.2);'>" . __('Go to Dashboard', 'cosy-appointments') . "</a>
                </p>
                <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-bottom: 0;'>" . __('If you have any questions or need assistance, please feel free to reach out to our support team.', 'cosy-appointments') . "</p>
            ";
            
            cosy_send_html_email($user->user_email, $subject, __('Video Approved!', 'cosy-appointments'), $html_content);
        }

        wp_send_json_success([
            'message' => 'Video approved successfully!',
            'status' => 'approved'
        ]);
    }

    //--------------- Reject video ---------------//
    public function ajax_reject_video()
    {
        // Security check
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

        // Update DB table status
        $wpdb->update(
            $table_name,
            ['status' => 'rejected', 'reviewed_at' => current_time('mysql')],
            ['user_id' => $user_id],
            ['%s', '%s'],
            ['%d']
        );

        // Fetch the video URL to delete it from media library
        $video_url = get_user_meta($user_id, 'introduction_video', true);
        if ($video_url) {
            $attachment_id = attachment_url_to_postid($video_url);
            if ($attachment_id) {
                wp_delete_attachment($attachment_id, true); // true = bypass trash and delete permanently
            }
        }

        // Delete video + update status in meta
        delete_user_meta($user_id, 'introduction_video');
        update_user_meta($user_id, 'video_status', 'rejected');

        // Send rejection email to provider
        $user = get_userdata($user_id);
        if ($user) {
            $subject = __('Video Upload Update Required', 'cosy-appointments');
            $dashboard_url = esc_url(home_url('/provider-dashboard/'));

            $html_content = "
                <p>Hello <strong>" . esc_html($user->display_name) . "</strong>,</p>
                <p>Thank you for uploading your introductory video. During our review, we found that the video did not meet our guidelines or quality standards, and it has been rejected.</p>
                <p>Please log in to your dashboard to upload a new video that complies with our guidelines (Max 3 MB, MP4 format).</p>
                <p style='text-align: center; margin: 30px 0;'>
                    <a href='" . esc_url($dashboard_url) . "' style='background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 50px; font-weight: 600; display: inline-block; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);'>" . __('Upload New Video', 'cosy-appointments') . "</a>
                </p>
                <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-bottom: 0;'>" . __('If you have any questions or need assistance, please feel free to reach out to our support team.', 'cosy-appointments') . "</p>
            ";

            cosy_send_html_email($user->user_email, $subject, __('Video Update Required', 'cosy-appointments'), $html_content);
        }

        wp_send_json_success([
            'message' => 'Video rejected and deleted!',
            'status' => 'rejected'
        ]);
    }
}
