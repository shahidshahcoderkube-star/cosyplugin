<?php

/**
 * Plugin Name: Cosy Appointments
 * Description: A complete multi-provider appointment booking and scheduling solution for WordPress. Manage providers, services, availability, and payments — all in one place.
 * Version: 1.0.61
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
define('COSY_APPT_VER', '1.0.61');  // Current version

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

/**
 * HANDLES PLUGIN ACTIVATION
 * 
 * USE CASE:
 * Executed when administrator activates Cosy Appointments plugin in WP Admin.
 * 
 * HOW TO USE:
 * Triggered automatically by WordPress register_activation_hook().
 * 
 * WHAT IT DOES INTERNALLY:
 * 1. Instantiates Activator class.
 * 2. Creates custom user roles ('customer' and 'provider').
 * 3. Triggers database table migration scripts via Database::run_db_migrations().
 * 4. Schedules cleanup cron job and flushes rewrite rules.
 */
function cosy_plugin_activate()
{
    $activator = new \Cosy\Appointments\Common\Activator();
    $activator->activate();
}

/**
 * HANDLES PLUGIN DEACTIVATION
 * 
 * USE CASE:
 * Executed when administrator deactivates Cosy Appointments plugin in WP Admin.
 * 
 * HOW TO USE:
 * Triggered automatically by WordPress register_deactivation_hook().
 * 
 * WHAT IT DOES INTERNALLY:
 * 1. Instantiates Activator class.
 * 2. Un-schedules cleanup cron jobs via Cron::deactivate().
 * 3. Flushes WordPress rewrite rules to remove custom permalink endpoints.
 */
function cosy_plugin_deactivate()
{
    $activator = new \Cosy\Appointments\Common\Activator();
    $activator->deactivate();
}

/**
 * BOOTSTRAPS COSY APPOINTMENTS PLUGIN
 * 
 * USE CASE:
 * Main plugin execution entry point triggered on script load.
 * 
 * HOW TO USE:
 * cosy_appt_start();
 * 
 * WHAT IT DOES INTERNALLY:
 * 1. Instantiates main Plugin class.
 * 2. Instantiates DeactivationHandler.
 * 3. Calls $plugin->run() to attach all hooks to WordPress core.
 */
function cosy_appt_start()
{
    $plugin = new \Cosy\Appointments\Plugin();
    new \Cosy\Appointments\Admin\DeactivationHandler();
    $plugin->run();
}
cosy_appt_start();
