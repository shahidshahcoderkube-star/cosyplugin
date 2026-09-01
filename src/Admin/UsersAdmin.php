<?php

namespace Cosy\Appointments\Admin;

use Cosy\Appointments\Loader;
use Cosy\Appointments\Common\GlobalCommonFunctions;
use WP_User_Query;

/**
 * UsersAdmin
 *
 * Handles the admin Manage Users page — data queries, AJAX handlers,
 * and shared helper methods for appointments/ordinals/status.
 *
 * Templates are loaded from src/Admin/Backend/:
 *  - users-page.php                  → Main page layout
 *  - user-details-modal-content.php  → AJAX modal content
 *
 * JavaScript is in src/Admin/Assets/js/users-admin.js
 * CSS is in src/Admin/Assets/css/admin.css
 */
class UsersAdmin
{
    use GlobalCommonFunctions;

    /**
     * REGISTERS USER MANAGEMENT HOOKS & AJAX ENDPOINTS
     * 
     * USE CASE:
     * Called during plugin load sequence to hook admin user management actions and AJAX handlers.
     * 
     * HOW TO USE:
     * (new UsersAdmin())->register($loader);
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Attaches AJAX action handlers for user status updates, resending verification emails, fetching user details, and bulk deletion.
     * 2. Hooks script enqueue callback for admin user management scripts.
     * 3. Attaches cache invalidation callbacks to 'save_post_cosy_appointment' and 'before_delete_post'.
     * 
     * @param Loader $loader Plugin loader instance.
     */
    public function register(Loader $loader): void
    {
        // AJAX handler for updating user status
        $loader->add_action('wp_ajax_cosy_admin_update_user_status', $this, 'handle_ajax_update_user_status');

        // AJAX handler for resending verification email
        $loader->add_action('wp_ajax_cosy_admin_resend_verification', $this, 'handle_ajax_resend_verification');

        // AJAX handler for fetching full user details
        $loader->add_action('wp_ajax_cosy_admin_get_user_details', $this, 'handle_ajax_get_user_details');

        // AJAX handler for bulk deleting users
        $loader->add_action('wp_ajax_cosy_admin_delete_users', $this, 'handle_ajax_delete_users');

        // Enqueue dedicated users-admin JS on the users page
        $loader->add_action('admin_enqueue_scripts', $this, 'enqueue_users_admin_scripts');

        // Clear user appointments cache when appointment is updated or deleted
        $loader->add_action('save_post_cosy_appointment', $this, 'clear_user_appointments_cache', 10, 2);
        $loader->add_action('before_delete_post', $this, 'clear_user_appointments_cache_before_delete');
    }

    // =========================================================================
    // ENQUEUE SCRIPTS
    // =========================================================================

    /**
     * Enqueues the users-admin.js script only on the Users admin page.
     * Passes nonces and i18n strings via wp_localize_script.
     */
    public function enqueue_users_admin_scripts(string $hook): void
    {
        if ($hook !== 'cc-booking_page_cosy-users') {
            return;
        }

        wp_enqueue_script(
            'cosy-users-admin-script',
            COSY_APPT_URL . 'src/Admin/Assets/js/users-admin.js',
            ['jquery', 'cosy-admin-script'],
            COSY_APPT_VER . '-' . time(),
            true
        );

        wp_localize_script('cosy-users-admin-script', 'cosyUsersAdmin', [
            'nonces' => [
                'status'  => wp_create_nonce('cosy_admin_status_nonce'),
                'email'   => wp_create_nonce('cosy_admin_email_nonce'),
                'details' => wp_create_nonce('cosy_admin_details_nonce'),
                'delete'  => wp_create_nonce('cosy_admin_delete_nonce'),
            ],
            'i18n' => [
                'statusFailed'   => __('Failed to update status.', 'cosy-appointments'),
                'confirmResend'  => __('Are you sure you want to resend the verification email to this user?', 'cosy-appointments'),
                'sending'        => __('Sending...', 'cosy-appointments'),
                'resendEmail'    => __('Resend Email', 'cosy-appointments'),
                'emailSent'      => __('Verification email sent successfully.', 'cosy-appointments'),
                'emailFailed'    => __('Failed to send email.', 'cosy-appointments'),
                'loadingDetails' => __('Loading details...', 'cosy-appointments'),
                'detailsFailed'  => __('Failed to load details.', 'cosy-appointments'),
                'confirmDelete'  => __('Are you sure you want to delete the selected users? This action cannot be undone and will permanently remove their profile data.', 'cosy-appointments'),
                'deleting'       => __('Deleting...', 'cosy-appointments'),
                'deleteBtn'      => __('Delete', 'cosy-appointments'),
                'deleteFailed'   => __('Failed to delete users.', 'cosy-appointments'),
            ],
        ]);
    }

