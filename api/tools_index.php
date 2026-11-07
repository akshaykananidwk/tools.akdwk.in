<?php
/** KRISHNA TOOLS — JSON tool index for global search (cached, public). */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/seo.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=3600');

$disabled = [];
try { foreach (all("SELECT slug FROM " . tbl('tools') . " WHERE is_active = 0") as $r) $disabled[$r['slug']] = true; } catch (Throwable $e) {}

$out = [];
foreach (kt_tools() as $t) {
    if (isset($disabled[$t['slug']])) continue;
    // 'k' = extra search phrases, so typing "voice typing" or "vyaj" finds the right tool.
    $out[] = ['slug' => $t['slug'], 'name_gu' => $t['name_gu'], 'name_en' => $t['name_en'], 'icon' => $t['icon'], 'cat' => $t['cat'],
              'k' => implode(' ', array_slice(kt_tool_keywords($t, 8), 0, 8))];
}
echo json_encode($out, JSON_UNESCAPED_UNICODE);
