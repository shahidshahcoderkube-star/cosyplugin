<?php

namespace Cosy\Appointments\Admin;

class SettingsAdmin
{
    /**
     * REGISTERS SETTINGS HOOKS
     * 
     * USE CASE:
     * Called during plugin initialization to register admin settings menu and options.
     * 
     * HOW TO USE:
     * (new SettingsAdmin())->register($loader);
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Attaches add_settings_page to 'admin_menu' hook.
     * 2. Attaches register_settings to 'admin_init' hook.
     * 3. Attaches enqueue_settings_assets to 'admin_enqueue_scripts' hook.
     * 
     * @param Loader $loader Plugin loader instance.
     */
    public function register($loader): void
    {
        $loader->add_action('admin_menu', $this, 'add_settings_page');
        $loader->add_action('admin_init', $this, 'register_settings');
        $loader->add_action('admin_enqueue_scripts', $this, 'enqueue_settings_assets');
        $loader->add_action('wp_ajax_cosy_test_smtp_email', $this, 'ajax_send_test_email');
    }

    /**
     * ENQUEUES SETTINGS MEDIA ASSETS
     * 
     * USE CASE:
     * Enqueues WordPress WP Media Uploader scripts on the plugin settings screen.
     * 
     * HOW TO USE:
     * Triggered automatically during 'admin_enqueue_scripts'.
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Checks if current admin page hook contains 'cosy-settings'.
     * 2. Calls wp_enqueue_media() if matching settings screen.
     * 
     * @param string $hook Admin page hook string.
     */
    public function enqueue_settings_assets($hook): void
    {
        if (strpos($hook, 'cosy-settings') !== false) {
            wp_enqueue_media();
        }
    }

    /**
     * ADDS SETTINGS & DOCUMENTATION SUBMENU PAGES
     * 
     * USE CASE:
     * Adds Settings and Documentation pages under 'CC Booking' menu.
     * 
     * HOW TO USE:
     * Triggered automatically during 'admin_menu'.
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Calls add_submenu_page() for 'cosy-settings' with capability 'manage_options'.
     * 2. Calls add_submenu_page() for 'cosy-documentation' with capability 'manage_cosy_appointments'.
     */
    public function add_settings_page(): void
    {
        add_submenu_page(
            'cosy-booking-dashboard',
            __('Settings', 'cosy-appointments'),
            __('Settings', 'cosy-appointments'),
            'manage_options',
            'cosy-settings',
            [$this, 'render_settings']
        );

        add_submenu_page(
            'cosy-booking-dashboard',
            __('Documentation', 'cosy-appointments'),
            __('Documentation', 'cosy-appointments'),
            'manage_cosy_appointments',
            'cosy-documentation',
            [$this, 'render_documentation']
        );
    }

    /**
     * RENDERS DOCUMENTATION PAGE
     * 
     * USE CASE:
     * Callback renderer for plugin documentation page.
     * 
     * HOW TO USE:
     * Triggered when visiting 'cosy-documentation' admin page.
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Buffers output and includes documentation.php template.
     */
    public function render_documentation(): void
    {
        ob_start();
        include COSY_APPT_PATH . 'src/Admin/Backend/documentation.php';
        echo ob_get_clean();
    }

