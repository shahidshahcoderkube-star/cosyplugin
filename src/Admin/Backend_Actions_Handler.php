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
            
            $message = '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>' . esc_html($subject) . '</title>
            </head>
            <body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; color: #334155;">
                <div style="max-width: 600px; margin: 40px auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); border: 1px solid #e2e8f0;">
                    <div style="background: linear-gradient(135deg, #a44390 0%, #631852 100%); padding: 30px; text-align: center;">
                        <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 700; letter-spacing: -0.5px;">🎉 ' . esc_html__('Video Approved!', 'cosy-appointments') . '</h1>
                    </div>
                    <div style="padding: 40px 30px;">
                        <p style="font-size: 16px; line-height: 1.6; margin-top: 0;">' . sprintf(esc_html__('Hello %s,', 'cosy-appointments'), '<strong>' . esc_html($user->display_name) . '</strong>') . '</p>
                        <p style="font-size: 16px; line-height: 1.6;">' . esc_html__('Great news! Your profile\'s introductory video has been reviewed and successfully approved by our administration team.', 'cosy-appointments') . '</p>
                        <p style="font-size: 16px; line-height: 1.6;">' . esc_html__('Your video is now live on your public profile page, allowing customers to see your introduction and learn more about your services.', 'cosy-appointments') . '</p>
                        <div style="text-align: center; margin: 30px 0;">
                            <a href="' . $dashboard_url . '" style="display: inline-block; padding: 14px 30px; background-color: #a44390; color: #ffffff; text-decoration: none; font-weight: 600; border-radius: 8px; font-size: 16px; box-shadow: 0 4px 6px rgba(164, 67, 144, 0.2);">' . esc_html__('Go to Dashboard', 'cosy-appointments') . '</a>
                        </div>
                        <p style="font-size: 14px; line-height: 1.6; color: #64748b; margin-bottom: 0;">' . esc_html__('If you have any questions or need assistance, please feel free to reach out to our support team.', 'cosy-appointments') . '</p>
                    </div>
                    <div style="background-color: #f1f5f9; padding: 20px 30px; text-align: center; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b;">
                        <p style="margin: 0;">' . esc_html__('This is an automated notification from Cosy Appointments. Please do not reply directly to this email.', 'cosy-appointments') . '</p>
                    </div>
                </div>
            </body>
            </html>';

            $headers = array('Content-Type: text/html; charset=UTF-8');
            wp_mail($user->user_email, $subject, $message, $headers);
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

            $message = '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>' . esc_html($subject) . '</title>
            </head>
            <body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; color: #334155;">
                <div style="max-width: 600px; margin: 40px auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); border: 1px solid #e2e8f0;">
                    <div style="background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); padding: 30px; text-align: center;">
                        <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 700; letter-spacing: -0.5px;">⚠️ ' . esc_html__('Video Update Required', 'cosy-appointments') . '</h1>
                    </div>
                    <div style="padding: 40px 30px;">
                        <p style="font-size: 16px; line-height: 1.6; margin-top: 0;">' . sprintf(esc_html__('Hello %s,', 'cosy-appointments'), '<strong>' . esc_html($user->display_name) . '</strong>') . '</p>
                        <p style="font-size: 16px; line-height: 1.6;">' . esc_html__('Thank you for uploading your introductory video. During our review, we found that the video did not meet our guidelines or quality standards, and it has been rejected.', 'cosy-appointments') . '</p>
                        <p style="font-size: 16px; line-height: 1.6;">' . esc_html__('Please log in to your dashboard to upload a new video that complies with our guidelines (Max 3 MB, MP4 format).', 'cosy-appointments') . '</p>
                        <div style="text-align: center; margin: 30px 0;">
                            <a href="' . $dashboard_url . '" style="display: inline-block; padding: 14px 30px; background-color: #ef4444; color: #ffffff; text-decoration: none; font-weight: 600; border-radius: 8px; font-size: 16px; box-shadow: 0 4px 6px rgba(239, 68, 68, 0.2);">' . esc_html__('Upload New Video', 'cosy-appointments') . '</a>
                        </div>
                        <p style="font-size: 14px; line-height: 1.6; color: #64748b; margin-bottom: 0;">' . esc_html__('If you have any questions or need assistance, please feel free to reach out to our support team.', 'cosy-appointments') . '</p>
                    </div>
                    <div style="background-color: #f1f5f9; padding: 20px 30px; text-align: center; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b;">
                        <p style="margin: 0;">' . esc_html__('This is an automated notification from Cosy Appointments. Please do not reply directly to this email.', 'cosy-appointments') . '</p>
                    </div>
                </div>
            </body>
            </html>';

            $headers = array('Content-Type: text/html; charset=UTF-8');
            wp_mail($user->user_email, $subject, $message, $headers);
        }

        wp_send_json_success([
            'message' => 'Video rejected and deleted!',
            'status' => 'rejected'
        ]);
    }
}
