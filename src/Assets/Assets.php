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
     * register
     * 
     * Sets up the hooks to load assets.
     * admin_enqueue_scripts -> Loads files in the WP Dashboard.
     * wp_enqueue_scripts    -> Loads files on the public website.
     */
    public function register(Loader $loader): void
    {
        $loader->add_action('admin_enqueue_scripts', $this, 'admin_assets');
        $loader->add_action('wp_enqueue_scripts', $this, 'frontend_assets');
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
            'users.php', // Required for the verification dropdown
        ];


        // Check if the current hook is allowed
        if (!in_array($hook, $allowed_hooks, true)) {
            return;
        }

        // Google Fonts
        wp_enqueue_style(
            'cosy-outfit-jakarta-font',
            'https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap',
            [],
            null
        );

        // Bootstrap CSS
        wp_enqueue_style(
            'cosy-bootstrap',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
            [],
            '5.3.2'
        );

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
            'cosy-admin-style',
            COSY_APPT_URL . 'src/Admin/assets/admin.css',
            [],
            COSY_APPT_VER
        );

        wp_enqueue_script(
            'cosy-admin-script',
            COSY_APPT_URL . 'src/Admin/assets/admin.js',
            ['jquery'],
            COSY_APPT_VER,
            true
        );
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
        wp_enqueue_style(
            'cosy-style',
            COSY_APPT_URL . 'src/assets/css/style.css',
            [],
            COSY_APPT_VER
        );

        // 2. Service Provider Profile Stylesheet (For profile layouts, calendar elements, and bios)
        wp_enqueue_style(
            'service-provider-style',
            COSY_APPT_URL . 'src/assets/css/service-provide.css',
            [],
            COSY_APPT_VER
        );

        // 3. Poppins Typography Web Font (Enforces clean brand-consistent fonts)
        wp_enqueue_style(
            'cosy-poppins-font',
            'https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap',
            [],
            null
        );

        // 3.5. Outfit & Plus Jakarta Sans Typography Web Fonts (For headings and checkout)
        wp_enqueue_style(
            'cosy-outfit-jakarta-font',
            'https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap',
            [],
            null
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

        // 10. Localization: Pass REST Base Endpoint URL and WP Nonces to cosy-script
        wp_localize_script('cosy-script', 'cosyAppointments', [
            'restUrl' => esc_url_raw(rest_url('cosy/v1/')),
            'nonce' => wp_create_nonce('wp_rest')
        ]);

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



        // 16. Cosy API Javascript Mapping Script (Defines core server endpoints for validation.js requests)
        wp_register_script(
            'cosy-api',
            COSY_APPT_URL . 'src/assets/js/api.js',
            [],
            '1.0',
            true
        );

        // 16.5. CosyAlert Utility Script
        wp_enqueue_script(
            'cosy-alert',
            COSY_APPT_URL . 'src/assets/js/cosy-alert.js',
            ['sweetalert2'],
            COSY_APPT_VER,
            true
        );

        // 17. Cosy Forms Controller script (Binds form events, AJAX saves, and updates for availability and profile details)
        wp_enqueue_script(
            'cosy-validation',
            COSY_APPT_URL . 'src/assets/js/validation.js',
            ['cosy-api', 'jquery', 'jquery-validate', 'cosy-alert'],
            COSY_APPT_VER,
            true
        );

        // 18. Localization data map for cosy-api script
        wp_localize_script('cosy-api', 'cosy_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('wp_rest'),
            'root'     => esc_url_raw(rest_url())
        ]);

        // Enqueue the registered cosy-api script dependency
        wp_enqueue_script('cosy-api');

        // 19. Core Frontend JS logic script
        wp_enqueue_script(
            'cosy-script',
            COSY_APPT_URL . 'src/assets/js/frontend.js',
            ['jquery', 'cosy-api'],
            COSY_APPT_VER,
            true
        );

        // 20. Dashboard JS Controller (Controls interactive non-reloading reviews approval, fadeouts, and DOM operations)
        if (is_page('provider-dashboard')) {
            wp_enqueue_script(
                'cosy-dashboard',
                COSY_APPT_URL . 'src/assets/js/dashboard.js',
                ['jquery', 'bootstrap-bundle', 'sweetalert2'],
                COSY_APPT_VER,
                true
            );

            // 21. Localization map for dashboard.js script
            wp_localize_script('cosy-dashboard', 'cosyDashboard', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('cosy_dashboard_nonce'),
            ]);
        }

        if (is_page('cosy-checkout')) {
            // Register Stripe JS Library
            wp_register_script(
                'stripe-js',
                'https://js.stripe.com/v3/',
                [],
                null,
                false // load in header so it is available before other scripts
            );

            // 22. Checkout JS Controller (Handles dynamic rendering and payment processing on checkout)
            wp_enqueue_script(
                'cosy-checkout',
                COSY_APPT_URL . 'src/assets/js/checkout.js',
                ['jquery', 'sweetalert2', 'stripe-js'],
                COSY_APPT_VER,
                true
            );

            // Pass necessary PHP variables safely to checkout JS to prevent inline injections
            $current_user = wp_get_current_user();
            wp_localize_script('cosy-checkout', 'cosyCheckout', [
                'ajaxUrl'              => admin_url('admin-ajax.php'),
                'nonce'                => wp_create_nonce('cosy_booking_nonce'),
                'providerUrl'          => esc_url(site_url('/service-provider')),
                'profileUrl'           => esc_url(site_url('/customer-profile')),
                'customerName'         => $current_user->exists() ? esc_html($current_user->display_name) : '',
                'customerEmail'        => $current_user->exists() ? esc_html($current_user->user_email) : '',
                'stripePublishableKey' => esc_js(get_option('cosy_stripe_publishable_key'))
            ]);
        }
    }
}
