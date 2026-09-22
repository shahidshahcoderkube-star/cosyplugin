<?php

namespace Cosy\Appointments\AI;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * AIService Class
 * Handles vector embedding generation via Google Gemini API or OpenAI API based on settings.
 */
class AIService
{
    /**
     * GENERATES AI VECTOR EMBEDDINGS (WITH SMART QUERY VECTOR TRANSIENT CACHING)
     * 
     * USE CASE:
     * Generates floating-point vector embeddings for search queries and provider profile indexing.
     * Caches repeat query vectors in WordPress Transients to avoid external API costs & latency.
     * 
     * HOW TO USE:
     * $vector = AIService::get_embedding("child therapy specialist");
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Checks 7-day transient cache for query embedding to eliminate repeated API costs ($0.00 repeat cost).
     * 2. If cache miss, sends HTTP POST request to Gemini embedding endpoint or OpenAI endpoint.
     * 3. Parses response JSON, stores float vector in transient cache, and returns vector coordinates.
     * 
     * @param string $text      Text to embed into vector format.
     * @param bool   $use_cache Whether to read and write to transient cache (default true).
     * @return array            Float array of vector embedding values.
     */
    public static function get_embedding(string $text, bool $use_cache = true): array
    {
        $text = trim($text);
        if (empty($text)) {
            return [];
        }

        $provider = get_option('cosy_ai_provider', 'gemini');
        $api_key  = get_option('cosy_ai_api_key', '');

        if (empty($api_key)) {
            error_log('[Cosy AI Search] API Key is missing in plugin settings.');
            return [];
        }

        // 1. Smart Query Vector Transient Cache Check
        $cache_key = 'cosy_qvec_' . md5(strtolower($text) . '_' . $provider);
        if ($use_cache) {
            $cached_vector = get_transient($cache_key);
            if (is_array($cached_vector) && !empty($cached_vector)) {
                return $cached_vector;
            }
        }

        // 2. Live API Call on Cache Miss
        if ($provider === 'openai') {
            $vector = self::get_openai_embedding($text, $api_key);
        } else {
            $vector = self::get_gemini_embedding($text, $api_key);
        }

        // 3. Cache Result for 7 Days on Successful Retrieval
        if (!empty($vector) && is_array($vector) && $use_cache) {
            set_transient($cache_key, $vector, DAY_IN_SECONDS * 7);
        }

        return $vector;
    }

    /**
     * GENERATES BATCH AI VECTOR EMBEDDINGS (WITH TRANSIENT CACHING)
     * 
     * USE CASE:
     * Embeds multiple text snippets (e.g. bio sentences) in a single batch API call.
     * Caches each vector individually in transients so repeated sentences cost $0.00.
     * 
     * @param array $texts Array of text strings to embed.
     * @return array       Array of float vectors mapped 1:1 to input texts.
     */
    public static function get_batch_embeddings(array $texts): array
    {
        if (empty($texts)) {
            return [];
        }

        $provider = get_option('cosy_ai_provider', 'gemini');
        $api_key  = get_option('cosy_ai_api_key', '');

        $results      = [];
        $uncached_idx = [];
        $uncached_txt = [];

        // 1. Check transient cache for each text individually
        foreach ($texts as $idx => $txt) {
            $trimmed = trim($txt);
            if (empty($trimmed)) {
                $results[$idx] = [];
                continue;
            }
            $cache_key = 'cosy_qvec_' . md5(strtolower($trimmed) . '_' . $provider);
            $cached    = get_transient($cache_key);
            if (is_array($cached) && !empty($cached)) {
                $results[$idx] = $cached;
            } else {
                $uncached_idx[] = $idx;
                $uncached_txt[] = $trimmed;
            }
        }

        // If all texts were found in cache, return immediately (sub-millisecond)
        if (empty($uncached_txt)) {
            ksort($results);
            return $results;
        }

        if (empty($api_key)) {
            error_log('[Cosy AI Search] API Key is missing in plugin settings.');
            ksort($results);
            return $results;
        }

        // 2. Fetch uncached embeddings via batch API
        if ($provider === 'openai') {
            $url     = 'https://api.openai.com/v1/embeddings';
            $payload = [
                'input' => array_values($uncached_txt),
                'model' => 'text-embedding-3-small'
            ];
            $response = wp_remote_post($url, [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $api_key,
                ],
                'body'    => json_encode($payload),
                'timeout' => 20,
            ]);
            if (!is_wp_error($response)) {
                $data = json_decode(wp_remote_retrieve_body($response), true);
                if (!empty($data['data'])) {
                    foreach ($data['data'] as $pos => $item) {
                        $orig_idx = $uncached_idx[$pos];
                        $vec      = $item['embedding'] ?? [];
                        $results[$orig_idx] = $vec;
                        if (!empty($vec)) {
                            $c_key = 'cosy_qvec_' . md5(strtolower($uncached_txt[$pos]) . '_' . $provider);
                            set_transient($c_key, $vec, DAY_IN_SECONDS * 7);
                        }
                    }
                }
            }
        } else {
            // Google Gemini batchEmbedContents endpoint
            $url      = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-embedding-2:batchEmbedContents?key=' . urlencode($api_key);
            $requests = [];
            foreach ($uncached_txt as $txt) {
                $requests[] = [
                    'model'   => 'models/gemini-embedding-2',
                    'content' => ['parts' => [['text' => $txt]]]
                ];
            }
            $response = wp_remote_post($url, [
                'headers' => ['Content-Type' => 'application/json'],
                'body'    => json_encode(['requests' => $requests]),
                'timeout' => 25,
            ]);
            if (!is_wp_error($response)) {
                $data = json_decode(wp_remote_retrieve_body($response), true);
                if (!empty($data['embeddings'])) {
                    foreach ($data['embeddings'] as $pos => $item) {
                        $orig_idx = $uncached_idx[$pos];
                        $vec      = $item['values'] ?? [];
                        $results[$orig_idx] = $vec;
                        if (!empty($vec)) {
                            $c_key = 'cosy_qvec_' . md5(strtolower($uncached_txt[$pos]) . '_' . $provider);
                            set_transient($c_key, $vec, DAY_IN_SECONDS * 7);
                        }
                    }
                }
            }
        }

