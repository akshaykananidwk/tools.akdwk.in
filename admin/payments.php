<?php
/**
 * KRISHNA TOOLS — payments ledger with status filter, totals, refund notes.
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_admin();
require_once __DIR__ . '/layout.php';

$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_check();
    $pid  = (int) ($_POST['payment_id'] ?? 0);
    $note = trim($_POST['note'] ?? '');
    try {
        $pay = $pid ? one("SELECT * FROM " . tbl('payments') . " WHERE id = :id", [':id' => $pid]) : null;
        if ($pay && $note !== '') {
            $stamp = '[' . date('Y-m-d H:i') . ' admin note] ' . $note;
            $raw = trim(($pay['raw_response'] ?? '') . "\n" . $stamp);
            $data = ['raw_response' => mb_substr($raw, 0, 60000)];
            if (($_POST['mark_refunded'] ?? '') === '1') $data['status'] = 'refunded';
            update('payments', $data, ['id' => $pid]);
            activity_log((int) current_user()['id'], 'admin_payment_note', 'payment #' . $pid);
            $flash = 'નોંધ ઉમેરાઈ.';
        } else {
            $flash = 'નોંધ ખાલી છે અથવા પેમેન્ટ મળ્યું નહીં.';
        }
    } catch (Throwable $e) {
        kt_error_log('admin payments: ' . $e->getMessage());
        $flash = 'ભૂલ આવી.';
    }
}

$status  = trim($_GET['status'] ?? '');
$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 25;
$offset  = ($page - 1) * $perPage;

$where = '';
$params = [];
if ($status !== '') { $where = " WHERE pm.status = :st"; $params[':st'] = $status; }

$total = 0; $rows = []; $sumPaid = 0.0; $sumAll = 0.0;
try {
    $total = (int) scalar("SELECT COUNT(*) FROM " . tbl('payments') . " pm" . $where, $params);
    $rows = all("SELECT pm.*, u.name AS uname, u.email AS uemail
                 FROM " . tbl('payments') . " pm
                 LEFT JOIN " . tbl('users') . " u ON u.id = pm.user_id" . $where . "
                 ORDER BY pm.id DESC LIMIT $perPage OFFSET $offset", $params);
    $sumPaid = (float) scalar("SELECT COALESCE(SUM(amount),0) FROM " . tbl('payments') . " WHERE status='paid'");
    $sumAll  = (float) scalar("SELECT COALESCE(SUM(amount),0) FROM " . tbl('payments') . $where, $params);
} catch (Throwable $e) {
    kt_error_log('admin payments list: ' . $e->getMessage());
}

$statuses = ['', 'created', 'paid', 'failed', 'refunded'];

admin_head('પેમેન્ટ્સ');
?>
<?php if ($flash): ?>
  <div class="kt-card p-3 mb-4 text-sm" style="border-color:var(--gold)"><?= e($flash) ?></div>
<?php endif; ?>

<div class="grid gap-4 sm:grid-cols-2 mb-4">
  <div class="kt-card kpi p-4"><div class="text-sm opacity-70">કુલ પેઇડ આવક</div><div class="text-2xl font-extrabold"><?= money($sumPaid) ?></div></div>
  <div class="kt-card kpi p-4"><div class="text-sm opacity-70">આ ફિલ્ટરનો સરવાળો</div><div class="text-2xl font-extrabold"><?= money($sumAll) ?></div></div>
</div>

<div class="kt-card p-4 mb-4">
  <form method="get" class="flex flex-wrap gap-2 items-center">
    <label class="kt-label !mb-0 text-sm">સ્ટેટસ:</label>
    <select name="status" class="kt-select max-w-[200px]" onchange="this.form.submit()">
      <?php foreach ($statuses as $s): ?>
        <option value="<?= e($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= $s === '' ? 'બધા' : e($s) ?></option>
      <?php endforeach; ?>
    </select>
    <span class="opacity-60 text-sm ms-auto">કુલ રેકોર્ડ: <?= number_format($total) ?></span>
  </form>
</div>

<div class="kt-card p-0 overflow-x-auto">
  <table class="kt-table">
    <thead>
      <tr><th>#</th><th>યુઝર</th><th>રકમ</th><th>સ્ટેટસ</th><th>ઓર્ડર / પેમેન્ટ ID</th><th>તારીખ</th><th>ક્રિયા</th></tr>
    </thead>
    <tbody>
    <?php if (!$rows): ?>
      <tr><td colspan="7" class="text-center opacity-60 py-6">કોઈ પેમેન્ટ મળ્યું નહીં.</td></tr>
    <?php else: foreach ($rows as $r):
        $stColor = ['paid' => 'var(--morpankh-green)', 'failed' => '#DC2626', 'refunded' => 'var(--saffron)'][$r['status']] ?? 'inherit';
    ?>
      <tr>
        <td class="opacity-60"><?= (int) $r['id'] ?></td>
        <td><div class="font-semibold"><?= e($r['uname'] ?? '—') ?></div><div class="text-xs opacity-60"><?= e($r['uemail'] ?? '') ?></div></td>
        <td class="font-semibold whitespace-nowrap"><?= money((float) $r['amount']) ?></td>
        <td><span class="text-xs font-semibold" style="color:<?= e($stColor) ?>"><?= e($r['status']) ?></span></td>
        <td class="text-xs opacity-70">
          <div><?= e($r['razorpay_order_id'] ?? '—') ?></div>
          <div class="opacity-60"><?= e($r['razorpay_payment_id'] ?? '') ?></div>
        </td>
        <td class="text-xs opacity-60 whitespace-nowrap"><?= e($r['created_at'] ?? '') ?></td>
        <td>
          <details class="relative">
            <summary class="btn btn-ghost !py-1 !px-2 cursor-pointer list-none text-sm">નોંધ</summary>
            <div class="kt-card p-3 mt-1 absolute end-0 z-30 w-72 shadow-lg">
              <form method="post" class="space-y-2">
                <?= csrf_field() ?>
                <input type="hidden" name="payment_id" value="<?= (int) $r['id'] ?>">
                <label class="kt-label !mb-1 text-xs">રિફંડ / એડમિન નોંધ</label>
                <textarea name="note" rows="3" class="kt-input !py-1.5 text-sm" placeholder="કારણ લખો..."></textarea>
                <label class="flex items-center gap-2 text-xs"><input type="checkbox" name="mark_refunded" value="1"> સ્ટેટસ "refunded" કરો</label>
                <button class="btn btn-primary !py-1 w-full justify-center text-sm">સેવ કરો</button>
              </form>
            </div>
          </details>
        </td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
<?= admin_pager($page, $total, $perPage) ?>
<?php admin_foot(); ?>
