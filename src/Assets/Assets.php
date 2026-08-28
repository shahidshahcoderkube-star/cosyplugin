<?php

namespace Cosy\Appointments\Assets;

use Cosy\Appointments\Loader;

/**
 * Assets Class
 * 
 * This class is responsible for loading all CSS and JavaScript files needed for the plugin.
 * It handles both the WordPress Admin area and the public Frontend.
 */
class Assets
{
    /**
     * REGISTERS ASSET ENQUEUE HOOKS
     * 
     * USE CASE:
     * Called during plugin initialization sequence to hook CSS and JS asset loading.
     * 
     * HOW TO USE:
     * (new Assets())->register($loader);
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Attaches admin_assets callback to 'admin_enqueue_scripts' hook.
     * 2. Attaches frontend_assets callback to 'wp_enqueue_scripts' hook.
     * 3. Attaches add_defer_attribute filter to 'script_loader_tag' hook.
     * 
     * @param Loader $loader Plugin loader instance.
     */
    public function register(Loader $loader): void
    {
        $loader->add_action('admin_enqueue_scripts', $this, 'admin_assets');
        $loader->add_action('wp_enqueue_scripts', $this, 'frontend_assets');
        $loader->add_filter('script_loader_tag', $this, 'add_defer_attribute', 10, 3);
    }

    /**
     * admin_assets
     * 
     * Loads CSS and JS for the WordPress Admin pages.
     * It only loads these files on specific plugin pages to keep the site fast.
     */
    public function admin_assets($hook): void
    {
        // Allowed hooks (security) 
        $allowed_hooks = [
            'toplevel_page_cosy-booking-dashboard',
            'cc-booking_page_cosy-orders',
            'cc-booking_page_cosy-settings',
            'cc-booking_page_cosy-media-approve',
            'cc-booking_page_cosy-documentation',
            'cc-booking_page_cosy-users',
            'cc-booking_page_cosy-reviews',
            'cc-booking_page_cosy-logs',
            'users.php', // Required for the verification dropdown
        ];


        global $post_type;
        // Check if the current hook is allowed
        if (!in_array($hook, $allowed_hooks, true)) {
            if ($hook !== 'edit.php' || $post_type !== 'cosy_service') {
                return;
            }
        }

        // Google Fonts
        wp_enqueue_style(
            'cosy-outfit-jakarta-font',
            'https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap',
            [],
            null
        );

        // Bootstrap CSS (Do not load on native WP users.php to preserve WordPress default list table styles)
        if ($hook !== 'users.php') {
            wp_enqueue_style(
                'cosy-bootstrap',
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
                [],
                '5.3.2'
            );
        }

        // Bootstrap JS (with Popper)
        wp_enqueue_script(
            'cosy-bootstrap',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js',
            ['jquery'],
            '5.3.2',
            true
        );

        wp_enqueue_style(
            'cosy-font-awesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css'
        );

        wp_enqueue_style(
            'sweetalert2',
            'https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css'
        );

        wp_enqueue_script(
            'sweetalert2',
            'https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js',
            [],
            '11.10.5',
            true
        );

        wp_enqueue_style(
            'cosy-admin-style',
            COSY_APPT_URL . 'src/Admin/assets/admin.css',
            [],
            COSY_APPT_VER . '-' . time()
        );

        wp_enqueue_script(
            'cosy-admin-script',
            COSY_APPT_URL . 'src/Admin/assets/admin.js',
            ['jquery'],
            COSY_APPT_VER . '-' . time(),
            true
        );

        wp_localize_script('cosy-admin-script', 'cosyAdmin', [
            'currencySymbol' => cosy_get_currency_symbol(),
            'currencyCode'   => cosy_get_currency_code(),
            'nonce'          => wp_create_nonce('cosy_admin_nonce'),
        ]);

        // Enqueue dedicated Dashboard Admin CSS & JS ONLY on the main booking dashboard page
        if ($hook === 'toplevel_page_cosy-booking-dashboard') {
            wp_enqueue_style(
                'cosy-dashboard-admin-style',
                COSY_APPT_URL . 'src/Admin/assets/dashboard-admin.css',
                [],
                COSY_APPT_VER
            );

            wp_enqueue_script(
                'cosy-chartjs',
                'https://cdn.jsdelivr.net/npm/chart.js',
                [],
                '4.4.1',
                true
            );

            wp_enqueue_script(
                'cosy-dashboard-admin-script',
                COSY_APPT_URL . 'src/Admin/assets/dashboard-admin.js',
                ['cosy-chartjs'],
                COSY_APPT_VER,
                true
            );
        }
    }

