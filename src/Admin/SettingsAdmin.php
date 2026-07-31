<?php

namespace Cosy\Appointments\Admin;

class SettingsAdmin
{
    public function register($loader): void
    {
        $loader->add_action('admin_menu', $this, 'add_settings_page');
        $loader->add_action('admin_init', $this, 'register_settings');
        $loader->add_action('admin_enqueue_scripts', $this, 'enqueue_settings_assets');
    }

    public function enqueue_settings_assets($hook): void
    {
        if (strpos($hook, 'cosy-settings') !== false) {
            wp_enqueue_media();
        }
    }

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

    public function render_documentation(): void
    {
        ob_start();
        include COSY_APPT_PATH . 'src/Admin/Backend/documentation.php';
        echo ob_get_clean();
    }

    public function register_settings(): void
    {
        // Stripe
        register_setting('cosy_payment_settings', 'cosy_stripe_key', [
            'sanitize_callback' => [$this, 'sanitize_stripe_secret_key']
        ]);
        register_setting('cosy_payment_settings', 'cosy_stripe_publishable_key', [
            'sanitize_callback' => [$this, 'sanitize_stripe_publishable_key']
        ]);
        register_setting('cosy_payment_settings', 'cosy_stripe_test_mode', [
            'sanitize_callback' => 'absint'
        ]);
        register_setting('cosy_payment_settings', 'cosy_stripe_charge', [
            'sanitize_callback' => [$this, 'sanitize_charge']
        ]);
        register_setting('cosy_payment_settings', 'cosy_stripe_currency', [
            'sanitize_callback' => 'sanitize_text_field'
        ]);

        // General Booking Fee Settings
        register_setting('cosy_payment_settings', 'cosy_service_fee_type', [
            'sanitize_callback' => 'sanitize_text_field'
        ]);
        register_setting('cosy_payment_settings', 'cosy_service_fee_value', [
            'sanitize_callback' => [$this, 'sanitize_charge']
        ]);

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
        register_setting('cosy_payment_settings', 'cosy_sig_enabled', [
            'sanitize_callback' => 'absint'
        ]);

        // Media Upload settings
        register_setting('cosy_media_settings', 'cosy_max_video_upload_size', [
            'sanitize_callback' => 'absint'
        ]);

        // WorldPay
        register_setting('cosy_payment_settings', 'cosy_worldpay_token', [
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
    }

    public function sanitize_stripe_secret_key($value)
    {
        $value = sanitize_text_field($value);
        if (!empty($value) && !preg_match('/^(sk_live_|sk_test_|rk_live_|rk_test_)/', $value)) {
            add_settings_error('cosy_stripe_key', 'invalid_stripe_key', 'Stripe Secret Key must start with sk_live_ or sk_test_.', 'error');
            return get_option('cosy_stripe_key');
        }
        return $value;
    }

    public function sanitize_stripe_publishable_key($value)
    {
        $value = sanitize_text_field($value);
        if (!empty($value) && !preg_match('/^(pk_live_|pk_test_)/', $value)) {
            add_settings_error('cosy_stripe_publishable_key', 'invalid_stripe_pk', 'Stripe Publishable Key must start with pk_live_ or pk_test_.', 'error');
            return get_option('cosy_stripe_publishable_key');
        }
        return $value;
    }

    public function sanitize_charge($value)
    {
        return empty($value) ? '0.00' : number_format((float)$value, 2, '.', '');
    }

    public function render_settings(): void
    {
        ob_start();
        include COSY_APPT_PATH . 'src/Admin/Backend/settings-page.php';
        echo ob_get_clean();
    }
}
