<?php
/**
 * KRISHNA TOOLS — converter sitemap (paginated ?p=N, 40,000 URLs per file; ?cat=weight for one unit category).
 * Lists only pages meant for Google: every unit pair page, number pages of popular pairs
 * (1–100, 150, 200, 250, 500, 1000) and pages listed in Admin → SEO. Other number pages
 * still work for visitors but carry noindex, so they are left out here.
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/convert_content.php';

header('Content-Type: application/xml; charset=utf-8');
$base = rtrim(SITE_URL, '/');
$perFile = 40000;
$page = max(1, (int) ($_GET['p'] ?? 1));
$onlyCat = preg_replace('/[^a-z0-9-]/', '', (string) ($_GET['cat'] ?? ''));   // ?cat=weight → one unit category
$all = gen_sitemap_convert_urls();
if ($onlyCat !== '') $all = array_values(array_filter($all, fn($u) => (gen_parse_slug($u[0])['cat'] ?? '') === $onlyCat));
$urls = array_slice($all, ($page - 1) * $perFile, $perFile);
$lastmod = gen_convert_lastmod();
$prefix = '/convert/';

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as [$slug, $prio]) {
    echo '  <url><loc>' . htmlspecialchars($base . $prefix . $slug, ENT_XML1) . '</loc><lastmod>' . $lastmod
       . '</lastmod><changefreq>monthly</changefreq><priority>' . $prio . "</priority></url>\n";
}
echo '</urlset>' . "\n";
