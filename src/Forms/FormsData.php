<?php

namespace Cosy\Appointments\Forms;

use Cosy\Appointments\Common\GlobalCommonFunctions;
use WP_User;

class FormsData
{
    use GlobalCommonFunctions;

    /**
     * Constructor: Initializes the form handler.
     * Hooks up AJAX actions for login, registration, and password recovery.
     */
    public function __construct()
    {
        // Register all AJAX handlers dynamically
        $actions = [
            'cosy_customer_register' => 'handle_customer_registration',
            'cosy_provider_register' => 'handle_provider_registration',
            'cosy_login'             => 'handle_login',
            'cosy_forgot_password'   => 'handle_forgot_password',
        ];
        $this->register_ajax_handlers($actions, $this);

        // Hook for non-AJAX verification (email link)
        add_action('init', [$this, 'handle_provider_verification']);
    }

    /**
     * Utility method to send a standard JSON response to the frontend.
     * Used by AJAX handlers to return success or error messages.
     */
    public function send_response($success, $message)
    {
        if ($success) {
            wp_send_json_success($message);
        } else {
            wp_send_json_error($message);
        }
    }

    /**
     * Utility method to send JSON responses containing an array of messages.
     */
    public function send_multiple_response($success, $message)
    {
        if ($success) {
            wp_send_json_success([$message]);
        } else {
            wp_send_json_error([$message]);
        }
    }

    /**
     * Handles AJAX requests for new Customer registrations.
     * Validates input, creates a WordPress user with the 'customer' role, and sends a response.
     */
    public function handle_customer_registration()
    {
        // Security check: verify nonce
        if (!isset($_POST['cosy_nonce']) || !wp_verify_nonce($_POST['cosy_nonce'], 'cosy_customer_register_nonce')) {
            wp_send_json_error('Security check failed. Please refresh the page.');
        }

        // Ensure request is POST + correct action
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'cosy_customer_register') {
            return; // silently ignore if not our form
        }

        // Safely fetch POST values
        $name  = !empty($_POST['cust_name']) ? sanitize_text_field($_POST['cust_name']) : '';
        $email = !empty($_POST['cust_email']) ? sanitize_email($_POST['cust_email']) : '';
        $pass  = !empty($_POST['cust_pass']) ? sanitize_text_field($_POST['cust_pass']) : '';

        // Validate required fields
        if (empty($name) || empty($email) || empty($pass)) {
            $this->send_response(false, 'All fields are required.');
            return;
        }

        // Check if email already exists
        if (email_exists($email)) {
            $this->send_response(false, 'This email is already registered.');
            return;
        }

        // Create user (WordPress will hash password automatically)
        $user_id = wp_create_user($email, $pass, $email);

        if (is_wp_error($user_id)) {
            $this->send_response(false, 'Registration failed: ' . $user_id->get_error_message());
            return;
        }

        // Save extra meta
        update_user_meta($user_id, 'first_name', $name);
        update_user_meta($user_id, 'role_type', 'customer');

        // Assign role
        $user = new WP_User($user_id);
        $user->set_role('customer'); // or custom role "customer"

