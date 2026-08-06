<?php

namespace Cosy\Appointments\Frontend;

use Cosy\Appointments\Forms\FormsData;
use Cosy\Appointments\Loader;
use Cosy\Appointments\Common\GlobalCommonFunctions;

class Frontend
{
    use GlobalCommonFunctions;

    /**
     * Initializes all frontend hooks and shortcodes.
     * This function is called when the plugin starts. It registers shortcodes, 
     * template redirects (for page security), and AJAX endpoints.
     */
    public function register(Loader $loader): void
    {
        /* Register shortcode and footer action*/
        $loader->add_action('init', $this, 'register_shortcode');
        $loader->add_action('init', $this, 'handle_worldpay_payment_response');
        $loader->add_action('wp_footer', $this, 'render_register_popup');
        $loader->add_action('template_redirect', $this, 'restrict_direct_page_access');
        $loader->add_action('after_setup_theme', $this, 'hide_admin_menu');
        $loader->add_action('admin_init', $this, 'redirect_provider_from_admin');
        $loader->add_filter('login_redirect', $this, 'custom_login_redirect', 10, 3);

        // FormsData handles its own registration in constructor
        new FormsData();

        $loader->add_filter('template_include', $this, 'provider_profile_dashboard_shortcode', 9999);

        // Register AJAX handlers for booking creation
        $this->register_ajax_handlers([
            'cosy_create_appointment_direct' => 'handle_create_appointment_direct',
            'cosy_create_worldpay_order'   => 'handle_create_worldpay_order',
            'cosy_update_booking_status'    => 'handle_update_booking_status',
            'cosy_get_booked_slots'         => 'handle_get_booked_slots',
            'filter_service_providers'      => 'handle_filter_service_providers'
        ], $this);
    }


    /**
     * Registers all the shortcodes used by the plugin.
     * Shortcodes allow admins to place functionality (like forms or dashboards) 
     * on any WordPress page by typing text like [cosy_appointments].
     */
    public function register_shortcode(): void
    {
        add_shortcode('cosy_appointments', [$this, 'appointments_shortcode']);
        add_shortcode('cosy_customer_registration', [$this, 'customer_registration_shortcode']);
        add_shortcode('cosy_provider_registration', [$this, 'provider_registration_shortcode']);
        add_shortcode('customer_profile', [$this, 'customer_profile_shortcode']);
        add_shortcode('cosy_verify_provider', [$this, 'provider_verify_shortcode']);
        add_shortcode('cosy_login_form', [$this, 'login_form']);
        add_shortcode('cosy_customer_order', [$this, 'customer_order_page']);
        add_shortcode('cosy_service_provider_list', [$this, 'service_provider_shortcode']);
        add_shortcode('cosy_checkout', [$this, 'checkout_page']);
        add_shortcode('cosy_leave_review', [$this, 'leave_review_page']);
    }


    /**
     * This function renders the popup for choosing member type
     * The register button link is created in the admin menu settings as link in to the menu.
     */

    public function render_register_popup(): void
    {
        ob_start();
        include COSY_APPT_PATH . 'templates/popup-template.php';
        echo ob_get_clean();
    }


    /**
     * Renders the main appointment booking calendar.
     * Used by shortcode: [cosy_appointments]
     */
    public function appointments_shortcode(): string
    {
        ob_start();
        include COSY_APPT_PATH . 'templates/appointments-template.php';
        return ob_get_clean();
    }


    /**
     * Renders the registration form for new customers.
     * Used by shortcode: [cosy_customer_registration]
     */
    public function customer_registration_shortcode(): string
    {
        ob_start();
        include COSY_APPT_PATH . 'templates/customer-registration-template.php';
        return ob_get_clean();
    }


    /**
     * Renders the registration form for new service providers.
     * Used by shortcode: [cosy_provider_registration]
     */
    public function provider_registration_shortcode(): string
    {
        ob_start();
        include COSY_APPT_PATH . 'templates/provider-registration-template.php';
        return ob_get_clean();
    }


    /**
     * Renders the customer's account dashboard and profile page.
     * Used by shortcode: [customer_profile]
     */
    public function customer_profile_shortcode(): string
    {
        ob_start();
        include COSY_APPT_PATH . 'templates/customer-profile-template.php';
        return ob_get_clean();
    }


    /**
     * Renders the login form for both customers and providers.
     * Used by shortcode: [cosy_login_form]
     */
    public function login_form(): string
    {

        ob_start();
        include COSY_APPT_PATH . 'templates/login-template.php';
        return ob_get_clean();
    }


    /**
     * Renders the customer order history page.
     * Used by shortcode: [cosy_customer_order]
     */
    public function customer_order_page(): string
    {
        ob_start();
        include COSY_APPT_PATH . 'templates/customer-order-template.php';
        return ob_get_clean();
    }


    /**
     * Renders the checkout page.
     * Used by shortcode: [cosy_checkout]
     */
    public function checkout_page(): string
    {
        ob_start();
        include COSY_APPT_PATH . 'templates/checkout-template.php';
        return ob_get_clean();
    }

