<?php

namespace Cosy\Appointments\AI;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * SearchEngine Class
 * Executes vector search using Cosine Similarity, Caching, and Hybrid Sorting (Relevance + Rating + Price).
 */
class SearchEngine
{
    /**
     * PERFORMS AI SEMANTIC VECTOR SEARCH
     * 
     * USE CASE:
     * Executes natural language semantic search over provider profiles using vector embeddings and cosine similarity.
     * 
     * HOW TO USE:
     * $results = SearchEngine::search("looking for a child counselor in London", 6);
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Checks local MySQL search cache for query hash matches.
     * 2. Generates vector embedding vector from OpenAI/Gemini via AIService.
     * 3. Calculates cosine similarity across indexed provider profile embeddings.
     * 4. Ranks profiles using hybrid scoring algorithm (relevance + rating + price).
     * 5. Returns array of formatted provider cards.
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
                if (is_array($cached_ids)) {
                    return self::fetch_provider_cards($cached_ids, $limit);
                }
            }
        }

        // Expand numbers in search query (e.g., "8 children" -> "8 children eight children")
        $expanded_query = self::expand_query_numbers($query_text);

        // 2. Fetch Query Vector Embedding
        $query_vector = AIService::get_embedding($expanded_query);
        if (empty($query_vector)) {
            return [];
        }

        // 3. Load all provider embeddings from database
        $table_embeddings = $wpdb->prefix . 'provider_embeddings';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_embeddings'") !== $table_embeddings) {
            return [];
        }

        $all_vectors = $wpdb->get_results("SELECT provider_id, embedding FROM $table_embeddings");
        if (empty($all_vectors)) {
            return [];
        }

        // Filter embeddings to include ONLY active service providers
        $active_vectors = [];
        foreach ($all_vectors as $row) {
            $pid = (int)$row->provider_id;
            $status = get_user_meta($pid, 'cosy_provider_status', true);
            if ($status === 'active') {
                $active_vectors[] = $row;
            }
        }
        $all_vectors = $active_vectors;
        if (empty($all_vectors)) {
            return [];
        }

        // Pre-fetch provider text details for exact keyword boosting
        $provider_details = self::get_provider_text_lookup(array_column($all_vectors, 'provider_id'));
        $search_keywords  = self::get_search_keywords($query_text, $expanded_query);

        // 4. Calculate Cosine Similarity Scores + Keyword Boosting
        $raw_matches       = [];
        $max_score         = 0.0;
        $has_keyword_match = false;

        foreach ($all_vectors as $row) {
            $provider_id = (int)$row->provider_id;
            $vector = json_decode($row->embedding, true);
            if (empty($vector) || !is_array($vector)) {
                continue;
            }

            $base_score = self::cosine_similarity($query_vector, $vector);
            $boost      = 0.0;

            // Check exact keyword matches against provider details
            $p_text = isset($provider_details[$provider_id]) ? strtolower($provider_details[$provider_id]) : '';
            if (!empty($p_text) && !empty($search_keywords)) {
                foreach ($search_keywords as $word) {
                    if (strlen($word) >= 2 && strpos($p_text, $word) !== false) {
                        $boost += 0.25;
                        $has_keyword_match = true;
                    }
                }
            }

            $final_score = min(1.0, $base_score + $boost);

            if ($final_score > $max_score) {
                $max_score = $final_score;
            }
            $raw_matches[] = [
                'provider_id' => $provider_id,
                'score'       => $final_score,
            ];
        }

        // Out-of-Context Safety Filter:
        // If maximum score across all profiles is below absolute floor (0.38) AND no exact keywords matched,
        // classify as out-of-context (e.g. "buy a car") and return 0 results instead of random profiles.
        if ($max_score < 0.38 && !$has_keyword_match) {
            return [];
        }

        // Dynamic Relevance Threshold: Cutoff floor of 0.38 or 75% of max score
        $threshold = max(0.38, $max_score * 0.75);
        $matches   = [];
        foreach ($raw_matches as $item) {
            if ($item['score'] >= $threshold) {
                $matches[] = $item;
            }
        }

        if (empty($matches)) {
            return [];
        }

        // 5. Fetch Provider Meta (Rating & Price) for Hybrid Ranking
        $services_table = $wpdb->prefix . 'provider_services';
        $reviews_table  = $wpdb->prefix . 'cosy_provider_reviews';

        foreach ($matches as &$item) {
            $pid = $item['provider_id'];

            // Get average rating
            $rating = 0;
            if ($wpdb->get_var("SHOW TABLES LIKE '$reviews_table'") === $reviews_table) {
                $avg_rating = $wpdb->get_var($wpdb->prepare("SELECT AVG(rating) FROM $reviews_table WHERE provider_id = %d", $pid));
                $rating = $avg_rating ? floatval($avg_rating) : 0;
            }

            // Get lowest price
            $price = 0;
            if ($wpdb->get_var("SHOW TABLES LIKE '$services_table'") === $services_table) {
                $min_price = $wpdb->get_var($wpdb->prepare("SELECT MIN(price) FROM $services_table WHERE provider_id = %d AND price > 0", $pid));
                $price = $min_price ? floatval($min_price) : 0;
            }

            $item['rating'] = $rating;
            $item['price']  = $price;
        }
        unset($item);

        // 6. Hybrid Sorting: Relevance -> Rating (DESC) -> Price (ASC)
        usort($matches, function ($a, $b) {
            // First priority: Similarity Score (higher similarity wins if diff > 0.1)
            if (abs($a['score'] - $b['score']) > 0.10) {
                return ($a['score'] > $b['score']) ? -1 : 1;
            }

            // Second priority: Rating (5-star down to 1-star)
            if ($a['rating'] !== $b['rating']) {
                return ($a['rating'] > $b['rating']) ? -1 : 1;
            }

            // Third priority: Price (Lower price wins)
            if ($a['price'] !== $b['price']) {
                return ($a['price'] < $b['price']) ? -1 : 1;
            }

            return 0;
        });

        // 7. Extract Provider IDs & Save to Cache Table
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
                $avg_rating = $wpdb->get_var($wpdb->prepare("SELECT AVG(rating) FROM $reviews_table WHERE provider_id = %d", $user_id));
                $rating = $avg_rating ? round(floatval($avg_rating), 1) : 0;
            }

            // Fetch service title & lowest price
            $service_name = '';
            $price = '0.00';
            if ($wpdb->get_var("SHOW TABLES LIKE '$services_table'") === $services_table) {
                $srow = $wpdb->get_row($wpdb->prepare("SELECT service, price FROM $services_table WHERE provider_id = %d ORDER BY price ASC LIMIT 1", $user_id));
                if ($srow) {
                    $service_name = $srow->service;
                    $price        = number_format(floatval($srow->price), 2);
                }
            }

            $intro_video = get_user_meta($user_id, 'introduction_video', true) ?: '';

            $cards[] = [
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
        $stopwords = ['and', 'the', 'for', 'with', 'you', 'our', 'are', 'was', 'were', 'who', 'this', 'that', 'have', 'has'];
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

