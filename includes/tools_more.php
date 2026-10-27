<?php
/**
 * KRISHNA TOOLS — industry tool packs (restaurant, hotel, salon, doctor,
 * contractor, electrician, printing, media, student, fuel, finance, wishes…).
 *
 * Each file in /includes/tools_more/*.php returns:
 *   ['cats' => [category defs], 'tools' => [compact tool defs]]
 * A compact def is pure data; one of four engines in /assets/js/engines-more.js
 * runs it (formula | textgen | card | doc). This file expands compact defs into
 * the full registry shape (names, descriptions, guide + FAQ in both languages)
 * so they behave exactly like the hand-written tools in tools_registry.php.
 */

/** Raw packs, loaded once per request. */
function kt_more_packs(): array {
    static $packs = null;
    if ($packs !== null) return $packs;
    $packs = ['cats' => [], 'tools' => []];
    $files = glob(__DIR__ . '/tools_more/*.php') ?: [];
    sort($files);
    foreach ($files as $file) {
        $p = require $file;
        if (!is_array($p)) continue;
        foreach ($p['cats'] ?? [] as $c) $packs['cats'][] = $c;
        foreach ($p['tools'] ?? [] as $t) $packs['tools'][] = $t;
    }
    return $packs;
}

/** Extra categories contributed by the packs (sorted after the core ones). */
function kt_more_categories(): array {
    $out = [];
    $i = 100;
    foreach (kt_more_packs()['cats'] as $c) {
        $out[] = [
            'slug' => $c['slug'], 'name_en' => $c['name_en'], 'name_gu' => $c['name_gu'],
            'icon' => $c['icon'] ?? 'grid', 'color' => $c['color'] ?? '#334155',
            'sort_order' => $c['sort_order'] ?? $i++,
        ];
    }
    return $out;
}

/** Compact pack defs expanded into full registry tool arrays. */
function kt_more_tools(): array {
    static $tools = null;
    if ($tools !== null) return $tools;
    $engine = ['calc' => 'formula', 'text' => 'textgen', 'card' => 'card', 'doc' => 'doc'];
    $tools = [];
    foreach (kt_more_packs()['tools'] as $t) {
        $type = $t['type'] ?? 'calc';
        $opt = array_intersect_key($t, array_flip(['in', 'out', 'tpl_en', 'tpl_gu', 'card', 'doc']));
        $opt['slug'] = $t['slug'];
        [$gGu, $gEn] = kt_more_guide($t, $type);
        [$fGu, $fEn] = kt_more_faq($t, $type);
        $tools[] = [
            'slug' => $t['slug'], 'cat' => $t['cat'], 'name_gu' => $t['gu'], 'name_en' => $t['en'],
            'icon' => $t['icon'] ?? 'calculator', 'premium' => 0, 'engine' => $engine[$type] ?? 'formula',
            'opt' => $opt, 'desc_gu' => $t['dg'], 'desc_en' => $t['de'],
            'guide' => $gGu, 'faq' => $fGu, 'guide_en' => $gEn, 'faq_en' => $fEn,
        ];
    }
    return $tools;
}

/** Input labels joined for the "how to use" step. */
function kt_more_labels(array $t, string $lang): string {
    $l = array_map(fn($i) => $i[$lang] ?? $i['en'], $t['in'] ?? []);
    $l = array_slice($l, 0, 4);
    return implode(', ', $l);
}

