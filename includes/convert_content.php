<?php
/**
 * KRISHNA TOOLS — unique, useful text for every /convert/ page.
 *
 * Every page gets: a direct answer, the formula, three worked examples with steps, what each unit is,
 * where the conversion is used, the reverse conversion, a practical tip, a 30–40 row table and 6–8
 * page-specific FAQs. Sentences are chosen per page (stable hash of the slug) and filled with the
 * page's own units, factors and examples, so pages don't read like copies of each other.
 */
require_once __DIR__ . '/generated_tools.php';
require_once __DIR__ . '/convert_units_info.php';

/** Short form people type in searches (qtl, kg, mg, Mbps…). */
function gen_short(string $unit, string $cat): string {
    $map = ['quintal' => 'qtl', 'metric-ton' => 'ton', 'pound' => 'lbs', 'us-ton' => 'US ton', 'square-foot' => 'sq ft', 'square-meter' => 'sq m',
        'square-yard' => 'sq yd (gaj)', 'square-kilometer' => 'sq km', 'square-centimeter' => 'sq cm', 'square-inch' => 'sq in', 'square-mile' => 'sq mi',
        'cubic-foot' => 'cft', 'cubic-meter' => 'cubic meter', 'kilometer-per-hour' => 'km/h', 'tola' => 'tola', 'bigha' => 'bigha', 'guntha' => 'guntha',
        'acre' => 'acre', 'hectare' => 'hectare', 'month' => 'month', 'year' => 'year', 'week' => 'week', 'day' => 'day', 'hour' => 'hour', 'minute' => 'min'];
    return $map[$unit] ?? gen_unit_label($cat, $unit)['symbol'];
}

/** Plural-friendly lower-case name for running text ("kilograms", "square feet"). */
function gen_plural(string $name): string {
    $n = strtolower($name);
    $irregular = ['foot' => 'feet', 'square foot' => 'square feet', 'cubic foot' => 'cubic feet', 'inch' => 'inches', 'square inch' => 'square inches',
        'cubic inch' => 'cubic inches', 'celsius' => 'degrees Celsius', 'fahrenheit' => 'degrees Fahrenheit', 'kelvin' => 'kelvin', 'rankine' => 'degrees Rankine',
        'mach' => 'Mach', 'psi' => 'psi', 'mmhg' => 'mmHg', 'torr' => 'torr', 'rpm' => 'rpm', 'hertz' => 'hertz', 'btu' => 'BTU', 'century' => 'centuries'];
    if (isset($irregular[$n])) return $irregular[$n];
    if (preg_match('/^(.+?) per (.+)$/', $n, $m)) return gen_plural($m[1]) . ' per ' . $m[2];   // "megabits per second"
    if (str_contains($n, '/') || str_contains($n, '(')) return $n;
    if (preg_match('/(s|x|ch|sh)$/', $n)) return $n . 'es';
    return $n . 's';
}

/** Stable "random" choice for this page, so the text differs between pages but never changes on reload. */
function gen_pick(string $seed, array $options, string $salt = '') {
    return $options[crc32($seed . '|' . $salt) % count($options)];
}

/** Units with number pages (1–100, 150, 200, 250, 500, 1000): the ones with real Indian search demand. */
function gen_popular_units(): array {
    return ['quintal', 'tola', 'milligram', 'millimeter', 'kilogram', 'gram', 'megabit-per-second', 'metric-ton'];
}

/** Values that get an indexed number page for a promoted pair. */
function gen_index_values(): array {
    return array_merge(range(1, 100), [150, 200, 250, 500, 1000]);
}

/** True when this pair has number pages (popular unit → one of its usual partners). */
function gen_is_promoted_pair(string $from, string $to): bool {
    return in_array($from, gen_popular_units(), true) && in_array($to, gen_main_partner($from), true);
}

/** Number pages that exist (linked + in the sitemap) for a pair. Other numbers still work but are noindex. */
function gen_value_pages_for(string $from, string $to): array {
    return gen_is_promoted_pair($from, $to) ? gen_index_values() : [];
}

/**
 * Should this number page be indexed? Yes for promoted pairs with a listed value, and for any page
 * the owner listed in Admin → SEO (pages that already get impressions in Search Console).
 */
function gen_value_indexable(string $from, string $to, float $value): bool {
    if (gen_is_promoted_pair($from, $to)) {
        foreach (gen_index_values() as $x) if (abs($x - $value) < 1e-9) return true;
    }
    return isset(gen_keep_slugs()[gen_canonical_slug($value, $from, $to)]);
}

