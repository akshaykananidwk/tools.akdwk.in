<?php
/**
 * KRISHNA TOOLS — repair / re-seed tool.
 * Re-seeds tools, categories, plans, WhatsApp templates and blog posts from
 * the registries WITHOUT touching users, payments, subscriptions or settings.
 * Requires an existing /config/config.php. Admin login gate.
 */
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once __DIR__ . '/lib.php';

require_admin();
$prefix = DB_PREFIX;
$pdo = db();
$done = [];
$run = ($_SERVER['REQUEST_METHOD'] === 'POST');
if ($run) {
    csrf_verify_or_redirect();
    $c = install_seed_tools($pdo, $prefix);
    $done[] = "Updated categories: {$c['categories']}, tools: {$c['tools']}";
    $done[] = 'Plans: ' . install_seed_plans($pdo, $prefix);
    $done[] = 'WhatsApp templates: ' . install_seed_templates($pdo, $prefix);
    $done[] = 'Blog posts: ' . install_seed_blog($pdo, $prefix);
    activity_log(current_user()['id'] ?? null, 'repair', implode('; ', $done));
}
function csrf_verify_or_redirect() {
    kt_session_start();
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(419); exit('CSRF'); }
}
?><!DOCTYPE html><html lang="gu"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Repair — Krishna Tools</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="../assets/css/krishna.css">
</head><body class="morpankh-bg p-6">
<div class="max-w-lg mx-auto kt-card p-6">
  <h1 class="brand-title text-2xl mb-1">🔧 Repair / Re-seed</h1>
  <p class="opacity-70 text-sm mb-4">Re-seeds tools, categories, plans, templates and blog posts. Users and payments are kept safe.</p>
  <?php if ($done): ?>
    <div class="p-3 rounded-lg mb-4 text-sm" style="background:rgba(5,150,105,.1)">
      <?php foreach ($done as $d): ?><div>✅ <?= e($d) ?></div><?php endforeach; ?></div>
  <?php endif; ?>
  <form method="post">
    <?= csrf_field() ?>
    <button class="btn btn-cta">🦚 Run Re-seed</button>
    <a href="../admin/" class="btn btn-ghost">Admin</a>
  </form>
</div></body></html>
