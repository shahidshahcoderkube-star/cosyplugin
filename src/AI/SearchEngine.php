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
     * Perform AI Semantic Search for a user query.
     *
     * @param string $query_text Search input query string
     * @param int $limit Maximum number of provider profiles to return (default 6)
     * @return array List of provider profile details
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

        // 2. Fetch Query Vector Embedding
        $query_vector = AIService::get_embedding($query_text);
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

        // 4. Calculate Cosine Similarity Scores
        $raw_matches = [];
        $max_score   = 0.0;

        foreach ($all_vectors as $row) {
            $provider_id = (int)$row->provider_id;
            $vector = json_decode($row->embedding, true);
            if (empty($vector) || !is_array($vector)) {
                continue;
            }

            $score = self::cosine_similarity($query_vector, $vector);
            if ($score > $max_score) {
                $max_score = $score;
            }
            $raw_matches[] = [
                'provider_id' => $provider_id,
                'score'       => $score,
            ];
        }

        // Relative Relevance Threshold: Only return profiles within close range of top match score
        $threshold = max(0.60, $max_score - 0.045);
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
                'first_name'         => trim("$first_name $last_name"),
                'name'               => trim("$first_name $last_name"),
                'username'           => $user->user_login,
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
}