/** Canonical slugs the owner wants kept in the index (Admin → SEO, from Search Console). */
function gen_keep_slugs(): array {
    static $keep = null;
    if ($keep !== null) return $keep;
    $keep = [];
    $raw = function_exists('setting') ? (string) setting('seo_keep_convert', '') : '';
    foreach (preg_split('/[\s,]+/', $raw) as $line) {
        $line = strtolower(trim($line));
        if ($line === '') continue;
        if (preg_match('#/convert/([a-z0-9.\-]+)#', $line, $m)) $line = $m[1];
        $p = gen_parse_slug($line);
        if ($p && $p['value'] !== null) $keep[$p['canonical_slug']] = true;
    }
    return $keep;
}

/** For each popular unit, the partner units people actually convert it to. */
function gen_main_partner(string $unit): array {
    return [
        'quintal' => ['kilogram', 'metric-ton', 'gram', 'pound'], 'tola' => ['gram', 'milligram', 'ounce', 'kilogram'],
        'milligram' => ['gram', 'kilogram', 'microgram', 'ounce'], 'millimeter' => ['centimeter', 'inch', 'meter', 'foot'],
        'kilogram' => ['pound', 'gram', 'quintal', 'metric-ton', 'ounce', 'stone'], 'gram' => ['kilogram', 'milligram', 'ounce', 'tola', 'pound'],
        'megabit-per-second' => ['megabyte-per-second', 'kilobyte-per-second', 'gigabit-per-second', 'kilobit-per-second'],
        'metric-ton' => ['kilogram', 'quintal', 'pound'], 'centimeter' => ['inch', 'millimeter', 'meter', 'foot'],
        'pound' => ['kilogram', 'gram', 'ounce'], 'bigha' => ['guntha', 'acre', 'hectare', 'square-foot', 'square-meter'],
        'guntha' => ['square-foot', 'acre', 'square-meter', 'bigha'], 'acre' => ['hectare', 'square-foot', 'guntha', 'bigha', 'square-meter'],
        'square-foot' => ['square-meter', 'square-yard', 'acre', 'guntha'],
    ][$unit] ?? [];
}

/** Factor for linear categories (1 from = factor to), or null for temperature / fuel. */
function gen_factor(string $cat, string $from, string $to): ?float {
    $c = gen_unit_categories()[$cat];
    if (!empty($c['special'])) return null;
    return $c['units'][$from][0] / $c['units'][$to][0];
}

/** A tidy number for running text: "3,000", "0.45359", "1.2e-7". */
function gen_n(float $n): string { return gen_fmt($n); }

/** Formula pieces for this pair. */
function gen_formula(string $cat, string $from, string $to): array {
    $fs = gen_short($from, $cat); $ts = gen_short($to, $cat);
    if ($cat === 'temperature') {
        $f = [
            'celsius>fahrenheit' => '°F = °C × 9/5 + 32', 'fahrenheit>celsius' => '°C = (°F − 32) × 5/9', 'celsius>kelvin' => 'K = °C + 273.15',
            'kelvin>celsius' => '°C = K − 273.15', 'fahrenheit>kelvin' => 'K = (°F − 32) × 5/9 + 273.15', 'kelvin>fahrenheit' => '°F = (K − 273.15) × 9/5 + 32',
            'celsius>rankine' => '°R = (°C + 273.15) × 9/5', 'rankine>celsius' => '°C = °R × 5/9 − 273.15', 'fahrenheit>rankine' => '°R = °F + 459.67',
            'rankine>fahrenheit' => '°F = °R − 459.67', 'kelvin>rankine' => '°R = K × 9/5', 'rankine>kelvin' => 'K = °R × 5/9',
        ][$from . '>' . $to] ?? '';
        return ['text' => $f, 'op' => 'formula', 'factor' => null];
    }
    if ($cat === 'fuel') {
        $one = gen_convert($cat, 1, $from, $to);
        if ($to === 'liter-per-100km' || $from === 'liter-per-100km') {
            $k = 100 * ($from === 'liter-per-100km' ? (1 / gen_unit_categories()['fuel']['units'][$to][0]) : gen_unit_categories()['fuel']['units'][$from][0]);
            return ['text' => "$ts = " . gen_n($k) . " ÷ $fs", 'op' => 'inverse', 'factor' => $k];
        }
        return ['text' => "$ts = $fs × " . gen_n($one), 'op' => 'multiply', 'factor' => $one];
    }
    $f = gen_factor($cat, $from, $to);
    $inv = 1 / $f;
    // Prefer "÷ 100" over "× 0.01" when the inverse is a clean number.
    if ($f < 1 && abs($inv - round($inv)) < 1e-9 && $inv <= 1e12) {
        return ['text' => "$ts = $fs ÷ " . gen_n(round($inv)), 'op' => 'divide', 'factor' => $f, 'div' => round($inv)];
    }
    return ['text' => "$ts = $fs × " . gen_n($f), 'op' => 'multiply', 'factor' => $f];
}

