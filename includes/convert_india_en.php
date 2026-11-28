<?php
/**
 * KRISHNA TOOLS — extra English content for Indian units on /convert/ pages.
 * English is the main language of the site (most Indian searches are in English or Hinglish),
 * so the English pages carry the full regional detail: a size-by-state table with sources,
 * the Hinglish ways people type the question, and practical market / land questions.
 */
require_once __DIR__ . '/regional_units.php';
require_once __DIR__ . '/local_units.php';

/** Units searched in Hinglish ("1 quintal kitna kg hota hai"). */
function gen_india_units(): array {
    return ['quintal', 'tola', 'maund', 'ser', 'bigha', 'guntha', 'square-yard', 'lakh', 'crore', 'acre', 'hectare'];
}

function gen_is_india_pair(string $from, string $to): bool {
    return in_array($from, gen_india_units(), true) || in_array($to, gen_india_units(), true);
}

/** Hinglish search forms for an Indian pair: ["30 quintal kitna kg hota hai", "30 quintal me kitne kilo"]. */
function gen_en_hinglish(string $from, string $to, ?float $value): array {
    if (!gen_is_india_pair($from, $to)) return [];
    $n = $value !== null ? gen_plain($value) : '1';
    $f = gen_lname($from, 'en'); $t = gen_lname($to, 'en');
    $tShort = ['kilo' => 'kg', 'sq ft' => 'square feet', 'gaj' => 'gaj'][$t] ?? $t;
    return ["$n $f kitna $tShort hota hai", "$n $f me kitne $t", "$n $f = kitne $t"];
}

/** Size-by-region rows for a regional unit: [[region, value text, source]], or []. */
function gen_en_region_rows(string $unit): array {
    $R = gen_regions()[$unit] ?? null;
    if (!$R || count($R['list']) < 2) return [];
    $rows = [];
    foreach ($R['list'] as $k => $r) {
        if ($unit === 'bigha') {
            $sqft = $r['f'] / KT_SQFT;
            $val = gen_fmt($sqft) . ' sq ft = ' . gen_fmt($sqft / 9) . ' sq yd = ' . gen_fmt($r['f'] / 4046.8564224) . ' acre = ' . gen_fmt($sqft / 1089) . ' guntha';
        } else {
            $val = gen_fmt($r['f']) . ' kg';
        }
        $rows[] = [$r['en'] . ($k === $R['default'] ? ' (used on this page)' : ''), $val, $r['src']];
    }
    return $rows;
}

/** Practical questions people in India ask about these units (English). */
function gen_en_india_faq(string $from, string $to): array {
    $q = [
        'quintal' => ['If the mandi rate is per quintal, what is the price per kg?', 'Divide the quintal rate by 100. For example, cotton at ₹7,500 per quintal is ₹75 per kg, and wheat at ₹2,500 per quintal is ₹25 per kg. In Gujarat market yards, where prices are also said per maund of 20 kg, ₹7,500 per quintal is ₹1,500 per maund.'],
        'maund' => ['Why is the Gujarat maund 20 kg when the Indian maund is 37.32 kg?', 'The maund was never one fixed size across India. Gujarat market yards settled on 20 kg (1 man = 20 kilo) for groundnut, cotton, cumin and castor prices, while the 1956 Weights and Measures Act defined the standard Indian maund as 40 seer = 37.3242 kg, often rounded to 40 kg in the north. Always ask which maund a price refers to.'],
        'ser' => ['Is 1 seer equal to 1 kg?', 'Not exactly. The standard Indian seer is 80 tola = 933.1 grams. After India moved to the metric system the kilogram replaced it, so many people now loosely use "seer" for 1 kg.'],
        'bigha' => ['How many bigha make 1 acre?', 'In Gujarat, 1 acre = 2.5 vigha (bigha), because 1 vigha = 16 guntha and 1 acre = 40 guntha. In Rajasthan (pucca bigha), eastern UP and Bihar, 1 acre ≈ 1.6 bigha; in West Bengal and Assam 1 acre ≈ 3.03 bigha; in Himachal 1 acre = 5 bigha.'],
        'guntha' => ['How many guntha are in 1 acre and 1 vigha?', '1 acre = 40 guntha everywhere the guntha is used. In Gujarat 1 vigha = 16 guntha, so 1 acre = 2.5 vigha. One guntha is 1,089 sq ft (121 sq yd).'],
        'square-yard' => ['How many sq ft is a 100 gaj plot?', '1 gaj (square yard, called var in Gujarat) = 9 sq ft, so a 100 gaj plot is 900 sq ft, a 200 gaj plot is 1,800 sq ft and a 50 gaj plot is 450 sq ft.'],
        'tola' => ['Do jewellers use 10 grams or 11.66 grams for 1 tola?', 'The traditional tola is 11.6638 g (180 grains). Most Indian jewellers now quote gold per 10 grams and some call that a tola, so check the bill: 1 tola at 11.66 g costs about 16.6% more than 10 g.'],
        'lakh' => ['How do I read lakh and crore in million?', '10 lakh = 1 million, 1 crore = 10 million, and 100 crore = 1 billion. So ₹25 lakh = ₹2.5 million and ₹3 crore = ₹30 million.'],
        'crore' => ['How many lakh make 1 crore?', '100 lakh make 1 crore (1,00,00,000). A crore is also 10 million.'],
        'acre' => ['How many vigha and guntha are in 1 acre in Gujarat?', '1 acre = 40 guntha = 2.5 Gujarat vigha = 43,560 sq ft.'],
        'hectare' => ['How do I read hectare-are figures on a 7/12 extract?', 'Land records in Gujarat show area as hectare-are-square metre. 1 hectare = 100 are = 10,000 m² ≈ 2.47 acre ≈ 6.18 Gujarat vigha; 1 are = 100 m².'],
    ];
    $out = [];
    foreach (array_unique([$from, $to]) as $u) if (isset($q[$u])) $out[] = $q[$u];
    return array_slice($out, 0, 2);
}
