<?php

namespace Cosy\Appointments\Admin;

class SettingsAdmin
{
    public function register($loader): void
    {
        $loader->add_action('admin_menu', $this, 'add_settings_page');
        $loader->add_action('admin_init', $this, 'register_settings');
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


        // Razorpay
        register_setting('cosy_payment_settings', 'cosy_razorpay_key', [
            'sanitize_callback' => 'sanitize_text_field'
        ]);
        register_setting('cosy_payment_settings', 'cosy_razorpay_test_mode', [
            'sanitize_callback' => 'absint'
        ]);
        register_setting('cosy_payment_settings', 'cosy_razorpay_charge', [
            'sanitize_callback' => [$this, 'sanitize_charge']
        ]);

        // PayPal
        register_setting('cosy_payment_settings', 'cosy_paypal_id', [
            'sanitize_callback' => 'sanitize_text_field'
        ]);
        register_setting('cosy_payment_settings', 'cosy_paypal_test_mode', [
            'sanitize_callback' => 'absint'
        ]);
        register_setting('cosy_payment_settings', 'cosy_paypal_charge', [
            'sanitize_callback' => [$this, 'sanitize_charge']
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
