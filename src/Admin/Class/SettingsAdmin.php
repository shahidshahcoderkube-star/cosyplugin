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
    }

    public function register_settings(): void
    {
        // Stripe
        register_setting('cosy_payment_settings', 'cosy_stripe_key');
        register_setting('cosy_payment_settings', 'cosy_stripe_test_mode');
        register_setting('cosy_payment_settings', 'cosy_stripe_charge');

        // Razorpay
        register_setting('cosy_payment_settings', 'cosy_razorpay_key');
        register_setting('cosy_payment_settings', 'cosy_razorpay_test_mode');
        register_setting('cosy_payment_settings', 'cosy_razorpay_charge');

        // PayPal
        register_setting('cosy_payment_settings', 'cosy_paypal_id');
        register_setting('cosy_payment_settings', 'cosy_paypal_test_mode');
        register_setting('cosy_payment_settings', 'cosy_paypal_charge');

        // WorldPay
        register_setting('cosy_payment_settings', 'cosy_worldpay_token');
        register_setting('cosy_payment_settings', 'cosy_worldpay_client_key');
        register_setting('cosy_payment_settings', 'cosy_worldpay_test_mode');
        register_setting('cosy_payment_settings', 'cosy_worldpay_charge');
    }

    public function render_settings(): void
    {
        echo '<div class="wrap"><h1>' . __('Payment Settings', 'cosy-appointments') . '</h1>';
        echo '<form method="post" action="options.php">';
        settings_fields('cosy_payment_settings');

        echo '<h2>' . __('WorldPay Settings', 'cosy-appointments') . '</h2>';
        echo '<table class="form-table"><tbody>';
        echo '<tr><th scope="row">Test Mode</th><td><input type="checkbox" name="cosy_worldpay_test_mode" value="1" ' . checked(1, get_option('cosy_worldpay_test_mode'), false) . '/> Enable</td></tr>';
        echo '<tr><th scope="row">API Token</th><td><input type="text" name="cosy_worldpay_token" value="' . esc_attr(get_option('cosy_worldpay_token')) . '" class="regular-text"/></td></tr>';
        echo '<tr><th scope="row">Client Key</th><td><input type="text" name="cosy_worldpay_client_key" value="' . esc_attr(get_option('cosy_worldpay_client_key')) . '" class="regular-text"/></td></tr>';
        echo '<tr><th scope="row">Transaction Charge (%)</th><td><input type="number" name="cosy_worldpay_charge" value="' . esc_attr(get_option('cosy_worldpay_charge')) . '" /></td></tr>';
        echo '</tbody></table>';

        echo '<h2>' . __('Stripe Settings', 'cosy-appointments') . '</h2>';
        echo '<table class="form-table"><tbody>';
        echo '<tr><th scope="row">Test Mode</th><td><input type="checkbox" name="cosy_stripe_test_mode" value="1" ' . checked(1, get_option('cosy_stripe_test_mode'), false) . '/> Enable</td></tr>';
        echo '<tr><th scope="row">API Key</th><td><input type="text" name="cosy_stripe_key" value="' . esc_attr(get_option('cosy_stripe_key')) . '" class="regular-text"/></td></tr>';
        echo '<tr><th scope="row">Transaction Charge (%)</th><td><input type="number" name="cosy_stripe_charge" value="' . esc_attr(get_option('cosy_stripe_charge')) . '" /></td></tr>';
        echo '</tbody></table>';

        echo '<h2>' . __('Razorpay Settings', 'cosy-appointments') . '</h2>';
        echo '<table class="form-table"><tbody>';
        echo '<tr><th scope="row">Test Mode</th><td><input type="checkbox" name="cosy_razorpay_test_mode" value="1" ' . checked(1, get_option('cosy_razorpay_test_mode'), false) . '/> Enable</td></tr>';
        echo '<tr><th scope="row">API Key</th><td><input type="text" name="cosy_razorpay_key" value="' . esc_attr(get_option('cosy_razorpay_key')) . '" class="regular-text"/></td></tr>';
        echo '<tr><th scope="row">Transaction Charge (%)</th><td><input type="number" name="cosy_razorpay_charge" value="' . esc_attr(get_option('cosy_razorpay_charge')) . '" /></td></tr>';
        echo '</tbody></table>';

        echo '<h2>' . __('PayPal Settings', 'cosy-appointments') . '</h2>';
        echo '<table class="form-table"><tbody>';
        echo '<tr><th scope="row">Test Mode</th><td><input type="checkbox" name="cosy_paypal_test_mode" value="1" ' . checked(1, get_option('cosy_paypal_test_mode'), false) . '/> Enable</td></tr>';
        echo '<tr><th scope="row">Client ID</th><td><input type="text" name="cosy_paypal_id" value="' . esc_attr(get_option('cosy_paypal_id')) . '" class="regular-text"/></td></tr>';
        echo '<tr><th scope="row">Transaction Charge (%)</th><td><input type="number" name="cosy_paypal_charge" value="' . esc_attr(get_option('cosy_paypal_charge')) . '" /></td></tr>';
        echo '</tbody></table>';

        submit_button();
        echo '</form></div>';
    }
}
