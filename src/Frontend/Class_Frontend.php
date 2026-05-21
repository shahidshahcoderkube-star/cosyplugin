<?php

namespace Cosy\Appointments\Frontend;

use Cosy\Appointments\Forms\FormsData;
use Cosy\Appointments\Loader;
use Cosy\Appointments\Common\GlobalCommonFunctions;

class Frontend
{
    use GlobalCommonFunctions;

    //--------------- Register Actions ----------------//
    public function register(Loader $loader): void
    {
        /* Register shortcode and footer action*/
        $loader->add_action('init', $this, 'register_shortcode');
        $loader->add_action('wp_footer', $this, 'render_register_popup');
        $loader->add_action('template_redirect', $this, 'restrict_direct_page_access');
        $loader->add_action('after_setup_theme', $this, 'hide_admin_menu');

        // FormsData handles its own registration in constructor
        new FormsData();

        $loader->add_filter('template_include', $this, 'provider_profile_dashboard_shortcode', 9999);

        // Register AJAX handlers for booking creation
        $this->register_ajax_handlers([
            'cosy_create_stripe_session' => 'handle_create_stripe_session',
            'cosy_update_booking_status' => 'handle_update_booking_status',
            'cosy_get_booked_slots' => 'handle_get_booked_slots'
        ], $this);
    }


