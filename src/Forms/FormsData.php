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
            'cosy_customer_register'        => 'handle_customer_registration',
            'cosy_provider_register'        => 'handle_provider_registration',
            'cosy_login'                    => 'handle_login',
            'cosy_forgot_password'          => 'handle_forgot_password',
            'cosy_customer_profile_update'  => 'handle_customer_profile_update',
            'cosy_customer_password_update' => 'handle_customer_password_update',
        ];
        $this->register_ajax_handlers($actions, $this);

        // Hook for non-AJAX verification (email link)
        add_action('init', [$this, 'handle_provider_verification']);

        // Filter to prevent unverified logins
        add_filter('wp_authenticate_user', [$this, 'restrict_unverified_login'], 10, 2);
    }

    /**
     * Blocks login attempts for users whose email address is not verified.
     */
    public function restrict_unverified_login($user, $password)
    {
        if (is_wp_error($user)) {
            return $user;
        }

        // Get account verification status
        $status = get_user_meta($user->ID, 'account_status', true);

        // If the account is pending verification, reject the login
        if ($status === 'pending') {
            return new \WP_Error('email_not_verified', __('Your email is not verified. Please check your inbox for the activation link.', 'cosy-appointments'));
        } elseif ($status === 'deactive') {
            return new \WP_Error('account_deactivated', __('Your account has been deactivated by the administrator.', 'cosy-appointments'));
        }

        return $user;
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
            wp_send_json_error(__('Security check failed. Please refresh the page.', 'cosy-appointments'));
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
            $this->send_response(false, __('All fields are required.', 'cosy-appointments'));
            return;
        }

        if (!is_email($email)) {
            $this->send_response(false, __('Please enter a valid email address.', 'cosy-appointments'));
            return;
        }

        // Check if email already exists
        if (email_exists($email)) {
            $this->send_response(false, __('This email is already registered.', 'cosy-appointments'));
            return;
        }

        // Create user (WordPress will hash password automatically)
        $user_id = wp_create_user($email, $pass, $email);

        if (is_wp_error($user_id)) {
            $this->send_response(false, __('Registration failed: ', 'cosy-appointments') . $user_id->get_error_message());
            return;
        }

        // Save extra meta
        update_user_meta($user_id, 'first_name', $name);
        update_user_meta($user_id, 'role_type', 'customer');
        update_user_meta($user_id, 'account_status', 'pending'); // Set customer status to pending

        // Assign role
        $user = new WP_User($user_id);
        $user->set_role('customer'); // assign customer role

        // Send verification email
        $this->send_verification_email($user_id, 'customer');

        // Success response
        $this->send_response(true, __('Registration successful! Please check your email to verify your account.', 'cosy-appointments'));
    }

    /**
     * Handles AJAX requests for new Service Provider registrations.
     * Creates a user with the 'provider' role, saves additional metadata, and sends an email verification link.
     */
    public function handle_provider_registration()
    {
        // Security check: verify nonce
        if (!isset($_POST['cosy_nonce']) || !wp_verify_nonce($_POST['cosy_nonce'], 'cosy_provider_register_nonce')) {
            wp_send_json_error(__('Security check failed. Please refresh the page.', 'cosy-appointments'));
        }

        // Sanitize required fields
        $username = !empty($_POST['prov_username']) ? sanitize_user($_POST['prov_username'], true) : '';
        $email    = !empty($_POST['prov_email']) ? sanitize_email($_POST['prov_email']) : '';
        $pass     = !empty($_POST['prov_pass']) ? sanitize_text_field($_POST['prov_pass']) : '';

        // Basic validation
        if (empty($username) || empty($email) || empty($pass)) {
            $this->send_response(false, __('All fields are required.', 'cosy-appointments'));
            return;
        }

        if (!is_email($email)) {
            $this->send_response(false, __('Please enter a valid email address.', 'cosy-appointments'));
            return;
        }

        if (username_exists($username)) {
            $this->send_response(false, __('This username is already taken.', 'cosy-appointments'));
            return;
        }

        if (email_exists($email)) {
            $this->send_response(false, __('This email is already registered.', 'cosy-appointments'));
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
            $this->send_response(false, __('Registration failed: ', 'cosy-appointments') . $user_id->get_error_message());
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

        // Send verification email
        $this->send_verification_email($user_id, 'provider');

        // Response
        $this->send_response(true, __('Registration successful! Please check your email to confirm.', 'cosy-appointments'));
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
            wp_send_json_error(sprintf(__('Security check failed. Received: %s. Expected: cosy_login_nonce', 'cosy-appointments'), $received_nonce));
        }

        $creds = [
            'user_login'    => sanitize_text_field($_POST['log']),
            'user_password' => sanitize_text_field($_POST['pwd']),
            'remember'      => true,
        ];

        $user = wp_signon($creds, false);

        if (is_wp_error($user)) {
            $error_code = $user->get_error_code();
            if ($error_code === 'email_not_verified' || $error_code === 'account_deactivated') {
                $this->send_response(false, $user->get_error_message());
            } else {
                $this->send_response(false, __('Invalid username or password.', 'cosy-appointments'));
            }
        } else {
            // Determine redirect URL based on user role to bypass homepage cache
            $roles = (array) $user->roles;
            $redirect_url = home_url();

            if (in_array('administrator', $roles)) {
                $redirect_url = admin_url();
            } elseif (in_array('provider', $roles)) {
                $redirect_url = home_url('/provider-dashboard/');
            } elseif (in_array('customer', $roles)) {
                $redirect_url = home_url('/customer-profile/');
            }

            $this->send_response(true, $redirect_url);
        }
    }

    /**
     * Handles AJAX requests for the "Forgot Password" feature.
     * Generates a password reset key and emails it to the user.
     */
    public function handle_forgot_password()
    {
        // Ensure request is POST + correct action
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'cosy_forgot_password') {
            return;
        }

        $email = !empty($_POST['email']) ? sanitize_email($_POST['email']) : '';

        if (empty($email)) {
            $this->send_response(false, __('Email is required.', 'cosy-appointments'));
            return;
        }

        // Check if user exists
        $user = get_user_by('email', $email);
        if (!$user) {
            $this->send_response(false, __('No account found with this email.', 'cosy-appointments'));
            return;
        }

        // Generate password reset key using WordPress built-in secure function
        $key = get_password_reset_key($user);
        if (is_wp_error($key)) {
            $this->send_response(false, __('Unable to generate reset link. Please try again.', 'cosy-appointments'));
            return;
        }

        // Build the secure reset URL using WordPress's core endpoint
        $reset_url = add_query_arg([
            'action' => 'rp',
            'key'    => $key,
            'login'  => rawurlencode($user->user_login),
        ], wp_login_url());

        // Send email
        $subject = __('Password Reset Request', 'cosy-appointments');
        $html_content = "
            <p>Hello <strong>" . esc_html($user->display_name) . "</strong>,</p>
            <p>You requested a password reset for your account. Please click the button below to set a new password:</p>
            <p style='text-align: center; margin: 30px 0;'>
                <a href='" . esc_url($reset_url) . "' style='background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%); color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 50px; font-weight: 600; display: inline-block; box-shadow: 0 4px 12px rgba(164, 67, 144, 0.2);'>Reset Password</a>
            </p>
            <p style='font-size: 13px; color: #64748b; margin-top: 25px;'>If you did not request this reset, you can safely ignore this email. Your password will remain unchanged.</p>
            <p style='font-size: 13px; word-break: break-all; color: #a44390;'><a href='" . esc_url($reset_url) . "' style='color: #a44390; text-decoration: none;'>" . esc_html($reset_url) . "</a></p>
        ";
        $mail_sent = cosy_send_html_email($email, $subject, $subject, $html_content);

        if ($mail_sent) {
            $this->send_response(true, __('A password reset link has been sent to your email.', 'cosy-appointments'));
        } else {
            $this->send_response(false, __('Failed to send password reset email.', 'cosy-appointments'));
        }
    }

    /**
     * Verifies a User's email address when they click the link in their registration email.
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
            wp_die(__('Invalid or expired verification link.', 'cosy-appointments'));
        }
    }

    /**
     * Handles AJAX requests to update Customer profile information.
     */
    public function handle_customer_profile_update()
    {
        // Security check: verify nonce
        if (!isset($_POST['cosy_profile_nonce']) || !wp_verify_nonce($_POST['cosy_profile_nonce'], 'cosy_customer_profile_nonce')) {
            wp_send_json_error(__('Security check failed. Please refresh the page.', 'cosy-appointments'));
        }

        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(__('You must be logged in to update your profile.', 'cosy-appointments'));
        }

        $first_name = !empty($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
        $last_name  = !empty($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
        $email      = !empty($_POST['email']) ? sanitize_email($_POST['email']) : '';

        if (empty($first_name) || empty($email)) {
            $this->send_response(false, __('First Name and Email are required.', 'cosy-appointments'));
            return;
        }

        if (!is_email($email)) {
            $this->send_response(false, __('Please enter a valid email address.', 'cosy-appointments'));
            return;
        }

        // Check if email is already used by another user
        $existing_user = get_user_by('email', $email);
        if ($existing_user && $existing_user->ID !== $user_id) {
            $this->send_response(false, __('This email address is already in use.', 'cosy-appointments'));
            return;
        }

        // Update WordPress user data
        $userdata = [
            'ID'         => $user_id,
            'user_email' => $email,
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'display_name' => $first_name . ' ' . $last_name,
        ];

        $update_status = wp_update_user($userdata);

        if (is_wp_error($update_status)) {
            $this->send_response(false, __('Profile update failed: ', 'cosy-appointments') . $update_status->get_error_message());
        } else {
            // Save metadata
            update_user_meta($user_id, 'first_name', $first_name);
            update_user_meta($user_id, 'last_name', $last_name);
            $this->send_response(true, __('Profile details updated successfully!', 'cosy-appointments'));
        }
    }

    /**
     * Handles AJAX requests to update Customer password.
     */
    public function handle_customer_password_update()
    {
        // Security check: verify nonce
        if (!isset($_POST['cosy_password_nonce']) || !wp_verify_nonce($_POST['cosy_password_nonce'], 'cosy_customer_password_nonce')) {
            wp_send_json_error(__('Security check failed. Please refresh the page.', 'cosy-appointments'));
        }

        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(__('You must be logged in to update your password.', 'cosy-appointments'));
        }

        $new_pass     = !empty($_POST['new_password']) ? $_POST['new_password'] : '';
        $confirm_pass = !empty($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

        if (empty($new_pass) || empty($confirm_pass)) {
            $this->send_response(false, __('Both password fields are required.', 'cosy-appointments'));
            return;
        }

        // Validate new password length (minimum 6 chars)
        if (strlen($new_pass) < 6) {
            $this->send_response(false, __('New password must be at least 6 characters long.', 'cosy-appointments'));
            return;
        }

        // Check if passwords match
        if ($new_pass !== $confirm_pass) {
            $this->send_response(false, __('New passwords do not match.', 'cosy-appointments'));
            return;
        }

        // Update password
        wp_set_password($new_pass, $user_id);
        
        // Log user back in automatically as wp_set_password clears session/auth cookies
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);

        $this->send_response(true, __('Password changed successfully!', 'cosy-appointments'));
    }
}
