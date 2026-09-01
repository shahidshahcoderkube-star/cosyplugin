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
     * USE CASE:
     * Used by the frontend search bar, AJAX live search endpoints, REST APIs, and conversational finder
     * to match users with the most relevant and empathetic parent guides/mentors. Handles diverse inputs:
     * - Short queries (e.g., "sleep", "stress", "adhd")
     * - Multi-topic & constraint searches (e.g., "mum with baby loss and IVF experience under 20")
     * - Conversational & emotional crisis queries (e.g., "i feel like im losing my mind", "need someone to listen")
     * - Typos, spelling mistakes, and Hinglish queries (e.g., "relation ship problem", "kisi se bat karni he")
     *
     * HOW TO USE:
     * ```php
     * use Cosy\Appointments\AI\SearchEngine;
     *
     * // 1. Basic search (returns top 6 provider cards by default)
     * $results = SearchEngine::search('mum of primary school kids with ADHD');
     *
     * // 2. Search with custom result limit (e.g., 4 cards)
     * $results = SearchEngine::search('cheap IVF parent guide under 20', 4);
     *
     * // 3. Iterate over returned provider cards
     * foreach ($results as $guide) {
     *     $id       = $guide['id'];
     *     $name     = $guide['name'];
     *     $service  = $guide['service'];
     *     $price    = $guide['price'];     // Formatted price string e.g. "15.00"
     *     $bio      = $guide['bio'];       // Contextual bio highlight
     *     $avatar   = $guide['avatar_url'];
     *     $rating   = $guide['rating'];    // Float rating e.g. 5.0
     * }
     * ```
     *
     * WHAT IT DOES INTERNALLY:
     * 1. Query Pre-processing & Typo Correction:
     *    - Strips special characters and fixes spelling errors/typos against domain vocabulary.
     *    - Normalizes colloquial phrases and Hinglish inputs to standard conversational English.
     * 2. Intent & Constraint Extraction (`parse_query_intent`):
     *    - Detects gender/role filters (female/mum vs male/father).
     *    - Detects budget caps (e.g., "under 20" -> max price £20.00).
     *    - Detects target child age ranges (e.g., "5-year-old", "teenager").
     *    - Detects open-ended emotional distress vs domain-specific queries.
     * 3. Vector Embedding Generation:
     *    - Generates 3072-dimension OpenAI semantic embeddings (`text-embedding-3-large`) with persistent DB caching.
     * 4. Multi-Layer Candidate Filtering:
     *    - Hard-filters out inactive profiles, gender contradictions, and over-budget guides.
     *    - Verifies genuine lived experience proof in bio narratives for registered domain topics.
     * 5. Hybrid Scoring Engine:
     *    - Vector Semantic Similarity (Cosine score baseline).
     *    - Exact Phrase Boost (+1.5) & BM25 Keyword Match Boost (+0.25 per matched keyword).
     *    - Age Proximity Boost (+3.0 for exact age, +2.0 for close age range).
     *    - Multi-Topic Joint Intersection Bonus (+3.5 for dual-experience matching, e.g. Baby Loss + IVF).
     * 6. Safety Filter & Dynamic Ranking:
     *    - Guards against irrelevant/out-of-domain queries.
     *    - Blends composite relevance scores with guide ratings and budget fit.
     * 7. Profile Card Hydration (`fetch_provider_cards`):
     *    - Fetches user metadata, services, hourly rates, and extracts relevant bio snippets.
     *
     * @param string $query_text Natural language search query entered by the user.
     * @param int    $limit      Maximum number of provider profile cards to return (default: 6).
     * @return array<int, array<string, mixed>> Structured list of matched provider profile cards.
     */
    public static function search(string $query_text, int $limit = 6): array
    {
        global $wpdb;

        // Auto-purge stale cache & transients on version upgrade (v1.0.45)
        if (get_option('cosy_ai_search_version') !== '1.0.45') {
            $table_c = $wpdb->prefix . 'cosychats_search_cache';
            if ($wpdb->get_var("SHOW TABLES LIKE '$table_c'") === $table_c) {
                $wpdb->query("TRUNCATE TABLE $table_c");
            }
            $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_cosy_prov_list_%' OR option_name LIKE '_transient_timeout_cosy_prov_list_%'");
            update_option('cosy_ai_search_version', '1.0.45');
        }

        // 1. Auto-Correct Typo & Spelling Mistakes (e.g. "adoptoin" -> "adoption", "misscarriage" -> "miscarriage")
        $corrected_query = self::correct_spelling_typos($query_text);

        // 2. Always-Live Real-Time Intent Parsing
        $intent         = self::parse_query_intent($corrected_query);
        $expanded_query = self::expand_query_numbers($corrected_query);

        // 3. Fetch Query Vector Embedding
        $query_vector = AIService::get_embedding($expanded_query);

        // 4. Load all active providers directly from database by meta status
        $active_provider_ids = get_users([
            'meta_key'   => 'cosy_provider_status',
            'meta_value' => 'active',
            'fields'     => 'ID',
        ]);
        if (!is_array($active_provider_ids)) {
            $active_provider_ids = [];
        }

        // Load provider embeddings from database for vector similarity scoring if available
        $table_embeddings = $wpdb->prefix . 'provider_embeddings';
        $all_vectors      = [];
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_embeddings'") === $table_embeddings) {
            $all_vectors = $wpdb->get_results("SELECT provider_id, embedding FROM $table_embeddings");
        }

        if (empty($active_provider_ids)) {
            return [];
        }

        // Build text lookup and keywords
        $provider_details    = self::get_provider_text_lookup($active_provider_ids);
        $provider_narratives = self::get_provider_narrative_lookup($active_provider_ids);
        $search_keywords     = self::get_search_keywords($corrected_query, $expanded_query);

        if (!empty($intent['synonyms'])) {
            $search_keywords = array_unique(array_merge($search_keywords, $intent['synonyms']));
        }

        // Clean keywords for domain topic extraction (strip currency symbols e.g. £15 -> 15)
        $clean_search_kws = [];
        foreach ($search_keywords as $kw) {
            $ckw = preg_replace('/[^a-z0-9]/i', '', $kw);
            if (!empty($ckw)) {
                $clean_search_kws[] = $ckw;
            }
        }
        $clean_search_kws = array_unique($clean_search_kws);

        // Extract domain topic keywords
        $modifier_words   = ['highest', 'highly', 'high', 'top', 'best', 'good', 'popular', 'great', 'rated', 'rating', 'ratings', 'reviewed', 'reviews', 'review', 'experience', 'experiences', 'experienced', 'expert', 'experts', 'specialist', 'specialists', 'trained', 'qualified', 'knowledgeable', 'proven', 'guide', 'guides', 'parent', 'parents', 'mum', 'mums', 'mom', 'moms', 'mother', 'mothers', 'mama', 'mamas', 'dad', 'dads', 'father', 'fathers', 'papa', 'papas', 'female', 'male', 'woman', 'women', 'man', 'men', 'girl', 'boy', 'profile', 'profiles', 'person', 'people', 'user', 'users', 'account', 'accounts', 'hello', 'hi', 'hey', 'greetings', 'thanks', 'thankyou', 'pls', 'please', 'something', 'anything', 'everything', 'nothing', 'nice', 'cool', 'awesome', 'lovely', 'amazing', 'sweet', 'friendly', 'kind', 'helpful', 'caring', 'warm', 'gentle', 'under', 'max', 'only', 'cheap', 'cheapest', 'affordable', 'budget', 'low', 'cost', 'price', 'rate', 'rates', 'value', 'zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen', 'twenty', 'thirty', 'forty', 'fifty', 'hundred', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '25', '30', '40', '50', 'for', 'with', 'and', 'but', 'also', 'or', 'so', 'is', 'am', 'are', 'be', 'been', 'being', 'can', 'could', 'would', 'should', 'will', 'the', 'who', 'need', 'needs', 'needing', 'want', 'wants', 'about', 'someone', 'how', 'in', 'of', 'to', 'a', 'an', 'understand', 'understands', 'understanding', 'help', 'looking', 'support', 'guidance', 'advisor', 'coaching', 'emergency', 'urgent', 'repair', 'fixing', 'fitting', 'fittings', 'service', 'services', 'talk', 'talks', 'talking', 'chat', 'chatting', 'call', 'meeting', 'session', 'consultation', 'conversation', 'kisi', 'se', 'bat', 'baat', 'karni', 'he', 'hai', 'chahiye', 'madad', 'listen', 'listening', 'listener', 'hear', 'somebody', 'anyone', 'anybody', 'today', 'tonight', 'tomorrow', 'weekend', 'soon', 'now', 'therapist', 'counsellor', 'counselor', 'therapy', 'counseling', 'coach', 'mentor', 'second', 'hand', 'car', 'buy', 'not', 'sure', 'kind', 'just', 'what', 'which', 'where', 'when', 'why', 'feel', 'feeling', 'type', 'know', 'handle', 'anymore', 'okay', 'ok', 'much', 'mess', 'mind', 'really', 'bad', 'cant', 'cannot', 'dont', 'im'];
        $domain_query_kws = array_diff($clean_search_kws, $modifier_words);

        // 4.5. Check for Explicit Provider Name Matches in Query
        $name_matched_ids = [];
        $query_words      = preg_split('/[\s,;.!?-]+/', strtolower($corrected_query), -1, PREG_SPLIT_NO_EMPTY);

        $has_potential_name = false;

        // Name searches are 1-3 word specific queries, not broad conversational / emotional phrases
        if (empty($intent['is_conversational_broad']) && count($query_words) <= 3) {
            foreach ($query_words as $qw) {
                $clean_qw = preg_replace('/[^a-z0-9]/i', '', $qw);
                if (strlen($clean_qw) >= 3 && !is_numeric($clean_qw) && !in_array($clean_qw, $modifier_words, true)) {
                    $has_potential_name = true;
                    break;
                }
            }
        }

        foreach ($active_provider_ids as $pid) {
            $user_obj = get_userdata($pid);
            if (!$user_obj) continue;

            $fname = strtolower(get_user_meta($pid, 'first_name', true) ?: '');
            $lname = strtolower(get_user_meta($pid, 'last_name', true) ?: '');
            $dname = strtolower($user_obj->display_name ?: '');

            foreach ($query_words as $qw) {
                $clean_qw = preg_replace('/[^a-z0-9]/i', '', $qw);
                if (strlen($clean_qw) >= 3 && !in_array($clean_qw, $modifier_words, true)) {
                    if (($fname !== '' && $clean_qw === $fname) || ($lname !== '' && $clean_qw === $lname) || ($dname !== '' && strpos($dname, $clean_qw) !== false)) {
                        $name_matched_ids[] = $pid;
                        break;
                    }
                }
            }
        }
        $name_matched_ids = array_unique($name_matched_ids);

        // If user explicitly searched for a non-existent provider name (e.g. "Emma 5.0"), but NO provider with that name exists and no domain topic was queried
        if ($has_potential_name && empty($name_matched_ids) && empty($domain_query_kws)) {
            return [];
        }

        // 5. Multi-Layer Hybrid Relevance Scoring & Contradiction Filtering
        $raw_matches       = [];
        $max_score         = 0.0;
        $has_exact_phrase  = false;
        $has_keyword_match = false;

        $clean_query = strtolower(trim($query_text));

        foreach ($active_provider_ids as $provider_id) {
            // Strict Provider Name Hard Filter: If query explicitly contains a provider's name, filter out unrelated providers
            if (!empty($name_matched_ids) && !in_array($provider_id, $name_matched_ids, true)) {
                continue;
            }

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
                    $min_price = $wpdb->get_var($wpdb->prepare("SELECT MIN(price) FROM $services_table WHERE provider_id = %d AND price > 0", $provider_id));
                    $lowest_p  = ($min_price !== null && floatval($min_price) > 0) ? floatval($min_price) : 0;
                    if ($lowest_p > 0 && $lowest_p > $intent['max_price']) {
                        continue;
                    }
                }
            }

            // Explicit Target Children Count Hard Filter (e.g. "17 children", "3 kids")
            if (!empty($intent['target_children_count']) && $intent['target_children_count'] > 0) {
                $ccount = $intent['target_children_count'];
                $word_counts = [
                    1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five',
                    6 => 'six', 7 => 'seven', 8 => 'eight', 9 => 'nine', 10 => 'ten'
                ];
                $w_word = $word_counts[$ccount] ?? '';

                $has_count_match = preg_match('/\b' . $ccount . '\s+(?:children|child|kids|kid)\b/i', $p_text) ||
                                   preg_match('/\b(?:mum|mother|dad|father|parent|raising)\s+of\s+' . $ccount . '\b/i', $p_text);
                if (!$has_count_match) {
                    continue;
                }
            }

            // Explicit Target Experience Years Hard Filter (e.g. "50 years of experience")
            if (!empty($intent['target_experience_years']) && $intent['target_experience_years'] > 0) {
                $req_years = $intent['target_experience_years'];
                $word_counts = [
                    1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five',
                    6 => 'six', 7 => 'seven', 8 => 'eight', 9 => 'nine', 10 => 'ten',
                    20 => 'twenty', 30 => 'thirty', 40 => 'forty', 50 => 'fifty'
                ];
                $w_word = $word_counts[$req_years] ?? '';

                $has_exp_match = preg_match('/\b' . $req_years . '\s*(?:-| |\s*to\s*)*(?:year\s*s?|yr\s*s?)\b/i', $p_text);
                if (!empty($w_word)) {
                    $has_exp_match = $has_exp_match || preg_match('/\b' . $w_word . '\s*(?:-| |\s*to\s*)*(?:year\s*s?|yr\s*s?)\b/i', $p_text);
                }
                if (!$has_exp_match) {
                    continue;
                }
            }

            // Layer A: Vector Cosine Similarity
            $vector_score = 0.0;
            if (!empty($query_vector) && isset($vector_lookup[$provider_id]) && is_array($vector_lookup[$provider_id])) {
                $vector_score = self::cosine_similarity($query_vector, $vector_lookup[$provider_id]);
            }

            // Baseline score for broad conversational / exploratory queries without explicit domain topic
            if (!empty($intent['is_conversational_broad']) && empty($domain_query_kws)) {
                $vector_score = max(0.50, $vector_score);
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

            // Layer C: Dynamic Keyword Matches (+0.25 per matching keyword)
            $keyword_boost   = 0.0;
            $prov_has_kw     = false;
            $modifier_words   = ['highest', 'highly', 'high', 'top', 'best', 'good', 'popular', 'great', 'rated', 'rating', 'ratings', 'reviewed', 'reviews', 'review', 'experience', 'experiences', 'experienced', 'expert', 'experts', 'specialist', 'specialists', 'trained', 'qualified', 'knowledgeable', 'proven', 'guide', 'guides', 'parent', 'parents', 'mum', 'mums', 'mom', 'moms', 'mother', 'mothers', 'mama', 'mamas', 'dad', 'dads', 'father', 'fathers', 'papa', 'papas', 'female', 'male', 'woman', 'women', 'man', 'men', 'girl', 'boy', 'profile', 'profiles', 'person', 'people', 'user', 'users', 'account', 'accounts', 'hello', 'hi', 'hey', 'greetings', 'thanks', 'thankyou', 'pls', 'please', 'something', 'anything', 'everything', 'nothing', 'nice', 'cool', 'awesome', 'lovely', 'amazing', 'sweet', 'friendly', 'kind', 'helpful', 'caring', 'warm', 'gentle', 'under', 'max', 'only', 'cheap', 'cheapest', 'affordable', 'budget', 'low', 'cost', 'price', 'rate', 'rates', 'value', 'zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen', 'twenty', 'thirty', 'forty', 'fifty', 'hundred', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '25', '30', '40', '50', 'for', 'with', 'and', 'but', 'also', 'or', 'so', 'is', 'am', 'are', 'be', 'been', 'being', 'can', 'could', 'would', 'should', 'will', 'the', 'who', 'need', 'needs', 'needing', 'want', 'wants', 'about', 'someone', 'how', 'in', 'of', 'to', 'a', 'an', 'understand', 'understands', 'understanding', 'help', 'looking', 'support', 'guidance', 'advisor', 'coaching', 'emergency', 'urgent', 'repair', 'fixing', 'fitting', 'fittings', 'service', 'services', 'talk', 'talks', 'talking', 'chat', 'chatting', 'call', 'meeting', 'session', 'consultation', 'conversation', 'kisi', 'se', 'bat', 'baat', 'karni', 'he', 'hai', 'chahiye', 'madad', 'listen', 'listening', 'listener', 'hear', 'somebody', 'anyone', 'anybody', 'today', 'tonight', 'tomorrow', 'weekend', 'soon', 'now', 'therapist', 'counsellor', 'counselor', 'therapy', 'counseling', 'coach', 'mentor', 'second', 'hand', 'car', 'buy', 'not', 'sure', 'kind', 'just', 'what', 'which', 'where', 'when', 'why', 'feel', 'feeling', 'type', 'know', 'handle', 'anymore', 'okay', 'ok', 'much', 'mess', 'mind', 'really', 'bad', 'cant', 'cannot', 'dont', 'im'];
            $domain_query_kws = array_diff($clean_search_kws, $modifier_words);
            $query_has_domain_kw = !empty($domain_query_kws);

            if (!empty($p_text) && !empty($search_keywords)) {
                foreach ($search_keywords as $kw) {
                    $kw_stem = (strlen($kw) > 3 && substr($kw, -1) === 's') ? substr($kw, 0, -1) : $kw;
                    if (strlen($kw) >= 2 && (strpos($p_text, $kw) !== false || strpos($p_text, $kw_stem) !== false)) {
                        $keyword_boost += 0.25;
                        $has_keyword_match = true;
                        if (in_array($kw, $domain_query_kws, true) || in_array($kw_stem, $domain_query_kws, true)) {
                            $prov_has_kw = true;
                        }
                    }
                }
            }

            // Filter domain query keywords down to actual registered taxonomy topics
            $registered_domain_topics = [];
            foreach ($domain_query_kws as $dkw) {
                if (self::is_domain_taxonomy_topic($dkw)) {
                    $registered_domain_topics[] = $dkw;
                }
            }
            $query_has_registered_topic = !empty($registered_domain_topics);

            // Per-Provider Dynamic Domain Keyword Hard Filter:
            // If user explicitly queried a registered domain category topic (e.g. "adoption" or "ivf"), check strict topic proof
            if ($query_has_registered_topic) {
                $has_topic_match = false;
                $p_narrative     = $provider_narratives[$provider_id] ?? '';

                foreach ($registered_domain_topics as $dkw) {
                    if (strlen($dkw) < 2) continue;

                    // Get full synonym/alternative cluster for this domain keyword
                    $cluster = self::get_domain_synonym_cluster($dkw);

                    // Check if provider's narrative (Bio or service descriptions) actually contains proof (topic or any alternative term)
                    $has_text_proof = false;
                    foreach ($cluster as $cword) {
                        if (preg_match('/\b' . preg_quote($cword, '/') . '\b/i', $p_narrative)) {
                            // Negative context check e.g. "failed ivf" when user isn't asking for failure
                            if ($cword === 'ivf' && (preg_match('/failed\s+(?:\w+\s+){0,2}ivf/i', $p_narrative) || preg_match('/ivf\s+attempts/i', $p_narrative))) {
                                continue;
                            }
                            $has_text_proof = true;
                            break;
                        }
                    }

                    // Strict Topic Qualification:
                    // Provider must have actual text proof in their bio or service description.
                    // If they merely checked a service checkbox with ZERO mention of the topic or its alternatives in their narrative, reject them.
                    if ($has_text_proof) {
                        $has_topic_match = true;
                        break;
                    }
                }

                if (!$has_topic_match) {
                    continue;
                }
            }

            // Layer D: Age Proximity Boosting (matches 5-year-old, 6-year-old, age 5)
            $age_boost = 0.0;
            if (!empty($intent['target_age']) && $intent['target_age'] > 0) {
                if (preg_match_all('/\b(\d+)\s*(?:-| |\s*to\s*)*year\s*(?:s|-)*old\b/i', $p_text, $p_ages)) {
                    foreach ($p_ages[1] as $p_age_val) {
                        $p_age = intval($p_age_val);
                        $diff  = abs($p_age - $intent['target_age']);
                        if ($diff === 0) {
                            $age_boost += 3.0; // Exact age match (e.g., 5-year-old)
                        } elseif ($diff <= 2) {
                            $age_boost += 2.0; // Very close age match (e.g., 6-year-old for 5-year-old query!)
                        }
                    }
                }
            }

            // Layer E: Gender / Role Intent Alignment Multiplier
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

            // Layer F: Multi-Topic Joint Intersection Bonus
            // If query contains 2+ distinct topics (e.g. Baby Loss AND IVF), reward providers who have BOTH experiences
            $intersection_boost = 0.0;
            if (count($registered_domain_topics) >= 2) {
                $matched_topics = 0;
                foreach ($registered_domain_topics as $dkw) {
                    $cluster = self::get_domain_synonym_cluster($dkw);
                    foreach ($cluster as $cword) {
                        if (preg_match('/\b' . preg_quote($cword, '/') . '\b/i', $p_narrative)) {
                            $matched_topics++;
                            break;
                        }
                    }
                }
                if ($matched_topics >= count($registered_domain_topics)) {
                    $intersection_boost = 3.5; // Matches ALL requested topics!
                } elseif ($matched_topics >= 2) {
                    $intersection_boost = 1.5;
                }
            }

            $composite_score = ($vector_score + $phrase_boost + $keyword_boost + $age_boost + $intersection_boost) * $intent_multiplier;

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

        // Strict Out-of-Context & Gibberish Query Safety Filter
        // Rejects queries that lack domain keyword alignment & intent (e.g. "xyz 123 random text", "crypto bitcoin")
        $is_valid_intent = ($intent['target_role'] !== 'any') || ($intent['max_price'] > 0) || ($intent['target_age'] > 0) || ($intent['target_children_count'] > 0) || ($intent['target_experience_years'] > 0) || !empty($intent['synonyms']) || !empty($intent['is_conversational_broad']) || preg_match('/\b(best|top|cheap|cheapest|affordable|rated|rating|reviewed|reviews|guide|guides|parent|parents|mum|mums|dad|dads|talk|listen|listening|help|someone|support|therapist|counsellor|counselor|coach|advice|guidance|bat|baat|madad)\b/i', $corrected_query);

        if (!$is_valid_intent && empty($domain_query_kws)) {
            return [];
        }

        // Dynamic Cutoff Floor
        $threshold = ($max_score > 0.0) ? max(0.20, $max_score * 0.40) : 0.0;
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
        $services_table          = $wpdb->prefix . 'provider_services';
        $reviews_table           = $wpdb->prefix . 'cosy_provider_reviews';
        $is_highest_rated_intent = preg_match('/\b(highest|top|best)\b.*?\b(rated|rating|ratings|stars|reviews)\b/i', $corrected_query) || preg_match('/\b(highest|top|best)\b/i', $corrected_query);

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

            // Apply Rating Boost: Strong +3.0 multiplier for explicit "highest/top rated" intent
            if ($is_highest_rated_intent) {
                $rating_boost = ($rating / 10.0) * 3.0 + (min(5, $review_count) * 0.1);
            } else {
                $rating_boost = ($rating / 10.0) * 0.15 + (min(5, $review_count) * 0.02);
            }

            // Apply Price Budget Fit Boost if user specified a budget or asked for cheap/affordable/low cost
            $price_boost = 0.0;
            if ($price > 0 && (preg_match('/\b(cheap|cheapest|affordable|budget)\b/i', $corrected_query) || preg_match('/\blow\s*(?:cost|price|rate|rates)*\b/i', $corrected_query))) {
                $price_boost += max(0, (60.0 - $price) * 0.10);
            }
            if ($intent['max_price'] > 0 && $price > 0) {
                if ($price <= $intent['max_price']) {
                    $price_boost += 0.20;
                } else {
                    $price_boost -= 0.30; // Exceeds explicit budget
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

        // 8. Extract Sorted Provider IDs & Return Real-Time Cards with Contextual Service Selection
        $sorted_provider_ids = array_column($matches, 'provider_id');

        $effective_limit = (!empty($intent['requested_limit']) && $intent['requested_limit'] > 0) ? min($intent['requested_limit'], 12) : $limit;
        return self::fetch_provider_cards($sorted_provider_ids, $effective_limit, $corrected_query);
    }

    /**
     * Parse Query Intent, Role Targets, Explicit Price Limits, Age Taxonomy, and Dynamic N-Gram Phrases.
     */
    private static function parse_query_intent(string $query): array
    {
        $q = strtolower(trim($query));

        $intent = [
            'target_role'             => 'any', // female, male, any
            'target_age'              => 0,
            'target_children_count'   => 0,
            'target_experience_years' => 0,
            'max_price'               => 0,
            'is_conversational_broad' => false,
            'phrases'                 => [],
            'synonyms'                => [],
        ];

        // Detect broad emotional, open-ended or conversational intent
        if (preg_match('/\b(talk|speak|chat|listen|listening|listener|help|someone|somebody|anyone|anybody|guidance|advice|confused|overwhelmed|overwhelming|low|alone|isolated|isolation|support|therapist|counsellor|counselor|coach|mentor|hear me|hear us|anymore|handle|okay|ok|struggling|struggle|coping|cope|suffering|suffer|exhausted|exhaustion|broken|breaking|crying|tears|lost|mess|failing|fail|hopeless|desperate|mind|drowning|giving up|give up|survive|surviving|hard|difficult|scared|afraid|panic|panicking|lonely|loneliness|anxiety|anxious|depressed|depression|stress|stressed|burnout|bat|baat|madad|feel|feeling|bad|sad|hurt|hurting|pain|tough|trouble|know|need|want|much|everything|find|do|cant|cannot)\b/i', $q)) {
            $intent['is_conversational_broad'] = true;
        }

        // Extract explicit child age intent (e.g. "5 year old", "5-year-old", "5yo", "age 5")
        if (preg_match('/\b(\d+)\s*(?:-| |\s*to\s*)*year\s*(?:s|-)*old\b/i', $q, $amatches)) {
            $intent['target_age'] = intval($amatches[1]);
        } elseif (preg_match('/\bage\s*(\d+)\b/i', $q, $amatches)) {
            $intent['target_age'] = intval($amatches[1]);
        }

        // Extract explicit children count intent (e.g. "17 children", "3 kids", "4 children")
        if (preg_match('/\b(\d+)\s+(?:children|child|kids|kid)\b/i', $q, $cmatches)) {
            $intent['target_children_count'] = intval($cmatches[1]);
        }

        // Extract explicit experience years intent (e.g. "50 years of experience", "10 yrs experience")
        if (preg_match('/\b(\d+)\s*(?:-| |\s*to\s*)*(?:year\s*s?|yr\s*s?)\s+(?:of\s+)?experience\b/i', $q, $ymatches)) {
            $intent['target_experience_years'] = intval($ymatches[1]);
        }

        // Comprehensive Category & Age Synonym Mapping
        $synonym_map = [
            'adolescent'   => ['teenager', 'teenage', 'teen', 'teens'],
            'adolescence'  => ['teenager', 'teenage', 'teen', 'teens'],
            'youth'        => ['teenager', 'teenage', 'teen', 'teens'],
            'puberty'      => ['teenager', 'teenage', 'teen', 'teens'],
            'gcse'         => ['teenager', 'teenage', 'teen', 'teens'],
            'gcses'        => ['teenager', 'teenage', 'teen', 'teens'],
            'infant'       => ['baby', 'newborn'],
            'preschool'    => ['toddler', 'kids'],
            'nursery'      => ['toddler', 'kids'],
            'icsi'         => ['ivf', 'fertility'],
            'conception'   => ['ivf', 'fertility'],
            'bedtime'      => ['sleep', 'sleeping', 'nighttime'],
            'bedtimes'     => ['sleep', 'sleeping', 'nighttime'],
            'nighttime'    => ['sleep', 'sleeping', 'bedtime'],
            'nap'          => ['sleep', 'sleeping'],
            'naps'         => ['sleep', 'sleeping'],
            'marriage'     => ['divorce', 'separation', 'co-parenting', 'wellbeing'],
            'marital'      => ['divorce', 'separation', 'co-parenting', 'wellbeing'],
            'relationship' => ['divorce', 'separation', 'co-parenting', 'wellbeing'],
            'therapist'    => ['wellbeing', 'mental health', 'support', 'guide'],
            'counsellor'   => ['wellbeing', 'mental health', 'support', 'guide'],
            'counselor'    => ['wellbeing', 'mental health', 'support', 'guide'],
        ];

        foreach ($synonym_map as $trigger => $syns) {
            if (strpos($q, $trigger) !== false) {
                $intent['synonyms'] = array_merge($intent['synonyms'], $syns);
            }
        }

        // Fuzzy Typo & Misspelling Correction Engine (e.g. "adpottion" -> "adoption", "teanager" -> "teenager")
        $domain_taxonomy_targets = [
            'adoption'     => ['adoption', 'adopting', 'adoptive', 'adopted', 'adopt'],
            'ivf'          => ['ivf', 'fertility', 'icsi'],
            'sleep'        => ['sleep', 'sleeping', 'bedtime', 'nighttime'],
            'teenager'     => ['teenager', 'teenagers', 'teenage', 'teens', 'teen', 'gcses', 'gcse'],
            'toddler'      => ['toddler', 'toddlers'],
            'baby'         => ['baby', 'babies', 'infant', 'newborn'],
            'adhd'         => ['adhd', 'autism', 'send', 'senco', 'special needs'],
            'foster'       => ['foster', 'fostering', 'fostercare'],
            'surrogacy'    => ['surrogacy', 'surrogate'],
            'miscarriage'  => ['miscarriage', 'baby loss', 'grief', 'bereavement'],
            'twins'        => ['twins', 'twin', 'multiples'],
            'breastfeeding'=> ['breastfeeding', 'nursing', 'lactation'],
            'relationship' => ['relationship', 'marriage', 'marital', 'couples', 'divorce', 'separation', 'coparenting', 'co-parenting'],
            'marriage'     => ['marriage', 'marital', 'relationship', 'couples', 'divorce', 'separation', 'coparenting'],
            'therapist'    => ['therapist', 'counsellor', 'counselor', 'therapy', 'counseling', 'wellbeing', 'coach', 'mentor'],
        ];

        $q_tokens = preg_split('/[\s,;.!?-]+/', $q, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($q_tokens as $token) {
            if (strlen($token) < 3) continue;
            foreach ($domain_taxonomy_targets as $canonical => $syn_list) {
                if (in_array($token, $syn_list, true)) {
                    $intent['synonyms'][] = $canonical;
                    $intent['synonyms'] = array_merge($intent['synonyms'], $syn_list);
                    continue 2;
                }

                // Check Levenshtein edit distance for typo tolerance
                $dist = levenshtein($token, $canonical);
                $max_allowed_dist = (strlen($canonical) >= 6) ? 2 : 1;
                if ($dist <= $max_allowed_dist) {
                    $intent['synonyms'][] = $canonical;
                    $intent['synonyms'] = array_merge($intent['synonyms'], $syn_list);
                    break;
                }
            }
        }
        $intent['synonyms'] = array_values(array_unique($intent['synonyms']));

        // 1. Comprehensive Gender & Role Synonym Mapping with Position-Aware Order
        $female_roles = ['mum', 'mums', 'mom', 'moms', 'mother', 'mothers', 'mama', 'mamas', 'female', 'woman', 'women', 'single mum', 'solo mum', 'single mom', 'solo mom', 'single mother', 'solo mother'];
        $male_roles   = ['dad', 'dads', 'father', 'fathers', 'papa', 'papas', 'male', 'man', 'men', 'single dad', 'solo dad', 'single father', 'solo father'];

        $first_f_pos = 999;
        $first_m_pos = 999;

        foreach ($female_roles as $f_role) {
            if (preg_match('/\b' . preg_quote($f_role, '/') . '\b/i', $q, $m, PREG_OFFSET_CAPTURE)) {
                $pos = $m[0][1];
                if ($pos < $first_f_pos) {
                    $first_f_pos = $pos;
                }
            }
        }

        foreach ($male_roles as $m_role) {
            if (preg_match('/\b' . preg_quote($m_role, '/') . '\b/i', $q, $m, PREG_OFFSET_CAPTURE)) {
                $pos = $m[0][1];
                if ($pos < $first_m_pos) {
                    $first_m_pos = $pos;
                }
            }
        }

        if ($first_f_pos < $first_m_pos) {
            $intent['target_role'] = 'female';
        } elseif ($first_m_pos < $first_f_pos) {
            $intent['target_role'] = 'male';
        }

        // 2. Extract Explicit Price / Budget Constraint (e.g. "under £15", "15 or less", "up to 20", "below 25", "max 15")
        if (preg_match('/(?:under|below|less than|up to|max|budget|\<)\s*(?:£|&pound;|gbp|\$)?\s*(\d+(?:\.\d+)?)/iu', $q, $pmatches)) {
            $intent['max_price'] = floatval($pmatches[1]);
        } elseif (preg_match('/(?:£|&pound;|gbp|\$)?\s*(\d+(?:\.\d+)?)\s*(?:or less|or lower|max|budget|below)\b/iu', $q, $pmatches)) {
            $intent['max_price'] = floatval($pmatches[1]);
        }

        // 3. Extract Requested Result Count Limit (e.g. "only 2 best providers", "top 3", "only two best")
        $word_num_map = [
            'one' => 1, 'two' => 2, 'three' => 3, 'four' => 4, 'five' => 5,
            'six' => 6, 'seven' => 7, 'eight' => 8, 'nine' => 9, 'ten' => 10,
        ];
        if (preg_match('/(?:only|top|first|give me|show me|just|best)\s+(\d+|one|two|three|four|five|six|seven|eight|nine|ten)\b/i', $q, $lmatches)) {
            $val = strtolower($lmatches[1]);
            $intent['requested_limit'] = is_numeric($val) ? intval($val) : ($word_num_map[$val] ?? 0);
        } elseif (preg_match('/\b(\d+|one|two|three|four|five|six|seven|eight|nine|ten)\s+(?:best|top)?\s*(?:providers?|profiles?|mums?|dads?|parents?)\b/i', $q, $lmatches)) {
            $val = strtolower($lmatches[1]);
            $intent['requested_limit'] = is_numeric($val) ? intval($val) : ($word_num_map[$val] ?? 0);
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
    private static function fetch_provider_cards(array $provider_ids, int $limit = 6, string $query_text = ''): array
    {
        global $wpdb;

        if (empty($provider_ids)) {
            return [];
        }

        $services_table = $wpdb->prefix . 'provider_services';
        $reviews_table  = $wpdb->prefix . 'cosy_provider_reviews';
        $cards          = [];

        // Extract domain topic words from query_text for context matching
        $search_kws   = !empty($query_text) ? self::get_search_keywords($query_text, '') : [];
        $modifiers    = ['highest', 'highly', 'high', 'top', 'best', 'good', 'popular', 'great', 'rated', 'rating', 'ratings', 'reviewed', 'reviews', 'review', 'experience', 'experiences', 'guide', 'guides', 'parent', 'parents', 'mum', 'mums', 'dad', 'dads', 'under', 'max', 'only', 'cheap', 'for', 'with', 'and', 'the', 'who', 'need', 'needs', 'about', 'someone', 'how', 'in', 'of', 'to', 'a', 'an', 'understand', 'understands', 'help', 'looking'];
        $query_topics = array_diff($search_kws, $modifiers);

        $provider_ids = array_slice($provider_ids, 0, 50); // Sanity cap

        foreach ($provider_ids as $user_id) {
            if (count($cards) >= $limit) {
                break;
            }

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

            // Fetch service title & lowest positive price matching searched topic first!
            $service_name = '';
            $price = '0.00';
            $matched_service_desc = '';
            if ($wpdb->get_var("SHOW TABLES LIKE '$services_table'") === $services_table) {
                $srow = null;

                // Try to find service matching exact searched topic or its synonym cluster (e.g. IVF or Adoption)
                if (!empty($query_topics)) {
                    foreach ($query_topics as $topic) {
                        $cluster = self::get_domain_synonym_cluster($topic);
                        foreach ($cluster as $cword) {
                            if (strlen($cword) >= 3) {
                                $srow = $wpdb->get_row($wpdb->prepare(
                                    "SELECT service, price, description FROM $services_table WHERE provider_id = %d AND (LOWER(service) LIKE %s OR LOWER(description) LIKE %s) ORDER BY price ASC LIMIT 1",
                                    $user_id,
                                    '%' . $wpdb->esc_like(strtolower($cword)) . '%',
                                    '%' . $wpdb->esc_like(strtolower($cword)) . '%'
                                ));
                                if ($srow) {
                                    break 2;
                                }
                            }
                        }
                    }
                }

                if (!$srow) {
                    $srow = $wpdb->get_row($wpdb->prepare("SELECT service, price, description FROM $services_table WHERE provider_id = %d AND price > 0 ORDER BY price ASC LIMIT 1", $user_id));
                }
                if (!$srow) {
                    $srow = $wpdb->get_row($wpdb->prepare("SELECT service, price, description FROM $services_table WHERE provider_id = %d ORDER BY price ASC LIMIT 1", $user_id));
                }
                if ($srow) {
                    $service_name         = $srow->service;
                    $price                = number_format(floatval($srow->price), 2);
                    $matched_service_desc = !empty($srow->description) ? trim($srow->description) : '';
                }
            }

            // Contextual Description: Show the exact service description or relevant sentence matching user query
            $card_desc = $bio;
            if (!empty($matched_service_desc)) {
                // If service description has relevant topic content, prefer it over generic bio
                if (!empty($query_topics)) {
                    foreach ($query_topics as $topic) {
                        $cluster = self::get_domain_synonym_cluster($topic);
                        foreach ($cluster as $cword) {
                            if (stripos($matched_service_desc, $cword) !== false) {
                                $card_desc = $matched_service_desc;
                                break 2;
                            }
                        }
                    }
                }
            }

            // If general bio is used, highlight the relevant sentence matching query topic
            if (!empty($query_topics) && strlen($card_desc) > 80) {
                $sentences = preg_split('/(?<=[.?!])\s+/', $card_desc, -1, PREG_SPLIT_NO_EMPTY);
                $best_idx = -1;
                foreach ($query_topics as $topic) {
                    $cluster = self::get_domain_synonym_cluster($topic);
                    foreach ($sentences as $s_idx => $sentence) {
                        foreach ($cluster as $cword) {
                            if (stripos($sentence, $cword) !== false) {
                                $best_idx = $s_idx;
                                break 3;
                            }
                        }
                    }
                }
                if ($best_idx > 0) {
                    $card_desc = implode(' ', array_slice($sentences, $best_idx));
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
                'description'        => $card_desc,
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
        $stopwords = [
            'and', 'the', 'for', 'with', 'you', 'your', 'our', 'are', 'was', 'were', 'who', 'whom', 'whose',
            'this', 'that', 'these', 'those', 'have', 'has', 'had', 'having', 'looking', 'need', 'needs',
            'needing', 'want', 'wants', 'someone', 'somebody', 'anyone', 'anybody', 'me', 'my', 'myself',
            'text', 'random', 'test', 'sample', 'xyz', 'hello', 'hi', 'hey', 'greetings', 'thanks', 'thankyou',
            'pls', 'please', 'good', 'morning', 'afternoon', 'evening', 'what', 'which', 'where', 'when',
            'why', 'how', 'can', 'could', 'would', 'should', 'will', 'shall', 'may', 'might', 'must',
            'just', 'not', 'sure', 'kind', 'type', 'about', 'some', 'any', 'all', 'more', 'most', 'very', 'like',
            'of', 'in', 'to', 'at', 'by', 'on', 'off', 'up', 'out', 'over', 'into', 'onto', 'down', 'from',
            'as', 'if', 'an', 'a', 'is', 'am', 'be', 'been', 'being', 'so', 'than',
            'do', 'doing', 'done', 'did', 'cant', 'cannot', 'im', 'dont', 'ive', 'ill', 'id', 'anymore',
            'too', 'much', 'really', 'bad', 'okay', 'ok', 'handle', 'mind', 'mess', 'everything', 'something'
        ];
        $keywords  = [];

        foreach ($words as $w) {
            if (strlen($w) >= 2 && !in_array($w, $stopwords, true)) {
                $keywords[] = $w;
            }
        }

        return array_unique($keywords);
    }

    /**
     * Return domain synonyms and alternative topic terms for strict semantic verification.
     */
    public static function get_domain_synonym_cluster(string $term): array
    {
        $term = strtolower(trim($term));
        $clusters = [
            'adoption' => [
                'adoption', 'adopt', 'adopting', 'adopted', 'adoptive', 'adopter', 'adopters',
                'foster', 'fostering', 'foster-to-adopt', 'foster carer', 'panel approval',
                'adoption panel', 'placement', 'child placement', 'guardianship'
            ],
            'adopt' => [
                'adoption', 'adopt', 'adopting', 'adopted', 'adoptive', 'adopter', 'adopters',
                'foster', 'fostering', 'foster-to-adopt', 'foster carer', 'panel approval',
                'adoption panel', 'placement', 'child placement', 'guardianship'
            ],
            'baby loss' => [
                'baby loss', 'pregnancy loss', 'miscarriage', 'miscarriages', 'stillbirth', 'stillborn',
                'infant loss', 'bereavement', 'grief', 'angel baby', 'tfmr', 'rainbow baby', 'loss of a baby'
            ],
            'loss' => [
                'baby loss', 'pregnancy loss', 'miscarriage', 'miscarriages', 'stillbirth', 'stillborn',
                'infant loss', 'bereavement', 'grief', 'angel baby', 'tfmr', 'rainbow baby', 'loss of a baby'
            ],
            'ivf' => [
                'ivf', 'infertility', 'fertility', 'icsi', 'embryo', 'donor sperm', 'donor egg',
                'surrogacy', 'egg retrieval', 'two-week wait', 'two week wait', 'fertility treatment'
            ],
            'fertility' => [
                'ivf', 'infertility', 'fertility', 'icsi', 'embryo', 'donor sperm', 'donor egg',
                'surrogacy', 'egg retrieval', 'two-week wait', 'two week wait', 'fertility treatment'
            ],
            'adhd' => [
                'adhd', 'add', 'neurodivergent', 'hyperactive', 'attention deficit', 'inattention'
            ],
            'autism' => [
                'autism', 'autistic', 'asd', 'neurodivergent', 'sensory overload', 'aspergers'
            ],
            'teen' => [
                'teen', 'teens', 'teenager', 'teenagers', 'adolescent', 'adolescence', 'puberty',
                'secondary school', 'high school', 'gcse', 'gcses', 'exams', 'revision', 'mood swings',
                'screaming', 'attitude', 'screen time', 'gaming'
            ],
            'teenager' => [
                'teen', 'teens', 'teenager', 'teenagers', 'adolescent', 'adolescence', 'puberty',
                'secondary school', 'high school', 'gcse', 'gcses', 'exams', 'revision', 'mood swings',
                'screaming', 'attitude', 'screen time', 'gaming'
            ],
            'sleep' => [
                'sleep', 'sleeping', 'night feeding', 'sleepless', 'deprivation', 'insomnia',
                'exhaustion', 'tired', 'cant sleep', 'night waking', 'bedtime', 'nighttime'
            ],
            'relationship' => [
                'relationship', 'marriage', 'marital', 'partner', 'husband', 'wife', 'couples',
                'divorce', 'separation', 'breakup', 'break-up', 'break up', 'broken up', 'co-parenting', 'coparenting', 'custody', 'split', 'ex', 'ex-partner',
                'family dynamics', 'conflict', 'wellbeing'
            ],
            'marriage' => [
                'relationship', 'marriage', 'marital', 'partner', 'husband', 'wife', 'couples',
                'divorce', 'separation', 'breakup', 'break-up', 'break up', 'co-parenting', 'coparenting', 'custody', 'split', 'ex', 'ex-partner',
                'family dynamics', 'conflict', 'wellbeing'
            ],
            'divorce' => [
                'divorce', 'separation', 'breakup', 'break-up', 'break up', 'co-parenting', 'coparenting', 'custody', 'split', 'ex', 'ex-partner',
                'marriage', 'marital', 'relationship', 'partner', 'couples', 'family dynamics'
            ],
            'separation' => [
                'divorce', 'separation', 'breakup', 'break-up', 'break up', 'co-parenting', 'coparenting', 'custody', 'split', 'ex', 'ex-partner',
                'marriage', 'marital', 'relationship', 'partner', 'couples', 'family dynamics'
            ],
            'breakup' => [
                'divorce', 'separation', 'breakup', 'break-up', 'break up', 'broken up', 'co-parenting', 'coparenting', 'custody', 'split', 'ex', 'ex-partner',
                'marriage', 'marital', 'relationship', 'partner', 'couples', 'family dynamics'
            ],
            'break-up' => [
                'divorce', 'separation', 'breakup', 'break-up', 'break up', 'broken up', 'co-parenting', 'coparenting', 'custody', 'split', 'ex', 'ex-partner',
                'marriage', 'marital', 'relationship', 'partner', 'couples', 'family dynamics'
            ],
            'co-parenting' => [
                'co-parenting', 'coparenting', 'divorce', 'separation', 'breakup', 'break-up', 'custody', 'split', 'ex',
                'ex-partner', 'marriage', 'marital', 'relationship', 'partner', 'couples'
            ],
            'coparenting' => [
                'co-parenting', 'coparenting', 'divorce', 'separation', 'breakup', 'break-up', 'custody', 'split', 'ex',
                'ex-partner', 'marriage', 'marital', 'relationship', 'partner', 'couples'
            ],
            'therapist' => [
                'therapist', 'counsellor', 'counselor', 'counseling', 'therapy', 'psychologist',
                'coach', 'mentor', 'advisor', 'listening', 'support', 'guide', 'wellbeing', 'mental health'
            ],
            'counsellor' => [
                'therapist', 'counsellor', 'counselor', 'counseling', 'therapy', 'psychologist',
                'coach', 'mentor', 'advisor', 'listening', 'support', 'guide', 'wellbeing', 'mental health'
            ],
            'wellbeing' => [
                'wellbeing', 'well-being', 'burnout', 'anxiety', 'depression', 'mental health',
                'divorce', 'separation', 'co-parenting', 'exhaustion', 'stress', 'low', 'support', 'listen'
            ],
        ];

        foreach ($clusters as $key => $syns) {
            if ($term === $key || in_array($term, $syns, true)) {
                return $syns;
            }
        }

        // Return term and its standard singular/plural stem
        $stem = (strlen($term) > 3 && substr($term, -1) === 's') ? substr($term, 0, -1) : $term;
        return array_unique([$term, $stem]);
    }

    /**
     * Check if a search keyword represents a registered parenting domain taxonomy topic.
     */
    public static function is_domain_taxonomy_topic(string $term): bool
    {
        $term = strtolower(trim($term));
        if ($term === 'ivf' || $term === 'asd' || $term === 'add') {
            return true;
        }

        $domain_roots = [
            'adoption', 'adopt', 'adopting', 'adopted', 'adoptive', 'adopter', 'adopters', 'foster', 'fostering', 'foster-to-adopt', 'guardianship',
            'baby loss', 'pregnancy loss', 'miscarriage', 'miscarriages', 'stillbirth', 'stillborn', 'bereavement', 'grief', 'angel baby', 'tfmr', 'rainbow baby',
            'infertility', 'fertility', 'icsi', 'embryo', 'surrogacy', 'surrogate', 'donor egg', 'donor sperm', 'egg retrieval',
            'adhd', 'autism', 'autistic', 'neurodivergent', 'hyperactive', 'senco',
            'teen', 'teens', 'teenager', 'teenagers', 'adolescent', 'adolescence', 'puberty', 'gcse', 'gcses',
            'sleep', 'sleeping', 'sleepless', 'insomnia', 'night feeding',
            'relationship', 'marriage', 'marital', 'partner', 'husband', 'wife', 'couples', 'divorce', 'separation', 'co-parenting', 'coparenting', 'breakup', 'break-up',
            'newborn', 'newborns', 'postnatal', 'postpartum', 'paternity', 'maternity', 'breastfeeding', 'colic',
            'wellbeing', 'burnout', 'anxiety', 'depression', 'mental health',
            'therapist', 'counsellor', 'counselor', 'therapy', 'counseling'
        ];

        foreach ($domain_roots as $root) {
            if ($term === $root || (strlen($term) >= 4 && strpos($root, $term) !== false) || (strlen($root) >= 4 && strpos($term, $root) !== false)) {
                return true;
            }
        }
        return false;
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

            $services_text = [];
            if ($wpdb->get_var("SHOW TABLES LIKE '$services_table'") === $services_table) {
                $rows = $wpdb->get_results($wpdb->prepare("SELECT service, description FROM $services_table WHERE provider_id = %d", $id));
                foreach ($rows as $r) {
                    if (!empty($r->service)) {
                        $services_text[] = $r->service;
                    }
                    if (!empty($r->description)) {
                        $services_text[] = $r->description;
                    }
                }
            }

            $lookup[$id] = trim("$fname $lname " . implode(' ', $services_text) . " $bio");
        }

        return $lookup;
    }

    /**
     * Build lookup array of provider narrative content (Bio and Service Descriptions)
     * strictly excluding service category titles to verify genuine lived experience.
     */
    private static function get_provider_narrative_lookup(array $provider_ids): array
    {
        if (empty($provider_ids)) {
            return [];
        }

        global $wpdb;
        $services_table = $wpdb->prefix . 'provider_services';
        $lookup = [];

        foreach ($provider_ids as $id) {
            $bio = get_user_meta($id, 'description', true) ?: '';

            $descriptions = [];
            if ($wpdb->get_var("SHOW TABLES LIKE '$services_table'") === $services_table) {
                $rows = $wpdb->get_results($wpdb->prepare("SELECT description FROM $services_table WHERE provider_id = %d", $id));
                foreach ($rows as $r) {
                    if (!empty($r->description)) {
                        $descriptions[] = $r->description;
                    }
                }
            }

            $lookup[$id] = strtolower(trim($bio . ' ' . implode(' ', $descriptions)));
        }

        return $lookup;
    }

    /**
     * Auto-correct common typos, Hinglish phrases, and spelling errors in search query
     * against our domain taxonomy vocabulary.
     */
    public static function correct_spelling_typos(string $query): string
    {
        $q_norm = strtolower(trim($query));

        // Normalise common typos and Hinglish phrases to standard conversational English
        $typo_phrase_map = [
            '/\b(?:mujhe\s+)?kisi\s+se\s+ba?t\s+karni\s+he?\b/i' => 'talk to someone',
            '/\b(?:madad|help)\s+chahiye\b/i'                   => 'need help',
            '/\bproblem\s+hai\b/i'                              => 'having problems',
            '/\b(?:stress|tension)\s+ho\s+rah[ia]\s+hai?\b/i'    => 'feeling stressed anxiety',
            '/\bmy\s+wife\s+se\s+problem\b/i'                   => 'marriage problem with wife',
            '/\bkisi\s+se\s+milna\b/i'                          => 'meet someone',
            '/\brelation\s+ship\b/i'                            => 'relationship',
            '/\bmental\s+helth\b/i'                             => 'mental health',
            '/\btlk\b/i'                                        => 'talk',
            '/\btheripist\b/i'                                  => 'therapist',
            '/\bcounsling\b/i'                                  => 'counseling',
            '/\bcounseller\b/i'                                 => 'counsellor',
            '/\banxeity\b/i'                                    => 'anxiety',
            '/\brelashionship\b/i'                              => 'relationship',
        ];
        foreach ($typo_phrase_map as $pattern => $replacement) {
            $q_norm = preg_replace($pattern, $replacement, $q_norm);
        }

        $has_phrase_correction = ($q_norm !== strtolower(trim($query)));

        $words = preg_split('/[\s,;.!?-]+/', $q_norm, -1, PREG_SPLIT_NO_EMPTY);
        if (empty($words)) {
            return $query;
        }

        $vocabulary = [
            'adoption', 'adoptive', 'adopted', 'fostering', 'foster', 'guardianship',
            'pregnancy', 'miscarriage', 'miscarriages', 'stillbirth', 'bereavement', 'infant', 'grief',
            'infertility', 'fertility', 'embryo', 'surrogacy', 'surrogate', 'ivf',
            'toddler', 'toddlers', 'teenager', 'teenagers', 'adolescent', 'puberty',
            'autism', 'autistic', 'neurodivergent', 'hyperactive', 'senco', 'adhd',
            'newborn', 'newborns', 'postnatal', 'paternity', 'maternity', 'breastfeeding',
            'wellbeing', 'burnout', 'anxiety', 'depression', 'divorce', 'separation', 'coparenting',
            'father', 'mother', 'parent', 'parents', 'children', 'experience', 'experienced',
            'marriage', 'marital', 'relationship', 'partner', 'husband', 'wife', 'couples',
            'therapist', 'counsellor', 'counseling', 'therapy', 'mentor', 'coach', 'listen', 'listening',
            'sleepless', 'insomnia', 'lonely', 'anxious'
        ];

        $corrected_words = [];
        $has_correction = false;

        foreach ($words as $w) {
            $clean_w = preg_replace('/[^a-z0-9]/i', '', $w);
            if (strlen($clean_w) < 4 || is_numeric($clean_w) || in_array($clean_w, $vocabulary, true)) {
                $corrected_words[] = $w;
                continue;
            }

            $best_match = $w;
            $best_distance = 99;

            foreach ($vocabulary as $vocab) {
                if (abs(strlen($clean_w) - strlen($vocab)) > 2) {
                    continue;
                }

                $dist = levenshtein($clean_w, $vocab);
                $max_allowed_dist = (strlen($vocab) >= 7) ? 2 : 1;

                if ($dist <= $max_allowed_dist && $dist < $best_distance) {
                    $best_distance = $dist;
                    $best_match = $vocab;
                }
            }

            if ($best_distance <= 2 && $best_match !== $w) {
                $has_correction = true;
                $corrected_words[] = $best_match;
            } else {
                $corrected_words[] = $w;
            }
        }

        return ($has_correction || $has_phrase_correction) ? implode(' ', $corrected_words) : $query;
    }
}
