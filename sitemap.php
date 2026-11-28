<?php
/**
 * KRISHNA TOOLS — sitemap INDEX: one sitemap per section so Search Console shows indexing per category.
 *   pages (home, hubs, categories, blog) · tools per tool category · converters per unit category
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/convert_content.php';

header('Content-Type: application/xml; charset=utf-8');
$base = rtrim(SITE_URL, '/');

$toolMod = date('Y-m-d', max(array_map(fn($f) => (int) @filemtime($f), array_merge(glob(__DIR__ . '/includes/tools_more/*.php') ?: [],
    [__DIR__ . '/includes/tools_registry.php', __DIR__ . '/includes/seo.php', __DIR__ . '/includes/tool_wrapper.php']))));
$convMod = gen_convert_lastmod();

$maps = [["$base/sitemap-main.xml?part=pages", null]];
$toolCats = [];
foreach (kt_tools() as $t) $toolCats[$t['cat']] = true;
foreach (kt_categories() as $c) if (isset($toolCats[$c['slug']])) $maps[] = ["$base/sitemap-main.xml?part=tools&cat=" . $c['slug'], $toolMod];
$convCats = [];
foreach (gen_sitemap_convert_urls() as [$slug]) { $p = gen_parse_slug($slug); if ($p) $convCats[$p['cat']] = true; }
foreach (array_keys(gen_unit_categories()) as $uc) if (isset($convCats[$uc])) $maps[] = ["$base/sitemap-convert.xml?cat=$uc", $convMod];

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($maps as [$loc, $mod]) {
    echo '  <sitemap><loc>' . htmlspecialchars($loc, ENT_XML1) . '</loc>' . ($mod ? "<lastmod>$mod</lastmod>" : '') . "</sitemap>\n";
}
echo '</sitemapindex>' . "\n";
