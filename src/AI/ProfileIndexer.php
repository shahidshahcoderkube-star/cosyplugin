<?php

namespace Cosy\Appointments\AI;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * ProfileIndexer Class
 * Generates and stores vector embeddings for parent/provider profiles.
 */
class ProfileIndexer
{
    /**
     * INDEXES SINGLE PROVIDER PROFILE FOR AI VECTOR SEARCH
     * 
     * USE CASE:
     * Called whenever a provider updates profile info, bio, or offered services to generate updated embeddings.
     * 
     * HOW TO USE:
     * ProfileIndexer::index_provider($user_id);
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Fetches user details (name, bio, gender, age, services).
     * 2. Formats bio and service tags into a rich searchable text string.
     * 3. Generates vector embedding array via AIService::get_embedding().
     * 4. Upserts embedding JSON string into wp_cosychats_embeddings database table.
     * 
     * @param int $user_id Provider WP User ID.
     * @return bool        True if successfully indexed, false otherwise.
     */
    public static function index_provider(int $user_id): bool
    {
        global $wpdb;

        $user = get_userdata($user_id);
        if (!$user) {
            return false;
        }

        // If provider is deactive/unverified, purge existing embedding and clear search cache
        $status = get_user_meta($user_id, 'cosy_provider_status', true);
        if ($status !== 'active') {
            $embeddings_table = $wpdb->prefix . 'provider_embeddings';
            if ($wpdb->get_var("SHOW TABLES LIKE '$embeddings_table'") === $embeddings_table) {
                $wpdb->delete($embeddings_table, ['provider_id' => $user_id], ['%d']);
            }
            self::clear_search_cache();
            return false;
        }

        $first_name = get_user_meta($user_id, 'first_name', true) ?: $user->display_name;
        $last_name  = get_user_meta($user_id, 'last_name', true) ?: '';
        $bio        = get_user_meta($user_id, 'description', true) ?: '';
        $gender     = get_user_meta($user_id, 'gender', true) ?: '';
        $age_group  = get_user_meta($user_id, 'age_group', true) ?: '';
        $dob        = get_user_meta($user_id, 'dob', true) ?: '';

        // Calculate age in years if DOB is present
        $age_str = '';
        if (!empty($dob)) {
            $dob_datetime = date_create($dob);
            if ($dob_datetime) {
                $now = date_create('today');
                $diff = date_diff($dob_datetime, $now);
                if ($diff && $diff->y > 0) {
                    $age_str = $diff->y . " years old";
                }
            }
        }

        // Fetch assigned services from wp_provider_services
        $services_table = $wpdb->prefix . 'provider_services';
        $services = [];
        if ($wpdb->get_var("SHOW TABLES LIKE '$services_table'") === $services_table) {
            $service_rows = $wpdb->get_results(
                $wpdb->prepare("SELECT DISTINCT service FROM $services_table WHERE provider_id = %d", $user_id)
            );
            foreach ($service_rows as $row) {
                if (!empty($row->service)) {
                    $services[] = $row->service;
                }
            }
        }
        $services_str = implode(', ', $services);

        // Build descriptive profile text for vector embedding
        $text_parts = [];
        $text_parts[] = "Name: " . trim("$first_name $last_name");
        if (!empty($gender)) {
            $text_parts[] = "Gender: " . $gender;
        }
        if (!empty($age_group)) {
            $text_parts[] = "Age Group: " . $age_group;
        }
        if (!empty($age_str)) {
            $text_parts[] = "Age: " . $age_str;
        }
        if (!empty($services_str)) {
            $text_parts[] = "Services & Support Offered: " . $services_str;
        }
        if (!empty($bio)) {
            $text_parts[] = "Bio & Lived Experiences: " . $bio;
        }

        $profile_text = implode(". ", $text_parts);

        // Expand numbers in profile text (e.g. "eight" -> "8") to enrich embedding semantic vector
        $map = [
            'zero' => '0', 'one' => '1', 'two' => '2', 'three' => '3', 'four' => '4',
            'five' => '5', 'six' => '6', 'seven' => '7', 'eight' => '8', 'nine' => '9', 'ten' => '10'
        ];
        $extra_terms = [];
        foreach ($map as $word => $digit) {
            if (preg_match("/\b" . preg_quote($word, '/') . "\b/i", $profile_text)) {
                $extra_terms[] = "$digit $word";
            }
            if (preg_match("/\b" . preg_quote($digit, '/') . "\b/i", $profile_text)) {
                $extra_terms[] = "$digit $word";
            }
        }
        if (!empty($extra_terms)) {
            $profile_text .= ". Keywords: " . implode(" ", array_unique($extra_terms));
        }

        // Extract Structured Profile Facts (Statement Owner Scope vs Helper Scope)
        $facts = self::extract_profile_facts($bio, $gender, $services_str);
        update_user_meta($user_id, 'cosy_profile_facts', $facts);

        // Fetch vector embedding
        $vector = AIService::get_embedding($profile_text);
        if (empty($vector)) {
            return false;
        }

        // Store or update in wp_provider_embeddings
        $embeddings_table = $wpdb->prefix . 'provider_embeddings';
        $result = $wpdb->replace(
            $embeddings_table,
            [
                'provider_id' => $user_id,
                'embedding'   => json_encode($vector),
                'updated_at'  => current_time('mysql'),
            ],
            ['%d', '%s', '%s']
        );

        // Clear search cache since database content updated
        self::clear_search_cache();

        return $result !== false;
    }

