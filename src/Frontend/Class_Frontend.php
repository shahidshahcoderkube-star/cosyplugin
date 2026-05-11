<?php

namespace Cosy\Appointments\Frontend;

use Cosy\Appointments\Forms\FormsData;
use Cosy\Appointments\Loader;
use Cosy\Appointments\Common\GlobalCommonFunctions;

class Frontend
{
    use GlobalCommonFunctions;

    //--------------- Register Actions ----------------//
    public function register(Loader $loader): void
    {
        /* Register shortcode and footer action*/
        $loader->add_action('init', $this, 'register_shortcode');
        $loader->add_action('wp_footer', $this, 'render_register_popup');
        $loader->add_action('template_redirect', $this, 'restrict_direct_page_access');
        $loader->add_action('after_setup_theme', $this, 'hide_admin_menu');
        
        // FormsData handles its own registration in constructor
        new FormsData();

        $loader->add_filter('template_include', $this, 'provider_profile_dashboard_shortcode', 9999);
    }


    //--------------- Registering the shortcode for appointments----------------//
    public function register_shortcode(): void
    {
        add_shortcode('cosy_appointments', [$this, 'appointments_shortcode']);
        add_shortcode('cosy_customer_registration', [$this, 'customer_registration_shortcode']);
        add_shortcode('cosy_provider_registration', [$this, 'provider_registration_shortcode']);
        add_shortcode('customer_profile', [$this, 'customer_profile_shortcode']);
        // add_shortcode('cosy_profile_dashboard', [$this, 'provider_profile_dashboard_shortcode']);
        add_shortcode('cosy_verify_provider', [$this, 'provider_verify_shortcode']);
        add_shortcode('cosy_login_form', [$this, 'login_form']);
        add_shortcode('cosy_customer_order', [$this, 'customer_order_page']);
        add_shortcode('cosy_service_provider_list', [$this, 'service_provider_shortcode']);
    }


    /**
     * This function renders the popup for choosing member type
     * The register button link is created in the admin menu settings as link in to the menu.
     */

    public function render_register_popup(): void
    {
        ob_start();
        include COSY_APPT_PATH . 'templates/popup-template.php';
        echo ob_get_clean();
    }


    //------------- Rendering the shortcode content */
    public function appointments_shortcode(): string
    {
        ob_start();
        include COSY_APPT_PATH . 'templates/appointments-template.php';
        return ob_get_clean();
    }


    //------------- Rendering the customer registration shortcode content -------------//
    public function customer_registration_shortcode(): string
    {
        ob_start();
        include COSY_APPT_PATH . 'templates/customer-registration-template.php';
        return ob_get_clean();
    }


    //------------- Rendering the provider registration shortcode content -------------//
    public function provider_registration_shortcode(): string
    {
        ob_start();
        include COSY_APPT_PATH . 'templates/provider-registration-template.php';
        return ob_get_clean();
    }


    //------------- Rendering the customer profile shortcode content -------------//
    public function customer_profile_shortcode(): string
    {
        ob_start();
        include COSY_APPT_PATH . 'templates/customer-profile-template.php';
        return ob_get_clean();
    }


    //------------- Rendering the login form shortcode content -------------//
    public function login_form(): string
    {

        ob_start();
        include COSY_APPT_PATH . 'templates/login-template.php';
        return ob_get_clean();
    }


    //------------- Rendering the customer order shortcode content -------------//
    public function customer_order_page(): string
    {
        ob_start();
        include COSY_APPT_PATH . 'templates/customer-order-template.php';
        return ob_get_clean();
    }


    //------------- Rendering the provider dashboard shortcode content -------------//
    public function provider_verify_shortcode(): string
    {
        ob_start();
        include COSY_APPT_PATH . 'templates/provider/provider-verify.php';
        return ob_get_clean();
    }


    public function service_provider_shortcode(): string
    {
        ob_start();
        include COSY_APPT_PATH . 'templates/service-provider-template.php';
        return ob_get_clean();
    }

    //------------- Rendering the provider profile dashboard content it open single page when user click on provider profile view profile button -------------//
    public function provider_profile_dashboard_shortcode($template): string
    {
        if (is_author()) {
            $custom_template = COSY_APPT_PATH . 'templates/provider-profile-template.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        return $template;
    }
    //------------- Hide Admin Menu for specific roles -------------//
    public function hide_admin_menu()
    {

        if (is_user_logged_in()) {
            $user = wp_get_current_user();

            // Roles for which the admin bar should be hidden
            $hide_roles = array('customer', 'provider');

            if (array_intersect($hide_roles, (array) $user->roles)) {
                show_admin_bar(false);
            }
        }
    }


    //------------- Restrict Direct Page Access -------------//
    public function restrict_direct_page_access()
    {
        // Pages that require login
        $restricted_slugs = ['appointments', 'orders', 'customer-order', 'customer-profile', 'provider-dashboard', 'provider-verify'];
        if (is_page($restricted_slugs) && !is_user_logged_in()) {
            wp_redirect(site_url('/login'));
            exit;
        }

        // Additional check for provider-dashboard page
        if (is_user_logged_in()) {

            $user = wp_get_current_user();

            $blocked_for_provider = ['customer-order', 'customer-profile', 'appointments', 'orders'];
            if (in_array('provider', (array) $user->roles) && is_page($blocked_for_provider)) {
                wp_redirect(site_url('/provider-dashboard'));
                exit;
            }

            $blocked_for_customer = ['provider-dashboard', 'provider-verify'];
            if (in_array('customer', (array) $user->roles, true) && is_page($blocked_for_customer)) {
                wp_safe_redirect(site_url('/customer-profile'));
                exit;
            }

            // fallback
            // wp_redirect(site_url('/login'));
            // exit;
        }
    }
}