    /**
     * frontend_assets
     * 
     * Enqueues all CSS stylesheets, web fonts, icons, and JavaScript files
     * required for the public-facing pages and the provider dashboard tab systems.
     * 
     * Features:
     * - Loads Bootstrap & FontAwesome globally for modern layout structuring.
     * - Registers Bootstrap Icons (v1.11.3) globally so they can be seamlessly
     *   rendered within components (e.g. non-working days tab) without duplications.
     * - Enqueues SweetAlert2 and jQuery Validate libraries for interactive validation.
     * - Passes REST APIs & AJAX nonces to the client using wp_localize_script mapping.
     */
    public function frontend_assets(): void
    {
        // 1. Primary Plugin Stylesheet (Contains theme design system, layout, and Bento utilities)
        // Loaded globally to ensure the header menu dropdown and register popup styling remain intact on all pages.
        wp_enqueue_style(
            'cosy-style',
            COSY_APPT_URL . 'src/Assets/css/style.css',
            [],
            COSY_APPT_VER
        );

        // 3. Typography Web Fonts (Enforces clean brand-consistent fonts)
        // Load Poppins globally; combine with Outfit and Plus Jakarta Sans only if plugin pages are active
        $font_url = 'https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400';
        if ($this->should_load_assets()) {
            $font_url .= '&family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800';
        }
        $font_url .= '&display=swap';

        wp_enqueue_style(
            'cosy-poppins-font',
            $font_url,
            [],
            null
        );

        // 16. Cosy API Javascript Mapping Script (Defines core server endpoints for validation.js requests)
        wp_register_script(
            'cosy-api',
            COSY_APPT_URL . 'src/Assets/js/api.js',
            [],
            '1.0',
            true
        );

        // 18. Localization data map for cosy-api script
        wp_localize_script('cosy-api', 'cosy_ajax', [
            'ajax_url'              => admin_url('admin-ajax.php'),
            'nonce'                 => wp_create_nonce('wp_rest'),
            'root'                  => esc_url_raw(rest_url()),
            'max_video_upload_size' => intval(get_option('cosy_max_video_upload_size', 3))
        ]);

        // Enqueue the registered cosy-api script dependency
        wp_enqueue_script('cosy-api');

        // 19. Core Frontend JS logic script
        wp_register_script(
            'cosy-script',
            COSY_APPT_URL . 'src/Assets/js/frontend.js',
            ['jquery', 'cosy-api'],
            COSY_APPT_VER,
            true
        );

        // Localization: Pass REST Base Endpoint URL, WP Nonces, and Currency to cosy-script
        wp_localize_script('cosy-script', 'cosyAppointments', [
            'restUrl'        => esc_url_raw(rest_url('cosy/v1/')),
            'nonce'          => wp_create_nonce('wp_rest'),
            'currencySymbol' => cosy_get_currency_symbol(),
            'currencyCode'   => cosy_get_currency_code(),
        ]);

        wp_enqueue_script('cosy-script');

        // Conditionally return early to prevent loading heavy frameworks and CDNs on non-plugin pages
        if (!$this->should_load_assets()) {
            return;
        }

        // 2. Service Provider Profile Stylesheet (For profile layouts, calendar elements, and bios)
        wp_enqueue_style(
            'service-provider-style',
            COSY_APPT_URL . 'src/Assets/css/service-provide.css',
            [],
            COSY_APPT_VER
        );



        // 4. Bootstrap 5 Framework CSS (Responsive grid and styling structure)
        wp_enqueue_style(
            'bootstrap',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
        );

        // 5. FontAwesome Icons v6 (Provides vector icons globally for buttons and navigation items)
        wp_enqueue_style(
            'font-awesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css',
        );

        // 6. Bootstrap Icons v1.11.3 (Enqueued globally here so individual dashboard tabs like non-working days can render them directly)
        wp_enqueue_style(
            'bootstrap-icons',
            'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css',
        );

        // 8. SweetAlert2 CSS Styles (For clean popups, action modals, and dynamic alerts)
        wp_enqueue_style(
            'sweetalert2',
            'https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css',
            [],
            '11.10.5'
        );

        // 9. SweetAlert2 Core Script (Used for all interactive user confirmation dialogs)
        wp_enqueue_script(
            'sweetalert2',
            'https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js',
            [],
            '11.10.5',
            true
        );

        // 11. jQuery UI Datepicker (Native WordPress library for interactive calendar date selections)
        wp_enqueue_script('jquery-ui-datepicker');

        // 12. jQuery Validate (Used to perform robust client-side validation on auth and info forms)
        wp_enqueue_script(
            'jquery-validate',
            'https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js',
            ['jquery'],
            null,
            true
        );

        // 13. jQuery Validate Additional Methods (Provides additional validation rules like phone numbers, passwords)
        wp_enqueue_script(
            'additional-validate',
            'https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/additional-methods.min.js',
            ['jquery'],
            null,
            true
        );

        // 14. Bootstrap JS Bundle (With Popper included for tooltip, dropdown, and popover animations)
        wp_enqueue_script(
            'bootstrap-bundle',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js',
            ['jquery'],
            null,
            true
        );

        // 16.5. CosyAlert Utility Script
        wp_enqueue_script(
            'cosy-alert',
            COSY_APPT_URL . 'src/Assets/js/cosy-alert.js',
            ['sweetalert2'],
            COSY_APPT_VER,
            true
        );

        // 17. Cosy Forms Controller script (Binds form events, AJAX saves, and updates for availability and profile details)
        wp_enqueue_script(
            'cosy-validation',
            COSY_APPT_URL . 'src/Assets/js/validation.js',
            ['cosy-api', 'jquery', 'jquery-validate', 'cosy-alert'],
            COSY_APPT_VER,
            true
        );

        // 20. Dashboard JS Controller (Controls interactive non-reloading reviews approval, fadeouts, and DOM operations)
        if (is_page('provider-dashboard')) {
            wp_enqueue_script(
                'cosy-dashboard',
                COSY_APPT_URL . 'src/Assets/js/dashboard.js',
                ['jquery', 'bootstrap-bundle', 'sweetalert2'],
                COSY_APPT_VER,
                true
            );

            // 21. Localization map for dashboard.js script
            wp_localize_script('cosy-dashboard', 'cosyDashboard', [
                'ajax_url'       => admin_url('admin-ajax.php'),
                'nonce'          => wp_create_nonce('cosy_dashboard_nonce'),
                'currencySymbol' => cosy_get_currency_symbol(),
                'currencyCode'   => cosy_get_currency_code(),
            ]);
        }

        if (is_page('cosy-checkout') || (function_exists('cosy_get_page_id') && is_page(cosy_get_page_id('cosy-checkout')))) {
            // 22. Checkout JS Controller (Handles dynamic rendering and payment processing on checkout)
            wp_enqueue_script(
                'cosy-checkout',
                COSY_APPT_URL . 'src/Assets/js/checkout.js',
                ['jquery', 'sweetalert2'],
                time(),
                true
            );

            // Pass necessary PHP variables safely to checkout JS to prevent inline injections
            $current_user = wp_get_current_user();
            $active_gw    = get_option('cosy_default_payment_gateway', 'worldpay');
            wp_localize_script('cosy-checkout', 'cosyCheckout', [
                'ajaxUrl'              => admin_url('admin-ajax.php'),
                'nonce'                => wp_create_nonce('cosy_booking_nonce'),
                'providerUrl'          => esc_url(cosy_get_page_url('service-provider')),
                'profileUrl'           => esc_url(cosy_get_page_url('customer-profile')),
                'customerName'         => $current_user->exists() ? esc_html($current_user->display_name) : '',
                'activeGateway'        => 'worldpay',
                'worldpayClientKey'    => esc_js(get_option('cosy_worldpay_client_key')),
                'currencySymbol'       => cosy_get_currency_symbol(),
                'currencyCode'         => cosy_get_currency_code(),
                'feeType'              => 'percent',
                'feeValue'             => floatval(get_option('cosy_worldpay_charge', '0')),
            ]);
        }

        // 23. Provider Profile Booking Calendar Script (Handles interactive calendar date clicks and slot modals)
        if (is_author() || is_page('service-provider') || (function_exists('cosy_get_page_id') && is_page(cosy_get_page_id('service-provider')))) {
            wp_enqueue_script(
                'cosy-calendar',
                COSY_APPT_URL . 'src/Assets/js/calendar.js',
                ['jquery', 'bootstrap-bundle', 'sweetalert2'],
                time(),
                true
            );
            wp_localize_script('cosy-calendar', 'cosyCalendar', [
                'currencySymbol' => cosy_get_currency_symbol(),
            ]);
        }
    }