/** One worked example with plain-language steps. */
function gen_example(string $cat, float $v, string $from, string $to): array {
    $fs = gen_short($from, $cat); $ts = gen_short($to, $cat);
    $r = gen_convert($cat, $v, $from, $to);
    $fm = gen_formula($cat, $from, $to);
    $steps = ["Start with the value: <b>" . gen_n($v) . " $fs</b>."];
    if ($fm['op'] === 'formula') {
        $steps[] = 'Use the formula <b>' . e($fm['text']) . '</b>.';
        $steps[] = 'Put the value in: ' . e(str_replace(['°C', '°F', 'K', '°R'], gen_n($v), explode(' = ', $fm['text'])[1] ?? $fm['text'])) . ' = <b>' . gen_n($r) . " $ts</b>.";
    } elseif ($fm['op'] === 'inverse') {
        $steps[] = 'Divide ' . gen_n($fm['factor']) . ' by the value: ' . gen_n($fm['factor']) . ' ÷ ' . gen_n($v) . '.';
        $steps[] = 'Result: <b>' . gen_n($r) . " $ts</b>.";
    } elseif ($fm['op'] === 'divide') {
        $steps[] = "Divide by " . gen_n($fm['div']) . " (because 1 $ts = " . gen_n($fm['div']) . " $fs).";
        $steps[] = gen_n($v) . ' ÷ ' . gen_n($fm['div']) . ' = <b>' . gen_n($r) . " $ts</b>.";
    } else {
        $steps[] = "Multiply by " . gen_n($fm['factor']) . " (because 1 $fs = " . gen_n($fm['factor']) . " $ts).";
        $steps[] = gen_n($v) . ' × ' . gen_n($fm['factor']) . ' = <b>' . gen_n($r) . " $ts</b>.";
    }
    return ['v' => $v, 'r' => $r, 'steps' => $steps];
}

/** 30–40 table values, centred on the page's own number for number pages. */
function gen_table_values(?float $value, string $cat, string $from): array {
    $base = [0.1, 0.25, 0.5, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 12, 15, 20, 25, 30, 40, 50, 60, 70, 75, 80, 90, 100, 150, 200, 250, 300, 400, 500, 750, 1000, 2000, 5000];
    if ($cat === 'temperature') $base = [-40, -30, -20, -10, -5, 0, 5, 10, 15, 20, 21, 22, 24, 25, 27, 30, 32, 35, 36, 37, 38, 39, 40, 45, 50, 60, 70, 80, 90, 100, 120, 150, 180, 200, 250, 300];
    if ($value !== null) {
        $near = [];
        $step = $value >= 100 ? 10 : ($value >= 10 ? 1 : 0.5);
        for ($k = -5; $k <= 5; $k++) { $x = round($value + $k * $step, 4); if ($x > 0 || $cat === 'temperature') $near[] = $x; }
        $base = array_values(array_unique(array_merge($near, $base), SORT_REGULAR));
        sort($base);
        $base = array_slice($base, 0, 40);
    }
    return $base;
}

