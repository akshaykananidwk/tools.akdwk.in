<?php
/** KRISHNA TOOLS — billing: list the user's payments + subscriptions. */
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$user = current_user();
$en = current_lang() === 'en';

// All payments for this user, newest first.
$payments = all("SELECT * FROM " . tbl('payments') . " WHERE user_id = :u ORDER BY id DESC", [':u' => $user['id']]);

// Subscriptions joined with plan names.
$subs = all("SELECT s.*, p.name_gu, p.name_en FROM " . tbl('subscriptions') . " s
             LEFT JOIN " . tbl('plans') . " p ON p.id = s.plan_id
             WHERE s.user_id = :u ORDER BY s.id DESC", [':u' => $user['id']]);

$page_title = ($en ? 'Billing' : 'બિલિંગ') . ' — ' . setting('site_name', 'કૃષ્ણા ટૂલ્સ');
$breadcrumb = [
    ['label' => t('home'), 'url' => SITE_URL . '/'],
    ['label' => t('dashboard'), 'url' => SITE_URL . '/dashboard.php'],
    ['label' => $en ? 'Billing' : 'બિલિંગ'],
];
require __DIR__ . '/includes/header.php';
?>
<div class="max-w-4xl mx-auto my-6">
  <h1 class="section-title text-2xl mb-1"><?= $en ? 'Billing & Payments' : 'બિલિંગ અને પેમેન્ટ' ?></h1>
  <div class="divider-gold mb-5"></div>

  <!-- Subscriptions -->
  <div class="kt-card p-5 mb-6">
    <h2 class="font-bold mb-3"><?= $en ? 'Subscriptions' : 'સબસ્ક્રિપ્શન' ?></h2>
    <?php if ($subs): ?>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="text-left opacity-60">
            <tr>
              <th class="py-2"><?= $en ? 'Plan' : 'પ્લાન' ?></th>
              <th class="py-2"><?= $en ? 'Amount' : 'રકમ' ?></th>
              <th class="py-2"><?= $en ? 'Status' : 'સ્ટેટસ' ?></th>
              <th class="py-2"><?= $en ? 'Valid till' : 'માન્યતા' ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($subs as $s): ?>
              <tr class="border-t" style="border-color:var(--border)">
                <td class="py-2"><?= e(loc($s, 'name')) ?></td>
                <td class="py-2"><?= money((float) $s['amount']) ?></td>
                <td class="py-2">
                  <span class="badge-premium"><?= e($s['status']) ?></span>
                </td>
                <td class="py-2"><?= $s['end_date'] ? e(date('d-m-Y', strtotime($s['end_date']))) : '—' ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="opacity-60 text-sm"><?= $en ? 'No subscriptions yet.' : 'હજુ કોઈ સબસ્ક્રિપ્શન નથી.' ?>
        <a href="<?= SITE_URL ?>/pricing.php" class="text-[color:var(--peacock-teal)] hover:underline"><?= t('upgrade') ?></a></p>
    <?php endif; ?>
  </div>

  <!-- Payments -->
  <div class="kt-card p-5">
    <h2 class="font-bold mb-3"><?= $en ? 'Payment History' : 'પેમેન્ટ ઇતિહાસ' ?></h2>
    <?php if ($payments): ?>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="text-left opacity-60">
            <tr>
              <th class="py-2"><?= $en ? 'Date' : 'તારીખ' ?></th>
              <th class="py-2"><?= $en ? 'Payment ID' : 'પેમેન્ટ ID' ?></th>
              <th class="py-2"><?= $en ? 'Amount' : 'રકમ' ?></th>
              <th class="py-2"><?= $en ? 'Status' : 'સ્ટેટસ' ?></th>
              <th class="py-2"><?= $en ? 'Invoice' : 'ઇન્વોઇસ' ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($payments as $p): ?>
              <tr class="border-t" style="border-color:var(--border)">
                <td class="py-2"><?= $p['created_at'] ? e(date('d-m-Y', strtotime($p['created_at']))) : '—' ?></td>
                <td class="py-2 font-mono text-xs"><?= e($p['razorpay_payment_id'] ?: '—') ?></td>
                <td class="py-2"><?= money((float) $p['amount']) ?></td>
                <td class="py-2">
                  <?php $paid = $p['status'] === 'paid'; ?>
                  <span class="text-xs px-2 py-1 rounded-full" style="background:<?= $paid ? 'rgba(5,150,105,.12);color:#059669' : 'rgba(180,83,9,.12);color:#B45309' ?>"><?= e($p['status']) ?></span>
                </td>
                <td class="py-2">
                  <?php if ($p['status'] === 'paid'): ?>
                    <a href="<?= SITE_URL ?>/invoice.php?id=<?= (int) $p['id'] ?>" class="btn btn-ghost text-xs !py-1">
                      <i data-lucide="download" class="w-3 h-3"></i> <?= $en ? 'Invoice' : 'ઇન્વોઇસ' ?>
                    </a>
                  <?php else: ?>
                    <span class="opacity-40 text-xs">—</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="opacity-60 text-sm"><?= $en ? 'No payments yet.' : 'હજુ કોઈ પેમેન્ટ નથી.' ?></p>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
