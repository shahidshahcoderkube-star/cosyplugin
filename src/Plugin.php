<?php

namespace Cosy\Appointments;

use Cosy\Appointments\PostTypes\ServiceCPT;
use Cosy\Appointments\Frontend\Frontend;
use Cosy\Appointments\Admin\Admin;
use Cosy\Appointments\Admin\Backend_Actions_Handler;
use Cosy\Appointments\Admin\SettingsAdmin;
use Cosy\Appointments\Rest\Routes;
use Cosy\Appointments\Assets\Assets;
use Cosy\Appointments\Frontend\Dashboard;
use Cosy\Appointments\Rest\ProviderServices;
use Cosy\Appointments\Frontend\Class_Header_Menu;

// use Cosy\Appointments\Common\GlobalCommonFunctions;

class Plugin
{
    protected Loader $loader;

    /**
     * Constructor
     * This runs when the plugin is initialized. It registers all the 
     * necessary components like Admin area, Frontend logic, Custom Post Types, and API routes.
     */
    public function __construct()
    {
        // Initialize the Loader class which helps register all WordPress hooks cleanly.
        $this->loader = new Loader();

        // Register Admin Panel Menus and Settings Pages.
        (new Admin())->register($this->loader);
        (new \Cosy\Appointments\Admin\Class_Provider_Verification())->register($this->loader);
        (new \Cosy\Appointments\Admin\UsersAdmin())->register($this->loader);

        // Register AJAX handlers for backend (Admin area) actions.
        (new Backend_Actions_Handler());

        // Register the plugin's Global Settings page in WP Admin.
        (new SettingsAdmin())->register($this->loader);

        // Load CSS and JS files for the plugin.
        (new Assets())->register($this->loader);

        // Register Custom Post Types like Appointments and Services.
        (new ServiceCPT())->register($this->loader);

        // Register Frontend elements like shortcodes, checkout, and forms.
        (new Frontend())->register($this->loader);

        // Register Stripe Payment Gateway & Checkout AJAX handler.
        (new \Cosy\Appointments\Gateways\StripePaymentGateway())->register($this->loader);

        // Register the Provider Dashboard interface and logic.
        (new Dashboard())->register($this->loader);

        // Register the custom header menu dropdown functionality.
        (new Class_Header_Menu())->register($this->loader);

        // Register custom REST API endpoints for external applications.
        (new Routes())->register($this->loader);

        // Register Admin Reviews moderation handlers
        (new \Cosy\Appointments\Admin\Class_Reviews_Admin())->register($this->loader);

        // Register AI Search Controller endpoints (Gemini / OpenAI vector engine)
        new \Cosy\Appointments\AI\SearchController();

        // Register database migrations
        $database = new \Cosy\Appointments\Common\Database();
        $this->loader->add_action('plugins_loaded', $database, 'run_db_migrations');

        // Register rewrite rules and page initialization checks
        $activator = new \Cosy\Appointments\Common\Activator();
        $this->loader->add_action('init', $activator, 'author_rewrite');
        $this->loader->add_filter('query_vars', $activator, 'register_query_vars');
        $this->loader->add_action('init', $activator, 'check_and_create_missing_pages');

        // Register scheduled cron tasks
        $cron = new \Cosy\Appointments\Common\Cron();
        $this->loader->add_action('cosy_cleanup_activity_logs_cron', $cron, 'do_cleanup_activity_logs');
    }

    /**
     * Executes all the registered WordPress hooks (actions and filters)
     * through the Loader class.
     */
    public function run(): void
    {
        $this->loader->run();
    }
}


