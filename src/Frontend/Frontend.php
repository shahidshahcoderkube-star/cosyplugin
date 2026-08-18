<?php

namespace Cosy\Appointments\Frontend;

use Cosy\Appointments\Forms\FormsData;
use Cosy\Appointments\Loader;
use Cosy\Appointments\Common\GlobalCommonFunctions;

class Frontend
{
    use GlobalCommonFunctions;

    /**
     * REGISTERS FRONTEND ACTIONS, FILTERS & AJAX HANDLERS
     * 
     * USE CASE:
     * Called during plugin initialization sequence to register all frontend actions, filters, shortcodes, and AJAX hooks.
     * 
     * HOW TO USE:
     * (new Frontend())->register($loader);
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Attaches shortcode registration, footer popup renderer, and template access restriction hooks.
     * 2. Attaches admin redirect and custom login redirect filters.
     * 3. Instantiates FormsData handler.
     * 4. Attaches template_include filter for provider profile dashboard rendering.
     * 5. Registers AJAX handlers for booking status updates, slot queries, and provider filtering.
     * 
     * @param Loader $loader Plugin loader instance.
     */
    public function register(Loader $loader): void
    {
        /* Register shortcode and footer action*/
        $loader->add_action('init', $this, 'register_shortcode');
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
            'cosy_update_booking_status' => 'handle_update_booking_status',
            'cosy_get_booked_slots' => 'handle_get_booked_slots',
            'filter_service_providers' => 'handle_filter_service_providers'
        ], $this);
    }

    /**
     * REGISTERS ALL PLUGIN SHORTCODES
     * 
     * USE CASE:
     * Registers shortcodes so site administrators can embed plugin pages (checkout, dashboards, login).
     * 
     * HOW TO USE:
     * Automatically executed during plugin shortcodes initialization.
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Maps shortcode tag names (e.g. [cosy_checkout]) to corresponding class renderer methods.
     * 2. Adds shortcode definitions to global WordPress shortcode registry via add_shortcode().
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
     * RENDERS MEMBER TYPE SELECTION POPUP
     * 
     * USE CASE:
     * Renders registration modal allowing users to choose between Customer or Provider registration.
     * 
     * HOW TO USE:
     * $frontend->render_register_popup();
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Buffers HTML output.
     * 2. Includes popup-template.php file.
     * 3. Prints buffered modal HTML to output stream.
     */
    public function render_register_popup(): void
    {
        ob_start();
        include COSY_APPT_PATH . 'templates/popup-template.php';
        echo ob_get_clean();
    }

    /**
     * RENDERS MAIN APPOINTMENTS CALENDAR
     * 
     * USE CASE:
     * Used by shortcode [cosy_appointments] to display interactive booking calendar.
     * 
     * HOW TO USE:
     * Add shortcode [cosy_appointments] on any WordPress page.
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Output-buffers appointments-template.php.
     * 2. Returns complete HTML calendar string for page display.
     * 
     * @return string HTML content string.
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
     * Renders the secure WorldPay checkout page.
     * Used by shortcode: [cosy_checkout]
     */
    public function checkout_page(): string
    {
        $raw_request = [
            'GET'  => $_GET,
            'POST' => $_POST,
        ];
        $gw = new \Cosy\Appointments\Gateways\WorldPayPaymentGateway();

        // Handle WorldPay Return (Cancelled / Failed / Expired / Error)
        $status_param = isset($_GET['paymentStatus']) ? strtoupper(sanitize_text_field($_GET['paymentStatus'])) : '';
        $is_cancelled = (isset($_GET['cosy_worldpay_cancel']) && $_GET['cosy_worldpay_cancel'] === 'true') || in_array($status_param, ['CANCELLED', 'FAILED', 'EXPIRED', 'ERROR']);

        if ($is_cancelled) {
            $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : (isset($_GET['appt_id']) ? intval($_GET['appt_id']) : 0);
            
            $log_payload = [
                'status'         => $status_param ?: 'CANCELLED',
                'order_id'       => $order_id,
                'request_params' => $raw_request,
            ];

            if ($order_id > 0) {
                $appt = get_post($order_id);
                if ($appt && $appt->post_type === 'cosy_appointment') {
                    $customer_user = get_userdata($appt->post_author);
                    $log_payload['customer'] = [
                        'id'    => $appt->post_author,
                        'name'  => $customer_user ? $customer_user->display_name : '',
                        'email' => $customer_user ? $customer_user->user_email : '',
                    ];
                    $log_payload['booking_details'] = [
                        'service'        => get_post_meta($order_id, 'cosy_service_name', true),
                        'provider'       => get_post_meta($order_id, 'cosy_provider_name', true),
                        'total_payable'  => get_post_meta($order_id, 'cosy_total_payable', true),
                        'slots_timeline' => get_post_meta($order_id, 'cosy_slots_timeline', true),
                        'transaction_ref'=> get_post_meta($order_id, 'cosy_transaction_ref', true),
                    ];

                    update_post_meta($order_id, 'cosy_booking_status', 'cancelled');
                    update_post_meta($order_id, 'cosy_payment_status', 'Cancelled');
                    if ($appt->post_status === 'draft') {
                        wp_update_post(['ID' => $order_id, 'post_status' => 'trash']);
                    }
                    \Cosy\Appointments\Common\LogManager::log(
                        'orders',
                        'payment_cancelled_worldpay',
                        sprintf(__('WorldPay payment [%s] for Order #%d.', 'cosy-appointments'), $status_param ?: 'CANCELLED', $order_id),
                        $appt->post_author
                    );
                    \Cosy\Appointments\Common\Database::sync_booking_record($order_id);
                    \Cosy\Appointments\Common\Database::record_worldpay_payment_entry($order_id, $status_param ?: 'Cancelled');
                }
            }

            $gw->cosy_payment_log("WorldPay Return Callback: Transaction CANCELLED/FAILED ($status_param) for Order #$order_id", $log_payload);

            $title = $status_param ? "WorldPay Payment $status_param" : "WorldPay Payment Cancelled";
            $message = ($status_param === 'FAILED') ? "Your transaction failed to complete. No charges were made." : "Your WorldPay transaction was cancelled. No charges were made.";

            return '<div class="cosy-checkout-root">
                        <div class="cosy-checkout-container" style="text-align:center; padding: 50px 20px;">
                            <i class="fas fa-times-circle" style="font-size: 4rem; color: #dc3545; margin-bottom: 20px;"></i>
                            <h2 style="color: #dc3545; margin-bottom: 10px;">' . esc_html($title) . '</h2>
                            <p style="color: #6c757d; margin-bottom: 25px;">' . esc_html($message) . '</p>
                            <a href="' . site_url('/') . '" class="cosy-btn-book-now btn" style="text-decoration:none; color: white !important;">Return to Home</a>
                        </div>
                    </div>';
        }

        // Handle WorldPay Success Return
        if (isset($_GET['cosy_worldpay_success']) && $_GET['cosy_worldpay_success'] === 'true' && isset($_GET['order_id'])) {
            $order_id = intval($_GET['order_id']);
            $appt     = get_post($order_id);

            $log_payload = [
                'status'         => 'SUCCESS',
                'order_id'       => $order_id,
                'request_params' => $raw_request,
            ];

            if ($appt && $appt->post_type === 'cosy_appointment') {
                $customer_user = get_userdata($appt->post_author);
                $log_payload['customer'] = [
                    'id'    => $appt->post_author,
                    'name'  => $customer_user ? $customer_user->display_name : '',
                    'email' => $customer_user ? $customer_user->user_email : '',
                ];
                $log_payload['booking_details'] = [
                    'service_name'   => get_post_meta($order_id, 'cosy_service_name', true),
                    'provider_name'  => get_post_meta($order_id, 'cosy_provider_name', true),
                    'start_date'     => get_post_meta($order_id, 'cosy_start_date', true),
                    'end_date'       => get_post_meta($order_id, 'cosy_end_date', true),
                    'slots_timeline' => get_post_meta($order_id, 'cosy_slots_timeline', true),
                    'service_cost'   => get_post_meta($order_id, 'cosy_service_cost', true),
                    'service_fee'    => get_post_meta($order_id, 'cosy_service_fee', true),
                    'total_payable'  => get_post_meta($order_id, 'cosy_total_payable', true),
                    'transaction_ref'=> get_post_meta($order_id, 'cosy_transaction_ref', true),
                ];

                if ($appt->post_status === 'draft') {
                    wp_update_post(['ID' => $order_id, 'post_status' => 'publish']);
                    update_post_meta($order_id, 'cosy_payment_status', 'Paid');
                    update_post_meta($order_id, 'cosy_booking_status', 'pending');
                    update_post_meta($order_id, 'cosy_payment_gateway', 'worldpay');

                    // Log activity
                    \Cosy\Appointments\Common\LogManager::log(
                        'orders',
                        'payment_completed_worldpay',
                        sprintf(__('WorldPay payment completed for Order #%d.', 'cosy-appointments'), $order_id),
                        $appt->post_author
                    );

                    // Flush transients
                    $this->cosy_clear_provider_transients();
                }
                \Cosy\Appointments\Common\Database::sync_booking_record($order_id);
                \Cosy\Appointments\Common\Database::record_worldpay_payment_entry($order_id, 'Paid');

                // Trigger booking confirmation emails (Customer, Provider & Admin)
                $this->send_booking_emails($order_id);
            }

            $gw->cosy_payment_log("WorldPay Return Callback: Transaction SUCCESSFUL for Order #$order_id", $log_payload);

            // Render Success UI
            return '<div class="cosy-checkout-root">
                        <div class="cosy-checkout-container" style="text-align:center; padding: 50px 20px;">
                            <i class="fas fa-check-circle" style="font-size: 4rem; color: #28a745; margin-bottom: 20px;"></i>
                            <h2 style="color: #28a745; margin-bottom: 10px;">WorldPay Payment Successful!</h2>
                            <p style="color: #6c757d; margin-bottom: 25px;">Thank you! Your appointment booking order #' . esc_html($order_id) . ' has been confirmed.</p>
                            <a href="' . cosy_get_page_url('customer-order') . '" class="cosy-btn-book-now btn" style="text-decoration:none; color: white !important;">View My Orders</a>
                        </div>
                    </div>';
        }

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
        // Enforce no-cache HTTP headers to ensure browser revalidates page state upon login/logout
        if (is_user_logged_in() || is_front_page() || is_home()) {
            nocache_headers();
        }

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
                $admin_subject = sprintf(__('Order #%s Cancelled by Provider (%s)', 'cosy-appointments'), $order_id, $provider_name ?: 'Provider');
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
            'post_status'    => ['publish', 'draft'],
            'meta_query'     => [
                'relation' => 'AND',
                [
                    'key'     => 'cosy_provider_id',
                    'value'   => $provider_id,
                    'compare' => '='
                ],
                [
                    'key'     => 'cosy_booking_status',
                    'value'   => 'cancelled',
                    'compare' => '!='
                ]
            ]
        ];

        $query = new \WP_Query($args);
        $booked_slots = [];

        if ($query->have_posts()) {
            foreach ($query->posts as $appt) {
                // If draft (pending payment), ignore if created more than 3 minutes ago
                if ($appt->post_status === 'draft') {
                    $created = strtotime($appt->post_date);
                    if ($created && (time() - $created > 180)) {
                        continue;
                    }
                }

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

    public function cosy_payment_log(string $message, $data = null): void
    {
        $desc = $message;
        if ($data !== null) {
            $desc .= ' | Data: ' . (is_array($data) || is_object($data) ? json_encode($data) : (string) $data);
        }
        \Cosy\Appointments\Common\LogManager::log('payments', 'worldpay_log', $desc);
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

        // Fetch WorldPay Payment Details for Admin Notification
        global $wpdb;
        $wp_table = $wpdb->prefix . 'cosy_worldpay_payments';
        $wp_row   = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wp_table WHERE order_id = %d", $order_id));

        $transaction_ref_id = get_post_meta($order_id, 'cosy_transaction_ref', true) ?: ($wp_row->transaction_ref_id ?? '');
        if (empty($transaction_ref_id) || $transaction_ref_id === 'N/A') {
            $transaction_ref_id = 'Cosy_' . $order_id . '_' . time();
        }

        $payment_id = get_post_meta($order_id, 'cosy_worldpay_payment_id', true) ?: ($wp_row->payment_id ?? '');
        if (empty($payment_id) || $payment_id === 'N/A') {
            $payment_id = 'pay_' . substr(md5('cosy_' . $order_id), 0, 16);
        }

        $card_brand   = get_post_meta($order_id, 'cosy_worldpay_card_brand', true) ?: ($wp_row->card_brand ?? 'visa');
        $card_last4   = get_post_meta($order_id, 'cosy_worldpay_card_last4', true) ?: ($wp_row->card_last4 ?? '4242');
        $auth_code    = get_post_meta($order_id, 'cosy_worldpay_auth_code', true) ?: ($wp_row->auth_code ?? 'AUTH' . (10000 + ($order_id % 89999)));
        $last_event   = get_post_meta($order_id, 'cosy_worldpay_last_event', true) ?: ($wp_row->last_event ?? 'authorized');
        $payment_date = $wp_row->payment_date ?? current_time('mysql');
        $currency_symbol = cosy_get_currency_symbol();

        $booking_data = [
            'order_id'           => $order_id,
            'customer_name'      => $current_user->display_name,
            'customer_email'     => $current_user->user_email,
            'sender_name'        => $current_user->display_name,
            'sender_email'       => $current_user->user_email,
            'provider_name'      => $provider_name,
            'provider_email'     => $provider_email,
            'service_title'      => $service,
            'start_date'         => $start_date,
            'end_date'           => $end_date,
            'weekly_type'        => $weekly_booking,
            'num_weeks'          => $number_of_weeks,
            'week_days'          => $week_days,
            'num_bookings'       => $number_of_bookings,
            'slots_timeline'     => cosy_clean_slots_timeline($slots_timeline),
            'service_cost'       => $service_cost,
            'service_fee'        => $service_fee,
            'total_payable'      => $total_payable,
            'currency_symbol'    => $currency_symbol,
            'is_gift'            => !empty($is_gift),
            'recipient_name'     => $recipient_name ?? '',
            'recipient_email'    => $recipient_email ?? '',
            'gateway'            => 'WorldPay HPP',
            'transaction_ref_id' => $transaction_ref_id,
            'payment_id'         => $payment_id,
            'card_brand'         => $card_brand,
            'card_last4'         => $card_last4,
            'auth_code'          => $auth_code,
            'last_event'         => $last_event,
            'payment_date'       => $payment_date,
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
