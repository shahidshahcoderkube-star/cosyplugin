<?php

/**
 * Plugin Name: Cosy Appointments
 * Description: A complete multi-provider appointment booking and scheduling solution for WordPress. Manage providers, services, availability, and payments — all in one place.
 * Version: 1.0.35
 * Author: Shahid Shah — Coderkube Technology
 * Author URI: https://coderkube.com
 * Text Domain: cosy-appointments
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Main Plugin Constants
 * These constants define the paths and version of the plugin for easy access.
 */
define('COSY_APPT_PATH', plugin_dir_path(__FILE__));   // Absolute path to the plugin folder
define('COSY_APPT_URL', plugin_dir_url(__FILE__));     // URL to the plugin folder
define('COSY_APPT_VER', '1.0.35');  // Current version

// Custom robust PSR-4 autoloader with dual-mode fail-safe for Windows-to-Linux ZIP extraction bugs
spl_autoload_register(function ($class) {
    $prefix = 'Cosy\\Appointments\\';
    $base_dir = COSY_APPT_PATH . 'src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);

    // 1. Standard cross-platform path (using forward slashes)
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require_once $file;
        return;
    }

    // 2. Fail-safe backup for Windows ZIP extraction bug on Linux hosts
    // If files are extracted flat in the root directory with literal backslashes in their names
    $flat_file = COSY_APPT_PATH . 'src\\' . $relative_class . '.php';
    if (file_exists($flat_file)) {
        require_once $flat_file;
        return;
    }
});

// Manually load the Helpers file with fail-safe check
if (file_exists(COSY_APPT_PATH . 'src/Helpers.php')) {
    require_once COSY_APPT_PATH . 'src/Helpers.php';
} elseif (file_exists(COSY_APPT_PATH . 'src\\Helpers.php')) {
    require_once COSY_APPT_PATH . 'src\\Helpers.php';
}

//-------Register activation hook--------//
register_activation_hook(__FILE__, 'cosy_plugin_activate');

//-------Register deactivation hook--------//
register_deactivation_hook(__FILE__, 'cosy_plugin_deactivate');

//-------Register roles & database tables on activation--------//
function cosy_plugin_activate()
{
    $activator = new \Cosy\Appointments\Common\Activator();
    $activator->activate();
}

//-------Clean up rewrite rules on deactivation--------//
function cosy_plugin_deactivate()
{
    $activator = new \Cosy\Appointments\Common\Activator();
    $activator->deactivate();
}

/**
 * cosy_appt_start
 * 
 * This is the entry point of the plugin.
 * It initializes the main Plugin class and triggers the run() method.
 */
function cosy_appt_start()
{
    $plugin = new \Cosy\Appointments\Plugin();
    new \Cosy\Appointments\Admin\DeactivationHandler();
    $plugin->run();
}
cosy_appt_start();