/** All the text blocks for a converter page. */
function gen_page_content(array $p): array {
    $cat = $p['cat']; $from = $p['from']; $to = $p['to']; $value = $p['value'];
    $seed = $p['canonical_slug'];
    $info = gen_units_info(); $cinfo = gen_category_info()[$cat] ?? ['why' => '', 'tip' => ''];
    $fl = gen_unit_label($cat, $from); $tl = gen_unit_label($cat, $to);
    $fN = $fl['name']; $tN = $tl['name']; $fP = gen_plural($fN); $tP = gen_plural($tN);
    $fs = gen_short($from, $cat); $ts = gen_short($to, $cat);
    $one = gen_convert($cat, 1, $from, $to); $back = gen_convert($cat, 1, $to, $from);
    $fm = gen_formula($cat, $from, $to);
    $fi = $info[$from] ?? ['about' => "$fN is a unit of " . strtolower(gen_unit_categories()[$cat]['name']) . '.', 'use' => 'science and everyday measurement', 'ex' => [1, 10, 100]];
    $ti = $info[$to] ?? ['about' => "$tN is a unit of " . strtolower(gen_unit_categories()[$cat]['name']) . '.', 'use' => 'science and everyday measurement', 'ex' => [1, 10, 100]];
    $v = $value ?? 1; $r = gen_convert($cat, $v, $from, $to);
    $vs = gen_n($v); $rs = gen_n($r);

    // ---- Answer (shown first, in HTML) ----
    $cap = fn(string $n, float $x) => abs($x - 1) < 1e-12 ? $n : ucwords(gen_plural($n));
    $answer = $value !== null ? "$vs " . $cap($fN, $v) . " = $rs " . $cap($tN, $r) : "1 $fN = " . gen_n($one) . ' ' . $cap($tN, $one);
    $answerSub = $value !== null
        ? "$vs $fs in $ts is $rs. " . ($fm['op'] === 'formula' ? 'Formula: ' . $fm['text'] . '.' : ($fm['op'] === 'divide' ? "Divide the $fs value by " . gen_n($fm['div']) . '.' : ($fm['op'] === 'inverse' ? 'Formula: ' . $fm['text'] . '.' : "Multiply the $fs value by " . gen_n($fm['factor']) . '.')))
        : ($fm['op'] === 'formula' ? "$fs to $ts formula: " . $fm['text'] : ($fm['op'] === 'divide' ? "$fs to $ts: divide by " . gen_n($fm['div']) : ($fm['op'] === 'inverse' ? "$fs to $ts: " . $fm['text'] : "$fs to $ts: multiply by " . gen_n($fm['factor']))));

    // ---- Intro ----
    $intro = $value !== null ? gen_pick($seed, [
        "If you have $vs $fP and want the same amount in $tP, the answer is <b>$rs $ts</b>. People search this as “$vs $fs to {$ts}”, “{$vs}{$fs} to {$ts}” or “$vs $fs in {$tP}” — all mean the same conversion, worked out below with the formula and steps.",
        "<b>$vs $fs = $rs $ts.</b> That is the exact value of $vs " . strtolower($fN) . " expressed in " . strtolower($tP) . ". Below you can see how the number is calculated, check nearby values in the table, or type any other amount into the converter.",
        "Converting $vs $fP to $tP gives <b>$rs $ts</b>. Whether you write it as “$vs $fs in {$ts}” or “$vs " . strtolower($fN) . " to " . strtolower($tN) . "”, the method is the same: " . ($fm['op'] === 'formula' ? 'apply the temperature formula' : ($fm['op'] === 'divide' ? 'divide by ' . gen_n($fm['div']) : 'multiply by the conversion factor')) . '.',
    ], 'intro') : gen_pick($seed, [
        "<b>1 $fN is equal to " . gen_n($one) . " $tP</b>, so " . ($fm['op'] === 'formula' ? "temperatures need the formula below rather than a simple multiplication." : "every $fs value can be changed to $ts with one quick step.") . " This $fs to $ts converter gives the exact answer as you type, and the guide below shows how to do it by hand.",
        "Need to change $fP into $tP? One " . strtolower($fN) . ' equals <b>' . gen_n($one) . ' ' . strtolower($tP) . "</b>. You will also see this written as “$fs to {$ts}” or “" . strtolower($fN) . " in {$ts}”. Use the live converter, or follow the formula and examples on this page.",
        "The $fN to $tN conversion is simple once you know the factor: <b>1 $fs = " . gen_n($one) . " $ts</b>. Below are the formula, three solved examples, a ready table and answers to the questions people ask most about $fs and $ts.",
    ], 'intro');

    // ---- Formula ----
    if ($fm['op'] === 'formula') {
        $formula = "Temperature scales start at different points, so the conversion uses an offset as well as a ratio. The formula from $fN to $tN is <b>" . e($fm['text']) . "</b>. Work the brackets first, then multiply or add.";
    } elseif ($fm['op'] === 'inverse') {
        $formula = "Fuel use and fuel economy are inverse measures — more km per liter means fewer liters per 100 km — so you divide instead of multiply: <b>" . e($fm['text']) . '</b>.';
    } elseif ($fm['op'] === 'divide') {
        $formula = "Since 1 $tN = " . gen_n($fm['div']) . " $fP, the rule is <b>" . e($fm['text']) . "</b>. In words: divide the number of $fP by " . gen_n($fm['div']) . " to get $tP.";
    } else {
        $formula = "Since 1 $fN = " . gen_n($fm['factor']) . " $tP, the rule is <b>" . e($fm['text']) . "</b>. In words: multiply the number of $fP by " . gen_n($fm['factor']) . " to get $tP.";
    }

    // ---- Examples ----
    $exVals = $fi['ex'] ?? [1, 10, 100];
    if ($value !== null) {
        $alt = array_values(array_filter($exVals, fn($x) => abs($x - $value) > 1e-9));
        $exVals = [$value, $alt[0] ?? $value * 2, $alt[1] ?? $value * 10];
    }
    $examples = array_map(fn($x) => gen_example($cat, (float) $x, $from, $to), array_slice($exVals, 0, 3));
    $useLine = gen_pick($seed, ["For example, someone dealing with %s might need this.", "This is the kind of calculation that comes up in %s.", "Typical situations: %s."], 'exuse');
    $exIntro = sprintf($useLine, $fi['use']);

    // ---- Where used ----
    $where = gen_pick($seed, [
        "You meet $fP in " . $fi['use'] . ", while $tP are common in " . $ti['use'] . ". " . $cinfo['why'],
        $cinfo['why'] . " In practice $fs appears in " . $fi['use'] . "; $ts is used for " . $ti['use'] . '.',
        "Converting $fs to $ts is handy because the two units live in different worlds: $fP are used in " . $fi['use'] . ", and $tP in " . $ti['use'] . '. ' . $cinfo['why'],
    ], 'where');

    // ---- Reverse ----
    $reverse = "To go the other way, 1 $tN = <b>" . gen_n($back) . " $fs</b>"
        . ($fm['op'] === 'multiply' || $fm['op'] === 'divide' ? ($fm['op'] === 'divide' ? ", so you multiply $ts by " . gen_n($fm['div']) : ', so you divide ' . $ts . ' by ' . gen_n($fm['factor'])) : '')
        . ". For example, $rs $ts is back to $vs $fs. Use the <a class=\"underline\" href=\"" . SITE_URL . "/convert/$to-to-$from\">$tN to $fN converter</a> for that direction.";

    // ---- Bigger or smaller ----
    $cmp = '';
    if ($fm['op'] === 'multiply' || $fm['op'] === 'divide') {
        $cmp = $one > 1
            ? "A $fN is bigger than a $tN — about " . gen_n($one) . " times — so the number in $ts is always larger than the number in $fs."
            : "A $fN is smaller than a $tN (1 $tN is " . gen_n($back) . " $fP), so the number in $ts is always smaller than the number in $fs.";
    }

    // ---- FAQ (6–8, specific to this page) ----
    $faq = [];
    $faq[] = ["How many $tP are in 1 $fN?", "1 $fN = " . gen_n($one) . " $tP (1 $fs = " . gen_n($one) . " $ts)."];
    $faq[] = ["How do I convert $fs to $ts?", strip_tags($formula)];
    if ($value !== null) $faq[] = ["What is $vs $fs in $ts?", "$vs $fN = $rs $tN."];
    $faq[] = ["How many $fP make 1 $tN?", "1 $tN = " . gen_n($back) . " $fP."];
    foreach (array_slice($examples, $value !== null ? 1 : 0, 2) as $ex) {
        $faq[] = ['What is ' . gen_n($ex['v']) . " $fs in $ts?", gen_n($ex['v']) . " $fP = " . gen_n($ex['r']) . " $tP."];
    }
    if ($cmp) $faq[] = ["Which is bigger, $fN or $tN?", $cmp];
    if (!empty($fi['faq'])) $faq[] = $fi['faq'];
    elseif (!empty($ti['faq'])) $faq[] = $ti['faq'];
    $faq[] = ["What is $fN used for?", ucfirst($fN) . ' is used in ' . $fi['use'] . '.'];
    $faq = array_slice($faq, 0, 8);

    // ---- Quick answers (long-tail: "5 qtl in kg", "10 mg to g") ----
    $quick = [];
    foreach (array_unique(array_merge([2, 10], array_slice($fi['ex'] ?? [], 0, 3), [50, 1000])) as $q) {
        if ($value !== null && abs($q - $value) < 1e-9) continue;
        $quick[] = gen_n((float) $q) . " $fs = " . gen_n(gen_convert($cat, (float) $q, $from, $to)) . " $ts";
        if (count($quick) >= 5) break;
    }
    $quickText = 'Quick answers: ' . implode('; ', $quick) . '.';

    // ---- The same amount in every other unit of the category (unique numbers per page) ----
    $others = [];
    foreach (gen_unit_categories()[$cat]['units'] as $uk => $u) {
        if ($uk === $from) continue;
        $others[] = ['unit' => $uk, 'name' => $u[1], 'sym' => $u[2], 'val' => gen_n(gen_convert($cat, $v, $from, $uk))];
    }

    // "What is a quintal?" / "What is an ounce?" / "What is Celsius?"
    $what = function (string $name) use ($cat): string {
        if (in_array($cat, ['temperature', 'fuel'], true)) return "What is $name";
        return 'What is ' . (preg_match('/^[aeiou]/i', $name) && !preg_match('/^(u[bcfhjkqrstvwxyz]|eu)/i', $name) ? 'an ' : 'a ') . $name;
    };

    return [
        'answer' => $answer, 'quick' => $quickText, 'others' => $others, 'vs' => $vs, 'what_from' => $what($fN), 'what_to' => $what($tN), 'answer_sub' => $answerSub, 'intro' => $intro, 'formula' => $formula, 'formula_text' => $fm['text'],
        'examples' => $examples, 'ex_intro' => $exIntro,
        'about_from' => $fi['about'], 'about_to' => $ti['about'], 'where' => $where, 'reverse' => $reverse,
        'compare' => $cmp, 'tip' => $cinfo['tip'], 'faq' => $faq,
        'fs' => $fs, 'ts' => $ts, 'fP' => $fP, 'tP' => $tP, 'fN' => $fN, 'tN' => $tN, 'one' => $one, 'back' => $back,
        'table' => gen_table_values($value, $cat, $from),
    ];
}

