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
     * Fetch vector embedding for a given input text string.
     *
     * @param string $text
     * @return array Array of float values (vector embedding) or empty array on failure.
     */
    public static function get_embedding(string $text): array
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

        if ($provider === 'openai') {
            return self::get_openai_embedding($text, $api_key);
        } else {
            return self::get_gemini_embedding($text, $api_key);
        }
    }

    /**
     * Call Google Gemini text-embedding-004 endpoint.
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
}
