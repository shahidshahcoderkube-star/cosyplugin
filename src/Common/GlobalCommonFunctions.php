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
        } else {
            // No specific service selected. Fetch the minimum price for each provider as a default display price.
            $table_name = $wpdb->prefix . 'provider_services';
            $service_results = $wpdb->get_results(
                "SELECT provider_id, MIN(price) as price FROM $table_name WHERE checkbox_status = 'yes' GROUP BY provider_id",
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

        // Provider status must be active (or NOT EXISTS)
        $args['meta_query'][] = [
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
            $args['include'] = array_unique($include_users);
        } elseif (!empty($service_slug)) {
            // Service was specified but no providers found
            return [];
        }

        $service_providers = get_users($args);

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
            
            // Calculate average rating
            $rating_data = $this->get_provider_reviews($user_id, true);
            $avg_rating = isset($rating_data['average_rating']) ? floatval($rating_data['average_rating']) : 0.0;

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
        $approved_reviews = array_filter($reviews, function ($r) {
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

        if ($role_type === 'provider') {
            $subject = __('Confirm Your Provider Account', 'cosy-appointments');
            $html_content = "
                <p>Hello <strong>" . esc_html($name) . "</strong>,</p>
                <p>Thank you for joining as a Service Provider! Please click the button below to verify your email address and activate your provider account:</p>
                <p style='text-align: center; margin: 30px 0;'>
                    <a href='" . esc_url($verify_url) . "' style='background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%); color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 50px; font-weight: 600; display: inline-block; box-shadow: 0 4px 12px rgba(164, 67, 144, 0.2);'>Verify & Activate Account</a>
                </p>
                <p style='font-size: 13px; color: #64748b; margin-top: 25px;'>If you're having trouble clicking the button, copy and paste the link below into your web browser:</p>
                <p style='font-size: 13px; word-break: break-all; color: #a44390;'><a href='" . esc_url($verify_url) . "' style='color: #a44390; text-decoration: none;'>" . esc_html($verify_url) . "</a></p>
            ";
        } else {
            $subject = __('Confirm Your Customer Account', 'cosy-appointments');
            $html_content = "
                <p>Hello <strong>" . esc_html($name) . "</strong>,</p>
                <p>Thank you for registering a customer account with us! Please click the button below to verify your email address and activate your account:</p>
                <p style='text-align: center; margin: 30px 0;'>
                    <a href='" . esc_url($verify_url) . "' style='background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%); color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 50px; font-weight: 600; display: inline-block; box-shadow: 0 4px 12px rgba(164, 67, 144, 0.2);'>Verify & Activate Account</a>
                </p>
                <p style='font-size: 13px; color: #64748b; margin-top: 25px;'>If you're having trouble clicking the button, copy and paste the link below into your web browser:</p>
                <p style='font-size: 13px; word-break: break-all; color: #a44390;'><a href='" . esc_url($verify_url) . "' style='color: #a44390; text-decoration: none;'>" . esc_html($verify_url) . "</a></p>
            ";
        }

        return (bool) cosy_send_html_email($email, $subject, __('Confirm Your Account', 'cosy-appointments'), $html_content);
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
}
