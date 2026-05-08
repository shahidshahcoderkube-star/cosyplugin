<?php

namespace Cosy\Appointments\Frontend;

use Cosy\Appointments\Forms\FormsData;
use Cosy\Appointments\Loader;
use Cosy\Appointments\Common\GlobalCommonFunctions;

class Dashboard
{
    //--------------- Traits ----------------//
    use GlobalCommonFunctions;

    //--------------- Constructor ----------------//
    public function __construct()
    {
        //------ Register all AJAX handlers dynamically-----//
        $actions = [
            'cosy_provider_information_update'   => 'handle_profile_update',
            'cosy_provider_video'   => 'handle_video_upload',
            'delete_video' => 'ajax_delete_video',
            'load_dashboard_tab' => 'cosy_load_dashboard_tab',
        ];

        //------ Register AJAX handlers -----//
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

    //----------------- Profile Update Handler ----------------//
    public function handle_profile_update(): void
    {
        check_ajax_referer('cosy_dashboard_nonce', 'nonce');
        
        if (!current_user_can('manage_cosy_appointments')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(['message' => 'User not logged in']);
        }

        if (isset($_POST['update_provider_profile']) || !isset($_POST['action']) || $_POST['action'] !== 'cosy_customer_register') {
            $user_id = get_current_user_id();

            $provider_meta = [
                'prov_username' => sanitize_text_field($_POST['prov_username']),
                // 'prov_fname'    => sanitize_text_field($_POST['prov_fname']),
                // 'prov_mname'    => sanitize_text_field($_POST['prov_mname']),
                'first_name'    => sanitize_text_field($_POST['prov_fname']),
                'prov_mname'    => sanitize_text_field($_POST['prov_mname']),
                'last_name'    => sanitize_text_field($_POST['prov_sname']),
                'prov_email'    => sanitize_email($_POST['prov_email']),
                'prov_phone'    => sanitize_text_field($_POST['prov_phone']),
                'prov_address'  => sanitize_textarea_field($_POST['prov_address']),
                'dob'           => sanitize_text_field($_POST['dob']),
                'postal_code'   => sanitize_text_field($_POST['postal_code']),
                'description'           => sanitize_textarea_field($_POST['bio']),
                'gender'        => sanitize_text_field($_POST['gender']),
                'age_group'     => sanitize_text_field($_POST['age_group']),
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

    //----------------- Video Upload Handler ----------------//
    public function handle_video_upload(): void
    {
        check_ajax_referer('cosy_dashboard_nonce', 'nonce');

        if (!current_user_can('manage_cosy_appointments')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(['message' => 'User not logged in']);
        }

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

                wp_send_json_success([
                    'message'     => 'Video uploaded successfully! Awaiting admin approval.',
                    'video_url'   => $video_url,
                    'uploaded_on' => current_time('mysql'),
                    'status'      => 'pending'
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
        check_ajax_referer('cosy_dashboard_nonce', 'nonce');

        if (!current_user_can('manage_cosy_appointments')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

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


    public function cosy_load_dashboard_tab()
    {
        check_ajax_referer('cosy_dashboard_nonce', 'nonce');

        if (!current_user_can('manage_cosy_appointments')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $user_id = get_current_user_id();

        if (!$user_id) {
            wp_send_json_error(['message' => 'User not logged in']);
        }


        if (empty($_POST['tab'])) {
            wp_send_json_error('Invalid tab');
        }

        $tab = sanitize_text_field($_POST['tab']);

        // allowed tabs (security)
        $allowed_tabs = [
            'profile'     => 'profile-information.php',
            'video'       => 'media-upload.php',
            'services'    => 'services.php',
            'availability' => 'availability.php',
            'orders'      => 'orders.php',
            'nonworking'  => 'holidays.php',
            'reviews'     => 'customer-reviews.php',
            'invoices'    => 'invoices.php',
        ];

        if (! isset($allowed_tabs[$tab])) {
            wp_send_json_error('Tab not allowed');
        }

        $file_path = plugin_dir_path(__FILE__) . '../../templates/provider/dashboard/' . $allowed_tabs[$tab];

        if (! file_exists($file_path)) {
            wp_send_json_error('File not found');
        }

        ob_start();
        include $file_path;
        $html = ob_get_clean();

        wp_send_json_success([
            'html' => $html
        ]);
    }
}