    /**
     * Determines if the current page/request requires cosy appointments frontend assets.
     *
     * @return bool
     */
    private function should_load_assets(): bool
    {
        // 1. Always load in front page, home page, or author page (Provider Profile Page)
        if (is_front_page() || is_home() || is_author()) {
            return true;
        }

        // 2. If single post or page, check content for shortcodes or specific page IDs
        if (is_singular()) {
            $post = get_post();
            if ($post) {
                // List of plugin shortcodes to scan
                $shortcodes = [
                    'cosy_appointments',
                    'cosy_customer_registration',
                    'cosy_provider_registration',
                    'customer_profile',
                    'cosy_verify_provider',
                    'cosy_login_form',
                    'cosy_customer_order',
                    'cosy_service_provider_list',
                    'cosy_checkout',
                    'cosy_provider_dashboard',
                    'cosy_leave_review'
                ];

                foreach ($shortcodes as $shortcode) {
                    if (has_shortcode($post->post_content, $shortcode)) {
                        return true;
                    }
                }

                // Check against dynamic page IDs registered in the plugin
                $page_keys = [
                    'login',
                    'user-registration',
                    'provider-registration',
                    'appointments',
                    'orders',
                    'customer-order',
                    'customer-profile',
                    'provider-dashboard',
                    'provider-verify',
                    'service-provider',
                    'cosy-checkout',
                    'cosy-leave-review'
                ];

                $current_page_id = $post->ID;
                foreach ($page_keys as $key) {
                    if (function_exists('cosy_get_page_id')) {
                        if ($current_page_id === cosy_get_page_id($key)) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Appends defer attribute to non-critical frontend scripts to improve page render time.
     *
     * @param string $tag    The <script> tag for the enqueued script.
     * @param string $handle The script's registered handle.
     * @param string $src    The script's source URL.
     * @return string
     */
    public function add_defer_attribute(string $tag, string $handle, string $src): string
    {
        // Script handles to defer on frontend
        $defer_scripts = [
            'sweetalert2',
            'jquery-validate',
            'additional-validate',
            'bootstrap-bundle',
            'cosy-alert',
            'cosy-validation',
            'cosy-dashboard',
            'cosy-checkout',
        ];

        // Do not defer in admin panel
        if (is_admin()) {
            return $tag;
        }

        if (in_array($handle, $defer_scripts, true)) {
            // Ensure we don't add duplicate defer attributes if already present
            if (strpos($tag, ' defer') === false) {
                return str_replace(' src', ' defer src', $tag);
            }
        }

        return $tag;
    }
}
