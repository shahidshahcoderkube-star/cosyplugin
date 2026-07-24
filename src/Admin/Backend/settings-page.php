<?php
/**
 * Settings Page Template View
 *
 * Renders the HTML and CSS for the plugin's settings tab.
 * Included by SettingsAdmin::render_settings()
 */

defined('ABSPATH') || exit;
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
                        <button class="nav-link d-flex align-items-center gap-3 py-3 px-4 rounded-3 border-0 text-start" id="v-pills-worldpay-tab" data-bs-toggle="pill" data-bs-target="#v-pills-worldpay" type="button" role="tab" aria-controls="v-pills-worldpay" aria-selected="false">
                            <i class="fa-solid fa-globe fs-4 w-20"></i>
                            <span class="fw-bold">WorldPay</span>
                        </button>
                        <button class="nav-link d-flex align-items-center gap-3 py-3 px-4 rounded-3 border-0 text-start" id="v-pills-fees-tab" data-bs-toggle="pill" data-bs-target="#v-pills-fees" type="button" role="tab" aria-controls="v-pills-fees" aria-selected="false">
                            <i class="fa-solid fa-calculator fs-4 w-20"></i>
                            <span class="fw-bold">Booking Fees</span>
                        </button>
                        <button class="nav-link d-flex align-items-center gap-3 py-3 px-4 rounded-3 border-0 text-start" id="v-pills-aisearch-tab" data-bs-toggle="pill" data-bs-target="#v-pills-aisearch" type="button" role="tab" aria-controls="v-pills-aisearch" aria-selected="false">
                            <i class="fa-solid fa-brain fs-4 w-20"></i>
                            <span class="fw-bold">AI Search Engine</span>
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
                                <div class="col-md-6">
                                    <label for="cosy_stripe_currency" class="form-label fw-bold text-secondary">Payment Currency</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-coins"></i></span>
                                        <?php
                                        $selected_currency = cosy_get_currency_code();
                                        $currencies = [
                                            'USD' => 'USD - United States Dollar ($)',
                                            'EUR' => 'EUR - Euro (€)',
                                            'GBP' => 'GBP - British Pound (£)',
                                            'INR' => 'INR - Indian Rupee (₹)',
                                            'AUD' => 'AUD - Australian Dollar (A$)',
                                            'CAD' => 'CAD - Canadian Dollar (C$)',
                                        ];
                                        ?>
                                        <select class="form-select border-start-0 py-2" name="cosy_stripe_currency" id="cosy_stripe_currency">
                                            <?php foreach ($currencies as $code => $name) : ?>
                                                <option value="<?php echo esc_attr($code); ?>" <?php selected($selected_currency, $code); ?>>
                                                    <?php echo esc_html($name); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-text text-muted mt-1">Select the payment currency for all transactions.</div>
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

                        <!-- Booking Fees Settings Tab -->
                        <div class="tab-pane fade" id="v-pills-fees" role="tabpanel" aria-labelledby="v-pills-fees-tab">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h3 class="fw-bold text-dark m-0 d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-calculator fs-2 text-primary" style="color: #a44390 !important;"></i>
                                    Booking Fees Configuration
                                </h3>
                            </div>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="cosy_service_fee_type" class="form-label fw-bold text-secondary">Service Fee Type</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-gears"></i></span>
                                        <?php
                                        $fee_type = get_option('cosy_service_fee_type', 'flat');
                                        ?>
                                        <select class="form-select border-start-0 py-2" name="cosy_service_fee_type" id="cosy_service_fee_type">
                                            <option value="flat" <?php selected($fee_type, 'flat'); ?>>Flat Fee</option>
                                            <option value="percent" <?php selected($fee_type, 'percent'); ?>>Percentage Fee (%)</option>
                                        </select>
                                    </div>
                                    <div class="form-text text-muted mt-1">Choose whether the service fee is a fixed flat amount or a percentage of the service cost.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="cosy_service_fee_value" class="form-label fw-bold text-secondary">Service Fee Value</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-coins"></i></span>
                                        <input type="number" step="0.01" class="form-control border-start-0 py-2" name="cosy_service_fee_value" id="cosy_service_fee_value" value="<?php echo esc_attr(get_option('cosy_service_fee_value', '0.10')); ?>" placeholder="0.00">
                                    </div>
                                    <div class="form-text text-muted mt-1">Specify the service fee amount (e.g. 0.10 for flat fee, or 10.00 for 10% fee).</div>
                                </div>
                            </div>

                        </div>

                        <!-- AI Search Settings Tab -->
                        <div class="tab-pane fade" id="v-pills-aisearch" role="tabpanel" aria-labelledby="v-pills-aisearch-tab">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h3 class="fw-bold text-dark m-0 d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-brain fs-2 text-primary" style="color: #a44390 !important;"></i>
                                    AI Search Engine Configuration
                                </h3>
                            </div>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="cosy_ai_provider" class="form-label fw-bold text-secondary">AI Provider</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-robot"></i></span>
                                        <?php
                                        $ai_provider = get_option('cosy_ai_provider', 'gemini');
                                        ?>
                                        <select class="form-select border-start-0 py-2" name="cosy_ai_provider" id="cosy_ai_provider">
                                            <option value="gemini" <?php selected($ai_provider, 'gemini'); ?>>Google Gemini (Free Tier / Testing)</option>
                                            <option value="openai" <?php selected($ai_provider, 'openai'); ?>>OpenAI (Production - text-embedding-3-small)</option>
                                        </select>
                                    </div>
                                    <div class="form-text text-muted mt-1">Select the AI engine provider to use for semantic profile search.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="cosy_ai_api_key" class="form-label fw-bold text-secondary">AI API Key</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-key"></i></span>
                                        <input type="password" class="form-control border-start-0 py-2" name="cosy_ai_api_key" id="cosy_ai_api_key" value="<?php echo esc_attr(get_option('cosy_ai_api_key')); ?>" placeholder="Enter Gemini or OpenAI API Key">
                                    </div>
                                    <div class="form-text text-muted mt-1">Paste your Google Gemini API key or OpenAI secret key here.</div>
                                </div>
                                <div class="col-12 mt-4 pt-3 border-top">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <h6 class="fw-bold mb-1 text-dark">Provider Profiles Indexing</h6>
                                            <p class="text-muted small mb-0">Generate vector embeddings for all registered parent/provider profiles using the configured API key.</p>
                                        </div>
                                        <button type="button" id="cosy-reindex-ai-btn" class="btn btn-outline-primary fw-semibold px-3 py-2 rounded-3">
                                            <i class="fa-solid fa-arrows-rotate me-1"></i> Re-index All Profiles
                                        </button>
                                    </div>
                                    <div id="cosy-reindex-status" class="mt-2 small fw-semibold"></div>
                                </div>
                            </div>
                        </div>

                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const btn = document.getElementById('cosy-reindex-ai-btn');
                            const status = document.getElementById('cosy-reindex-status');
                            if (!btn) return;

                            btn.addEventListener('click', function() {
                                btn.disabled = true;
                                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Indexing...';
                                status.className = 'mt-2 small text-info';
                                status.innerText = 'Connecting to AI API and generating profile vectors...';

                                fetch(ajaxurl + '?action=cosy_ai_reindex', { method: 'POST' })
                                    .then(res => res.json())
                                    .then(data => {
                                        btn.disabled = false;
                                        btn.innerHTML = '<i class="fa-solid fa-arrows-rotate me-1"></i> Re-index All Profiles';
                                        if (data.success) {
                                            status.className = 'mt-2 small text-success';
                                            status.innerText = '✅ ' + data.data.message;
                                        } else {
                                            status.className = 'mt-2 small text-danger';
                                            status.innerText = '❌ Error: ' + (data.data ? data.data.message : 'Indexing failed.');
                                        }
                                    })
                                    .catch(err => {
                                        btn.disabled = false;
                                        btn.innerHTML = '<i class="fa-solid fa-arrows-rotate me-1"></i> Re-index All Profiles';
                                        status.className = 'mt-2 small text-danger';
                                        status.innerText = '❌ Request failed. Please check network/API key.';
                                    });
                            });
                        });
                        </script>

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
        outline: none !important;
        box-shadow: none !important;
        padding-left: 12px !important;
    }

    .cosy-settings-wrap .form-select {
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
        padding-left: 12px !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
        background-position: right 0.75rem center !important;
        background-size: 16px 12px !important;
        background-repeat: no-repeat !important;
        padding-right: 2.5rem !important;
        appearance: none !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        max-width: 100% !important;
        height: auto !important;
        font-weight: 500 !important;
    }

    .cosy-settings-wrap .form-control:focus,
    .cosy-settings-wrap .form-select:focus {
        border: none !important;
        outline: none !important;
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
