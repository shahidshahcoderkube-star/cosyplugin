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
     * REGISTERS AJAX HANDLERS DYNAMICALLY
     * 
     * USE CASE:
     * Used across controllers to register arrays of AJAX hooks for logged-in and guest users without duplicate code.
     * 
     * HOW TO USE:
     * $this->register_ajax_handlers(['action_name' => 'method_name'], $this);
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Iterates over $actions array mapping.
     * 2. Calls add_action() for 'wp_ajax_$action' and 'wp_ajax_nopriv_$action'.
     * 
     * @param array  $actions  Associative array of action_name => method_name.
     * @param object $instance Controller class instance.
     */
    protected function register_ajax_handlers(array $actions, $instance)
    {
        foreach ($actions as $action => $method) {
            add_action("wp_ajax_$action", [$instance, $method]);
            add_action("wp_ajax_nopriv_$action", [$instance, $method]);
        }
    }

    /**
     * VERIFIES AJAX SECURITY, NONCE & USER ROLE
     * 
     * USE CASE:
     * Called at the start of AJAX handlers to enforce nonce security, login check, and role authorization in one clean step.
     * 
     * HOW TO USE:
     * $user_id = $this->verify_ajax_request('cosy_nonce', 'nonce', 'provider');
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Validates nonce token via check_ajax_referer().
     * 2. Checks current user capabilities and required role.
     * 3. Sends JSON error if unauthorized or non-logged-in.
     * 4. Returns validated WP User ID.
     * 
     * @param string $nonce_action  Nonce action name.
     * @param string $nonce_field   POST field name containing nonce (default: 'nonce').
     * @param string $required_role Required user role (default: 'provider').
     * @return int                  Validated user ID.
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

        // Single Source of Truth: Ensure prov_username and prov_email stay in 100% sync with core user account
        $data['prov_username'] = $user->user_login;
        $data['prov_email']    = $user->user_email;

        $data['video_status'] = $this->get_provider_video_status($user_id);

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
    public function get_all_service_providers(array $filters = []): array
    {
        // 0. PERFORMANCE OPTIMIZATION: Transient Cache Check (< 5ms response for repeat visits)
        $cache_key   = 'cosy_prov_list_' . md5(json_encode($filters));
        $cached_data = get_transient($cache_key);
        if ($cached_data !== false && is_array($cached_data)) {
            return $cached_data;
        }

        global $wpdb;

        // Get service slug from URL or filters
        $service_slug = '';
        if (!empty($filters['service_category'])) {
            $service_slug = sanitize_text_field($filters['service_category']);
        } else {
            // Get service slug from URL (e.g. "kids" from /service-provider/kids/)
            $uri_path = '';
            if (wp_doing_ajax()) {
                $referer = wp_get_referer();
                if ($referer) {
                    $uri_path = parse_url($referer, PHP_URL_PATH);
                }
            } else {
                $uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            }

            if (!empty($uri_path)) {
                $current_url = trim($uri_path, '/');
                $url_segments = explode('/', $current_url);
                $service_slug = end($url_segments);

                // Ignore 'service-provider' so it fetches ALL providers when no specific service is given in the URL.
                if ($service_slug === 'service-provider') {
                    $service_slug = '';
                }
            }
        }

        $price_order_sql = '';
        if (!empty($filters['price_range'])) {
            $direction = ($filters['price_range'] === 'low_high') ? 'ASC' : 'DESC';
            $price_order_sql = " ORDER BY price $direction";
        }

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

                // Find providers who selected this service and their prices in ONE SQL query (Sorted at DB level)
                $table_name = $wpdb->prefix . 'provider_services';
                $service_results = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT provider_id, price FROM $table_name WHERE service_id = %d AND checkbox_status = 'yes'" . $price_order_sql,
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
        } else {
            // No specific service selected. Fetch the minimum price for each provider as a default display price (Sorted at DB level).
            $table_name = $wpdb->prefix . 'provider_services';
            $service_results = $wpdb->get_results(
                "SELECT provider_id, MIN(price) as price FROM $table_name WHERE checkbox_status = 'yes' GROUP BY provider_id" . $price_order_sql,
                OBJECT_K
            );
            if (!empty($service_results)) {
                $provider_prices = $service_results;
                $include_users = array_map('intval', array_keys($provider_prices));
            } else {
                return []; // No providers have set up services!
            }
        }

        $args = [
            'role' => 'provider',
            'number' => -1,
            'order' => 'DESC',
            'meta_query' => [
                'relation' => 'AND',
            ]
        ];

        // Search filtering at SQL Query Level
        if (!empty($filters['search_name'])) {
            $search_val = sanitize_text_field($filters['search_name']);
            $args['search'] = '*' . $search_val . '*';
            $args['search_columns'] = ['display_name', 'user_login', 'user_nicename', 'user_email'];
        }

        // Provider status must be strictly active
        $args['meta_query'][] = [
            'key'     => 'cosy_provider_status',
            'value'   => 'active',
            'compare' => '='
        ];

        // Account status must also be active if set
        $args['meta_query'][] = [
            'relation' => 'OR',
            [
                'key'     => 'account_status',
                'value'   => 'active',
                'compare' => '='
            ],
            [
                'key'     => 'account_status',
                'compare' => 'NOT EXISTS'
            ]
        ];

        // Filter by gender
        if (!empty($filters['gender'])) {
            $args['meta_query'][] = [
                'key' => 'gender',
                'value' => sanitize_text_field($filters['gender']),
                'compare' => '='
            ];
        }

        // Filter by age group
        if (!empty($filters['age_group'])) {
            $args['meta_query'][] = [
                'key' => 'age_group',
                'value' => sanitize_text_field($filters['age_group']),
                'compare' => '='
            ];
        }

        if (!empty($include_users)) {
            $args['include'] = array_values($include_users);
            if (!empty($filters['price_range'])) {
                $args['orderby'] = 'include';
            }
        } elseif (!empty($service_slug)) {
            // Service was specified but no providers found
            return [];
        }

        $service_providers = get_users($args);

        if (empty($service_providers)) {
            return [];
        }

        // 1. PERFORMANCE OPTIMIZATION: Pre-fetch all user metadata in 1 single database query
        // This prevents the N+1 query problem by filling WP object cache upfront.
        $user_ids = wp_list_pluck($service_providers, 'ID');
        update_meta_cache('user', $user_ids);

        // 2. PERFORMANCE OPTIMIZATION: Batch fetch average ratings for all providers in 1 single SQL query
        $table_reviews = $wpdb->prefix . 'cosy_provider_reviews';
        $user_ids_in   = implode(',', array_map('intval', $user_ids));
        $ratings_map   = [];

        if (!empty($user_ids_in)) {
            $rating_results = $wpdb->get_results(
                "SELECT provider_id, AVG(rating) as avg_rating FROM $table_reviews WHERE provider_id IN ($user_ids_in) AND status = 'approved' GROUP BY provider_id",
                OBJECT_K
            );
            if (!empty($rating_results)) {
                foreach ($rating_results as $pid => $r_obj) {
                    $ratings_map[$pid] = round(floatval($r_obj->avg_rating), 1);
                }
            }
        }

        $data = [];

        foreach ($service_providers as $provider) {
            $user_id = $provider->ID;

            // Profile Information Check: Must have filled basic profile details
            $has_profile_info = !empty(get_user_meta($user_id, 'first_name', true)) &&
                                !empty(get_user_meta($user_id, 'prov_phone', true)) &&
                                !empty(get_user_meta($user_id, 'dob', true)) &&
                                !empty(get_user_meta($user_id, 'gender', true)) &&
                                !empty(get_user_meta($user_id, 'age_group', true));
            if (!$has_profile_info) {
                continue; // Skip listing provider if profile information is incomplete
            }

            // Availability check: Provider must have set up availability (working hours) for at least one day
            $has_availability = false;
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            foreach ($days as $day) {
                $day_data = get_user_meta($user_id, "cosy_availability_{$day}", true);
                if (!empty($day_data) && !empty($day_data['start_time']) && !empty($day_data['end_time'])) {
                    $has_availability = true;
                    break;
                }
            }
            if (!$has_availability) {
                continue; // Skip listing provider if no availability is set up
            }
            
            // Read pre-cached average rating (0 DB queries executed here)
            $avg_rating = isset($ratings_map[$user_id]) ? $ratings_map[$user_id] : 0.0;

            // Filter by rating
            if (!empty($filters['rating'])) {
                $min_rating = floatval($filters['rating']);
                if ($avg_rating < $min_rating) {
                    continue; // Skip if rating is too low
                }
            }

            $data[] = [
                'ID' => $provider->ID,
                'username' => $provider->user_login,
                'email' => $provider->user_email,
                'name' => $provider->display_name,
                'role' => implode(', ', $provider->roles),
                // Custom user meta (retrieved instantly from in-memory cache)
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
                'rating' => $avg_rating
            ];

            
        }

        // Filter by Search Name in PHP
        if (!empty($filters['search_name'])) {
            $search_val = strtolower(sanitize_text_field($filters['search_name']));
            $data = array_filter($data, function ($item) use ($search_val) {
                $full_name = strtolower($item['first_name'] . ' ' . $item['last_name']);
                return (strpos(strtolower($item['name']), $search_val) !== false) || 
                       (strpos(strtolower($item['first_name']), $search_val) !== false) ||
                       (strpos(strtolower($item['last_name']), $search_val) !== false) ||
                       (strpos($full_name, $search_val) !== false);
            });
            $data = array_values($data); // Reindex array
        }

        // Sort by price if requested
        if (!empty($filters['price_range'])) {
            $price_order = $filters['price_range'];
            usort($data, function ($a, $b) use ($price_order) {
                $price_a = floatval($a['price']);
                $price_b = floatval($b['price']);
                if ($price_a == $price_b) {
                    return 0;
                }
                if ($price_order === 'low_high') {
                    return ($price_a < $price_b) ? -1 : 1;
                } else {
                    return ($price_a > $price_b) ? -1 : 1;
                }
            });
        }

        // Save query results in Transient Cache for 1 hour
        set_transient($cache_key, $data, HOUR_IN_SECONDS);

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
        $replies_table = $wpdb->prefix . 'cosy_review_replies';

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

        // Attach multi-level thread replies to each review
        foreach ($reviews as &$r) {
            $r_replies = $wpdb->get_results(
                $wpdb->prepare("SELECT * FROM $replies_table WHERE review_id = %d ORDER BY reply_level ASC, created_at ASC", $r['id']),
                ARRAY_A
            );

            // Backward compatibility: If level 1 is missing in replies table, prepend legacy provider_reply
            $has_l1_entry = false;
            foreach ($r_replies as $rep_item) {
                if (intval($rep_item['reply_level']) === 1) {
                    $has_l1_entry = true;
                    break;
                }
            }
            if (!$has_l1_entry && !empty($r['provider_reply'])) {
                $prov_user = get_userdata($provider_id);
                $prov_name = $prov_user ? ($prov_user->first_name ?: $prov_user->display_name) : 'Provider';
                array_unshift($r_replies, [
                    'id'          => 0,
                    'review_id'   => $r['id'],
                    'sender_id'   => $provider_id,
                    'sender_role' => 'provider',
                    'sender_name' => $prov_name,
                    'reply_text'  => $r['provider_reply'],
                    'reply_level' => 1,
                    'created_at'  => $r['reply_date'] ?: $r['created_at']
                ]);
            }
            $r['replies'] = $r_replies ?: [];
        }
        unset($r);

        // Calculate metrics based on APPROVED reviews
        $approved_reviews = array_filter($reviews, function ($r) {
            return $r['status'] === 'approved';
        });

        $total_approved = count($approved_reviews);
        $average_rating = 0;
        $rating_counts = [10 => 0, 9 => 0, 8 => 0, 7 => 0, 6 => 0, 5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

        if ($total_approved > 0) {
            $sum_ratings = 0;
            foreach ($approved_reviews as $r) {
                $score = max(1, min(10, intval($r['rating'])));
                $sum_ratings += $score;
                if (isset($rating_counts[$score])) {
                    $rating_counts[$score]++;
                }
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
        $holiday_reasons = [];

        if (!empty($provider_id)) {
            foreach ($days_of_week as $day) {
                // Fetch saved metadata for each specific day
                $day_data = get_user_meta($provider_id, "cosy_availability_{$day}", true);
                $availability[$day] = !empty($day_data) ? $day_data : null;
            }

            // Fetch Holidays
            $raw_holidays = get_user_meta($provider_id, 'cosy_provider_holidays', true);
            $holidays_arr = [];

            if (is_array($raw_holidays)) {
                $holidays_arr = $raw_holidays;
            } elseif (is_string($raw_holidays) && !empty($raw_holidays)) {
                $raw_clean = stripslashes($raw_holidays);
                $decoded   = json_decode($raw_clean, true);
                if (!is_array($decoded)) {
                    $decoded = json_decode($raw_holidays, true);
                }
                if (is_array($decoded)) {
                    $holidays_arr = $decoded;
                }
            }

            if (is_array($holidays_arr)) {
                foreach ($holidays_arr as $h) {
                    $h_date   = is_array($h) ? ($h['date'] ?? '') : $h;
                    $h_reason = is_array($h) ? ($h['reason'] ?? 'Holiday') : 'Holiday';
                    if (!empty($h_date)) {
                        $ts = strtotime($h_date);
                        $formatted_date = $ts ? date('Y-m-d', $ts) : trim(sanitize_text_field($h_date));
                        $holiday_dates[] = $formatted_date;
                        $holiday_reasons[$formatted_date] = sanitize_text_field($h_reason ?: 'Holiday');
                    }
                }
            }
        }

        // Remove duplicates and ensure clean array values
        $holiday_dates = array_values(array_unique(array_filter($holiday_dates)));

        return [
            'availability'    => $availability,
            'holiday_dates'   => $holiday_dates,
            'holiday_reasons' => $holiday_reasons,
        ];
    }

    /**
     * Sends/resends a verification email to a user.
     * Generates a verification token if one doesn't exist.
     *
     * @param int    $user_id   The WordPress user ID.
     * @param string $role_type The user role ('provider' or 'customer').
     * @return bool             Whether the email was sent successfully.
     */
    public function send_verification_email(int $user_id, string $role_type): bool
    {
        $user = get_userdata($user_id);
        if (!$user) {
            return false;
        }

        $email = $user->user_email;
        $name  = $user->display_name ?: $user->user_login;

        // Generate or retrieve verification token
        $token = get_user_meta($user_id, 'verification_token', true);
        if (empty($token)) {
            $token = wp_generate_password(32, false);
            update_user_meta($user_id, 'verification_token', $token);
        }

        // Build verification link
        $verify_url = add_query_arg([
            'action' => 'cosy_verify_provider',
            'uid'    => $user_id,
            'token'  => $token,
        ], home_url('/provider-verify'));

        $template_name = ($role_type === 'provider') ? 'provider_verification' : 'customer_verification';
        return EmailTemplates::send($template_name, $email, [
            'name'       => $name,
            'verify_url' => $verify_url,
        ]);
    }

    /**
     * get_provider_video_status
     * 
     * Retrieves the video approval status from the custom table to ensure it is 
     * always in sync, and automatically cleans up/updates the user meta.
     * 
     * @param int $user_id
     * @return string
     */
    public function get_provider_video_status(int $user_id): string
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cosy_media_approvals';
        
        // If table doesn't exist, fallback to user meta
        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) !== $table_name) {
            return (string) get_user_meta($user_id, 'video_status', true);
        }

        $db_status = $wpdb->get_var(
            $wpdb->prepare("SELECT status FROM $table_name WHERE user_id = %d ORDER BY id DESC LIMIT 1", $user_id)
        );

        $status = $db_status ?: '';

        if (empty($status)) {
            delete_user_meta($user_id, 'introduction_video');
            delete_user_meta($user_id, 'video_status');
        } else {
            $meta_status = get_user_meta($user_id, 'video_status', true);
            if ($meta_status !== $status) {
                update_user_meta($user_id, 'video_status', $status);
            }
        }

        return $status;
    }

    /**
     * Deletes a media file (attachment) from the WordPress Media Library
     * using its URL. Resolves potential domain/path mismatches.
     *
     * @param string $url The URL of the attachment to delete.
     * @return bool True if deleted, false otherwise.
     */
    public function delete_media_file_by_url(string $url): bool
    {
        if (empty($url)) {
            return false;
        }

        // 1. Try default WordPress function first
        $attachment_id = attachment_url_to_postid($url);
        
        // 2. Fallback query if attachment_url_to_postid fails
        if (!$attachment_id) {
            global $wpdb;
            $attachment_id = $wpdb->get_var($wpdb->prepare(
                "SELECT ID FROM $wpdb->posts WHERE guid = %s AND post_type = 'attachment'",
                $url
            ));
        }

        // 3. Fallback: query by relative upload path
        if (!$attachment_id) {
            $uploads = wp_upload_dir();
            $base_url = $uploads['baseurl'];
            if (strpos($url, $base_url) !== false) {
                $relative_path = str_replace($base_url . '/', '', $url);
                global $wpdb;
                $attachment_id = $wpdb->get_var($wpdb->prepare(
                    "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value = %s",
                    $relative_path
                ));
            }
        }

        if ($attachment_id) {
            return (bool) wp_delete_attachment($attachment_id, true);
        }

        return false;
    }

    /**
     * Clear all provider directory transient caches.
     * 
     * USE CASE:
     * Triggered automatically whenever a provider updates their profile info,
     * working hours availability, holiday dates, or when a customer review is approved/deleted.
     * 
     * WHY IT IS NEEDED:
     * Ensures that stale cached directory listings are flushed immediately so frontend visitors
     * always see the latest active provider profile information.
     */
    public function cosy_clear_provider_transients(): void
    {
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_cosy_prov_list_%' OR option_name LIKE '_transient_timeout_cosy_prov_list_%'");
    }

    /**
     * STANDARDIZED ADMIN AJAX VERIFICATION HELPER
     * 
     * USE CASE:
     * Call this single function at the beginning of any backend admin AJAX handler
     * (e.g. approving videos, updating user status, deleting orders, clearing activity logs).
     * 
     * WHAT IT DOES:
     * 1. Verifies that the user is currently logged into WordPress.
     * 2. Auto-detects and validates the security Nonce token ('nonce' or 'security' POST key).
     * 3. Verifies that the user possesses the required administrative capability permission.
     * 4. Sends a standardized JSON error response if any check fails, or returns the User ID.
     * 
     * @param string $nonce_action Nonce action name (default: 'cosy_admin_nonce').
     * @param string $capability   Required WP capability (default: 'manage_cosy_appointments').
     * @param string $nonce_param  Expected POST query parameter key (default: 'nonce').
     * @return int                 Validated current user ID.
     */
    public function verify_admin_ajax_request(string $nonce_action = 'cosy_admin_nonce', string $capability = 'manage_cosy_appointments', string $nonce_param = 'nonce'): int
    {
        // 1. Verify user authentication status
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Unauthorized access. Please log in.', 'cosy-appointments')]);
        }

        // 2. Auto-detect parameter key if 'nonce' is not explicitly set in $_REQUEST
        if (!isset($_REQUEST[$nonce_param]) && isset($_REQUEST['security'])) {
            $nonce_param = 'security';
        }

        // 3. Verify security CSRF Nonce token
        if (!check_ajax_referer($nonce_action, $nonce_param, false)) {
            wp_send_json_error(['message' => __('Invalid security token. Please refresh the page.', 'cosy-appointments')]);
        }

        // 4. Verify user administrative capabilities
        if (!current_user_can($capability) && !current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('You do not have permission to perform this action.', 'cosy-appointments')]);
        }

        return get_current_user_id();
    }

    /**
     * Flushes all cached provider list transients to instantly reflect status changes on the frontend.
     */
    public function flush_provider_transients(): void
    {
        global $wpdb;
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_cosy_prov_list_%' 
                OR option_name LIKE '_transient_timeout_cosy_prov_list_%'"
        );

        if (class_exists('\Cosy\Appointments\AI\ProfileIndexer')) {
            \Cosy\Appointments\AI\ProfileIndexer::clear_search_cache();
        }
    }
}
