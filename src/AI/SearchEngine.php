<?php

namespace Cosy\Appointments\AI;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * SearchEngine Class
 * 3-Phase Enterprise Hybrid Search Engine combining:
 * 1. Intent & Gender Entity Parsing with Contradiction Penalties
 * 2. Exact Phrase Matching & BM25 Keyword Boosting
 * 3. Vector Semantic Cosine Similarity
 * 4. Hybrid Ranking by Relevance, Ratings, and Price Fit
 */
class SearchEngine
{
    /**
     * PERFORMS ENTERPRISE HYBRID AI SEMANTIC & LEXICAL SEARCH
     * 
     * @param string $query_text Natural language search query.
     * @param int    $limit      Maximum provider cards to return.
     * @return array             Array of provider profile cards.
     */
    public static function search(string $query_text, int $limit = 6): array
    {
        $query_text = trim($query_text);
        if (empty($query_text)) {
            return [];
        }

        global $wpdb;
        $query_hash = md5(strtolower($query_text));
        $table_cache = $wpdb->prefix . 'cosychats_search_cache';

        // 1. Check Local Search Cache
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_cache'") === $table_cache) {
            $cached = $wpdb->get_var(
                $wpdb->prepare("SELECT matching_provider_ids FROM $table_cache WHERE query_hash = %s", $query_hash)
            );
            if (!empty($cached)) {
                $cached_ids = json_decode($cached, true);
                if (is_array($cached_ids) && !empty($cached_ids)) {
                    // Verify all cached IDs are still active
                    $valid_cached = [];
                    foreach ($cached_ids as $cid) {
                        $st = get_user_meta((int)$cid, 'cosy_provider_status', true);
                        if ($st === 'active') {
                            $valid_cached[] = (int)$cid;
                        }
                    }
                    if (count($valid_cached) === count($cached_ids)) {
                        return self::fetch_provider_cards($valid_cached, $limit);
                    }
                }
            }
        }

        // 2. Parse Query Intent, Gender Constraints, Price, and Number Normalization
        $intent = self::parse_query_intent($query_text);
        $expanded_query = self::expand_query_numbers($query_text);

        // 3. Fetch Query Vector Embedding
        $query_vector = AIService::get_embedding($expanded_query);