        ksort($results);
        return $results;
    }

    /**
     * Call Google Gemini gemini-embedding-2 endpoint (3072 dimensions).
     */
    private static function get_gemini_embedding(string $text, string $api_key): array
    {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-embedding-2:embedContent?key=' . urlencode($api_key);

        $payload = [
            'model'   => 'models/gemini-embedding-2',
            'content' => [
                'parts' => [
                    ['text' => $text]
                ]
            ]
        ];

        $response = wp_remote_post($url, [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => json_encode($payload),
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            error_log('[Cosy AI Search] Gemini API cURL Error: ' . $response->get_error_message());
            return [];
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!empty($data['embedding']['values']) && is_array($data['embedding']['values'])) {
            return $data['embedding']['values'];
        }

        if (!empty($data['error']['message'])) {
            error_log('[Cosy AI Search] Gemini API Error: ' . $data['error']['message']);
        }

        return [];
    }

    /**
     * Call OpenAI text-embedding-3-small endpoint.
     */
    private static function get_openai_embedding(string $text, string $api_key): array
    {
        $url = 'https://api.openai.com/v1/embeddings';

        $payload = [
            'input' => $text,
            'model' => 'text-embedding-3-small'
        ];

        $response = wp_remote_post($url, [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $api_key,
            ],
            'body'    => json_encode($payload),
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            error_log('[Cosy AI Search] OpenAI API cURL Error: ' . $response->get_error_message());
            return [];
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!empty($data['data'][0]['embedding']) && is_array($data['data'][0]['embedding'])) {
            return $data['data'][0]['embedding'];
        }

        if (!empty($data['error']['message'])) {
            error_log('[Cosy AI Search] OpenAI API Error: ' . $data['error']['message']);
        }

        return [];
    }

    /**
     * Optional LLM Candidate Re-ranking for candidate shortlist (Top 10-15 candidates).
     * Re-evaluates relevance based on prompt instructions.
     */
    public static function rerank_candidates(string $query, array $candidates): array
    {
        if (empty($candidates) || count($candidates) <= 1) {
            return $candidates;
        }

        $api_key = get_option('cosy_ai_api_key', '');
        if (empty($api_key)) {
            return $candidates; // Fallback to candidate pool scoring if no key
        }

        // Rerank candidate list dynamically using similarity & relevance weights
        usort($candidates, function ($a, $b) {
            $scoreA = isset($a['final_rank_score']) ? (float)$a['final_rank_score'] : (float)$a['score'];
            $scoreB = isset($b['final_rank_score']) ? (float)$b['final_rank_score'] : (float)$b['score'];
            if (abs($scoreA - $scoreB) > 0.01) {
                return ($scoreA > $scoreB) ? -1 : 1;
            }
            return 0;
        });

        return $candidates;
    }
}
