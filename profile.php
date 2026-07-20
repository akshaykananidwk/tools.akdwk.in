<?php
/** KRISHNA TOOLS — user profile: edit name/phone + change password. */
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$user = current_user();
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF (contract-defined inline check).
    kt_session_start();
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(419); exit('CSRF'); }

    $action = $_POST['action'] ?? '';

    if ($action === 'profile') {
        // Update display name + WhatsApp phone.
        $name  = trim($_POST['name'] ?? '');
        $phone = normalize_phone($_POST['phone'] ?? '');
        if ($name === '') {
            $err = current_lang() === 'en' ? 'Name is required.' : 'નામ જરૂરી છે.';
        } else {
            update('users', ['name' => $name, 'phone' => $phone], ['id' => $user['id']]);
            activity_log((int) $user['id'], 'profile_update', 'name/phone');
            $msg = current_lang() === 'en' ? 'Profile updated.' : 'પ્રોફાઇલ સેવ થઈ.';
            $user = one("SELECT * FROM " . tbl('users') . " WHERE id = :id", [':id' => $user['id']]);
        }
    } elseif ($action === 'password') {
        // Change password: verify current, min 8, confirm match.
        $cur  = $_POST['current_password'] ?? '';
        $new  = $_POST['new_password'] ?? '';
        $conf = $_POST['confirm_password'] ?? '';
        if (!password_verify($cur, $user['password_hash'])) {
            $err = current_lang() === 'en' ? 'Current password is incorrect.' : 'હાલનો પાસવર્ડ ખોટો છે.';
        } elseif (strlen($new) < 8) {
            $err = current_lang() === 'en' ? 'New password must be at least 8 characters.' : 'નવો પાસવર્ડ ઓછામાં ઓછો 8 અક્ષર.';
        } elseif ($new !== $conf) {
            $err = current_lang() === 'en' ? 'Passwords do not match.' : 'પાસવર્ડ મેળ ખાતા નથી.';
        } else {
            update('users', ['password_hash' => password_hash($new, PASSWORD_DEFAULT)], ['id' => $user['id']]);
            activity_log((int) $user['id'], 'password_change', '');
            $msg = current_lang() === 'en' ? 'Password changed.' : 'પાસવર્ડ બદલાયો.';
        }
    }
}

$en = current_lang() === 'en';
$page_title = t('profile') . ' — ' . setting('site_name', 'કૃષ્ણા ટૂલ્સ');
$breadcrumb = [
    ['label' => t('home'), 'url' => SITE_URL . '/'],
    ['label' => t('dashboard'), 'url' => SITE_URL . '/dashboard.php'],
    ['label' => t('profile')],
];
require __DIR__ . '/includes/header.php';
?>
<div class="max-w-2xl mx-auto my-6">
  <h1 class="section-title text-2xl mb-1"><?= t('profile') ?></h1>
  <div class="divider-gold mb-5"></div>

  <?php if ($msg): ?><div class="p-3 rounded-lg mb-4 text-sm" style="background:rgba(5,150,105,.1);color:#059669"><?= e($msg) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="p-3 rounded-lg mb-4 text-sm" style="background:rgba(220,38,38,.1);color:#DC2626"><?= e($err) ?></div><?php endif; ?>

  <!-- Basic details -->
  <div class="kt-card p-5 mb-5">
    <h2 class="font-bold mb-3"><?= $en ? 'Account Details' : 'એકાઉન્ટ વિગત' ?></h2>
    <form method="post" class="space-y-4">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="profile">
      <div>
        <label class="kt-label"><?= t('full_name') ?></label>
        <input name="name" class="kt-input" value="<?= e($user['name']) ?>" required>
      </div>
      <div>
        <label class="kt-label"><?= t('email') ?></label>
        <input class="kt-input" value="<?= e($user['email']) ?>" disabled>
        <div class="text-xs opacity-60 mt-1"><?= $en ? 'Email cannot be changed.' : 'ઈમેલ બદલી શકાતું નથી.' ?></div>
      </div>
      <div>
        <label class="kt-label"><?= t('phone') ?> (WhatsApp)</label>
        <input name="phone" class="kt-input" value="<?= e($user['phone']) ?>" placeholder="9978123146">
      </div>
      <button class="btn btn-primary"><i data-lucide="save" class="w-4 h-4"></i> <?= $en ? 'Save' : 'સેવ કરો' ?></button>
    </form>
  </div>

  <!-- Referral (read-only) -->
  <div class="kt-card p-5 mb-5">
    <h2 class="font-bold mb-3"><?= $en ? 'Referral' : 'રેફરલ' ?></h2>
    <div class="grid grid-cols-2 gap-4 text-sm">
      <div>
        <div class="opacity-60"><?= $en ? 'Your code' : 'તમારો કોડ' ?></div>
        <div class="font-mono font-bold text-lg"><?= e($user['referral_code']) ?></div>
      </div>
      <div>
        <div class="opacity-60"><?= $en ? 'Credits' : 'ક્રેડિટ' ?></div>
        <div class="font-bold text-lg" style="color:var(--peacock-teal)"><?= money((float) $user['credits']) ?></div>
      </div>
    </div>
    <p class="text-xs opacity-60 mt-3"><?= $en ? 'Share your code — you earn ₹50 credit per signup.' : 'તમારો કોડ શેર કરો — દરેક સાઇનઅપ પર ₹50 ક્રેડિટ.' ?></p>
  </div>

  <!-- Change password -->
  <div class="kt-card p-5">
    <h2 class="font-bold mb-3"><?= $en ? 'Change Password' : 'પાસવર્ડ બદલો' ?></h2>
    <form method="post" class="space-y-4">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="password">
      <div>
        <label class="kt-label"><?= $en ? 'Current password' : 'હાલનો પાસવર્ડ' ?></label>
        <input type="password" name="current_password" class="kt-input" required>
      </div>
      <div>
        <label class="kt-label"><?= $en ? 'New password' : 'નવો પાસવર્ડ' ?></label>
        <input type="password" name="new_password" class="kt-input" minlength="8" required>
      </div>
      <div>
        <label class="kt-label"><?= t('confirm_password') ?></label>
        <input type="password" name="confirm_password" class="kt-input" minlength="8" required>
      </div>
      <button class="btn btn-primary"><i data-lucide="key-round" class="w-4 h-4"></i> <?= $en ? 'Update Password' : 'પાસવર્ડ અપડેટ કરો' ?></button>
    </form>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