        // Success response
        $this->send_response(true, 'Customer registered successfully! Please login now');
    }

    /**
     * Handles AJAX requests for new Service Provider registrations.
     * Creates a user with the 'provider' role, saves additional metadata, and sends an email verification link.
     */
    public function handle_provider_registration()
    {
        // Security check: verify nonce
        if (!isset($_POST['cosy_nonce']) || !wp_verify_nonce($_POST['cosy_nonce'], 'cosy_provider_register_nonce')) {
            wp_send_json_error('Security check failed. Please refresh the page.');
        }

        // Sanitize required fields
        $username = !empty($_POST['prov_username']) ? sanitize_text_field($_POST['prov_username']) : '';
        $email    = !empty($_POST['prov_email']) ? sanitize_email($_POST['prov_email']) : '';
        $pass     = !empty($_POST['prov_pass']) ? sanitize_text_field($_POST['prov_pass']) : '';

        // Basic validation
        if (empty($username) || empty($email) || empty($pass)) {
            $this->send_response(false, 'All fields are required.');
            return;
        }

        if (email_exists($email)) {
            $this->send_response(false, 'This email is already registered.');
            return;
        }

        // Create user with provider role
        $user_id = wp_insert_user([
            'user_login' => $username,
            'user_pass'  => $pass,
            'user_email' => $email,
            'role'       => 'provider', // actual WP role set
        ]);

        if (is_wp_error($user_id)) {
            $this->send_response(false, 'Registration failed: ' . $user_id->get_error_message());
            return;
        }

        // Save meta (role_type + status)
        update_user_meta($user_id, 'role_type', 'provider');
        update_user_meta($user_id, 'account_status', 'pending'); // Email verification status
        update_user_meta($user_id, 'cosy_provider_status', 'deactive'); // Admin verification status
        $provider_meta = [
            'prov_username' => sanitize_text_field($_POST['prov_username']),
            'prov_mname'    => sanitize_text_field($_POST['prov_mname']),
            'first_name'    => sanitize_text_field($_POST['prov_fname']),
            'last_name'    => sanitize_text_field($_POST['prov_sname']),
            'prov_email'    => sanitize_email($_POST['prov_email']),
            'prov_phone'    => sanitize_text_field($_POST['prov_phone']),
            'prov_address'  => sanitize_textarea_field($_POST['prov_address']),
            'dob'           => sanitize_text_field($_POST['dob']),
            'terms'         => !empty($_POST['terms']) ? 'yes' : 'no',
        ];

        foreach ($provider_meta as $key => $value) {
            update_user_meta($user_id, $key, $value);
        }

        // Generate verification token
        $token = wp_generate_password(32, false);
        update_user_meta($user_id, 'verification_token', $token);

        // Build verification link
        $verify_url = add_query_arg([
            'action' => 'cosy_verify_provider',
            'uid'    => $user_id,
            'token'  => $token,
        ], home_url('/provider-verify'));

        // Send verification email
        wp_mail(
            $email,
            'Confirm Your Provider Account',
            "Hello $username,\n\nClick below to activate your account:\n\n$verify_url"
        );

        // Response
        $this->send_response(true, 'Registration successful! Please check your email to confirm.');
    }

    /**
     * Handles AJAX requests for user login (both customers and providers).
     * Signs the user in securely using WordPress authentication.
     */
    public function handle_login()
    {
        $received_nonce = $_POST['cosy_nonce'] ?? 'MISSING';
        
        // Security check: verify nonce
        if (!isset($_POST['cosy_nonce']) || !wp_verify_nonce($_POST['cosy_nonce'], 'cosy_login_nonce')) {
            wp_send_json_error("Security check failed. Received: $received_nonce. Expected: cosy_login_nonce");
        }

        $creds = [
            'user_login'    => sanitize_text_field($_POST['log']),
            'user_password' => sanitize_text_field($_POST['pwd']),
            'remember'      => true,
        ];

        $user = wp_signon($creds, false);

        if (is_wp_error($user)) {
            $this->send_response(false, 'Invalid username or password.');
        } else {
            // Instead of just message, send home_url for redirect 
            $this->send_response(true, home_url());
            $this->send_response(true, 'Login successful!');
        }
    }

    /**
     * Handles AJAX requests for the "Forgot Password" feature.
     * Generates a new random password and emails it to the user.
     */
    public function handle_forgot_password()
    {
        // Ensure request is POST + correct action
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'cosy_forgot_password') {
            return;
        }

        $email = !empty($_POST['email']) ? sanitize_email($_POST['email']) : '';

        if (empty($email)) {
            $this->send_response(false, 'Email is required.');
            return;
        }

        // Check if user exists
        $user = get_user_by('email', $email);
        if (!$user) {
            $this->send_response(false, 'No account found with this email.');
            return;
        }

        // Generate random password
        $new_pass = wp_generate_password(12, true);

        // Set new password
        wp_set_password($new_pass, $user->ID);

        // Send email
        wp_mail(
            $email,
            'Your New Password',
            'Hello ' . $user->display_name . ",\n\nYour new password is: " . $new_pass . "\n\nPlease login and change it after logging in."
        );

        $this->send_response(true, 'A new password has been sent to your email.');
    }


    /**
     * Verifies a Provider's email address when they click the link in their registration email.
     * Activates their account and automatically logs them in.
     */
    public function handle_provider_verification()
    {
        $uid   = isset($_GET['uid']) ? intval($_GET['uid']) : 0;
        $token = isset($_GET['token']) ? sanitize_text_field($_GET['token']) : '';

        if (empty($uid) || empty($token)) {
            return; // Invalid request
        }

        $saved_token = get_user_meta($uid, 'verification_token', true);

        if ($saved_token && $token === $saved_token) {
            // Activate account
            update_user_meta($uid, 'account_status', 'active');
            delete_user_meta($uid, 'verification_token');

            // Auto login
            wp_set_current_user($uid);
            wp_set_auth_cookie($uid);

            // Redirect after setting cookie
            wp_safe_redirect(home_url('/provider-verify?verified=1'));
            exit;
        } else {
            wp_die('Invalid or expired verification link.');
        }
    }
}
