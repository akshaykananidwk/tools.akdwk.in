<?php
/** KRISHNA TOOLS — pricing + Razorpay Checkout. */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/csrf.php';

$plans = all("SELECT * FROM " . tbl('plans') . " WHERE is_active = 1 ORDER BY price ASC");
$rzpEnabled = defined('RAZORPAY_KEY_ID') && RAZORPAY_KEY_ID !== '';
$page_title = t('pricing') . ' — ' . setting('site_name', 'કૃષ્ણા ટૂલ્સ');
$breadcrumb = [['label' => t('home'), 'url' => SITE_URL . '/'], ['label' => t('pricing')]];
require __DIR__ . '/includes/header.php';
?>
<div class="text-center my-8">
  <h1 class="section-title text-3xl"><?= t('pricing') ?></h1>
  <p class="opacity-70 mt-2"><?= current_lang() === 'en' ? 'Simple, honest pricing. Cancel anytime.' : 'સરળ અને પ્રામાણિક કિંમત. ગમે ત્યારે બંધ કરો.' ?></p>
</div>
<div class="grid md:grid-cols-3 gap-5 max-w-4xl mx-auto">
  <?php foreach ($plans as $p):
    $feats = array_filter(explode('|', $p['features'] ?? ''));
    $yearly = (int) $p['duration_days'] >= 365; ?>
    <div class="kt-card p-6 flex flex-col <?= $yearly ? 'border-2' : '' ?>" style="<?= $yearly ? 'border-color:var(--gold)' : '' ?>">
      <?php if ($yearly): ?><div class="badge-premium self-start mb-2"><?= t('best_value') ?></div><?php endif; ?>
      <h2 class="text-xl font-bold"><?= e(loc($p, 'name')) ?></h2>
      <div class="my-3">
        <span class="text-3xl font-extrabold">₹<?= (int) $p['price'] ?></span>
        <?php if ((float) $p['price'] > 0): ?><span class="opacity-60"><?= $yearly ? t('per_year') : t('per_month') ?></span><?php endif; ?>
        <?php if ($yearly): ?><div class="text-xs opacity-60"><s>₹1188</s> → ₹499</div><?php endif; ?>
      </div>
      <ul class="space-y-2 text-sm flex-1">
        <?php foreach ($feats as $f): ?><li class="flex gap-2"><span style="color:var(--morpankh-green)">✓</span> <?= e($f) ?></li><?php endforeach; ?>
      </ul>
      <?php if ((float) $p['price'] <= 0): ?>
        <a href="<?= SITE_URL ?>/register.php" class="btn btn-ghost w-full justify-center mt-5"><?= t('register') ?></a>
      <?php else: ?>
        <button onclick="buyPlan(<?= (int) $p['id'] ?>, this)" class="btn <?= $yearly ? 'btn-cta' : 'btn-primary' ?> w-full justify-center mt-5"><?= t('buy_now') ?> ₹<?= (int) $p['price'] ?></button>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>

<?php if (!$rzpEnabled): ?>
<p class="text-center text-sm opacity-60 mt-6">💡 <?= current_lang() === 'en' ? 'Payments not configured yet — free mode active.' : 'પેમેન્ટ હજુ સેટ નથી — ફ્રી મોડ ચાલુ છે.' ?></p>
<?php endif; ?>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
async function buyPlan(planId, btn) {
  if (!window.KT.loggedIn) { location.href = window.KT.url + '/login.php?next=/pricing.php'; return; }
  btn.disabled = true; btn.textContent = '⏳...';
  try {
    const r = await fetch(window.KT.url + '/api/razorpay_order.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF': window.KT.csrf },
      body: JSON.stringify({ plan_id: planId }),
    });
    const d = await r.json();
    if (!d.ok) { ktToast(d.error || 'Error', true); btn.disabled = false; return; }
    const rzp = new Razorpay({
      key: d.key, amount: d.amount, currency: 'INR', name: d.name,
      description: 'Krishna Tools Subscription', order_id: d.order_id, prefill: d.prefill,
      theme: { color: '#1E3A8A' },
      handler: function () { ktToast('🦚 ' + (window.KT.lang === 'en' ? 'Payment received! Activating...' : 'પેમેન્ટ મળ્યું! સક્રિય થઈ રહ્યું છે...')); setTimeout(() => location.href = window.KT.url + '/dashboard.php', 2500); },
    });
    rzp.on('payment.failed', () => ktToast('Payment failed', true));
    rzp.open();
  } catch (e) { ktToast('Error', true); }
  btn.disabled = false; btn.textContent = 'Buy';
}
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