        // 4. Load all provider embeddings from database
        $table_embeddings = $wpdb->prefix . 'provider_embeddings';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_embeddings'") !== $table_embeddings) {
            return [];
        }

        $all_vectors = $wpdb->get_results("SELECT provider_id, embedding FROM $table_embeddings");

        // Fallback: If embeddings table is empty, fetch all active provider IDs
        $active_provider_ids = [];
        if (!empty($all_vectors)) {
            foreach ($all_vectors as $row) {
                $pid = (int)$row->provider_id;
                $status = get_user_meta($pid, 'cosy_provider_status', true);
                if ($status === 'active') {
                    $active_provider_ids[] = $pid;
                }
            }
        }

        // If embeddings missing or incomplete, get all active providers directly
        if (empty($active_provider_ids)) {
            $all_users = get_users(['role' => 'provider', 'fields' => 'ID']);
            foreach ($all_users as $uid) {
                $status = get_user_meta($uid, 'cosy_provider_status', true);
                if ($status === 'active') {
                    $active_provider_ids[] = (int)$uid;
                }
            }
        }

        if (empty($active_provider_ids)) {
            return [];
        }

        // Build text lookup and keywords
        $provider_details = self::get_provider_text_lookup($active_provider_ids);
        $search_keywords  = self::get_search_keywords($query_text, $expanded_query);
        $vector_lookup    = [];
        if (!empty($all_vectors)) {
            foreach ($all_vectors as $vrow) {
                $vector_lookup[(int)$vrow->provider_id] = json_decode($vrow->embedding, true);
            }
        }

        // 5. Multi-Layer Hybrid Relevance Scoring & Contradiction Filtering
        $raw_matches       = [];
        $max_score         = 0.0;
        $has_exact_phrase  = false;
        $has_keyword_match = false;

        $clean_query = strtolower(trim($query_text));

        foreach ($active_provider_ids as $provider_id) {
            $p_text = isset($provider_details[$provider_id]) ? strtolower($provider_details[$provider_id]) : '';
            $p_gender = strtolower(get_user_meta($provider_id, 'gender', true) ?: '');

            // Strict Contradiction / Negative Matching Hard Filter (Point #7 & #11 of Spec Document)
            if ($intent['target_role'] === 'female') {
                // User explicitly asked for female / mum / mother
                // Hard-filter if provider is male OR if text explicitly establishes father / single father
                if ($p_gender === 'male' || preg_match('/\b(single father|solo father|father|dad|dads)\b/i', $p_text)) {
                    if (!preg_match('/\b(mum|mums|mother|mothers|female|woman)\b/i', $p_text)) {
                        continue;
                    }
                }
            } elseif ($intent['target_role'] === 'male') {
                // User explicitly asked for male / dad / father
                // Hard-filter if provider is female OR if text explicitly establishes mum / single mum
                if ($p_gender === 'female' || preg_match('/\b(single mum|solo mum|mother|mum|mums)\b/i', $p_text)) {
                    if (!preg_match('/\b(father|dad|dads|male|man)\b/i', $p_text)) {
                        continue;
                    }
                }
            }

            // Explicit Max Price Hard Filter (Point #4 & #8 of Spec Document)
            if ($intent['max_price'] > 0) {
                $services_table = $wpdb->prefix . 'provider_services';
                if ($wpdb->get_var("SHOW TABLES LIKE '$services_table'") === $services_table) {
                    $min_price = $wpdb->get_var($wpdb->prepare("SELECT MIN(price) FROM $services_table WHERE provider_id = %d", $provider_id));
                    $lowest_p = ($min_price !== null) ? floatval($min_price) : 0;
                    if ($lowest_p > $intent['max_price']) {
                        continue;
                    }
                }
            }

            // Layer A: Vector Cosine Similarity
            $vector_score = 0.0;
            if (!empty($query_vector) && isset($vector_lookup[$provider_id]) && is_array($vector_lookup[$provider_id])) {
                $vector_score = self::cosine_similarity($query_vector, $vector_lookup[$provider_id]);
            }

            // Layer B: Exact Phrase Boosting (+2.0 Score for Exact Match)
            $phrase_boost = 0.0;
            if (!empty($clean_query) && strpos($p_text, $clean_query) !== false) {
                $phrase_boost += 2.0;
                $has_exact_phrase = true;
            }

            // Specific multi-word phrase boosts (e.g., "single mum", "solo mum", "toddler sleep")
            foreach ($intent['phrases'] as $phrase) {
                if (strlen($phrase) >= 3 && strpos($p_text, $phrase) !== false) {
                    $phrase_boost += 1.5;
                    $has_exact_phrase = true;
                }
            }

            // Layer C: Keyword Matches (+0.25 per matching keyword)
            $keyword_boost = 0.0;
            if (!empty($p_text) && !empty($search_keywords)) {
                foreach ($search_keywords as $kw) {
                    if (strlen($kw) >= 2 && strpos($p_text, $kw) !== false) {
                        $keyword_boost += 0.25;
                        $has_keyword_match = true;
                    }
                }
            }

            // Layer D: Gender / Role Intent Alignment Multiplier
            $intent_multiplier = 1.0;
            if ($intent['target_role'] === 'female') {
                if (preg_match('/\b(single mum|solo mum|mum|mother)\b/i', $p_text)) {
                    $intent_multiplier = 1.5;
                }
            } elseif ($intent['target_role'] === 'male') {
                if (preg_match('/\b(single father|solo father|father|dad)\b/i', $p_text)) {
                    $intent_multiplier = 1.5;
                }
            }

            $composite_score = ($vector_score + $phrase_boost + $keyword_boost) * $intent_multiplier;

            if ($composite_score > $max_score) {
                $max_score = $composite_score;
            }

            $raw_matches[] = [
                'provider_id' => $provider_id,
                'score'       => $composite_score,
            ];
        }

        if (empty($raw_matches)) {
            return [];
        }

        // Out-of-Context Safety Filter:
        if ($max_score < 0.30 && !$has_exact_phrase && !$has_keyword_match) {
            return [];
        }

        // Dynamic Cutoff Floor
        $threshold = max(0.20, $max_score * 0.40);
        $matches   = [];
        foreach ($raw_matches as $item) {
            if ($item['score'] >= $threshold) {
                $matches[] = $item;
            }
        }

        if (empty($matches)) {
            return [];
        }

        // 6. Fetch Ratings, Reviews & Price for Hybrid Ranking Boosts
        $services_table = $wpdb->prefix . 'provider_services';
        $reviews_table  = $wpdb->prefix . 'cosy_provider_reviews';

        foreach ($matches as &$item) {
            $pid = $item['provider_id'];

            // Get average rating and review count
            $rating = 0;
            $review_count = 0;
            if ($wpdb->get_var("SHOW TABLES LIKE '$reviews_table'") === $reviews_table) {
                $r_data = $wpdb->get_row($wpdb->prepare("SELECT AVG(rating) as avg_rating, COUNT(id) as cnt FROM $reviews_table WHERE provider_id = %d AND status = 'approved'", $pid));
                if ($r_data) {
                    $rating = $r_data->avg_rating ? floatval($r_data->avg_rating) : 0;
                    $review_count = intval($r_data->cnt);
                }
            }

            // Get lowest price
            $price = 0;
            if ($wpdb->get_var("SHOW TABLES LIKE '$services_table'") === $services_table) {
                $min_price = $wpdb->get_var($wpdb->prepare("SELECT MIN(price) FROM $services_table WHERE provider_id = %d AND price > 0", $pid));
                $price = $min_price ? floatval($min_price) : 0;
            }

            // Apply Rating Boost: +0.05 per star, plus review count bonus
            $rating_boost = ($rating / 10.0) * 0.15 + (min(5, $review_count) * 0.02);

            // Apply Price Budget Fit Boost if user specified a budget
            $price_boost = 0.0;
            if ($intent['max_price'] > 0 && $price > 0) {
                if ($price <= $intent['max_price']) {
                    $price_boost = 0.20;
                } else {
                    $price_boost = -0.30; // Exceeds explicit budget
                }
            }

            $item['final_rank_score'] = $item['score'] + $rating_boost + $price_boost;
            $item['rating'] = $rating;
            $item['price']  = $price;
        }
        unset($item);

        // 7. Sort Candidate Pool by Final Rank Score (DESC)
        usort($matches, function ($a, $b) {
            if (abs($a['final_rank_score'] - $b['final_rank_score']) > 0.01) {
                return ($a['final_rank_score'] > $b['final_rank_score']) ? -1 : 1;
            }
            if ($a['rating'] !== $b['rating']) {
                return ($a['rating'] > $b['rating']) ? -1 : 1;
            }
            return 0;
        });

        // 8. Extract Provider IDs & Save to Cache Table
        $sorted_provider_ids = array_column($matches, 'provider_id');
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_cache'") === $table_cache) {
            $wpdb->replace(
                $table_cache,
                [
                    'query_hash'            => $query_hash,
                    'query_text'            => $query_text,
                    'matching_provider_ids' => json_encode($sorted_provider_ids),
                    'created_at'            => current_time('mysql'),
                ],
                ['%s', '%s', '%s', '%s']
            );
        }

        return self::fetch_provider_cards($sorted_provider_ids, $limit);
    }

    /**
     * Parse Query Intent, Role Targets, Explicit Price Limits, Age Taxonomy, and Dynamic N-Gram Phrases.
     */
    private static function parse_query_intent(string $query): array
    {
        $q = strtolower(trim($query));

        $intent = [
            'target_role' => 'any', // female, male, any
            'max_price'   => 0,
            'phrases'     => [],
            'synonyms'    => [],
        ];

        // 1. Comprehensive Gender & Role Synonym Mapping
        $female_roles = ['mum', 'mums', 'mom', 'moms', 'mother', 'mothers', 'mama', 'mamas', 'female', 'woman', 'women', 'single mum', 'solo mum', 'single mom', 'solo mom', 'single mother', 'solo mother'];
        $male_roles   = ['dad', 'dads', 'father', 'fathers', 'papa', 'papas', 'male', 'man', 'men', 'single dad', 'solo dad', 'single father', 'solo father'];

        foreach ($female_roles as $f_role) {
            if (preg_match('/\b' . preg_quote($f_role, '/') . '\b/i', $q)) {
                $intent['target_role'] = 'female';
                break;
            }
        }
        if ($intent['target_role'] === 'any') {
            foreach ($male_roles as $m_role) {
                if (preg_match('/\b' . preg_quote($m_role, '/') . '\b/i', $q)) {
                    $intent['target_role'] = 'male';
                    break;
                }
            }
        }

        // 2. Extract Explicit Price / Budget Constraint (e.g. "under £15", "under 20", "below 25")
        if (preg_match('/(?:under|below|less than|\<)\s*£?\s*(\d+(?:\.\d+)?)/i', $q, $pmatches)) {
            $intent['max_price'] = floatval($pmatches[1]);
        }

        // 3. Dynamic N-Gram Phrase Extractor (2-word, 3-word, 4-word phrases)
        $words = preg_split('/[\s,;.!?-]+/', $q, -1, PREG_SPLIT_NO_EMPTY);
        $w_count = count($words);
        $stopwords = ['and', 'the', 'for', 'with', 'you', 'our', 'are', 'was', 'were', 'who', 'this', 'that', 'have', 'has', 'looking', 'need', 'want', 'someone', 'help', 'guide', 'support'];
        
        for ($i = 0; $i < $w_count - 1; $i++) {
            if (!in_array($words[$i], $stopwords, true) || !in_array($words[$i + 1], $stopwords, true)) {
                $intent['phrases'][] = $words[$i] . ' ' . $words[$i + 1];
            }
            if ($i < $w_count - 2) {
                $intent['phrases'][] = $words[$i] . ' ' . $words[$i + 1] . ' ' . $words[$i + 2];
            }
        }

        // 4. Dynamic Service Titles Integration from WordPress Database
        if (post_type_exists('cosy_service')) {
            $service_posts = get_posts([
                'post_type'      => 'cosy_service',
                'posts_per_page' => 20,
                'post_status'    => 'publish',
            ]);
            foreach ($service_posts as $spost) {
                $stitle = is_object($spost) ? $spost->post_title : (string)$spost;
                $stitle_clean = strtolower($stitle);
                if (strlen($stitle_clean) >= 3 && strpos($q, $stitle_clean) !== false) {
                    $intent['phrases'][] = $stitle_clean;
                }
            }
        }

        $intent['phrases'] = array_values(array_unique($intent['phrases']));

        return $intent;
    }

    /**
     * Compute Cosine Similarity between two numeric vector arrays.
     */
    public static function cosine_similarity(array $vecA, array $vecB): float
    {
        $count = count($vecA);
        if ($count === 0 || $count !== count($vecB)) {
            return 0.0;
        }

        $dotProduct = 0.0;
        $normA      = 0.0;
        $normB      = 0.0;

        for ($i = 0; $i < $count; $i++) {
            $a = (float)$vecA[$i];
            $b = (float)$vecB[$i];

            $dotProduct += $a * $b;
            $normA      += $a * $a;
            $normB      += $b * $b;
        }

        if ($normA == 0.0 || $normB == 0.0) {
            return 0.0;
        }

        return $dotProduct / (sqrt($normA) * sqrt($normB));
    }

    /**
     * Format and return provider profile details for rendering search cards.
     */
    private static function fetch_provider_cards(array $provider_ids, int $limit): array
    {
        if (empty($provider_ids)) {
            return [];
        }

        global $wpdb;
        $services_table = $wpdb->prefix . 'provider_services';
        $reviews_table  = $wpdb->prefix . 'cosy_provider_reviews';

        $cards = [];
        $provider_ids = array_slice($provider_ids, 0, $limit);

        foreach ($provider_ids as $user_id) {
            $user = get_userdata($user_id);
            if (!$user) {
                continue;
            }

            // Ensure provider is active
            $status = get_user_meta($user_id, 'cosy_provider_status', true);
            if ($status !== 'active') {
                continue;
            }

            $acct_status = get_user_meta($user_id, 'account_status', true);
            if (!empty($acct_status) && $acct_status !== 'active') {
                continue;
            }

            $first_name    = get_user_meta($user_id, 'first_name', true) ?: $user->display_name;
            $last_name     = get_user_meta($user_id, 'last_name', true) ?: '';
            $bio           = get_user_meta($user_id, 'description', true) ?: '';
            $profile_image = get_user_meta($user_id, 'profile_image', true) ?: 'https://i.pravatar.cc/300?img=' . ($user_id % 70);

            // Fetch rating
            $rating = 0;
            if ($wpdb->get_var("SHOW TABLES LIKE '$reviews_table'") === $reviews_table) {
                $avg_rating = $wpdb->get_var($wpdb->prepare("SELECT AVG(rating) FROM $reviews_table WHERE provider_id = %d AND status = 'approved'", $user_id));
                $rating = $avg_rating ? round(floatval($avg_rating), 1) : 0;
            }

            // Fetch service title & lowest positive price (e.g., £9.00 starting rate)
            $service_name = '';
            $price = '0.00';
            if ($wpdb->get_var("SHOW TABLES LIKE '$services_table'") === $services_table) {
                $srow = $wpdb->get_row($wpdb->prepare("SELECT service, price FROM $services_table WHERE provider_id = %d AND price > 0 ORDER BY price ASC LIMIT 1", $user_id));
                if (!$srow) {
                    $srow = $wpdb->get_row($wpdb->prepare("SELECT service, price FROM $services_table WHERE provider_id = %d ORDER BY price ASC LIMIT 1", $user_id));
                }
                if ($srow) {
                    $service_name = $srow->service;
                    $price        = number_format(floatval($srow->price), 2);
                }
            }

            $intro_video = get_user_meta($user_id, 'introduction_video', true) ?: '';

            // Structured Facts for Debug Explanation (Point #16)
            $facts = get_user_meta($user_id, 'cosy_profile_facts', true) ?: [];

            $card_data = [
                'ID'                 => $user_id,
                'provider_id'        => $user_id,
                'first_name'         => $first_name,
                'last_name'          => $last_name,
                'name'               => trim("$first_name $last_name") ?: $user->display_name,
                'username'           => $user->user_login,
                'email'              => $user->user_email,
                'description'        => $bio,
                'bio'                => $bio,
                'profile_image'      => $profile_image,
                'rating'             => $rating,
                'service'            => $service_name,
                'price'              => $price,
                'introduction_video' => $intro_video,
                'profile_url'        => get_author_posts_url($user_id),
            ];

            // Debug Explanation Mode (Point #16 of Spec Document)
            if (isset($_GET['cosy_search_debug']) && current_user_can('manage_options')) {
                $card_data['debug_info'] = [
                    'facts'          => $facts,
                    'gender_meta'    => get_user_meta($user_id, 'gender', true),
                    'provider_status' => $status,
                ];
            }

            $cards[] = $card_data;
        }

        return $cards;
    }

    /**
     * Expand search query text to include both digits and word representations.
     * E.g., "8 children" -> "8 children eight children".
     */
    private static function expand_query_numbers(string $query): string
    {
        $map = [
            '0'  => 'zero',
            '1'  => 'one',
            '2'  => 'two',
            '3'  => 'three',
            '4'  => 'four',
            '5'  => 'five',
            '6'  => 'six',
            '7'  => 'seven',
            '8'  => 'eight',
            '9'  => 'nine',
            '10' => 'ten',
            '18' => 'eighteen',
        ];

        $expanded_parts = [$query];
        foreach ($map as $digit => $word) {
            if (preg_match("/\b" . preg_quote($digit, '/') . "\b/i", $query)) {
                $expanded_parts[] = preg_replace("/\b" . preg_quote($digit, '/') . "\b/i", $word, $query);
            }
            if (preg_match("/\b" . preg_quote($word, '/') . "\b/i", $query)) {
                $expanded_parts[] = preg_replace("/\b" . preg_quote($word, '/') . "\b/i", $digit, $query);
            }
        }

        return implode(' ', array_unique($expanded_parts));
    }

    /**
     * Extract meaningful search keywords from original and expanded query strings.
     */
    private static function get_search_keywords(string $query, string $expanded_query): array
    {
        $all_text  = strtolower($query . ' ' . $expanded_query);
        $words     = preg_split('/[\s,;.!?-]+/', $all_text, -1, PREG_SPLIT_NO_EMPTY);
        $stopwords = ['and', 'the', 'for', 'with', 'you', 'our', 'are', 'was', 'were', 'who', 'this', 'that', 'have', 'has', 'looking', 'need', 'want', 'someone', 'help', 'guide', 'support'];
        $keywords  = [];

        foreach ($words as $w) {
            if (strlen($w) >= 2 && !in_array($w, $stopwords, true)) {
                $keywords[] = $w;
            }
        }

        return array_unique($keywords);
    }

    /**
     * Build lookup array of combined provider text (Name, Services, Bio) for keyword boost matching.
     */
    private static function get_provider_text_lookup(array $provider_ids): array
    {
        if (empty($provider_ids)) {
            return [];
        }

        global $wpdb;
        $services_table = $wpdb->prefix . 'provider_services';
        $lookup = [];

        foreach ($provider_ids as $id) {
            $user = get_userdata($id);
            if (!$user) {
                continue;
            }

            $fname = get_user_meta($id, 'first_name', true) ?: $user->display_name;
            $lname = get_user_meta($id, 'last_name', true) ?: '';
            $bio   = get_user_meta($id, 'description', true) ?: '';

            $services = [];
            if ($wpdb->get_var("SHOW TABLES LIKE '$services_table'") === $services_table) {
                $rows = $wpdb->get_results($wpdb->prepare("SELECT service FROM $services_table WHERE provider_id = %d", $id));
                foreach ($rows as $r) {
                    if (!empty($r->service)) {
                        $services[] = $r->service;
                    }
                }
            }

            $lookup[$id] = trim("$fname $lname " . implode(' ', $services) . " $bio");
        }

        return $lookup;
    }
}
