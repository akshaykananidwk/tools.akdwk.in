<?php
/** KRISHNA TOOLS — registration (email + password; welcome WhatsApp on signup). */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/ratelimit.php';
require_once __DIR__ . '/includes/whatsapp.php';

if (is_logged_in()) redirect(SITE_URL . '/dashboard.php');
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    kt_session_start();
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(419); exit('CSRF'); }
    if (!rate_limit('register', 5, 600)) { $err = 'ઘણા પ્રયત્નો. પછી પ્રયત્ન કરો.'; }
    elseif (!honeypot_ok()) { $err = 'Spam detected.'; }
    else {
        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $phone = normalize_phone($_POST['phone'] ?? '');
        $pass = $_POST['password'] ?? '';
        $ref = trim($_POST['referral'] ?? '');
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 8) {
            $err = 'બધા ફીલ્ડ ભરો (પાસવર્ડ ઓછામાં ઓછો 8 અક્ષર).';
        } elseif (one("SELECT id FROM " . tbl('users') . " WHERE email = :e", [':e' => $email])) {
            $err = 'આ ઈમેલ પહેલેથી નોંધાયેલ છે.';
        } else {
            $referrer = $ref ? one("SELECT id FROM " . tbl('users') . " WHERE referral_code = :r", [':r' => $ref]) : null;
            $free = plan_free();
            $uid = insert('users', [
                'name' => $name, 'email' => $email, 'phone' => $phone,
                'password_hash' => password_hash($pass, PASSWORD_DEFAULT),
                'role' => 'user', 'status' => 'active', 'email_verified' => 0,
                'plan_id' => $free['id'] ?: null,
                'referral_code' => strtoupper(substr(md5($email . time()), 0, 8)),
                'referred_by' => $referrer['id'] ?? null,
                'credits' => 0, 'created_at' => date('Y-m-d H:i:s'),
            ]);
            // Referral credit (₹50).
            if ($referrer) {
                q("UPDATE " . tbl('users') . " SET credits = credits + 50 WHERE id = :id", [':id' => $referrer['id']]);
            }
            // Welcome WhatsApp (best-effort).
            if ($phone) { try { wa()->sendTemplate($phone, 'welcome', ['name' => $name], $uid); } catch (Throwable $e) {} }
            activity_log($uid, 'register', $email);
            login_user(one("SELECT * FROM " . tbl('users') . " WHERE id = :id", [':id' => $uid]));
            redirect(SITE_URL . '/dashboard.php');
        }
    }
}
$page_title = t('register') . ' — ' . setting('site_name', 'કૃષ્ણા ટૂલ્સ');
require __DIR__ . '/includes/header.php';
?>
<div class="max-w-md mx-auto mt-8">
  <div class="kt-card p-6">
    <div class="text-center mb-5"><div class="text-4xl">🦚</div>
      <h1 class="brand-title text-2xl mt-2"><?= t('register') ?></h1></div>
    <?php if ($err): ?><div class="p-3 rounded-lg mb-4 text-sm" style="background:rgba(220,38,38,.1);color:#DC2626"><?= e($err) ?></div><?php endif; ?>
    <form method="post" class="space-y-4">
      <?= csrf_field() ?>
      <div><label class="kt-label"><?= t('full_name') ?></label><input name="name" class="kt-input" required></div>
      <div><label class="kt-label"><?= t('email') ?></label><input type="email" name="email" class="kt-input" required></div>
      <div><label class="kt-label"><?= t('phone') ?> (WhatsApp)</label><input name="phone" class="kt-input" placeholder="9978123146"></div>
      <div><label class="kt-label"><?= t('password') ?></label><input type="password" name="password" class="kt-input" minlength="8" required></div>
      <div><label class="kt-label">Referral Code <span class="opacity-50">(optional)</span></label><input name="referral" class="kt-input"></div>
      <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off">
      <button class="btn btn-primary w-full justify-center"><?= t('register') ?></button>
    </form>
    <div class="text-center mt-4 text-sm">
      <a href="<?= SITE_URL ?>/login.php" class="text-[color:var(--peacock-teal)] hover:underline"><?= t('have_account') ?> <?= t('login') ?></a>
    </div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