/** Plain number for titles/meta (no thousands commas — matches what people type: "3000 kg"). */
function gen_plain(float $n): string {
    $a = abs($n);
    if ($a != 0 && ($a >= 1e9 || $a < 1e-4)) return gen_fmt($n);
    return rtrim(rtrim(number_format($n, $a >= 100 ? 2 : ($a >= 1 ? 4 : 6), '.', ''), '0'), '.');
}

/** Name used in titles: the full name, or the short form people really type (Kg, Mbps, MB/s, Lbs). */
function gen_title_name(string $unit, string $cat): string {
    $map = ['kilogram' => 'Kg', 'metric-ton' => 'Ton', 'pound' => 'Lbs', 'kilometer-per-hour' => 'Km/h', 'mile-per-hour' => 'Mph',
        'square-foot' => 'Sq Ft', 'square-meter' => 'Sq Meter', 'square-yard' => 'Sq Yard (Gaj)', 'cubic-foot' => 'CFT'];
    if (isset($map[$unit])) return $map[$unit];
    if (in_array($cat, ['data-rate'], true)) return gen_unit_label($cat, $unit)['symbol'];
    return gen_unit_label($cat, $unit)['name'];
}

/** <title>: answer + short forms, e.g. "30 Quintal to Kilogram = 3000 kg | 30 qtl in kg". */
function gen_seo_title(array $p, array $C): string {
    $fN = gen_title_name($p['from'], $p['cat']); $tN = gen_title_name($p['to'], $p['cat']); $fs = $C['fs']; $ts = $C['ts'];
    if ($p['value'] !== null) {
        $v = gen_plain($p['value']); $r = gen_plain(gen_convert($p['cat'], $p['value'], $p['from'], $p['to']));
        $c = ["$v $fN to $tN = $r $ts | $v $fs in $ts", "$v $fN to $tN = $r $ts", "$v $fs to $ts = $r $ts"];
        if (strtolower($fN) === strtolower($fs) && strtolower($tN) === strtolower($ts)) $c = ["$v $fs to $ts = $r $ts | $v $fs in $ts converter", "$v $fs to $ts = $r $ts | $v $fs in $ts"];
        foreach ($c as $t) if (mb_strlen($t) <= 65) return $t;
        return "$v $fs to $ts = $r $ts";
    }
    $one = gen_plain($C['one']);
    $same = strtolower($fN) === strtolower($fs) && strtolower($tN) === strtolower($ts);
    if ($same) { $fN = $C['fN']; $tN = $C['tN']; }   // "Megabit/second to Megabyte/second (Mbps to MB/s)"
    $cands = $p['cat'] === 'temperature'
        ? ["$fN to $tN ($fs to $ts) — Formula & Converter", "$fN to $tN ($fs to $ts) Converter"]
        : ["$fN to $tN ($fs to $ts) — 1 $fN = $one $ts | Converter", "$fN to $tN ($fs to $ts) — 1 $fN = $one $ts", "$fN to $tN ($fs to $ts) — 1 $fs = $one $ts", "$fN to $tN ($fs to $ts) Converter"];
    foreach ($cands as $t) if (mb_strlen($t) <= 68) return $t;
    return end($cands);
}

