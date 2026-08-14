<?php

namespace Cosy\Appointments\Frontend;

use Cosy\Appointments\Forms\FormsData;
use Cosy\Appointments\Loader;
use Cosy\Appointments\Common\GlobalCommonFunctions;

/**
 * Class Dashboard
 * 
 * This class handles all backend actions for the Provider Dashboard.
 * It includes profile updates, video uploads, tab loading, and availability settings.
 */
class Dashboard
{
    /**
     * Includes helper methods that are shared across multiple classes.
     */
    use GlobalCommonFunctions;

    /**
     * CONSTRUCTS PROVIDER DASHBOARD CONTROLLER & AJAX ENDPOINTS
     * 
     * USE CASE:
     * Instantiated during plugin initialization to register AJAX handlers for provider profile updates, availability, video uploads, and reviews.
     * 
     * HOW TO USE:
     * (new Dashboard());
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Defines actions array mapping AJAX hooks to class methods.
     * 2. Registers all AJAX handlers via register_ajax_handlers() helper.
     */
    public function __construct()
    {
        // List of AJAX actions and their corresponding functions
        $actions = [
            'cosy_provider_information_update' => 'handle_profile_update',   // Updates profile info
            'cosy_provider_video'              => 'handle_video_upload',     // Handles video upload
            'delete_video'                     => 'ajax_delete_video',       // Deletes provider video
            'load_dashboard_tab'               => 'cosy_load_dashboard_tab', // Loads tabs via AJAX
            'save_provider_availability'       => 'handle_availability_save', // Saves working hours
            'delete_provider_availability_day' => 'handle_delete_availability_day', // Deletes working hours for a day
            'cosy_add_holiday'                 => 'handle_add_holiday',      // Adds a non-working day
            'cosy_delete_holiday'              => 'handle_delete_holiday',   // Deletes a non-working day
            'cosy_add_provider_review'         => 'handle_add_review',
            'cosy_approve_provider_review'     => 'handle_approve_review',
            'cosy_delete_provider_review'      => 'handle_delete_review',
            'cosy_provider_reply_review'       => 'handle_provider_reply_review',
            'cosy_customer_reply_review'       => 'handle_customer_reply_review',
            'cosy_dismiss_audit_alerts'        => 'handle_dismiss_audit_alerts',
            'cosy_check_profile_completeness'  => 'handle_check_profile_completeness', // Checks profile completion dynamically
            'cosy_submit_token_review'         => 'handle_submit_token_review',
        ];

        // Register all AJAX handlers dynamically
        $this->register_ajax_handlers($actions, $this);
    }

    /**
     * REGISTERS DASHBOARD SHORTCODE HOOK
     * 
     * USE CASE:
     * Called during plugin loader initialization to register provider dashboard shortcode.
     * 
     * HOW TO USE:
     * (new Dashboard())->register($loader);
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Attaches 'register_dashboard_shortcode' callback to WordPress 'init' hook.
     * 
     * @param Loader $loader Plugin loader instance.
     */
    public function register(Loader $loader): void
    {
        /* Registering the shortcode for provider dashboard */
        $loader->add_action('init', $this, 'register_dashboard_shortcode');
    }

    /**
     * Registers the [cosy_provider_dashboard] shortcode with WordPress.
     */
    public function register_dashboard_shortcode(): void
    {
        add_shortcode('cosy_provider_dashboard', [$this, 'provider_dashboard_shortcode']);
    }

    /**
     * Renders the HTML content for the Provider Dashboard when the shortcode is used.
     * It loads the dashboard.php template file.
     */
    public function provider_dashboard_shortcode(): void
    {
        ob_start();
        include COSY_APPT_PATH . 'templates/provider/dashboard.php';
        echo ob_get_clean();
    }

    /**
     * Fetch all appointments for a given provider ID
     * Centralized OOP method to avoid raw WP_Query calls in templates
     */
    public static function get_provider_appointments(int $provider_id): array
    {
        $args = [
            'post_type'      => 'cosy_appointment',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => [
                [
                    'key'     => 'cosy_provider_id',
                    'value'   => $provider_id,
                    'compare' => '='
                ]
            ],
            'orderby'        => 'date',
            'order'          => 'DESC'
        ];

        return (new \WP_Query($args))->posts;
    }

    /**
     * handle_profile_update
     * 
     * This function saves the provider's profile information (Name, Email, Bio, etc.)
     * Data comes from the profile form via POST request.
     * It saves the data into WordPress User Meta.
     */
    public function handle_profile_update(): void
    {
        $user_id = $this->verify_ajax_request('cosy_dashboard_nonce');

        if (isset($_POST['update_provider_profile']) || !isset($_POST['action']) || $_POST['action'] !== 'cosy_customer_register') {

            $provider_meta = [
                'prov_username' => sanitize_text_field($_POST['prov_username']),
                // 'prov_fname'    => sanitize_text_field($_POST['prov_fname']),
                // 'prov_mname'    => sanitize_text_field($_POST['prov_mname']),
                'first_name' => sanitize_text_field($_POST['prov_fname']),
                'prov_mname' => sanitize_text_field($_POST['prov_mname']),
                'last_name' => sanitize_text_field($_POST['prov_sname']),
                'prov_email' => sanitize_email($_POST['prov_email']),
                'prov_phone' => sanitize_text_field($_POST['prov_phone']),
                'prov_address' => sanitize_textarea_field($_POST['prov_address']),
                'dob' => sanitize_text_field($_POST['dob']),
                'postal_code' => sanitize_text_field($_POST['postal_code']),
                'description' => sanitize_textarea_field($_POST['bio']),
                'gender' => sanitize_text_field($_POST['gender']),
                'age_group' => sanitize_text_field($_POST['age_group']),
            ];

            foreach ($provider_meta as $key => $value) {
                update_user_meta($user_id, $key, $value);
            }

            // Handle profile image upload correctly

            if (!empty($_FILES['profile_image']['name'])) {
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                require_once(ABSPATH . 'wp-admin/includes/media.php');
                require_once(ABSPATH . 'wp-admin/includes/image.php');

                $attachment_id = media_handle_upload('profile_image', 0);
                if (!is_wp_error($attachment_id)) {
                    $image_url = wp_get_attachment_url($attachment_id);
                    update_user_meta($user_id, 'profile_image', $image_url);
                }
            }

            // Log profile update
            $prov_user = get_userdata($user_id);
            \Cosy\Appointments\Common\LogManager::log(
                'dashboard',
                'profile_updated',
                sprintf(__('Provider "%s" updated their profile information.', 'cosy-appointments'), $prov_user ? $prov_user->display_name : 'Provider'),
                $user_id
            );

            // Send notification email to Administrator if provider setup/profile is updated
            if (function_exists('cosy_notify_admin_provider_setup_ready')) {
                cosy_notify_admin_provider_setup_ready($user_id);
            }

            // Flush provider directory transient cache on profile update
            $this->cosy_clear_provider_transients();

            $forms = new FormsData();
            $forms->send_response(true, ['message' => 'Profile updated successfully!', 'data' => $provider_meta]);
        }
    }

