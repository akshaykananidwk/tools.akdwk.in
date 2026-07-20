<?php
/**
 * KRISHNA TOOLS — site settings editor (grouped) + logo upload.
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_admin();
require_once __DIR__ . '/layout.php';

// Text keys we persist verbatim.
$textKeys = [
    'site_name', 'site_url', 'shop_name', 'shop_address', 'shop_phone', 'shop_whatsapp',
    'gstin', 'wa_base_url', 'wa_api_key', 'wa_session_id', 'wa_sender', 'wa_inbound_url',
    'rzp_webhook_secret', 'ga_id',
];
$boolKeys = ['wa_ivr_enabled', 'maintenance_mode'];

$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_check();
    try {
        if (($_POST['form'] ?? '') === 'logo') {
            $flash = admin_save_logo();
        } else {
            foreach ($textKeys as $k) {
                if (array_key_exists($k, $_POST)) set_setting($k, trim((string) $_POST[$k]));
            }
            foreach ($boolKeys as $k) {
                set_setting($k, isset($_POST[$k]) ? '1' : '0');
            }
            $lang = ($_POST['default_lang'] ?? 'gu') === 'en' ? 'en' : 'gu';
            set_setting('default_lang', $lang);
            activity_log((int) current_user()['id'], 'admin_settings_update', '');
            $flash = 'સેટિંગ્સ સેવ થઈ.';
        }
    } catch (Throwable $e) {
        kt_error_log('admin settings: ' . $e->getMessage());
        $flash = 'સેવ કરવામાં ભૂલ આવી.';
    }
    // Refresh the cached settings for the render below by re-reading via a fresh request would be ideal;
    // settings() is statically cached, so we just reflect POSTed values in $_POST for the form.
}

/** Validate + store an uploaded logo as assets/img/logo/logo.png. */
function admin_save_logo(): string {
    if (empty($_FILES['logo']['tmp_name']) || !is_uploaded_file($_FILES['logo']['tmp_name'])) {
        return 'કોઈ ફાઇલ પસંદ થઈ નથી.';
    }
    if (($_FILES['logo']['error'] ?? 1) !== UPLOAD_ERR_OK) {
        return 'અપલોડમાં ભૂલ આવી.';
    }
    if (($_FILES['logo']['size'] ?? 0) > 3 * 1024 * 1024) {
        return 'ફાઇલ 3 MB થી નાની હોવી જોઈએ.';
    }
    $tmp = $_FILES['logo']['tmp_name'];
    $allowed = ['image/png', 'image/jpeg', 'image/webp', 'image/gif'];
    $mime = '';
    if (function_exists('finfo_open')) {
        $fi = finfo_open(FILEINFO_MIME_TYPE);
        $mime = (string) finfo_file($fi, $tmp);
        finfo_close($fi);
    }
    // Double-check it is a real raster image.
    $info = @getimagesize($tmp);
    if (!in_array($mime, $allowed, true) || $info === false) {
        return 'માન્ય ઇમેજ ફાઇલ નથી (PNG/JPG/WEBP/GIF).';
    }
    $dir = (defined('KT_ROOT') ? KT_ROOT : dirname(__DIR__)) . '/assets/img/logo';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    $dest = $dir . '/logo.png';
    if (!@move_uploaded_file($tmp, $dest)) {
        return 'લોગો સેવ કરવામાં નિષ્ફળ (પરમિશન તપાસો).';
    }
    @chmod($dest, 0644);
    activity_log((int) current_user()['id'], 'admin_logo_upload', '');
    return 'લોગો અપલોડ થયો.';
}

/** Current value (POSTed value wins so the form is sticky after save). */
function sv(string $key, string $default = ''): string {
    if (isset($_POST[$key]) && ($_POST['form'] ?? '') !== 'logo') return (string) $_POST[$key];
    return (string) setting($key, $default);
}
function sb(string $key): bool {
    if (($_POST['form'] ?? '') !== 'logo' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        return isset($_POST[$key]);
    }
    return setting($key, '0') === '1';
}

admin_head('સેટિંગ્સ');
?>
<?php if ($flash): ?>
  <div class="kt-card p-3 mb-4 text-sm" style="border-color:var(--gold)"><?= e($flash) ?></div>
<?php endif; ?>

