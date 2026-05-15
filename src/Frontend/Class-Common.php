<?php

namespace Cosy\Appointments\Common;

trait GlobalCommonFunctions
{

    //------ Utility: Register AJAX Handlers Dynamically -----//
    protected function register_ajax_handlers(array $actions, $instance)
    {
        foreach ($actions as $action => $method) {
            add_action("wp_ajax_$action", [$instance, $method]);
            add_action("wp_ajax_nopriv_$action", [$instance, $method]);
        }
    }


    ///------ Utility: Get User Data -----//
    public function get_user_data(int $user_id): array
    {
        $user = get_userdata($user_id);
        if (!$user) {
            return [];
        }

        // Basic WP user fields
        $data = [
            'ID' => $user->ID,
            'username' => $user->user_login,
            'email' => $user->user_email,
            'name' => $user->display_name,
            'role' => implode(', ', $user->roles),
        ];

        $data['phone'] = get_user_meta($user_id, 'phone', true);
        $data['address'] = get_user_meta($user_id, 'address', true);
        $data['profile_image'] = get_user_meta($user_id, 'profile_image', true);
        $data['video_url'] = get_user_meta($user_id, 'introduction_video', true);

        return $data;
    }


    //------ Utility: Get Provider Data -----//
    public function get_provider_data(int $user_id): array
    {
        $user = get_userdata($user_id);
        if (!$user) {
            return [];
        }

        // Basic WP user fields
        $data = [
            'ID' => $user->ID,
            'username' => $user->user_login,
            'email' => $user->user_email,
            'name' => $user->display_name,
            'role' => implode(', ', $user->roles),
            'user_registered' => $user->user_registered,
        ];

        // ✅ Fetch all user meta in one go
        $meta = get_user_meta($user_id);

        // Flatten meta (each meta key returns array, so take first value)
        foreach ($meta as $key => $value) {
            $data[$key] = is_array($value) ? reset($value) : $value;
        }

        return $data;
    }


    //------ Utility: Get Selected/Checked Services for Provider (By Authorship) -----//
    public function get_checked_services(): array
    {
        $provider_id = get_current_user_id();
        if (!$provider_id) {
            return [];
        }

        // Get all cosy_service posts authored by this provider
        $services = get_posts([
            'post_type' => 'cosy_service',
            'post_status' => 'publish',
            'author' => $provider_id,
            'posts_per_page' => -1,
            'fields' => 'ids',
        ]);

        return is_array($services) ? array_map('intval', $services) : [];
    }


    //------ Utility: Get All Services -----//
    public function get_all_services(): array
    {
        // Fetch all cosy_service posts
        $services = get_posts([
            'post_type' => 'cosy_service',
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        $data = [];

        foreach ($services as $service) {
            $data[] = [
                'ID' => $service->ID,
                'title' => $service->post_title,
                'post_name' => $service->post_name,
                'description' => $service->post_content,
                'price' => get_post_meta($service->ID, 'service_price', true),
                'duration' => get_post_meta($service->ID, 'service_duration', true),
                'provider' => get_userdata($service->post_author)->display_name ?? '',
            ];
        }

        return $data;
    }

    //------ Utility: Get All Service Providers -----//
    public function get_all_service_providers(): array
    {
        global $wpdb;

        // Get service slug from URL (e.g. "kids" from /service-provider/kids/)
        $uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $current_url = trim($uri_path, '/');
        $url_segments = explode('/', $current_url);
        $service_slug = end($url_segments);

        $include_users = [];
        $provider_prices = []; // Initialize to avoid undefined variable error
        if (!empty($service_slug)) {
            // Find service matching the slug to get its ID
            $matched_services = get_posts([
                'post_type' => 'cosy_service',
                'post_status' => 'publish',
                'name' => $service_slug,
                'posts_per_page' => 1,
                'fields' => 'ids',
            ]);

            if (!empty($matched_services)) {
                $service_id = $matched_services[0];

                // Find providers who selected this service and their prices in ONE query
                $table_name = $wpdb->prefix . 'provider_services';
                $service_results = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT provider_id, price FROM $table_name WHERE service_id = %d AND checkbox_status = 'yes'",
                        $service_id
                    ),
                    OBJECT_K // This will index the result by Provider ID.
                );

                if (!empty($service_results)) {
                    $include_users = array_map('intval', array_keys($service_results));
                    $provider_prices = $service_results; // Index-ready array
                } else {
                    return []; // Service exists, but no provider selected it
                }
            } else {
                return []; // Service not found
            }
        }

        $args = [
            'role' => 'provider',
            'number' => -1,
            'order' => 'DESC',
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => 'cosy_provider_status',
                    'value' => 'active',
                    'compare' => '='
                ],
                [
                    'key' => 'cosy_provider_status',
                    'compare' => 'NOT EXISTS'
                ]
            ]
        ];

        if (!empty($include_users)) {
            $args['include'] = array_unique($include_users);
        } elseif (!empty($service_slug)) {
            // Service was specified but no providers found
            return [];
        }

        $service_providers = get_users($args);

        $data = [];

        foreach ($service_providers as $provider) {
            $user_id = $provider->ID;
            $data[] = [
                'ID' => $provider->ID,
                'username' => $provider->user_login,
                'email' => $provider->user_email,
                'name' => $provider->display_name,
                'role' => implode(', ', $provider->roles),
                // Custom user meta
                'prov_username' => get_user_meta($user_id, 'prov_username', true),
                'first_name' => get_user_meta($user_id, 'first_name', true),
                'middle_name' => get_user_meta($user_id, 'prov_mname', true),
                'last_name' => get_user_meta($user_id, 'last_name', true),
                'prov_email' => get_user_meta($user_id, 'prov_email', true),
                'phone' => get_user_meta($user_id, 'prov_phone', true),
                'address' => get_user_meta($user_id, 'prov_address', true),
                'dob' => get_user_meta($user_id, 'dob', true),
                'postal_code' => get_user_meta($user_id, 'postal_code', true),
                'description' => get_user_meta($user_id, 'description', true),
                'gender' => get_user_meta($user_id, 'gender', true),
                'profile_image' => get_user_meta($user_id, 'profile_image', true),
                'age_group' => get_user_meta($user_id, 'age_group', true),
                'introduction_video' => get_user_meta($user_id, 'introduction_video', true),
                'price' => isset($provider_prices[$user_id]) ? $provider_prices[$user_id]->price : '0.00',
            ];
        }


        return $data;
    }

    //------ Utility: Get Provider with Services by slug -----//
    public function get_provider_with_services(string $slug): array
    {
        $user = get_user_by('slug', $slug);
        if (!$user) {
            // Fallback: check by author name if slug doesn't match
            $user = get_user_by('login', $slug);
        }

        if (!$user) {
            return [];
        }

        $provider_id = $user->ID;
        $data = $this->get_provider_data($provider_id);

        global $wpdb;
        $table = $wpdb->prefix . 'provider_services';
        $posts_table = $wpdb->posts;

        // Fetching ALL selected services for this provider
        $services_data = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 
                    ps.service_id AS ID, 
                    p.post_title AS title, 
                    ps.price, 
                    ps.duration AS time 
                FROM $table ps
                JOIN $posts_table p ON ps.service_id = p.ID
                WHERE ps.provider_id = %d AND ps.checkbox_status = 'yes'",
                $provider_id
            ),
            ARRAY_A
        );

        $data['services'] = !empty($services_data) ? $services_data : [];
        return $data;
    }
}