    /**
     * handle_video_upload
     * 
     * This function handles the introduction video upload for providers.
     * 1. Checks for file size (max 2MB).
     * 2. Uploads the file to the WordPress Media Library.
     * 3. Saves the video URL and status ('pending') to User Meta.
     * 4. Updates the 'cosy_media_approvals' table for admin review.
     */
    public function handle_video_upload(): void
    {
        $user_id = $this->verify_ajax_request('cosy_dashboard_nonce');

        // Check if user already has a pending video
        $current_status = get_user_meta($user_id, 'video_status', true);
        if ($current_status === 'pending') {
            wp_send_json_error(['message' => 'Your previous video approval is still pending. You cannot upload a new video until it is reviewed.']);
        }

        if (!empty($_FILES['video_upload']['name'])) {
            // Size check (Dynamic limit)
            $limit_mb = intval(get_option('cosy_max_video_upload_size', 3));
            if ($limit_mb <= 0) {
                $limit_mb = 3;
            }
            $max_size = $limit_mb * 1024 * 1024;
            if ($_FILES['video_upload']['size'] > $max_size) {
                wp_send_json_error(['message' => sprintf(__('Video size must not exceed %d MB', 'cosy-appointments'), $limit_mb)]);
            }


            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');

            $attachment_id = media_handle_upload('video_upload', 0);
            if (!is_wp_error($attachment_id)) {
                $video_url = wp_get_attachment_url($attachment_id);

                // Save video URL
                update_user_meta($user_id, 'introduction_video', $video_url);

                // Save current date/time
                update_user_meta($user_id, 'video_uploaded_on', current_time('mysql'));

                // Save status as pending
                update_user_meta($user_id, 'video_status', 'pending');

                // Sync with custom table for Admin Dashboard - Update existing or insert new to avoid duplicate rows per provider
                global $wpdb;
                $table_name = $wpdb->prefix . 'cosy_media_approvals';

                $exists = $wpdb->get_var(
                    $wpdb->prepare("SELECT id FROM $table_name WHERE user_id = %d", $user_id)
                );

                if ($exists) {
                    $wpdb->update(
                        $table_name,
                        [
                            'media_url'   => $video_url,
                            'status'      => 'pending',
                            'uploaded_at' => current_time('mysql'),
                            'reviewed_at' => null,
                        ],
                        ['user_id' => $user_id],
                        ['%s', '%s', '%s', null],
                        ['%d']
                    );
                } else {
                    $wpdb->insert(
                        $table_name,
                        [
                            'user_id'     => $user_id,
                            'media_url'   => $video_url,
                            'status'      => 'pending',
                            'uploaded_at' => current_time('mysql'),
                        ],
                        ['%d', '%s', '%s', '%s']
                    );
                }

                // Log video upload
                \Cosy\Appointments\Common\LogManager::log(
                    'dashboard',
                    'video_uploaded',
                    __('Provider uploaded a new video for admin approval.', 'cosy-appointments'),
                    $user_id
                );

                wp_send_json_success([
                    'message' => 'Video uploaded successfully! Awaiting admin approval.',
                    'video_url' => $video_url,
                    'uploaded_on' => current_time('mysql'),
                    'status' => 'pending'
                ]);
            } else {
                wp_send_json_error([
                    'message' => 'Video upload failed: ' . $attachment_id->get_error_message()
                ]);
            }
        } else {
            wp_send_json_error(['message' => 'No video file uploaded']);
        }
    }

