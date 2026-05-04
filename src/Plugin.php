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

// use Cosy\Appointments\Common\GlobalCommonFunctions;

class Plugin
{
    protected Loader $loader;

    //--------------- Constructor ----------------//
    public function __construct()
    {
        //--------------- Initialize Loader ----------------//
        $this->loader = new Loader();

        //--------------- Register Admin Components ----------------//
        (new Admin())->register($this->loader);

        //--------------- Register Backend AJAX Handlers ----------------//
        (new Backend_Actions_Handler());

        //--------------- Register Settings & Assets ----------------//
        (new SettingsAdmin())->register($this->loader);

        //--------------- Register Assets ----------------//
        (new Assets())->register($this->loader);

        //--------------- Register Custom Post Types ----------------//
        (new ServiceCPT())->register($this->loader);

        //--------------- Register Frontend Components ----------------//
        (new Frontend())->register($this->loader);

        //--------------- Register Provider Dashboard ----------------//
        (new Dashboard())->register($this->loader);

        //--------------- Register REST API Routes ----------------//
        (new Routes())->register($this->loader);
        // (new ProviderServices())->register($this->loader);

        if (class_exists(\Cosy\Appointments\Rest\ProviderServices::class)) {
            error_log('✅ ProviderServices loaded');
        } else {
            error_log('❌ ProviderServices NOT loaded');
        }
    }

    //--------------- Run the Plugin ----------------//
    public function run(): void
    {
        $this->loader->run();
    }
    //--------------- Register Shortcode ----------------//
    public function register_shortcode(): void
    {
        add_shortcode('cosy_appointments', [$this, 'render_shortcode']);
    }

    //--------------- Shortcode Render Callback ----------------//
    public function render_shortcode(): string
    {
        return '<h2>Welcome to Cosy Appointments</h2>
                <p>Plugin Path: ' . COSY_APPT_PATH . '</p>
                <p>Plugin URL: ' . COSY_APPT_URL . '</p>
                <p>Plugin Version: ' . COSY_APPT_VER . '</p>';
    }
}
