<?php
/** KRISHNA TOOLS — login page. */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/ratelimit.php';

if (is_logged_in()) redirect(SITE_URL . '/dashboard.php');
$err = '';
$next = $_GET['next'] ?? ($_POST['next'] ?? '/dashboard.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_page();
    if (!rate_limit('login', 6, 300)) {
        $err = 'ઘણા પ્રયત્નો. થોડી વાર પછી પ્રયત્ન કરો.';
    } elseif (!honeypot_ok()) {
        $err = 'Spam detected.';
    } else {
        $user = attempt_login($_POST['email'] ?? '', $_POST['password'] ?? '');
        if ($user) redirect(str_starts_with($next, '/') ? SITE_URL . $next : SITE_URL . '/dashboard.php');
        $err = 'ઈમેલ કે પાસવર્ડ ખોટો છે.';
    }
}
function csrf_verify_page() { kt_session_start(); if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(419); exit('CSRF'); } }

$page_title = t('login') . ' — ' . setting('site_name', 'કૃષ્ણા ટૂલ્સ');
require __DIR__ . '/includes/header.php';
?>
<div class="max-w-md mx-auto mt-8">
  <div class="kt-card p-6">
    <div class="text-center mb-5"><div class="text-4xl">🦚</div>
      <h1 class="brand-title text-2xl mt-2"><?= t('login') ?></h1></div>
    <?php if ($err): ?><div class="p-3 rounded-lg mb-4 text-sm" style="background:rgba(220,38,38,.1);color:#DC2626"><?= e($err) ?></div><?php endif; ?>
    <form method="post" class="space-y-4">
      <?= csrf_field() ?>
      <input type="hidden" name="next" value="<?= e($next) ?>">
      <div><label class="kt-label"><?= t('email') ?></label><input type="email" name="email" class="kt-input" required></div>
      <div><label class="kt-label"><?= t('password') ?></label><input type="password" name="password" class="kt-input" required></div>
      <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off">
      <button class="btn btn-primary w-full justify-center"><?= t('login') ?></button>
    </form>
    <div class="flex justify-between mt-4 text-sm">
      <a href="<?= SITE_URL ?>/forgot.php" class="text-[color:var(--peacock-teal)] hover:underline"><?= t('forgot_password') ?></a>
      <a href="<?= SITE_URL ?>/register.php" class="text-[color:var(--peacock-teal)] hover:underline"><?= t('no_account') ?> <?= t('register') ?></a>
    </div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
