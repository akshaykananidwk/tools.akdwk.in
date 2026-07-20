<?php
/** KRISHNA TOOLS — dynamic XML sitemap. */
require_once __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/xml; charset=utf-8');

$base = rtrim(SITE_URL, '/');

/** Collect [loc, lastmod|null, changefreq, priority] rows. */
$urls = [];
$urls[] = [$base . '/', null, 'daily', '1.0'];
$urls[] = [$base . '/pricing.php', null, 'monthly', '0.8'];
$urls[] = [$base . '/blog/', null, 'weekly', '0.7'];
$urls[] = [$base . '/about.php', null, 'monthly', '0.4'];
$urls[] = [$base . '/contact.php', null, 'monthly', '0.4'];

// Categories.
foreach (kt_categories() as $c) {
    $urls[] = [$base . '/category/' . $c['slug'], null, 'weekly', '0.7'];
}

// Tools.
foreach (kt_tools() as $t) {
    $urls[] = [$base . '/tool/' . $t['slug'], null, 'weekly', '0.6'];
}

// Published blog posts (with last-modified from created_at).
try {
    foreach (all("SELECT slug, created_at FROM " . tbl('blog_posts') . " WHERE is_published = 1") as $p) {
        $lastmod = $p['created_at'] ? date('Y-m-d', strtotime($p['created_at'])) : null;
        $urls[] = [$base . '/blog/' . $p['slug'], $lastmod, 'monthly', '0.6'];
    }
} catch (Throwable $e) { /* DB not ready — skip blog URLs */ }

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as [$loc, $lastmod, $changefreq, $priority]) {
    echo "  <url>\n";
    echo '    <loc>' . htmlspecialchars($loc, ENT_XML1, 'UTF-8') . "</loc>\n";
    if ($lastmod) echo '    <lastmod>' . $lastmod . "</lastmod>\n";
    echo '    <changefreq>' . $changefreq . "</changefreq>\n";
    echo '    <priority>' . $priority . "</priority>\n";
    echo "  </url>\n";
}
echo '</urlset>' . "\n";
