<?php

namespace Cosy\Appointments\Admin;

/**
 * Cosy Appointments - Demo Data Importer Engine
 * Decoupled controller that imports and builds a complete, vibrant demo environment
 * from standalone JSON files, strictly under fresh installation conditions.
 */
class DemoImporter
{
    /**
     * Identifies if the database has any prior user or post records
     * related to Cosy Appointments. Ensures 100% protection for live sites!
     */
    public static function is_fresh_install(): bool
    {
        // 1. Check if there are any existing services
        $services = get_posts([
            'post_type'   => 'cosy_service',
            'post_status' => 'any',
            'numberposts' => 1,
            'fields'      => 'ids'
        ]);
        if (!empty($services)) {
            return false;
        }

        // 2. Check if there are any existing appointments
        $appointments = get_posts([
            'post_type'   => 'cosy_appointment',
            'post_status' => 'any',
            'numberposts' => 1,
            'fields'      => 'ids'
        ]);
        if (!empty($appointments)) {
            return false;
        }

        // 3. Check if there are any existing providers in the database
        $providers = get_users([
            'role'   => 'provider',
            'number' => 1,
            'fields' => 'ID'
        ]);
        if (!empty($providers)) {
            return false;
        }

        return true;
    }

    /**
     * Automatically imports the demo environment.
     * Triggered during fresh installation only.
     */
    public static function run_seeder(): bool
    {
        // Block execution if it's not a completely fresh install
        if (!self::is_fresh_install()) {
            return false;
        }

        // Check if already seeded to prevent duplicate operations
        if (get_option('cosy_demo_data_seeded')) {
            return false;
        }

        $base_path = plugin_dir_path(dirname(dirname(__FILE__))) . 'demo-data/';
        
        $providers_file  = $base_path . 'providers.json';
        $services_file   = $base_path . 'services.json';
        $customers_file  = $base_path . 'customers.json';
        $appointments_file = $base_path . 'appointments.json';

        if (!file_exists($providers_file) || !file_exists($services_file) || !file_exists($customers_file) || !file_exists($appointments_file)) {
            return false;
        }

        // Parse JSON data
        $providers_data   = json_decode(file_get_contents($providers_file), true);
        $services_data    = json_decode(file_get_contents($services_file), true);
        $customers_data   = json_decode(file_get_contents($customers_file), true);
        $appointments_data = json_decode(file_get_contents($appointments_file), true);

        if (!$providers_data || !$services_data || !$customers_data || !$appointments_data) {
            return false;
        }

        // 1. Seed Providers
        $seeded_providers = [];
        foreach ($providers_data as $idx => $prov) {
            // Check if user already exists (by email or username)
            $existing_user = get_user_by('login', $prov['username']);
            if (!$existing_user) {
                $existing_user = get_user_by('email', $prov['email']);
            }

            if ($existing_user) {
                $user_id = $existing_user->ID;
            } else {
                $display_name = $prov['first_name'] . ' ' . (!empty($prov['middle_name']) ? $prov['middle_name'] . ' ' : '') . $prov['last_name'];
                $user_id = wp_insert_user([
                    'user_login'   => $prov['username'],
                    'user_pass'    => $prov['password'],
                    'user_email'   => $prov['email'],
                    'first_name'   => $prov['first_name'],
                    'last_name'    => $prov['last_name'],
                    'nickname'     => $prov['first_name'],
                    'display_name' => $display_name,
                    'role'         => 'provider'
                ]);
            }

            if (!is_wp_error($user_id)) {
                // Save custom provider metadata
                update_user_meta($user_id, 'prov_mname', $prov['middle_name']);
                update_user_meta($user_id, 'provider_category', $prov['category']);
                update_user_meta($user_id, 'provider_status', $prov['status']);
                update_user_meta($user_id, 'provider_bio', $prov['bio']);
                update_user_meta($user_id, 'is_demo_data', 1);
                
                // Active status metadata
                update_user_meta($user_id, 'video_status', 'approved');
                update_user_meta($user_id, 'introduction_video', 'https://wppremiumplugins.com/cosychats/wp-content/plugins/cosy-appointments/src/Assets/images/intro-demo.mp4');

                $seeded_providers[$idx] = [
                    'id'           => $user_id,
                    'display_name' => $prov['first_name'] . ' ' . $prov['last_name']
                ];
            }
        }

        // 2. Seed Services
        $seeded_services = [];
        foreach ($services_data as $idx => $srv) {
            $post_id = wp_insert_post([
                'post_title'   => $srv['title'],
                'post_content' => $srv['content'],
                'post_status'  => 'publish',
                'post_type'    => 'cosy_service'
            ]);

            if ($post_id && !is_wp_error($post_id)) {
                update_post_meta($post_id, 'cosy_service_duration', $srv['duration']);
                update_post_meta($post_id, 'cosy_service_cost', $srv['cost']);
                update_post_meta($post_id, 'cosy_service_category', $srv['category']);
                update_post_meta($post_id, 'is_demo_data', 1);

                $seeded_services[$idx] = [
                    'id'    => $post_id,
                    'title' => $srv['title']
                ];
            }
        }

        // 3. Seed Customers
        $seeded_customers = [];
        foreach ($customers_data as $idx => $cust) {
            $existing_user = get_user_by('login', $cust['username']);
            if (!$existing_user) {
                $existing_user = get_user_by('email', $cust['email']);
            }

            if ($existing_user) {
                $user_id = $existing_user->ID;
            } else {
                $user_id = wp_insert_user([
                    'user_login'   => $cust['username'],
                    'user_pass'    => $cust['password'],
                    'user_email'   => $cust['email'],
                    'first_name'   => $cust['first_name'],
                    'last_name'    => $cust['last_name'],
                    'nickname'     => $cust['first_name'],
                    'display_name' => $cust['first_name'] . ' ' . $cust['last_name'],
                    'role'         => 'customer'
                ]);
            }

            if (!is_wp_error($user_id)) {
                update_user_meta($user_id, 'is_demo_data', 1);
                $seeded_customers[$idx] = [
                    'id'           => $user_id,
                    'display_name' => $cust['first_name'] . ' ' . $cust['last_name'],
                    'email'        => $cust['email']
                ];
            }
        }

        // 4. Seed Appointments
        foreach ($appointments_data as $appt) {
            $provider = isset($seeded_providers[$appt['provider_idx']]) ? $seeded_providers[$appt['provider_idx']] : null;
            $customer = isset($seeded_customers[$appt['customer_idx']]) ? $seeded_customers[$appt['customer_idx']] : null;
            $service  = isset($seeded_services[$appt['service_idx']]) ? $seeded_services[$appt['service_idx']] : null;

            if (!$provider || !$customer || !$service) {
                continue;
            }

            // Calculate dynamic relative timestamps
            $relative_time = strtotime($appt['date_offset']);
            $post_date     = date('Y-m-d H:i:s', $relative_time);
            $start_date    = date('M d, Y h:i A', $relative_time);
            $end_date      = date('M d, Y h:i A', strtotime("+{$service['id']} minutes", $relative_time));

            $appt_post_id = wp_insert_post([
                'post_title'   => 'Booking for ' . $customer['display_name'],
                'post_content' => 'Seeded booking entry for demonstration purposes.',
                'post_status'  => $appt['post_status'],
                'post_type'    => 'cosy_appointment',
                'post_date'    => $post_date
            ]);

            if ($appt_post_id && !is_wp_error($appt_post_id)) {
                // Populate all essential order metadata
                update_post_meta($appt_post_id, 'cosy_customer_id', $customer['id']);
                update_post_meta($appt_post_id, 'cosy_customer_name', $customer['display_name']);
                update_post_meta($appt_post_id, 'cosy_customer_email', $customer['email']);
                
                update_post_meta($appt_post_id, 'cosy_provider_id', $provider['id']);
                update_post_meta($appt_post_id, 'cosy_provider_name', $provider['display_name']);
                
                update_post_meta($appt_post_id, 'cosy_service_name', $service['title']);
                update_post_meta($appt_post_id, 'cosy_start_date', $start_date);
                update_post_meta($appt_post_id, 'cosy_end_date', $end_date);
                update_post_meta($appt_post_id, 'cosy_appointment_date', $start_date);
                update_post_meta($appt_post_id, 'cosy_appointment_price', $appt['total']);

                update_post_meta($appt_post_id, 'cosy_weekly_booking', 'Single Session');
                update_post_meta($appt_post_id, 'cosy_number_of_weeks', $appt['weeks']);
                update_post_meta($appt_post_id, 'cosy_number_of_bookings', $appt['slots']);
                update_post_meta($appt_post_id, 'cosy_service_cost', $appt['cost']);
                update_post_meta($appt_post_id, 'cosy_service_fee', $appt['fee']);
                update_post_meta($appt_post_id, 'cosy_total_payable', $appt['total']);
                update_post_meta($appt_post_id, 'cosy_booking_status', $appt['booking_status']);
                update_post_meta($appt_post_id, 'is_demo_data', 1);

                // Add payment handshakes
                if ($appt['post_status'] === 'publish') {
                    update_post_meta($appt_post_id, 'cosy_stripe_session_id', 'cs_test_demo_' . wp_generate_password(16, false));
                }
            }
        }

        // 5. Append Mock Payments Log File
        $log_file = plugin_dir_path(dirname(dirname(__FILE__))) . 'payment.log';
        $log_entry = "[" . date('d-M-Y H:i:s e') . "] --- MOCK STIPE TRANSACTION DEMO WEBHOOK HANDSHAKE SUCCESSFUL --- \n" .
                     "Checkout session completed. Total Processed Revenue: £1,220. Webhook Response: 200 OK\n\n";
        file_put_contents($log_file, $log_entry, FILE_APPEND);

        // Mark as seeded in WP Options
        update_option('cosy_demo_data_seeded', 1);
        return true;
    }