    /**
     * Renders the token-based leave review page.
     * Used by shortcode: [cosy_leave_review]
     */
    public function leave_review_page(): string
    {
        ob_start();
        include COSY_APPT_PATH . 'templates/leave-review-template.php';
        return ob_get_clean();
    }


    /**
     * Renders the identity verification form for service providers.
     * Used by shortcode: [cosy_verify_provider]
     */
    public function provider_verify_shortcode(): string
    {
        ob_start();
        include COSY_APPT_PATH . 'templates/provider/provider-verify.php';
        return ob_get_clean();
    }


    public function service_provider_shortcode(): string
    {
        ob_start();
        include COSY_APPT_PATH . 'templates/service-provider-template.php';
        return ob_get_clean();
    }

    /**
     * Custom template redirect for the Provider's Public Profile page.
     * If a user clicks to view a provider's profile, it loads a custom PHP template 
     * instead of the default WordPress author page.
     */
    public function provider_profile_dashboard_shortcode($template): string
    {
        if (is_author()) {
            $custom_template = COSY_APPT_PATH . 'templates/provider-profile-template.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        return $template;
    }
    /**
     * Hides the top black WordPress Admin Bar for specific roles.
     * We don't want regular customers or providers to see the WordPress backend bar 
     * when they are browsing the frontend website.
     */
    public function hide_admin_menu()
    {

        if (is_user_logged_in()) {
            $user = wp_get_current_user();

            // Roles for which the admin bar should be hidden
            $hide_roles = array('customer', 'provider');

            if (array_intersect($hide_roles, (array) $user->roles)) {
                show_admin_bar(false);
            }
        }
    }


    /**
     * Prevents users from accessing specific private pages if they are not logged in.
     * If a guest tries to visit the dashboard, checkout, or profile pages, 
     * they are automatically redirected to the login page.
     */
    public function restrict_direct_page_access()
    {
        // Get target page IDs dynamically
        $login_id                 = cosy_get_page_id('login');
        $user_reg_id              = cosy_get_page_id('user-registration');
        $prov_reg_id              = cosy_get_page_id('provider-registration');
        $appointments_id          = cosy_get_page_id('appointments');
        $orders_id                = cosy_get_page_id('orders');
        $customer_order_id        = cosy_get_page_id('customer-order');
        $customer_profile_id      = cosy_get_page_id('customer-profile');
        $provider_dashboard_id    = cosy_get_page_id('provider-dashboard');
        $provider_verify_id       = cosy_get_page_id('provider-verify');
        $checkout_id              = cosy_get_page_id('cosy-checkout');

        // Pages that require login
        $restricted_ids = array_filter([
            $appointments_id,
            $orders_id,
            $customer_order_id,
            $customer_profile_id,
            $provider_dashboard_id,
            $checkout_id
        ]);

        if (is_page($restricted_ids) && !is_user_logged_in()) {
            wp_safe_redirect(cosy_get_page_url('login'));
            exit;
        }

        // Additional check for logged-in users
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            $roles = (array) $user->roles;

            // Redirect logged-in users away from login/registration pages
            $auth_blocked_ids = array_filter([$login_id, $user_reg_id, $prov_reg_id]);
            if (is_page($auth_blocked_ids)) {
                if (in_array('provider', $roles)) {
                    wp_safe_redirect(cosy_get_page_url('provider-dashboard'));
                    exit;
                } else {
                    wp_safe_redirect(cosy_get_page_url('customer-profile'));
                    exit;
                }
            }

            // Block providers from customer pages
            $blocked_for_provider = array_filter([
                $customer_order_id,
                $customer_profile_id,
                $appointments_id,
                $orders_id,
                $checkout_id
            ]);
            if (in_array('provider', $roles) && is_page($blocked_for_provider)) {
                wp_safe_redirect(cosy_get_page_url('provider-dashboard'));
                exit;
            }

            // Block customers from provider pages
            $blocked_for_customer = array_filter([$provider_dashboard_id, $provider_verify_id]);
            if (in_array('customer', $roles, true) && is_page($blocked_for_customer)) {
                wp_safe_redirect(cosy_get_page_url('customer-profile'));
                exit;
            }
        }
    }

    /**
     * Redirects service providers attempting to access WP Admin (/wp-admin/)
     * to their provider dashboard profile tab (e.g. /provider-dashboard/#profile).
     */
    public function redirect_provider_from_admin(): void
    {
        // Allow AJAX and REST API requests to process normally
        if (wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)) {
            return;
        }

