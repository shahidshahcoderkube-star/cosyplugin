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
            COSY_APPT_URL . 'src/Admin/Assets/js/deactivation.js',
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
                \Cosy\Appointments\Common\LogManager::log(
                    'settings',
                    'deactivation_blocked',
                    __('Unauthorized plugin deactivation attempt was blocked by OTP guard.', 'cosy-appointments')
                );
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
            \Cosy\Appointments\Common\LogManager::log(
                'settings',
                'plugin_deactivated',
                __('Plugin deactivation authorized via OTP and executed.', 'cosy-appointments')
            );
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

        // Prepare HTML email content
        $subject = 'Security Alert: OTP to Deactivate Cosy Appointments';
        
        $message = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Security Alert: OTP to Deactivate Cosy Appointments</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: \'Poppins\', \'Inter\', -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif;">
    <table cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8fafc; padding: 40px 0;">
        <tr>
            <td align="center">
                <table cellpadding="0" cellspacing="0" width="100%" style="max-width: 500px; background-color: #ffffff; border-radius: 20px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03), 0 8px 30px rgba(15, 23, 42, 0.04); overflow: hidden; border: 1px solid #e2e8f0;">
                    <!-- Header Section -->
                    <tr>
                        <td align="center" style="background-color: #ffffff; padding: 40px 40px 10px 40px;">
                            <div style="background-color: #fdf2f8; width: 64px; height: 64px; border-radius: 50%; line-height: 64px; text-align: center; font-size: 32px; display: inline-block;">
                                🔒
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Content Section -->
                    <tr>
                        <td style="padding: 20px 40px; text-align: center;">
                            <h2 style="color: #0f172a; font-size: 24px; font-weight: 700; margin: 0 0 16px 0;">Deactivation Request</h2>
                            <p style="color: #475569; font-size: 15px; line-height: 1.6; margin: 0 0 24px 0;">
                                A request has been initiated to deactivate the <strong>Cosy Appointments</strong> plugin on your WordPress website.
                            </p>
                            <p style="color: #64748b; font-size: 14px; margin: 0 0 12px 0;">
                                Please use the following One-Time Password (OTP) to authorize this action:
                            </p>
                            
                            <!-- Premium Trendy OTP Block -->
                            <div style="background: #fdf2f8; border: 2px dashed #a44390; border-radius: 16px; padding: 18px 24px; font-size: 36px; font-weight: 800; color: #a44390; letter-spacing: 8px; text-align: center; margin: 24px 0; display: inline-block; width: 80%; max-width: 320px;">
                                ' . $otp . '
                            </div>
                            
                            <p style="color: #94a3b8; font-size: 13px; line-height: 1.5; margin: 16px 0 0 0;">
                                This OTP is valid for <strong>5 minutes</strong>. If you did not initiate this request, please ignore this email and secure your administrator credentials.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer Section -->
                    <tr>
                        <td align="center" style="background-color: #f8fafc; padding: 24px 40px; border-top: 1px solid #e2e8f0; text-align: center;">
                            <p style="color: #64748b; font-size: 13px; font-weight: 500; margin: 0 0 4px 0;">
                                Regards,
                            </p>
                            <p style="color: #0f172a; font-size: 14px; font-weight: 700; margin: 0;">
                                Cosy Appointments Security Team
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';

        $headers = array('Content-Type: text/html; charset=UTF-8');

        $mail_sent = function_exists('cosy_send_html_email') 
            ? cosy_send_html_email($admin_email, $subject, $message) 
            : wp_mail($admin_email, $subject, $message, $headers);

        if ($mail_sent) {
            \Cosy\Appointments\Common\LogManager::log(
                'settings',
                'deactivation_otp_sent',
                sprintf(__('Deactivation OTP sent to site administrator email (%s).', 'cosy-appointments'), $admin_email)
            );
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
            \Cosy\Appointments\Common\LogManager::log(
                'settings',
                'deactivation_otp_failed',
                sprintf(__('Failed plugin deactivation OTP verification attempt. Entered OTP: %s', 'cosy-appointments'), $entered_otp)
            );
            wp_send_json_error(['message' => 'Invalid or expired OTP. Please try again.']);
        }

        // OTP is valid! Clear OTP transient
        delete_transient('cosy_deactivation_otp');

        // Authorize deactivation for the next 30 seconds
        set_transient('cosy_deactivation_authorized', 'yes', 30);

        \Cosy\Appointments\Common\LogManager::log(
            'settings',
            'deactivation_otp_verified',
            __('Plugin deactivation OTP verified successfully.', 'cosy-appointments')
        );

        wp_send_json_success(['message' => 'OTP verified successfully.']);
    }}