    public function register_settings(): void
    {
        // AI Search Settings
        register_setting('cosy_payment_settings', 'cosy_ai_provider', [
            'sanitize_callback' => 'sanitize_text_field'
        ]);
        register_setting('cosy_payment_settings', 'cosy_ai_api_key', [
            'sanitize_callback' => 'sanitize_text_field'
        ]);

        // Branding & Page Image Settings
        register_setting('cosy_payment_settings', 'cosy_registration_image_url', [
            'sanitize_callback' => 'esc_url_raw'
        ]);
        register_setting('cosy_payment_settings', 'cosy_login_image_url', [
            'sanitize_callback' => 'esc_url_raw'
        ]);

        // Email Signature Settings
        register_setting('cosy_payment_settings', 'cosy_sig_logo_url', [
            'sanitize_callback' => 'esc_url_raw'
        ]);
        register_setting('cosy_payment_settings', 'cosy_sig_name', [
            'sanitize_callback' => 'sanitize_text_field'
        ]);
        register_setting('cosy_payment_settings', 'cosy_sig_title', [
            'sanitize_callback' => 'sanitize_text_field'
        ]);
        register_setting('cosy_payment_settings', 'cosy_sig_phone', [
            'sanitize_callback' => 'sanitize_text_field'
        ]);
        register_setting('cosy_payment_settings', 'cosy_sig_email', [
            'sanitize_callback' => 'sanitize_email'
        ]);
        register_setting('cosy_payment_settings', 'cosy_sig_website', [
            'sanitize_callback' => 'esc_url_raw'
        ]);
        register_setting('cosy_payment_settings', 'cosy_sig_address', [
            'sanitize_callback' => 'sanitize_text_field'
        ]);
        register_setting('cosy_payment_settings', 'cosy_sig_linkedin', [
            'sanitize_callback' => 'esc_url_raw'
        ]);
        register_setting('cosy_payment_settings', 'cosy_sig_facebook', [
            'sanitize_callback' => 'esc_url_raw'
        ]);
        register_setting('cosy_payment_settings', 'cosy_sig_instagram', [
            'sanitize_callback' => 'esc_url_raw'
        ]);
        register_setting('cosy_payment_settings', 'cosy_sig_twitter', [
            'sanitize_callback' => 'esc_url_raw'
        ]);
        register_setting('cosy_payment_settings', 'cosy_sig_tiktok', [
            'sanitize_callback' => 'esc_url_raw'
        ]);
        register_setting('cosy_payment_settings', 'cosy_sig_youtube', [
            'sanitize_callback' => 'esc_url_raw'
        ]);
        register_setting('cosy_payment_settings', 'cosy_sig_enabled', [
            'sanitize_callback' => 'absint'
        ]);

        // Media Upload settings
        register_setting('cosy_media_settings', 'cosy_max_video_upload_size', [
            'sanitize_callback' => 'absint'
        ]);

        // Active Gateway Selection
        register_setting('cosy_payment_settings', 'cosy_default_payment_gateway', [
            'sanitize_callback' => 'sanitize_text_field'
        ]);

        // WorldPay Settings
        register_setting('cosy_payment_settings', 'cosy_worldpay_inst_id', [
            'sanitize_callback' => 'sanitize_text_field'
        ]);
        register_setting('cosy_payment_settings', 'cosy_worldpay_token', [
            'sanitize_callback' => 'sanitize_text_field'
        ]);
        register_setting('cosy_payment_settings', 'cosy_worldpay_password', [
            'sanitize_callback' => 'sanitize_text_field'
        ]);
        register_setting('cosy_payment_settings', 'cosy_worldpay_client_key', [
            'sanitize_callback' => 'sanitize_text_field'
        ]);
        register_setting('cosy_payment_settings', 'cosy_worldpay_test_mode', [
            'sanitize_callback' => 'absint'
        ]);
        register_setting('cosy_payment_settings', 'cosy_worldpay_charge', [
            'sanitize_callback' => [$this, 'sanitize_charge']
        ]);

        // Dynamic SMTP Configuration Settings
        register_setting('cosy_payment_settings', 'cosy_smtp_enabled', [
            'sanitize_callback' => 'absint'
        ]);
        register_setting('cosy_payment_settings', 'cosy_smtp_host', [
            'sanitize_callback' => 'sanitize_text_field'
        ]);
        register_setting('cosy_payment_settings', 'cosy_smtp_port', [
            'sanitize_callback' => 'absint'
        ]);
        register_setting('cosy_payment_settings', 'cosy_smtp_encryption', [
            'sanitize_callback' => 'sanitize_text_field'
        ]);
        register_setting('cosy_payment_settings', 'cosy_smtp_auth', [
            'sanitize_callback' => 'absint'
        ]);
        register_setting('cosy_payment_settings', 'cosy_smtp_user', [
            'sanitize_callback' => 'sanitize_text_field'
        ]);
        register_setting('cosy_payment_settings', 'cosy_smtp_pass', [
            'sanitize_callback' => 'sanitize_text_field'
        ]);
        register_setting('cosy_payment_settings', 'cosy_smtp_from_name', [
            'sanitize_callback' => 'sanitize_text_field'
        ]);
        register_setting('cosy_payment_settings', 'cosy_smtp_from_email', [
            'sanitize_callback' => 'sanitize_email'
        ]);
    }

    public function sanitize_charge($value)
    {
        return empty($value) ? '0.00' : number_format((float)$value, 2, '.', '');
    }

    /**
     * AJAX HANDLER FOR SENDING TEST SMTP EMAIL
     */
    public function ajax_send_test_email(): void
    {
        check_ajax_referer('cosy_test_smtp_nonce', 'security');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'cosy-appointments')]);
        }

        $test_to = sanitize_email($_POST['test_email'] ?? '');
        if (!is_email($test_to)) {
            wp_send_json_error(['message' => __('Please enter a valid email address.', 'cosy-appointments')]);
        }

        $subject = sprintf(__('CosyChats SMTP Test - %s', 'cosy-appointments'), date('d M Y, H:i'));
        $heading = __('SMTP Configuration Test Successful!', 'cosy-appointments');
        $content = "<p>" . __('This is a test email sent from your CosyChats CC Booking settings to verify your SMTP configuration.', 'cosy-appointments') . "</p><p>" . sprintf(__('Dispatched at %s via dynamic SMTP credentials.', 'cosy-appointments'), date('Y-m-d H:i:s')) . "</p>";

        $sent = cosy_send_html_email($test_to, $subject, $heading, $content);

        if ($sent) {
            \Cosy\Appointments\Common\LogManager::log('email', 'test_email_sent', "SMTP Test email successfully sent to {$test_to}");
            wp_send_json_success(['message' => sprintf(__('Test email sent successfully to %s!', 'cosy-appointments'), $test_to)]);
        } else {
            \Cosy\Appointments\Common\LogManager::log('email', 'test_email_failed', "SMTP Test email failed to {$test_to}");
            wp_send_json_error(['message' => __('Failed to send test email. Please check your SMTP host, port, credentials, and error log.', 'cosy-appointments')]);
        }
    }

    public function render_settings(): void
    {
        ob_start();
        include COSY_APPT_PATH . 'src/Admin/Backend/settings-page.php';
        echo ob_get_clean();
    }
}
