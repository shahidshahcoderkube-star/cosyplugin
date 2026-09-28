<?php
/**
 * Interactive Search Tester CLI Tool
 *
 * Usage:
 *   php test-search.php "My child has ADHD"
 *   php test-search.php "single mum"
 *   php test-search.php "new baby"
 *   php test-search.php "new car"
 *
 * Or run without arguments for interactive prompt:
 *   php test-search.php
 */

define('WP_USE_THEMES', false);
require_once __DIR__ . '/wp-load.php';

use Cosy\Appointments\AI\SearchEngine;

// Read query from command-line argument or prompt user
$query = $argv[1] ?? null;

if (empty($query)) {
    echo "\n=======================================================\n";
    echo "       COSYCHAT AI SEARCH INSTANT CLI TESTER           \n";
    echo "=======================================================\n";
    echo "Enter search query: ";
    $handle = fopen("php://stdin", "r");
    $query = trim(fgets($handle));
    fclose($handle);
}

if (empty($query)) {
    echo "No query entered. Exiting.\n";
    exit(0);
}

echo "\n-------------------------------------------------------\n";
echo "Testing Query: \"$query\"\n";
echo "-------------------------------------------------------\n";

// Execute search
$start = microtime(true);
$results = SearchEngine::search_detailed($query, 6, 1);
$elapsed = round((microtime(true) - $start) * 1000, 2);

$has_match   = !empty($results['has_match']);
$is_fallback = !empty($results['is_fallback']);
$total       = $results['total_results'] ?? count($results['results']);
$pages       = $results['total_pages'] ?? 1;

echo "Status      : " . ($has_match ? "MATCH FOUND [OK]" : "NO DIRECT MATCH (Fallback Banner Shown)") . "\n";
echo "Is Fallback : " . ($is_fallback ? "YES" : "NO") . "\n";
echo "Total Found : $total parent(s) across $pages page(s)\n";
echo "Search Time : {$elapsed} ms\n\n";

if (!empty($results['results'])) {
    echo "--- Top Results (Page 1) ---\n";
    foreach ($results['results'] as $idx => $card) {
        $num = $idx + 1;
        $name = $card['name'] ?? 'Unknown';
        $role = $card['role'] ?? '';
        $pid  = $card['provider_id'] ?? 0;
        $gender = get_user_meta($pid, 'gender', true) ?: 'unspecified';
        $bio = get_user_meta($pid, 'description', true) ?: '';
        $bio_snippet = substr(trim(preg_replace('/\s+/', ' ', $bio)), 0, 110);

        echo " #$num: $name ($gender) - $role\n";
        echo "     Bio: \"$bio_snippet...\"\n";
        if (!empty($card['service'])) {
            echo "     Service: \"{$card['service']}\"\n";
        }
        echo "\n";
    }
}

if ($is_fallback) {
    echo "[!] Fallback Banner Details:\n";
    echo "    Title   : " . ($results['no_match_title'] ?? '') . "\n";
    echo "    Subtitle: " . ($results['no_match_subtitle'] ?? '') . "\n";
}

echo "=======================================================\n";