function kt_more_guide(array $t, string $type): array {
    $inGu = kt_more_labels($t, 'gu');
    $inEn = kt_more_labels($t, 'en');
    $out0 = $t['out'][0] ?? null;
    switch ($type) {
        case 'text':
            return [
                ['વિગત ભરો' . ($inGu ? " ($inGu)" : ''), 'ભાષા પસંદ કરો (ગુજરાતી કે English)', 'તૈયાર મેસેજમાંથી ગમતો પસંદ કરો', 'કોપી કરો અથવા સીધું WhatsApp પર મોકલો'],
                ['Fill in the details' . ($inEn ? " ($inEn)" : ''), 'Choose the language (Gujarati or English)', 'Pick the message you like from the ready-made list', 'Copy it or send it straight to WhatsApp'],
            ];
        case 'card':
            return [
                ['લખાણ ભરો' . ($inGu ? " ($inGu)" : ''), 'સાઇઝ (સ્ટેટસ, પોસ્ટ, પોસ્ટર) અને રંગ પસંદ કરો', 'નીચે તૈયાર ડિઝાઇનનો પ્રિવ્યૂ જુઓ', 'PNG ડાઉનલોડ કરો અથવા WhatsApp પર મોકલો'],
                ['Type your text' . ($inEn ? " ($inEn)" : ''), 'Choose a size (status, post, poster) and colours', 'Check the live preview below', 'Download the PNG or share it on WhatsApp'],
            ];
        case 'doc':
            return [
                ['ધંધાનું નામ, સરનામું અને ફોન એક વખત ભરો (બ્રાઉઝરમાં સેવ રહેશે)', 'વિગત ભરો' . ($inGu ? " ($inGu)" : '') . ' અને જરૂર હોય તો લાઇન ઉમેરો', 'નીચે તૈયાર ડોક્યુમેન્ટનો પ્રિવ્યૂ તપાસો', 'પ્રિન્ટ કરો, PDF સેવ કરો કે WhatsApp પર મોકલો'],
                ['Enter your business name, address and phone once (saved in your browser)', 'Fill in the details' . ($inEn ? " ($inEn)" : '') . ' and add lines if needed', 'Check the document preview below', 'Print it, save as PDF or send on WhatsApp'],
            ];
        default:
            return [
                [($inGu ?: 'વિગત') . ' દાખલ કરો', 'પરિણામ તરત જ આપોઆપ ગણાશે', ($out0['gu'] ?? 'પરિણામ') . ' અને બીજાં પરિણામ જુઓ', 'પરિણામ કોપી કરો, પ્રિન્ટ કરો કે WhatsApp પર મોકલો'],
                ['Enter ' . ($inEn ?: 'the values'), 'The result is calculated instantly as you type', 'Read the ' . ($out0['en'] ?? 'result') . ' and the other results', 'Copy, print or share the result on WhatsApp'],
            ];
    }
}

function kt_more_faq(array $t, string $type): array {
    $gu = []; $en = [];
    if (!empty($t['how_en'])) {
        $gu[] = [$t['gu'] . ' કેવી રીતે ગણાય છે?', $t['how_gu'] ?? $t['how_en']];
        $en[] = ['How does the ' . $t['en'] . ' calculate the result?', $t['how_en']];
    }
    foreach ($t['faq'] ?? [] as $f) {
        $en[] = [$f[0], $f[1]];
        $gu[] = [$f[2] ?? $f[0], $f[3] ?? $f[1]];
    }
    $gu[] = ['શું ' . $t['gu'] . ' ફ્રી છે?', 'હા, સંપૂર્ણ ફ્રી છે. લોગિન કે રજિસ્ટ્રેશનની જરૂર નથી અને ગમે તેટલી વાર વાપરી શકો.'];
    $en[] = ['Is the ' . $t['en'] . ' free?', 'Yes, it is completely free with no login or sign-up, and you can use it as many times as you like.'];
    $gu[] = ['મારી માહિતી સુરક્ષિત છે?', 'હા. બધું તમારા બ્રાઉઝરમાં જ થાય છે, તમારી માહિતી અમારા સર્વર પર મોકલાતી નથી.'];
    $en[] = ['Is my data safe?', 'Yes. Everything runs inside your browser and the details you type are not sent to our server.'];
    if ($type === 'calc') {
        $gu[] = ['મોબાઇલ પર ચાલે છે?', 'હા, મોબાઇલ, ટેબ્લેટ અને કમ્પ્યુટર બધા પર ચાલે છે. પરિણામ WhatsApp પર પણ મોકલી શકો.'];
        $en[] = ['Does it work on mobile?', 'Yes, it works on phones, tablets and computers, and you can share the result on WhatsApp.'];
    }
    return [$gu, $en];
}