        if (is_user_logged_in()) {
            $user  = wp_get_current_user();
            $roles = (array) $user->roles;

            // Redirect non-admin providers from WP Admin to provider dashboard profile
            if (in_array('provider', $roles, true) && !current_user_can('manage_options')) {
                $target_url = rtrim(cosy_get_page_url('provider-dashboard'), '/') . '/#profile';
                wp_redirect($target_url);
                exit;
            }
        }
    }

    /**
     * Filters login redirect URL for providers to send them directly to provider-dashboard/#profile.
     */
    public function custom_login_redirect(string $redirect_to, string $request, $user)
    {
        if ($user instanceof \WP_User && isset($user->roles) && is_array($user->roles)) {
            if (in_array('provider', $user->roles, true) && !user_can($user, 'manage_options')) {
                return rtrim(cosy_get_page_url('provider-dashboard'), '/') . '/#profile';
            }
        }
        return $redirect_to;
    }



    /**
     * handle_update_booking_status
     * 
     * AJAX handler to process appointment status changes.
     */
    public function handle_update_booking_status(): void
    {
        check_ajax_referer('cosy_dashboard_nonce', 'nonce');
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'User must be logged in.']);
        }

        $order_id   = isset($_POST['order_id']) ? intval($_POST['order_id']) : (isset($_POST['appointment_id']) ? intval($_POST['appointment_id']) : 0);
        $new_status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';

        $allowed_statuses = ['confirmed', 'completed', 'cancelled'];
        if (empty($order_id) || !in_array($new_status, $allowed_statuses)) {
            wp_send_json_error(['message' => 'Invalid parameters.']);
        }

        // Validate that the logged in user is the provider or admin
        $current_user    = wp_get_current_user();
        $current_user_id = $current_user->ID;
        $provider_id     = intval(get_post_meta($order_id, 'cosy_provider_id', true));

        if ($provider_id !== $current_user_id && !current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized action.']);
        }

        // Grab booking details for the log BEFORE updating
        $customer_name  = get_post_meta($order_id, 'cosy_customer_name', true);
        $customer_email = get_post_meta($order_id, 'cosy_customer_email', true);
        $service_name   = get_post_meta($order_id, 'cosy_service_name', true);
        $provider_name  = get_post_meta($order_id, 'cosy_provider_name', true);
        $start_date     = get_post_meta($order_id, 'cosy_start_date', true);
        $end_date       = get_post_meta($order_id, 'cosy_end_date', true);
        $total_payable  = get_post_meta($order_id, 'cosy_total_payable', true);

        // Update the status
        update_post_meta($order_id, 'cosy_booking_status', $new_status);

        // Build action label for the log
        $action_map = [
            'confirmed' => 'order_confirmed',
            'completed' => 'order_completed',
            'cancelled' => 'order_cancelled',
        ];
        $action_label = $action_map[$new_status];

        $currency_sym = cosy_get_currency_symbol();
        // Build a rich log description
        $log_description = sprintf(
            'ORDER %s | Order #%d | Customer: %s (%s) | Service: %s | Provider: %s | Dates: %s to %s | Amount: ' . $currency_sym . '%s | Updated by: %s',
            strtoupper($new_status),
            $order_id,
            $customer_name ?: 'N/A',
            $customer_email ?: 'N/A',
            $service_name ?: 'N/A',
            $provider_name ?: $current_user->display_name,
            $start_date ?: 'N/A',
            $end_date ?: 'N/A',
            $total_payable ?: '0.00',
            $current_user->display_name
        );

        \Cosy\Appointments\Common\LogManager::log(
            'orders',
            $action_label,
            $log_description,
            $current_user_id
        );

        // Send email notification to customer & admin on status update (Confirmed/Completed/Cancelled)
        $admin_email = get_option('admin_email');
        $slots_timeline = get_post_meta($order_id, 'cosy_slots_timeline', true);
        $email_payload = [
            'order_id'       => $order_id,
            'customer_name'  => $customer_name ?: 'Customer',
            'customer_email' => $customer_email,
            'provider_name'  => $provider_name ?: $current_user->display_name,
            'service_title'  => $service_name ?: 'Parent Conversation',
            'start_date'     => $start_date ?: '',
            'slots_timeline' => $slots_timeline ?: '',
            'status'         => $new_status,
        ];

        if ($new_status === 'cancelled') {
            $tpl = \Cosy\Appointments\Common\EmailTemplates::get_booking_cancelled_customer_template($email_payload);
            if (!empty($customer_email)) {
                cosy_send_html_email($customer_email, $tpl['subject'], $tpl['heading'], $tpl['content']);
            }
            if (!empty($admin_email)) {
                $admin_subject = sprintf(__('[ADMIN ALERT] Order #%s Cancelled by Provider (%s)', 'cosy-appointments'), $order_id, $provider_name ?: 'Provider');
                cosy_send_html_email($admin_email, $admin_subject, $tpl['heading'], $tpl['content']);
            }
        } else {
            $tpl = \Cosy\Appointments\Common\EmailTemplates::get_booking_status_update_customer_template($email_payload);
            if (!empty($customer_email)) {
                cosy_send_html_email($customer_email, $tpl['subject'], $tpl['heading'], $tpl['content']);
            }
            if (!empty($admin_email)) {
                $admin_subject = sprintf(__('Provider Order #%s Status Updated to %s', 'cosy-appointments'), $order_id, ucfirst($new_status));
                cosy_send_html_email($admin_email, $admin_subject, $tpl['heading'], $tpl['content']);
            }
        }

        // Send Review Invite Email when order is marked COMPLETED
        if ($new_status === 'completed' && !empty($customer_email)) {
            global $wpdb;
            $tokens_table = $wpdb->prefix . 'cosy_review_tokens';
            $customer_user_id = intval(get_post_meta($order_id, 'cosy_customer_id', true));
            if (empty($customer_user_id)) {
                $appt_post = get_post($order_id);
                $customer_user_id = $appt_post ? intval($appt_post->post_author) : 0;
            }

            // Only send if no unused token already exists for this order
            $existing_token = $wpdb->get_var($wpdb->prepare(
                "SELECT token FROM $tokens_table WHERE order_id = %d AND used = 0 LIMIT 1",
                $order_id
            ));

            if (empty($existing_token) && $customer_user_id > 0) {
                $review_token = bin2hex(random_bytes(32));
                $wpdb->insert($tokens_table, [
                    'token'          => $review_token,
                    'order_id'       => $order_id,
                    'provider_id'    => $provider_id,
                    'customer_id'    => $customer_user_id,
                    'customer_email' => $customer_email,
                    'used'           => 0,
                    'created_at'     => current_time('mysql'),
                ], ['%s', '%d', '%d', '%d', '%s', '%d', '%s']);

                $review_page_url = add_query_arg('token', $review_token, cosy_get_page_url('cosy-leave-review'));
                $review_tpl = \Cosy\Appointments\Common\EmailTemplates::get_review_invite_template([
                    'customer_name' => $customer_name ?: 'Customer',
                    'provider_name' => $provider_name ?: $current_user->display_name,
                    'service_title' => $service_name ?: 'Parent Conversation',
                    'review_url'    => $review_page_url,
                ]);
                cosy_send_html_email($customer_email, $review_tpl['subject'], $review_tpl['heading'], $review_tpl['content']);
            }
        }

        wp_send_json_success(['message' => 'Status updated successfully to ' . ucfirst($new_status)]);
    }

    /**
     * handle_get_booked_slots
     * 
     * Retrieves already booked time slots for a given provider on a specific date.
     */
    public function handle_get_booked_slots(): void
    {
        $provider_id = isset($_POST['provider_id']) ? intval($_POST['provider_id']) : 0;
        $date_str    = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '';

        if (empty($provider_id) || empty($date_str)) {
            wp_send_json_error(['message' => 'Missing provider or date parameter.']);
        }

        // Normalize target date to timestamp or YYYY-MM-DD
        $target_time = strtotime($date_str);
        $target_formatted = $target_time ? date('Y-m-d', $target_time) : $date_str;

        $args = [
            'post_type'      => 'cosy_appointment',
            'posts_per_page' => -1,
            'post_status'    => 'publish', // ONLY Paid & Confirmed appointments block slots!
            'meta_query'     => [
                'relation' => 'AND',
                [
                    'key'     => 'cosy_provider_id',
                    'value'   => $provider_id,
                    'compare' => '='
                ],
                [
                    'key'     => 'cosy_payment_status',
                    'value'   => 'Paid',
                    'compare' => '='
                ],
                [
                    'key'     => 'cosy_booking_status',
                    'value'   => 'confirmed',
                    'compare' => '='
                ]
            ]
        ];

        $query = new \WP_Query($args);
        $booked_slots = [];

        if ($query->have_posts()) {
            foreach ($query->posts as $appt) {

                $slots_meta = get_post_meta($appt->ID, 'cosy_slots', true);
                if (!empty($slots_meta)) {
                    $decoded = html_entity_decode($slots_meta);
                    $slots = json_decode($decoded, true);
                    if (!is_array($slots)) {
                        $slots = json_decode($slots_meta, true);
                    }

                    if (is_array($slots)) {
                        foreach ($slots as $k => $v) {
                            // Case 1: Key-Value Dictionary {"05-08-2026": ["10:00 AM", "10:10 AM"]}
                            if (is_array($v) && !isset($v['date'])) {
                                $k_time = strtotime($k);
                                $k_formatted = $k_time ? date('Y-m-d', $k_time) : $k;

                                if ($k === $date_str || $k_formatted === $target_formatted) {
                                    foreach ($v as $time_val) {
                                        $booked_slots[] = $time_val;
                                    }
                                }
                            }
                            // Case 2: Array of objects [{"date": "...", "time": "..."}]
                            elseif (is_array($v) || is_object($v)) {
                                $slot_obj = (array) $v;
                                if (isset($slot_obj['date'])) {
                                    $s_time = strtotime($slot_obj['date']);
                                    $s_formatted = $s_time ? date('Y-m-d', $s_time) : $slot_obj['date'];
                                    if ($slot_obj['date'] === $date_str || $s_formatted === $target_formatted) {
                                        if (isset($slot_obj['time'])) {
                                            $booked_slots[] = $slot_obj['time'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        wp_send_json_success(array_values(array_unique($booked_slots)));
    }

    /**
     * handle_create_appointment_direct
     * 
     * Backend AJAX handler to directly process and confirm appointment booking.
     */
    public function handle_create_appointment_direct(): void
    {
        check_ajax_referer('cosy_booking_nonce', 'nonce');
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'User must be logged in to book services.']);
        }

        $current_user = wp_get_current_user();
        if (!in_array('customer', (array) $current_user->roles)) {
            wp_send_json_error(['message' => 'Only customers are authorized to book appointments.']);
        }

        // Retrieve POST data
        $service_id         = !empty($_POST['serviceId']) ? intval($_POST['serviceId']) : 1;
        $service            = !empty($_POST['service']) ? sanitize_text_field($_POST['service']) : 'Parent Conversation';
        $provider_id        = isset($_POST['providerId']) ? intval($_POST['providerId']) : 0;
        $provider_name      = isset($_POST['providerName']) ? sanitize_text_field($_POST['providerName']) : '';
        $start_date         = isset($_POST['startDate']) ? sanitize_text_field($_POST['startDate']) : '';
        $end_date           = isset($_POST['endDate']) ? sanitize_text_field($_POST['endDate']) : '';
        $weekly_booking     = isset($_POST['weeklyBooking']) ? sanitize_text_field($_POST['weeklyBooking']) : '';
        $number_of_weeks    = isset($_POST['numberOfWeeks']) ? intval($_POST['numberOfWeeks']) : 1;
        $number_of_bookings = isset($_POST['numberOfBookings']) ? intval($_POST['numberOfBookings']) : 1;
        $service_cost       = isset($_POST['serviceCost']) ? sanitize_text_field($_POST['serviceCost']) : '0.00';
        $service_fee        = isset($_POST['serviceFee']) ? sanitize_text_field($_POST['serviceFee']) : '0.00';
        $total_payable      = isset($_POST['totalPayable']) ? sanitize_text_field($_POST['totalPayable']) : '0.00';
        $slots_json         = isset($_POST['slots']) ? wp_unslash($_POST['slots']) : '';
        $week_days          = isset($_POST['weekDays']) ? sanitize_text_field($_POST['weekDays']) : '';
        $slots_timeline     = isset($_POST['slotsTimeline']) ? sanitize_text_field($_POST['slotsTimeline']) : '';
        $is_gift            = !empty($_POST['isGift']) ? 1 : 0;
        $recipient_name     = isset($_POST['recipientName']) ? sanitize_text_field($_POST['recipientName']) : '';
        $recipient_email    = isset($_POST['recipientEmail']) ? sanitize_email($_POST['recipientEmail']) : '';

        if (empty($end_date) && !empty($start_date)) {
            $s_time = strtotime($start_date);
            if ($s_time) {
                $num_w = max(1, $number_of_weeks);
                $e_time = strtotime("+{$num_w} weeks -1 day", $s_time);
                $end_date = date('d-m-Y', $e_time);
            }
        }

        if (empty($provider_id)) {
            wp_send_json_error(['message' => 'Missing required provider details.']);
        }

        // Create appointment post with 'publish' status (Confirmed)
        $appointment_title = sprintf(
            '%s booked %s by %s',
            $current_user->display_name,
            $service,
            $provider_name
        );

        $order_id = wp_insert_post([
            'post_title'   => $appointment_title,
            'post_type'    => 'cosy_appointment',
            'post_status'  => 'publish',
            'post_author'  => $current_user->ID
        ]);

        if (is_wp_error($order_id)) {
            wp_send_json_error(['message' => 'Failed to create order: ' . $order_id->get_error_message()]);
        }

        // Save metadata
        $meta_data = [
            'cosy_service_id'         => $service_id,
            'cosy_service_name'       => $service,
            'cosy_provider_id'        => $provider_id,
            'cosy_provider_name'      => $provider_name,
            'cosy_customer_id'        => $current_user->ID,
            'cosy_customer_name'      => $current_user->display_name,
            'cosy_customer_email'     => $current_user->user_email,
            'cosy_start_date'         => $start_date,
            'cosy_end_date'           => $end_date,
            'cosy_weekly_booking'     => $weekly_booking,
            'cosy_number_of_weeks'    => $number_of_weeks,
            'cosy_number_of_bookings' => $number_of_bookings,
            'cosy_service_cost'       => $service_cost,
            'cosy_service_fee'        => $service_fee,
            'cosy_total_payable'      => $total_payable,
            'cosy_slots'              => sanitize_textarea_field($slots_json),
            'cosy_week_days'          => $week_days,
            'cosy_slots_timeline'     => $slots_timeline,
            'cosy_is_gift'            => $is_gift,
            'cosy_recipient_name'     => $recipient_name,
            'cosy_recipient_email'    => $recipient_email,
            'cosy_payment_status'     => 'Paid',
            'cosy_booking_status'     => 'confirmed',
        ];

        foreach ($meta_data as $key => $value) {
            update_post_meta($order_id, $key, $value);
        }

        // Send confirmation emails to customer & provider
        $this->send_booking_emails($order_id);

        $redirect_url = add_query_arg([
            'booking_success' => 'true',
            'order_id'        => $order_id
        ], cosy_get_page_url('customer-profile'));

        wp_send_json_success([
            'message' => __('Appointment booked successfully!', 'cosy-appointments'),
            'url'     => $redirect_url
        ]);
    }

    /**
     * handle_create_worldpay_order
     * 
     * Backend AJAX handler to process appointment booking via WorldPay Gateway.
     */
    public function handle_create_worldpay_order(): void
    {
        check_ajax_referer('cosy_booking_nonce', 'nonce');
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'User must be logged in to book services.']);
        }

        $current_user = wp_get_current_user();
        if (!in_array('customer', (array) $current_user->roles)) {
            wp_send_json_error(['message' => 'Only customers are authorized to book appointments.']);
        }

        // Retrieve POST data
        $service_id         = !empty($_POST['serviceId']) ? intval($_POST['serviceId']) : 1;
        $service            = !empty($_POST['service']) ? sanitize_text_field($_POST['service']) : 'Parent Conversation';
        $provider_id        = isset($_POST['providerId']) ? intval($_POST['providerId']) : 0;
        $provider_name      = isset($_POST['providerName']) ? sanitize_text_field($_POST['providerName']) : '';
        $start_date         = isset($_POST['startDate']) ? sanitize_text_field($_POST['startDate']) : '';
        $end_date           = isset($_POST['endDate']) ? sanitize_text_field($_POST['endDate']) : '';
        $weekly_booking     = isset($_POST['weeklyBooking']) ? sanitize_text_field($_POST['weeklyBooking']) : '';
        $number_of_weeks    = isset($_POST['numberOfWeeks']) ? intval($_POST['numberOfWeeks']) : 1;
        $number_of_bookings = isset($_POST['numberOfBookings']) ? intval($_POST['numberOfBookings']) : 1;
        $service_cost       = isset($_POST['serviceCost']) ? sanitize_text_field($_POST['serviceCost']) : '0.00';
        $service_fee        = isset($_POST['serviceFee']) ? sanitize_text_field($_POST['serviceFee']) : '0.00';
        $total_payable      = isset($_POST['totalPayable']) ? sanitize_text_field($_POST['totalPayable']) : '0.00';
        $slots_json         = isset($_POST['slots']) ? wp_unslash($_POST['slots']) : '';
        $week_days          = isset($_POST['weekDays']) ? sanitize_text_field($_POST['weekDays']) : '';
        $slots_timeline     = isset($_POST['slotsTimeline']) ? sanitize_text_field($_POST['slotsTimeline']) : '';
        $is_gift            = !empty($_POST['isGift']) ? 1 : 0;
        $recipient_name     = isset($_POST['recipientName']) ? sanitize_text_field($_POST['recipientName']) : '';
        $recipient_email    = isset($_POST['recipientEmail']) ? sanitize_email($_POST['recipientEmail']) : '';

        if (empty($end_date) && !empty($start_date)) {
            $s_time = strtotime($start_date);
            if ($s_time) {
                $num_w = max(1, $number_of_weeks);
                $e_time = strtotime("+{$num_w} weeks -1 day", $s_time);
                $end_date = date('d-m-Y', $e_time);
            }
        }

        if (empty($provider_id)) {
            $this->cosy_payment_log("WorldPay Order Creation FAILED: Missing provider details.", $_POST);
            wp_send_json_error(['message' => 'Missing required provider details.']);
        }

        // Create appointment post with 'draft' status (Pending Payment)
        $appointment_title = sprintf(
            '%s booked %s by %s (WorldPay Pending)',
            $current_user->display_name,
            $service,
            $provider_name
        );

        $order_id = wp_insert_post([
            'post_title'   => $appointment_title,
            'post_type'    => 'cosy_appointment',
            'post_status'  => 'draft', // Draft until payment is verified
            'post_author'  => $current_user->ID
        ]);

        if (is_wp_error($order_id)) {
            wp_send_json_error(['message' => 'Failed to create order: ' . $order_id->get_error_message()]);
        }

        // Save metadata as Pending
        $meta_data = [
            'cosy_service_id'         => $service_id,
            'cosy_service_name'       => $service,
            'cosy_provider_id'        => $provider_id,
            'cosy_provider_name'      => $provider_name,
            'cosy_customer_id'        => $current_user->ID,
            'cosy_customer_name'      => $current_user->display_name,
            'cosy_customer_email'     => $current_user->user_email,
            'cosy_start_date'         => $start_date,
            'cosy_end_date'           => $end_date,
            'cosy_weekly_booking'     => $weekly_booking,
            'cosy_number_of_weeks'    => $number_of_weeks,
            'cosy_number_of_bookings' => $number_of_bookings,
            'cosy_service_cost'       => $service_cost,
            'cosy_service_fee'        => $service_fee,
            'cosy_total_payable'      => $total_payable,
            'cosy_slots'              => sanitize_textarea_field($slots_json),
            'cosy_week_days'          => $week_days,
            'cosy_slots_timeline'     => $slots_timeline,
            'cosy_is_gift'            => $is_gift,
            'cosy_recipient_name'     => $recipient_name,
            'cosy_recipient_email'    => $recipient_email,
            'cosy_payment_status'     => 'Pending',
            'cosy_booking_status'     => 'pending',
            'cosy_payment_gateway'    => 'worldpay',
        ];

        foreach ($meta_data as $key => $value) {
            update_post_meta($order_id, $key, $value);
        }

        // Execute WorldPay Order Creation via WorldPay Hosted Payment Page (Approach 1: /wcc/purchase)
        $installation_id     = get_option('cosy_worldpay_installation_id');
        $worldpay_token      = get_option('cosy_worldpay_token');
        $worldpay_client_key = get_option('cosy_worldpay_client_key');
        $is_test_mode        = get_option('cosy_worldpay_test_mode');

        if (empty($installation_id) && empty($worldpay_token) && empty($worldpay_client_key)) {
            $this->cosy_payment_log("WorldPay Order Creation FAILED: No WorldPay Installation ID configured in settings.", $_POST);
            wp_send_json_error(['message' => __('WorldPay is not configured. Please enter your WorldPay Installation ID in Admin Payment Settings.', 'cosy-appointments')]);
        }

        $inst_id = !empty($installation_id) ? $installation_id : (!empty($worldpay_client_key) ? $worldpay_client_key : $worldpay_token);
        $base_gateway_url = !empty($is_test_mode) 
            ? 'https://secure-test.worldpay.com/wcc/purchase' 
            : 'https://secure.worldpay.com/wcc/purchase';

        $callback_url = add_query_arg([
            'booking_success' => 'true',
            'order_id'        => $order_id
        ], cosy_get_page_url('customer-profile'));

        // Construct WorldPay Hosted Payment Page URL (/wcc/purchase)
        $hosted_url = add_query_arg([
            'instId'      => $inst_id,
            'cartId'      => "COSY-$order_id",
            'amount'      => number_format(floatval($total_payable), 2, '.', ''),
            'currency'    => cosy_get_currency_code(),
            'desc'        => "CosyChats Appointment Booking #$order_id",
            'testMode'    => !empty($is_test_mode) ? '100' : '0',
            'MC_orderId'  => $order_id,
            'MC_callback' => urlencode($callback_url)
        ], $base_gateway_url);

        $this->cosy_payment_log("WorldPay Hosted Redirect URL generated for Order #$order_id (Draft Pending Payment):", $hosted_url);

        // DO NOT SEND CONFIRMATION EMAILS HERE! Emails are sent only AFTER WorldPay confirms payment.

        wp_send_json_success([
            'message' => __('Redirecting to WorldPay Hosted Payment Gateway...', 'cosy-appointments'),
            'url'     => $hosted_url
        ]);
    }

    /**
     * handle_worldpay_payment_response
     * 
     * Handles payment verification callback when customer returns from WorldPay.
     * Only converts order from draft to publish and sends emails IF payment was authorized (transStatus = Y).
     */
    public function handle_worldpay_payment_response(): void
    {
        $order_id = 0;
        if (isset($_REQUEST['MC_orderId'])) {
            $order_id = intval($_REQUEST['MC_orderId']);
        } elseif (isset($_REQUEST['order_id'])) {
            $order_id = intval($_REQUEST['order_id']);
        } elseif (isset($_REQUEST['cartId']) && str_starts_with($_REQUEST['cartId'], 'COSY-')) {
            $order_id = intval(str_replace('COSY-', '', $_REQUEST['cartId']));
        }

        $trans_status = isset($_REQUEST['transStatus']) ? sanitize_text_field($_REQUEST['transStatus']) : '';

        if ($order_id > 0) {
            $appt = get_post($order_id);
            if ($appt && $appt->post_type === 'cosy_appointment') {
                // WorldPay Payment SUCCESS check: ONLY when transStatus is 'Y' (Authorised) or 'SUCCESS'
                if ($trans_status === 'Y' || $trans_status === 'SUCCESS') {
                    if ($appt->post_status !== 'publish') {
                        wp_update_post([
                            'ID'          => $order_id,
                            'post_title'  => str_replace('(WorldPay Pending)', '(WorldPay Paid)', $appt->post_title),
                            'post_status' => 'publish'
                        ]);
                        update_post_meta($order_id, 'cosy_payment_status', 'Paid');
                        update_post_meta($order_id, 'cosy_booking_status', 'confirmed');

                        // Send confirmation emails ONLY NOW
                        $this->send_booking_emails($order_id);

                        \Cosy\Appointments\Common\LogManager::log(
                            'orders',
                            'worldpay_payment_success',
                            "WorldPay Payment SUCCESS for Order #$order_id (Amount Paid).",
                            get_current_user_id()
                        );
                    }
                } elseif ($trans_status === 'C' || $trans_status === 'N' || (isset($_GET['booking_success']) && $trans_status !== 'Y')) {
                    // Payment Cancelled, Refused, or Abandoned without authorization
                    if ($appt->post_status === 'draft') {
                        wp_update_post([
                            'ID'          => $order_id,
                            'post_status' => 'trash' // Trash draft so slots are FREED IMMEDIATELY!
                        ]);
                        update_post_meta($order_id, 'cosy_payment_status', 'Cancelled');
                        update_post_meta($order_id, 'cosy_booking_status', 'cancelled');

                        \Cosy\Appointments\Common\LogManager::log(
                            'orders',
                            'worldpay_payment_failed',
                            "WorldPay Payment CANCELLED/REFUSED for Order #$order_id. Slots released.",
                            get_current_user_id()
                        );
                    }
                }
            }
        }
    }

    /**
     * send_booking_emails
     * 
     * Handles sending beautiful confirmation emails to customer, provider, and admin.
     */
    public function send_booking_emails($order_id): void
    {
        $appt = get_post($order_id);
        if (!$appt || $appt->post_type !== 'cosy_appointment') return;

        $current_user = get_userdata($appt->post_author);
        if (!$current_user) return;

        // Fetch all booking meta in one clean block
        $meta_keys = [
            'service'            => 'cosy_service_name',
            'provider_id'        => 'cosy_provider_id',
            'provider_name'      => 'cosy_provider_name',
            'start_date'         => 'cosy_start_date',
            'end_date'           => 'cosy_end_date',
            'weekly_booking'     => 'cosy_weekly_booking',
            'number_of_weeks'    => 'cosy_number_of_weeks',
            'number_of_bookings' => 'cosy_number_of_bookings',
            'service_cost'       => 'cosy_service_cost',
            'service_fee'        => 'cosy_service_fee',
            'total_payable'      => 'cosy_total_payable',
            'week_days'          => 'cosy_week_days',
            'slots_timeline'     => 'cosy_slots_timeline',
            'is_gift'            => 'cosy_is_gift',
            'recipient_name'     => 'cosy_recipient_name',
            'recipient_email'    => 'cosy_recipient_email',
        ];

        foreach ($meta_keys as $var => $meta_key) {
            $$var = get_post_meta($order_id, $meta_key, true);
        }

        // Get Provider email
        $provider_user = get_userdata($provider_id);
        $provider_email = $provider_user ? $provider_user->user_email : '';

        $currency_symbol = cosy_get_currency_symbol();

        $booking_data = [
            'order_id'       => $order_id,
            'customer_name'  => $current_user->display_name,
            'customer_email' => $current_user->user_email,
            'sender_name'    => $current_user->display_name,
            'sender_email'   => $current_user->user_email,
            'provider_name'  => $provider_name,
            'provider_email' => $provider_email,
            'service_title'  => $service,
            'start_date'     => $start_date,
            'end_date'       => $end_date,
            'weekly_type'    => $weekly_booking,
            'num_weeks'      => $number_of_weeks,
            'week_days'      => $week_days,
            'num_bookings'   => $number_of_bookings,
            'slots_timeline' => $slots_timeline,
            'service_cost'   => $service_cost,
            'service_fee'    => $service_fee,
            'total_payable'  => $total_payable,
            'currency_symbol'=> $currency_symbol,
            'is_gift'        => !empty($is_gift),
            'recipient_name' => $recipient_name ?? '',
            'recipient_email'=> $recipient_email ?? '',
        ];

        // 1. Send Customer Email
        $cust_tpl = \Cosy\Appointments\Common\EmailTemplates::get_booking_customer_template($booking_data);
        cosy_send_html_email($current_user->user_email, $cust_tpl['subject'], $cust_tpl['heading'], $cust_tpl['content']);

        // 2. Send Provider Email
        if (!empty($provider_email)) {
            $prov_tpl = \Cosy\Appointments\Common\EmailTemplates::get_booking_provider_template($booking_data);
            cosy_send_html_email($provider_email, $prov_tpl['subject'], $prov_tpl['heading'], $prov_tpl['content']);
        }

        // 3. Send Administrator Email
        $admin_email = get_option('admin_email');
        if (!empty($admin_email)) {
            $admin_tpl = \Cosy\Appointments\Common\EmailTemplates::get_admin_payment_template($booking_data);
            cosy_send_html_email($admin_email, $admin_tpl['subject'], $admin_tpl['heading'], $admin_tpl['content']);
        }

        // 4. Send Gift Recipient Email (If Gift Booking)
        if (!empty($is_gift) && !empty($recipient_email)) {
            $gift_tpl = \Cosy\Appointments\Common\EmailTemplates::get_gifted_booking_template($booking_data);
            cosy_send_html_email($recipient_email, $gift_tpl['subject'], $gift_tpl['heading'], $gift_tpl['content']);
        }
    }

    /**
     * AJAX handler for filtering service providers.
     */
    public function handle_filter_service_providers(): void
    {
        $filters = [
            'search_name'      => isset($_POST['search_name']) ? sanitize_text_field($_POST['search_name']) : '',
            'service_category' => isset($_POST['service_category']) ? sanitize_text_field($_POST['service_category']) : '',
            'price_range'      => isset($_POST['price_range']) ? sanitize_text_field($_POST['price_range']) : '',
            'gender'           => isset($_POST['gender']) ? sanitize_text_field($_POST['gender']) : '',
            'age_group'        => isset($_POST['age_group']) ? sanitize_text_field($_POST['age_group']) : '',
            'rating'           => isset($_POST['rating']) ? sanitize_text_field($_POST['rating']) : '',
        ];

        $all_providers   = $this->get_all_service_providers($filters);
        $total_providers = count($all_providers);
        $per_page        = 9;
        $paged           = isset($_POST['paged']) ? max(1, intval($_POST['paged'])) : 1;
        $total_pages     = max(1, (int) ceil($total_providers / $per_page));
        $paged           = min($paged, $total_pages);
        $offset          = ($paged - 1) * $per_page;

        $providers = array_slice($all_providers, $offset, $per_page);
        $active_service_slug = $filters['service_category'];

        ob_start();
        include COSY_APPT_PATH . 'templates/service-provider-grid-template.php';
        $html = ob_get_clean();

        wp_send_json_success([
            'html'            => $html,
            'paged'           => $paged,
            'total_pages'     => $total_pages,
            'total_providers' => $total_providers,
        ]);
    }
}
