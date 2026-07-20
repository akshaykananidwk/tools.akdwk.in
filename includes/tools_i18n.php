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
    }
    return $tool['guide'] ?? [];
}

/** 3 FAQ [question, answer] pairs for a tool in the current language. */
function tool_faq(array $tool): array {
    if (current_lang() === 'en') {
        $c = kt_en_content();
        if (!empty($c[$tool['slug']]['faq'])) return $c[$tool['slug']]['faq'];
    }
    return $tool['faq'] ?? [];
}