    // =========================================================================
    // SHARED HELPER METHODS (used by both table rows and modal content)
    // =========================================================================

    /**
     * Fetches all appointments for a user (provider or customer).
     * Results are cached via transients for 10 minutes.
     *
     * @param int    $user_id  The WordPress user ID.
     * @param string $role     'provider' or 'customer'.
     * @return array Array of appointment objects.
     */
    public function get_user_appointments(int $user_id, string $role): array
    {
        global $wpdb;
        $meta_key = ($role === 'provider') ? 'cosy_provider_id' : 'cosy_customer_id';
        $transient_key = 'cosy_user_appts_' . $user_id . '_' . $role;

        $appointments = get_transient($transient_key);
        if (false === $appointments) {
            $appointments = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT p.ID, 
                           (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = p.ID AND meta_key = 'cosy_service_name' LIMIT 1) as service_name,
                           (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = p.ID AND meta_key = 'cosy_start_date' LIMIT 1) as start_date,
                           (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = p.ID AND meta_key = 'cosy_slots_timeline' LIMIT 1) as slots_timeline,
                           (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = p.ID AND meta_key = 'cosy_booking_status' LIMIT 1) as booking_status,
                           (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = p.ID AND meta_key = 'cosy_total_payable' LIMIT 1) as total_payable
                    FROM {$wpdb->posts} p
                    JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                    WHERE p.post_type = 'cosy_appointment'
                      AND p.post_status = 'publish'
                      AND pm.meta_key = %s
                      AND pm.meta_value = %d
                    ORDER BY p.ID DESC",
                    $meta_key,
                    $user_id
                )
            );
            set_transient($transient_key, $appointments, 10 * MINUTE_IN_SECONDS);
        }

        return $appointments ?: [];
    }

    /**
     * Calculates ordinal booking numbers for each appointment (1st, 2nd, 3rd, etc.)
     * grouped by service name in chronological order.
     *
     * @param array $appointments Array of appointment objects (newest first).
     * @return array Associative array [appointment_ID => ordinal_number].
     */
    public function calculate_booking_ordinals(array $appointments): array
    {
        $chrono_appts = array_reverse($appointments);
        $service_counts = [];
        $ordinals = [];

        foreach ($chrono_appts as $appt) {
            $srv_name = $appt->service_name;
            if (!isset($service_counts[$srv_name])) {
                $service_counts[$srv_name] = 0;
            }
            $service_counts[$srv_name]++;
            $ordinals[$appt->ID] = $service_counts[$srv_name];
        }

        return $ordinals;
    }

    /**
     * Returns the status information for an appointment based on its date and booking_status.
     *
     * @param object $appt           Appointment object with booking_status and start_date.
     * @param bool   $include_extras  If true, includes 'slug' and 'bg' keys for modal cards.
     * @return array ['label' => string, 'color' => string, 'slug' => string, 'bg' => string]
     */
    public function get_appointment_status_info(object $appt, bool $include_extras = false): array
    {
        $status = !empty($appt->booking_status) ? $appt->booking_status : 'pending';

        if ($status === 'cancelled') {
            $result = [
                'label' => __('Cancelled', 'cosy-appointments'),
                'color' => '#991b1b',
                'slug'  => 'cancelled',
                'bg'    => '#fee2e2',
            ];
        } else {
            $appt_time = strtotime($appt->start_date);
            $today_time = strtotime('today');

            if ($appt_time < $today_time) {
                $result = [
                    'label' => __('Completed', 'cosy-appointments'),
                    'color' => '#166534',
                    'slug'  => 'completed',
                    'bg'    => '#dcfce7',
                ];
            } elseif ($appt_time === $today_time) {
                $result = [
                    'label' => __('In Progress', 'cosy-appointments'),
                    'color' => '#7c3aed',
                    'slug'  => 'in-progress',
                    'bg'    => '#f5f3ff',
                ];
            } else {
                $result = [
                    'label' => __('Upcoming', 'cosy-appointments'),
                    'color' => '#1e40af',
                    'slug'  => 'upcoming',
                    'bg'    => '#dbeafe',
                ];
            }
        }

        return $result;
    }

    /**
     * Generates a human-readable ordinal label like "1st Session" or "2nd Booking".
     *
     * @param int    $booking_num The ordinal number.
     * @param string $role        'provider' or 'customer'.
     * @return string
     */
    public function get_ordinal_label(int $booking_num, string $role): string
    {
        $ends = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];
        if (($booking_num % 100) >= 11 && ($booking_num % 100) <= 13) {
            $ordinal = $booking_num . 'th';
        } else {
            $ordinal = $booking_num . $ends[$booking_num % 10];
        }

        return ($role === 'provider')
            ? sprintf(__('%s Session', 'cosy-appointments'), $ordinal)
            : sprintf(__('%s Booking', 'cosy-appointments'), $ordinal);
    }

    /**
     * Fetches the list of services offered by a provider (from provider_services table).
     *
     * @param int $user_id Provider user ID.
     * @return array Array of service title strings.
     */
    public function get_provider_offered_services(int $user_id): array
    {
        global $wpdb;
        $services_table = $wpdb->prefix . 'provider_services';

        return $wpdb->get_col(
            $wpdb->prepare(
                "SELECT DISTINCT p.post_title 
                 FROM $services_table ps
                 JOIN {$wpdb->posts} p ON ps.service_id = p.ID
                 WHERE ps.provider_id = %d AND ps.checkbox_status = 'yes'",
                $user_id
            )
        ) ?: [];
    }

    // =========================================================================
    // RENDER: Users Page
    // =========================================================================

    /**
     * Renders the unified Users management page.
     * Loads the template from Backend/users-page.php
     */
    public function render_users_page(): void
    {
        if (!current_user_can('manage_options') && !current_user_can('manage_cosy_appointments')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'cosy-appointments'));
        }

        // Filters and Search
        $role_filter = isset($_GET['role']) ? sanitize_text_field($_GET['role']) : 'all';
        $service_filter = isset($_GET['service']) ? sanitize_text_field($_GET['service']) : '';
        $search_query = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $number = 20; // Users per page

        $args = [
            'orderby'      => 'registered',
            'order'        => 'DESC',
            'number'       => $number,
            'offset'       => ($paged - 1) * $number,
            'count_total'  => true,
        ];

        if ($role_filter === 'provider') {
            $args['role'] = 'provider';
        } elseif ($role_filter === 'customer') {
            $args['role'] = 'customer';
        } else {
            $args['role__in'] = ['provider', 'customer'];
        }

        if (!empty($search_query)) {
            $args['search'] = '*' . esc_attr($search_query) . '*';
            $args['search_columns'] = ['user_login', 'user_email', 'user_nicename', 'display_name'];
        }

        // Service Filter Logic
        $matching_user_ids = null;
        if (!empty($service_filter)) {
            global $wpdb;

            // 1. Get user IDs from appointments with this service
            $appt_user_ids = $wpdb->get_col($wpdb->prepare(
                "SELECT DISTINCT pm.meta_value 
                 FROM {$wpdb->postmeta} pm
                 JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                 WHERE p.post_type = 'cosy_appointment'
                   AND pm.meta_key IN ('cosy_provider_id', 'cosy_customer_id')
                   AND p.ID IN (
                       SELECT post_id 
                       FROM {$wpdb->postmeta} 
                       WHERE meta_key = 'cosy_service_name' AND meta_value = %s
                   )",
                $service_filter
            ));

            // 2. Get provider IDs offering this service
            $prov_table = $wpdb->prefix . 'provider_services';
            $provider_user_ids = $wpdb->get_col($wpdb->prepare(
                "SELECT DISTINCT ps.provider_id 
                 FROM $prov_table ps
                 JOIN {$wpdb->posts} p ON ps.service_id = p.ID
                 WHERE ps.checkbox_status = 'yes' AND p.post_title = %s",
                $service_filter
            ));

            $merged_ids = array_unique(array_merge(array_map('intval', $appt_user_ids), array_map('intval', $provider_user_ids)));
            $matching_user_ids = !empty($merged_ids) ? $merged_ids : [0];
        }

        if ($matching_user_ids !== null) {
            $args['include'] = $matching_user_ids;
        }

        $user_query = new WP_User_Query($args);
        $users = $user_query->get_results();
        $total_users = $user_query->get_total();
        $total_pages = ceil($total_users / $number);

        // Pass $this as $controller so the template can call helper methods
        $controller = $this;

        // Load the template
        include COSY_APPT_PATH . 'src/Admin/Backend/users-page.php';
    }

    // =========================================================================
    // AJAX HANDLERS
    // =========================================================================

    /**
     * AJAX handler: Updates user status.
     */
    public function handle_ajax_update_user_status(): void
    {
        check_ajax_referer('cosy_admin_status_nonce', 'security');

        if (!current_user_can('manage_options') && !current_user_can('manage_cosy_appointments')) {
            wp_send_json_error(__('Permission denied.', 'cosy-appointments'));
        }

        $user_id = intval($_POST['user_id'] ?? 0);
        $role    = sanitize_text_field($_POST['role'] ?? '');
        $status  = sanitize_text_field($_POST['status'] ?? '');

        if (!$user_id || !in_array($status, ['active', 'deactive'])) {
            wp_send_json_error(__('Invalid data provided.', 'cosy-appointments'));
        }

        if ($role === 'provider') {
            // For providers, we update cosy_provider_status (which controls profile visibility on the frontend)
            $old_status = get_user_meta($user_id, 'cosy_provider_status', true);
            if (empty($old_status)) {
                $old_status = 'active';
            }

            update_user_meta($user_id, 'cosy_provider_status', $status);
            $this->flush_provider_transients();

            // Send notification email to provider if status changed
            if ($old_status !== $status) {
                $user = get_userdata($user_id);
                if ($user) {
                    if ($status === 'active') {
                        $was_ever_activated = (bool) get_user_meta($user_id, 'cosy_was_ever_activated', true);
                        if ($was_ever_activated) {
                            // User was previously active, deactivated, and now re-activated
                            $tpl = \Cosy\Appointments\Email\EmailTemplates::get_provider_reactivated_template($user->display_name);
                        } else {
                            // First time activation by Admin
                            $tpl = \Cosy\Appointments\Email\EmailTemplates::get_provider_active_template($user->display_name);
                            update_user_meta($user_id, 'cosy_was_ever_activated', 1);
                        }
                    } else {
                        $tpl = \Cosy\Appointments\Email\EmailTemplates::get_provider_deactivated_template($user->display_name);
                    }
                    cosy_send_html_email($user->user_email, $tpl['subject'], $tpl['heading'], $tpl['content']);
                }
            }
        } else {
            // For customers, deactive status suspends them from logging in (saved inside account_status)
            $old_status = get_user_meta($user_id, 'account_status', true);
            if (empty($old_status)) {
                $old_status = 'active';
            }

            update_user_meta($user_id, 'account_status', $status);
        }

        $user = get_userdata($user_id);
        if ($user) {
            \Cosy\Appointments\Common\LogManager::log(
                'users',
                'user_status_updated',
                sprintf(__('Admin updated user "%s" (ID: %d, Role: %s) status from "%s" to "%s".', 'cosy-appointments'), $user->display_name ?: $user->user_login, $user_id, ucfirst($role), $old_status, $status)
            );
        }

        $msg = ($status === 'active')
            ? __('User status set to Active.', 'cosy-appointments')
            : __('User status set to Deactive.', 'cosy-appointments');

        wp_send_json_success($msg);
    }

    /**
     * AJAX handler: Resends verification email.
     */
    public function handle_ajax_resend_verification(): void
    {
        check_ajax_referer('cosy_admin_email_nonce', 'security');

        if (!current_user_can('manage_options') && !current_user_can('manage_cosy_appointments')) {
            wp_send_json_error(__('Permission denied.', 'cosy-appointments'));
        }

        $user_id = intval($_POST['user_id'] ?? 0);
        $role    = sanitize_text_field($_POST['role'] ?? '');

        if (!$user_id) {
            wp_send_json_error(__('Invalid user ID.', 'cosy-appointments'));
        }

        $sent = $this->send_verification_email($user_id, $role);

        if ($sent) {
            $user = get_userdata($user_id);
            if ($user) {
                \Cosy\Appointments\Common\LogManager::log(
                    'users',
                    'verification_resent',
                    sprintf(__('Admin resent verification/activation email to user "%s" (ID: %d).', 'cosy-appointments'), $user->display_name ?: $user->user_login, $user_id)
                );
            }
            wp_send_json_success(__('Verification email resent successfully.', 'cosy-appointments'));
        } else {
            wp_send_json_error(__('Failed to send verification email. Please check SMTP settings.', 'cosy-appointments'));
        }
    }

    /**
     * AJAX handler: Fetches full user details HTML for modal display.
     * Loads the template from Backend/user-details-modal-content.php
     */
    public function handle_ajax_get_user_details(): void
    {
        check_ajax_referer('cosy_admin_details_nonce', 'security');

        if (!current_user_can('manage_options') && !current_user_can('manage_cosy_appointments')) {
            wp_send_json_error(__('Permission denied.', 'cosy-appointments'));
        }

        $user_id = intval($_POST['user_id'] ?? 0);
        if (!$user_id) {
            wp_send_json_error(__('Invalid user ID.', 'cosy-appointments'));
        }

        $user = get_userdata($user_id);
        if (!$user) {
            wp_send_json_error(__('User not found.', 'cosy-appointments'));
        }

        $roles = (array) $user->roles;
        $role = in_array('provider', $roles) ? 'provider' : (in_array('customer', $roles) ? 'customer' : 'other');

        // Pass $this as $controller so the template can call helper methods
        $controller = $this;

        ob_start();
        include COSY_APPT_PATH . 'src/Admin/Backend/user-details-modal-content.php';
        $html = ob_get_clean();

        $title = sprintf(
            __('%1$s Details - %2$s', 'cosy-appointments'),
            $role === 'provider' ? __('Provider', 'cosy-appointments') : __('Customer', 'cosy-appointments'),
            $user->display_name
        );

        wp_send_json_success([
            'title' => $title,
            'html'  => $html
        ]);
    }

    /**
     * AJAX handler: Bulk deletes selected users.
     */
    public function handle_ajax_delete_users(): void
    {
        check_ajax_referer('cosy_admin_delete_nonce', 'security');

        if (!current_user_can('manage_options') && !current_user_can('manage_cosy_appointments')) {
            wp_send_json_error(__('Permission denied.', 'cosy-appointments'));
        }

        $user_ids = isset($_POST['user_ids']) ? array_map('intval', $_POST['user_ids']) : [];
        if (empty($user_ids)) {
            wp_send_json_error(__('No users selected.', 'cosy-appointments'));
        }

        $current_user_id = get_current_user_id();
        $deleted_count = 0;
        $errors = [];
        $deleted_user_infos = [];

        // Required for wp_delete_user() function
        require_once ABSPATH . 'wp-admin/includes/user.php';

        foreach ($user_ids as $id) {
            if ($id === $current_user_id) {
                $errors[] = __('You cannot delete your own account.', 'cosy-appointments');
                continue;
            }

            $user = get_userdata($id);
            if (!$user) {
                continue;
            }

            // Verify role is manageable by our plugin
            $roles = (array) $user->roles;
            if (!in_array('provider', $roles) && !in_array('customer', $roles)) {
                $errors[] = sprintf(__('User ID %d does not have a manageable role.', 'cosy-appointments'), $id);
                continue;
            }

            $user_display = $user->display_name ?: $user->user_login;
            $user_role = implode(', ', $roles);

            $deleted = wp_delete_user($id);
            if ($deleted) {
                $deleted_count++;
                $deleted_user_infos[] = sprintf('%s (ID: %d, Role: %s)', $user_display, $id, $user_role);
            } else {
                $errors[] = sprintf(__('Failed to delete user ID %d.', 'cosy-appointments'), $id);
            }
        }

        if ($deleted_count > 0) {
            \Cosy\Appointments\Common\LogManager::log(
                'users',
                'user_deleted',
                sprintf(__('Admin bulk deleted %d user(s): %s.', 'cosy-appointments'), $deleted_count, implode('; ', $deleted_user_infos))
            );
            $msg = sprintf(_n('Successfully deleted %d user.', 'Successfully deleted %d users.', $deleted_count, 'cosy-appointments'), $deleted_count);
            if (!empty($errors)) {
                $msg .= ' ' . implode(' ', $errors);
            }
            wp_send_json_success($msg);
        } else {
            wp_send_json_error(implode(' ', $errors) ?: __('Failed to delete selected users.', 'cosy-appointments'));
        }
    }

    // =========================================================================
    // CACHE MANAGEMENT
    // =========================================================================

    /**
     * Clears user appointments cache transients when a cosy_appointment post is updated/saved.
     */
    public function clear_user_appointments_cache(int $post_id, \WP_Post $post): void
    {
        $provider_id = get_post_meta($post_id, 'cosy_provider_id', true);
        $customer_id = get_post_meta($post_id, 'cosy_customer_id', true);
        if ($provider_id) {
            delete_transient('cosy_user_appts_' . $provider_id . '_provider');
        }
        if ($customer_id) {
            delete_transient('cosy_user_appts_' . $customer_id . '_customer');
        }
    }

    /**
     * Clears user appointments cache transients before deleting a cosy_appointment post.
     */
    public function clear_user_appointments_cache_before_delete(int $post_id): void
    {
        $post = get_post($post_id);
        if ($post && $post->post_type === 'cosy_appointment') {
            $this->clear_user_appointments_cache($post_id, $post);
        }
    }
}