    /**
     * Parse and extract structured facts from provider bio text (Statement Owner Scope vs Helper Scope).
     */
    public static function extract_profile_facts(string $bio, string $gender = '', string $services = ''): array
    {
        $text = strtolower($bio);

        $facts = [
            'gender'                 => strtolower($gender),
            'is_owner_single_parent' => false,
            'is_helper_only'         => false,
            'children_count'         => 0,
            'experience_years'       => 0,
        ];

        // 1. Detect Owner Statement Scope vs Helper Statement Scope
        // Owner Identity Statements: "I am a single mum", "I'm a solo mother", "I became a single parent"
        $owner_pattern = '/\b(i am|i\'m|became a|as a)\s+(a\s+)?(single|solo)\s+(mum|mom|mother|parent)\b/i';
        if (preg_match($owner_pattern, $text)) {
            $facts['is_owner_single_parent'] = true;
        }

        // Helper Statements: "I support single mums", "work with single mums", "supported many single mums"
        $helper_pattern = '/\b(support|supported|working with|help|counsel)\s+(many\s+)?(single|solo)\s+(mums|moms|mothers|parents)\b/i';
        if (preg_match($helper_pattern, $text) && !preg_match($owner_pattern, $text)) {
            $facts['is_helper_only'] = true;
        }

        // Negative Statement Exclusions: "I'm not a single mum"
        if (preg_match('/\b(not a single mum|not a single mother|not a solo mum)\b/i', $text)) {
            $facts['is_owner_single_parent'] = false;
            $facts['is_helper_only'] = true;
        }

        // 2. Extract Children Count Context
        $child_word_map = [
            'one' => 1, 'two' => 2, 'three' => 3, 'four' => 4, 'five' => 5,
            '1'   => 1, '2'   => 2, '3'     => 3, '4'    => 4, '5'    => 5
        ];

        foreach ($child_word_map as $word => $count) {
            if (preg_match('/\b(mum|mother|mom|parent|dad|father)\s+of\s+' . preg_quote($word, '/') . '\b/i', $text) ||
                preg_match('/\b' . preg_quote($word, '/') . '\s+(children|kids|boys|girls|sons|daughters)\b/i', $text)) {
                $facts['children_count'] = $count;
                break;
            }
        }

        // 3. Extract Experience Years Context
        if (preg_match('/(\d+)\s+years?\s+(of\s+)?(experience|supporting|counseling|guiding)/i', $text, $exp_matches)) {
            $facts['experience_years'] = intval($exp_matches[1]);
        }

        return $facts;
    }

    /**
     * Index all active providers in bulk.
     *
     * @return int Number of profiles successfully indexed.
     */
    public static function bulk_index_all_providers(): int
    {
        $args = [
            'role'       => 'provider',
            'number'     => -1,
            'fields'     => 'ID',
            'meta_query' => [
                [
                    'key'     => 'cosy_provider_status',
                    'value'   => 'active',
                    'compare' => '='
                ]
            ]
        ];
        $provider_ids = get_users($args);
        $indexed_count = 0;

        foreach ($provider_ids as $id) {
            if (self::index_provider((int)$id)) {
                $indexed_count++;
            }
        }

        return $indexed_count;
    }

    /**
     * Empty search cache table whenever profile data changes.
     */
    public static function clear_search_cache(): void
    {
        global $wpdb;
        $table_cache = $wpdb->prefix . 'cosychats_search_cache';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_cache'") === $table_cache) {
            $wpdb->query("TRUNCATE TABLE $table_cache");
        }
    }
}
