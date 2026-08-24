<?php

namespace Cosy\Appointments\AI;

use Cosy\Appointments\Common\LogManager;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * SearchController Class
 * Connects AI Search and Reindex functionality with WordPress AJAX & REST endpoints.
 */
class SearchController
{
    /**
     * CONSTRUCTS AI SEARCH CONTROLLER & REGISTERS HOOKS
     * 
     * USE CASE:
     * Instantiated during plugin initialization to register AJAX search and auto-indexing hooks.
     * 
     * HOW TO USE:
     * new SearchController();
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Attaches 'cosy_ai_search' AJAX endpoints for logged-in and guest users.
     * 2. Attaches 'cosy_ai_reindex' AJAX endpoint for admin bulk re-indexing.
     * 3. Attaches profile update and user meta hooks to auto-update vector embeddings in real time.
     */
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
     * HANDLES AI SEARCH AJAX REQUESTS
     * 
     * USE CASE:
     * Triggered via AJAX when a site visitor enters a natural language query in the AI search field.
     * 
     * HOW TO USE:
     * Automatically invoked by WordPress AJAX endpoint 'cosy_ai_search'.
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Sanitizes incoming search query parameter string.
     * 2. Calls SearchEngine::search($query) to compute semantic matches.
     * 3. Logs search event to LogManager.
     * 4. Returns JSON response containing array of matching provider profile cards.
     */
    public function handle_ai_search(): void
    {
        $query = isset($_REQUEST['query']) ? sanitize_text_field(wp_unslash($_REQUEST['query'])) : '';

        if (empty($query)) {
            wp_send_json_error(['message' => __('Please enter a search query.', 'cosy-appointments')]);
        }

        $results = SearchEngine::search($query);

        // Determine user role label for log message
        $actor_label = __('Guest', 'cosy-appointments');
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            $roles = (array) $user->roles;
            if (in_array('administrator', $roles, true)) {
                $actor_label = __('Admin', 'cosy-appointments');
            } elseif (in_array('provider', $roles, true)) {
                $actor_label = __('Provider', 'cosy-appointments');
            } elseif (in_array('customer', $roles, true)) {
                $actor_label = __('Customer', 'cosy-appointments');
            } else {
                $actor_label = __('User', 'cosy-appointments');
            }
        }

        // Log search query in Admin Activity Logs table
        if (class_exists(LogManager::class)) {
            LogManager::log(
                'ai_search',
                'AI Search',
                sprintf(__('%s searched for: "%s" (%d results found)', 'cosy-appointments'), $actor_label, $query, count($results))
            );
        }

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
        $relevant_keys = ['description', 'age_group', 'gender', 'dob', 'first_name', 'last_name', 'cosy_provider_status', 'account_status'];
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
