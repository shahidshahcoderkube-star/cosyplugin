<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('cosy_render_popup')) {
    /**
     * RENDERS STANDARDIZED BOOTSTRAP 5 MODAL POPUP
     *
     * USE CASE:
     * Used anywhere in frontend or backend to generate clean Bootstrap 5 modal HTML.
     *
     * HOW TO USE:
     * echo cosy_render_popup('myModal', 'Modal Title', '<p>Modal Body Content</p>');
     *
     * WHAT IT DOES INTERNALLY:
     * 1. Extracts modal width, z-index, and header/footer CSS options.
     * 2. Wraps title, body, and footer HTML in Bootstrap 5 modal containers.
     * 3. Returns complete output-buffered HTML string.
     *
     * @param string $id            HTML ID of the modal (e.g. 'addHolidayModal').
     * @param string $title_html    Title HTML or text.
     * @param string $body_html     Internal body HTML content.
     * @param array  $options       Optional styling configurations.
     * @return string               Complete HTML string for modal.
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
     * UNIFIED HTML EMAIL DISPATCHER WITH BRAND HEADERS
     *
     * USE CASE:
     * Dispatches styled HTML emails to customers, providers, or administrators.
     *
     * HOW TO USE:
     * cosy_send_html_email($to_email, $subject, $heading_title, $body_content_html);
     *
     * WHAT IT DOES INTERNALLY:
     * 1. Validates recipient address using is_email().
     * 2. Builds brand HTML signature block based on admin settings.
     * 3. Wraps body content in responsive gradient HTML container.
     * 4. Sets content-type HTML and From headers.
     * 5. Dispatches email via wp_mail() and logs delivery status.
     *
     * @param string $to             Target recipient email address.
     * @param string $subject        Email subject line.
     * @param string $heading        Header title displayed in top banner.
     * @param string $content_html   Core message body HTML.
     * @param bool   $show_signature Whether to append brand email signature.
     * @return bool                  True if accepted by mail server, false otherwise.
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
            $sig_logo = get_option('cosy_sig_logo_url', '');
            if (empty($sig_logo)) {
                $custom_logo_id = get_theme_mod('custom_logo');
                if ($custom_logo_id) {
                    $sig_logo = wp_get_attachment_image_url($custom_logo_id, 'full');
                } else {
                    $sig_logo = get_site_icon_url();
                }
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
                    <td style=\"width:110px; vertical-align:middle; padding-right:16px;\">
                        <img src=\"" . esc_url($sig_logo) . "\" alt=\"Logo\" width=\"100\" style=\"max-width:100px; height:auto; display:block; border:0;\">
                    </td>
                    <td style=\"width:2px; vertical-align:middle; padding:0 16px 0 0;\">
                        <div style=\"width:2px; height:90px; background:linear-gradient(180deg,#a44390,#6d2e67);\"></div>
                    </td>";
            }

            $contact_rows = '';
            if (!empty($sig_email))   $contact_rows .= "<p style=\"margin:0 0 4px 0; font-size:12px; color:#334155;\">&#9993; <a href=\"mailto:" . esc_attr($sig_email) . "\" style=\"color:#a44390; text-decoration:none;\">" . esc_html($sig_email) . "</a></p>";
            if (!empty($sig_website)) $contact_rows .= "<p style=\"margin:0 0 4px 0; font-size:12px; color:#334155;\">&#127760; <a href=\"" . esc_url($sig_website) . "\" style=\"color:#a44390; text-decoration:none;\">" . esc_html($sig_website) . "</a></p>";
            if (!empty($sig_address)) $contact_rows .= "<p style=\"margin:0 0 8px 0; font-size:12px; color:#334155;\">&#128205; " . esc_html($sig_address) . "</p>";

            $social_badges = '';
            if (!empty($sig_fb))  $social_badges .= "<a href=\"" . esc_url($sig_fb) . "\" target=\"_blank\" style=\"display:inline-block; margin-right:8px; text-decoration:none;\"><img src=\"https://wppremiumplugins.com/cosychats/wp-content/uploads/2026/08/facebook.png\" width=\"26\" height=\"26\" alt=\"Facebook\" style=\"display:inline-block; vertical-align:middle; width:26px; height:26px; border:0;\"></a>";
            if (!empty($sig_tw))  $social_badges .= "<a href=\"" . esc_url($sig_tw) . "\" target=\"_blank\" style=\"display:inline-block; margin-right:8px; text-decoration:none;\"><img src=\"https://wppremiumplugins.com/cosychats/wp-content/uploads/2026/08/X.png\" width=\"26\" height=\"26\" alt=\"X\" style=\"display:inline-block; vertical-align:middle; width:26px; height:26px; border:0;\"></a>";
            if (!empty($sig_ig))  $social_badges .= "<a href=\"" . esc_url($sig_ig) . "\" target=\"_blank\" style=\"display:inline-block; margin-right:8px; text-decoration:none;\"><img src=\"https://wppremiumplugins.com/cosychats/wp-content/uploads/2026/08/instagram.png\" width=\"26\" height=\"26\" alt=\"Instagram\" style=\"display:inline-block; vertical-align:middle; width:26px; height:26px; border:0;\"></a>";
            if (!empty($sig_tk))  $social_badges .= "<a href=\"" . esc_url($sig_tk) . "\" target=\"_blank\" style=\"display:inline-block; margin-right:8px; text-decoration:none;\"><img src=\"https://wppremiumplugins.com/cosychats/wp-content/uploads/2026/08/tiktok.png\" width=\"26\" height=\"26\" alt=\"TikTok\" style=\"display:inline-block; vertical-align:middle; width:26px; height:26px; border:0;\"></a>";
            if (!empty($sig_yt))  $social_badges .= "<a href=\"" . esc_url($sig_yt) . "\" target=\"_blank\" style=\"display:inline-block; margin-right:8px; text-decoration:none;\"><img src=\"https://wppremiumplugins.com/cosychats/wp-content/uploads/2026/08/youtube.png\" width=\"26\" height=\"26\" alt=\"YouTube\" style=\"display:inline-block; vertical-align:middle; width:26px; height:26px; border:0;\"></a>";
            if (!empty($sig_li))  $social_badges .= "<a href=\"" . esc_url($sig_li) . "\" target=\"_blank\" style=\"display:inline-block; margin-right:8px; text-decoration:none;\"><img src=\"https://wppremiumplugins.com/cosychats/wp-content/uploads/2026/08/Linkedin.png\" width=\"26\" height=\"26\" alt=\"LinkedIn\" style=\"display:inline-block; vertical-align:middle; width:26px; height:26px; border:0;\"></a>";

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
        // Configure Gmail SMTP settings
        $smtp_user = 'contact@cosychats.com';
        $smtp_pass = 'suln klpu wrwp bsvy';

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            "From: {$site_name} <{$smtp_user}>",
            "Reply-To: {$smtp_user}",
        ];

        // Configure PHPMailer to use Gmail SMTP
        $smtp_handler = function ($phpmailer) use ($smtp_user, $smtp_pass, $site_name) {
            $phpmailer->isSMTP();
            $phpmailer->Host       = 'smtp.gmail.com';
            $phpmailer->SMTPAuth   = true;
            $phpmailer->Port       = 587;
            $phpmailer->SMTPSecure = 'tls';
            $phpmailer->Username   = $smtp_user;
            $phpmailer->Password   = $smtp_pass;
            $phpmailer->From       = $smtp_user;
            $phpmailer->FromName   = $site_name;
            $phpmailer->Sender     = $smtp_user;
        };
        add_action('phpmailer_init', $smtp_handler, 999999);

        // Filter WP Mail SMTP internal options if WP Mail SMTP plugin has an expired Google OAuth connection
        $wp_smtp_filter = function ($value, $group, $key) use ($smtp_user, $smtp_pass, $site_name) {
            if ($group === 'mail' && $key === 'mailer') return 'smtp';
            if ($group === 'smtp' && $key === 'host') return 'smtp.gmail.com';
            if ($group === 'smtp' && $key === 'port') return 587;
            if ($group === 'smtp' && $key === 'encryption') return 'tls';
            if ($group === 'smtp' && $key === 'auth') return true;
            if ($group === 'smtp' && $key === 'user') return $smtp_user;
            if ($group === 'smtp' && $key === 'pass') return $smtp_pass;
            if ($group === 'mail' && $key === 'from_email') return $smtp_user;
            if ($group === 'mail' && $key === 'from_name') return $site_name;
            return $value;
        };
        add_filter('wp_mail_smtp_options_get', $wp_smtp_filter, 999999, 3);

        // Set content type to HTML
        $html_email_filter = function () {
            return 'text/html; charset=UTF-8';
        };
        add_filter('wp_mail_content_type', $html_email_filter);

        $result = wp_mail($to, $subject, $message, $headers);

        remove_action('phpmailer_init', $smtp_handler, 999999);
        remove_filter('wp_mail_smtp_options_get', $wp_smtp_filter, 999999);
        remove_filter('wp_mail_content_type', $html_email_filter);

        return $result;
    }
}

if (!function_exists('cosy_clean_slots_timeline')) {
    /**
     * Formats slots timeline string into clean "Day Time1, Time2" breakdown.
     * e.g., "06 Oct 2026 (Tuesday): 08:00 AM, 08:10 AM" -> "Tue 08:00 AM, 08:10 AM"
     *
     * @param string $slots_timeline
     * @param string $start_date     Optional start date fallback (e.g. "01-10-2026")
     * @param string $week_days      Optional week days fallback (e.g. "Thursday")
     * @return string
     */
    function cosy_clean_slots_timeline($slots_timeline, $start_date = '', $week_days = '')
    {
        if (empty($slots_timeline)) {
            return '';
        }

        // Double-clean Protection Guard: If input is already formatted HTML table or div, return as-is
        if (is_string($slots_timeline) && (strpos($slots_timeline, '<table') !== false || strpos($slots_timeline, '<div') !== false)) {
            return $slots_timeline;
        }

        $parts = preg_split('/[|\n]+/', $slots_timeline);
        $day_slots_map = [];
        $has_any_day_label = false;

        foreach ($parts as $part) {
            $part = trim($part);
            if (empty($part)) {
                continue;
            }

            $day_label = '';
            $times_str = $part;

            // Case 1: Part starts with Day Name (e.g. "Sun 11:30 AM" or "Wed 10:10 AM")
            if (preg_match('/^\s*(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday|Mon|Tue|Wed|Thu|Fri|Sat|Sun)[\s:]*(.+)$/i', $part, $m)) {
                $day_name = ucfirst(strtolower($m[1]));
                $day_label = substr($day_name, 0, 3);
                $times_str = trim($m[2]);
                $has_any_day_label = true;
            }
            // Case 2: Date Header before colon (e.g. "06 Oct 2026: 09:00 AM" or "01-10-2026: 09:30 AM")
            elseif (preg_match('/^(.*?):\s*(.+)$/', $part, $m)) {
                $header = trim($m[1]);
                $rest   = trim($m[2]);

                if (preg_match('/\b(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday|Mon|Tue|Wed|Thu|Fri|Sat|Sun)\b/i', $header, $dm)) {
                    $day_label = substr(ucfirst(strtolower($dm[1])), 0, 3);
                    $times_str = $rest;
                    $has_any_day_label = true;
                } elseif (preg_match('/[A-Za-z\s\-]/', $header) && !preg_match('/^\d{1,2}$/', $header)) {
                    $dt = strtotime($header);
                    if ($dt) {
                        $day_label = date('D', $dt);
                        $times_str = $rest;
                        $has_any_day_label = true;
                    }
                }
            }

            $times_str = strip_tags($times_str);
            $key = !empty($day_label) ? $day_label : '__NO_DAY__';

            if (!isset($day_slots_map[$key])) {
                $day_slots_map[$key] = [];
            }

            $raw_times = array_filter(array_map('trim', explode(',', $times_str)));
            foreach ($raw_times as $t) {
                // Normalize "10 AM" -> "10:00 AM" or "9:20 AM" -> "09:20 AM"
                if (preg_match('/^(\d{1,2})\s*(AM|PM)$/i', $t, $tm)) {
                    $t = sprintf('%02d:00 %s', $tm[1], strtoupper($tm[2]));
                } elseif (preg_match('/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i', $t, $tm)) {
                    $t = sprintf('%02d:%02d %s', $tm[1], $tm[2], strtoupper($tm[3]));
                }
                $timestamp = strtotime("2026-01-01 " . $t);
                $day_slots_map[$key][] = [
                    'formatted' => $t,
                    'ts'        => $timestamp ?: 0
                ];
            }
        }

        // Fallback if NO day label was detected
        if (!$has_any_day_label && isset($day_slots_map['__NO_DAY__'])) {
            $fallback_day = '';
            if (!empty($week_days)) {
                $first_day = trim(explode(',', $week_days)[0]);
                if (preg_match('/\b(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday|Mon|Tue|Wed|Thu|Fri|Sat|Sun)\b/i', $first_day, $dm)) {
                    $fallback_day = substr(ucfirst(strtolower($dm[1])), 0, 3);
                }
            }
            if (empty($fallback_day) && !empty($start_date)) {
                $dt = strtotime($start_date);
                if (!$dt && preg_match('/^\d{2}-\d{2}-\d{4}$/', $start_date)) {
                    $p = explode('-', $start_date);
                    $dt = strtotime("{$p[2]}-{$p[1]}-{$p[0]}");
                }
                if ($dt) {
                    $fallback_day = date('D', $dt);
                }
            }
            if (!empty($fallback_day)) {
                $day_slots_map[$fallback_day] = $day_slots_map['__NO_DAY__'];
                unset($day_slots_map['__NO_DAY__']);
            }
        }

        $rows_html = [];
        foreach ($day_slots_map as $d => $slot_list) {
            usort($slot_list, function($a, $b) {
                return $a['ts'] <=> $b['ts'];
            });

            $clean_times = [];
            foreach ($slot_list as $sl) {
                if (!in_array($sl['formatted'], $clean_times, true)) {
                    $clean_times[] = $sl['formatted'];
                }
            }

            $times_output = implode(', ', $clean_times);

            if ($d !== '__NO_DAY__') {
                $rows_html[] = '<tr><td style="padding: 2px 0; color: #334155; line-height: 1.5; font-size: 13px;"><strong>' . esc_html($d) . '</strong> ' . esc_html($times_output) . '</td></tr>';
            } else {
                $rows_html[] = '<tr><td style="padding: 2px 0; color: #334155; line-height: 1.5; font-size: 13px;">' . esc_html($times_output) . '</td></tr>';
            }
        }

        if (empty($rows_html)) {
            return '';
        }

        return '<table style="width:100%; border-collapse:collapse; margin:0; padding:0; border:none; background:transparent;">' . implode('', $rows_html) . '</table>';
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
