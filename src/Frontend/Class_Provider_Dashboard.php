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
    //--------------- Traits ----------------//
    use GlobalCommonFunctions;

    /**
     * Constructor: Initializes the class and registers AJAX handlers.
     * These handlers allow the frontend (JavaScript) to talk to the backend (PHP).
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
            'cosy_add_holiday'                 => 'handle_add_holiday',      // Adds a non-working day
            'cosy_delete_holiday'              => 'handle_delete_holiday',   // Deletes a non-working day
            'cosy_add_provider_review'         => 'handle_add_review',
            'cosy_approve_provider_review'     => 'handle_approve_review',
            'cosy_delete_provider_review'      => 'handle_delete_review',
        ];

        // Register all AJAX handlers dynamically
        $this->register_ajax_handlers($actions, $this);
    }

    //----- Registering the shortcode for provider dashboard -----//
    public function register(Loader $loader): void
    {
        /* Registering the shortcode for provider dashboard */
        $loader->add_action('init', $this, 'register_dashboard_shortcode');
    }

    //----- Registering the shortcode for provider dashboard-----//
    public function register_dashboard_shortcode(): void
    {
        add_shortcode('cosy_provider_dashboard', [$this, 'provider_dashboard_shortcode']);
    }

    //------ Rendering the provider dashboard shortcode content----//
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

            // ✅ Handle profile image upload correctly

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

        // ✅ Check if user already has a pending video
        $current_status = get_user_meta($user_id, 'video_status', true);
        if ($current_status === 'pending') {
            wp_send_json_error(['message' => 'Your previous video approval is still pending. You cannot upload a new video until it is reviewed.']);
        }

        if (!empty($_FILES['video_upload']['name'])) {
            // ✅ Size check (2 MB)
            $max_size = 2 * 1024 * 1024;
            if ($_FILES['video_upload']['size'] > $max_size) {
                wp_send_json_error(['message' => 'Video size must not exceed 2 MB']);
            }

            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');

            $attachment_id = media_handle_upload('video_upload', 0);
            if (!is_wp_error($attachment_id)) {
                $video_url = wp_get_attachment_url($attachment_id);

                // ✅ Save video URL
                update_user_meta($user_id, 'introduction_video', $video_url);

                // ✅ Save current date/time
                update_user_meta($user_id, 'video_uploaded_on', current_time('mysql'));

                // ✅ Save status as pending
                update_user_meta($user_id, 'video_status', 'pending');

                // ✅ Sync with custom table for Admin Dashboard
                global $wpdb;
                $table_name = $wpdb->prefix . 'cosy_media_approvals';

                // Check if user already has an entry
                $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE user_id = %d", $user_id));

                if ($existing) {
                    $wpdb->update(
                        $table_name,
                        [
                            'media_url'   => $video_url,
                            'status'      => 'pending',
                            'uploaded_at' => current_time('mysql'),
                            'reviewed_at' => null
                        ],
                        ['user_id' => $user_id],
                        ['%s', '%s', '%s', '%s'],
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

    //----------------- Delete Video Handler ----------------//
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
            $attachment_id = attachment_url_to_postid($video_url);
            if ($attachment_id) {
                wp_delete_attachment($attachment_id, true); // ✅ delete from media library
            }
            delete_user_meta($user_id, 'introduction_video');
        }

        update_user_meta($user_id, 'video_status', 'deleted');

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
            'invoices' => 'invoices.php',
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
        $holidays = get_user_meta($user_id, 'cosy_provider_holidays', true);
        $holidays = !empty($holidays) ? json_decode($holidays, true) : [];

        // Prevent duplicate dates
        foreach ($holidays as $h) {
            if ($h['date'] === $date) {
                wp_send_json_error(['message' => 'This date is already marked as a holiday.']);
            }
        }

        // Add the new holiday
        $holidays[] = [
            'date'   => $date,
            'reason' => $reason ?: 'Holiday',
        ];

        // Sort by date ascending
        usort($holidays, fn($a, $b) => strcmp($a['date'], $b['date']));

        // Save back to user meta
        update_user_meta($user_id, 'cosy_provider_holidays', wp_json_encode($holidays));

        // Format date for display: 01 Jan 2026
        $display_date = date('d M Y', strtotime($date));

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

        // Load existing holidays
        $holidays = get_user_meta($user_id, 'cosy_provider_holidays', true);
        $holidays = !empty($holidays) ? json_decode($holidays, true) : [];

        // Filter out the holiday with the given date
        $updated = array_values(array_filter($holidays, fn($h) => $h['date'] !== $date));

        // Save updated list
        update_user_meta($user_id, 'cosy_provider_holidays', wp_json_encode($updated));

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
        $day = sanitize_text_field($_POST['day']);
        
        if (!$day) {
            wp_send_json_error('Day is required');
        }

        $availability_data = [
            'start_time'    => sanitize_text_field($_POST['start_time']),
            'end_time'      => sanitize_text_field($_POST['end_time']),
            'slot_duration' => sanitize_text_field($_POST['slot_duration']),
            'break_start'   => sanitize_text_field($_POST['break_start']),
            'break_end'     => sanitize_text_field($_POST['break_end']),
        ];

        // Save in user meta for the specific day
        update_user_meta($user_id, "cosy_availability_{$day}", $availability_data);
 
        wp_send_json_success('Availability saved successfully for ' . $day);
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
 
        if ($rating < 1 || $rating > 5) {
            wp_send_json_error(['message' => 'Please select a star rating (1 to 5).']);
        }
 
        if (empty($review_text)) {
            wp_send_json_error(['message' => 'Please write a review comment.']);
        }
 
        global $wpdb;
        $table_name = $wpdb->prefix . 'cosy_provider_reviews';
 
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
            wp_send_json_success(['message' => 'Review submitted successfully! It will be displayed after provider approval.']);
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
            wp_send_json_success(['message' => 'Review approved successfully!']);
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
            wp_send_json_success(['message' => 'Review deleted successfully!']);
        } else {
            wp_send_json_error(['message' => 'Failed to delete review.']);
        }
    }
}
