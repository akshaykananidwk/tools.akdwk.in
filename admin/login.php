<?php
/**
 * KRISHNA TOOLS — admin login. Standalone (no require_admin).
 * Authenticates with attempt_login() then requires an admin role.
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/ratelimit.php';

// Already an admin? Straight to the dashboard.
if (is_admin()) redirect(SITE_URL . '/admin/index.php');

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_login_csrf();
    if (!rate_limit('admin_login', 6, 300)) {
        $err = 'ઘણા પ્રયત્નો થયા. કૃપા કરી થોડી વાર પછી પ્રયત્ન કરો.';
    } else {
        $user = attempt_login($_POST['email'] ?? '', $_POST['password'] ?? '');
        if ($user && is_admin()) {
            redirect(SITE_URL . '/admin/index.php');
        }
        // Not an admin (or bad credentials): don't leave a half-session behind.
        if ($user && !is_admin()) {
            logout_user();
        }
        $err = 'ઈમેલ કે પાસવર્ડ ખોટો છે, અથવા આ એકાઉન્ટ પાસે એડમિન અધિકાર નથી.';
    }
}

function admin_login_csrf(): void {
    kt_session_start();
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419);
        exit('CSRF');
    }
}
$siteName = setting('site_name', 'કૃષ્ણા ટૂલ્સ');
?><!DOCTYPE html>
<html lang="gu" data-theme="light" class="gu">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>એડમિન લોગિન — <?= e($siteName) ?></title>
<meta name="robots" content="noindex,nofollow">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Baloo+Bhai+2:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/krishna.css">
<link rel="icon" href="<?= SITE_URL ?>/assets/img/logo/favicon.svg" type="image/svg+xml">
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js" defer></script>
</head>
<body class="morpankh-bg">
<div class="min-h-screen flex items-center justify-center px-4">
  <div class="w-full max-w-md">
    <div class="kt-card p-7">
      <div class="text-center mb-6">
        <div class="text-4xl">🦚</div>
        <h1 class="brand-title text-2xl mt-2"><?= e($siteName) ?></h1>
        <p class="text-sm opacity-70 mt-1">એડમિન પેનલ લોગિન</p>
      </div>
      <?php if ($err): ?>
        <div class="p-3 rounded-lg mb-4 text-sm flex items-center gap-2" style="background:rgba(220,38,38,.1);color:#DC2626">
          <i data-lucide="alert-triangle" class="w-4 h-4"></i><span><?= e($err) ?></span>
        </div>
      <?php endif; ?>
      <form method="post" class="space-y-4">
        <?= csrf_field() ?>
        <div>
          <label class="kt-label">ઈમેલ</label>
          <input type="email" name="email" class="kt-input" required autofocus autocomplete="username">
        </div>
        <div>
          <label class="kt-label">પાસવર્ડ</label>
          <input type="password" name="password" class="kt-input" required autocomplete="current-password">
        </div>
        <button class="btn btn-primary w-full justify-center">
          <i data-lucide="shield-check" class="w-4 h-4"></i> લોગિન કરો
        </button>
      </form>
      <div class="text-center mt-5 text-sm">
        <a href="<?= SITE_URL ?>/" class="text-[color:var(--peacock-teal)] hover:underline">← સાઇટ પર પાછા જાઓ</a>
      </div>
    </div>
  </div>
</div>
<script>window.addEventListener('load',function(){if(window.lucide)lucide.createIcons();});</script>
</body>
</html>
