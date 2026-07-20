<?php
/** KRISHNA TOOLS — password reset via WhatsApp OTP (two steps, one page). */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/ratelimit.php';
require_once __DIR__ . '/includes/whatsapp.php';

if (is_logged_in()) redirect(SITE_URL . '/dashboard.php');

kt_session_start();
$en  = current_lang() === 'en';
$err = '';
$msg = '';
$step = 1;                       // 1 = enter email, 2 = enter OTP + new password
$sent = false;                   // whether an OTP was actually dispatched to WhatsApp

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF (contract-defined inline check).
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(419); exit('CSRF'); }

    $action = $_POST['action'] ?? '';

    if ($action === 'request') {
        // Step 1 — look up the user and generate an OTP.
        if (!rate_limit('forgot', 4, 600)) {
            $err = $en ? 'Too many attempts. Try again later.' : 'ઘણા પ્રયત્નો. થોડી વાર પછી પ્રયત્ન કરો.';
        } elseif (!honeypot_ok()) {
            $err = 'Spam detected.';
        } else {
            $email = strtolower(trim($_POST['email'] ?? ''));
            $user = one("SELECT * FROM " . tbl('users') . " WHERE email = :e", [':e' => $email]);
            if ($user) {
                $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $_SESSION['pwreset'] = [
                    'uid'     => (int) $user['id'],
                    'otp'     => $otp,
                    'expires' => time() + 300,   // 5-minute validity
                ];
                // Send OTP over WhatsApp if a phone is on file (best-effort).
                if (!empty($user['phone'])) {
                    try {
                        $res = wa()->sendTemplate($user['phone'], 'reset_otp', ['otp' => $otp], (int) $user['id']);
                        $sent = !empty($res['ok']);
                    } catch (Throwable $e) { $sent = false; }
                }
                activity_log((int) $user['id'], 'password_reset_request', $sent ? 'otp_sent' : 'otp_no_phone');
            }
            // Always advance to step 2 and show a neutral message (no user enumeration).
            $step = 2;
            $msg = $sent
                ? ($en ? 'An OTP has been sent to your WhatsApp number.' : 'તમારા WhatsApp નંબર પર OTP મોકલ્યો છે.')
                : ($en ? 'If the account exists, an OTP has been issued. Check WhatsApp.' : 'એકાઉન્ટ હોય તો OTP જારી થયો છે. WhatsApp તપાસો.');
        }
    } elseif ($action === 'verify') {
        // Step 2 — verify OTP and set a new password.
        $step = 2;
        $otpIn = trim($_POST['otp'] ?? '');
        $new   = $_POST['new_password'] ?? '';
        $conf  = $_POST['confirm_password'] ?? '';
        $data  = $_SESSION['pwreset'] ?? null;

        if (!$data || time() > ($data['expires'] ?? 0)) {
            $err = $en ? 'OTP expired. Please request a new one.' : 'OTP સમય પૂરો. ફરી નવો OTP માંગો.';
            $step = 1;
            unset($_SESSION['pwreset']);
        } elseif (!hash_equals((string) $data['otp'], $otpIn)) {
            $err = $en ? 'Incorrect OTP.' : 'OTP ખોટો છે.';
        } elseif (strlen($new) < 8) {
            $err = $en ? 'Password must be at least 8 characters.' : 'પાસવર્ડ ઓછામાં ઓછો 8 અક્ષર.';
        } elseif ($new !== $conf) {
            $err = $en ? 'Passwords do not match.' : 'પાસવર્ડ મેળ ખાતા નથી.';
        } else {
            update('users', ['password_hash' => password_hash($new, PASSWORD_DEFAULT)], ['id' => $data['uid']]);
            activity_log((int) $data['uid'], 'password_reset', 'via otp');
            unset($_SESSION['pwreset']);
            $msg = $en ? 'Password reset. You can now log in.' : 'પાસવર્ડ બદલાયો. હવે લોગિન કરો.';
            $step = 3;   // done
        }
    }
}

$page_title = t('forgot_password') . ' — ' . setting('site_name', 'કૃષ્ણા ટૂલ્સ');
require __DIR__ . '/includes/header.php';
?>
<div class="max-w-md mx-auto mt-8">
  <div class="kt-card p-6">
    <div class="text-center mb-5"><div class="text-4xl">🔑</div>
      <h1 class="brand-title text-2xl mt-2"><?= t('forgot_password') ?></h1></div>

    <?php if ($msg): ?><div class="p-3 rounded-lg mb-4 text-sm" style="background:rgba(5,150,105,.1);color:#059669"><?= e($msg) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="p-3 rounded-lg mb-4 text-sm" style="background:rgba(220,38,38,.1);color:#DC2626"><?= e($err) ?></div><?php endif; ?>

    <?php if ($step === 3): ?>
      <a href="<?= SITE_URL ?>/login.php" class="btn btn-primary w-full justify-center"><?= t('login') ?></a>

    <?php elseif ($step === 2): ?>
      <form method="post" class="space-y-4">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="verify">
        <p class="text-sm opacity-70"><?= $en ? 'Enter the 6-digit OTP and choose a new password.' : '6-આંકડાનો OTP અને નવો પાસવર્ડ દાખલ કરો.' ?></p>
        <div><label class="kt-label">OTP</label><input name="otp" class="kt-input font-mono tracking-widest text-center" inputmode="numeric" maxlength="6" required></div>
        <div><label class="kt-label"><?= $en ? 'New password' : 'નવો પાસવર્ડ' ?></label><input type="password" name="new_password" class="kt-input" minlength="8" required></div>
        <div><label class="kt-label"><?= t('confirm_password') ?></label><input type="password" name="confirm_password" class="kt-input" minlength="8" required></div>
        <button class="btn btn-primary w-full justify-center"><?= $en ? 'Reset Password' : 'પાસવર્ડ બદલો' ?></button>
      </form>
      <div class="text-center mt-4 text-sm">
        <a href="<?= SITE_URL ?>/forgot.php" class="text-[color:var(--peacock-teal)] hover:underline"><?= $en ? 'Start over' : 'ફરી શરૂ કરો' ?></a>
      </div>

    <?php else: ?>
      <form method="post" class="space-y-4">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="request">
        <p class="text-sm opacity-70"><?= $en ? 'Enter your account email. We will send an OTP to your registered WhatsApp number.' : 'તમારું ઈમેલ દાખલ કરો. અમે તમારા WhatsApp નંબર પર OTP મોકલીશું.' ?></p>
        <div><label class="kt-label"><?= t('email') ?></label><input type="email" name="email" class="kt-input" required></div>
        <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off">
        <button class="btn btn-primary w-full justify-center"><?= $en ? 'Send OTP' : 'OTP મોકલો' ?></button>
      </form>
      <div class="text-center mt-4 text-sm">
        <a href="<?= SITE_URL ?>/login.php" class="text-[color:var(--peacock-teal)] hover:underline"><?= t('login') ?></a>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
