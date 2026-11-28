<?php
/**
 * KRISHNA TOOLS — Gujarati and Hindi converter pages (/gu/convert/…, /hi/convert/…).
 *
 * Which pages exist (at most 500 per language), unit names as people say them
 * in Gujarat and in Hindi-speaking states, and the hreflang map that links each
 * page to its English twin.
 */
require_once __DIR__ . '/regional_units.php';

/** The languages that have local converter pages. */
function gen_local_langs(): array { return ['gu', 'hi']; }

/**
 * Unit names. [gu name, hi name, hinglish word, gu plural/oblique (optional), hi oblique (optional)]
 * The names are the words used in the market and in daily talk ("કિલો", not "કિલોગ્રામ"),
 * with the formal word explained in the page text.
 */
function gen_local_names(): array {
    return [
        // weight
        'kilogram' => ['કિલો', 'किलो', 'kilo'],
        'gram' => ['ગ્રામ', 'ग्राम', 'gram'],
        'milligram' => ['મિલિગ્રામ', 'मिलीग्राम', 'mg'],
        'microgram' => ['માઇક્રોગ્રામ', 'माइक्रोग्राम', 'mcg'],
        'metric-ton' => ['ટન', 'टन', 'ton'],
        'quintal' => ['ક્વિન્ટલ', 'क्विंटल', 'quintal'],
        'tola' => ['તોલા', 'तोला', 'tola'],
        'maund' => ['મણ', 'मन', 'man'],
        'ser' => ['શેર', 'सेर', 'ser'],
        'pound' => ['પાઉન્ડ', 'पाउंड', 'pound'],
        'ounce' => ['ઔંસ', 'औंस', 'ounce'],
        'stone' => ['સ્ટોન', 'स्टोन', 'stone'],
        // length
        'meter' => ['મીટર', 'मीटर', 'meter'],
        'centimeter' => ['સેન્ટિમીટર', 'सेंटीमीटर', 'cm'],
        'millimeter' => ['મિલિમીટર', 'मिलीमीटर', 'mm'],
        'mile' => ['માઇલ', 'मील', 'mile'],
        'foot' => ['ફૂટ', 'फीट', 'feet'],
        'inch' => ['ઇંચ', 'इंच', 'inch'],
        // temperature
        'celsius' => ['સેલ્સિયસ', 'सेल्सियस', 'celsius'],
        'fahrenheit' => ['ફેરનહીટ', 'फ़ारेनहाइट', 'fahrenheit'],
        // area
        'acre' => ['એકર', 'एकड़', 'acre'],
        'hectare' => ['હેક્ટર', 'हेक्टेयर', 'hectare'],
        'bigha' => ['વીઘા', 'बीघा', 'bigha'],
        'guntha' => ['ગુંઠા', 'गुंठा', 'guntha'],
        'square-foot' => ['ચોરસ ફૂટ', 'वर्ग फुट', 'sq ft'],
        'square-yard' => ['ચોરસ વાર', 'वर्ग गज', 'gaj'],
        'square-meter' => ['ચોરસ મીટર', 'वर्ग मीटर', 'sq meter'],
        // volume
        'milliliter' => ['મિલિ', 'मिलीलीटर', 'ml'],
        'cup' => ['કપ', 'कप', 'cup'],
        'quart' => ['ક્વાર્ટ', 'क्वार्ट', 'quart'],
        'gallon-us' => ['US ગેલન', 'US गैलन', 'gallon'],
        // time
        'second' => ['સેકન્ડ', 'सेकंड', 'second'],
        'minute' => ['મિનિટ', 'मिनट', 'minute'],
        'hour' => ['કલાક', 'घंटे', 'ghante'],
        'day' => ['દિવસ', 'दिन', 'din'],
        'month' => ['મહિના', 'महीने', 'mahine', 'મહિનો'],
        // data rate
        'kilobit-per-second' => ['Kbps', 'Kbps', 'kbps'],
        'megabit-per-second' => ['Mbps', 'Mbps', 'mbps'],
        'gigabit-per-second' => ['Gbps', 'Gbps', 'gbps'],
        'kilobyte-per-second' => ['KB/s', 'KB/s', 'KB/s'],
        'megabyte-per-second' => ['MB/s', 'MB/s', 'MB/s'],
        // pressure
        'bar' => ['બાર', 'बार', 'bar'],
        'psi' => ['PSI', 'PSI', 'psi'],
        // Indian numbers
        'thousand' => ['હજાર', 'हज़ार', 'hazar'],
        'lakh' => ['લાખ', 'लाख', 'lakh'],
        'crore' => ['કરોડ', 'करोड़', 'crore'],
        'million' => ['મિલિયન', 'मिलियन', 'million'],
        'billion' => ['બિલિયન', 'बिलियन', 'billion'],
    ];
}

