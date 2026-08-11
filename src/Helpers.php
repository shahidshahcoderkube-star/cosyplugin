<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('cosy_render_popup')) {
    /**
     * Renders a standardized Bootstrap 5 modal/popup.
     * Keeps HTML DRY and easy to maintain.
     *
     * @param string $id            The HTML ID of the modal (e.g., 'addHolidayModal').
     * @param string $title_html    The title (can include HTML like icons).
     * @param string $body_html     The internal content of the modal body.
     * @param array  $options       Optional configs (max_width, z_index).
     * @return string               The complete HTML string for the modal.
     */
    function cosy_render_popup($id, $title_html, $body_html, $options = [])
    {
        $max_width    = isset($options['max_width']) ? $options['max_width'] : '480px';
        $z_index      = isset($options['z_index']) ? $options['z_index'] : '99999';
        $dialog_class = isset($options['dialog_class']) ? $options['dialog_class'] : '';
        $header_class = isset($options['header_class']) ? $options['header_class'] : '';
        $footer_html  = isset($options['footer_html']) ? $options['footer_html'] : '';

        ob_start();
?>
        <div class="modal fade" id="<?php echo esc_attr($id); ?>" tabindex="-1" aria-hidden="true" style="z-index: <?php echo esc_attr($z_index); ?>;">
            <div class="modal-dialog modal-dialog-centered <?php echo esc_attr($dialog_class); ?>" style="max-width: <?php echo esc_attr($max_width); ?>;">
                <div class="modal-content cosy-modal-content border-0 shadow-lg">

                    <!-- Modal Header -->
                    <div class="modal-header cosy-modal-header <?php echo esc_attr($header_class); ?>">
                        <h5 class="modal-title fw-bold text-white mb-0" id="<?php echo esc_attr($id); ?>Label">
                            <?php echo $title_html; ?>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <!-- Modal Body -->
                    <div class="cosy-modal-body p-4">
                        <?php echo $body_html; ?>
                    </div>

                    <?php if (!empty($footer_html)) : ?>
                        <!-- Modal Footer -->
                        <div class="modal-footer border-0 p-4 pt-0 justify-content-end gap-2" id="<?php echo esc_attr($id); ?>Footer">
                            <?php echo $footer_html; ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
<?php
        return ob_get_clean();
    }
}

