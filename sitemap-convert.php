<?php
/**
 * KRISHNA TOOLS — generated converter URLs sitemap (paginated ?p=N).
 * Streams up to 40,000 converter URLs per page: all directional unit pairs
 * followed by the most-searched value-specific pages.
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/generated_tools.php';

header('Content-Type: application/xml; charset=utf-8');
$base = rtrim(SITE_URL, '/');
$perFile = 40000;
$page = max(1, (int) ($_GET['p'] ?? 1));
$start = ($page - 1) * $perFile;
$end = $start + $perFile;

// Build the ordered URL list lazily by index window to keep memory low.
$pairs = gen_all_pairs();
$values = gen_common_values();
$i = 0;

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

$emit = function (string $slug, string $priority) use ($base, &$i, $start, $end) {
    if ($i >= $start && $i < $end) {
        echo '  <url><loc>' . htmlspecialchars($base . '/convert/' . $slug, ENT_XML1) . '</loc>'
           . '<changefreq>monthly</changefreq><priority>' . $priority . "</priority></url>\n";
    }
    $i++;
};

// 1) Pair pages (higher priority — the primary landing pages).
foreach ($pairs as $pr) {
    if ($i >= $end) break;
    $emit($pr['from'] . '-to-' . $pr['to'], '0.6');
}
// 2) Value-specific pages.
if ($i < $end) {
    foreach ($pairs as $pr) {
        if ($i >= $end) break;
        foreach ($values as $v) {
            $emit($v . '-' . $pr['from'] . '-to-' . $pr['to'], '0.4');
        }
    }
}

echo '</urlset>' . "\n";
