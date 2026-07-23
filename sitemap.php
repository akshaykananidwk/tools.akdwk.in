<?php
/**
 * KRISHNA TOOLS — sitemap INDEX.
 * Points to the main sitemap (pages/tools/blog) and the paginated converter
 * sitemaps. Google allows 50,000 URLs per sitemap file, so the generated
 * converter pages are chunked.
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/generated_tools.php';

header('Content-Type: application/xml; charset=utf-8');
$base = rtrim(SITE_URL, '/');

$counts = gen_total_count();
$perFile = 40000;
$pages = max(1, (int) ceil($counts['total'] / $perFile));

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
echo "  <sitemap><loc>$base/sitemap-main.xml</loc></sitemap>\n";
for ($p = 1; $p <= $pages; $p++) {
    echo "  <sitemap><loc>$base/sitemap-convert.xml?p=$p</loc></sitemap>\n";
}
echo '</sitemapindex>' . "\n";
