<?php

namespace Cosy\Appointments\Admin;

use Cosy\Appointments\Loader;
use Cosy\Appointments\Common\GlobalCommonFunctions;

/**
 * DeactivationHandler Class
 * 
 * Implements high-security deactivation protection using Email OTP.
 * Prevents unauthorized deactivation of the Cosy Appointments plugin.
 */
class DeactivationHandler
{
    use GlobalCommonFunctions;

    public function __construct()
    {
        // Register AJAX handlers dynamically
        $actions = [
            'cosy_send_deactivation_otp'   => 'ajax_send_otp',
            'cosy_verify_deactivation_otp' => 'ajax_verify_otp',
        ];
        $this->register_ajax_handlers($actions, $this);

        // Register intercept hook
        add_action('deactivate_plugin', [$this, 'check_deactivation'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_deactivation_assets']);
    }

    /**
     * Enqueue deactivation assets strictly on the plugins.php screen
     */
    public function enqueue_deactivation_assets($hook): void
    {
        if ($hook !== 'plugins.php') {
            return;
        }

        // SweetAlert2 CSS & JS
        wp_enqueue_style(
            'cosy-sweetalert2',
            'https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css',
            [],
            '11.10.5'
        );

        wp_enqueue_script(
            'cosy-sweetalert2',
            'https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js',
            [],
            '11.10.5',
            true
        );

        // Custom deactivation protection script
        wp_enqueue_script(
            'cosy-deactivation-script',
            COSY_APPT_URL . 'src/Admin/assets/deactivation.js',
            ['jquery', 'cosy-sweetalert2'],
            COSY_APPT_VER,
            true
        );

        // Localize script with necessary data
        wp_localize_script('cosy-deactivation-script', 'cosyDeactivation', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('cosy_deactivation_nonce'),
        ]);
    }

    /**
     * Intercept and block unauthorized plugin deactivation
     */
    public function check_deactivation($plugin, $silent = false): void
    {
        $plugin_base = 'cosy-appointments/cosy-appointments.php';
        
        if (plugin_basename(COSY_APPT_PATH . 'cosy-appointments.php') === $plugin) {
            $authorized = get_transient('cosy_deactivation_authorized');
            
            if ($authorized !== 'yes') {
                wp_die(
                    '<div style="text-align: center; font-family: \'Plus Jakarta Sans\', sans-serif; padding: 20px;">
                        <h2 style="color: #dc3545; font-size: 24px; margin-bottom: 10px;">🔒 Deactivation Protection Active</h2>
                        <p style="color: #6c757d; font-size: 16px; margin-bottom: 20px;">You cannot deactivate Cosy Appointments without verifying the OTP sent to the Administrator\'s email.</p>
                        <a href="' . esc_url(admin_url('plugins.php')) . '" style="background: #007cba; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 5px; font-weight: bold;">Return to Plugins</a>
                     </div>',
                    'Deactivation Blocked',
                    ['response' => 403]
                );
            }
            
            // Authorized! Clear the authorization flag immediately
            delete_transient('cosy_deactivation_authorized');
        }
    }

    /**
     * AJAX handler to send OTP to the Site Administrator
     */
    public function ajax_send_otp(): void
    {
        check_ajax_referer('cosy_deactivation_nonce', 'nonce');

        if (!current_user_can('activate_plugins')) {
            wp_send_json_error(['message' => 'Unauthorized access.']);
        }

        // Generate secure 6-digit OTP
        $otp = rand(100000, 999999);

        // Save OTP transient for 5 minutes
        set_transient('cosy_deactivation_otp', $otp, 5 * MINUTE_IN_SECONDS);

        // Get admin email
        $admin_email = get_option('admin_email');

        // Prepare email
        $subject = 'Security Alert: OTP to Deactivate Cosy Appointments';
        $message = "Hello Admin,\n\n";
        $message .= "A request has been made to deactivate the Cosy Appointments plugin on your website.\n\n";
        $message .= "Please use the following One-Time Password (OTP) to authorize this action:\n";
        $message .= "OTP: " . $otp . "\n\n";
        $message .= "This OTP is valid for 5 minutes. If you did not request this, please secure your credentials.\n\n";
        $message .= "Regards,\nCosy Appointments Security Team";

        $mail_sent = wp_mail($admin_email, $subject, $message);

        if ($mail_sent) {
            wp_send_json_success(['message' => 'OTP has been successfully sent to ' . $admin_email]);
        } else {
            wp_send_json_error(['message' => 'Failed to send email. Please check your mail server configuration.']);
        }
    }

    /**
     * AJAX handler to verify the entered OTP
     */
    public function ajax_verify_otp(): void
    {
        check_ajax_referer('cosy_deactivation_nonce', 'nonce');

        if (!current_user_can('activate_plugins')) {
            wp_send_json_error(['message' => 'Unauthorized access.']);
        }

        $entered_otp = isset($_POST['otp']) ? sanitize_text_field($_POST['otp']) : '';
        $saved_otp = get_transient('cosy_deactivation_otp');

        if (empty($entered_otp) || !$saved_otp || (int)$entered_otp !== (int)$saved_otp) {
            wp_send_json_error(['message' => 'Invalid or expired OTP. Please try again.']);
        }

        // OTP is valid! Clear OTP transient
        delete_transient('cosy_deactivation_otp');

        // Authorize deactivation for the next 30 seconds
        set_transient('cosy_deactivation_authorized', 'yes', 30);

        wp_send_json_success(['message' => 'OTP verified successfully.']);
    }
}