    //--------------- Registering the shortcode for appointments----------------//
    public function register_shortcode(): void
    {
        add_shortcode('cosy_appointments', [$this, 'appointments_shortcode']);
        add_shortcode('cosy_customer_registration', [$this, 'customer_registration_shortcode']);
        add_shortcode('cosy_provider_registration', [$this, 'provider_registration_shortcode']);
        add_shortcode('customer_profile', [$this, 'customer_profile_shortcode']);
        // add_shortcode('cosy_profile_dashboard', [$this, 'provider_profile_dashboard_shortcode']);
        add_shortcode('cosy_verify_provider', [$this, 'provider_verify_shortcode']);
        add_shortcode('cosy_login_form', [$this, 'login_form']);
        add_shortcode('cosy_customer_order', [$this, 'customer_order_page']);
        add_shortcode('cosy_service_provider_list', [$this, 'service_provider_shortcode']);
        add_shortcode('cosy_checkout', [$this, 'checkout_page']);
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


    //------------- Rendering the shortcode content */
    public function appointments_shortcode(): string
    {
        ob_start();
        include COSY_APPT_PATH . 'templates/appointments-template.php';
        return ob_get_clean();
    }


    //------------- Rendering the customer registration shortcode content -------------//
    public function customer_registration_shortcode(): string
    {
        ob_start();
        include COSY_APPT_PATH . 'templates/customer-registration-template.php';
        return ob_get_clean();
    }


    //------------- Rendering the provider registration shortcode content -------------//
    public function provider_registration_shortcode(): string
    {
        ob_start();
        include COSY_APPT_PATH . 'templates/provider-registration-template.php';
        return ob_get_clean();
    }


    //------------- Rendering the customer profile shortcode content -------------//
    public function customer_profile_shortcode(): string
    {
        ob_start();
        include COSY_APPT_PATH . 'templates/customer-profile-template.php';
        return ob_get_clean();
    }


    //------------- Rendering the login form shortcode content -------------//
    public function login_form(): string
    {

        ob_start();
        include COSY_APPT_PATH . 'templates/login-template.php';
        return ob_get_clean();
    }


    //------------- Rendering the customer order shortcode content -------------//
    public function customer_order_page(): string
    {
        ob_start();
        include COSY_APPT_PATH . 'templates/customer-order-template.php';
        return ob_get_clean();
    }


    //------------- Rendering the checkout shortcode content -------------//
    public function checkout_page(): string
    {
        // Handle Stripe Cancelled
        if (isset($_GET['cosy_stripe_cancel']) && $_GET['cosy_stripe_cancel'] === 'true') {
            $this->cosy_payment_log("Stripe Checkout CANCELLED by user.");
            return '<div class="cosy-checkout-root">
                        <div class="cosy-checkout-container" style="text-align:center; padding: 50px 20px;">
                            <i class="fas fa-times-circle" style="font-size: 4rem; color: #dc3545; margin-bottom: 20px;"></i>
                            <h2 style="color: #dc3545; margin-bottom: 10px;">Payment Cancelled</h2>
                            <p style="color: #6c757d; margin-bottom: 25px;">Your payment was cancelled. No charges were made.</p>
                            <a href="' . site_url('/') . '" class="cosy-btn-book-now btn" style="text-decoration:none; color: white !important;" onmouseover="this.style.opacity=\'0.9\';" onmouseout="this.style.opacity=\'1\';">Return to Home</a>
                        </div>
                    </div>';
        }

        // Handle Stripe Success
        if (isset($_GET['cosy_stripe_success']) && $_GET['cosy_stripe_success'] === 'true' && isset($_GET['appt_id'])) {
            $appointment_id = intval($_GET['appt_id']);
            $session_id = isset($_GET['session_id']) ? sanitize_text_field($_GET['session_id']) : '';

            $this->cosy_payment_log("Stripe Checkout SUCCESS return for Appointment #$appointment_id. Session ID: $session_id");

            $appt = get_post($appointment_id);
            if ($appt && $appt->post_type === 'cosy_appointment' && $appt->post_status === 'draft') {
                $this->cosy_payment_log("Confirming Appointment #$appointment_id and updating payment status to Paid.");
                // Update post to publish
                wp_update_post([
                    'ID' => $appointment_id,
                    'post_status' => 'publish'
                ]);

                // Update payment meta
                update_post_meta($appointment_id, 'cosy_payment_status', 'Paid');
                update_post_meta($appointment_id, 'cosy_stripe_session_id', $session_id);

                // Send confirmation emails
                $this->send_booking_emails($appointment_id);
            }

            return '<div class="cosy-checkout-root">
                        <div class="cosy-checkout-container" style="text-align:center; padding: 50px 20px;">
                            <i class="fas fa-check-circle" style="font-size: 4rem; color: #198754; margin-bottom: 20px;"></i>
                            <h2 style="color: #198754; margin-bottom: 10px;">Payment Successful!</h2>
                            <p style="color: #6c757d; margin-bottom: 25px;">Thank you for your booking. Your appointment has been confirmed.</p>
                            <a href="' . site_url('/customer-profile') . '" class="cosy-btn-book-now btn" style="text-decoration:none; color: white !important;" onmouseover="this.style.opacity=\'0.9\';" onmouseout="this.style.opacity=\'1\';">View My Bookings</a>
                        </div>
                    </div>';
        }

        ob_start();
        include COSY_APPT_PATH . 'templates/checkout-template.php';
        return ob_get_clean();
    }


    //------------- Rendering the provider dashboard shortcode content -------------//
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

    //------------- Rendering the provider profile dashboard content it open single page when user click on provider profile view profile button -------------//
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
    //------------- Hide Admin Menu for specific roles -------------//
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


    //------------- Restrict Direct Page Access -------------//
    public function restrict_direct_page_access()
    {
        // Pages that require login
        $restricted_slugs = ['appointments', 'orders', 'customer-order', 'customer-profile', 'provider-dashboard', 'provider-verify', 'cosy-checkout'];
        if (is_page($restricted_slugs) && !is_user_logged_in()) {
            wp_safe_redirect(site_url('/login'));
            exit;
        }

        // Additional check for provider-dashboard page
        if (is_user_logged_in()) {

            $user = wp_get_current_user();

            $blocked_for_provider = ['customer-order', 'customer-profile', 'appointments', 'orders', 'cosy-checkout'];
            if (in_array('provider', (array) $user->roles) && is_page($blocked_for_provider)) {
                wp_safe_redirect(site_url('/provider-dashboard'));
                exit;
            }

            $blocked_for_customer = ['provider-dashboard', 'provider-verify'];
            if (in_array('customer', (array) $user->roles, true) && is_page($blocked_for_customer)) {
                wp_safe_redirect(site_url('/customer-profile'));
                exit;
            }

            // fallback
            // wp_redirect(site_url('/login'));
            // exit;
        }
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

        $appointment_id = isset($_POST['appointment_id']) ? intval($_POST['appointment_id']) : 0;
        $new_status     = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';

        if (empty($appointment_id) || !in_array($new_status, ['completed', 'cancelled'])) {
            wp_send_json_error(['message' => 'Invalid parameters.']);
        }

        // Validate that the logged in user is indeed the provider for this appointment
        $current_user_id = get_current_user_id();
        $provider_id = intval(get_post_meta($appointment_id, 'cosy_provider_id', true));

        if ($provider_id !== $current_user_id && !current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized action.']);
        }

        update_post_meta($appointment_id, 'cosy_booking_status', $new_status);

        wp_send_json_success(['message' => 'Status updated successfully to ' . ucfirst($new_status)]);
    }

    /**
     * handle_get_booked_slots
     * 
     * Retrieves already booked time slots for a given provider on a specific date.
     */
    public function handle_get_booked_slots(): void
    {
        check_ajax_referer('cosy_calendar_nonce', 'nonce');
        $provider_id = isset($_POST['provider_id']) ? intval($_POST['provider_id']) : 0;
        $date_str    = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : ''; // e.g. "Wed May 20 2026"

        if (empty($provider_id) || empty($date_str)) {
            wp_send_json_error(['message' => 'Missing provider or date parameter.']);
        }

        $args = [
            'post_type'      => 'cosy_appointment',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
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
                $slots_meta = get_post_meta($appt->ID, 'cosy_slots', true);
                if (!empty($slots_meta)) {
                    // slots_meta is expected to be a JSON string like:
                    // [{"date":"Wed May 20 2026","time":"09:00"}]
                    // Decoded with html_entity_decode to handle any WordPress escaping
                    $decoded = html_entity_decode($slots_meta);
                    $slots = json_decode($decoded, true);

                    // Fallback to normal json_decode if decode fails
                    if (!is_array($slots)) {
                        $slots = json_decode($slots_meta, true);
                    }

                    if (is_array($slots)) {
                        foreach ($slots as $slot) {
                            if (isset($slot['date']) && $slot['date'] === $date_str) {
                                if (isset($slot['time'])) {
                                    $booked_slots[] = $slot['time']; // "HH:MM" e.g. "09:00"
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
     * handle_create_stripe_session
     * 
     * Backend AJAX handler to generate a Stripe Checkout session.
     * Inserts a pending cosy_appointment post first, then contacts Stripe API to get session URL.
     */
    public function handle_create_stripe_session(): void
    {
        check_ajax_referer('cosy_booking_nonce', 'nonce');
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'User must be logged in to book services.']);
        }

        $current_user = wp_get_current_user();
        if (!in_array('customer', (array) $current_user->roles)) {
            wp_send_json_error(['message' => 'Only customers are authorized to book appointments.']);
        }

        // Retrieve and validate POST data
        $service            = isset($_POST['service']) ? sanitize_text_field($_POST['service']) : '';
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
        $slots_json         = isset($_POST['slots']) ? wp_unslash($_POST['slots']) : ''; // raw JSON string

        if (empty($service) || empty($provider_id)) {
            $this->cosy_payment_log("Stripe Session Creation FAILED: Missing service or provider details.", $_POST);
            wp_send_json_error(['message' => 'Missing required service or provider details.']);
        }

        $this->cosy_payment_log("Initiating Stripe Session Creation for Service: $service, Provider ID: $provider_id", $_POST);

        // Create booking in draft/pending status first
        $appointment_title = sprintf(
            '%s booked %s by %s (Pending Stripe Payment)',
            $current_user->display_name,
            $service,
            $provider_name
        );

        $appointment_id = wp_insert_post([
            'post_title'   => $appointment_title,
            'post_type'    => 'cosy_appointment',
            'post_status'  => 'draft', // Draft status indicates unpaid/pending
            'post_author'  => $current_user->ID
        ]);

        if (is_wp_error($appointment_id)) {
            $this->cosy_payment_log("Stripe Session FAILED: Could not create draft appointment.", $appointment_id->get_error_message());
            wp_send_json_error(['message' => 'Failed to create pending booking: ' . $appointment_id->get_error_message()]);
        }

        $this->cosy_payment_log("Draft Appointment created successfully with ID: $appointment_id. Proceeding to Stripe API.");

        // Save metadata
        update_post_meta($appointment_id, 'cosy_service_name', $service);
        update_post_meta($appointment_id, 'cosy_provider_id', $provider_id);
        update_post_meta($appointment_id, 'cosy_provider_name', $provider_name);
        update_post_meta($appointment_id, 'cosy_customer_id', $current_user->ID);
        update_post_meta($appointment_id, 'cosy_customer_name', $current_user->display_name);
        update_post_meta($appointment_id, 'cosy_customer_email', $current_user->user_email);
        update_post_meta($appointment_id, 'cosy_start_date', $start_date);
        update_post_meta($appointment_id, 'cosy_end_date', $end_date);
        update_post_meta($appointment_id, 'cosy_weekly_booking', $weekly_booking);
        update_post_meta($appointment_id, 'cosy_number_of_weeks', $number_of_weeks);
        update_post_meta($appointment_id, 'cosy_number_of_bookings', $number_of_bookings);
        update_post_meta($appointment_id, 'cosy_service_cost', $service_cost);
        update_post_meta($appointment_id, 'cosy_service_fee', $service_fee);
        update_post_meta($appointment_id, 'cosy_total_payable', $total_payable);
        update_post_meta($appointment_id, 'cosy_slots', sanitize_textarea_field($slots_json));
        update_post_meta($appointment_id, 'cosy_payment_status', 'Pending');
        update_post_meta($appointment_id, 'cosy_booking_status', 'pending');

        // Fetch Stripe keys
        $secret_key = get_option('cosy_stripe_key');
        if (empty($secret_key)) {
            $this->cosy_payment_log("Stripe API ERROR: Secret Key is empty. Admin needs to configure Stripe.");
            wp_send_json_error(['message' => 'Stripe is not configured by the admin yet.']);
        }

        // Convert total payable to pence (cents)
        $amount_in_cents = round(floatval($total_payable) * 100);

        // Call Stripe Checkout API using WordPress HTTP API
        $stripe_endpoint = 'https://api.stripe.com/v1/checkout/sessions';
        $success_url = add_query_arg([
            'cosy_stripe_success' => 'true',
            'session_id'          => '{CHECKOUT_SESSION_ID}',
            'appt_id'             => $appointment_id
        ], site_url('/cosy-checkout'));

        $cancel_url = add_query_arg([
            'cosy_stripe_cancel' => 'true'
        ], site_url('/cosy-checkout'));

        $body = [
            'payment_method_types[0]'                      => 'card',
            'mode'                                         => 'payment',
            'success_url'                                  => $success_url,
            'cancel_url'                                   => $cancel_url,
            'customer_email'                               => $current_user->user_email,
            'client_reference_id'                          => (string) $appointment_id,
            'line_items[0][price_data][currency]'          => 'gbp',
            'line_items[0][price_data][product_data][name]' => 'Appointment Booking: ' . $service,
            'line_items[0][price_data][unit_amount]'       => $amount_in_cents,
            'line_items[0][quantity]'                      => 1,
            'metadata[appointment_id]'                     => $appointment_id
        ];

        $response = wp_remote_post($stripe_endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $secret_key,
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ],
            'body'    => $body,
            'timeout' => 15
        ]);

        if (is_wp_error($response)) {
            $this->cosy_payment_log("Stripe API HTTP Request FAILED", $response->get_error_message());
            wp_send_json_error(['message' => 'Stripe API communication failed: ' . $response->get_error_message()]);
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $decoded = json_decode($response_body, true);

        if ($response_code !== 200) {
            $error_msg = isset($decoded['error']['message']) ? $decoded['error']['message'] : 'Unknown error from Stripe.';
            $this->cosy_payment_log("Stripe API ERROR RESPONSE (Code $response_code)", $decoded);
            wp_send_json_error(['message' => 'Stripe Checkout creation failed: ' . $error_msg]);
        }

        $this->cosy_payment_log("Stripe Session Created SUCCESSFULLY for Appointment #$appointment_id. Session ID: {$decoded['id']}");

        wp_send_json_success([
            'sessionId' => $decoded['id'],
            'url'       => $decoded['url']
        ]);
    }

    /**
     * send_booking_emails
     * 
     * Handles sending beautiful confirmation emails to customer, provider, and admin.
     */
    public function send_booking_emails($appointment_id): void
    {
        $appt = get_post($appointment_id);
        if (!$appt || $appt->post_type !== 'cosy_appointment') return;

        $current_user = get_userdata($appt->post_author);
        if (!$current_user) return;

        $service = get_post_meta($appointment_id, 'cosy_service_name', true);
        $provider_id = get_post_meta($appointment_id, 'cosy_provider_id', true);
        $provider_name = get_post_meta($appointment_id, 'cosy_provider_name', true);
        $start_date = get_post_meta($appointment_id, 'cosy_start_date', true);
        $end_date = get_post_meta($appointment_id, 'cosy_end_date', true);
        $weekly_booking = get_post_meta($appointment_id, 'cosy_weekly_booking', true);
        $number_of_weeks = get_post_meta($appointment_id, 'cosy_number_of_weeks', true);
        $number_of_bookings = get_post_meta($appointment_id, 'cosy_number_of_bookings', true);
        $service_cost = get_post_meta($appointment_id, 'cosy_service_cost', true);
        $service_fee = get_post_meta($appointment_id, 'cosy_service_fee', true);
        $total_payable = get_post_meta($appointment_id, 'cosy_total_payable', true);

        // Set email content type to HTML
        $html_email_filter = function () {
            return 'text/html';
        };
        add_filter('wp_mail_content_type', $html_email_filter);

        // Get Provider email
        $provider_user = get_userdata($provider_id);
        $provider_email = $provider_user ? $provider_user->user_email : '';

        // Common email styling wrapper
        $email_style = "
            background-color: #faf6f9;
            padding: 40px 15px;
            font-family: 'Outfit', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1e293b;
        ";
        $card_style = "
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            border: 1px solid #f1e4ef;
            box-shadow: 0 10px 25px rgba(109, 46, 103, 0.05);
            overflow: hidden;
        ";
        $header_style = "
            background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%);
            padding: 30px 20px;
            text-align: center;
            color: #ffffff;
        ";
        $body_style = "
            padding: 30px 25px;
            font-size: 15px;
            line-height: 1.6;
        ";
        $table_style = "
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 14px;
        ";
        $footer_style = "
            background-color: #fdf2fb;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #8a7a88;
            border-top: 1px solid #f1e4ef;
        ";

        // 1. Send Customer Email
        $customer_subject = "🌸 Booking Confirmed - Thank you for your payment!";
        $customer_body = "
        <div style='{$email_style}'>
            <div style='{$card_style}'>
                <div style='{$header_style}'>
                    <h1 style='margin: 0; font-size: 24px; font-weight: 700;'>Booking Confirmed!</h1>
                </div>
                <div style='{$body_style}'>
                    <p>Hello <strong>{$current_user->display_name}</strong>,</p>
                    <p>Thank you for choosing our platform. Your payment of <strong>£{$total_payable}</strong> has been successfully processed securely.</p>
                    
                    <h3 style='color: #6d2e67; border-bottom: 2px solid #f1e4ef; padding-bottom: 8px; margin-top: 25px;'>Booking Information Summary:</h3>
                    <table style='{$table_style}'>
                        <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0; font-weight: 600; width: 40%;'>Order ID</td><td style='padding: 10px 0;'>#{$appointment_id}</td></tr>
                        <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0; font-weight: 600;'>Service Booked</td><td style='padding: 10px 0;'>{$service}</td></tr>
                        <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0; font-weight: 600;'>Service Provider</td><td style='padding: 10px 0;'>{$provider_name}</td></tr>
                        <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0; font-weight: 600;'>Start Date</td><td style='padding: 10px 0;'>{$start_date}</td></tr>
                        <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0; font-weight: 600;'>End Date</td><td style='padding: 10px 0;'>{$end_date}</td></tr>
                        <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0; font-weight: 600;'>Weekly Schedule</td><td style='padding: 10px 0;'>{$weekly_booking}</td></tr>
                        <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0; font-weight: 600;'>Number of Weeks</td><td style='padding: 10px 0;'>{$number_of_weeks}</td></tr>
                        <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0; font-weight: 600;'>Total Booked Slots</td><td style='padding: 10px 0;'>{$number_of_bookings} slots</td></tr>
                    </table>

                    <h3 style='color: #6d2e67; border-bottom: 2px solid #f1e4ef; padding-bottom: 8px; margin-top: 25px;'>Payment Details:</h3>
                    <table style='{$table_style}'>
                        <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0;'>Service Cost</td><td style='padding: 10px 0; text-align: right;'>£{$service_cost}</td></tr>
                        <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0;'>Service Fee</td><td style='padding: 10px 0; text-align: right;'>£{$service_fee}</td></tr>
                        <tr style='background-color: #fdf2fb;'><td style='padding: 12px 10px; font-weight: 700; color: #a44390;'>Total Paid</td><td style='padding: 12px 10px; font-weight: 700; text-align: right; color: #a44390;'>£{$total_payable}</td></tr>
                    </table>
                    
                    <p style='margin-top: 30px; font-size: 14px; color: #64748b; text-align: center;'>You can track your live schedule and update booking details directly from your Customer account profile.</p>
                </div>
                <div style='{$footer_style}'>
                    &copy; " . date('Y') . " Cosy Appointments. All rights reserved.
                </div>
            </div>
        </div>";
        wp_mail($current_user->user_email, $customer_subject, $customer_body);

        // 2. Send Provider Email
        if (!empty($provider_email)) {
            $provider_subject = "📅 New Booking Received - {$current_user->display_name} has booked your service!";
            $provider_body = "
            <div style='{$email_style}'>
                <div style='{$card_style}'>
                    <div style='{$header_style}'>
                        <h1 style='margin: 0; font-size: 24px; font-weight: 700;'>New Appointment Notification</h1>
                    </div>
                    <div style='{$body_style}'>
                        <p>Hello <strong>{$provider_name}</strong>,</p>
                        <p>Great news! A new customer, <strong>{$current_user->display_name}</strong>, has booked your services and completed the payment transaction.</p>
                        
                        <h3 style='color: #6d2e67; border-bottom: 2px solid #f1e4ef; padding-bottom: 8px; margin-top: 25px;'>Booking Information:</h3>
                        <table style='{$table_style}'>
                            <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0; font-weight: 600; width: 40%;'>Order ID</td><td style='padding: 10px 0;'>#{$appointment_id}</td></tr>
                            <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0; font-weight: 600;'>Service Booked</td><td style='padding: 10px 0;'>{$service}</td></tr>
                            <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0; font-weight: 600;'>Customer Name</td><td style='padding: 10px 0;'>{$current_user->display_name}</td></tr>
                            <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0; font-weight: 600;'>Customer Email</td><td style='padding: 10px 0;'>{$current_user->user_email}</td></tr>
                            <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0; font-weight: 600;'>Start Date</td><td style='padding: 10px 0;'>{$start_date}</td></tr>
                            <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0; font-weight: 600;'>End Date</td><td style='padding: 10px 0;'>{$end_date}</td></tr>
                            <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0; font-weight: 600;'>Weekly Schedule</td><td style='padding: 10px 0;'>{$weekly_booking}</td></tr>
                            <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0; font-weight: 600;'>Number of Weeks</td><td style='padding: 10px 0;'>{$number_of_weeks}</td></tr>
                            <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0; font-weight: 600;'>Total Booked Slots</td><td style='padding: 10px 0;'>{$number_of_bookings} slots</td></tr>
                            <tr style='background-color: #fdf2fb;'><td style='padding: 12px 10px; font-weight: 700; color: #6d2e67;'>Your Earnings</td><td style='padding: 12px 10px; font-weight: 700; text-align: right; color: #6d2e67;'>£{$service_cost}</td></tr>
                        </table>
                        
                        <p style='margin-top: 30px; font-size: 14px; color: #64748b; text-align: center;'>Please log in to your Provider Dashboard to manage your dashboard schedule and check invoice receipts.</p>
                    </div>
                    <div style='{$footer_style}'>
                        &copy; " . date('Y') . " Cosy Appointments. All rights reserved.
                    </div>
                </div>
            </div>";
            wp_mail($provider_email, $provider_subject, $provider_body);
        }

        // 3. Send Administrator Email
        $admin_email = get_option('admin_email');
        if (!empty($admin_email)) {
            $admin_subject = "🔔 New Secure Payment Received - Order #{$appointment_id}";
            $admin_body = "
            <div style='{$email_style}'>
                <div style='{$card_style}'>
                    <div style='{$header_style}'>
                        <h1 style='margin: 0; font-size: 24px; font-weight: 700;'>Admin Payment Alert</h1>
                    </div>
                    <div style='{$body_style}'>
                        <p>Hello Administrator,</p>
                        <p>A new payment transaction has been processed and authorized successfully.</p>
                        
                        <h3 style='color: #6d2e67; border-bottom: 2px solid #f1e4ef; padding-bottom: 8px; margin-top: 25px;'>Order Information Summary:</h3>
                        <table style='{$table_style}'>
                            <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0; font-weight: 600; width: 40%;'>Order Reference ID</td><td style='padding: 10px 0;'>#{$appointment_id}</td></tr>
                            <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0; font-weight: 600;'>Service</td><td style='padding: 10px 0;'>{$service}</td></tr>
                            <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0; font-weight: 600;'>Customer Name</td><td style='padding: 10px 0;'>{$current_user->display_name} ({$current_user->user_email})</td></tr>
                            <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0; font-weight: 600;'>Service Provider</td><td style='padding: 10px 0;'>{$provider_name}</td></tr>
                            <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0; font-weight: 600;'>Start Date</td><td style='padding: 10px 0;'>{$start_date}</td></tr>
                            <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0; font-weight: 600;'>End Date</td><td style='padding: 10px 0;'>{$end_date}</td></tr>
                            <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0; font-weight: 600;'>Weekly Schedule</td><td style='padding: 10px 0;'>{$weekly_booking}</td></tr>
                            <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0; font-weight: 600;'>Weeks & Slots</td><td style='padding: 10px 0;'>{$number_of_bookings} slots over {$number_of_weeks} week(s)</td></tr>
                        </table>

                        <h3 style='color: #6d2e67; border-bottom: 2px solid #f1e4ef; padding-bottom: 8px; margin-top: 25px;'>Financial Details:</h3>
                        <table style='{$table_style}'>
                            <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0;'>Provider Revenue Share</td><td style='padding: 10px 0; text-align: right;'>£{$service_cost}</td></tr>
                            <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0;'>Platform Service Fee (Net)</td><td style='padding: 10px 0; text-align: right;'>£{$service_fee}</td></tr>
                            <tr style='background-color: #fdf2fb;'><td style='padding: 12px 10px; font-weight: 700; color: #a44390;'>Total Paid</td><td style='padding: 12px 10px; font-weight: 700; text-align: right; color: #a44390;'>£{$total_payable}</td></tr>
                        </table>
                    </div>
                    <div style='{$footer_style}'>
                        &copy; " . date('Y') . " Cosy Appointments. Admin Operations Panel.
                    </div>
                </div>
            </div>";
            wp_mail($admin_email, $admin_subject, $admin_body);
        }

        // Remove HTML filter to restore defaults for other system emails
        remove_filter('wp_mail_content_type', $html_email_filter);
    }
}
