<?php
/**
 * GLOBAL SETTINGS PAGE TEMPLATE VIEW
 *
 * USE CASE:
 * Renders the Admin -> CC Booking -> Settings interface.
 * 
 * HOW TO USE:
 * Included by SettingsAdmin::render_settings().
 * 
 * WHAT IT DOES INTERNALLY:
 * 1. Renders tabbed interface for WorldPay credentials, AI search settings, page assignments, and email signatures.
 * 2. Outputs WordPress settings fields and submit buttons via settings_fields().
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
                    <p class="text-muted m-0 mt-1"><?php _e('Configure WorldPay payment gateway, AI search engine, page branding images, and email signatures.', 'cosy-appointments'); ?></p>
                </div>
            </div>
        </div>

        <form method="post" action="options.php">
            <?php settings_fields('cosy_settings'); ?>

            <div class="row">
                <!-- Navigation Sidebar -->
                <div class="col-md-3 mb-4">
                    <div class="nav flex-column nav-pills me-3 gap-2" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                        <button class="nav-link active d-flex align-items-center gap-3 py-3 px-4 rounded-3 border-0 text-start" id="v-pills-worldpay-tab" data-bs-toggle="pill" data-bs-target="#v-pills-worldpay" type="button" role="tab" aria-controls="v-pills-worldpay" aria-selected="true">
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
                        <button class="nav-link d-flex align-items-center gap-3 py-3 px-4 rounded-3 border-0 text-start" id="v-pills-smtp-tab" data-bs-toggle="pill" data-bs-target="#v-pills-smtp" type="button" role="tab" aria-controls="v-pills-smtp" aria-selected="false">
                            <i class="fa-solid fa-envelope-open-text fs-4 w-20"></i>
                            <span class="fw-bold">SMTP Settings</span>
                        </button>
                    </div>
                </div>


                <!-- Tab Content Card -->
                <div class="col-md-9">
                    <div class="tab-content bg-light bg-opacity-50 p-4 rounded-4 border border-secondary-subtle cosy-settings-tab-content" id="v-pills-tabContent">

                        <!-- WorldPay Settings Tab -->
                        <div class="tab-pane fade show active" id="v-pills-worldpay" role="tabpanel" aria-labelledby="v-pills-worldpay-tab">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h3 class="fw-bold text-dark m-0 d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-globe fs-2 cosy-settings-icon-purple"></i>
                                    WorldPay Configuration
                                </h3>
                                <div class="d-flex align-items-center m-0">
                                    <label class="cosy-switch">
                                        <input type="checkbox" name="cosy_worldpay_test_mode" id="cosy_worldpay_test_mode" value="1" <?php checked(1, get_option('cosy_worldpay_test_mode', 1)); ?>>
                                        <span class="cosy-slider round"></span>
                                    </label>
                                    <span class="fw-semibold text-secondary ms-2">Sandbox / Test Mode</span>
                                </div>
                            </div>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="cosy_worldpay_inst_id" class="form-label fw-bold text-secondary">Installation ID</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-id-card"></i></span>
                                        <input type="text" class="form-control border-start-0 py-2" name="cosy_worldpay_inst_id" id="cosy_worldpay_inst_id" value="<?php echo esc_attr(get_option('cosy_worldpay_inst_id')); ?>" placeholder="e.g. 1057362">
                                    </div>
                                    <div class="form-text text-muted mt-1">Enter your 7-digit WorldPay Installation ID (e.g. 1057362).</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="cosy_worldpay_token" class="form-label fw-bold text-secondary">API User name</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-user"></i></span>
                                        <input type="text" class="form-control border-start-0 py-2" name="cosy_worldpay_token" id="cosy_worldpay_token" value="<?php echo esc_attr(get_option('cosy_worldpay_token')); ?>" placeholder="e.g. rMkF6vE1F9xmmGtu">
                                    </div>
                                    <div class="form-text text-muted mt-1">From API Credentials section.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="cosy_worldpay_password" class="form-label fw-bold text-secondary">API Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-lock"></i></span>
                                        <input type="password" class="form-control border-start-0 py-2" name="cosy_worldpay_password" id="cosy_worldpay_password" value="<?php echo esc_attr(get_option('cosy_worldpay_password')); ?>" placeholder="API Password">
                                    </div>
                                    <div class="form-text text-muted mt-1">From API Credentials section.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="cosy_worldpay_charge" class="form-label fw-bold text-secondary">Transaction Charge (%)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-percent"></i></span>
                                        <input type="number" step="0.01" class="form-control border-start-0 py-2" name="cosy_worldpay_charge" id="cosy_worldpay_charge" value="<?php echo esc_attr(get_option('cosy_worldpay_charge')); ?>" placeholder="0.00">
                                    </div>
                                    <div class="form-text text-muted mt-1">Specify platform fee percentage charged for WorldPay transactions.</div>
                                </div>
                            </div>
                        </div>

                        <!-- AI Search Settings Tab -->
                        <div class="tab-pane fade" id="v-pills-aisearch" role="tabpanel" aria-labelledby="v-pills-aisearch-tab">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h3 class="fw-bold text-dark m-0 d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-brain fs-2 cosy-settings-icon-purple"></i>
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
                                    <i class="fa-solid fa-image fs-2 cosy-settings-icon-purple"></i>
                                    Registration &amp; Login Page Images
                                </h3>
                            </div>
                            <div class="row g-4">
                                <div class="col-md-12">
                                    <label for="cosy_registration_image_url" class="form-label fw-bold text-secondary">User Registration Page Image</label>
                                    <div class="input-group mb-2">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-image"></i></span>
                                        <input type="text" class="form-control border-start-0 py-2 cosy-img-input" name="cosy_registration_image_url" id="cosy_registration_image_url" value="<?php echo esc_attr(get_option('cosy_registration_image_url')); ?>" placeholder="Select or upload image from Media Library">
                                        <button type="button" class="btn px-3 fw-semibold cosy-media-select-btn cosy-settings-media-btn" data-target="#cosy_registration_image_url" data-preview="#cosy_reg_preview">
                                            <i class="fa-solid fa-folder-open me-1 cosy-settings-icon-purple"></i> Choose Image
                                        </button>
                                    </div>
                                    <div class="form-text text-muted">Select or upload the right-side banner image for the Customer Registration page (/user-registration/).</div>
                                    <?php $reg_preview = get_option('cosy_registration_image_url'); ?>
                                    <div id="cosy_reg_preview" class="mt-2 <?php echo empty($reg_preview) ? 'd-none' : ''; ?>">
                                        <img src="<?php echo esc_url($reg_preview); ?>" class="cosy-settings-media-preview-img">
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <label for="cosy_login_image_url" class="form-label fw-bold text-secondary">Customer Login Page Image</label>
                                    <div class="input-group mb-2">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-image"></i></span>
                                        <input type="text" class="form-control border-start-0 py-2 cosy-img-input" name="cosy_login_image_url" id="cosy_login_image_url" value="<?php echo esc_attr(get_option('cosy_login_image_url')); ?>" placeholder="Select or upload image from Media Library">
                                        <button type="button" class="btn px-3 fw-semibold cosy-media-select-btn cosy-settings-media-btn" data-target="#cosy_login_image_url" data-preview="#cosy_login_preview">
                                            <i class="fa-solid fa-folder-open me-1 cosy-settings-icon-purple"></i> Choose Image
                                        </button>
                                    </div>
                                    <div class="form-text text-muted">Select or upload the right-side banner image for the Customer Login page (/login/).</div>
                                    <?php $login_preview = get_option('cosy_login_image_url'); ?>
                                    <div id="cosy_login_preview" class="mt-2 <?php echo empty($login_preview) ? 'd-none' : ''; ?>">
                                        <img src="<?php echo esc_url($login_preview); ?>" class="cosy-settings-media-preview-img">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Email Signature Settings Tab -->
                        <div class="tab-pane fade" id="v-pills-signature" role="tabpanel" aria-labelledby="v-pills-signature-tab">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h3 class="fw-bold text-dark m-0 d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-signature fs-2 cosy-settings-icon-purple"></i>
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
                                        <button type="button" class="btn px-3 fw-semibold cosy-media-select-btn cosy-settings-media-btn" data-target="#cosy_sig_logo_url" data-preview="#cosy_sig_logo_preview">
                                            <i class="fa-solid fa-folder-open me-1 cosy-settings-icon-purple"></i> Choose Logo
                                        </button>
                                    </div>
                                    <div class="form-text text-muted">Upload your CosyChats company logo. Recommended: PNG with transparent background, max 200px wide.</div>
                                    <?php $sig_logo = get_option('cosy_sig_logo_url'); ?>
                                    <div id="cosy_sig_logo_preview" class="mt-2 <?php echo empty($sig_logo) ? 'd-none' : ''; ?>">
                                        <img src="<?php echo esc_url($sig_logo); ?>" class="cosy-settings-logo-preview-img">
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
                                    <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-share-nodes me-2 cosy-settings-icon-purple"></i>Social Media Links</h6>
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
                                    <div class="border rounded-3 p-3 cosy-sig-preview-card">
                                        <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-eye me-2 cosy-settings-icon-purple"></i>Signature Preview</h6>
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
                                        <div class="cosy-sig-box">
                                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                                <tr>
                                                    <?php if (!empty($sig_logo)) : ?>
                                                    <td class="cosy-sig-logo-td">
                                                        <img src="<?php echo esc_url($sig_logo); ?>" alt="Logo" class="cosy-sig-logo-img">
                                                    </td>
                                                    <td class="cosy-sig-divider-td">
                                                        <div class="cosy-sig-divider-line"></div>
                                                    </td>
                                                    <?php endif; ?>
                                                    <td class="cosy-sig-details-td">
                                                        <?php if (!empty($sig_name)) : ?>
                                                        <p class="cosy-sig-name-p"><?php echo esc_html($sig_name); ?></p>
                                                        <?php endif; ?>
                                                        <?php if (!empty($sig_title)) : ?>
                                                        <p class="cosy-sig-title-p"><?php echo esc_html($sig_title); ?></p>
                                                        <?php endif; ?>
                                                        <?php if (!empty($sig_phone)) : ?>
                                                        <p class="cosy-sig-contact-p">📞 <?php echo esc_html($sig_phone); ?></p>
                                                        <?php endif; ?>
                                                        <?php if (!empty($sig_email)) : ?>
                                                        <p class="cosy-sig-contact-p">✉️ <?php echo esc_html($sig_email); ?></p>
                                                        <?php endif; ?>
                                                        <?php if (!empty($sig_website)) : ?>
                                                        <p class="cosy-sig-contact-p">🌐 <?php echo esc_html($sig_website); ?></p>
                                                        <?php endif; ?>
                                                        <?php if (!empty($sig_address)) : ?>
                                                        <p class="cosy-sig-contact-p-last">📍 <?php echo esc_html($sig_address); ?></p>
                                                        <?php endif; ?>
                                                        <?php if (!empty($sig_fb) || !empty($sig_tw) || !empty($sig_ig) || !empty($sig_tk) || !empty($sig_yt) || !empty($sig_li)) : ?>
                                                        <p class="cosy-sig-social-row">
                                                            <?php if (!empty($sig_fb)) : ?>
                                                            <a href="<?php echo esc_url($sig_fb); ?>" target="_blank" class="cosy-sig-social-link"><img src="https://wppremiumplugins.com/cosychats/wp-content/uploads/2026/08/facebook.png" width="26" height="26" alt="Facebook" class="cosy-sig-social-icon"></a>
                                                            <?php endif; ?>
                                                            <?php if (!empty($sig_tw)) : ?>
                                                            <a href="<?php echo esc_url($sig_tw); ?>" target="_blank" class="cosy-sig-social-link"><img src="https://wppremiumplugins.com/cosychats/wp-content/uploads/2026/08/X.png" width="26" height="26" alt="X" class="cosy-sig-social-icon"></a>
                                                            <?php endif; ?>
                                                            <?php if (!empty($sig_ig)) : ?>
                                                            <a href="<?php echo esc_url($sig_ig); ?>" target="_blank" class="cosy-sig-social-link"><img src="https://wppremiumplugins.com/cosychats/wp-content/uploads/2026/08/instagram.png" width="26" height="26" alt="Instagram" class="cosy-sig-social-icon"></a>
                                                            <?php endif; ?>
                                                            <?php if (!empty($sig_tk)) : ?>
                                                            <a href="<?php echo esc_url($sig_tk); ?>" target="_blank" class="cosy-sig-social-link"><img src="https://wppremiumplugins.com/cosychats/wp-content/uploads/2026/08/tiktok.png" width="26" height="26" alt="TikTok" class="cosy-sig-social-icon"></a>
                                                            <?php endif; ?>
                                                            <?php if (!empty($sig_yt)) : ?>
                                                            <a href="<?php echo esc_url($sig_yt); ?>" target="_blank" class="cosy-sig-social-link"><img src="https://wppremiumplugins.com/cosychats/wp-content/uploads/2026/08/youtube.png" width="26" height="26" alt="YouTube" class="cosy-sig-social-icon"></a>
                                                            <?php endif; ?>
                                                            <?php if (!empty($sig_li)) : ?>
                                                            <a href="<?php echo esc_url($sig_li); ?>" target="_blank" class="cosy-sig-social-link"><img src="https://wppremiumplugins.com/cosychats/wp-content/uploads/2026/08/Linkedin.png" width="26" height="26" alt="LinkedIn" class="cosy-sig-social-icon"></a>
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

                        <!-- SMTP Settings Tab -->
                        <div class="tab-pane fade" id="v-pills-smtp" role="tabpanel" aria-labelledby="v-pills-smtp-tab">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h3 class="fw-bold text-dark m-0 d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-envelope-open-text fs-2 cosy-settings-icon-purple"></i>
                                    <?php _e('SMTP Email Delivery Settings', 'cosy-appointments'); ?>
                                </h3>
                                <div class="d-flex align-items-center m-0">
                                    <label class="cosy-switch">
                                        <input type="checkbox" name="cosy_smtp_enabled" id="cosy_smtp_enabled" value="1" <?php checked(1, get_option('cosy_smtp_enabled', 1)); ?>>
                                        <span class="cosy-slider round"></span>
                                    </label>
                                    <span class="fw-semibold text-secondary ms-2"><?php _e('Enable Dynamic SMTP', 'cosy-appointments'); ?></span>
                                </div>
                            </div>

                            <div class="alert alert-info border-0 rounded-3 p-3 mb-4 d-flex align-items-center cosy-settings-alert-info">
                                <i class="fa-solid fa-circle-info fs-4 me-3 cosy-settings-icon-purple"></i>
                                <div>
                                    <strong><?php _e('Dynamic SMTP Delivery:', 'cosy-appointments'); ?></strong>
                                    <?php _e('All system emails (Booking Confirmations, Status Updates, Reviews, Password Resets) will use these credentials automatically.', 'cosy-appointments'); ?>
                                </div>
                            </div>

                            <div class="row g-4">
                                <div class="col-md-8">
                                    <label for="cosy_smtp_host" class="form-label fw-bold text-secondary"><?php _e('SMTP Host / Server', 'cosy-appointments'); ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-server"></i></span>
                                        <input type="text" class="form-control border-start-0 py-2" name="cosy_smtp_host" id="cosy_smtp_host" value="<?php echo esc_attr(get_option('cosy_smtp_host', 'smtp.gmail.com')); ?>" placeholder="e.g. smtp.gmail.com">
                                    </div>
                                    <div class="form-text text-muted mt-1"><?php _e('Your outgoing mail server hostname (e.g. smtp.gmail.com).', 'cosy-appointments'); ?></div>
                                </div>

                                <div class="col-md-4">
                                    <label for="cosy_smtp_port" class="form-label fw-bold text-secondary"><?php _e('SMTP Port', 'cosy-appointments'); ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-network-wired"></i></span>
                                        <input type="number" class="form-control border-start-0 py-2" name="cosy_smtp_port" id="cosy_smtp_port" value="<?php echo esc_attr(get_option('cosy_smtp_port', 587)); ?>" placeholder="587">
                                    </div>
                                    <div class="form-text text-muted mt-1"><?php _e('Common ports: 587 (TLS), 465 (SSL), 25 (Non-secure).', 'cosy-appointments'); ?></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="cosy_smtp_encryption" class="form-label fw-bold text-secondary"><?php _e('Encryption Type', 'cosy-appointments'); ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-shield-halved"></i></span>
                                        <select class="form-select border-start-0 py-2" name="cosy_smtp_encryption" id="cosy_smtp_encryption">
                                            <?php $enc = get_option('cosy_smtp_encryption', 'tls'); ?>
                                            <option value="tls" <?php selected($enc, 'tls'); ?>><?php _e('TLS (Recommended for Port 587)', 'cosy-appointments'); ?></option>
                                            <option value="ssl" <?php selected($enc, 'ssl'); ?>><?php _e('SSL (Port 465)', 'cosy-appointments'); ?></option>
                                            <option value="none" <?php selected($enc, 'none'); ?>><?php _e('None (Unencrypted)', 'cosy-appointments'); ?></option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="cosy_smtp_auth" class="form-label fw-bold text-secondary"><?php _e('Authentication', 'cosy-appointments'); ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-key"></i></span>
                                        <select class="form-select border-start-0 py-2" name="cosy_smtp_auth" id="cosy_smtp_auth">
                                            <option value="1" <?php selected(1, get_option('cosy_smtp_auth', 1)); ?>><?php _e('Yes - Authentication Required (Default)', 'cosy-appointments'); ?></option>
                                            <option value="0" <?php selected(0, get_option('cosy_smtp_auth', 1)); ?>><?php _e('No - Anonymous / No Auth', 'cosy-appointments'); ?></option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="cosy_smtp_user" class="form-label fw-bold text-secondary"><?php _e('SMTP Username / Email', 'cosy-appointments'); ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-user"></i></span>
                                        <input type="text" class="form-control border-start-0 py-2" name="cosy_smtp_user" id="cosy_smtp_user" value="<?php echo esc_attr(get_option('cosy_smtp_user', 'contact@cosychats.com')); ?>" placeholder="e.g. contact@cosychats.com">
                                    </div>
                                    <div class="form-text text-muted mt-1"><?php _e('Your outgoing SMTP login email address.', 'cosy-appointments'); ?></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="cosy_smtp_pass" class="form-label fw-bold text-secondary"><?php _e('SMTP Password / App Password', 'cosy-appointments'); ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-lock"></i></span>
                                        <input type="password" class="form-control border-start-0 border-end-0 py-2" name="cosy_smtp_pass" id="cosy_smtp_pass" value="<?php echo esc_attr(get_option('cosy_smtp_pass', 'suln klpu wrwp bsvy')); ?>" placeholder="App Password">
                                        <button type="button" class="btn btn-outline-secondary border-start-0 cosy-smtp-toggle-pass-btn" id="btn-toggle-smtp-pass">
                                            <i class="fa-solid fa-eye" id="eye-smtp-pass"></i>
                                        </button>
                                    </div>
                                    <div class="form-text text-muted mt-1"><?php _e('For Gmail/Google Workspace, enter your 16-character App Password.', 'cosy-appointments'); ?></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="cosy_smtp_from_name" class="form-label fw-bold text-secondary"><?php _e('From Name', 'cosy-appointments'); ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-id-badge"></i></span>
                                        <input type="text" class="form-control border-start-0 py-2" name="cosy_smtp_from_name" id="cosy_smtp_from_name" value="<?php echo esc_attr(get_option('cosy_smtp_from_name', 'CosyChats')); ?>" placeholder="CosyChats">
                                    </div>
                                    <div class="form-text text-muted mt-1"><?php _e('The sender name displayed in email inboxes.', 'cosy-appointments'); ?></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="cosy_smtp_from_email" class="form-label fw-bold text-secondary"><?php _e('From Email Address', 'cosy-appointments'); ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-envelope"></i></span>
                                        <input type="email" class="form-control border-start-0 py-2" name="cosy_smtp_from_email" id="cosy_smtp_from_email" value="<?php echo esc_attr(get_option('cosy_smtp_from_email', 'contact@cosychats.com')); ?>" placeholder="<?php _e('Enter Email Address', 'cosy-appointments'); ?>">
                                    </div>
                                    <div class="form-text text-muted mt-1"><?php _e('The sender email address (matches SMTP account).', 'cosy-appointments'); ?></div>
                                </div>
                            </div>
                                                                
                            <!-- Live SMTP Test Email Card -->
                            <div class="card border border-0 shadow-sm mt-4 rounded-4 cosy-smtp-test-card">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="fa-solid fa-vial-circle-check fs-4 cosy-settings-icon-purple"></i>
                                        <h5 class="fw-bold text-dark m-0"><?php _e('Send Test Email', 'cosy-appointments'); ?></h5>
                                    </div>
                                    <p class="text-muted small mb-3"><?php _e('Verify your SMTP settings immediately by dispatching a test email. Remember to click "Save Configurations" first if you changed any credentials above.', 'cosy-appointments'); ?></p>
                                    
                                    <div class="row g-2 align-items-center">
                                        <div class="col-md-7">
                                            <input type="email" id="cosy_test_smtp_recipient" class="form-control py-2" placeholder="Enter recipient email..." value="<?php echo esc_attr(get_option('admin_email')); ?>">
                                        </div>
                                        <div class="col-md-5">
                                            <button type="button" id="btn-send-smtp-test" class="btn btn-cosy-smtp-test w-100 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2 text-white">
                                                <i class="fa-solid fa-paper-plane"></i>
                                                <span id="btn-test-text"><?php _e('Send Test Email', 'cosy-appointments'); ?></span>
                                            </button>
                                        </div>
                                    </div>
                                    <div id="smtp-test-result" class="mt-3 cosy-smtp-test-result"></div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Action Buttons Card Footer -->
                    <div class="d-flex align-items-center justify-content-end gap-3 mt-4">
                        <button type="submit" class="btn btn-primary d-flex align-items-center gap-2 py-2.5 px-4 rounded-3 border-0 fw-semibold text-white shadow-sm cosy-settings-submit-btn">
                            <i class="fa-solid fa-circle-check fs-5"></i>
                            Save Configurations
                        </button>
                    </div>

                </div>
            </div>
        </form>
    </div>
</div>