if (!function_exists('cosy_send_html_email')) {
    /**
     * UNIFIED HTML EMAIL DISPATCHER WITH BRAND HEADERS & SPAM PROTECTION
     *
     * USE CASE:
     * Dispatches HTML emails using the official CosyChats responsive email template layout.
     * Contains pink gradient header banner, styled body container, footer copyright, and brand signature.
     * 
     * KEY FEATURES & SAFETY CHECKS:
     * 1. Recipient Address Validation: Uses is_email() to block invalid or empty recipient addresses.
     * 2. Spam Protection Headers: Sets explicit "From: CosyChats <admin_email>" and "Reply-To" headers
     *    to prevent emails from being flagged as SPAM by Gmail, Outlook, or Yahoo.
     * 3. Delivery Error Logging: Captures wp_mail() return status and logs delivery failures to LogManager.
     *
     * @param string $to             Target recipient email address.
     * @param string $subject        Email subject line.
     * @param string $heading        Header title text displayed inside the top gradient banner.
     * @param string $content_html   The core message body HTML (paragraphs, lists, buttons, tables).
     * @param bool   $show_signature Whether to append the admin brand email signature at the bottom (default: true).
     * @return bool                  True if the email was successfully accepted by mail server, false otherwise.
     */
    function cosy_send_html_email($to, $subject, $heading, $content_html, $show_signature = true)
    {
        if (empty($to) || !is_email($to)) {
            \Cosy\Appointments\Common\LogManager::log(
                'email',
                'invalid_recipient',
                sprintf(__('Attempted to send email to invalid address: %s', 'cosy-appointments'), var_export($to, true))
            );
            return false;
        }

        $year = date('Y');

        // --- Build Email Signature HTML ---
        $sig_html = '';
        if (get_option('cosy_sig_enabled', 1)) {
            $sig_logo    = get_option('cosy_sig_logo_url', '');
            if (empty($sig_logo) || strpos($sig_logo, 'localhost') !== false) {
                $sig_logo = 'https://cosychats.com/wp-content/uploads/2024/10/logo.png';
            }
            $sig_name    = get_option('cosy_sig_name', 'The CosyChats Team');
            $sig_title   = get_option('cosy_sig_title', 'Customer Support');
            $sig_phone   = get_option('cosy_sig_phone', '');
            $sig_email   = get_option('cosy_sig_email', '');
            $sig_website = get_option('cosy_sig_website', '');
            $sig_address = get_option('cosy_sig_address', '');
            $sig_li      = get_option('cosy_sig_linkedin', '');
            $sig_fb      = get_option('cosy_sig_facebook', '');
            $sig_ig      = get_option('cosy_sig_instagram', '');
            $sig_tw      = get_option('cosy_sig_twitter', '');
            $sig_tk      = get_option('cosy_sig_tiktok', '');
            $sig_yt      = get_option('cosy_sig_youtube', '');

            $logo_col = '';
            if (!empty($sig_logo)) {
                $logo_col = "
                    <td style='width:110px; vertical-align:middle; padding-right:16px;'>
                        <img src='" . esc_url($sig_logo) . "' alt='Logo' style='max-width:100px; height:auto; display:block;'>
                    </td>
                    <td style='width:2px; vertical-align:middle; padding:0 16px 0 0;'>
                        <div style='width:2px; height:90px; background:linear-gradient(180deg,#a44390,#6d2e67);'></div>
                    </td>";
            }

            $contact_rows = '';
            if (!empty($sig_phone))   $contact_rows .= "<p style='margin:0 0 4px 0; font-size:12px; color:#334155;'>&#128222; " . esc_html($sig_phone) . "</p>";
            if (!empty($sig_email))   $contact_rows .= "<p style='margin:0 0 4px 0; font-size:12px; color:#334155;'>&#9993; <a href='mailto:" . esc_attr($sig_email) . "' style='color:#a44390; text-decoration:none;'>" . esc_html($sig_email) . "</a></p>";
            if (!empty($sig_website)) $contact_rows .= "<p style='margin:0 0 4px 0; font-size:12px; color:#334155;'>&#127760; <a href='" . esc_url($sig_website) . "' style='color:#a44390; text-decoration:none;'>" . esc_html($sig_website) . "</a></p>";
            if (!empty($sig_address)) $contact_rows .= "<p style='margin:0 0 8px 0; font-size:12px; color:#334155;'>&#128205; " . esc_html($sig_address) . "</p>";

            $social_badges = '';
            if (!empty($sig_fb))  $social_badges .= "<a href='" . esc_url($sig_fb) . "' target='_blank' style='display:inline-block; margin-right:8px; text-decoration:none;'><img src='https://img.icons8.com/color/48/facebook-circled--v1.png' width='28' height='28' alt='Facebook' style='display:inline-block; vertical-align:middle; width:28px; height:28px; border:0;'></a>";
            if (!empty($sig_tw))  $social_badges .= "<a href='" . esc_url($sig_tw) . "' target='_blank' style='display:inline-block; margin-right:8px; text-decoration:none;'><img src='https://img.icons8.com/color/48/twitter-circled--v1.png' width='28' height='28' alt='Twitter' style='display:inline-block; vertical-align:middle; width:28px; height:28px; border:0;'></a>";
            if (!empty($sig_ig))  $social_badges .= "<a href='" . esc_url($sig_ig) . "' target='_blank' style='display:inline-block; margin-right:8px; text-decoration:none;'><img src='https://img.icons8.com/color/48/instagram-new.png' width='28' height='28' alt='Instagram' style='display:inline-block; vertical-align:middle; width:28px; height:28px; border:0;'></a>";
            if (!empty($sig_tk))  $social_badges .= "<a href='" . esc_url($sig_tk) . "' target='_blank' style='display:inline-block; margin-right:8px; text-decoration:none;'><img src='https://img.icons8.com/color/48/tiktok.png' width='28' height='28' alt='TikTok' style='display:inline-block; vertical-align:middle; width:28px; height:28px; border:0;'></a>";
            if (!empty($sig_yt))  $social_badges .= "<a href='" . esc_url($sig_yt) . "' target='_blank' style='display:inline-block; margin-right:8px; text-decoration:none;'><img src='https://img.icons8.com/color/48/youtube-play.png' width='28' height='28' alt='YouTube' style='display:inline-block; vertical-align:middle; width:28px; height:28px; border:0;'></a>";
            if (!empty($sig_li))  $social_badges .= "<a href='" . esc_url($sig_li) . "' target='_blank' style='display:inline-block; margin-right:8px; text-decoration:none;'><img src='https://img.icons8.com/color/48/linkedin-circled--v1.png' width='28' height='28' alt='LinkedIn' style='display:inline-block; vertical-align:middle; width:28px; height:28px; border:0;'></a>";

            $sig_html = "
            <div style='margin: 24px 0 0 0; padding: 18px 20px; background: #fdf6fc; border: 1px solid #f1e4ef; border-radius: 10px;'>
                <table role='presentation' cellpadding='0' cellspacing='0' border='0' width='100%'>
                    <tr>
                        {$logo_col}
                        <td style='vertical-align: middle;'>
                            " . (!empty($sig_name) ? "<p style='margin:0 0 2px 0; font-size:15px; font-weight:700; color:#a44390;'>" . esc_html($sig_name) . "</p>" : '') . "
                            " . (!empty($sig_title) ? "<p style='margin:0 0 8px 0; font-size:11px; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;'>" . esc_html($sig_title) . "</p>" : '') . "
                            {$contact_rows}
                            " . (!empty($social_badges) ? "<p style='margin:4px 0 0 0;'>{$social_badges}</p>" : '') . "
                        </td>
                    </tr>
                </table>
            </div>";
        }

        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>" . esc_html($subject) . "</title>
            <style>
                body {
                    margin: 0;
                    padding: 0;
                    background-color: #faf6f9;
                }
            </style>
        </head>
        <body style='margin: 0; padding: 0; background-color: #faf6f9; font-family: \"Outfit\", \"Segoe UI\", Tahoma, Geneva, Verdana, sans-serif;'>
            <div style='background-color: #faf6f9; padding: 40px 15px; color: #1e293b;'>
                <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; border: 1px solid #f1e4ef; box-shadow: 0 10px 25px rgba(109, 46, 103, 0.05); overflow: hidden;'>
                    <!-- Header -->
                    <div style='background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%); padding: 30px 20px; text-align: center; color: #ffffff;'>
                        <h1 style='margin: 0; font-size: 24px; font-weight: 700; color: #ffffff;'>" . esc_html($heading) . "</h1>
                    </div>
                    
                    <!-- Body -->
                    <div style='padding: 35px 25px; font-size: 15px; line-height: 1.6;'>
                        " . $content_html . "
                        " . ($show_signature ? $sig_html : '') . "
                    </div>
                    
                    <!-- Footer -->
                    " . (function() use ($year) {
                        $site_name = get_bloginfo('name');
                        if (empty($site_name) || strtolower($site_name) === 'cosyplugin' || strtolower($site_name) === 'wordpress') {
                            $site_name = 'CosyChats';
                        }
                        return "<div style='background-color: #fdf2fb; padding: 20px; text-align: center; font-size: 12px; color: #8a7a88; border-top: 1px solid #f1e4ef;'>
                            &copy; {$year} " . esc_html($site_name) . ". All rights reserved.
                        </div>";
                    })() . "

                </div>
            </div>
        </body>
        </html>";

        // Build proper headers for spam prevention
        $site_name   = get_bloginfo('name');
        if (empty($site_name) || strtolower($site_name) === 'cosyplugin' || strtolower($site_name) === 'wordpress') {
            $site_name = 'CosyChats';
        }
        $admin_email = get_option('admin_email');

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            "From: {$site_name} <{$admin_email}>",
            "Reply-To: {$admin_email}",
        ];

        // Set content type to HTML
        $html_email_filter = function () {
            return 'text/html; charset=UTF-8';
        };
        add_filter('wp_mail_content_type', $html_email_filter);

        $result = wp_mail($to, $subject, $message, $headers);

        // Restore default text/plain content type filter to avoid affecting other emails
        remove_filter('wp_mail_content_type', $html_email_filter);

        if (!$result) {
            \Cosy\Appointments\Common\LogManager::log(
                'email',
                'send_failed',
                sprintf(__('Failed to dispatch HTML email to %s with subject "%s".', 'cosy-appointments'), $to, $subject)
            );
        }

        return $result;
    }
}

