<?php
/**
 * KRISHNA TOOLS — converter sitemap (paginated ?p=N, 40,000 URLs per file).
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
$urls = array_slice(gen_sitemap_convert_urls(), ($page - 1) * $perFile, $perFile);
$lastmod = gen_convert_lastmod();

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as [$slug, $prio]) {
    echo '  <url><loc>' . htmlspecialchars($base . '/convert/' . $slug, ENT_XML1) . '</loc><lastmod>' . $lastmod
       . '</lastmod><changefreq>monthly</changefreq><priority>' . $prio . "</priority></url>\n";
}
echo '</urlset>' . "\n";
