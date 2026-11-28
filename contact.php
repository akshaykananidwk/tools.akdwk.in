<?php
/** KRISHNA TOOLS — public contact form (honeypot + CSRF), notifies the shop. */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/ratelimit.php';
require_once __DIR__ . '/includes/whatsapp.php';

$err = '';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF (contract-defined inline check).
    kt_session_start();
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(419); exit('CSRF'); }

    if (!rate_limit('contact', 5, 600)) {
        $err = 'Too many messages. Try again later.';
    } elseif (!honeypot_ok()) {
        $err = 'Spam detected.';
    } else {
        $name    = trim($_POST['name'] ?? '');
        $email   = strtolower(trim($_POST['email'] ?? ''));
        $phone   = normalize_phone($_POST['phone'] ?? '');
        $message = trim($_POST['message'] ?? '');
        if ($name === '' || $message === '') {
            $err = 'Name and message are required.';
        } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $err = 'Invalid email.';
        } else {
            insert('contact_messages', [
                'name' => $name, 'email' => $email ?: null, 'phone' => $phone ?: null,
                'message' => mb_substr($message, 0, 4000), 'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            // Notify the shop over WhatsApp (best-effort — never break the form).
            try {
                $shopWa = normalize_phone(setting('shop_whatsapp', setting('wa_sender', '')));
                if ($shopWa !== '') {
                    $body = "📩 New contact message\nName: {$name}\nPhone: " . ($phone ?: '-') . "\nEmail: " . ($email ?: '-') . "\n\n{$message}";
                    wa()->send($shopWa, $body);
                }
            } catch (Throwable $e) { /* ignore */ }
            // AK Earn: enquiry submitted (only with a mobile number, sent after the response).
            try { if (ak_mobile10($phone) !== '') ak_later('ak_track_lead', $name, ak_mobile10($phone), null, mb_substr($message, 0, 300)); } catch (Throwable $e) {}
            $msg = 'Thank you! Your message has been received. We will reply soon. Hare Krishna 🦚';
        }
    }
}

$shopAddress = kt_en_text(setting('shop_address', 'Dwarka, Gujarat'), 'Dwarka, Gujarat');
$shopPhone   = setting('shop_phone', '9978123146');
$shopWaLink  = normalize_phone(setting('shop_whatsapp', setting('wa_sender', '919978123146')));

$page_title = t('contact') . ' — ' . brand_name();
$page_desc  = 'Contact Krishna Tools and AK Computer, Dwarka — questions about a tool, a wrong result, privacy requests, CCTV or computer service. WhatsApp or the form.';
$breadcrumb = [
    ['label' => t('home'), 'url' => SITE_URL . '/'],
    ['label' => t('contact')],
];
require __DIR__ . '/includes/header.php';
?>
<div class="max-w-4xl mx-auto my-6">
  <h1 class="section-title text-2xl mb-1">Contact us</h1>
  <div class="divider-gold mb-3"></div>
  <p class="leading-relaxed mb-5">Found a wrong result, need a tool that is missing, or want help with CCTV, computers or networking in Dwarka? Send a message here or on WhatsApp — we usually reply within one working day. For privacy requests (to see or delete your data), mention "privacy" in your message. See also our <a class="underline" href="<?= SITE_URL ?>/about.php">About</a>, <a class="underline" href="<?= SITE_URL ?>/privacy.php">Privacy policy</a>, <a class="underline" href="<?= SITE_URL ?>/terms.php">Terms</a> and <a class="underline" href="<?= SITE_URL ?>/disclaimer.php">Disclaimer</a>.</p>

  <div class="grid md:grid-cols-2 gap-6">
    <!-- Form -->
    <div class="kt-card p-5">
      <?php if ($msg): ?>
        <div class="p-3 rounded-lg mb-4 text-sm" style="background:rgba(5,150,105,.1);color:#059669"><?= e($msg) ?></div>
      <?php endif; ?>
      <?php if ($err): ?>
        <div class="p-3 rounded-lg mb-4 text-sm" style="background:rgba(220,38,38,.1);color:#DC2626"><?= e($err) ?></div>
      <?php endif; ?>
      <form method="post" class="space-y-4" data-ak-form>
        <?= csrf_field() ?>
        <div><label class="kt-label"><?= t('full_name') ?></label><input name="name" class="kt-input" required></div>
        <div><label class="kt-label"><?= t('email') ?> <span class="opacity-50">(optional)</span></label><input type="email" name="email" class="kt-input"></div>
        <div><label class="kt-label"><?= t('phone') ?></label><input name="phone" class="kt-input" placeholder="9978123146"></div>
        <div><label class="kt-label"><?= 'Message' ?></label><textarea name="message" class="kt-input" rows="4" required></textarea></div>
        <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off">
        <button class="btn btn-primary w-full justify-center"><i data-lucide="send" class="w-4 h-4"></i> <?= 'Send Message' ?></button>
      </form>
    </div>

    <!-- Shop details + map -->
    <div class="space-y-4">
      <div class="kt-card p-5">
        <div class="font-bold text-lg mb-2"><?= e(setting('shop_name', 'AK Computer')) ?></div>
        <p class="text-sm opacity-70 flex items-start gap-2"><i data-lucide="map-pin" class="w-4 h-4 mt-0.5 shrink-0"></i> <?= e($shopAddress) ?></p>
        <p class="text-sm opacity-70 flex items-center gap-2 mt-2"><i data-lucide="phone" class="w-4 h-4"></i> <a href="tel:+91<?= e(substr(preg_replace('/\D/', '', $shopPhone), -10)) ?>"><?= e($shopPhone) ?></a></p>
        <?php if ($e = setting('shop_email', '')): ?><p class="text-sm opacity-70 flex items-center gap-2 mt-2"><i data-lucide="mail" class="w-4 h-4"></i> <a href="mailto:<?= e($e) ?>"><?= e($e) ?></a></p><?php endif; ?>
        <?php if ($h = setting('shop_hours', '')): ?><p class="text-sm opacity-70 flex items-center gap-2 mt-2"><i data-lucide="clock" class="w-4 h-4"></i> <?= e($h) ?></p><?php endif; ?>
        <a href="https://wa.me/<?= e($shopWaLink) ?>" target="_blank" rel="noopener" class="btn btn-wa mt-4">
          <i data-lucide="message-circle" class="w-4 h-4"></i> <?= t('send_whatsapp') ?>
        </a>
      </div>
      <div class="kt-card p-2 overflow-hidden">
        <iframe
          title="AK Computer, Dwarka on Google Maps"
          class="w-full rounded-lg"
          height="260"
          style="border:0"
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade"
          src="https://maps.google.com/maps?q=<?= urlencode($shopAddress) ?>&output=embed"></iframe>
      </div>
    </div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
