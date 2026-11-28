<?php
/**
 * KRISHNA TOOLS — SEO report (Admin → SEO). Crawls the site's own pages the way Google sees them
 * and lists problems: missing / too long titles and descriptions, pages under 300 words,
 * noindex pages, wrong status codes, canonical mismatches and duplicate titles.
 *
 * Full run: the "seo_report" background job (nightly) crawls every URL in the sitemap.
 * Quick run: Admin → SEO → "Check tools & pages now" crawls everything except converter pages.
 * The result is saved to logs/seo_report.json.
 */
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/convert_content.php';

const KT_SEO_REPORT_FILE = __DIR__ . '/../logs/seo_report.json';

/** Every URL in the sitemap, built from the same code the sitemaps use. */
function kt_seo_urls(bool $withConverters = true): array {
    $base = rtrim(SITE_URL, '/');
    $urls = [$base . '/', $base . '/converters.php', $base . '/blog/', $base . '/about.php', $base . '/contact.php',
             $base . '/privacy.php', $base . '/terms.php', $base . '/disclaimer.php'];
    foreach (kt_categories() as $c) $urls[] = $base . '/category/' . $c['slug'];
    $disabled = [];
    try { foreach (all("SELECT slug FROM " . tbl('tools') . " WHERE is_active = 0") as $r) $disabled[$r['slug']] = true; } catch (Throwable $e) {}
    foreach (kt_tools() as $t) if (!isset($disabled[$t['slug']])) $urls[] = $base . '/tool/' . $t['slug'];
    try { foreach (all("SELECT slug FROM " . tbl('blog_posts') . " WHERE is_published = 1") as $p) $urls[] = $base . '/blog/' . $p['slug']; } catch (Throwable $e) {}
    if ($withConverters) foreach (gen_sitemap_convert_urls() as [$slug]) $urls[] = $base . '/convert/' . $slug;
    return array_values(array_unique($urls));
}

/** Read the SEO facts out of one page's HTML. */
function kt_seo_parse(string $html): array {
    $get = function (string $re) use ($html) { return preg_match($re, $html, $m) ? html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8') : null; };
    $title = $get('#<title>(.*?)</title>#si');
    $desc = $get('#<meta\s+name="description"\s+content="([^"]*)"#i');
    $robots = $get('#<meta\s+name="robots"\s+content="([^"]*)"#i') ?? '';
    $canon = $get('#<link\s+rel="canonical"\s+href="([^"]*)"#i');
    $main = preg_match('#<main[^>]*>(.*)</main>#si', $html, $m) ? $m[1] : $html;
    $main = preg_replace('#<(script|style|nav|header|footer|aside|noscript|select|textarea)\b.*?</\1>#si', ' ', $main);
    $text = html_entity_decode(strip_tags($main), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $words = preg_match_all("/[\\p{L}\\p{N}][\\p{L}\\p{N}'’\\-.,]*/u", $text);
    return ['title' => $title, 'desc' => $desc, 'noindex' => stripos($robots, 'noindex') !== false, 'canonical' => $canon,
            'h1' => preg_match_all('#<h1[\s>]#i', $html), 'words' => $words];
}

/** Crawl URLs (8 at a time) and save the report. Returns the report array. */
function kt_seo_report_run(bool $withConverters = true, int $timeBudget = 1500): array {
    @set_time_limit($timeBudget + 60);
    $urls = kt_seo_urls($withConverters);
    $start = time(); $rows = [];
    $mh = curl_multi_init(); $queue = $urls; $active = [];
    $add = function (string $u) use ($mh, &$active) {
        $ch = curl_init($u);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'KrishnaTools-SEO-Report/1.0 (+self-check; bot)', CURLOPT_ENCODING => '']);
        curl_multi_add_handle($mh, $ch); $active[(int) $ch] = [$ch, $u];
    };
    while (($queue || $active) && time() - $start < $timeBudget) {
        while ($queue && count($active) < 8) $add(array_shift($queue));
        curl_multi_exec($mh, $running); curl_multi_select($mh, 1.0);
        while ($info = curl_multi_info_read($mh)) {
            $ch = $info['handle']; [, $u] = $active[(int) $ch]; unset($active[(int) $ch]);
            $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            $html = (string) curl_multi_getcontent($ch);
            curl_multi_remove_handle($mh, $ch); curl_close($ch);
            $rows[$u] = ['code' => $code] + ($code === 200 ? kt_seo_parse($html) : []);
        }
    }
    curl_multi_close($mh);
    $report = ['at' => date('Y-m-d H:i:s'), 'full' => $withConverters, 'planned' => count($urls), 'checked' => count($rows),
               'seconds' => time() - $start, 'pages' => $rows];
    @mkdir(dirname(KT_SEO_REPORT_FILE), 0755, true);
    @file_put_contents(KT_SEO_REPORT_FILE, json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    return $report;
}

function kt_seo_report_load(): ?array {
    $j = is_file(KT_SEO_REPORT_FILE) ? json_decode((string) file_get_contents(KT_SEO_REPORT_FILE), true) : null;
    return is_array($j) ? $j : null;
}

/** Problems found in a report, grouped: [group key => [label, [url => detail]]]. */
function kt_seo_issues(array $report): array {
    $g = ['status' => ['Not 200 (error or redirect)', []], 'no_title' => ['Missing title', []], 'long_title' => ['Title over 70 characters', []],
          'no_desc' => ['Missing meta description', []], 'bad_desc' => ['Description under 70 or over 160 characters', []],
          'thin' => ['Under 300 words of content', []], 'noindex' => ['noindex page in the sitemap', []], 'canon' => ['Canonical is not the page itself', []],
          'h1' => ['Not exactly one H1', []], 'dup' => ['Duplicate title', []]];
    $titles = [];
    foreach ($report['pages'] as $u => $p) {
        if (($p['code'] ?? 0) !== 200) { $g['status'][1][$u] = 'HTTP ' . ($p['code'] ?: 'timeout'); continue; }
        $tl = mb_strlen((string) $p['title']); $dl = mb_strlen((string) $p['desc']);
        if (!$tl) $g['no_title'][1][$u] = ''; elseif ($tl > 70) $g['long_title'][1][$u] = "$tl chars: " . $p['title'];
        if (!$dl) $g['no_desc'][1][$u] = ''; elseif ($dl < 70 || $dl > 160) $g['bad_desc'][1][$u] = "$dl chars";
        if ($p['words'] < 300) $g['thin'][1][$u] = $p['words'] . ' words';
        if ($p['noindex']) $g['noindex'][1][$u] = 'robots: noindex';
        if ($p['canonical'] && rtrim($p['canonical'], '/') !== rtrim(preg_replace('#\?.*$#', '', $u), '/')) $g['canon'][1][$u] = '→ ' . $p['canonical'];
        if ((int) $p['h1'] !== 1) $g['h1'][1][$u] = $p['h1'] . ' H1';
        if ($tl) $titles[$p['title']][] = $u;
    }
    foreach ($titles as $t => $us) if (count($us) > 1) foreach ($us as $u) $g['dup'][1][$u] = $t;
    return $g;
}
