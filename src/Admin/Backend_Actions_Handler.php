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
            'cosy_delete_orders' => 'ajax_delete_orders',
            'cosy_delete_media' => 'ajax_delete_media',
            'cosy_toggle_page_logging' => 'ajax_toggle_page_logging',
            'cosy_clear_activity_logs' => 'ajax_clear_activity_logs',
        ];

        //------ Register AJAX handlers -----//
        $this->register_ajax_handlers($actions, $this);
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
            
            \Cosy\Appointments\Common\LogManager::log(
                'media_approve',
                'video_approved',
                sprintf(__('Admin approved introduction video for Provider "%s" (ID: %d).', 'cosy-appointments'), $user->display_name, $user_id)
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
            $this->delete_media_file_by_url($video_url);
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

            \Cosy\Appointments\Common\LogManager::log(
                'media_approve',
                'video_rejected',
                sprintf(__('Admin rejected/deleted introduction video for Provider "%s" (ID: %d).', 'cosy-appointments'), $user->display_name, $user_id)
            );
        }

        wp_send_json_success([
            'message' => 'Video rejected and deleted!',
            'status' => 'rejected'
        ]);
    }

    /**
     * AJAX Handler: Bulk delete selected orders
     */
    public function ajax_delete_orders()
    {
        // Security check
        check_ajax_referer('cosy_delete_orders_action', 'nonce');
        
        if (!current_user_can('manage_cosy_appointments')) {
            wp_send_json_error(['message' => __('Unauthorized access', 'cosy-appointments')]);
        }

        $order_ids = isset($_POST['order_ids']) ? array_map('intval', $_POST['order_ids']) : [];
        if (empty($order_ids)) {
            wp_send_json_error(['message' => __('No orders selected for deletion.', 'cosy-appointments')]);
        }

        $deleted_count = 0;
        foreach ($order_ids as $id) {
            if ($id > 0) {
                // Delete appointment post (permanently)
                $result = wp_delete_post($id, true);
                if ($result) {
                    $deleted_count++;
                }
            }
        }

        if ($deleted_count > 0) {
            \Cosy\Appointments\Common\LogManager::log(
                'orders',
                'order_deleted',
                sprintf(__('Admin bulk deleted %d booking order(s) (IDs: %s).', 'cosy-appointments'), $deleted_count, implode(', ', $order_ids))
            );
        }

        wp_send_json_success([
            'message' => sprintf(_n('Successfully deleted %d order.', 'Successfully deleted %d orders.', $deleted_count, 'cosy-appointments'), $deleted_count),
            'deleted_count' => $deleted_count
        ]);
    }

    /**
     * AJAX handler: Toggle logging state for a page.
     */
    public function ajax_toggle_page_logging()
    {
        check_ajax_referer('cosy_log_toggle_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized access', 'cosy-appointments')]);
        }

        $page = sanitize_key($_POST['page_name'] ?? '');
        $status = intval($_POST['status'] ?? 1);

        if (empty($page)) {
            wp_send_json_error(['message' => __('Invalid page name.', 'cosy-appointments')]);
        }

        $key = \Cosy\Appointments\Common\LogManager::get_toggle_key($page);
        update_option($key, $status ? '1' : '0');

        // Log the toggle action itself
        \Cosy\Appointments\Common\LogManager::log(
            'settings',
            'logging_toggle',
            sprintf(__('Admin toggled logging for page "%s" to %s.', 'cosy-appointments'), $page, $status ? 'Active' : 'Deactive')
        );

        wp_send_json_success([
            'message' => sprintf(__('Logging for %s has been %s.', 'cosy-appointments'), ucfirst($page), $status ? __('enabled', 'cosy-appointments') : __('disabled', 'cosy-appointments')),
            'status'  => $status
        ]);
    }

    /**
     * AJAX handler: Clear all activity logs.
     */
    public function ajax_clear_activity_logs()
    {
        // Manual nonce verification for graceful JSON response instead of wp_die()
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cosy_clear_logs_nonce')) {
            wp_send_json_error(['message' => __('Invalid security token. Please refresh the page and try again.', 'cosy-appointments')]);
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized access.', 'cosy-appointments')]);
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'cosy_activity_logs';

        // Delete all log entries — do NOT call LogManager::log() here,
        // because it would immediately re-insert a row, making the table never truly empty.
        $result = $wpdb->query("DELETE FROM $table_name");

        if ($result === false) {
            wp_send_json_error(['message' => __('Database error while clearing logs. Please try again.', 'cosy-appointments')]);
        }

        wp_send_json_success([
            'message' => __('All activity logs have been cleared successfully.', 'cosy-appointments')
        ]);
    }

    /**
     * AJAX Handler: Bulk delete selected media
     */
    public function ajax_delete_media()
    {
        // Security check
        check_ajax_referer('cosy_media_nonce', 'nonce');
        
        if (!current_user_can('approve_cosy_media')) {
            wp_send_json_error(['message' => __('Unauthorized access', 'cosy-appointments')]);
        }

        $media_ids = isset($_POST['media_ids']) ? array_map('intval', $_POST['media_ids']) : [];
        if (empty($media_ids)) {
            wp_send_json_error(['message' => __('No media selected for deletion.', 'cosy-appointments')]);
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'cosy_media_approvals';

        $deleted_count = 0;
        foreach ($media_ids as $id) {
            if ($id > 0) {
                // Fetch the record to get user_id and media_url before deleting
                $record = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));
                if ($record) {
                    $user_id = $record->user_id;
                    $video_url = $record->media_url;

                    // 1. Delete physical attachment from media library if exists
                    if (!empty($video_url)) {
                        $this->delete_media_file_by_url($video_url);
                    }

                    // 2. Also check if the user meta currently points to this video
                    $meta_video = get_user_meta($user_id, 'introduction_video', true);
                    if ($meta_video === $video_url) {
                        delete_user_meta($user_id, 'introduction_video');
                        update_user_meta($user_id, 'video_status', 'rejected');
                    }

                    // 3. Delete row from media approvals table
                    $deleted = $wpdb->delete($table_name, ['id' => $id], ['%d']);
                    if ($deleted !== false) {
                        $deleted_count++;
                        
                        // Log activity
                        $provider = get_userdata($user_id);
                        $prov_name = $provider ? $provider->display_name : "ID $user_id";
                        \Cosy\Appointments\Common\LogManager::log(
                            'media_approve',
                            'media_deleted',
                            sprintf(__('Admin deleted introduction media for Provider "%s" (Record ID: %d).', 'cosy-appointments'), $prov_name, $id)
                        );
                    }
                }
            }
        }

        wp_send_json_success([
            'message' => sprintf(_n('Successfully deleted %d media entry.', 'Successfully deleted %d media entries.', $deleted_count, 'cosy-appointments'), $deleted_count),
            'deleted_count' => $deleted_count
        ]);
    }
}
