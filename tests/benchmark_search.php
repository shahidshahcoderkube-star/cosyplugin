<?php
/**
 * Automated Search Accuracy Benchmark & Regression Test Suite
 * Run via CLI: php wp-content/plugins/cosy-appointments/tests/benchmark_search.php
 */

require_once __DIR__ . '/../../../../wp-load.php';

use Cosy\Appointments\AI\SearchEngine;

echo "========================================================================\n";
echo " 🧪 COSYCHATS AI SEARCH ACCURACY BENCHMARK & EVALUATION TEST SUITE\n";
echo "========================================================================\n\n";

$test_suite = [
    [
        'category' => 'Domain & Synonym Matching',
        'query'    => 'adoption experience',
        'expected_any' => ['Maria S.', 'Stephen K.', 'Benjamin H.', 'Claire R.', 'David M.'],
        'forbidden'    => ['Alex H.', 'Tom K.', 'Zoe F.'],
        'target_gender' => 'any',
    ],
    [
        'category' => 'Typo / Spelling Auto-Correction',
        'query'    => 'adoptoin experience',
        'expected_any' => ['Maria S.', 'Stephen K.', 'Benjamin H.'],
        'forbidden'    => ['Alex H.'],
        'target_gender' => 'any',
    ],
    [
        'category' => 'Gender Hard-Filter (Fathers Only)',
        'query'    => 'father with teenage experience',
        'expected_any' => ['Marcus K.', 'Charlotte D.', 'Gareth T.'],
        'forbidden'    => ['Helen R.', 'Lisa B.', 'Fiona L.', 'Maria S.'],
        'target_gender' => 'male',
    ],
    [
        'category' => 'Gender Hard-Filter (Mothers Only)',
        'query'    => 'mum with baby loss experience',
        'expected_any' => ['Chloe V.', 'Victoria N.', 'Hannah G.', 'Sieii A.'],
        'forbidden'    => ['Andrew L.', 'Matthew E.', 'Marcus K.'],
        'target_gender' => 'female',
    ],
    [
        'category' => 'Multi-Topic Joint Matching (Baby Loss + IVF)',
        'query'    => 'mum with baby loss and IVF experience',
        'expected_any' => ['Chloe V.', 'Victoria N.', 'Maria S.', 'Sophie D.'],
        'forbidden'    => ['Marcus K.', 'Gareth T.', 'Tom K.'],
        'target_gender' => 'female',
    ],
    [
        'category' => 'Child Age & ADHD / Neurodivergent',
        'query'    => 'mum of primary school kids with ADHD',
        'expected_any' => ['Laura M.', 'Sarah K.', 'Amanda D.'],
        'forbidden'    => ['Marcus K.', 'Tom K.', 'Chloe V.'],
        'target_gender' => 'female',
    ],
    [
        'category' => 'Budget & Low Cost Constraint',
        'query'    => 'cheap IVF parent guide under 20',
        'expected_any' => ['Paul K.', 'Ttunnei F.', 'Jessica M.', 'Rachel P.'],
        'forbidden'    => [],
        'target_gender' => 'any',
    ],
];

$passed_count = 0;
$total_count  = count($test_suite);

foreach ($test_suite as $idx => $test) {
    $num = $idx + 1;
    echo "Test #$num: [{$test['category']}]\n";
    echo "  Query: \"{$test['query']}\"\n";

    $start_time = microtime(true);
    $results    = SearchEngine::search($test['query']);
    $duration   = round((microtime(true) - $start_time) * 1000, 2);

    $matched_names = array_column($results, 'name');
    $test_passed   = true;
    $failure_reasons = [];

    // Check Expected Providers
    $found_expected = 0;
    foreach ($test['expected_any'] as $exp_name) {
        if (in_array($exp_name, $matched_names, true)) {
            $found_expected++;
        }
    }
    if ($found_expected === 0 && !empty($test['expected_any'])) {
        $test_passed = false;
        $failure_reasons[] = "None of the expected providers (" . implode(', ', $test['expected_any']) . ") were returned.";
    }

    // Check Forbidden Providers
    foreach ($test['forbidden'] as $forb_name) {
        if (in_array($forb_name, $matched_names, true)) {
            $test_passed = false;
            $failure_reasons[] = "Forbidden provider '$forb_name' was incorrectly included in results.";
        }
    }

    // Check Gender Constraint
    if ($test['target_gender'] !== 'any') {
        foreach ($results as $r) {
            $p_gender = strtolower(get_user_meta($r['ID'], 'gender', true) ?: '');
            if (!empty($p_gender) && $p_gender !== $test['target_gender']) {
                $test_passed = false;
                $failure_reasons[] = "Gender mismatch: Provider '{$r['name']}' has gender '$p_gender' (expected '{$test['target_gender']}').";
            }
        }
    }

    if ($test_passed) {
        $passed_count++;
        echo "  Status:  ✅ PASSED ($duration ms)\n";
        echo "  Results: " . implode(', ', array_slice($matched_names, 0, 4)) . (count($matched_names) > 4 ? '...' : '') . "\n";
    } else {
        echo "  Status:  ❌ FAILED ($duration ms)\n";
        foreach ($failure_reasons as $reason) {
            echo "    ⚠️ $reason\n";
        }
    }
    echo "------------------------------------------------------------------------\n";
}

$accuracy_pct = round(($passed_count / $total_count) * 100, 1);

echo "\n========================================================================\n";
echo " 📊 FINAL EVALUATION SCORE: $passed_count / $total_count PASSED ($accuracy_pct% Accuracy)\n";
echo "========================================================================\n";
