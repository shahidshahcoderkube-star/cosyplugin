<?php

namespace Cosy\Appointments\Common;

/**
 * Trait GlobalCommonFunctions
 * 
 * This trait contains helper functions that are used by multiple classes in the plugin.
 * It helps avoid repeating the same code in different places.
 */
trait GlobalCommonFunctions
{

    /**
     * register_ajax_handlers
     * 
     * This utility function automatically registers WordPress AJAX actions.
     * It connects a JavaScript 'action' string to a specific PHP method in a class.
     * wp_ajax_          -> For logged-in users.
     * wp_ajax_nopriv_   -> For guest/non-logged-in users.
     */
    protected function register_ajax_handlers(array $actions, $instance)
    {
        foreach ($actions as $action => $method) {
            add_action("wp_ajax_$action", [$instance, $method]);
            add_action("wp_ajax_nopriv_$action", [$instance, $method]);
        }
    }

    /**
     * verify_ajax_request
     *
     * Combines nonce verification, role authorization, and login check into one call.
     * Use this at the start of any AJAX handler to avoid repeating the same 3 checks.
     *
     * @param string $nonce_action  The nonce action name (e.g., 'cosy_dashboard_nonce').
     * @param string $nonce_field   The POST field containing the nonce (default: 'nonce').
     * @param string $required_role The role required to proceed (default: 'provider').
     * @return int                  The current user ID (guaranteed to be valid).
     */
    protected function verify_ajax_request(string $nonce_action, string $nonce_field = 'nonce', string $required_role = 'provider'): int
    {
        check_ajax_referer($nonce_action, $nonce_field);

        if (!current_user_can('manage_cosy_appointments') && !in_array($required_role, (array) wp_get_current_user()->roles)) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(['message' => 'User not logged in']);
        }

        return $user_id;
    }

    /**
     * cosy_payment_log
     * Logs payment activity into a file.
     */
    public function cosy_payment_log(string $message, $data = null): void
    {
        $log_file = COSY_APPT_PATH . 'payment.log';
        $timestamp = current_time('mysql');
        
        $entry = "[$timestamp] $message";
        if ($data !== null) {
            $entry .= " | DATA: " . wp_json_encode($data);
        }
        
        // Append to file safely
        file_put_contents($log_file, $entry . PHP_EOL, FILE_APPEND);
    }



    /**
     * Retrieves basic WordPress and custom metadata for a specific user.
     * Often used to display customer profiles.
     */
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


    /**
     * Retrieves detailed profile data specifically for a Service Provider.
     * It efficiently fetches only the metadata fields required to display 
     * a provider's dashboard or public profile.
     */
    public function get_provider_data(int $user_id): array
    {
        $user = get_userdata($user_id);
        if (!$user) {
            return [];
        }

        // Basic WP user object fields
        $data = [
            'ID'              => $user->ID,
            'username'        => $user->user_login,
            'email'           => $user->user_email,
            'user_email'      => $user->user_email,
            'name'            => $user->display_name,
            'first_name'      => $user->first_name,
            'last_name'       => $user->last_name,
            'role'            => implode(', ', $user->roles),
            'user_registered' => $user->user_registered,
        ];

        // OPTIMIZED: Fetch only the specific meta keys actually used across templates.
        // Previously used get_user_meta($user_id) with no key — which loaded ALL meta rows
        // (potentially 80-150 DB rows) even though only ~16 fields are needed.
        $meta_keys = [
            'prov_username',
            'prov_mname',
            'prov_email',
            'prov_phone',
            'prov_address',
            'dob',
            'postal_code',
            'description',
            'gender',
            'age_group',
            'profile_image',
            'introduction_video',
            'video_status',
            'video_uploaded_on',
            'cosy_provider_holidays',
            'cosy_provider_services',
        ];

        foreach ($meta_keys as $key) {
            $data[$key] = get_user_meta($user_id, $key, true);
        }

        return $data;
    }



    /**
     * Retrieves a list of Service IDs that the logged-in provider has authored.
     * Returns an array of integers representing service Post IDs.
     */
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


    /**
     * Retrieves all published services across the entire platform.
     * Returns an array containing service titles, descriptions, pricing, and provider names.
     */
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

    /**
     * get_all_service_providers
     * 
     * This function retrieves a list of all providers.
     * 1. It can filter providers by category (e.g., if the URL is /service-provider/kids/).
     * 2. It fetches the provider's specific price for that service from the custom database table.
     * 3. It returns an array with all profile details like Bio, Photo, and Price.
     */
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

    /**
     * get_provider_with_services
     * 
     * Fetches complete profile data for a single provider using their URL slug.
     * It also fetches all the services that this provider has currently active ('yes' status).
     * Used mainly for the public-facing 'Provider Profile' page.
     */
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

    /**
     * get_provider_reviews
     * 
     * Retrieves all reviews (or optionally only approved ones) for a specific provider
     * and calculates essential review metrics like rating counts and average ratings.
     * 
     * @param int $provider_id The provider's user ID.
     * @param bool $approved_only Whether to only fetch approved reviews.
     * @return array Contains 'all', 'approved', 'total_approved', 'average_rating', and 'rating_counts'.
     */
    public function get_provider_reviews(int $provider_id, bool $approved_only = false): array
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cosy_provider_reviews';

        if ($approved_only) {
            $reviews = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM $table_name WHERE provider_id = %d AND status = 'approved' ORDER BY created_at DESC",
                    $provider_id
                ),
                ARRAY_A
            );
        } else {
            $reviews = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM $table_name WHERE provider_id = %d ORDER BY created_at DESC",
                    $provider_id
                ),
                ARRAY_A
            );
        }

        // Calculate metrics based on APPROVED reviews
        $approved_reviews = array_filter($reviews, function($r) {
            return $r['status'] === 'approved';
        });

        $total_approved = count($approved_reviews);
        $average_rating = 0;
        $rating_counts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

        if ($total_approved > 0) {
            $sum_ratings = 0;
            foreach ($approved_reviews as $r) {
                $sum_ratings += intval($r['rating']);
                $rating_counts[intval($r['rating'])]++;
            }
            $average_rating = round($sum_ratings / $total_approved, 1);
        }

        return [
            'all'            => $reviews,
            'approved'       => array_values($approved_reviews),
            'total_approved' => $total_approved,
            'average_rating' => $average_rating,
            'rating_counts'  => $rating_counts,
        ];
    }

    /**
     * get_provider_availability_data
     * 
     * Retrieves the weekly availability schedule and holiday dates for a provider.
     * 
     * @param int $provider_id The provider's user ID.
     * @return array Contains 'availability' array and 'holiday_dates' array.
     */
    public function get_provider_availability_data(int $provider_id): array
    {
        $days_of_week = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $availability = [];
        $holiday_dates = [];

        if (!empty($provider_id)) {
            foreach ($days_of_week as $day) {
                // Fetch saved metadata for each specific day
                $day_data = get_user_meta($provider_id, "cosy_availability_{$day}", true);
                $availability[$day] = !empty($day_data) ? $day_data : null;
            }

            // Fetch Holidays
            $raw_holidays = get_user_meta($provider_id, 'cosy_provider_holidays', true);
            $holidays_arr = (!empty($raw_holidays)) ? json_decode($raw_holidays, true) : [];
            if (is_array($holidays_arr)) {
                foreach ($holidays_arr as $h) {
                    if (!empty($h['date'])) {
                        $holiday_dates[] = $h['date'];
                    }
                }
            }
        }

        return [
            'availability'  => $availability,
            'holiday_dates' => $holiday_dates,
        ];
    }
}