/** Meta description (≤155): answer + formula + "free, instant". */
function gen_seo_desc(array $p, array $C): string {
    $fs = $C['fs']; $ts = $C['ts'];
    $f = $C['formula_text'];
    if ($p['value'] !== null) {
        $v = gen_plain($p['value']); $r = gen_plain(gen_convert($p['cat'], $p['value'], $p['from'], $p['to']));
        $d = "$v $fs = $r $ts. Formula: $f. Step-by-step working, nearby values and a $fs to $ts table. Free, instant converter.";
    } elseif ($p['cat'] === 'temperature') {
        $d = "Convert $fs to $ts with the formula $f. 37 $fs = " . gen_plain(gen_convert('temperature', 37, $p['from'], $p['to'])) . " $ts. Solved examples and a full table. Free, instant.";
    } else {
        $d = "1 " . strtolower($C['fN']) . " = " . gen_plain($C['one']) . " $ts. Convert $fs to $ts with the formula $f, solved examples and a ready table. Free, instant.";
    }
    if (mb_strlen($d) > 155) $d = preg_replace('/ Step-by-step working, nearby values and a .*? table\./u', ' Steps and table inside.', $d);
    if (mb_strlen($d) > 155) $d = rtrim(mb_substr($d, 0, 152), ' ,.') . '…';
    return $d;
}

