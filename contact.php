<?php
/** KRISHNA TOOLS — public contact form (honeypot + CSRF), notifies the shop. */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/ratelimit.php';
require_once __DIR__ . '/includes/whatsapp.php';

$en  = current_lang() === 'en';
$err = '';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF (contract-defined inline check).
    kt_session_start();
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(419); exit('CSRF'); }

    if (!rate_limit('contact', 5, 600)) {
        $err = $en ? 'Too many messages. Try again later.' : 'ઘણા સંદેશા. થોડી વાર પછી પ્રયત્ન કરો.';
    } elseif (!honeypot_ok()) {
        $err = 'Spam detected.';
    } else {
        $name    = trim($_POST['name'] ?? '');
        $email   = strtolower(trim($_POST['email'] ?? ''));
        $phone   = normalize_phone($_POST['phone'] ?? '');
        $message = trim($_POST['message'] ?? '');
        if ($name === '' || $message === '') {
            $err = $en ? 'Name and message are required.' : 'નામ અને સંદેશ જરૂરી છે.';
        } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $err = $en ? 'Invalid email.' : 'ઈમેલ અમાન્ય છે.';
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
            $msg = $en
                ? 'Thank you! Your message has been received. We will reply soon. Hare Krishna 🦚'
                : 'આભાર! તમારો સંદેશ મળ્યો છે. અમે જલદી જવાબ આપીશું. હરે કૃષ્ણ 🦚';
        }
    }
}

$shopAddress = setting('shop_address', 'Dwarka, Gujarat');
$shopPhone   = setting('shop_phone', '9978123146');
$shopWaLink  = normalize_phone(setting('shop_whatsapp', setting('wa_sender', '919978123146')));

$page_title = t('contact') . ' — ' . setting('site_name', 'કૃષ્ણા ટૂલ્સ');
$page_desc  = $en ? 'Contact AK Computer, Dwarka — computer & CCTV shop.' : 'AK Computer, દ્વારકા સાથે સંપર્ક — કમ્પ્યુટર અને CCTV શોપ.';
$breadcrumb = [
    ['label' => t('home'), 'url' => SITE_URL . '/'],
    ['label' => t('contact')],
];
require __DIR__ . '/includes/header.php';
?>
<div class="max-w-4xl mx-auto my-6">
  <h1 class="section-title text-2xl mb-1"><?= t('contact') ?></h1>
  <div class="divider-gold mb-5"></div>

  <div class="grid md:grid-cols-2 gap-6">
    <!-- Form -->
    <div class="kt-card p-5">
      <?php if ($msg): ?>
        <div class="p-3 rounded-lg mb-4 text-sm" style="background:rgba(5,150,105,.1);color:#059669"><?= e($msg) ?></div>
      <?php endif; ?>
      <?php if ($err): ?>
        <div class="p-3 rounded-lg mb-4 text-sm" style="background:rgba(220,38,38,.1);color:#DC2626"><?= e($err) ?></div>
      <?php endif; ?>
      <form method="post" class="space-y-4">
        <?= csrf_field() ?>
        <div><label class="kt-label"><?= t('full_name') ?></label><input name="name" class="kt-input" required></div>
        <div><label class="kt-label"><?= t('email') ?> <span class="opacity-50">(optional)</span></label><input type="email" name="email" class="kt-input"></div>
        <div><label class="kt-label"><?= t('phone') ?></label><input name="phone" class="kt-input" placeholder="9978123146"></div>
        <div><label class="kt-label"><?= $en ? 'Message' : 'સંદેશ' ?></label><textarea name="message" class="kt-input" rows="4" required></textarea></div>
        <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off">
        <button class="btn btn-primary w-full justify-center"><i data-lucide="send" class="w-4 h-4"></i> <?= $en ? 'Send Message' : 'સંદેશ મોકલો' ?></button>
      </form>
    </div>

    <!-- Shop details + map -->
    <div class="space-y-4">
      <div class="kt-card p-5">
        <div class="font-bold text-lg mb-2"><?= e(setting('shop_name', 'AK Computer')) ?></div>
        <p class="text-sm opacity-70 flex items-start gap-2"><i data-lucide="map-pin" class="w-4 h-4 mt-0.5 shrink-0"></i> <?= e($shopAddress) ?></p>
        <p class="text-sm opacity-70 flex items-center gap-2 mt-2"><i data-lucide="phone" class="w-4 h-4"></i> <?= e($shopPhone) ?></p>
        <a href="https://wa.me/<?= e($shopWaLink) ?>" target="_blank" rel="noopener" class="btn btn-wa mt-4">
          <i data-lucide="message-circle" class="w-4 h-4"></i> <?= t('send_whatsapp') ?>
        </a>
      </div>
      <div class="kt-card p-2 overflow-hidden">
        <iframe
          title="Map"
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