if (!function_exists('cosy_get_currency_symbol')) {
    /**
     * Gets the currency symbol based on the saved 'cosy_payment_currency' option.
     * Defaults to '£' (GBP) if not set.
     *
     * @return string Currency symbol.
     */
    function cosy_get_currency_symbol()
    {
        $currency = get_option('cosy_payment_currency', 'GBP');

        switch (strtoupper($currency)) {
            case 'USD':
                return '$';
            case 'EUR':
                return '€';
            case 'INR':
                return '₹';
            case 'AUD':
                return 'A$';
            case 'CAD':
                return 'C$';
            case 'GBP':
            default:
                return '£';
        }
    }
}

if (!function_exists('cosy_get_currency_code')) {
    /**
     * Gets the active currency code.
     *
     * @return string Currency code (e.g., 'GBP', 'USD').
     */
    function cosy_get_currency_code()
    {
        $code = strtoupper((string) get_option('cosy_payment_currency', 'GBP'));
        return !empty($code) ? $code : 'GBP';
    }
}

if (!function_exists('cosy_get_page_id')) {
    /**
     * Dynamic Page ID Finder:
     * Finds the page ID by checking: 1) Saved options cache, 2) Unique page shortcodes, 3) Page slug.
     * This keeps redirects and links working even if the administrator changes the page slug.
     *
     * @param string $key Page identifier (e.g., 'login', 'cosy-checkout').
     * @return int Page ID or 0 if not found.
     */
    function cosy_get_page_id($key)
    {
        // 1. Try to get from option
        $opt_key = 'cosy_page_id_' . str_replace('-', '_', $key);
        $page_id = get_option($opt_key);
        if ($page_id) {
            return intval($page_id);
        }

        // 2. If option not set, map key to shortcode for lookup
        $shortcode_map = [
            'appointments'          => '[cosy_appointments]',
            'orders'                => '[cosy_orders]',
            'user-registration'     => '[cosy_customer_registration]',
            'provider-registration' => '[cosy_provider_registration]',
            'login'                 => '[cosy_login_form]',
            'customer-profile'      => '[customer_profile]',
            'customer-order'        => '[cosy_customer_order]',
            'provider-dashboard'    => '[cosy_provider_dashboard]',
            'provider-verify'       => '[cosy_verify_provider]',
            'service-provider'      => '[cosy_service_provider_list]',
            'provider-profile'      => '[cosy_profile_dashboard]',
            'cosy-checkout'         => '[cosy_checkout]',
            'cosy-leave-review'     => '[cosy_leave_review]',
        ];

        // 3. Try lookup by shortcode
        if (isset($shortcode_map[$key])) {
            global $wpdb;
            $id = $wpdb->get_var($wpdb->prepare(
                "SELECT ID FROM $wpdb->posts WHERE post_content LIKE %s AND post_status = 'publish' AND post_type = 'page' LIMIT 1",
                '%' . $wpdb->esc_like($shortcode_map[$key]) . '%'
            ));
            if ($id) {
                update_option($opt_key, $id);
                return intval($id);
            }
        }

        // 4. Try lookup by slug path
        $page = get_page_by_path($key);
        if ($page) {
            update_option($opt_key, $page->ID);
            return intval($page->ID);
        }

        return 0;
    }
}

