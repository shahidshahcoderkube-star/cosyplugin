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
                    <i class="fa-solid fa-sliders fs-4"></i>
                </div>
                <div>
                    <h1 class="wp-heading-inline m-0 fs-2 fw-bold text-dark"><?php _e('Global Settings', 'cosy-appointments'); ?></h1>
                    <p class="text-muted m-0 mt-1"><?php _e('Configure payment gateways, AI search engine, page branding images, and email signatures.', 'cosy-appointments'); ?></p>
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

                        <button class="nav-link d-flex align-items-center gap-3 py-3 px-4 rounded-3 border-0 text-start" id="v-pills-aisearch-tab" data-bs-toggle="pill" data-bs-target="#v-pills-aisearch" type="button" role="tab" aria-controls="v-pills-aisearch" aria-selected="false">
                            <i class="fa-solid fa-brain fs-4 w-20"></i>
                            <span class="fw-bold">AI Search Engine</span>
                        </button>
                        <button class="nav-link d-flex align-items-center gap-3 py-3 px-4 rounded-3 border-0 text-start" id="v-pills-branding-tab" data-bs-toggle="pill" data-bs-target="#v-pills-branding" type="button" role="tab" aria-controls="v-pills-branding" aria-selected="false">
                            <i class="fa-solid fa-image fs-4 w-20"></i>
                            <span class="fw-bold">Page Images</span>
                        </button>
                        <button class="nav-link d-flex align-items-center gap-3 py-3 px-4 rounded-3 border-0 text-start" id="v-pills-signature-tab" data-bs-toggle="pill" data-bs-target="#v-pills-signature" type="button" role="tab" aria-controls="v-pills-signature" aria-selected="false">
                            <i class="fa-solid fa-signature fs-4 w-20"></i>
                            <span class="fw-bold">Email Signature</span>
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

                        <!-- Page Images Settings Tab -->
                        <div class="tab-pane fade" id="v-pills-branding" role="tabpanel" aria-labelledby="v-pills-branding-tab">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h3 class="fw-bold text-dark m-0 d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-image fs-2 text-primary" style="color: #a44390 !important;"></i>
                                    Registration &amp; Login Page Images
                                </h3>
                            </div>
                            <div class="row g-4">
                                <div class="col-md-12">
                                    <label for="cosy_registration_image_url" class="form-label fw-bold text-secondary">User Registration Page Image</label>
                                    <div class="input-group mb-2">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-image"></i></span>
                                        <input type="text" class="form-control border-start-0 py-2 cosy-img-input" name="cosy_registration_image_url" id="cosy_registration_image_url" value="<?php echo esc_attr(get_option('cosy_registration_image_url')); ?>" placeholder="Select or upload image from Media Library">
                                        <button type="button" class="btn px-3 fw-semibold cosy-media-select-btn" data-target="#cosy_registration_image_url" data-preview="#cosy_reg_preview" style="color: #a44390; border: 1.5px solid #a44390; background: #ffffff;">
                                            <i class="fa-solid fa-folder-open me-1" style="color: #a44390;"></i> Choose Image
                                        </button>
                                    </div>
                                    <div class="form-text text-muted">Select or upload the right-side banner image for the Customer Registration page (/user-registration/).</div>
                                    <?php $reg_preview = get_option('cosy_registration_image_url'); ?>
                                    <div id="cosy_reg_preview" class="mt-2 <?php echo empty($reg_preview) ? 'd-none' : ''; ?>">
                                        <img src="<?php echo esc_url($reg_preview); ?>" style="max-height: 120px; border-radius: 10px; border: 1px solid #cbd5e1; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <label for="cosy_login_image_url" class="form-label fw-bold text-secondary">Customer Login Page Image</label>
                                    <div class="input-group mb-2">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-image"></i></span>
                                        <input type="text" class="form-control border-start-0 py-2 cosy-img-input" name="cosy_login_image_url" id="cosy_login_image_url" value="<?php echo esc_attr(get_option('cosy_login_image_url')); ?>" placeholder="Select or upload image from Media Library">
                                        <button type="button" class="btn px-3 fw-semibold cosy-media-select-btn" data-target="#cosy_login_image_url" data-preview="#cosy_login_preview" style="color: #a44390; border: 1.5px solid #a44390; background: #ffffff;">
                                            <i class="fa-solid fa-folder-open me-1" style="color: #a44390;"></i> Choose Image
                                        </button>
                                    </div>
                                    <div class="form-text text-muted">Select or upload the right-side banner image for the Customer Login page (/login/).</div>
                                    <?php $login_preview = get_option('cosy_login_image_url'); ?>
                                    <div id="cosy_login_preview" class="mt-2 <?php echo empty($login_preview) ? 'd-none' : ''; ?>">
                                        <img src="<?php echo esc_url($login_preview); ?>" style="max-height: 120px; border-radius: 10px; border: 1px solid #cbd5e1; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Email Signature Settings Tab -->
                        <div class="tab-pane fade" id="v-pills-signature" role="tabpanel" aria-labelledby="v-pills-signature-tab">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h3 class="fw-bold text-dark m-0 d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-signature fs-2 text-primary" style="color: #a44390 !important;"></i>
                                    Email Signature Configuration
                                </h3>
                                <div class="d-flex align-items-center m-0">
                                    <label class="cosy-switch">
                                        <input type="checkbox" name="cosy_sig_enabled" id="cosy_sig_enabled" value="1" <?php checked(1, get_option('cosy_sig_enabled', 1)); ?>>
                                        <span class="cosy-slider round"></span>
                                    </label>
                                    <span class="fw-semibold text-secondary ms-2">Enable Signature in Emails</span>
                                </div>
                            </div>

                            <div class="row g-4">
                                <!-- Logo Upload -->
                                <div class="col-md-12">
                                    <label for="cosy_sig_logo_url" class="form-label fw-bold text-secondary">Company Logo</label>
                                    <div class="input-group mb-2">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-image"></i></span>
                                        <input type="text" class="form-control border-start-0 py-2 cosy-img-input" name="cosy_sig_logo_url" id="cosy_sig_logo_url" value="<?php echo esc_attr(get_option('cosy_sig_logo_url')); ?>" placeholder="Select or upload your company logo">
                                        <button type="button" class="btn px-3 fw-semibold cosy-media-select-btn" data-target="#cosy_sig_logo_url" data-preview="#cosy_sig_logo_preview" style="color: #a44390; border: 1.5px solid #a44390; background: #ffffff;">
                                            <i class="fa-solid fa-folder-open me-1" style="color: #a44390;"></i> Choose Logo
                                        </button>
                                    </div>
                                    <div class="form-text text-muted">Upload your CosyChats company logo. Recommended: PNG with transparent background, max 200px wide.</div>
                                    <?php $sig_logo = get_option('cosy_sig_logo_url'); ?>
                                    <div id="cosy_sig_logo_preview" class="mt-2 <?php echo empty($sig_logo) ? 'd-none' : ''; ?>">
                                        <img src="<?php echo esc_url($sig_logo); ?>" style="max-height: 80px; border-radius: 8px; border: 1px solid #cbd5e1; box-shadow: 0 2px 6px rgba(0,0,0,0.05); padding: 6px; background: #fff;">
                                    </div>
                                </div>

                                <!-- Name & Title -->
                                <div class="col-md-6">
                                    <label for="cosy_sig_name" class="form-label fw-bold text-secondary">Display Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-user"></i></span>
                                        <input type="text" class="form-control border-start-0 py-2" name="cosy_sig_name" id="cosy_sig_name" value="<?php echo esc_attr(get_option('cosy_sig_name', 'The CosyChats Team')); ?>" placeholder="e.g. The CosyChats Team">
                                    </div>
                                    <div class="form-text text-muted mt-1">Name shown in the signature (e.g. your name or team name).</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="cosy_sig_title" class="form-label fw-bold text-secondary">Title / Role</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-id-badge"></i></span>
                                        <input type="text" class="form-control border-start-0 py-2" name="cosy_sig_title" id="cosy_sig_title" value="<?php echo esc_attr(get_option('cosy_sig_title', 'Customer Support')); ?>" placeholder="e.g. Customer Support">
                                    </div>
                                    <div class="form-text text-muted mt-1">Your role or designation shown below the name.</div>
                                </div>

                                <!-- Phone & Email -->
                                <div class="col-md-6">
                                    <label for="cosy_sig_phone" class="form-label fw-bold text-secondary">Phone Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-phone"></i></span>
                                        <input type="text" class="form-control border-start-0 py-2" name="cosy_sig_phone" id="cosy_sig_phone" value="<?php echo esc_attr(get_option('cosy_sig_phone')); ?>" placeholder="e.g. +44 123 456 7890">
                                    </div>
                                    <div class="form-text text-muted mt-1">Contact phone number displayed in the signature.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="cosy_sig_email" class="form-label fw-bold text-secondary">Support Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-envelope"></i></span>
                                        <input type="email" class="form-control border-start-0 py-2" name="cosy_sig_email" id="cosy_sig_email" value="<?php echo esc_attr(get_option('cosy_sig_email')); ?>" placeholder="e.g. support@cosychats.com">
                                    </div>
                                    <div class="form-text text-muted mt-1">Contact/support email shown in the signature.</div>
                                </div>

                                <!-- Website & Address -->
                                <div class="col-md-6">
                                    <label for="cosy_sig_website" class="form-label fw-bold text-secondary">Website URL</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-globe"></i></span>
                                        <input type="url" class="form-control border-start-0 py-2" name="cosy_sig_website" id="cosy_sig_website" value="<?php echo esc_attr(get_option('cosy_sig_website', home_url())); ?>" placeholder="https://cosychats.com">
                                    </div>
                                    <div class="form-text text-muted mt-1">Website link shown in the signature.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="cosy_sig_address" class="form-label fw-bold text-secondary">Office Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-location-dot"></i></span>
                                        <input type="text" class="form-control border-start-0 py-2" name="cosy_sig_address" id="cosy_sig_address" value="<?php echo esc_attr(get_option('cosy_sig_address')); ?>" placeholder="e.g. London, United Kingdom">
                                    </div>
                                    <div class="form-text text-muted mt-1">Physical address or city shown in the signature.</div>
                                </div>

                                <!-- Social Media Links -->
                                <div class="col-12 mt-2">
                                    <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-share-nodes me-2" style="color: #a44390;"></i>Social Media Links</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="cosy_sig_facebook" class="form-label fw-bold text-secondary">Facebook URL</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-brands fa-facebook"></i></span>
                                                <input type="url" class="form-control border-start-0 py-2" name="cosy_sig_facebook" id="cosy_sig_facebook" value="<?php echo esc_attr(get_option('cosy_sig_facebook')); ?>" placeholder="https://facebook.com/yourpage">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="cosy_sig_twitter" class="form-label fw-bold text-secondary">Twitter / X URL</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-brands fa-x-twitter"></i></span>
                                                <input type="url" class="form-control border-start-0 py-2" name="cosy_sig_twitter" id="cosy_sig_twitter" value="<?php echo esc_attr(get_option('cosy_sig_twitter')); ?>" placeholder="https://x.com/yourhandle">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="cosy_sig_instagram" class="form-label fw-bold text-secondary">Instagram URL</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-brands fa-instagram"></i></span>
                                                <input type="url" class="form-control border-start-0 py-2" name="cosy_sig_instagram" id="cosy_sig_instagram" value="<?php echo esc_attr(get_option('cosy_sig_instagram')); ?>" placeholder="https://instagram.com/yourhandle">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="cosy_sig_tiktok" class="form-label fw-bold text-secondary">TikTok URL</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-brands fa-tiktok"></i></span>
                                                <input type="url" class="form-control border-start-0 py-2" name="cosy_sig_tiktok" id="cosy_sig_tiktok" value="<?php echo esc_attr(get_option('cosy_sig_tiktok')); ?>" placeholder="https://tiktok.com/@yourhandle">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="cosy_sig_youtube" class="form-label fw-bold text-secondary">YouTube URL</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-brands fa-youtube"></i></span>
                                                <input type="url" class="form-control border-start-0 py-2" name="cosy_sig_youtube" id="cosy_sig_youtube" value="<?php echo esc_attr(get_option('cosy_sig_youtube')); ?>" placeholder="https://youtube.com/@yourchannel">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="cosy_sig_linkedin" class="form-label fw-bold text-secondary">LinkedIn URL</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-brands fa-linkedin"></i></span>
                                                <input type="url" class="form-control border-start-0 py-2" name="cosy_sig_linkedin" id="cosy_sig_linkedin" value="<?php echo esc_attr(get_option('cosy_sig_linkedin')); ?>" placeholder="https://linkedin.com/company/...">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Live Signature Preview -->
                                <div class="col-12 mt-3">
                                    <div class="border rounded-3 p-3" style="background: #fdfafd;">
                                        <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-eye me-2" style="color: #a44390;"></i>Signature Preview</h6>
                                        <?php
                                        $sig_logo    = get_option('cosy_sig_logo_url');
                                        $sig_name    = get_option('cosy_sig_name', 'The CosyChats Team');
                                        $sig_title   = get_option('cosy_sig_title', 'Customer Support');
                                        $sig_phone   = get_option('cosy_sig_phone');
                                        $sig_email   = get_option('cosy_sig_email');
                                        $sig_website = get_option('cosy_sig_website', home_url());
                                        $sig_address = get_option('cosy_sig_address');
                                        $sig_li      = get_option('cosy_sig_linkedin');
                                        $sig_fb      = get_option('cosy_sig_facebook');
                                        $sig_ig      = get_option('cosy_sig_instagram');
                                        $sig_tw      = get_option('cosy_sig_twitter');
                                        $sig_tk      = get_option('cosy_sig_tiktok');
                                        $sig_yt      = get_option('cosy_sig_youtube');
                                        ?>
                                        <div style="background:#ffffff; border:1px solid #f1e4ef; border-radius:12px; padding:20px; max-width:540px; font-family:'Segoe UI',Arial,sans-serif;">
                                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                                <tr>
                                                    <?php if (!empty($sig_logo)) : ?>
                                                    <td style="width:110px; vertical-align:middle; padding-right:16px;">
                                                        <img src="<?php echo esc_url($sig_logo); ?>" alt="Logo" style="max-width:100px; height:auto; display:block;">
                                                    </td>
                                                    <td style="width:2px; vertical-align:middle; padding:0 16px 0 0;">
                                                        <div style="width:2px; height:80px; background:linear-gradient(180deg,#a44390,#6d2e67);"></div>
                                                    </td>
                                                    <?php endif; ?>
                                                    <td style="vertical-align:middle;">
                                                        <?php if (!empty($sig_name)) : ?>
                                                        <p style="margin:0 0 2px 0; font-size:15px; font-weight:700; color:#a44390;"><?php echo esc_html($sig_name); ?></p>
                                                        <?php endif; ?>
                                                        <?php if (!empty($sig_title)) : ?>
                                                        <p style="margin:0 0 8px 0; font-size:12px; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;"><?php echo esc_html($sig_title); ?></p>
                                                        <?php endif; ?>
                                                        <?php if (!empty($sig_phone)) : ?>
                                                        <p style="margin:0 0 3px 0; font-size:12px; color:#334155;">📞 <?php echo esc_html($sig_phone); ?></p>
                                                        <?php endif; ?>
                                                        <?php if (!empty($sig_email)) : ?>
                                                        <p style="margin:0 0 3px 0; font-size:12px; color:#334155;">✉️ <?php echo esc_html($sig_email); ?></p>
                                                        <?php endif; ?>
                                                        <?php if (!empty($sig_website)) : ?>
                                                        <p style="margin:0 0 3px 0; font-size:12px; color:#334155;">🌐 <?php echo esc_html($sig_website); ?></p>
                                                        <?php endif; ?>
                                                        <?php if (!empty($sig_address)) : ?>
                                                        <p style="margin:0 0 8px 0; font-size:12px; color:#334155;">📍 <?php echo esc_html($sig_address); ?></p>
                                                        <?php endif; ?>
                                                        <?php if (!empty($sig_fb) || !empty($sig_tw) || !empty($sig_ig) || !empty($sig_tk) || !empty($sig_yt) || !empty($sig_li)) : ?>
                                                        <p style="margin:6px 0 0 0; line-height:1; display:flex; align-items:center; gap:8px;">
                                                            <?php if (!empty($sig_fb)) : ?>
                                                            <a href="<?php echo esc_url($sig_fb); ?>" target="_blank" style="display:inline-flex; align-items:center; justify-content:center; width:30px; height:30px; border-radius:50%; background:#3b5998; color:#ffffff; text-decoration:none; font-size:14px;">
                                                                <i class="fa-brands fa-facebook-f" style="color:#ffffff;"></i>
                                                            </a>
                                                            <?php endif; ?>
                                                            <?php if (!empty($sig_tw)) : ?>
                                                            <a href="<?php echo esc_url($sig_tw); ?>" target="_blank" style="display:inline-flex; align-items:center; justify-content:center; width:30px; height:30px; border-radius:50%; background:#000000; color:#ffffff; text-decoration:none; font-size:13px;">
                                                                <i class="fa-brands fa-x-twitter" style="color:#ffffff;"></i>
                                                            </a>
                                                            <?php endif; ?>
                                                            <?php if (!empty($sig_ig)) : ?>
                                                            <a href="<?php echo esc_url($sig_ig); ?>" target="_blank" style="display:inline-flex; align-items:center; justify-content:center; width:30px; height:30px; border-radius:50%; background:radial-gradient(circle at 30% 107%, #fdf497 0%, #fdf497 5%, #fd5949 45%,#d6249f 60%,#285AEB 90%); color:#ffffff; text-decoration:none; font-size:14px;">
                                                                <i class="fa-brands fa-instagram" style="color:#ffffff;"></i>
                                                            </a>
                                                            <?php endif; ?>
                                                            <?php if (!empty($sig_tk)) : ?>
                                                            <a href="<?php echo esc_url($sig_tk); ?>" target="_blank" style="display:inline-flex; align-items:center; justify-content:center; width:30px; height:30px; border-radius:50%; background:#000000; color:#ffffff; text-decoration:none; font-size:13px;">
                                                                <i class="fa-brands fa-tiktok" style="color:#ffffff;"></i>
                                                            </a>
                                                            <?php endif; ?>
                                                            <?php if (!empty($sig_yt)) : ?>
                                                            <a href="<?php echo esc_url($sig_yt); ?>" target="_blank" style="display:inline-flex; align-items:center; justify-content:center; width:30px; height:30px; border-radius:50%; background:#ff0000; color:#ffffff; text-decoration:none; font-size:13px;">
                                                                <i class="fa-brands fa-youtube" style="color:#ffffff;"></i>
                                                            </a>
                                                            <?php endif; ?>
                                                            <?php if (!empty($sig_li)) : ?>
                                                            <a href="<?php echo esc_url($sig_li); ?>" target="_blank" style="display:inline-flex; align-items:center; justify-content:center; width:30px; height:30px; border-radius:50%; background:#0077b5; color:#ffffff; text-decoration:none; font-size:13px;">
                                                                <i class="fa-brands fa-linkedin-in" style="color:#ffffff;"></i>
                                                            </a>
                                                            <?php endif; ?>
                                                        </p>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <p class="text-muted small mt-2 mb-0"><i class="fa-solid fa-info-circle me-1"></i>Save settings to refresh the live preview above.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <script>
                        jQuery(document).ready(function($) {
                            $('.cosy-media-select-btn').on('click', function(e) {
                                e.preventDefault();
                                var btn = $(this);
                                var targetInput = $(btn.data('target'));
                                var previewDiv = $(btn.data('preview'));

                                var mediaFrame = wp.media({
                                    title: 'Select or Upload Page Image',
                                    button: { text: 'Use This Image' },
                                    multiple: false
                                });

                                mediaFrame.on('select', function() {
                                    var attachment = mediaFrame.state().get('selection').first().toJSON();
                                    targetInput.val(attachment.url);
                                    if (previewDiv.length) {
                                        previewDiv.removeClass('d-none').find('img').attr('src', attachment.url);
                                    }
                                });

                                mediaFrame.open();
                            });
                        });
                        </script>

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

