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

    //---------------- Frontend Assets ----------------//
    public function frontend_assets(): void
    {
        wp_enqueue_style(
            'cosy-style',
            COSY_APPT_URL . 'src/assets/css/style.css',
            [],
            COSY_APPT_VER
        );

        wp_enqueue_style(
            'service-provider-style',
            COSY_APPT_URL . 'src/assets/css/service-provide.css',
            [],
            COSY_APPT_VER
        );

        wp_enqueue_style(
            'cosy-poppins-font',
            'https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap',
            [],
            null
        );
        wp_enqueue_style(
            'bootstrap',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
        );

        wp_enqueue_style(
            'font-awesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css',
        );

        wp_enqueue_style(
            'bootstrap-icons',
            'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css',
        );

        wp_enqueue_style(
            'fullcalendar-css',
            'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css',
        );

        // SweetAlert2: Used for beautiful popup notifications (Success, Error, Confirmations).
        wp_enqueue_style(
            'sweetalert2',
            'https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css',
            [],
            '11.10.5'
        );

        wp_enqueue_script(
            'sweetalert2',
            'https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js',
            [],
            '11.10.5',
            true
        );

        // Pass REST base URL to JS
        wp_localize_script('cosy-script', 'cosyAppointments', [
            'restUrl' => esc_url_raw(rest_url('cosy/v1/')),
            'nonce' => wp_create_nonce('wp_rest')
        ]);

        wp_enqueue_script('jquery-ui-datepicker');


        wp_enqueue_script(
            'jquery-validate',
            'https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js',
            ['jquery'],
            null,
            true
        );

        wp_enqueue_script(
            'additional-validate',
            'https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/additional-methods.min.js',
            ['jquery'],
            null,
            true
        );

        wp_enqueue_script(
            'bootstrap-bundle',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js',
            ['jquery'],
            null,
            true
        );

        wp_enqueue_script(
            'fullcalendar-js',
            'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.20/index.global.min.js',
            ['jquery'],
            null,
            true
        );

        // Register API Logic (api.js): This defines the endpoints the JS uses to talk to the server.
        // It must be registered first so validation.js can use it.
        wp_register_script(
            'cosy-api',
            COSY_APPT_URL . 'src/assets/js/api.js',
            [],
            '1.0',
            true
        );

        wp_enqueue_script(
            'cosy-validation',
            COSY_APPT_URL . 'src/assets/js/validation.js',
            ['cosy-api', 'jquery', 'jquery-validate'],
            rand(),
            true
        );

        // Localization: This passes PHP data (like the AJAX URL and Security Nonce) to JavaScript.
        // It allows 'api.js' to know exactly where to send data.
        wp_localize_script('cosy-api', 'cosy_ajax', ['ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('wp_rest'), 'root' => esc_url_raw(rest_url())]);
        // 3️⃣ Enqueue api.js
        wp_enqueue_script('cosy-api');

        wp_enqueue_script(
            'cosy-script',
            COSY_APPT_URL . 'src/assets/js/frontend.js',
            ['jquery'],
            COSY_APPT_VER,
            true
        );

        // Dashboard JS: Handles all Provider Dashboard tab interactions (holidays, etc.)
        wp_enqueue_script(
            'cosy-dashboard',
            COSY_APPT_URL . 'src/assets/js/dashboard.js',
            ['jquery', 'bootstrap-bundle', 'sweetalert2'],
            COSY_APPT_VER,
            true
        );

        // Pass AJAX URL + nonce to dashboard.js
        wp_localize_script('cosy-dashboard', 'cosyDashboard', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('cosy_dashboard_nonce'),
        ]);

        //cosy_nonce
    }
}
