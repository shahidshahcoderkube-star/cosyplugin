/**
 * CosyChats - Email Templates Manager Admin JS
 * 
 * Handles real-time live email previewing, debounced typing sync,
 * TinyMCE form synchronization, template saving, resetting, and test email sending.
 * 
 * @package Cosy\Appointments\Email
 */

jQuery(document).ready(function($) {
    'use strict';

    // Click to copy dynamic tag pills
    $(document).on('click', '.btn-copy-tag', function() {
        const tag = $(this).data('tag');
        if (!tag) return;
        
        navigator.clipboard.writeText(tag);
        const origText = $(this).text();
        $(this).text('Copied!').css({ background: '#a44390', color: '#ffffff' });
        setTimeout(() => {
            $(this).text(origText).css({ background: '', color: '' });
        }, 600);
    });

    // Helper: get current form values with full TinyMCE sync
    function getFormData() {
        if (typeof tinyMCE !== 'undefined') {
            tinyMCE.triggerSave();
        }

        let bodyContent = '';
        const bodyEditor = (typeof tinyMCE !== 'undefined') ? tinyMCE.get('cosy_email_body_text') : null;
        if (bodyEditor && !bodyEditor.isHidden()) {
            bodyContent = bodyEditor.getContent();
        } else {
            bodyContent = $('#cosy_email_body_text').val();
        }

        let outroContent = '';
        const outroEditor = (typeof tinyMCE !== 'undefined') ? tinyMCE.get('cosy_email_outro_text') : null;
        if (outroEditor && !outroEditor.isHidden()) {
            outroContent = outroEditor.getContent();
        } else {
            outroContent = $('#cosy_email_outro_text').val();
        }

        return {
            security: $('#cosy_email_tpl_security').val(),
            template_key: $('#cosy_template_key').val(),
            subject: $('#cosy_email_subject').val(),
            heading: $('#cosy_email_heading').val(),
            body_text: bodyContent,
            outro_text: outroContent
        };
    }

    // Function to update the Live Preview on the right side
    function updateLivePreview(callback) {
        const formData = getFormData();
        formData.action = 'cosy_admin_preview_email_template';

        $('#previewEmailBodyContainer').css('opacity', '0.5');

        const ajaxEndpoint = typeof ajaxurl !== 'undefined' ? ajaxurl : (window.cosyEmailTemplatesAdmin ? window.cosyEmailTemplatesAdmin.ajaxurl : '/wp-admin/admin-ajax.php');

        $.ajax({
            url: ajaxEndpoint,
            type: 'POST',
            data: formData,
            success: function(res) {
                $('#previewEmailBodyContainer').css('opacity', '1');
                if (res.success && res.data) {
                    $('#previewSubjectText').text(res.data.subject);
                    $('#previewEmailBodyContainer').html(res.data.html);
                } else {
                    $('#previewEmailBodyContainer').html('<div class="p-4 text-center text-danger">Failed to render email preview.</div>');
                }
                if (typeof callback === 'function') {
                    callback(true);
                }
            },
            error: function() {
                $('#previewEmailBodyContainer').css('opacity', '1');
                $('#previewEmailBodyContainer').html('<div class="p-4 text-center text-danger">Server error rendering email preview.</div>');
                if (typeof callback === 'function') {
                    callback(false);
                }
            }
        });
    }

    // Automatically load live preview on initial page load
    setTimeout(function() { updateLivePreview(); }, 300);

    // Update preview on manual button click with clear visual feedback
    $(document).on('click', '#btnTriggerPreview, #btnManualRefreshPreview', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const origHtml = $btn.html();
        
        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Updating...');

        updateLivePreview(function(success) {
            if (success) {
                $btn.html('<i class="fa-solid fa-check text-success me-1"></i> Updated!');
            } else {
                $btn.html('<i class="fa-solid fa-triangle-exclamation text-danger me-1"></i> Error');
            }
            setTimeout(function() {
                $btn.prop('disabled', false).html(origHtml);
            }, 900);
        });
    });

    // Real-time debounced auto-sync as user types
    let previewDebounceTimer = null;
    function scheduleAutoPreview() {
        clearTimeout(previewDebounceTimer);
        previewDebounceTimer = setTimeout(function() {
            updateLivePreview();
        }, 500);
    }

    // Update preview when subject or heading changes
    $('#cosy_email_subject, #cosy_email_heading').on('input blur change', scheduleAutoPreview);
    $('#cosy_email_body_text, #cosy_email_outro_text').on('input change', scheduleAutoPreview);

    // Hook into TinyMCE editors for instant preview as user types
    function hookTinyMCEEvents() {
        if (typeof tinyMCE !== 'undefined') {
            ['cosy_email_body_text', 'cosy_email_outro_text'].forEach(function(editorId) {
                const ed = tinyMCE.get(editorId);
                if (ed) {
                    ed.on('keyup change input NodeChange', scheduleAutoPreview);
                }
            });
        }
    }
    setTimeout(hookTinyMCEEvents, 600);
    setTimeout(hookTinyMCEEvents, 1500);

    // Save Template AJAX
    $('#cosyEmailTemplateForm').on('submit', function(e) {
        e.preventDefault();
        
        const $btn = $('#btnSaveCosyEmailTpl');
        const origText = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Saving...');

        const formData = getFormData();
        formData.action = 'cosy_admin_save_email_template';

        const ajaxEndpoint = typeof ajaxurl !== 'undefined' ? ajaxurl : (window.cosyEmailTemplatesAdmin ? window.cosyEmailTemplatesAdmin.ajaxurl : '/wp-admin/admin-ajax.php');

        $.ajax({
            url: ajaxEndpoint,
            type: 'POST',
            data: formData,
            success: function(res) {
                $btn.prop('disabled', false).html(origText);
                const $alert = $('#cosyEmailTplAlert');
                if (res.success) {
                    $alert.css({ background: '#dcfce7', color: '#166534', border: '1px solid #bbf7d0' })
                          .html('<i class="fa-solid fa-circle-check me-1"></i> ' + res.data).slideDown(200);
                    updateLivePreview();
                } else {
                    $alert.css({ background: '#fee2e2', color: '#991b1b', border: '1px solid #fecaca' })
                          .html('<i class="fa-solid fa-triangle-exclamation me-1"></i> ' + (res.data || 'Failed to save.')).slideDown(200);
                }
                setTimeout(() => { $alert.slideUp(200); }, 3500);
            },
            error: function() {
                $btn.prop('disabled', false).html(origText);
                alert('Error saving template settings.');
            }
        });
    });

    // Reset Template AJAX
    $('#btnResetCosyEmailTpl').on('click', function(e) {
        e.preventDefault();
        if (!confirm('Are you sure you want to reset this email template to default values?')) {
            return;
        }

        const $btn = $(this);
        $btn.prop('disabled', true);

        const ajaxEndpoint = typeof ajaxurl !== 'undefined' ? ajaxurl : (window.cosyEmailTemplatesAdmin ? window.cosyEmailTemplatesAdmin.ajaxurl : '/wp-admin/admin-ajax.php');

        $.ajax({
            url: ajaxEndpoint,
            type: 'POST',
            data: {
                action: 'cosy_admin_reset_email_template',
                security: $('#cosy_email_tpl_security').val(),
                template_key: $('#cosy_template_key').val()
            },
            success: function(res) {
                $btn.prop('disabled', false);
                if (res.success && res.data.template) {
                    const tpl = res.data.template;
                    $('#cosy_email_subject').val(tpl.subject);
                    $('#cosy_email_heading').val(tpl.heading);
                    
                    if (typeof tinyMCE !== 'undefined' && tinyMCE.get('cosy_email_body_text')) {
                        tinyMCE.get('cosy_email_body_text').setContent(tpl.body_text);
                    } else {
                        $('#cosy_email_body_text').val(tpl.body_text);
                    }

                    if (typeof tinyMCE !== 'undefined' && tinyMCE.get('cosy_email_outro_text')) {
                        tinyMCE.get('cosy_email_outro_text').setContent(tpl.outro_text);
                    } else {
                        $('#cosy_email_outro_text').val(tpl.outro_text);
                    }

                    const $alert = $('#cosyEmailTplAlert');
                    $alert.css({ background: '#dcfce7', color: '#166534', border: '1px solid #bbf7d0' })
                          .html('<i class="fa-solid fa-circle-check me-1"></i> ' + res.data.message).slideDown(200);
                    setTimeout(() => { $alert.slideUp(200); }, 3500);

                    updateLivePreview();
                }
            },
            error: function() {
                $btn.prop('disabled', false);
                alert('Error resetting template.');
            }
        });
    });

    // Send Test Email AJAX
    $('#btnSendTestEmail').on('click', function(e) {
        e.preventDefault();
        
        const $btn = $(this);
        const testEmail = $('#cosy_test_email_address').val().trim();
        const $alert = $('#cosyTestEmailAlert');

        if (!testEmail) {
            $alert.css({ background: '#fee2e2', color: '#991b1b', border: '1px solid #fecaca' })
                  .html('Please enter a recipient email address.').slideDown(200);
            setTimeout(() => { $alert.slideUp(200); }, 3500);
            return;
        }

        const origHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Sending...');

        const formData = getFormData();
        formData.action = 'cosy_admin_send_test_email';
        formData.test_email = testEmail;

        const ajaxEndpoint = typeof ajaxurl !== 'undefined' ? ajaxurl : (window.cosyEmailTemplatesAdmin ? window.cosyEmailTemplatesAdmin.ajaxurl : '/wp-admin/admin-ajax.php');

        $.ajax({
            url: ajaxEndpoint,
            type: 'POST',
            data: formData,
            success: function(res) {
                $btn.prop('disabled', false).html(origHtml);
                if (res.success) {
                    $alert.css({ background: '#dcfce7', color: '#166534', border: '1px solid #bbf7d0' })
                          .html('<i class="fa-solid fa-circle-check me-1"></i> ' + res.data).slideDown(200);
                } else {
                    $alert.css({ background: '#fee2e2', color: '#991b1b', border: '1px solid #fecaca' })
                          .html('<i class="fa-solid fa-triangle-exclamation me-1"></i> ' + (res.data || 'Failed to send test email.')).slideDown(200);
                }
                setTimeout(() => { $alert.slideUp(200); }, 5000);
            },
            error: function() {
                $btn.prop('disabled', false).html(origHtml);
                $alert.css({ background: '#fee2e2', color: '#991b1b', border: '1px solid #fecaca' })
                      .html('Server error while sending test email.').slideDown(200);
                setTimeout(() => { $alert.slideUp(200); }, 5000);
            }
        });
    });
});
