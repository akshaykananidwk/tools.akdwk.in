<?php
/**
 * KRISHNA TOOLS — main XML sitemap (pages, categories, tools, blog).
 *   /sitemap-main.xml                     → everything below (kept for old Search Console submissions)
 *   /sitemap-main.xml?part=pages          → home, hubs, static pages, categories, blog
 *   /sitemap-main.xml?part=tools&cat=pdf  → the tools of one category
 * Tools switched off in Admin → Tools are left out.
 */
require_once __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/xml; charset=utf-8');

$base = rtrim(SITE_URL, '/');
$part = $_GET['part'] ?? '';
$onlyCat = preg_replace('/[^a-z0-9-]/', '', (string) ($_GET['cat'] ?? ''));

$mtime = fn(array $files) => date('Y-m-d', max(array_map(fn($f) => (int) @filemtime($f), $files)));
$siteMod = $mtime([__DIR__ . '/index.php', __DIR__ . '/includes/header.php', __DIR__ . '/includes/footer.php']);
$toolMod = $mtime(array_merge(glob(__DIR__ . '/includes/tools_more/*.php') ?: [], glob(__DIR__ . '/includes/en/*.php') ?: [],
    [__DIR__ . '/includes/tools_registry.php', __DIR__ . '/includes/seo.php', __DIR__ . '/includes/tool_wrapper.php']));

$disabled = [];
try { foreach (all("SELECT slug FROM " . tbl('tools') . " WHERE is_active = 0") as $r) $disabled[$r['slug']] = true; } catch (Throwable $e) {}

/** [loc, lastmod|null, changefreq, priority] rows. */
$urls = [];
if ($part === '' || $part === 'pages') {
    $urls[] = [$base . '/', $siteMod, 'daily', '1.0'];
    $urls[] = [$base . '/converters.php', $siteMod, 'weekly', '0.8'];
    $urls[] = [$base . '/blog/', null, 'weekly', '0.7'];
    $urls[] = [$base . '/pricing.php', null, 'monthly', '0.4'];
    $urls[] = [$base . '/about.php', date('Y-m-d', (int) @filemtime(__DIR__ . '/about.php')), 'monthly', '0.4'];
    $urls[] = [$base . '/contact.php', null, 'monthly', '0.4'];
    foreach (['privacy', 'terms', 'disclaimer'] as $lp) $urls[] = [$base . "/$lp.php", date('Y-m-d', (int) @filemtime(__DIR__ . "/$lp.php")), 'yearly', '0.3'];
    foreach (kt_categories() as $c) $urls[] = [$base . '/category/' . $c['slug'], $toolMod, 'weekly', '0.8'];
    try {
        foreach (all("SELECT slug, created_at FROM " . tbl('blog_posts') . " WHERE is_published = 1") as $p) {
            $urls[] = [$base . '/blog/' . $p['slug'], $p['created_at'] ? date('Y-m-d', strtotime($p['created_at'])) : null, 'monthly', '0.6'];
        }
    } catch (Throwable $e) { /* DB not ready — skip blog URLs */ }
}
if ($part === '' || $part === 'tools') {
    foreach (kt_tools() as $t) {
        if (isset($disabled[$t['slug']]) || ($onlyCat !== '' && $t['cat'] !== $onlyCat)) continue;
        $urls[] = [$base . '/tool/' . $t['slug'], $toolMod, 'weekly', '0.7'];
    }
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as [$loc, $lastmod, $changefreq, $priority]) {
    echo '  <url><loc>' . htmlspecialchars($loc, ENT_XML1, 'UTF-8') . '</loc>'
       . ($lastmod ? '<lastmod>' . $lastmod . '</lastmod>' : '')
       . '<changefreq>' . $changefreq . '</changefreq><priority>' . $priority . "</priority></url>\n";
}
echo '</urlset>' . "\n";
