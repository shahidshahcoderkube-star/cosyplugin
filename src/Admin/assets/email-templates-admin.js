/**
 * email-templates-admin.js
 * Admin JavaScript controller for the Cosy Appointments Email Templates Manager.
 *
 * PURPOSE:
 *  - Manages real-time live preview synchronization as administrators edit templates.
 *  - Handles template saving, resetting to core defaults, and sending test emails via AJAX.
 *  - Controls horizontal navigation tabs with scroll retention and mouse-wheel scrolling.
 *
 * ARCHITECTURE:
 *  - Follows the Object-Literal Namespace pattern (matching admin.js & users-admin.js).
 *  - Encapsulated inside a single jQuery ready wrapper to prevent global scope pollution.
 *  - All DOM selectors are cached for maximum performance.
 *
 * MODULES:
 *  - CosyEmailTemplatesAdmin : Central controller for the Email Templates admin interface.
 *
 * @package Cosy\Appointments\Email
 */
jQuery(document).ready(function ($) {
    'use strict';

    // =========================================================================
    // MODULE: CosyEmailTemplatesAdmin
    // Central Controller Object for Email Templates Admin Page
    // =========================================================================
    var CosyEmailTemplatesAdmin = {

        /**
         * Cached DOM element references
         * Caching selectors avoids repeated DOM lookups on fast typing/scrolling.
         */
        $form: null,             // Main email template edit form
        $navTabs: null,          // Horizontal navigation tabs container
        $activeTab: null,        // Currently active tab link
        $previewContainer: null, // Right-side email HTML preview container
        $previewSubject: null,   // Right-side email subject bar
        $saveBtn: null,          // Save Changes submit button
        $resetBtn: null,         // Reset to Default button
        $sendTestBtn: null,      // Send Real Test Email button
        $templateAlert: null,    // Feedback alert banner for save/reset actions
        $testAlert: null,        // Feedback alert banner for test email sending

        /**
         * Debounce timer ID for real-time live preview typing sync.
         */
        previewDebounceTimer: null,

        /**
         * ---------------------------------------------------------------------
         * 1. INITIALIZATION & SETUP
         * ---------------------------------------------------------------------
         */

        /**
         * init
         * Primary entry point. Bootstraps cached selectors, tab scrolling,
         * event listeners, TinyMCE editor hooks, and initial live preview.
         *
         * @return {void}
         */
        init: function () {
            // Step 1: Cache all frequently queried DOM elements
            this.cacheDOM();

            // Step 2: Initialize horizontal tabs (scroll retention & wheel support)
            this.initTabs();

            // Step 3: Bind all DOM click, change, and submit event handlers
            this.bindEvents();

            // Step 4: Hook into WordPress TinyMCE rich-text editors for live typing sync
            this.hookTinyMCE();

            // Step 5: Render initial email preview on page load
            this.initInitialPreview();
        },

        /**
         * cacheDOM
         * Queries and caches all key DOM elements used throughout the module.
         *
         * @return {void}
         */
        cacheDOM: function () {
            this.$form             = $('#cosyEmailTemplateForm');
            this.$navTabs          = $('.cosy-email-wrap .nav-tabs');
            this.$activeTab        = this.$navTabs.find('.nav-link.active');
            this.$previewContainer = $('#previewEmailBodyContainer');
            this.$previewSubject   = $('#previewSubjectText');
            this.$saveBtn          = $('#btnSaveCosyEmailTpl');
            this.$resetBtn         = $('#btnResetCosyEmailTpl');
            this.$sendTestBtn      = $('#btnSendTestEmail');
            this.$templateAlert    = $('#cosyEmailTplAlert');
            this.$testAlert        = $('#cosyTestEmailAlert');
        },

        /**
         * getAjaxUrl
         * Resolves the WordPress admin-ajax.php endpoint URL safely.
         * Falls back gracefully if localization object is unavailable.
         *
         * @return {string} Admin AJAX URL
         */
        getAjaxUrl: function () {
            if (typeof ajaxurl !== 'undefined') {
                return ajaxurl;
            }
            if (window.cosyEmailTemplatesAdmin && window.cosyEmailTemplatesAdmin.ajaxurl) {
                return window.cosyEmailTemplatesAdmin.ajaxurl;
            }
            return '/wp-admin/admin-ajax.php';
        },

        /**
         * ---------------------------------------------------------------------
         * 2. HORIZONTAL TABS & SCROLL NAVIGATION
         * ---------------------------------------------------------------------
         */

        /**
         * initTabs
         * Handles horizontal scroll retention across page refreshes, auto-centers
         * the active tab in view, and enables mouse wheel left-right navigation.
         *
         * @return {void}
         */
        initTabs: function () {
            var self = this;
            if (!this.$navTabs.length || !this.$activeTab.length) {
                return;
            }

            // A. Restore saved horizontal scroll position from sessionStorage
            var savedScroll = sessionStorage.getItem('cosy_email_tabs_scroll');
            if (savedScroll !== null) {
                this.$navTabs.scrollLeft(parseInt(savedScroll, 10));
            }

            // B. Ensure the currently active tab is comfortably in the visible viewport
            var containerScrollLeft = this.$navTabs.scrollLeft();
            var containerWidth      = this.$navTabs.width();
            var tabOffsetLeft       = this.$activeTab[0].offsetLeft;
            var tabWidth            = this.$activeTab.outerWidth();

            // If active tab is partially or fully hidden, smoothly center it
            if (tabOffsetLeft < containerScrollLeft || (tabOffsetLeft + tabWidth) > (containerScrollLeft + containerWidth)) {
                var targetScroll = tabOffsetLeft - (containerWidth / 2) + (tabWidth / 2);
                this.$navTabs.scrollLeft(Math.max(0, targetScroll));
                sessionStorage.setItem('cosy_email_tabs_scroll', this.$navTabs.scrollLeft());
            }

            // C. Save scroll position when a tab is clicked and drop browser focus ring
            $(document).on('click', '.cosy-email-wrap .nav-tabs .nav-link', function () {
                if (self.$navTabs.length) {
                    sessionStorage.setItem('cosy_email_tabs_scroll', self.$navTabs.scrollLeft());
                }
                $(this).blur(); // Remove default browser focus outline
            });

            // D. Save scroll position whenever administrator manually scrolls the tab bar
            this.$navTabs.on('scroll', function () {
                sessionStorage.setItem('cosy_email_tabs_scroll', self.$navTabs.scrollLeft());
            });

            // E. Enable mouse wheel vertical-to-horizontal scrolling over the tab bar
            if (this.$navTabs[0]) {
                this.$navTabs[0].addEventListener('wheel', function (e) {
                    // Only intercept if user is rolling the vertical mouse wheel
                    if (Math.abs(e.deltaY) > Math.abs(e.deltaX)) {
                        e.preventDefault(); // Prevent full window vertical scroll
                        self.$navTabs[0].scrollLeft += e.deltaY;
                        sessionStorage.setItem('cosy_email_tabs_scroll', self.$navTabs[0].scrollLeft);
                    }
                }, { passive: false });
            }
        },

        /**
         * ---------------------------------------------------------------------
         * 3. EVENT BINDINGS
         * ---------------------------------------------------------------------
         */

        /**
         * bindEvents
         * Centralized binding of all click, input, change, and submit event listeners.
         *
         * @return {void}
         */
        bindEvents: function () {
            var self = this;

            // 1. Click-to-copy {tag} pills
            $(document).on('click', '.btn-copy-tag', this.handleTagCopy);

            // 2. Real-time live preview sync on text input changes
            $('#cosy_email_subject, #cosy_email_heading').on('input blur change', function () {
                self.scheduleAutoPreview();
            });
            $('#cosy_email_body_text, #cosy_email_outro_text').on('input change', function () {
                self.scheduleAutoPreview();
            });

            // 3. Manual Refresh Preview button click
            $(document).on('click', '#btnTriggerPreview, #btnManualRefreshPreview', function (e) {
                self.handleManualPreviewRefresh(e, $(this));
            });

            // 4. Save template form submission
            this.$form.on('submit', function (e) {
                self.handleTemplateSave(e);
            });

            // 5. Reset template to core defaults
            this.$resetBtn.on('click', function (e) {
                self.handleTemplateReset(e);
            });

            // 6. Send Real Test Email button click
            this.$sendTestBtn.on('click', function (e) {
                self.handleSendTestEmail(e);
            });
        },

        /**
         * handleTagCopy
         * Copies {placeholder_tag} pill to clipboard with temporary visual feedback.
         *
         * @return {void}
         */
        handleTagCopy: function () {
            var $btn = $(this);
            var tag  = $btn.data('tag');
            if (!tag) return;

            // Copy tag string to OS clipboard
            navigator.clipboard.writeText(tag);

            // Show temporary "Copied!" state
            var origText = $btn.text();
            $btn.text('Copied!').css({ background: '#a44390', color: '#ffffff' });
            setTimeout(function () {
                $btn.text(origText).css({ background: '', color: '' });
            }, 600);
        },

        /**
         * ---------------------------------------------------------------------
         * 4. FORM DATA & TINYMCE SYNCHRONIZATION
         * ---------------------------------------------------------------------
         */

        /**
         * getFormData
         * Gathers current form input values and synchronizes active TinyMCE rich-text editors.
         *
         * @return {object} Serialized form payload object
         */
        getFormData: function () {
            // Trigger WordPress TinyMCE save to synchronize iframe content with underlying textarea
            if (typeof tinyMCE !== 'undefined') {
                tinyMCE.triggerSave();
            }

            // Extract Intro Body Text from TinyMCE or raw textarea
            var bodyContent = '';
            var bodyEditor = (typeof tinyMCE !== 'undefined') ? tinyMCE.get('cosy_email_body_text') : null;
            if (bodyEditor && !bodyEditor.isHidden()) {
                bodyContent = bodyEditor.getContent();
            } else {
                bodyContent = $('#cosy_email_body_text').val();
            }

            // Extract Outro Body Text from TinyMCE or raw textarea
            var outroContent = '';
            var outroEditor = (typeof tinyMCE !== 'undefined') ? tinyMCE.get('cosy_email_outro_text') : null;
            if (outroEditor && !outroEditor.isHidden()) {
                outroContent = outroEditor.getContent();
            } else {
                outroContent = $('#cosy_email_outro_text').val();
            }

            return {
                security:     $('#cosy_email_tpl_security').val(),
                template_key: $('#cosy_template_key').val(),
                subject:      $('#cosy_email_subject').val(),
                heading:      $('#cosy_email_heading').val(),
                body_text:    bodyContent,
                outro_text:   outroContent
            };
        },

        /**
         * hookTinyMCE
         * Attaches listeners to TinyMCE editor instances so live preview updates on typing.
         * Runs with delayed retry to accommodate asynchronous TinyMCE initialization.
         *
         * @return {void}
         */
        hookTinyMCE: function () {
            var self = this;
            var attach = function () {
                if (typeof tinyMCE !== 'undefined') {
                    ['cosy_email_body_text', 'cosy_email_outro_text'].forEach(function (editorId) {
                        var ed = tinyMCE.get(editorId);
                        if (ed) {
                            // Unbind prior listeners to avoid duplicate trigger calls
                            ed.off('keyup change input NodeChange');
                            ed.on('keyup change input NodeChange', function () {
                                self.scheduleAutoPreview();
                            });
                        }
                    });
                }
            };

            // Retry attachment after 600ms and 1500ms for slow initializations
            setTimeout(attach, 600);
            setTimeout(attach, 1500);
        },

        /**
         * ---------------------------------------------------------------------
         * 5. REAL-TIME LIVE EMAIL PREVIEW
         * ---------------------------------------------------------------------
         */

        /**
         * initInitialPreview
         * Dispatches initial email preview render shortly after DOM load.
         *
         * @return {void}
         */
        initInitialPreview: function () {
            var self = this;
            setTimeout(function () {
                self.updateLivePreview();
            }, 300);
        },

        /**
         * scheduleAutoPreview
         * Debounces live preview rendering (500ms) to avoid flooding AJAX on fast typing.
         *
         * @return {void}
         */
        scheduleAutoPreview: function () {
            var self = this;
            clearTimeout(this.previewDebounceTimer);
            this.previewDebounceTimer = setTimeout(function () {
                self.updateLivePreview();
            }, 500);
        },

        /**
         * updateLivePreview
         * Sends an AJAX request to generate and render the email template preview in real time.
         *
         * @param {function} [callback] Optional callback executed upon AJAX completion
         * @return {void}
         */
        updateLivePreview: function (callback) {
            var self = this;
            var formData = this.getFormData();
            formData.action = 'cosy_admin_preview_email_template';

            // Indicate background loading by dimming preview
            this.$previewContainer.css('opacity', '0.5');

            $.ajax({
                url: self.getAjaxUrl(),
                type: 'POST',
                data: formData,
                success: function (res) {
                    self.$previewContainer.css('opacity', '1');
                    if (res.success && res.data) {
                        self.$previewSubject.text(res.data.subject);
                        self.$previewContainer.html(res.data.html);
                    } else {
                        self.$previewContainer.html('<div class="p-4 text-center text-danger">Failed to render email preview.</div>');
                    }
                    if (typeof callback === 'function') {
                        callback(true);
                    }
                },
                error: function () {
                    self.$previewContainer.css('opacity', '1');
                    self.$previewContainer.html('<div class="p-4 text-center text-danger">Server error rendering email preview.</div>');
                    if (typeof callback === 'function') {
                        callback(false);
                    }
                }
            });
        },

        /**
         * handleManualPreviewRefresh
         * Triggers manual preview update with clear button spinner and checkmark feedback.
         *
         * @param {Event}  e     Click event object
         * @param {jQuery} $btn  The clicked button element
         * @return {void}
         */
        handleManualPreviewRefresh: function (e, $btn) {
            e.preventDefault();
            var origHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Updating...');

            this.updateLivePreview(function (success) {
                if (success) {
                    $btn.html('<i class="fa-solid fa-check text-success me-1"></i> Updated!');
                } else {
                    $btn.html('<i class="fa-solid fa-triangle-exclamation text-danger me-1"></i> Error');
                }
                setTimeout(function () {
                    $btn.prop('disabled', false).html(origHtml);
                }, 900);
            });
        },

        /**
         * ---------------------------------------------------------------------
         * 6. TEMPLATE SAVE, RESET & TEST ACTIONS
         * ---------------------------------------------------------------------
         */

        /**
         * handleTemplateSave
         * Submits the template form via AJAX and displays a floating feedback alert.
         *
         * @param {Event} e Submit event object
         * @return {void}
         */
        handleTemplateSave: function (e) {
            e.preventDefault();
            var self = this;
            var origText = this.$saveBtn.html();
            this.$saveBtn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Saving...');

            var formData = this.getFormData();
            formData.action = 'cosy_admin_save_email_template';

            $.ajax({
                url: self.getAjaxUrl(),
                type: 'POST',
                data: formData,
                success: function (res) {
                    self.$saveBtn.prop('disabled', false).html(origText);
                    if (res.success) {
                        self.$templateAlert.css({ background: '#dcfce7', color: '#166534', border: '1px solid #bbf7d0' })
                            .html('<i class="fa-solid fa-circle-check me-1"></i> ' + res.data).slideDown(200);
                        self.updateLivePreview(); // Refresh preview with saved data
                    } else {
                        self.$templateAlert.css({ background: '#fee2e2', color: '#991b1b', border: '1px solid #fecaca' })
                            .html('<i class="fa-solid fa-triangle-exclamation me-1"></i> ' + (res.data || 'Failed to save.')).slideDown(200);
                    }
                    setTimeout(function () {
                        self.$templateAlert.slideUp(200);
                    }, 3500);
                },
                error: function () {
                    self.$saveBtn.prop('disabled', false).html(origText);
                    alert('Error saving template settings.');
                }
            });
        },

        /**
         * handleTemplateReset
         * Resets current email template back to plugin default content via AJAX with user confirmation.
         *
         * @param {Event} e Click event object
         * @return {void}
         */
        handleTemplateReset: function (e) {
            e.preventDefault();
            if (!confirm('Are you sure you want to reset this email template to default values?')) {
                return;
            }

            var self = this;
            this.$resetBtn.prop('disabled', true);

            $.ajax({
                url: self.getAjaxUrl(),
                type: 'POST',
                data: {
                    action:       'cosy_admin_reset_email_template',
                    security:     $('#cosy_email_tpl_security').val(),
                    template_key: $('#cosy_template_key').val()
                },
                success: function (res) {
                    self.$resetBtn.prop('disabled', false);
                    if (res.success && res.data.template) {
                        var tpl = res.data.template;

                        // 1. Populate text input fields
                        $('#cosy_email_subject').val(tpl.subject);
                        $('#cosy_email_heading').val(tpl.heading);

                        // 2. Populate Intro Body editor
                        if (typeof tinyMCE !== 'undefined' && tinyMCE.get('cosy_email_body_text')) {
                            tinyMCE.get('cosy_email_body_text').setContent(tpl.body_text);
                        } else {
                            $('#cosy_email_body_text').val(tpl.body_text);
                        }

                        // 3. Populate Outro Body editor
                        if (typeof tinyMCE !== 'undefined' && tinyMCE.get('cosy_email_outro_text')) {
                            tinyMCE.get('cosy_email_outro_text').setContent(tpl.outro_text);
                        } else {
                            $('#cosy_email_outro_text').val(tpl.outro_text);
                        }

                        // 4. Show success alert
                        self.$templateAlert.css({ background: '#dcfce7', color: '#166534', border: '1px solid #bbf7d0' })
                            .html('<i class="fa-solid fa-circle-check me-1"></i> ' + res.data.message).slideDown(200);
                        setTimeout(function () {
                            self.$templateAlert.slideUp(200);
                        }, 3500);

                        // 5. Update preview with restored default content
                        self.updateLivePreview();
                    }
                },
                error: function () {
                    self.$resetBtn.prop('disabled', false);
                    alert('Error resetting template.');
                }
            });
        },

        /**
         * handleSendTestEmail
         * Dispatches a real test email with realistic mock data via AJAX.
         *
         * @param {Event} e Click event object
         * @return {void}
         */
        handleSendTestEmail: function (e) {
            e.preventDefault();
            var self = this;
            var testEmail = $('#cosy_test_email_address').val().trim();

            // Validate that recipient email is not empty
            if (!testEmail) {
                this.$testAlert.css({ background: '#fee2e2', color: '#991b1b', border: '1px solid #fecaca' })
                    .html('Please enter a recipient email address.').slideDown(200);
                setTimeout(function () {
                    self.$testAlert.slideUp(200);
                }, 3500);
                return;
            }

            var origHtml = this.$sendTestBtn.html();
            this.$sendTestBtn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Sending...');

            var formData = this.getFormData();
            formData.action = 'cosy_admin_send_test_email';
            formData.test_email = testEmail;

            $.ajax({
                url: self.getAjaxUrl(),
                type: 'POST',
                data: formData,
                success: function (res) {
                    self.$sendTestBtn.prop('disabled', false).html(origHtml);
                    if (res.success) {
                        self.$testAlert.css({ background: '#dcfce7', color: '#166534', border: '1px solid #bbf7d0' })
                            .html('<i class="fa-solid fa-circle-check me-1"></i> ' + res.data).slideDown(200);
                    } else {
                        self.$testAlert.css({ background: '#fee2e2', color: '#991b1b', border: '1px solid #fecaca' })
                            .html('<i class="fa-solid fa-triangle-exclamation me-1"></i> ' + (res.data || 'Failed to send test email.')).slideDown(200);
                    }
                    setTimeout(function () {
                        self.$testAlert.slideUp(200);
                    }, 5000);
                },
                error: function () {
                    self.$sendTestBtn.prop('disabled', false).html(origHtml);
                    self.$testAlert.css({ background: '#fee2e2', color: '#991b1b', border: '1px solid #fecaca' })
                        .html('Server error while sending test email.').slideDown(200);
                    setTimeout(function () {
                        self.$testAlert.slideUp(200);
                    }, 5000);
                }
            });
        }
    };

    // Initialize controller on DOM ready
    CosyEmailTemplatesAdmin.init();
});