    /**
     * AJAX Handler: Deletes the provider's introductory video.
     * Removes the video file from the media library and updates the provider's meta data.
     */
    public function ajax_delete_video()
    {
        $this->verify_ajax_request('cosy_dashboard_nonce');

        $user_id = intval($_POST['user_id']);
        if (!$user_id) {
            wp_send_json_error([
                'message' => __('Invalid user ID', 'cosy-appointments')
            ]);
        }

        $video_url = get_user_meta($user_id, 'introduction_video', true);

        if ($video_url) {
            $this->delete_media_file_by_url($video_url);
            delete_user_meta($user_id, 'introduction_video');
        }

        update_user_meta($user_id, 'video_status', 'deleted');

        // Update the status of their latest video entry to 'deleted' in approvals table to keep logs
        global $wpdb;
        $table_name = $wpdb->prefix . 'cosy_media_approvals';
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE $table_name SET status = 'deleted' WHERE user_id = %d AND status IN ('pending', 'approved') ORDER BY id DESC LIMIT 1",
                $user_id
            )
        );

        // Log video deletion
        \Cosy\Appointments\Common\LogManager::log(
            'dashboard',
            'video_deleted',
            __('Provider deleted their introductory video.', 'cosy-appointments'),
            $user_id
        );

        wp_send_json_success([
            'message' => __('Video deleted successfully!', 'cosy-appointments')
        ]);
    }


    /**
     * cosy_load_dashboard_tab
     * 
     * This function loads the content of different dashboard tabs (Profile, Services, Availability, etc.)
     * using AJAX. This makes the dashboard feel faster because the whole page doesn't reload.
     */
    public function cosy_load_dashboard_tab()
    {
        $user_id = $this->verify_ajax_request('cosy_dashboard_nonce');


        if (empty($_POST['tab'])) {
            wp_send_json_error('Invalid tab');
        }

        $tab = sanitize_text_field($_POST['tab']);

        // allowed tabs (security)
        $allowed_tabs = [
            'profile' => 'profile-information.php',
            'video' => 'media-upload.php',
            'services' => 'services.php',
            'availability' => 'availability.php',
            'orders' => 'orders.php',
            'nonworking' => 'holidays.php',
            'reviews' => 'customer-reviews.php',
        ];

        if (!isset($allowed_tabs[$tab])) {
            wp_send_json_error('Tab not allowed');
        }

        $file_path = plugin_dir_path(__FILE__) . '../../templates/provider/dashboard/' . $allowed_tabs[$tab];

        if (!file_exists($file_path)) {
            wp_send_json_error('File not found');
        }

        $user_id = get_current_user_id();
        $days_list = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $availability = [];
        foreach ($days_list as $day) {
            $availability[$day] = get_user_meta($user_id, "cosy_availability_{$day}", true);
        }

        ob_start();
        include $file_path;
        $html = ob_get_clean();

        wp_send_json_success([
            'html' => $html
        ]);
    }
    /**
     * handle_add_holiday
     *
     * Saves a new non-working day for the provider.
     * Holidays are stored as a JSON array in user meta 'cosy_provider_holidays'.
     * Each holiday: { date: 'YYYY-MM-DD', reason: 'string' }
     */
    public function handle_add_holiday(): void
    {
        $user_id = $this->verify_ajax_request('cosy_dashboard_nonce');

        $date   = sanitize_text_field($_POST['holiday_date'] ?? '');
        $reason = sanitize_text_field($_POST['holiday_reason'] ?? '');

        if (empty($date)) {
            wp_send_json_error(['message' => 'Date is required.']);
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            wp_send_json_error(['message' => 'Invalid date format.']);
        }

        // Load existing holidays
        $raw = get_user_meta($user_id, 'cosy_provider_holidays', true);
        $holidays = [];
        if (is_array($raw)) {
            $holidays = $raw;
        } elseif (is_string($raw) && !empty($raw)) {
            $raw_clean = stripslashes($raw);
            $decoded   = json_decode($raw_clean, true);
            if (!is_array($decoded)) {
                $decoded = json_decode($raw, true);
            }
            if (is_array($decoded)) {
                $holidays = $decoded;
            }
        }

        // Standardize input date to YYYY-MM-DD
        $date_ts = strtotime($date);
        if ($date_ts) {
            $date = date('Y-m-d', $date_ts);
        }

        // Prevent duplicate dates
        foreach ($holidays as $h) {
            $h_date = is_array($h) ? ($h['date'] ?? '') : $h;
            $h_ts = strtotime($h_date);
            $h_formatted = $h_ts ? date('Y-m-d', $h_ts) : $h_date;
            if ($h_formatted === $date) {
                wp_send_json_error(['message' => 'This date is already marked as a holiday.']);
            }
        }

        // Add the new holiday
        $holidays[] = [
            'date'   => $date,
            'reason' => $reason ?: 'Holiday',
        ];

        // Sort by date ascending
        usort($holidays, function ($a, $b) {
            $da = is_array($a) ? ($a['date'] ?? '') : $a;
            $db = is_array($b) ? ($b['date'] ?? '') : $b;
            return strcmp($da, $db);
        });

        // Save back to user meta
        update_user_meta($user_id, 'cosy_provider_holidays', wp_json_encode($holidays));

        // Format date for display: 01 Jan 2026
        $display_date = date('d M Y', strtotime($date));

        // Log holiday addition
        \Cosy\Appointments\Common\LogManager::log(
            'dashboard',
            'holiday_added',
            sprintf(__('Provider added holiday: %s (Reason: %s).', 'cosy-appointments'), $display_date, $reason ?: __('None', 'cosy-appointments')),
            $user_id
        );

        // Flush provider directory transient cache on holiday update
        $this->cosy_clear_provider_transients();

        wp_send_json_success([
            'message'      => 'Holiday added successfully!',
            'date'         => $date,
            'display_date' => $display_date,
            'reason'       => $reason ?: 'Holiday',
        ]);
    }

    /**
     * handle_delete_holiday
     *
     * Removes a non-working day from the provider's holiday list by date.
     */
    public function handle_delete_holiday(): void
    {
        $user_id = $this->verify_ajax_request('cosy_dashboard_nonce');

        $date = sanitize_text_field($_POST['holiday_date'] ?? '');

        if (empty($date)) {
            wp_send_json_error(['message' => 'Date is required.']);
        }

        $date_ts = strtotime($date);
        if ($date_ts) {
            $date = date('Y-m-d', $date_ts);
        }

        // Load existing holidays
        $raw = get_user_meta($user_id, 'cosy_provider_holidays', true);
        $holidays = [];
        if (is_array($raw)) {
            $holidays = $raw;
        } elseif (is_string($raw) && !empty($raw)) {
            $raw_clean = stripslashes($raw);
            $decoded   = json_decode($raw_clean, true);
            if (!is_array($decoded)) {
                $decoded = json_decode($raw, true);
            }
            if (is_array($decoded)) {
                $holidays = $decoded;
            }
        }

        // Filter out the holiday with the given date
        $updated = array_values(array_filter($holidays, function ($h) use ($date) {
            $h_date = is_array($h) ? ($h['date'] ?? '') : $h;
            $h_ts = strtotime($h_date);
            $h_formatted = $h_ts ? date('Y-m-d', $h_ts) : $h_date;
            return $h_formatted !== $date;
        }));

        // Save updated list
        update_user_meta($user_id, 'cosy_provider_holidays', wp_json_encode($updated));

        // Log holiday deletion
        \Cosy\Appointments\Common\LogManager::log(
            'dashboard',
            'holiday_deleted',
            sprintf(__('Provider removed holiday scheduled for %s.', 'cosy-appointments'), $date),
            $user_id
        );

        // Flush provider directory transient cache on holiday deletion
        $this->cosy_clear_provider_transients();

        wp_send_json_success(['message' => 'Holiday removed successfully!', 'date' => $date]);
    }

    /**
     * handle_availability_save
     * 
     * This function saves the working hours for a specific day.
     * Data comes from the 'Availability' tab form.
     * It saves Start Time, End Time, Slot Duration, and Breaks into a specific User Meta key
     * for each day (e.g., 'cosy_availability_Monday').
     */
    public function handle_availability_save(): void
    {
        $user_id = $this->verify_ajax_request('cosy_dashboard_nonce');
        
        $days = isset($_POST['days']) && is_array($_POST['days']) ? array_map('sanitize_text_field', $_POST['days']) : [];
        if (empty($days)) {
            $day = sanitize_text_field($_POST['day']);
            $days = $day ? [$day] : [];
        }
        
        if (empty($days)) {
            wp_send_json_error('Day is required');
        }

        $start_time  = sanitize_text_field($_POST['start_time']);
        $end_time    = sanitize_text_field($_POST['end_time']);
        $break_start = sanitize_text_field($_POST['break_start']);
        $break_end   = sanitize_text_field($_POST['break_end']);

        // Smart Normalization: Convert 12-hour inputs (e.g. 06:00 for 6 PM, 01:00 for 1 PM) to 24-hour PM automatically
        $normalize_pm = function ($time_str, $ref_str) {
            if (empty($time_str)) return $time_str;
            $parts = explode(':', $time_str);
            if (count($parts) < 2) return $time_str;
            $h = intval($parts[0]);
            $m = $parts[1];

            $ref_h = 0;
            if (!empty($ref_str)) {
                $ref_parts = explode(':', $ref_str);
                $ref_h = intval($ref_parts[0]);
            }

            if ($h > 0 && $h <= 11 && $ref_h >= 1 && $h <= $ref_h) {
                $h += 12;
            }
            return sprintf('%02d:%s', $h, $m);
        };

        $end_time    = $normalize_pm($end_time, $start_time);
        $break_start = $normalize_pm($break_start, $start_time);
        $break_end   = $normalize_pm($break_end, $break_start ?: $start_time);

        $availability_data = [
            'start_time'    => $start_time,
            'end_time'      => $end_time,
            'slot_duration' => sanitize_text_field($_POST['slot_duration']),
            'break_start'   => $break_start,
            'break_end'     => $break_end,
        ];

        foreach ($days as $day) {
            // Save in user meta for the specific day
            update_user_meta($user_id, "cosy_availability_{$day}", $availability_data);
        }

        // Log availability save
        $days_string = implode(', ', $days);
        \Cosy\Appointments\Common\LogManager::log(
            'dashboard',
            'availability_saved',
            sprintf(__('Provider saved availability working hours for %s.', 'cosy-appointments'), $days_string),
            $user_id
        );

        // Send notification email to Administrator if provider setup/availability is updated
        if (function_exists('cosy_notify_admin_provider_setup_ready')) {
            cosy_notify_admin_provider_setup_ready($user_id);
        }

        // Flush provider directory transient cache on availability update
        $this->cosy_clear_provider_transients();

        wp_send_json_success('Availability for ' . $days_string . ' saved successfully.');
    }

    /**
     * AJAX Handler: Deletes availability working hours for a specific day.
     */
    public function handle_delete_availability_day(): void
    {
        $user_id = $this->verify_ajax_request('cosy_dashboard_nonce');
        $day = sanitize_text_field($_POST['day'] ?? '');

        if (!empty($day)) {
            delete_user_meta($user_id, "cosy_availability_{$day}");
            
            \Cosy\Appointments\Common\LogManager::log(
                'dashboard',
                'availability_deleted',
                sprintf(__('Provider removed availability working hours for %s.', 'cosy-appointments'), $day),
                $user_id
            );

            // Flush provider directory transient cache on availability update
            $this->cosy_clear_provider_transients();

            wp_send_json_success('Availability for ' . $day . ' removed successfully.');
        }

        wp_send_json_error('Invalid day parameter.');
    }
    /**
     * AJAX Handler: Adds a new review for a service provider.
     * 
     * Security & Business Logic:
     * 1. Validates that the user is logged in.
     * 2. RESTRICTS reviews strictly to logged-in users with the 'customer' role. 
     *    All other roles (e.g., service providers, authors, administrators) are rejected.
     * 3. Validates that provider_id, rating (1-5), and review text are present and valid.
     * 4. Saves the review with status='pending'. It will not appear publicly until approved.
     * 
     * Output:
     * - Returns JSON success if successfully saved.
     * - Returns JSON error with appropriate message on failure.
     */
    public function handle_add_review(): void
    {
        $this->verify_ajax_request('cosy_calendar_nonce', 'nonce', 'customer');
 
        $current_user = wp_get_current_user();
 
        // 3. Extract and sanitize inputs
        $provider_id = isset($_POST['provider_id']) ? intval($_POST['provider_id']) : 0;
        $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
        $review_text = isset($_POST['review']) ? sanitize_textarea_field($_POST['review']) : '';
 
        // 4. Input validation
        if (!$provider_id) {
            wp_send_json_error(['message' => 'Invalid Service Provider.']);
        }
 
        if ($rating < 1 || $rating > 10) {
            wp_send_json_error(['message' => 'Please select a rating (1 to 10).']);
        }

        if (empty($review_text)) {
            wp_send_json_error(['message' => 'Please write a review comment.']);
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'cosy_provider_reviews';
        $replies_table = $wpdb->prefix . 'cosy_review_replies';

        // Check if customer already has a review thread for this provider
        $existing_review = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE provider_id = %d AND customer_id = %d ORDER BY id ASC LIMIT 1",
            $provider_id,
            $current_user->ID
        ));

        if ($existing_review) {
            wp_send_json_error(['message' => __('You have already submitted a review for this parent. If your previous review is removed by Admin, you can post a new review.', 'cosy-appointments')]);
        }

        // 5. Insert pending review into custom DB table
        $inserted = $wpdb->insert(
            $table_name,
            [
                'provider_id'   => $provider_id,
                'customer_id'   => $current_user->ID,
                'customer_name' => $current_user->display_name,
                'rating'        => $rating,
                'review'        => $review_text,
                'status'        => 'pending', // Starts as pending, needs approval
                'created_at'    => current_time('mysql'),
            ],
            ['%d', '%d', '%s', '%d', '%s', '%s', '%s']
        );
 
        if ($inserted) {
            $new_review_id = $wpdb->insert_id;

            // Log activity in Reviews section
            \Cosy\Appointments\Common\LogManager::log(
                'reviews',
                'SUBMIT_REVIEW',
                sprintf(__('Customer "%s" submitted a new review (Rating: %d/10) for Provider #%d.', 'cosy-appointments'), $current_user->display_name, $rating, $provider_id),
                $current_user->ID
            );

            // Send Email notification to Administrator
            $admin_email = get_option('admin_email');
            $provider_user = get_userdata($provider_id);
            $provider_name = $provider_user ? $provider_user->display_name : 'Provider #' . $provider_id;

            if (!empty($admin_email) && function_exists('cosy_send_html_email')) {
                $tpl = \Cosy\Appointments\Common\EmailTemplates::get_admin_new_review_template(
                    $provider_name,
                    $current_user->display_name,
                    intval($rating),
                    $review_text
                );
                cosy_send_html_email($admin_email, $tpl['subject'], $tpl['heading'], $tpl['content']);
            }

            wp_send_json_success(['message' => 'Review submitted successfully! It will be displayed after approval.']);
        } else {
            wp_send_json_error(['message' => 'Failed to save review. Please try again.']);
        }
    }
 
    /**
     * AJAX Handler: Approves a pending review.
     * 
     * Security & Business Logic:
     * 1. Enforces logged-in user check.
     * 2. Enforces authorization check: Only administrators or the specific service provider 
     *    to whom this review was submitted can approve it.
     * 3. Service Providers can only update reviews targeted to their own provider ID.
     * 
     * Output:
     * - Returns JSON success on successful database status update.
     * - Returns JSON error on failure.
     */
    public function handle_approve_review(): void
    {
        $this->verify_ajax_request('cosy_dashboard_nonce');
 
        // Role scoping for DB query (provider can only approve their own reviews)
        $current_user = wp_get_current_user();
        $is_admin = current_user_can('manage_cosy_appointments');
        $is_provider = in_array('provider', (array) $current_user->roles);
 
        // 3. Extract and validate review ID
        $review_id = isset($_POST['review_id']) ? intval($_POST['review_id']) : 0;
 
        if (!$review_id) {
            wp_send_json_error(['message' => 'Invalid Review ID.']);
        }
 
        global $wpdb;
        $table_name = $wpdb->prefix . 'cosy_provider_reviews';
 
        // 4. Update status with secure scoping
        if ($is_provider && !$is_admin) {
            // Service provider can only approve reviews written for them
            $updated = $wpdb->update(
                $table_name,
                ['status' => 'approved'],
                ['id' => $review_id, 'provider_id' => $current_user->ID],
                ['%s'],
                ['%d', '%d']
            );
        } else {
            // Administrator can approve any review in the system
            $updated = $wpdb->update(
                $table_name,
                ['status' => 'approved'],
                ['id' => $review_id],
                ['%s'],
                ['%d']
            );
        }
 
        if ($updated !== false) {
            // Log review approval
            \Cosy\Appointments\Common\LogManager::log(
                'dashboard',
                'review_approved',
                sprintf(__('Review #%d approved.', 'cosy-appointments'), $review_id),
                $current_user->ID
            );

            // Flush provider directory transient cache on review approval
            $this->cosy_clear_provider_transients();

            wp_send_json_success(['message' => 'Review approved successfully.']);
        } else {
            wp_send_json_error(['message' => 'Failed to approve review.']);
        }
    }
 
    /**
     * AJAX Handler: Deletes or rejects a review.
     * 
     * Security & Business Logic:
     * 1. Enforces logged-in user check.
     * 2. Enforces authorization check: Only administrators or the specific service provider
     *    to whom this review was submitted can delete it.
     * 3. Service Providers can only delete reviews targeted to their own provider ID.
     * 
     * Output:
     * - Returns JSON success on successful database row deletion.
     * - Returns JSON error on failure.
     */
    public function handle_delete_review(): void
    {
        $this->verify_ajax_request('cosy_dashboard_nonce');
 
        // Role scoping for DB query (provider can only delete their own reviews)
        $current_user = wp_get_current_user();
        $is_admin = current_user_can('manage_cosy_appointments');
        $is_provider = in_array('provider', (array) $current_user->roles);
 
        // 3. Extract and validate review ID
        $review_id = isset($_POST['review_id']) ? intval($_POST['review_id']) : 0;
 
        if (!$review_id) {
            wp_send_json_error(['message' => 'Invalid Review ID.']);
        }
 
        global $wpdb;
        $table_name = $wpdb->prefix . 'cosy_provider_reviews';
 
        // 4. Delete review from database with secure scoping
        if ($is_provider && !$is_admin) {
            // Service provider can only delete reviews written for them
            $deleted = $wpdb->delete(
                $table_name,
                ['id' => $review_id, 'provider_id' => $current_user->ID],
                ['%d', '%d']
            );
        } else {
            // Administrator can delete any review in the system
            $deleted = $wpdb->delete(
                $table_name,
                ['id' => $review_id],
                ['%d']
            );
        }
 
        if ($deleted !== false) {
            // Log review deletion
            \Cosy\Appointments\Common\LogManager::log(
                'dashboard',
                'review_deleted',
                sprintf(__('Review #%d deleted.', 'cosy-appointments'), $review_id),
                $current_user->ID
            );

            wp_send_json_success(['message' => 'Review deleted successfully!']);
        } else {
            wp_send_json_error(['message' => 'Failed to delete review.']);
        }
    }

    /**
     * AJAX Handler: Allows a Provider to post/update a Public Reply to an approved review.
     */
    public function handle_provider_reply_review(): void
    {
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Please log in as a provider.', 'cosy-appointments')]);
        }

        $user_id    = get_current_user_id();
        $review_id  = isset($_POST['review_id']) ? intval($_POST['review_id']) : 0;
        $reply_text = isset($_POST['reply_text']) ? sanitize_textarea_field($_POST['reply_text']) : '';

        if (!$review_id || empty($reply_text)) {
            wp_send_json_error(['message' => __('Please enter a valid reply text.', 'cosy-appointments')]);
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'cosy_provider_reviews';
        $replies_table = $wpdb->prefix . 'cosy_review_replies';

        // Check that the review exists and belongs to this provider
        $review = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d AND provider_id = %d",
            $review_id,
            $user_id
        ));

        if (!$review) {
            wp_send_json_error(['message' => __('Review not found or unauthorized.', 'cosy-appointments')]);
        }

        // Determine level of provider reply (Level 1 initial reply vs Level 3 final reply)
        $existing_replies = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $replies_table WHERE review_id = %d ORDER BY reply_level ASC",
            $review_id
        ), ARRAY_A);

        $has_level1 = !empty($review->provider_reply);
        $has_level2 = false;
        foreach ($existing_replies as $er) {
            if ($er['reply_level'] == 1) $has_level1 = true;
            if ($er['reply_level'] == 2) $has_level2 = true;
        }

        $target_level = 1;
        if ($has_level1 && $has_level2) {
            $target_level = 3;
        } elseif ($has_level1 && !$has_level2) {
            // Updating existing Level 1 reply if customer hasn't responded yet
            $target_level = 1;
        }

        $prov_user = get_userdata($user_id);
        $prov_name = $prov_user ? ($prov_user->first_name ?: $prov_user->display_name) : 'Provider';

        // Delete previous reply at target_level if provider is updating it
        $wpdb->delete($replies_table, ['review_id' => $review_id, 'reply_level' => $target_level], ['%d', '%d']);

        $inserted = $wpdb->insert(
            $replies_table,
            [
                'review_id'   => $review_id,
                'sender_id'   => $user_id,
                'sender_role' => 'provider',
                'sender_name' => $prov_name,
                'reply_text'  => $reply_text,
                'reply_level' => $target_level,
                'created_at'  => current_time('mysql')
            ],
            ['%d', '%d', '%s', '%s', '%s', '%d', '%s']
        );

        if ($target_level === 1) {
            $wpdb->update(
                $table_name,
                [
                    'provider_reply' => $reply_text,
                    'reply_date'     => current_time('mysql')
                ],
                ['id' => $review_id],
                ['%s', '%s'],
                ['%d']
            );
        }

        if ($inserted !== false) {
            \Cosy\Appointments\Common\LogManager::log(
                'reviews',
                'PROVIDER_REPLY',
                sprintf(__('Provider #%d posted Level %d response for Review #%d.', 'cosy-appointments'), $user_id, $target_level, $review_id),
                $user_id
            );

            // Send Email notification to Customer about Provider's response
            if (!empty($review->customer_id)) {
                $customer_user = get_userdata($review->customer_id);
                if ($customer_user && !empty($customer_user->user_email) && function_exists('cosy_send_html_email')) {
                    $customer_name = !empty($review->customer_name) ? $review->customer_name : $customer_user->display_name;

                    if ($target_level === 3) {
                        // Level 3 Final Closing Response: Include full conversation history transcript
                        $l1_row = $wpdb->get_row($wpdb->prepare("SELECT reply_text FROM $replies_table WHERE review_id = %d AND reply_level = 1 LIMIT 1", $review_id));
                        $l2_row = $wpdb->get_row($wpdb->prepare("SELECT reply_text FROM $replies_table WHERE review_id = %d AND reply_level = 2 LIMIT 1", $review_id));
                        $l1_text = $l1_row ? $l1_row->reply_text : ($review->provider_reply ?: '');
                        $l2_text = $l2_row ? $l2_row->reply_text : '';

                        $tpl = \Cosy\Appointments\Common\EmailTemplates::get_customer_review_closing_template(
                            $customer_name,
                            $prov_name,
                            $review->review,
                            $l1_text,
                            $l2_text,
                            $reply_text
                        );
                    } else {
                        // Level 1 Initial Response Email
                        $tpl = \Cosy\Appointments\Common\EmailTemplates::get_customer_review_reply_template(
                            $customer_name,
                            $prov_name,
                            $reply_text,
                            $review->review
                        );
                    }

                    cosy_send_html_email($customer_user->user_email, $tpl['subject'], $tpl['heading'], $tpl['content']);
                }
            }

            wp_send_json_success(['message' => __('Public reply posted successfully!', 'cosy-appointments')]);
        } else {
            wp_send_json_error(['message' => __('Failed to save reply.', 'cosy-appointments')]);
        }
    }

    /**
     * AJAX Handler: Allows a Customer to post a follow-up response in their review thread.
     */
    public function handle_customer_reply_review(): void
    {
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Please log in as a customer to reply.', 'cosy-appointments')]);
        }

        $user_id    = get_current_user_id();
        $review_id  = isset($_POST['review_id']) ? intval($_POST['review_id']) : 0;
        $reply_text = isset($_POST['reply_text']) ? sanitize_textarea_field($_POST['reply_text']) : '';

        if (!$review_id || empty($reply_text)) {
            wp_send_json_error(['message' => __('Please enter a valid reply text.', 'cosy-appointments')]);
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'cosy_provider_reviews';
        $replies_table = $wpdb->prefix . 'cosy_review_replies';

        $review = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d AND customer_id = %d",
            $review_id,
            $user_id
        ));

        if (!$review) {
            wp_send_json_error(['message' => __('Review thread not found or unauthorized.', 'cosy-appointments')]);
        }

        // Verify provider reply (Level 1) exists
        $has_level1 = !empty($review->provider_reply);
        if (!$has_level1) {
            $has_level1 = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $replies_table WHERE review_id = %d AND reply_level = 1",
                $review_id
            ));
        }

        if (!$has_level1) {
            wp_send_json_error(['message' => __('You can post a response after the provider replies.', 'cosy-appointments')]);
        }

        // Check if customer already posted Level 2 reply
        $has_level2 = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $replies_table WHERE review_id = %d AND reply_level = 2",
            $review_id
        ));

        if ($has_level2) {
            wp_send_json_error(['message' => __('You have already posted your response in this review thread.', 'cosy-appointments')]);
        }

        $cust_user = get_userdata($user_id);
        $cust_name = $cust_user ? ($cust_user->first_name ?: $cust_user->display_name) : 'Customer';

        $inserted = $wpdb->insert(
            $replies_table,
            [
                'review_id'   => $review_id,
                'sender_id'   => $user_id,
                'sender_role' => 'customer',
                'sender_name' => $cust_name,
                'reply_text'  => $reply_text,
                'reply_level' => 2,
                'created_at'  => current_time('mysql')
            ],
            ['%d', '%d', '%s', '%s', '%s', '%d', '%s']
        );

        if ($inserted) {
            \Cosy\Appointments\Common\LogManager::log(
                'reviews',
                'CUSTOMER_REPLY',
                sprintf(__('Customer #%d posted Level 2 follow-up response for Review #%d.', 'cosy-appointments'), $user_id, $review_id),
                $user_id
            );

            // Send Email notification to Provider about Customer's follow-up reply
            $provider_user = get_userdata($review->provider_id);
            if ($provider_user && !empty($provider_user->user_email) && function_exists('cosy_send_html_email')) {
                $provider_name = $provider_user->first_name ?: $provider_user->display_name;
                $tpl = \Cosy\Appointments\Common\EmailTemplates::get_provider_review_followup_template(
                    $provider_name,
                    $cust_name,
                    $reply_text,
                    $review->review
                );
                cosy_send_html_email($provider_user->user_email, $tpl['subject'], $tpl['heading'], $tpl['content']);
            }

            wp_send_json_success(['message' => __('Your response has been added to the review thread!', 'cosy-appointments')]);
        } else {
            wp_send_json_error(['message' => __('Failed to post response.', 'cosy-appointments')]);
        }
    }

    /**
     * AJAX Handler: Allows a Provider to clear/dismiss audit trail notices.
     */
    public function handle_dismiss_audit_alerts(): void
    {
        $user_id = get_current_user_id();
        if ($user_id) {
            delete_user_meta($user_id, 'cosy_review_audit_alerts');
            wp_send_json_success(['message' => __('Audit notices cleared.', 'cosy-appointments')]);
        }
        wp_send_json_error(['message' => __('Unauthorized access.', 'cosy-appointments')]);
    }

    /**
     * AJAX Handler: Checks if the provider has completed their profile information,
     * services setup, and availability. Returns updated warning HTML if incomplete.
     */
    public function handle_check_profile_completeness(): void
    {
        $user_id = $this->verify_ajax_request('cosy_dashboard_nonce');

        // Check Profile Information
        $has_profile_info = !empty(get_user_meta($user_id, 'first_name', true)) &&
                            !empty(get_user_meta($user_id, 'prov_phone', true)) &&
                            !empty(get_user_meta($user_id, 'dob', true)) &&
                            !empty(get_user_meta($user_id, 'gender', true)) &&
                            !empty(get_user_meta($user_id, 'age_group', true));

        // Check Services
        global $wpdb;
        $services_table = $wpdb->prefix . 'provider_services';
        $has_services = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $services_table WHERE provider_id = %d AND checkbox_status = 'yes'",
                $user_id
            )
        );

        // Check Availability
        $has_availability = false;
        $days_of_week = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        foreach ($days_of_week as $day) {
            $day_data = get_user_meta($user_id, "cosy_availability_{$day}", true);
            if (!empty($day_data) && !empty($day_data['start_time']) && !empty($day_data['end_time'])) {
                $has_availability = true;
                break;
            }
        }

        $missing_requirements = [];
        if (!$has_profile_info) {
            $missing_requirements[] = __('Profile Information', 'cosy-appointments');
        }
        if (!$has_services) {
            $missing_requirements[] = __('Experiences', 'cosy-appointments');
        }
        if (!$has_availability) {
            $missing_requirements[] = __('Availability', 'cosy-appointments');
        }

        $html = '';
        if (!empty($missing_requirements)) {
            $count = count($missing_requirements);
            if ($count === 3) {
                $req_text = $missing_requirements[0] . ', ' . $missing_requirements[1] . ' ' . __('and', 'cosy-appointments') . ' ' . $missing_requirements[2];
            } elseif ($count === 2) {
                $req_text = $missing_requirements[0] . ' ' . __('and', 'cosy-appointments') . ' ' . $missing_requirements[1];
            } else {
                $req_text = $missing_requirements[0];
            }

            ob_start();
            ?>
            <div class="alert d-flex align-items-center mb-4 border-0 shadow-sm"
                style="background: #fff5f5; border-radius: 16px; color: #c53030;" role="alert">
                <div
                    style="width: 40px; height: 40px; background: rgba(229, 62, 62, 0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                    <i class="fas fa-exclamation-circle" style="font-size: 1.1rem; color: #e53e3e;"></i>
                </div>
                <div>
                    <strong style="font-family: 'Poppins', sans-serif;"><?php esc_html_e('Profile Incomplete:', 'cosy-appointments'); ?></strong> 
                    <span style="font-size: 0.95rem;">
                        <?php 
                        printf(
                            esc_html__('Please set up your %s. Your profile will not be visible on the front side until all of these are configured.', 'cosy-appointments'),
                            '<strong>' . esc_html($req_text) . '</strong>'
                        ); 
                        ?>
                    </span>
                </div>
            </div>
            <?php
            $html = ob_get_clean();
        }

        wp_send_json_success([
            'is_complete' => empty($missing_requirements),
            'html' => $html
        ]);
    }

    /**
     * AJAX Handler: Submits a token-validated review from email link.
     */
    public function handle_submit_token_review(): void
    {
        check_ajax_referer('cosy_review_nonce', 'nonce');

        $token       = isset($_POST['token']) ? sanitize_text_field($_POST['token']) : '';
        $rating      = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
        $review_text = isset($_POST['review']) ? sanitize_textarea_field($_POST['review']) : '';

        if (empty($token)) {
            wp_send_json_error(['message' => __('Missing review token.', 'cosy-appointments')]);
        }

        if ($rating < 1 || $rating > 10) {
            wp_send_json_error(['message' => __('Please select a rating score between 1 and 10.', 'cosy-appointments')]);
        }

        if (empty($review_text) || strlen($review_text) < 5) {
            wp_send_json_error(['message' => __('Please write a review comment.', 'cosy-appointments')]);
        }

        global $wpdb;
        $tokens_table  = $wpdb->prefix . 'cosy_review_tokens';
        $reviews_table = $wpdb->prefix . 'cosy_provider_reviews';

        $token_row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $tokens_table WHERE token = %s LIMIT 1",
            $token
        ));

        if (!$token_row) {
            wp_send_json_error(['message' => __('Invalid review link. Please check your email.', 'cosy-appointments')]);
        }

        if (intval($token_row->used) === 1) {
            wp_send_json_error(['message' => __('This review link has already been used.', 'cosy-appointments')]);
        }

        $customer_user = get_userdata($token_row->customer_id);
        $customer_name = $customer_user ? ($customer_user->display_name ?: $customer_user->first_name) : 'Customer';

        // Insert pending review
        $inserted = $wpdb->insert(
            $reviews_table,
            [
                'provider_id'   => $token_row->provider_id,
                'customer_id'   => $token_row->customer_id,
                'customer_name' => $customer_name,
                'rating'        => $rating,
                'review'        => $review_text,
                'status'        => 'pending',
                'created_at'    => current_time('mysql'),
            ],
            ['%d', '%d', '%s', '%d', '%s', '%s', '%s']
        );

        if (!$inserted) {
            wp_send_json_error(['message' => __('Database error: Failed to save review. Please try again.', 'cosy-appointments')]);
        }

        // Mark token as used
        $wpdb->update(
            $tokens_table,
            ['used' => 1],
            ['token' => $token],
            ['%d'],
            ['%s']
        );

        $provider_user = get_userdata($token_row->provider_id);
        $provider_slug = $provider_user ? $provider_user->user_nicename : '';
        $redirect_url  = !empty($provider_slug) ? site_url("/author/{$provider_slug}/") : site_url('/');

        // Send Email notification to Admin about new pending review
        $admin_email = get_option('admin_email');
        if (!empty($admin_email) && function_exists('cosy_send_html_email') && $provider_user) {
            $prov_name = $provider_user->display_name ?: $provider_user->first_name;
            $tpl = \Cosy\Appointments\Common\EmailTemplates::get_admin_new_review_template(
                $prov_name,
                $customer_name,
                intval($rating),
                $review_text
            );
            cosy_send_html_email($admin_email, $tpl['subject'], $tpl['heading'], $tpl['content']);
        }

        wp_send_json_success([
            'message'      => __('Thank you! Your review has been submitted successfully and is pending admin approval.', 'cosy-appointments'),
            'redirect_url' => $redirect_url
        ]);
    }
}
