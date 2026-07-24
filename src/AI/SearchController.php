<?php

namespace Cosy\Appointments\AI;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * SearchController Class
 * Connects AI Search and Reindex functionality with WordPress AJAX & REST endpoints.
 */
class SearchController
{
    public function __construct()
    {
        // Public & Logged-in User AI Search AJAX
        add_action('wp_ajax_cosy_ai_search', [$this, 'handle_ai_search']);
        add_action('wp_ajax_nopriv_cosy_ai_search', [$this, 'handle_ai_search']);

        // Admin Bulk Reindex AJAX
        add_action('wp_ajax_cosy_ai_reindex', [$this, 'handle_bulk_reindex']);

        // Auto-index single provider in real-time when profile is created or updated (Admin or Frontend)
        add_action('profile_update', [$this, 'handle_single_profile_update'], 10, 1);
        add_action('user_register', [$this, 'handle_single_profile_update'], 10, 1);
        add_action('edit_user_profile_update', [$this, 'handle_single_profile_update'], 10, 1);
        add_action('personal_options_update', [$this, 'handle_single_profile_update'], 10, 1);
        add_action('updated_user_meta', [$this, 'handle_user_meta_update'], 10, 4);
        add_action('cosy_provider_services_updated', [$this, 'handle_single_profile_update'], 10, 1);
    }

    /**
     * AJAX Handler for AI Search Query.
     */
    public function handle_ai_search(): void
    {
        $query = isset($_REQUEST['query']) ? sanitize_text_field(wp_unslash($_REQUEST['query'])) : '';

        if (empty($query)) {
            wp_send_json_error(['message' => __('Please enter a search query.', 'cosy-appointments')]);
        }

        $results = SearchEngine::search($query);

        $providers = $results;
        $html = '';
        if (defined('COSY_APPT_PATH') && file_exists(COSY_APPT_PATH . 'templates/service-provider-grid-template.php')) {
            ob_start();
            include COSY_APPT_PATH . 'templates/service-provider-grid-template.php';
            $html = ob_get_clean();
        }

        wp_send_json_success([
            'query'   => $query,
            'count'   => count($results),
            'results' => $results,
            'html'    => $html,
        ]);
    }

    /**
     * Admin AJAX Handler to trigger bulk vector indexing of all provider profiles.
     */
    public function handle_bulk_reindex(): void
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'cosy-appointments')]);
        }

        $count = ProfileIndexer::bulk_index_all_providers();

        wp_send_json_success([
            'message' => sprintf(__('%d provider profiles successfully indexed with AI vectors!', 'cosy-appointments'), $count),
            'indexed' => $count,
        ]);
    }

    /**
     * Automatic real-time indexing when a single provider registers or updates profile.
     */
    public function handle_single_profile_update(int $user_id): void
    {
        $user = get_userdata($user_id);
        if ($user && in_array('provider', (array)$user->roles)) {
            ProfileIndexer::index_provider($user_id);
            ProfileIndexer::clear_search_cache();
        }
    }

    /**
     * Automatic real-time indexing when provider meta field (description, age_group, gender, dob) is updated.
     */
    public function handle_user_meta_update($meta_id, $object_id, $meta_key, $_meta_value): void
    {
        $relevant_keys = ['description', 'age_group', 'gender', 'dob', 'first_name', 'last_name'];
        if (in_array($meta_key, $relevant_keys, true)) {
            $user_id = (int)$object_id;
            $user = get_userdata($user_id);
            if ($user && in_array('provider', (array)$user->roles)) {
                ProfileIndexer::index_provider($user_id);
                ProfileIndexer::clear_search_cache();
            }
        }
    }
}
