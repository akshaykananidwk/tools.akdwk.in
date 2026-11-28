<?php
/**
 * KRISHNA TOOLS — embeddable tool (/embed/<slug>), shown inside an <iframe> on other websites.
 * Only the tool itself plus a "Krishna Tools" credit link; noindex, canonical = the tool page.
 * Framing is allowed for this URL only (see .htaccess and the CSP header below).
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/tool_wrapper.php';
require_once __DIR__ . '/includes/csrf.php';
kt_session_start();

$slug = preg_replace('/[^a-z0-9-]/', '', (string) ($_GET['slug'] ?? ''));
$tool = $slug !== '' ? kt_tool($slug) : null;
$db = $tool ? (tool_db_row($slug) ?? kt_ensure_tool_row($tool)) : null;
if (!$tool || ($db && (int) $db['is_active'] === 0)) { http_response_code(404); header('Content-Type: text/plain'); exit('Tool not found'); }
$tool['db_id'] = $db['id'] ?? 0;

header('Content-Security-Policy: frame-ancestors *');
header('X-Robots-Tag: noindex, follow');
$access = tool_access($tool);
$name = loc($tool, 'name');
$toolUrl = SITE_URL . '/tool/' . $slug;
$css = '';
foreach (['css/krishna.css', 'css/tailwind.css'] as $f) { $p = __DIR__ . '/assets/' . $f; if (is_file($p)) $css .= file_get_contents($p) . "\n"; }
[$mh, $md] = kt_tool_reserve($tool);
?><!DOCTYPE html>
<html lang="en-IN" data-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($name) ?> — Krishna Tools</title>
<meta name="robots" content="noindex, follow">
<link rel="canonical" href="<?= e($toolUrl) ?>">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/fonts/fonts.css?v=<?= asset_ver() ?>">
<style><?= $css ?>body{background:transparent}.kt-embed{max-width:900px;margin:0 auto;padding:12px}</style>
<script src="<?= SITE_URL ?>/assets/js/icons.js?v=<?= asset_ver() ?>" defer></script>
<script>window.KT = { url: <?= json_encode(SITE_URL) ?>, lang: 'en', csrf: <?= json_encode(csrf_token()) ?>, loggedIn: false, ver: <?= json_encode(asset_ver()) ?>, embed: true };</script>
</head>
<body>
<div class="kt-embed">
  <div class="kt-card p-4">
    <div class="flex items-center justify-between gap-2 mb-3">
      <h1 class="font-bold text-lg" style="color:var(--krishna-blue)"><?= e($name) ?></h1>
      <a href="<?= e($toolUrl) ?>?utm_source=embed&amp;utm_medium=iframe" target="_blank" rel="noopener" class="text-xs opacity-70 hover:underline shrink-0">Open full tool ↗</a>
    </div>
    <div id="toolMount" style="--mh:<?= $mh ?>px;--md:<?= $md ?>px"
         data-engine="<?= e($tool['engine']) ?>" data-src="<?= e(kt_engine_file($tool['engine'])) ?>"
         data-slug="<?= e($slug) ?>" data-cat="<?= e($tool['cat']) ?>" data-toolid="<?= (int) $tool['db_id'] ?>"
         data-premium="<?= (int) !empty($tool['is_premium']) ?>" data-allowed="<?= $access['allowed'] ? 1 : 0 ?>"
         data-watermark="<?= $access['watermark'] ? 1 : 0 ?>" data-maxmb="<?= (int) $access['max_mb'] ?>"
         data-opt='<?= e(json_encode(kt_strip_gu($tool['opt'] ?? []) ?: new stdClass, JSON_UNESCAPED_UNICODE)) ?>'>
      <div class="text-center py-8"><div class="chakra-loader mx-auto"></div></div>
    </div>
    <div id="toolActions" class="flex flex-wrap gap-2 mt-4"></div>
  </div>
  <p class="text-center text-xs mt-2 opacity-80">Free tool by <a href="<?= e($toolUrl) ?>?utm_source=embed&amp;utm_medium=credit" target="_blank" rel="noopener" class="underline font-semibold">Krishna Tools</a> · tools.akdwk.in</p>
</div>
<div id="ktToast" class="kt-toast"></div>
<script src="<?= SITE_URL ?>/assets/js/app.js?v=<?= asset_ver() ?>" defer></script>
<script>document.addEventListener('DOMContentLoaded',()=>{if(window.lucide)lucide.createIcons();});</script>
</body>
</html>
