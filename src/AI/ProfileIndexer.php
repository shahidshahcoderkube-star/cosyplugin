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

        // Fetch assigned services from wp_provider_services (including service description if present)
        $services_table = $wpdb->prefix . 'provider_services';
        $services = [];
        if ($wpdb->get_var("SHOW TABLES LIKE '$services_table'") === $services_table) {
            $service_rows = $wpdb->get_results(
                $wpdb->prepare("SELECT service, description FROM $services_table WHERE provider_id = %d", $user_id)
            );
            foreach ($service_rows as $row) {
                if (!empty($row->service)) {
                    $entry = trim($row->service);
                    if (!empty($row->description)) {
                        $entry .= " (" . trim($row->description) . ")";
                    }
                    $services[] = $entry;
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

        // Pre-compute bio sentence vector embeddings for high-precision proof extraction
        if (!empty($bio)) {
            $raw_sentences = preg_split('/(?<=[.!?])\s+/u', $bio, -1, PREG_SPLIT_NO_EMPTY);
            $clean_sentences = [];
            foreach ($raw_sentences as $rs) {
                $rs_trim = trim($rs);
                if (mb_strlen($rs_trim) >= 20 && str_word_count($rs_trim) >= 4) {
                    $clean_sentences[] = $rs_trim;
                }
            }
            if (!empty($clean_sentences)) {
                $clean_sentences  = array_slice($clean_sentences, 0, 8);
                $sentence_vectors = AIService::get_batch_embeddings($clean_sentences);
                $bio_sentence_data = [];
                foreach ($clean_sentences as $idx => $s_text) {
                    if (!empty($sentence_vectors[$idx])) {
                        $bio_sentence_data[] = [
                            'text'   => $s_text,
                            'vector' => $sentence_vectors[$idx],
                        ];
                    }
                }
                update_user_meta($user_id, 'cosy_bio_sentence_embeddings', $bio_sentence_data);
            }
        }

        // Fetch overall vector embedding
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
    public static function extract_profile_facts(string $bio, string $gender = '', string $services_str = ''): array
    {
        $text = mb_strtolower("$bio. $services_str");

        $facts = [
            'gender'                 => strtolower($gender),
            'is_owner_single_parent' => false,
            'is_helper_only'         => false,
            'children_count'         => 0,
            'experience_years'       => 0,
            'has_adhd_send_exp'      => false,
            'has_ivf_loss_exp'       => false,
            'has_twins_multiples'    => false,
            'has_adoption_exp'       => false,
            'has_divorce_exp'        => false,
        ];

        // 1. Detect Owner Statement Scope vs Helper Statement Scope
        // Owner Identity Statements: "I am a single mum", "I'm Rachel, a single mum", "became a single parent", "single dad", "full custody", "divorced dad", "raising on my own"
        $owner_pattern = '/\b(i am|i\'m|became a|as a)\s+([a-z\'-]+,?\s+)*(a\s+)?(single|solo|divorced|separated)\s+(mum|mom|mother|dad|father|parent)\b/i';
        $direct_single_parent = '/\b(single (mum|mom|mother|dad|father|parent)|solo (mum|mom|mother|dad|father|parent)|raising.*on my own|full custody)\b/i';

        // Helper Statements: "I support single mums", "work with single mums", "supported many single mums"
        $helper_pattern = '/\b(support|supported|working with|help|counsel)\s+(many\s+)?(single|solo)\s+(mums|moms|mothers|dads|fathers|parents)\b/i';
        $is_helper = preg_match($helper_pattern, $text) && !preg_match($owner_pattern, $text);
        if ($is_helper) {
            $facts['is_helper_only'] = true;
        }

        if (preg_match($owner_pattern, $text) || (preg_match($direct_single_parent, $text) && !$is_helper)) {
            $facts['is_owner_single_parent'] = true;
        }

        // Negative Statement Exclusions: "I'm not a single mum"
        if (preg_match('/\b(not a single (mum|mom|mother|dad|father|parent))\b/i', $text)) {
            $facts['is_owner_single_parent'] = false;
            $facts['is_helper_only'] = true;
        }

        // 2. Extract Children Count Context (1 to 10)
        $child_word_map = [
            'ten' => 10, 'nine' => 9, 'eight' => 8, 'seven' => 7, 'six' => 6,
            'five' => 5, 'four' => 4, 'three' => 3, 'two' => 2, 'one' => 1,
            '10' => 10, '9' => 9, '8' => 8, '7' => 7, '6' => 6,
            '5' => 5, '4' => 4, '3' => 3, '2' => 2, '1' => 1
        ];

        // Check exact "mum/dad of X (not month/year-old)", "raising X (children/kids/boys/girls)"
        foreach ($child_word_map as $word => $count) {
            if (preg_match('/\b(mum|mother|mom|parent|dad|father)\s+(of|to)\s+' . preg_quote($word, '/') . '(?!\s*-?\s*(month|year))\b/i', $text) ||
                preg_match('/\braising\s+' . preg_quote($word, '/') . '\s+(children|kids|boys|girls|sons|daughters)\b/i', $text)) {
                $facts['children_count'] = $count;
                break;
            }
        }

        // Detect twins / triplets / single baby
        if ($facts['children_count'] === 0) {
            if (preg_match('/\b(twins)\b/i', $text)) {
                $facts['children_count'] = 2;
            } elseif (preg_match('/\b(triplets)\b/i', $text)) {
                $facts['children_count'] = 3;
            } elseif (preg_match('/\b(a\s+daughter\b.*\band\s+(a\s+)?son\b|a\s+son\b.*\band\s+(a\s+)?daughter\b)/i', $text)) {
                $facts['children_count'] = 2;
            } elseif (preg_match('/\b(first-time|new)\s+(mum|mother|mom|dad|father|parent)\b/i', $text) ||
                      preg_match('/\braising\s+(a|an|my)\s+([a-z\'-]+\s+)*(baby|infant|toddler|child|son|daughter|boy|girl)\b/i', $text) ||
                      preg_match('/\b(to|with)\s+(a|an|my)\s+([a-z\'-]+\s+)*(baby|infant|toddler|child|son|daughter|boy|girl)\b/i', $text)) {
                $facts['children_count'] = 1;
            } else {
                foreach ($child_word_map as $word => $count) {
                    if (preg_match('/\b' . preg_quote($word, '/') . '\s+(children|kids|boys|girls|sons|daughters)\b/i', $text) &&
                        !preg_match('/\b(no|any)\s+' . preg_quote($word, '/') . '\s+(children|kids)\b/i', $text)) {
                        $facts['children_count'] = $count;
                        break;
                    }
                }
            }
        }

        // 3. Extract Experience Years Context
        if (preg_match('/(\d+)\s+years?\s+(of\s+)?(experience|supporting|counseling|guiding)/i', $text, $exp_matches)) {
            $facts['experience_years'] = intval($exp_matches[1]);
        }

        // 4. Lived Experience Fact Detection (ADHD/SEND, IVF/Loss, Twins, Adoption, Divorce)
        if (preg_match('/\b(adhd|autism|autistic|send|special needs|neurodivergent|sensory processing|neurodiverse)\b/i', $text)) {
            $facts['has_adhd_send_exp'] = true;
        }
        if (preg_match('/\b(ivf|icsi|miscarriage|baby loss|fertility journey|pregnancy loss|stillbirth)\b/i', $text)) {
            $facts['has_ivf_loss_exp'] = true;
        }
        if (preg_match('/\b(twins|triplets|multiples)\b/i', $text)) {
            $facts['has_twins_multiples'] = true;
        }
        if (preg_match('/\b(adopt|adopted|adoption|foster|fostering|foster care)\b/i', $text)) {
            $facts['has_adoption_exp'] = true;
        }
        if (preg_match('/\b(divorce|divorced|separation|separated|co-parent|co-parenting|coparenting)\b/i', $text)) {
            $facts['has_divorce_exp'] = true;
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
     * Flush provider list transients whenever profile data changes.
     */
    public static function clear_search_cache(): void
    {
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_cosy_prov_list_%' OR option_name LIKE '_transient_timeout_cosy_prov_list_%'");
    }
}