<script>
document.addEventListener('DOMContentLoaded', function() {
    var tabButtons = document.querySelectorAll('#v-pills-tab button[data-bs-toggle="pill"]');
    
    function activateTab(tabTargetId) {
        if (!tabTargetId) return;
        var cleanId = tabTargetId.replace('#', '').replace('v-pills-', '').replace('-tab', '');
        var targetBtn = document.querySelector('#v-pills-' + cleanId + '-tab');
        if (targetBtn) {
            if (typeof bootstrap !== 'undefined' && bootstrap.Tab) {
                var tabObj = bootstrap.Tab.getOrCreateInstance(targetBtn);
                if (tabObj) tabObj.show();
            } else {
                targetBtn.click();
            }
        }
    }

    // 1. Restore active tab from URL hash, URL param, or localStorage
    var urlParams = new URLSearchParams(window.location.search);
    var urlTab = urlParams.get('tab');
    var hashTab = window.location.hash ? window.location.hash.replace('#', '') : '';
    var savedTab = localStorage.getItem('cosy_active_settings_tab');

    var initialTab = urlTab || hashTab || savedTab;
    if (initialTab) {
        activateTab(initialTab);
    }

    // 2. Track tab clicks and persist selection
    tabButtons.forEach(function(btn) {
        btn.addEventListener('shown.bs.tab', function(e) {
            var targetId = e.target.getAttribute('data-bs-target').replace('#v-pills-', '');
            localStorage.setItem('cosy_active_settings_tab', targetId);
            if (history.pushState) {
                history.pushState(null, null, '#v-pills-' + targetId);
            } else {
                window.location.hash = '#v-pills-' + targetId;
            }
        });
    });

    // 3. Keep current active tab when saving settings form
    var settingsForm = document.querySelector('.cosy-settings-wrap form');
    if (settingsForm) {
        settingsForm.addEventListener('submit', function() {
            var activeBtn = document.querySelector('#v-pills-tab button.active');
            if (activeBtn) {
                var targetId = activeBtn.getAttribute('data-bs-target').replace('#v-pills-', '');
                localStorage.setItem('cosy_active_settings_tab', targetId);
            }
        });
    }
});
</script>
