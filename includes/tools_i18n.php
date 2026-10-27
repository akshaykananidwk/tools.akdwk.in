<?php
/**
 * KRISHNA TOOLS — per-tool English guide + FAQ content loader.
 *
 * The registry (tools_registry.php) carries Gujarati guides/FAQs inline. The
 * English equivalents live in /includes/en/*.php (each returns [slug => [...]]).
 * These helpers return the language-appropriate guide/FAQ for a tool, falling
 * back to the registry's Gujarati content when an English entry is missing.
 */

/** Merge all English content batch files (cached). */
function kt_en_content(): array {
    static $cache = null;
    if ($cache !== null) return $cache;
    $cache = [];
    foreach (glob(__DIR__ . '/en/*.php') as $file) {
        $arr = require $file;
        if (is_array($arr)) $cache = array_merge($cache, $arr);
    }
    return $cache;
}

/** 4-step how-to guide for a tool in the current language. */
function tool_guide(array $tool): array {
    if (current_lang() === 'en') {
        $c = kt_en_content();
        if (!empty($c[$tool['slug']]['guide'])) return $c[$tool['slug']]['guide'];
        if (!empty($tool['guide_en'])) return $tool['guide_en'];
    }
    return $tool['guide'] ?? [];
}

/** 3 FAQ [question, answer] pairs for a tool in the current language. */
function tool_faq(array $tool): array {
    $faq = tool_faq_raw($tool);
    if (!function_exists('kt_all_free') || !kt_all_free()) return $faq;
    // While every tool is free, answers that talk about daily limits / premium
    // plans are out of date: replace them with the current "fully free" answer.
    $free = current_lang() === 'en'
        ? 'Yes, this tool is completely free with no daily limit, no watermark and no login.'
        : 'હા, આ ટૂલ સંપૂર્ણ ફ્રી છે. કોઈ દૈનિક મર્યાદા, વોટરમાર્ક કે લોગિનની જરૂર નથી.';
    foreach ($faq as &$qa) {
        if (preg_match('/premium|પ્રીમિયમ|per day|free plan|daily limit|દરરોજ|ફ્રી માં/iu', $qa[1] ?? '')) {
            $qa[1] = $free;
        }
    }
    return $faq;
}

function tool_faq_raw(array $tool): array {
    if (current_lang() === 'en') {
        $c = kt_en_content();
        if (!empty($c[$tool['slug']]['faq'])) return $c[$tool['slug']]['faq'];
        if (!empty($tool['faq_en'])) return $tool['faq_en'];
    }
    return $tool['faq'] ?? [];
}