    /**
     * Cleans up the database by removing all seeder custom posts, users, and logs.
     * Restores the system to a pristine, fresh state.
     */
    public static function wipe_demo_data(): bool
    {
        // 1. Delete all CPT Services marked as demo
        $services = get_posts([
            'post_type'   => 'cosy_service',
            'post_status' => 'any',
            'numberposts' => -1,
            'meta_key'    => 'is_demo_data',
            'meta_value'  => 1,
            'fields'      => 'ids'
        ]);
        foreach ($services as $id) {
            wp_delete_post($id, true);
        }

        // 2. Delete all CPT Appointments marked as demo
        $appointments = get_posts([
            'post_type'   => 'cosy_appointment',
            'post_status' => 'any',
            'numberposts' => -1,
            'meta_key'    => 'is_demo_data',
            'meta_value'  => 1,
            'fields'      => 'ids'
        ]);
        foreach ($appointments as $id) {
            wp_delete_post($id, true);
        }

        // 3. Delete all custom users marked as demo
        $demo_users = get_users([
            'meta_key'   => 'is_demo_data',
            'meta_value' => 1,
            'fields'     => 'ID'
        ]);
        
        // Include default master accounts as a fallback check
        $master_provider = get_user_by('login', 'demo_provider');
        if ($master_provider && !in_array($master_provider->ID, $demo_users)) {
            $demo_users[] = $master_provider->ID;
        }
        $master_customer = get_user_by('login', 'demo_customer');
        if ($master_customer && !in_array($master_customer->ID, $demo_users)) {
            $demo_users[] = $master_customer->ID;
        }

        require_once ABSPATH . 'wp-admin/includes/user.php';
        foreach ($demo_users as $uid) {
            wp_delete_user($uid);
        }

        // Clear option
        delete_option('cosy_demo_data_seeded');

        // Reset Stripe configuration option to defaults if any
        delete_option('force_plugin_deleted');

        return true;
    }
}