/** The three ways people type the same search, used once in the page text: "30qtl to kg", "30 qtl to kg", "30 quintals in kilograms". */
function gen_search_forms(array $p, array $C): array {
    $fs = strtolower($C['fs']); $ts = strtolower($C['ts']);
    if ($p['value'] !== null) {
        $v = gen_plain($p['value']);
        return [$v . str_replace(' ', '', $fs) . " to $ts", "$v $fs to $ts", "$v " . strtolower(abs($p['value'] - 1) < 1e-9 ? $C['fN'] : $C['fP']) . ' in ' . strtolower($C['tP'])];
    }
    return ["$fs to $ts", strtolower($C['fN']) . ' to ' . strtolower($C['tN']), strtolower($C['fN']) . ' in ' . strtolower($C['tP'])];
}

/** Every converter URL that should be in Google: all pair pages + indexable number pages. [[slug, priority], …] */
function gen_sitemap_convert_urls(): array {
    $out = [];
    foreach (gen_all_pairs() as $pr) $out[] = [$pr['from'] . '-to-' . $pr['to'], gen_is_promoted_pair($pr['from'], $pr['to']) ? '0.8' : '0.6'];
    $seen = [];
    foreach (gen_all_pairs() as $pr) {
        foreach (gen_value_pages_for($pr['from'], $pr['to']) as $v) {
            $s = gen_canonical_slug((float) $v, $pr['from'], $pr['to']);
            $seen[$s] = true; $out[] = [$s, '0.5'];
        }
    }
    foreach (array_keys(gen_keep_slugs()) as $s) if (!isset($seen[$s])) $out[] = [$s, '0.5'];
    return $out;
}

/** Date the converter content last changed (sitemap lastmod). */
function gen_convert_lastmod(): string {
    $files = [__FILE__, __DIR__ . '/convert_units_info.php', __DIR__ . '/generated_tools.php', dirname(__DIR__) . '/convert.php'];
    return date('Y-m-d', max(array_map(fn($f) => (int) @filemtime($f), $files)));
}
