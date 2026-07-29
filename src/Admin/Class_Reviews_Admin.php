<?php

namespace Cosy\Appointments\Admin;

use Cosy\Appointments\Loader;

class Class_Reviews_Admin
{
    public function register(Loader $loader): void
    {
        $loader->add_action('wp_ajax_cosy_admin_approve_review', $this, 'handle_approve_review');
        $loader->add_action('wp_ajax_cosy_admin_reject_review', $this, 'handle_reject_review');
        $loader->add_action('wp_ajax_cosy_admin_delete_review', $this, 'handle_delete_review');
    }

    public function render_reviews_page(): void
    {
        $status_filter   = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $provider_filter = isset($_GET['provider']) ? intval($_GET['provider']) : 0;

        global $wpdb;
        $table_name = $wpdb->prefix . 'cosy_provider_reviews';

        $where = ["1=1"];
        if (!empty($status_filter)) {
            $where[] = $wpdb->prepare("status = %s", $status_filter);
        }
        if (!empty($provider_filter)) {
            $where[] = $wpdb->prepare("provider_id = %d", $provider_filter);
        }

        $where_sql = implode(' AND ', $where);
        $reviews = $wpdb->get_results("SELECT * FROM $table_name WHERE $where_sql ORDER BY id DESC");

        $providers = get_users(['role' => 'provider']);

        include COSY_APPT_PATH . 'templates/admin/reviews-admin-template.php';
    }

    public function handle_approve_review(): void
    {
        if (!current_user_can('manage_options') && !current_user_can('manage_cosy_appointments')) {
            wp_send_json_error(['message' => __('Unauthorized access.', 'cosy-appointments')]);
        }

        $review_id = isset($_POST['review_id']) ? intval($_POST['review_id']) : 0;
        if (!$review_id) {
            wp_send_json_error(['message' => __('Invalid review ID.', 'cosy-appointments')]);
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'cosy_provider_reviews';

        $review = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $review_id));
        if (!$review) {
            wp_send_json_error(['message' => __('Review not found.', 'cosy-appointments')]);
        }

        $updated = $wpdb->update(
            $table_name,
            ['status' => 'approved'],
            ['id' => $review_id],
            ['%s'],
            ['%d']
        );

        if ($updated !== false) {
            \Cosy\Appointments\Common\LogManager::log('reviews', 'APPROVE_REVIEW', sprintf('Admin approved Review #%d for Provider #%d (%s).', $review_id, $review->provider_id, $review->customer_name));

            $provider = get_userdata($review->provider_id);
            if ($provider && !empty($provider->user_email) && function_exists('cosy_send_html_email')) {
                $subject = __('New Parent Review Approved on Your Profile!', 'cosy-appointments');
                $heading = __('Parent Review Approved', 'cosy-appointments');
                $message_html = sprintf(
                    '<p style="margin-bottom: 15px;">Hello <strong>%s</strong>,</p>
                    <p style="margin-bottom: 15px;">A new parent review from <strong>%s</strong> (<strong>%d Stars</strong>) has been approved by the Administrator and is now live on your profile page.</p>
                    <blockquote style="background: #fdf5fc; border-left: 4px solid #a44390; padding: 12px 16px; margin: 15px 0; font-style: italic;">"%s"</blockquote>
                    <p style="margin-bottom: 0;">You can view and post a public response to this review from your <strong>Provider Dashboard &rarr; Parent Reviews</strong> tab.</p>',
                    esc_html($provider->display_name),
                    esc_html($review->customer_name),
                    intval($review->rating),
                    esc_html($review->review)
                );
                cosy_send_html_email($provider->user_email, $subject, $heading, $message_html);
            }

            wp_send_json_success(['message' => __('Review approved successfully.', 'cosy-appointments')]);
        } else {
            wp_send_json_error(['message' => __('Failed to approve review.', 'cosy-appointments')]);
        }
    }

    public function handle_reject_review(): void
    {
        if (!current_user_can('manage_options') && !current_user_can('manage_cosy_appointments')) {
            wp_send_json_error(['message' => __('Unauthorized access.', 'cosy-appointments')]);
        }

        $review_id = isset($_POST['review_id']) ? intval($_POST['review_id']) : 0;
        if (!$review_id) {
            wp_send_json_error(['message' => __('Invalid review ID.', 'cosy-appointments')]);
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'cosy_provider_reviews';

        $updated = $wpdb->update(
            $table_name,
            ['status' => 'rejected'],
            ['id' => $review_id],
            ['%s'],
            ['%d']
        );

        if ($updated !== false) {
            \Cosy\Appointments\Common\LogManager::log('reviews', 'REJECT_REVIEW', sprintf('Admin rejected Review #%d.', $review_id));
            wp_send_json_success(['message' => __('Review rejected.', 'cosy-appointments')]);
        } else {
            wp_send_json_error(['message' => __('Failed to reject review.', 'cosy-appointments')]);
        }
    }

    public function handle_delete_review(): void
    {
        if (!current_user_can('manage_options') && !current_user_can('manage_cosy_appointments')) {
            wp_send_json_error(['message' => __('Unauthorized access.', 'cosy-appointments')]);
        }

        $review_id = isset($_POST['review_id']) ? intval($_POST['review_id']) : 0;
        if (!$review_id) {
            wp_send_json_error(['message' => __('Invalid review ID.', 'cosy-appointments')]);
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'cosy_provider_reviews';

        $review = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $review_id));
        if (!$review) {
            wp_send_json_error(['message' => __('Review not found.', 'cosy-appointments')]);
        }

        $provider_id = $review->provider_id;
        $replies_table = $wpdb->prefix . 'cosy_review_replies';
        $wpdb->delete($replies_table, ['review_id' => $review_id], ['%d']);
        $deleted = $wpdb->delete($table_name, ['id' => $review_id], ['%d']);

        if ($deleted) {
            \Cosy\Appointments\Common\LogManager::log('reviews', 'DELETE_REVIEW', sprintf('Admin deleted Review #%d for Provider #%d (Customer: %s).', $review_id, $provider_id, $review->customer_name));

            $existing_alerts = get_user_meta($provider_id, 'cosy_review_audit_alerts', true) ?: [];
            $existing_alerts[] = [
                'review_id'   => $review_id,
                'customer'    => $review->customer_name,
                'message'     => sprintf(__('Admin removed a review by %s for profile moderation policy compliance.', 'cosy-appointments'), $review->customer_name),
                'deleted_at'  => current_time('mysql')
            ];
            update_user_meta($provider_id, 'cosy_review_audit_alerts', $existing_alerts);

            wp_send_json_success(['message' => __('Review deleted and audit logged.', 'cosy-appointments')]);
        } else {
            wp_send_json_error(['message' => __('Failed to delete review.', 'cosy-appointments')]);
        }
    }
}