if (!function_exists('cosy_get_page_url')) {
    /**
     * Gets the dynamic page URL by key.
     *
     * @param string $key Page key name.
     * @return string Page URL.
     */
    function cosy_get_page_url($key)
    {
        $page_id = cosy_get_page_id($key);
        if ($page_id) {
            return get_permalink($page_id);
        }
        return site_url('/' . $key);
    }
}

if (!function_exists('cosy_notify_admin_provider_setup_ready')) {
    /**
     * Sends an email alert to site administrator when a new Provider setup is complete or updated.
     *
     * @param int $user_id Provider User ID.
     * @param bool $force  Whether to bypass already-notified check.
     * @return bool
     */
    function cosy_notify_admin_provider_setup_ready($user_id, $force = false)
    {
        if (empty($user_id)) {
            return false;
        }

        $user = get_userdata($user_id);
        if (!$user) {
            return false;
        }

        // Only send if user has provider role or role_type
        $user_roles = (array) $user->roles;
        $role_type  = get_user_meta($user_id, 'role_type', true);
        if (!in_array('provider', $user_roles, true) && $role_type !== 'provider') {
            return false;
        }

        // --- VERIFY THAT PROVIDER PROFILE IS 100% COMPLETE BEFORE NOTIFYING ADMIN ---
        // 1. Profile Info Check
        $has_profile_info = !empty(get_user_meta($user_id, 'first_name', true)) &&
            !empty(get_user_meta($user_id, 'prov_phone', true)) &&
            !empty(get_user_meta($user_id, 'dob', true)) &&
            !empty(get_user_meta($user_id, 'gender', true)) &&
            !empty(get_user_meta($user_id, 'age_group', true));

        // 2. Services / Experiences Check
        global $wpdb;
        $services_table = $wpdb->prefix . 'provider_services';
        $has_services = (bool) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $services_table WHERE provider_id = %d AND checkbox_status = 'yes'",
                $user_id
            )
        );

        // 3. Availability Working Hours Check
        $has_availability = false;
        $days_of_week = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        foreach ($days_of_week as $day) {
            $day_data = get_user_meta($user_id, "cosy_availability_{$day}", true);
            if (!empty($day_data) && !empty($day_data['start_time']) && !empty($day_data['end_time'])) {
                $has_availability = true;
                break;
            }
        }

        // If any requirement is incomplete, DO NOT send email to admin yet!
        if (!$has_profile_info || !$has_services || !$has_availability) {
            return false;
        }

        $admin_email = get_option('admin_email');
        if (empty($admin_email) || !function_exists('cosy_send_html_email')) {
            return false;
        }

        // Check if already notified unless forced
        if (!$force) {
            $already_notified = get_user_meta($user_id, 'cosy_admin_profile_ready_notified', true);
            if (!empty($already_notified)) {
                return false;
            }
        }

        $first_name = get_user_meta($user_id, 'first_name', true) ?: $user->first_name;
        $last_name  = get_user_meta($user_id, 'last_name', true) ?: $user->last_name;
        $provider_name = trim($first_name . ' ' . $last_name);
        if (empty($provider_name)) {
            $provider_name = $user->display_name;
        }

        $prov_status = get_user_meta($user_id, 'cosy_provider_status', true) ?: 'deactive (pending review)';

        $tpl = \Cosy\Appointments\Common\EmailTemplates::get_admin_provider_setup_template(
            $provider_name,
            $user->user_login,
            $user->user_email,
            $prov_status
        );

        $sent = cosy_send_html_email($admin_email, $tpl['subject'], $tpl['heading'], $tpl['content']);
        if ($sent) {
            update_user_meta($user_id, 'cosy_admin_profile_ready_notified', current_time('mysql'));
        }
        return $sent;
    }
}
