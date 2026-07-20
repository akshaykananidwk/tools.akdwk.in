<?php
/** KRISHNA TOOLS — JSON tool index for global search (cached, public). */
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=3600');

$disabled = [];
try { foreach (all("SELECT slug FROM " . tbl('tools') . " WHERE is_active = 0") as $r) $disabled[$r['slug']] = true; } catch (Throwable $e) {}

$out = [];
foreach (kt_tools() as $t) {
    if (isset($disabled[$t['slug']])) continue;
    $out[] = ['slug' => $t['slug'], 'name_gu' => $t['name_gu'], 'name_en' => $t['name_en'], 'icon' => $t['icon'], 'cat' => $t['cat']];
}
echo json_encode($out, JSON_UNESCAPED_UNICODE);
