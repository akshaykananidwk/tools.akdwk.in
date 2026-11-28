<?php
/**
 * KRISHNA TOOLS — converter index for the search box (cached, public).
 *   p: [[slug, "Quintal to Kilogram", "qtl to kg"], …]  every unit pair
 *   u: {"qtl": "quintal", "kg": "kilogram", "hours": "hour", …}  words people type → unit key
 * The browser uses it to suggest converters and to turn "30 qtl to kg" into a direct link.
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/convert_content.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=86400');

$pairs = [];
foreach (gen_all_pairs() as $pr) {
    $fl = gen_unit_label($pr['cat'], $pr['from']); $tl = gen_unit_label($pr['cat'], $pr['to']);
    $pairs[] = [$pr['from'] . '-to-' . $pr['to'], $fl['name'] . ' to ' . $tl['name'], gen_short($pr['from'], $pr['cat']) . ' to ' . gen_short($pr['to'], $pr['cat'])];
}
$units = [];
foreach (gen_unit_categories() as $cat => $c) {
    foreach ($c['units'] as $key => $u) {
        foreach ([$key, str_replace('-', ' ', $key), strtolower($u[1]), gen_plural($u[1]), strtolower($u[2]), strtolower(gen_short($key, $cat))] as $tok) {
            $tok = trim(strtolower($tok));
            if ($tok !== '' && !isset($units[$tok])) $units[$tok] = $key;
        }
    }
}
foreach (gen_aliases() as $a => $k) $units[str_replace('-', ' ', $a)] = $k;
echo json_encode(['p' => $pairs, 'u' => $units], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