<form method="post" class="space-y-5">
  <?= csrf_field() ?>

  <div class="kt-card p-5">
    <h2 class="section-title text-base mb-3">સાઇટ</h2>
    <div class="grid sm:grid-cols-2 gap-3">
      <div><label class="kt-label text-xs">સાઇટનું નામ</label><input name="site_name" class="kt-input !py-1.5" value="<?= e(sv('site_name', 'કૃષ્ણા ટૂલ્સ')) ?>"></div>
      <div><label class="kt-label text-xs">સાઇટ URL</label><input name="site_url" class="kt-input !py-1.5" value="<?= e(sv('site_url', defined('SITE_URL') ? SITE_URL : '')) ?>"></div>
      <div><label class="kt-label text-xs">Google Analytics ID</label><input name="ga_id" class="kt-input !py-1.5" value="<?= e(sv('ga_id')) ?>" placeholder="G-XXXXXXX"></div>
      <div>
        <label class="kt-label text-xs">ડિફોલ્ટ ભાષા</label>
        <select name="default_lang" class="kt-select !py-1.5">
          <?php $dl = sv('default_lang', 'gu'); ?>
          <option value="gu" <?= $dl === 'gu' ? 'selected' : '' ?>>ગુજરાતી</option>
          <option value="en" <?= $dl === 'en' ? 'selected' : '' ?>>English</option>
        </select>
      </div>
    </div>
  </div>

  <div class="kt-card p-5">
    <h2 class="section-title text-base mb-3">શોપ</h2>
    <div class="grid sm:grid-cols-2 gap-3">
      <div><label class="kt-label text-xs">શોપનું નામ</label><input name="shop_name" class="kt-input !py-1.5" value="<?= e(sv('shop_name')) ?>"></div>
      <div><label class="kt-label text-xs">GSTIN</label><input name="gstin" class="kt-input !py-1.5" value="<?= e(sv('gstin')) ?>"></div>
      <div class="sm:col-span-2"><label class="kt-label text-xs">સરનામું</label><input name="shop_address" class="kt-input !py-1.5" value="<?= e(sv('shop_address')) ?>"></div>
      <div><label class="kt-label text-xs">ફોન</label><input name="shop_phone" class="kt-input !py-1.5" value="<?= e(sv('shop_phone')) ?>"></div>
      <div><label class="kt-label text-xs">વ્હોટ્સએપ નંબર</label><input name="shop_whatsapp" class="kt-input !py-1.5" value="<?= e(sv('shop_whatsapp')) ?>"></div>
    </div>
  </div>

  <div class="kt-card p-5">
    <h2 class="section-title text-base mb-3">વ્હોટ્સએપ API</h2>
    <div class="grid sm:grid-cols-2 gap-3">
      <div><label class="kt-label text-xs">Base URL</label><input name="wa_base_url" class="kt-input !py-1.5" value="<?= e(sv('wa_base_url', 'https://bulk.akdwk.in/api.php')) ?>"></div>
      <div><label class="kt-label text-xs">API Key</label><input name="wa_api_key" class="kt-input !py-1.5" value="<?= e(sv('wa_api_key')) ?>"></div>
      <div><label class="kt-label text-xs">Session ID</label><input name="wa_session_id" class="kt-input !py-1.5" value="<?= e(sv('wa_session_id')) ?>"></div>
      <div><label class="kt-label text-xs">Sender</label><input name="wa_sender" class="kt-input !py-1.5" value="<?= e(sv('wa_sender')) ?>"></div>
      <div><label class="kt-label text-xs">Inbound URL</label><input name="wa_inbound_url" class="kt-input !py-1.5" value="<?= e(sv('wa_inbound_url')) ?>"></div>
      <label class="flex items-center gap-2 text-sm mt-6"><input type="checkbox" name="wa_ivr_enabled" <?= sb('wa_ivr_enabled') ? 'checked' : '' ?>> IVR સક્ષમ કરો</label>
    </div>
  </div>

  <div class="kt-card p-5">
    <h2 class="section-title text-base mb-3">પેમેન્ટ</h2>
    <div class="grid sm:grid-cols-2 gap-3">
      <div><label class="kt-label text-xs">Razorpay Webhook Secret</label><input name="rzp_webhook_secret" class="kt-input !py-1.5" value="<?= e(sv('rzp_webhook_secret')) ?>"></div>
    </div>
  </div>

  <div class="kt-card p-5">
    <h2 class="section-title text-base mb-3">મેન્ટેનન્સ</h2>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="maintenance_mode" <?= sb('maintenance_mode') ? 'checked' : '' ?>> મેન્ટેનન્સ મોડ ચાલુ કરો (સાઇટ બંધ)</label>
  </div>

  <button class="btn btn-primary"><i data-lucide="save" class="w-4 h-4"></i> બધું સેવ કરો</button>
</form>

<div class="kt-card p-5 mt-5">
  <h2 class="section-title text-base mb-3">લોગો</h2>
  <div class="flex items-center gap-4 mb-3">
    <img src="<?= SITE_URL ?>/assets/img/logo/logo.png?v=<?= time() ?>" alt="Logo" class="w-16 h-16 rounded-xl border" style="border-color:var(--border);object-fit:contain" onerror="this.style.opacity=.2">
    <p class="text-xs opacity-70">PNG/JPG/WEBP/GIF, મહત્તમ 3 MB. <code>assets/img/logo/logo.png</code> તરીકે સેવ થશે.</p>
  </div>
  <form method="post" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2">
    <?= csrf_field() ?>
    <input type="hidden" name="form" value="logo">
    <input type="file" name="logo" accept="image/*" class="kt-input !py-1.5 max-w-xs" required>
    <button class="btn btn-gold"><i data-lucide="upload" class="w-4 h-4"></i> અપલોડ</button>
  </form>
</div>
<?php admin_foot(); ?>
