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
     * Sends a unified HTML email using a premium design layout.
     *
     * @param string $to            Recipient email address.
     * @param string $subject       Email subject.
     * @param string $heading       The header title shown in the gradient banner.
     * @param string $content_html  The core message body (can contain HTML, paragraphs, lists, tables).
     * @return bool                 Whether the email was successfully sent.
     */
    function cosy_send_html_email($to, $subject, $heading, $content_html)
    {
        $year = date('Y');

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
                    </div>
                    
                    <!-- Footer -->
                    <div style='background-color: #fdf2fb; padding: 20px; text-align: center; font-size: 12px; color: #8a7a88; border-top: 1px solid #f1e4ef;'>
                        &copy; {$year} " . esc_html(get_bloginfo('name')) . ". All rights reserved.
                    </div>

                </div>
            </div>
        </body>
        </html>";

        // Set content type to HTML
        $html_email_filter = function () {
            return 'text/html; charset=UTF-8';
        };
        add_filter('wp_mail_content_type', $html_email_filter);

        $result = wp_mail($to, $subject, $message);

        // Restore default text/plain content type filter to avoid affecting other emails
        remove_filter('wp_mail_content_type', $html_email_filter);

        return $result;
    }
}

if (!function_exists('cosy_get_currency_symbol')) {
    /**
     * Gets the currency symbol based on the saved 'cosy_stripe_currency' option.
     * Defaults to '£' (GBP) if not set.
     *
     * @return string Currency symbol.
     */
    function cosy_get_currency_symbol()
    {
        $currency = get_option('cosy_stripe_currency', 'GBP');

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
        return strtoupper(get_option('cosy_stripe_currency', 'GBP'));
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
