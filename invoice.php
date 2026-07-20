<?php
/** KRISHNA TOOLS — printable A4 invoice for a paid payment owned by the user. */
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$user = current_user();
$id = (int) ($_GET['id'] ?? 0);

// Load the payment and ensure it belongs to the current user and is paid.
$pay = one("SELECT * FROM " . tbl('payments') . " WHERE id = :id AND user_id = :u",
           [':id' => $id, ':u' => $user['id']]);
if (!$pay || $pay['status'] !== 'paid') {
    http_response_code(404);
    exit('Invoice not found.');
}

// Resolve the plan/description via the linked subscription (by order id).
$plan = null;
if (!empty($pay['razorpay_order_id'])) {
    $plan = one("SELECT p.* FROM " . tbl('subscriptions') . " s
                 JOIN " . tbl('plans') . " p ON p.id = s.plan_id
                 WHERE s.order_id = :o LIMIT 1", [':o' => $pay['razorpay_order_id']]);
}
$en = current_lang() === 'en';

// Shop letterhead from settings.
$shopName    = setting('shop_name', 'AK Computer');
$shopAddress = setting('shop_address', 'Dwarka, Gujarat');
$shopPhone   = setting('shop_phone', '9978123146');
$gstin       = setting('gstin', '');

// Amounts. Plan prices are GST-inclusive; derive base + GST @18%.
$total = (float) $pay['amount'];
$gstRate = 18.0;
$base = $gstin ? round($total / (1 + $gstRate / 100), 2) : $total;
$gst  = round($total - $base, 2);

$invoiceNo = 'AKC-' . str_pad((string) $pay['id'], 5, '0', STR_PAD_LEFT);
$date = $pay['created_at'] ? date('d-m-Y', strtotime($pay['created_at'])) : date('d-m-Y');
$desc = $plan ? loc($plan, 'name') . ' ' . ($en ? 'Plan' : 'પ્લાન') : ($en ? 'Subscription' : 'સબસ્ક્રિપ્શન');
?><!DOCTYPE html>
<html lang="<?= e(current_lang()) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($invoiceNo) ?> — <?= e($shopName) ?></title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Segoe UI', Arial, sans-serif; color: #1f2937; background: #eef1f5; padding: 20px; }
  .sheet { width: 210mm; min-height: 297mm; margin: 0 auto; background: #fff; padding: 18mm; box-shadow: 0 4px 24px rgba(0,0,0,.12); }
  .head { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #1E3A8A; padding-bottom: 14px; }
  .brand { font-size: 26px; font-weight: 800; color: #1E3A8A; }
  .brand small { display: block; font-size: 12px; font-weight: 500; color: #6b7280; margin-top: 4px; }
  .inv-title { text-align: right; }
  .inv-title h1 { font-size: 22px; color: #B45309; letter-spacing: 1px; }
  .inv-title .no { font-family: monospace; font-size: 14px; margin-top: 6px; }
  .meta { display: flex; justify-content: space-between; margin: 24px 0; font-size: 13px; }
  .meta .box h3 { font-size: 11px; text-transform: uppercase; color: #9ca3af; margin-bottom: 4px; }
  table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13px; }
  th { background: #1E3A8A; color: #fff; text-align: left; padding: 10px; font-weight: 600; }
  td { padding: 10px; border-bottom: 1px solid #e5e7eb; }
  td.r, th.r { text-align: right; }
  .totals { margin-left: auto; width: 55%; margin-top: 14px; font-size: 13px; }
  .totals tr td { border: none; padding: 6px 10px; }
  .totals .grand td { border-top: 2px solid #1E3A8A; font-size: 16px; font-weight: 800; color: #1E3A8A; }
  .note { margin-top: 26px; font-size: 12px; color: #6b7280; line-height: 1.6; }
  .footer { margin-top: 36px; border-top: 1px solid #e5e7eb; padding-top: 12px; text-align: center; font-size: 12px; color: #9ca3af; }
  .paid-stamp { display: inline-block; border: 2px solid #059669; color: #059669; padding: 4px 14px; border-radius: 6px; font-weight: 700; transform: rotate(-6deg); margin-top: 10px; }
  .actions { max-width: 210mm; margin: 0 auto 14px; text-align: right; }
  .btn-print { background: #1E3A8A; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-size: 14px; cursor: pointer; }
  @media print {
    body { background: #fff; padding: 0; }
    .sheet { box-shadow: none; margin: 0; width: auto; min-height: auto; padding: 12mm; }
    .actions { display: none; }
  }
</style>
</head>
<body>
<div class="actions">
  <button class="btn-print" onclick="window.print()"><?= $en ? 'Print / Save PDF' : 'પ્રિન્ટ / PDF સેવ કરો' ?></button>
</div>
<div class="sheet">
  <div class="head">
    <div>
      <div class="brand"><?= e($shopName) ?><small><?= e($shopAddress) ?><?php if ($shopPhone): ?> · 📞 <?= e($shopPhone) ?><?php endif; ?></small></div>
      <?php if ($gstin): ?><div style="font-size:12px;margin-top:6px;">GSTIN: <b><?= e($gstin) ?></b></div><?php endif; ?>
    </div>
    <div class="inv-title">
      <h1><?= $en ? 'INVOICE' : 'ઇન્વોઇસ' ?></h1>
      <div class="no"><?= e($invoiceNo) ?></div>
      <div style="font-size:12px;color:#6b7280;margin-top:4px;"><?= $en ? 'Date' : 'તારીખ' ?>: <?= e($date) ?></div>
    </div>
  </div>

  <div class="meta">
    <div class="box">
      <h3><?= $en ? 'Billed To' : 'ગ્રાહક' ?></h3>
      <div><b><?= e($user['name']) ?></b></div>
      <div><?= e($user['email']) ?></div>
      <?php if (!empty($user['phone'])): ?><div><?= e($user['phone']) ?></div><?php endif; ?>
    </div>
    <div class="box" style="text-align:right;">
      <h3><?= $en ? 'Payment Ref' : 'પેમેન્ટ સંદર્ભ' ?></h3>
      <div style="font-family:monospace;font-size:12px;"><?= e($pay['razorpay_payment_id'] ?: ('#' . $pay['id'])) ?></div>
      <div class="paid-stamp"><?= $en ? 'PAID' : 'ચૂકવેલ' ?></div>
    </div>
  </div>

  <table>
    <thead>
      <tr><th><?= $en ? 'Description' : 'વર્ણન' ?></th><th class="r"><?= $en ? 'Amount' : 'રકમ' ?></th></tr>
    </thead>
    <tbody>
      <tr><td><?= e($desc) ?></td><td class="r"><?= money($base) ?></td></tr>
    </tbody>
  </table>

  <table class="totals">
    <tr><td><?= $en ? 'Subtotal' : 'સબટોટલ' ?></td><td class="r"><?= money($base) ?></td></tr>
    <?php if ($gstin): ?>
      <tr><td>CGST @<?= number_format($gstRate / 2, 1) ?>%</td><td class="r"><?= money(round($gst / 2, 2)) ?></td></tr>
      <tr><td>SGST @<?= number_format($gstRate / 2, 1) ?>%</td><td class="r"><?= money($gst - round($gst / 2, 2)) ?></td></tr>
    <?php endif; ?>
    <tr class="grand"><td><?= $en ? 'Total' : 'કુલ' ?></td><td class="r"><?= money($total) ?></td></tr>
  </table>

  <div class="note">
    <?php if ($gstin): ?>
      <?= $en
        ? 'All amounts are in INR and inclusive of GST @18%. This is a computer-generated invoice and does not require a signature.'
        : 'બધી રકમ INR માં અને GST @18% સહિત છે. આ કમ્પ્યુટર-જનરેટેડ ઇન્વોઇસ છે, સહીની જરૂર નથી.' ?>
    <?php else: ?>
      <?= $en
        ? 'All amounts are in INR. This is a computer-generated invoice and does not require a signature.'
        : 'બધી રકમ INR માં છે. આ કમ્પ્યુટર-જનરેટેડ ઇન્વોઇસ છે, સહીની જરૂર નથી.' ?>
    <?php endif; ?>
  </div>

  <div class="footer">
    🦚 <?= e(setting('site_name', 'કૃષ્ણા ટૂલ્સ')) ?> · <?= e($shopName) ?>, દ્વારકા · <?= $en ? 'Thank you!' : 'આભાર! હરે કૃષ્ણ.' ?>
  </div>
</div>
</body>
</html>