/** Unit name in a language; $one = true gives the singular where Gujarati has one ("1 મહિનો"). */
function gen_lname(string $unit, string $lang, bool $one = false): string {
    $n = gen_local_names()[$unit] ?? null;
    if (!$n) return $unit;
    if ($lang === 'gu') return $one && !empty($n[3]) ? $n[3] : $n[0];
    if ($lang === 'hi') return $n[1];
    return $n[2];
}

/** Pairs of Indian units that get a Gujarati and Hindi pair page (besides the 46 popular pairs). */
function gen_local_indian_pairs(): array {
    return [
        // weight
        'maund>kilogram', 'kilogram>maund', 'maund>quintal', 'quintal>maund', 'ser>kilogram', 'kilogram>ser', 'ser>gram', 'maund>ser',
        'kilogram>tola', 'tola>ser',
        // land
        'bigha>square-foot', 'square-foot>bigha', 'bigha>guntha', 'guntha>bigha', 'bigha>acre', 'acre>bigha', 'bigha>hectare', 'hectare>bigha',
        'bigha>square-meter', 'bigha>square-yard', 'square-yard>bigha',
        'guntha>square-foot', 'square-foot>guntha', 'guntha>acre', 'acre>guntha', 'guntha>square-meter', 'guntha>square-yard', 'square-yard>guntha',
        'acre>hectare', 'hectare>acre', 'square-foot>acre', 'hectare>square-foot', 'acre>square-yard', 'hectare>guntha',
        'square-yard>square-foot', 'square-foot>square-yard', 'square-yard>square-meter', 'square-meter>square-yard',
        'square-meter>square-foot', 'square-foot>square-meter',
        // Indian numbers
        'crore>lakh', 'lakh>crore', 'lakh>million', 'million>lakh', 'crore>million', 'million>crore', 'billion>crore', 'crore>billion',
        'lakh>thousand', 'thousand>lakh',
    ];
}

/** Number pages in Gujarati and Hindi: only quintal, tola, maund and vigha. */
function gen_local_value_pairs(): array {
    return ['quintal>kilogram', 'tola>gram', 'maund>kilogram', 'bigha>guntha', 'bigha>square-foot', 'bigha>acre'];
}

/** Values of the local number pages (61 per pair). */
function gen_local_values(): array {
    return array_merge(range(1, 50), [60, 70, 75, 80, 90, 100, 150, 200, 250, 500, 1000]);
}

/** Every local page slug (same slug as the English page), pair pages first. Same list for gu and hi. */
function gen_local_slugs(): array {
    static $list = null;
    if ($list !== null) return $list;
    $pairs = [];
    foreach (gen_unit_categories() as $cat => $m) {
        foreach (array_keys($m['units']) as $a) foreach (array_keys($m['units']) as $b) {
            if ($a !== $b && gen_is_promoted_pair($a, $b) && isset(gen_local_names()[$a], gen_local_names()[$b])) $pairs["$a>$b"] = true;
        }
    }
    foreach (gen_local_indian_pairs() as $p) $pairs[$p] = true;
    $list = [];
    foreach (array_keys($pairs) as $p) { [$a, $b] = explode('>', $p); $list[] = "$a-to-$b"; }
    foreach (gen_local_value_pairs() as $p) {
        [$a, $b] = explode('>', $p);
        foreach (gen_local_values() as $v) $list[] = gen_canonical_slug((float) $v, $a, $b);
    }
    return $list;
}

function gen_local_exists(string $slug): bool {
    static $set = null;
    if ($set === null) $set = array_flip(gen_local_slugs());
    return isset($set[$slug]);
}

/** hreflang map for a converter slug: en (+ gu, hi when they exist) and x-default → English. */
function gen_alternates(string $slug): array {
    $en = SITE_URL . '/convert/' . $slug;
    if (!gen_local_exists($slug)) return [];
    return ['en' => $en, 'gu' => SITE_URL . '/gu/convert/' . $slug, 'hi' => SITE_URL . '/hi/convert/' . $slug, 'x-default' => $en];
}
