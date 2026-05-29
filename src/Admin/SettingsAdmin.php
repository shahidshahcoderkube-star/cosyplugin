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
?>
        <div class="wrap cosy-settings-wrap py-4 pe-4">
            <div class="container-fluid bg-white rounded-4 shadow-sm p-4 border border-0">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-gradient text-white rounded-3 p-3 me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px; background: linear-gradient(135deg, #a44390 0%, #8f357b 100%) !important;">
                            <i class="fa-solid fa-credit-card fs-4"></i>
                        </div>
                        <div>
                            <h1 class="wp-heading-inline m-0 fs-2 fw-bold text-dark"><?php _e('Payment Settings', 'cosy-appointments'); ?></h1>
                            <p class="text-muted m-0 mt-1"><?php _e('Configure payment gateways, test modes, and transaction charges.', 'cosy-appointments'); ?></p>
                        </div>
                    </div>
                </div>

                <hr class="border-secondary-subtle my-4">

                <form method="post" action="options.php">
                    <?php settings_fields('cosy_payment_settings'); ?>

                    <div class="row">
                        <!-- Navigation Sidebar -->
                        <div class="col-md-3 mb-4">
                            <div class="nav flex-column nav-pills me-3 gap-2" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                <button class="nav-link active d-flex align-items-center gap-3 py-3 px-4 rounded-3 border-0 text-start" id="v-pills-stripe-tab" data-bs-toggle="pill" data-bs-target="#v-pills-stripe" type="button" role="tab" aria-controls="v-pills-stripe" aria-selected="true">
                                    <i class="fa-brands fa-stripe fs-4 w-20"></i>
                                    <span class="fw-bold">Stripe</span>
                                </button>
                                <button class="nav-link d-flex align-items-center gap-3 py-3 px-4 rounded-3 border-0 text-start" id="v-pills-paypal-tab" data-bs-toggle="pill" data-bs-target="#v-pills-paypal" type="button" role="tab" aria-controls="v-pills-paypal" aria-selected="false">
                                    <i class="fa-brands fa-paypal fs-4 w-20"></i>
                                    <span class="fw-bold">PayPal</span>
                                </button>
                                <button class="nav-link d-flex align-items-center gap-3 py-3 px-4 rounded-3 border-0 text-start" id="v-pills-razorpay-tab" data-bs-toggle="pill" data-bs-target="#v-pills-razorpay" type="button" role="tab" aria-controls="v-pills-razorpay" aria-selected="false">
                                    <i class="fa-solid fa-wallet fs-4 w-20"></i>
                                    <span class="fw-bold">Razorpay</span>
                                </button>
                                <button class="nav-link d-flex align-items-center gap-3 py-3 px-4 rounded-3 border-0 text-start" id="v-pills-worldpay-tab" data-bs-toggle="pill" data-bs-target="#v-pills-worldpay" type="button" role="tab" aria-controls="v-pills-worldpay" aria-selected="false">
                                    <i class="fa-solid fa-globe fs-4 w-20"></i>
                                    <span class="fw-bold">WorldPay</span>
                                </button>
                            </div>
                        </div>

                        <!-- Tab Content Card -->
                        <div class="col-md-9">
                            <div class="tab-content bg-light bg-opacity-50 p-4 rounded-4 border border-secondary-subtle" id="v-pills-tabContent" style="min-height: 300px;">

                                <!-- Stripe Settings Tab -->
                                <div class="tab-pane fade show active" id="v-pills-stripe" role="tabpanel" aria-labelledby="v-pills-stripe-tab">
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <h3 class="fw-bold text-dark m-0 d-flex align-items-center gap-2">
                                            <i class="fa-brands fa-stripe fs-2 text-primary" style="color: #635bff !important;"></i>
                                            Stripe Configuration
                                        </h3>
                                        <div class="d-flex align-items-center m-0">
                                            <label class="cosy-switch">
                                                <input type="checkbox" name="cosy_stripe_test_mode" id="cosy_stripe_test_mode" value="1" <?php checked(1, get_option('cosy_stripe_test_mode')); ?>>
                                                <span class="cosy-slider round"></span>
                                            </label>
                                            <span class="fw-semibold text-secondary ms-2">Sandbox / Test Mode</span>
                                        </div>
                                    </div>
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label for="cosy_stripe_key" class="form-label fw-bold text-secondary">Secret API Key</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-key"></i></span>
                                                <input type="password" class="form-control border-start-0 py-2" name="cosy_stripe_key" id="cosy_stripe_key" value="<?php echo esc_attr(get_option('cosy_stripe_key')); ?>" placeholder="sk_test_...">
                                            </div>
                                            <div class="form-text text-muted mt-1">Enter your Stripe secret key. Keep this confidential.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="cosy_stripe_publishable_key" class="form-label fw-bold text-secondary">Publishable API Key</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-key"></i></span>
                                                <input type="text" class="form-control border-start-0 py-2" name="cosy_stripe_publishable_key" id="cosy_stripe_publishable_key" value="<?php echo esc_attr(get_option('cosy_stripe_publishable_key')); ?>" placeholder="pk_test_...">
                                            </div>
                                            <div class="form-text text-muted mt-1">Enter your Stripe publishable key for secure frontend transactions.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="cosy_stripe_charge" class="form-label fw-bold text-secondary">Transaction Charge (%)</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-percent"></i></span>
                                                <input type="number" step="0.01" class="form-control border-start-0 py-2" name="cosy_stripe_charge" id="cosy_stripe_charge" value="<?php echo esc_attr(get_option('cosy_stripe_charge')); ?>" placeholder="0.00">
                                            </div>
                                            <div class="form-text text-muted mt-1">Specify additional convenience charges for this gateway.</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- PayPal Settings Tab -->
                                <div class="tab-pane fade" id="v-pills-paypal" role="tabpanel" aria-labelledby="v-pills-paypal-tab">
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <h3 class="fw-bold text-dark m-0 d-flex align-items-center gap-2">
                                            <i class="fa-brands fa-paypal fs-2 text-primary" style="color: #003087 !important;"></i>
                                            PayPal Configuration
                                        </h3>
                                        <div class="d-flex align-items-center m-0">
                                            <label class="cosy-switch">
                                                <input type="checkbox" name="cosy_paypal_test_mode" id="cosy_paypal_test_mode" value="1" <?php checked(1, get_option('cosy_paypal_test_mode')); ?>>
                                                <span class="cosy-slider round"></span>
                                            </label>
                                            <span class="fw-semibold text-secondary ms-2">Sandbox / Test Mode</span>
                                        </div>
                                    </div>
                                    <div class="row g-4">
                                        <div class="col-12">
                                            <label for="cosy_paypal_id" class="form-label fw-bold text-secondary">PayPal Client ID</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-user"></i></span>
                                                <input type="text" class="form-control border-start-0 py-2" name="cosy_paypal_id" id="cosy_paypal_id" value="<?php echo esc_attr(get_option('cosy_paypal_id')); ?>" placeholder="Client ID">
                                            </div>
                                            <div class="form-text text-muted mt-1">Provide your PayPal Business Client ID.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="cosy_paypal_charge" class="form-label fw-bold text-secondary">Transaction Charge (%)</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-percent"></i></span>
                                                <input type="number" step="0.01" class="form-control border-start-0 py-2" name="cosy_paypal_charge" id="cosy_paypal_charge" value="<?php echo esc_attr(get_option('cosy_paypal_charge')); ?>" placeholder="0.00">
                                            </div>
                                            <div class="form-text text-muted mt-1">Specify additional convenience charges for this gateway.</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Razorpay Settings Tab -->
                                <div class="tab-pane fade" id="v-pills-razorpay" role="tabpanel" aria-labelledby="v-pills-razorpay-tab">
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <h3 class="fw-bold text-dark m-0 d-flex align-items-center gap-2">
                                            <i class="fa-solid fa-wallet fs-2 text-primary" style="color: #0b4a8f !important;"></i>
                                            Razorpay Configuration
                                        </h3>
                                        <div class="d-flex align-items-center m-0">
                                            <label class="cosy-switch">
                                                <input type="checkbox" name="cosy_razorpay_test_mode" id="cosy_razorpay_test_mode" value="1" <?php checked(1, get_option('cosy_razorpay_test_mode')); ?>>
                                                <span class="cosy-slider round"></span>
                                            </label>
                                            <span class="fw-semibold text-secondary ms-2">Sandbox / Test Mode</span>
                                        </div>
                                    </div>
                                    <div class="row g-4">
                                        <div class="col-12">
                                            <label for="cosy_razorpay_key" class="form-label fw-bold text-secondary">Razorpay Key ID</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-key"></i></span>
                                                <input type="text" class="form-control border-start-0 py-2" name="cosy_razorpay_key" id="cosy_razorpay_key" value="<?php echo esc_attr(get_option('cosy_razorpay_key')); ?>" placeholder="rzp_test_...">
                                            </div>
                                            <div class="form-text text-muted mt-1">Enter your Razorpay API Key ID.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="cosy_razorpay_charge" class="form-label fw-bold text-secondary">Transaction Charge (%)</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-percent"></i></span>
                                                <input type="number" step="0.01" class="form-control border-start-0 py-2" name="cosy_razorpay_charge" id="cosy_razorpay_charge" value="<?php echo esc_attr(get_option('cosy_razorpay_charge')); ?>" placeholder="0.00">
                                            </div>
                                            <div class="form-text text-muted mt-1">Specify additional convenience charges for this gateway.</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- WorldPay Settings Tab -->
                                <div class="tab-pane fade" id="v-pills-worldpay" role="tabpanel" aria-labelledby="v-pills-worldpay-tab">
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <h3 class="fw-bold text-dark m-0 d-flex align-items-center gap-2">
                                            <i class="fa-solid fa-globe fs-2 text-primary" style="color: #0b4a8f !important;"></i>
                                            WorldPay Configuration
                                        </h3>
                                        <div class="d-flex align-items-center m-0">
                                            <label class="cosy-switch">
                                                <input type="checkbox" name="cosy_worldpay_test_mode" id="cosy_worldpay_test_mode" value="1" <?php checked(1, get_option('cosy_worldpay_test_mode')); ?>>
                                                <span class="cosy-slider round"></span>
                                            </label>
                                            <span class="fw-semibold text-secondary ms-2">Sandbox / Test Mode</span>
                                        </div>
                                    </div>
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label for="cosy_worldpay_token" class="form-label fw-bold text-secondary">Service API Token</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-shield"></i></span>
                                                <input type="password" class="form-control border-start-0 py-2" name="cosy_worldpay_token" id="cosy_worldpay_token" value="<?php echo esc_attr(get_option('cosy_worldpay_token')); ?>" placeholder="Token">
                                            </div>
                                            <div class="form-text text-muted mt-1">Enter your WorldPay API Token.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="cosy_worldpay_client_key" class="form-label fw-bold text-secondary">Client Key</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-key"></i></span>
                                                <input type="text" class="form-control border-start-0 py-2" name="cosy_worldpay_client_key" id="cosy_worldpay_client_key" value="<?php echo esc_attr(get_option('cosy_worldpay_client_key')); ?>" placeholder="Client Key">
                                            </div>
                                            <div class="form-text text-muted mt-1">Enter your WorldPay Client Key.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="cosy_worldpay_charge" class="form-label fw-bold text-secondary">Transaction Charge (%)</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-percent"></i></span>
                                                <input type="number" step="0.01" class="form-control border-start-0 py-2" name="cosy_worldpay_charge" id="cosy_worldpay_charge" value="<?php echo esc_attr(get_option('cosy_worldpay_charge')); ?>" placeholder="0.00">
                                            </div>
                                            <div class="form-text text-muted mt-1">Specify additional convenience charges for this gateway.</div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <!-- Action Buttons Card Footer -->
                            <div class="d-flex align-items-center justify-content-end gap-3 mt-4">
                                <button type="submit" class="btn btn-primary d-flex align-items-center gap-2 py-2.5 px-4 rounded-3 border-0 fw-semibold text-white shadow-sm" style="background: linear-gradient(135deg, #a44390 0%, #8f357b 100%) !important; transition: all 0.2s ease;">
                                    <i class="fa-solid fa-circle-check fs-5"></i>
                                    Save Configurations
                                </button>
                            </div>

                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Inject beautiful CSS overrides locally -->
        <style>
            .cosy-settings-wrap .nav-pills .nav-link {
                color: #64748b !important;
                background-color: #f8fafc !important;
                border: 1px solid #e2e8f0 !important;
                transition: all 0.2s ease-in-out;
            }

            .cosy-settings-wrap .nav-pills .nav-link.active {
                color: #ffffff !important;
                background: linear-gradient(135deg, #a44390 0%, #8f357b 100%) !important;
                border-color: #8f357b !important;
                box-shadow: 0 4px 6px -1px rgba(164, 67, 144, 0.15), 0 2px 4px -1px rgba(164, 67, 144, 0.1) !important;
            }

            .cosy-settings-wrap .nav-pills .nav-link:hover:not(.active) {
                background-color: #f1f5f9 !important;
                color: #0f172a !important;
                border-color: #cbd5e1 !important;
            }

            .cosy-settings-wrap .input-group {
                border: 1px solid #dee2e6 !important;
                border-radius: 8px !important;
                overflow: hidden !important;
                background-color: #ffffff !important;
                transition: all 0.2s ease-in-out !important;
            }

            .cosy-settings-wrap .input-group:focus-within {
                border-color: #a44390 !important;
                box-shadow: 0 0 0 0.25rem rgba(164, 67, 144, 0.15) !important;
            }

            .cosy-settings-wrap .input-group-text {
                border: none !important;
                background-color: transparent !important;
                color: #64748b !important;
                padding-right: 0 !important;
            }

            .cosy-settings-wrap .form-control {
                border: none !important;
                box-shadow: none !important;
                padding-left: 12px !important;
            }

            .cosy-settings-wrap .form-control:focus {
                border: none !important;
                box-shadow: none !important;
            }

            .cosy-settings-wrap .w-20 {
                width: 24px;
                text-align: center;
            }

            /* Styling submit transition */
            .cosy-settings-wrap button[type="submit"]:hover {
                opacity: 0.95;
                transform: translateY(-1px);
                box-shadow: 0 6px 12px rgba(164, 67, 144, 0.25) !important;
            }

            /* Custom CSS Switch Toggle to bypass WordPress Admin styles */
            .cosy-switch {
                position: relative;
                display: inline-block;
                width: 48px;
                height: 24px;
                vertical-align: middle;
                margin: 0 !important;
            }

            .cosy-switch input {
                opacity: 0 !important;
                width: 0 !important;
                height: 0 !important;
                position: absolute !important;
                margin: 0 !important;
            }

            .cosy-slider {
                position: absolute;
                cursor: pointer;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: #cbd5e1 !important;
                transition: .3s;
                border: none !important;
                box-shadow: none !important;
            }

            .cosy-slider:before {
                position: absolute;
                content: "";
                height: 18px;
                width: 18px;
                left: 3px;
                bottom: 3px;
                background-color: white !important;
                transition: .3s;
                border-radius: 50% !important;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15) !important;
            }

            .cosy-switch input:checked+.cosy-slider {
                background-color: #a44390 !important;
            }

            .cosy-switch input:checked+.cosy-slider:before {
                transform: translateX(24px) !important;
            }

            .cosy-slider.round {
                border-radius: 24px !important;
            }
        </style>
<?php
    }
}
