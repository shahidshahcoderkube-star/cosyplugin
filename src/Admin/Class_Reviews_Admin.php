<?php

namespace Cosy\Appointments\Admin;

use Cosy\Appointments\Loader;

class Class_Reviews_Admin
{
    use \Cosy\Appointments\Common\GlobalCommonFunctions;

    /**
     * REGISTERS REVIEWS MODERATION HOOKS & AJAX ENDPOINTS
     * 
     * USE CASE:
     * Called during plugin initialization to register AJAX handlers for review approval, rejection, and deletion.
     * 
     * HOW TO USE:
     * (new Class_Reviews_Admin())->register($loader);
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Attaches 'wp_ajax_cosy_admin_approve_review' to handle_approve_review callback.
     * 2. Attaches 'wp_ajax_cosy_admin_reject_review' to handle_reject_review callback.
     * 3. Attaches 'wp_ajax_cosy_admin_delete_review' to handle_delete_review callback.
     * 
     * @param Loader $loader Plugin loader instance.
     */
    public function register(Loader $loader): void
    {
        $loader->add_action('wp_ajax_cosy_admin_approve_review', $this, 'handle_approve_review');
        $loader->add_action('wp_ajax_cosy_admin_reject_review', $this, 'handle_reject_review');
        $loader->add_action('wp_ajax_cosy_admin_delete_review', $this, 'handle_delete_review');
    }

    /**
     * RENDERS ADMIN REVIEWS MODERATION TABLE
     * 
     * USE CASE:
     * Callback renderer for 'Reviews' admin page.
     * 
     * HOW TO USE:
     * Triggered when admin visits 'Reviews' submenu under 'CC Booking'.
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Extracts status and provider filter URL parameters.
     * 2. Queries review records from wp_cosy_provider_reviews database table.
     * 3. Fetches provider user lists for dropdown filter.
     * 4. Includes reviews-admin-template.php layout file.
     */
    public function render_reviews_page(): void
    {
        $status_filter   = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $provider_filter = isset($_GET['provider']) ? intval($_GET['provider']) : 0;
        $search_query    = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $paged           = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $per_page        = 20;
        $offset          = ($paged - 1) * $per_page;

        global $wpdb;
        $table_name = $wpdb->prefix . 'cosy_provider_reviews';

        $where = ["1=1"];
        if (!empty($status_filter)) {
            $where[] = $wpdb->prepare("status = %s", $status_filter);
        }
        if (!empty($provider_filter)) {
            $where[] = $wpdb->prepare("provider_id = %d", $provider_filter);
        }
        if (!empty($search_query)) {
            $search_like = '%' . $wpdb->esc_like($search_query) . '%';
            $where[] = $wpdb->prepare("(customer_name LIKE %s OR review LIKE %s OR provider_reply LIKE %s)", $search_like, $search_like, $search_like);
        }

        $where_sql     = implode(' AND ', $where);
        $total_reviews = intval($wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE $where_sql"));
        $total_pages   = max(1, ceil($total_reviews / $per_page));
        $reviews       = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE $where_sql ORDER BY id DESC LIMIT %d OFFSET %d", $per_page, $offset));

        $providers = get_users(['role' => 'provider']);

        include COSY_APPT_PATH . 'templates/admin/reviews-admin-template.php';
    }

    /**
     * AJAX Handler: Approve Customer Review.
     * 
     * USE CASE:
     * Triggered when an administrator clicks "Approve" on a pending review in Admin -> CC Booking -> Reviews.
     * 
     * WHAT IT DOES:
     * 1. Verifies admin security via verify_admin_ajax_request('cosy_admin_nonce', 'manage_cosy_appointments').
     * 2. Updates review status to 'approved' in wp_cosy_provider_reviews database table.
     * 3. Dispatches notification email to provider and logs action to LogManager.
     */
    public function handle_approve_review(): void
    {
        $this->verify_admin_ajax_request('cosy_admin_nonce', 'manage_cosy_appointments');

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
                $tpl = \Cosy\Appointments\Email\EmailTemplates::get_provider_review_approved_template(
                    $provider->display_name,
                    $review->customer_name,
                    intval($review->rating),
                    $review->review
                );
                cosy_send_html_email($provider->user_email, $tpl['subject'], $tpl['heading'], $tpl['content']);
            }

            wp_send_json_success(['message' => __('Review approved successfully.', 'cosy-appointments')]);
        } else {
            wp_send_json_error(['message' => __('Failed to approve review.', 'cosy-appointments')]);
        }
    }

    /**
     * AJAX Handler: Reject Customer Review.
     * 
     * USE CASE:
     * Triggered when an administrator clicks "Reject" on a review in Admin -> CC Booking -> Reviews.
     * 
     * WHAT IT DOES:
     * 1. Verifies admin security via verify_admin_ajax_request('cosy_admin_nonce', 'manage_cosy_appointments').
     * 2. Updates review status to 'rejected' in wp_cosy_provider_reviews table and logs action.
     */
    public function handle_reject_review(): void
    {
        $this->verify_admin_ajax_request('cosy_admin_nonce', 'manage_cosy_appointments');

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

    /**
     * AJAX Handler: Delete Customer Review.
     * 
     * USE CASE:
     * Triggered when an administrator clicks "Delete" on a review in Admin -> CC Booking -> Reviews.
     * 
     * WHAT IT DOES:
     * 1. Verifies admin security via verify_admin_ajax_request('cosy_admin_nonce', 'manage_cosy_appointments').
     * 2. Deletes review replies and the main review record permanently from MySQL table.
     * 3. Flushes directory transient cache and logs activity.
     */
    public function handle_delete_review(): void
    {
        $this->verify_admin_ajax_request('cosy_admin_nonce', 'manage_cosy_appointments');

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
