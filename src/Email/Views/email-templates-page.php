<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin Backend Template: Email Templates Settings
 * 
 * Side-by-Side Modern Split View:
 * Left: Compact, intuitive Template Editor.
 * Right: Real-time Live Email Preview & Send Test Email Widget.
 */
$all_templates = \Cosy\Appointments\Email\EmailTemplatesAdmin::get_default_email_templates();
$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'customer_booking';
if (!array_key_exists($active_tab, $all_templates)) {
    $active_tab = 'customer_booking';
}

$current_settings = \Cosy\Appointments\Email\EmailTemplatesAdmin::get_template_settings($active_tab);
$current_user = wp_get_current_user();
$default_test_email = $current_user->user_email ?: get_option('admin_email');
?>

<div class="wrap cosy-email-wrap">
    
    <!-- Top Compact Header Banner -->
    <div class="d-flex align-items-center justify-content-between p-3 px-4 mb-3 rounded-4 text-white shadow-sm" style="background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%);">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(255, 255, 255, 0.2);">
                <i class="fa-solid fa-envelope-open-text fs-4 text-white"></i>
            </div>
            <div>
                <h1 class="h4 fw-bold mb-0 text-white" style="letter-spacing: -0.3px;">
                    <?php esc_html_e('Email Templates Manager', 'cosy-appointments'); ?>
                </h1>
                <p class="mb-0 text-white-50 small" style="font-size: 12px;">
                    <?php esc_html_e('Customize email content on the left, review real-time preview on the right, and send test emails instantly.', 'cosy-appointments'); ?>
                </p>
            </div>
        </div>
        <div>
            <span class="badge bg-white text-dark fw-bold px-3 py-2 rounded-pill shadow-sm" style="color: #6d2e67 !important;">
                <i class="fa-solid fa-shield-halved me-1 text-success"></i> <?php esc_html_e('Tables & Signature Protected', 'cosy-appointments'); ?>
            </span>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="mb-3">
        <ul class="nav nav-tabs border-bottom border-2 flex-nowrap overflow-x-auto" style="gap: 3px;">
            <?php foreach ($all_templates as $key => $tpl) : 
                $is_active = ($key === $active_tab);
                $tab_url = add_query_arg(['page' => 'cosy-email', 'tab' => $key], admin_url('admin.php'));
            ?>
                <li class="nav-item">
                    <a href="<?php echo esc_url($tab_url); ?>" class="nav-link text-nowrap <?php echo $is_active ? 'active' : ''; ?>">
                        <?php echo esc_html($tpl['title']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- SIDE-BY-SIDE 2-COLUMN LAYOUT -->
    <div class="row g-3">
        
        <!-- LEFT COLUMN: Compact Form Editor (50% Width) -->
        <div class="col-xl-6 col-lg-6 col-md-12">
            <div class="cosy-form-card p-4">
                
                <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                    <div>
                        <h2 class="h5 fw-bold m-0 text-dark">
                            <?php echo esc_html($all_templates[$active_tab]['title']); ?>
                        </h2>
                        <span class="text-muted small" style="font-size: 12px;">
                            Template ID: <code><?php echo esc_html($active_tab); ?></code>
                        </span>
                    </div>
                    <?php 
                    $is_button_template = in_array($active_tab, ['customer_verification', 'provider_verification', 'password_reset', 'review_invite']);
                    ?>
                    <div class="d-flex align-items-center gap-1 flex-wrap">
                        <span class="text-muted small me-1" style="font-size: 11px;">Copy Tag:</span>
                        <span class="cosy-tag-pill btn-copy-tag" data-tag="{customer_name}" title="Click to copy">{customer_name}</span>
                        <span class="cosy-tag-pill btn-copy-tag" data-tag="{provider_name}" title="Click to copy">{provider_name}</span>
                        <?php if (in_array($active_tab, ['customer_verification', 'provider_verification'])): ?>
                            <span class="cosy-tag-pill btn-copy-tag" data-tag="{verify_url}" title="Click to copy" style="background:#fdf2fb; border-color:#a44390; color:#a44390;">{verify_url}</span>
                        <?php elseif ($active_tab === 'password_reset'): ?>
                            <span class="cosy-tag-pill btn-copy-tag" data-tag="{reset_url}" title="Click to copy" style="background:#fdf2fb; border-color:#a44390; color:#a44390;">{reset_url}</span>
                        <?php elseif ($active_tab === 'review_invite'): ?>
                            <span class="cosy-tag-pill btn-copy-tag" data-tag="{review_url}" title="Click to copy" style="background:#fdf2fb; border-color:#a44390; color:#a44390;">{review_url}</span>
                        <?php else: ?>
                            <span class="cosy-tag-pill btn-copy-tag" data-tag="{service_name}" title="Click to copy">{service_name}</span>
                            <span class="cosy-tag-pill btn-copy-tag" data-tag="{order_id}" title="Click to copy">{order_id}</span>
                        <?php endif; ?>
                    </div>
                </div>

                <form id="cosyEmailTemplateForm">
                    <?php wp_nonce_field('cosy_admin_email_tpl_nonce', 'cosy_email_tpl_security'); ?>
                    <input type="hidden" name="template_key" id="cosy_template_key" value="<?php echo esc_attr($active_tab); ?>">

                    <!-- 1. Email Subject -->
                    <div class="mb-3">
                        <label for="cosy_email_subject" class="cosy-field-label">
                            <?php esc_html_e('1. Email Subject Line', 'cosy-appointments'); ?> <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="subject" id="cosy_email_subject" class="form-control fw-semibold py-2 px-3 rounded-3" value="<?php echo esc_attr($current_settings['subject']); ?>" required>
                    </div>

                    <!-- 2. Email Heading -->
                    <div class="mb-3">
                        <label for="cosy_email_heading" class="cosy-field-label">
                            <?php esc_html_e('2. Email Banner Title', 'cosy-appointments'); ?>
                        </label>
                        <input type="text" name="heading" id="cosy_email_heading" class="form-control fw-semibold py-2 px-3 rounded-3" value="<?php echo esc_attr($current_settings['heading']); ?>">
                    </div>

                    <!-- 3. Intro Body Text -->
                    <div class="mb-3">
                        <label for="cosy_email_body_text" class="cosy-field-label">
                            <?php echo $is_button_template 
                                ? esc_html__('3. Intro Content (Above Action Button)', 'cosy-appointments') 
                                : esc_html__('3. Intro Content (Above System Tables)', 'cosy-appointments'); ?>
                        </label>
                        <?php 
                        $editor_settings = [
                            'textarea_name' => 'body_text',
                            'textarea_rows' => 4,
                            'media_buttons' => false,
                            'tinymce'       => [
                                'theme_advanced_buttons1' => 'bold,italic,underline,separator,bullist,numlist,separator,link,unlink,undo,redo',
                            ],
                            'quicktags'     => true,
                        ];
                        wp_editor($current_settings['body_text'], 'cosy_email_body_text', $editor_settings);
                        ?>
                    </div>

                    <!-- 4. Compact Locked System Table / Button Bar -->
                    <?php if ($is_button_template): ?>
                        <div class="cosy-locked-table-bar">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge text-white" style="background: #a44390;"><i class="fa-solid fa-square-check"></i></span>
                                <span class="fw-bold text-dark">
                                    <?php esc_html_e('Dynamic Action Button Renders Here', 'cosy-appointments'); ?>
                                </span>
                                <span class="text-muted small">(Verify / Reset / Action Button)</span>
                            </div>
                            <span class="badge bg-light text-secondary border">🔒 Locked &amp; Protected</span>
                        </div>
                    <?php else: ?>
                        <div class="cosy-locked-table-bar">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge text-white" style="background: #a44390;"><i class="fa-solid fa-lock"></i></span>
                                <span class="fw-bold text-dark">
                                    <?php esc_html_e('Dynamic System Tables Render Here', 'cosy-appointments'); ?>
                                </span>
                                <span class="text-muted small">(Order Details, Time Slots, &amp; Fees)</span>
                            </div>
                            <span class="badge bg-light text-secondary border">🔒 Locked &amp; Protected</span>
                        </div>
                    <?php endif; ?>

                    <!-- 5. Outro Content -->
                    <div class="mb-3">
                        <label for="cosy_email_outro_text" class="cosy-field-label d-flex align-items-center justify-content-between">
                            <span>
                                <?php echo $is_button_template 
                                    ? esc_html__('4. Outro Content (Below Action Button - Includes Fallback Link & Text)', 'cosy-appointments') 
                                    : esc_html__('4. Outro Content (Below System Tables)', 'cosy-appointments'); ?>
                            </span>
                        </label>
                        <?php if ($is_button_template): ?>
                            <p class="text-muted small mb-2" style="font-size: 12px;">
                                <i class="fa-solid fa-circle-info" style="color: #a44390;"></i>
                                <?php esc_html_e('You can freely customize the text below the button. The fallback link uses', 'cosy-appointments'); ?> 
                                <code><?php echo ($active_tab === 'password_reset') ? '{reset_url}' : (($active_tab === 'review_invite') ? '{review_url}' : '{verify_url}'); ?></code> 
                                <?php esc_html_e('which is automatically replaced by the real dynamic link.', 'cosy-appointments'); ?>
                            </p>
                        <?php endif; ?>
                        <?php 
                        $outro_editor_settings = [
                            'textarea_name' => 'outro_text',
                            'textarea_rows' => 4,
                            'media_buttons' => false,
                            'tinymce'       => [
                                'theme_advanced_buttons1' => 'bold,italic,underline,separator,bullist,numlist,separator,link,unlink,undo,redo',
                            ],
                            'quicktags'     => true,
                        ];
                        wp_editor($current_settings['outro_text'], 'cosy_email_outro_text', $outro_editor_settings);
                        ?>
                    </div>

                    <!-- Signature Note -->
                    <div class="d-flex align-items-center justify-content-between p-2 px-3 bg-light rounded-3 border mb-3 small text-muted">
                        <div>
                            <i class="fa-solid fa-signature text-primary me-1" style="color: #a44390 !important;"></i>
                            <?php esc_html_e('Official brand signature automatically attached at bottom.', 'cosy-appointments'); ?>
                        </div>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=cosy-settings&tab=signature')); ?>" target="_blank" class="fw-semibold" style="color: #a44390; font-size: 12px; text-decoration: none;">
                            Edit Signature &rarr;
                        </a>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex align-items-center justify-content-between pt-2 border-top gap-2">
                        <button type="button" id="btnResetCosyEmailTpl" class="btn btn-outline-danger btn-sm fw-semibold px-3 py-2 rounded-3">
                            <i class="fa-solid fa-rotate-left me-1"></i> <?php esc_html_e('Reset Default', 'cosy-appointments'); ?>
                        </button>
                        
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" id="btnTriggerPreview" class="btn btn-outline-secondary btn-sm fw-semibold px-3 py-2 rounded-3">
                                <i class="fa-solid fa-rotate me-1"></i> <?php esc_html_e('Update Preview', 'cosy-appointments'); ?>
                            </button>
                            <button type="submit" id="btnSaveCosyEmailTpl" class="btn btn-sm fw-bold px-4 py-2 rounded-3 text-white shadow-sm" style="background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%); border-color: #a44390;">
                                <i class="fa-solid fa-floppy-disk me-1"></i> <?php esc_html_e('Save Changes', 'cosy-appointments'); ?>
                            </button>
                        </div>
                    </div>

                    <div id="cosyEmailTplAlert" class="mt-3 p-2 px-3 rounded-3 fw-semibold small" style="display: none;"></div>
                </form>

            </div>
        </div>

        <!-- RIGHT COLUMN: Real-Time Live Preview & Test Mail Widget (50% Width) -->
        <div class="col-xl-6 col-lg-6 col-md-12 cosy-preview-col">
            <div class="cosy-preview-col-inner">
                
                <!-- Quick Send Test Email Bar -->
                <div class="card mt-0 p-3 rounded-4 border shadow-sm mb-3 bg-white" style="margin-top: 0 !important; flex-shrink: 0;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fw-bold text-dark small d-flex align-items-center gap-2">
                            <i class="fa-solid fa-paper-plane" style="color: #a44390;"></i>
                            <?php esc_html_e('Send Real Test Email', 'cosy-appointments'); ?>
                        </span>
                        <span class="text-muted" style="font-size: 11px;">
                            <?php esc_html_e('Sends live email with sample data', 'cosy-appointments'); ?>
                        </span>
                    </div>
                    <div class="input-group">
                        <input type="email" id="cosy_test_email_address" class="form-control py-2 px-3 rounded-start-3" value="<?php echo esc_attr($default_test_email); ?>" placeholder="your-email@example.com">
                        <button type="button" id="btnSendTestEmail" class="btn fw-bold px-3 text-white rounded-end-3" style="background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%);">
                            <i class="fa-solid fa-paper-plane me-1"></i> <?php esc_html_e('Send Test', 'cosy-appointments'); ?>
                        </button>
                    </div>
                    <div id="cosyTestEmailAlert" class="mt-2 p-2 rounded-3 fw-semibold small" style="display: none;"></div>
                </div>

                <!-- Real-Time Interactive Live Preview Card -->
                <div class="cosy-live-preview-box">
                    <div class="cosy-preview-header">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-desktop" style="color: #a44390;"></i>
                            <span class="fw-bold text-dark small"><?php esc_html_e('Interactive Live Email Preview', 'cosy-appointments'); ?></span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill" style="font-size: 10px;">Live Sync</span>
                        </div>
                        <button type="button" id="btnManualRefreshPreview" class="btn btn-sm btn-light border py-1 px-2 fw-semibold" style="font-size: 11px;" title="Refresh preview from current form inputs">
                            <i class="fa-solid fa-rotate me-1 text-secondary"></i> Refresh
                        </button>
                    </div>

                    <div class="cosy-preview-subject-bar">
                        <span class="fw-bold text-dark">Subject:</span>
                        <span id="previewSubjectText" class="fw-semibold text-primary" style="color: #a44390 !important;">Loading...</span>
                    </div>

                    <div class="cosy-preview-body" id="previewEmailBodyContainer">
                        <div class="text-center py-5 text-muted">
                            <i class="fa-solid fa-spinner fa-spin fs-3 mb-2" style="color: #a44390;"></i>
                            <div><?php esc_html_e('Rendering real-time email preview...', 'cosy-appointments'); ?></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

</div>


