<?php
/**
 * KRISHNA TOOLS — WhatsApp admin: status + test send, logs, template editor,
 * and a capped synchronous broadcast composer.
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_admin();
require_once __DIR__ . '/../includes/whatsapp.php';
require_once __DIR__ . '/layout.php';

$flash = '';
$testResult = null;
$broadcastSummary = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_check();
    $form = $_POST['form'] ?? '';
    $me = current_user();
    try {
        if ($form === 'test') {
            $num = trim($_POST['number'] ?? '');
            $msg = trim($_POST['message'] ?? '');
            if ($num !== '' && $msg !== '') {
                $res = wa()->send($num, $msg, (int) $me['id']);
                $testResult = $res;
                $flash = $res['ok'] ? 'ટેસ્ટ મેસેજ મોકલાયો.' : 'મોકલવામાં નિષ્ફળ.';
            } else {
                $flash = 'નંબર અને મેસેજ જરૂરી છે.';
            }
        } elseif ($form === 'template') {
            $tid = (int) ($_POST['template_id'] ?? 0);
            if ($tid && one("SELECT id FROM " . tbl('whatsapp_templates') . " WHERE id = :id", [':id' => $tid])) {
                update('whatsapp_templates', [
                    'body_gu'   => trim($_POST['body_gu'] ?? ''),
                    'body_en'   => trim($_POST['body_en'] ?? ''),
                    'is_active' => isset($_POST['is_active']) ? 1 : 0,
                ], ['id' => $tid]);
                activity_log((int) $me['id'], 'admin_wa_template', 'template #' . $tid);
                $flash = 'ટેમ્પ્લેટ સેવ થયું.';
            }
        } elseif ($form === 'broadcast') {
            $broadcastSummary = admin_wa_broadcast($_POST, (int) $me['id']);
            $flash = 'બ્રોડકાસ્ટ પૂર્ણ: ' . $broadcastSummary['sent'] . ' મોકલાયા, '
                   . $broadcastSummary['failed'] . ' નિષ્ફળ, ' . $broadcastSummary['skipped'] . ' છોડ્યા.';
        }
    } catch (Throwable $e) {
        kt_error_log('admin whatsapp: ' . $e->getMessage());
        $flash = 'ભૂલ આવી: ' . $e->getMessage();
    }
}

/**
 * Build the audience, then send synchronously with a 3s gap (capped at 200).
 * Each send is isolated in try/catch so one failure never aborts the run.
 */
function admin_wa_broadcast(array $post, int $adminId): array {
    $cap = 200;
    $message = trim($post['bmessage'] ?? '');
    $audience = $post['audience'] ?? 'all';
    if ($message === '') return ['sent' => 0, 'failed' => 0, 'skipped' => 0, 'total' => 0, 'note' => 'ખાલી મેસેજ'];

    // Collect target numbers.
    $numbers = [];
    if ($audience === 'paste') {
        foreach (preg_split('/[\s,;]+/', $post['numbers'] ?? '') as $n) {
            $n = trim($n);
            if ($n !== '') $numbers[] = $n;
        }
    } elseif ($audience === 'plan') {
        $planId = (int) ($post['plan_id'] ?? 0);
        $rows = all("SELECT phone FROM " . tbl('users') . "
                     WHERE plan_id = :p AND plan_expiry > NOW() AND phone IS NOT NULL AND phone <> '' AND status = 'active'",
                    [':p' => $planId]);
        foreach ($rows as $r) $numbers[] = $r['phone'];
    } else { // all active users with a phone
        $rows = all("SELECT phone FROM " . tbl('users') . "
                     WHERE phone IS NOT NULL AND phone <> '' AND status = 'active'");
        foreach ($rows as $r) $numbers[] = $r['phone'];
    }

    // De-duplicate on the normalised form.
    $seen = []; $clean = [];
    foreach ($numbers as $n) {
        $norm = normalize_phone($n);
        if (strlen($norm) < 10 || isset($seen[$norm])) continue;
        $seen[$norm] = true; $clean[] = $norm;
    }

    $total = count($clean);
    $skipped = max(0, $total - $cap);
    $clean = array_slice($clean, 0, $cap);

    @set_time_limit(0);
    @ignore_user_abort(true);

    $sent = 0; $failed = 0;
    foreach ($clean as $i => $num) {
        try {
            $res = wa()->send($num, $message, null);
            if (!empty($res['ok'])) $sent++; else $failed++;
        } catch (Throwable $e) {
            $failed++;
            kt_error_log('broadcast send failed ' . $num . ': ' . $e->getMessage());
        }
        // 3-second gap between sends (not after the last one).
        if ($i < count($clean) - 1) usleep(3000000);
    }
    activity_log($adminId, 'admin_wa_broadcast', "sent=$sent failed=$failed skipped=$skipped total=$total");
    return ['sent' => $sent, 'failed' => $failed, 'skipped' => $skipped, 'total' => $total, 'note' => ''];
}

// ── Data for the page ──────────────────────────────────────
$configured = false;
try { $configured = wa()->isConfigured(); } catch (Throwable $e) { $configured = false; }

$fStatus = trim($_GET['status'] ?? '');
$fNumber = trim($_GET['number'] ?? '');
$fDate   = trim($_GET['date'] ?? '');
$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 25;
$offset  = ($page - 1) * $perPage;

$logWhere = []; $logParams = [];
if ($fStatus !== '') { $logWhere[] = 'status = :st'; $logParams[':st'] = $fStatus; }
if ($fNumber !== '') { $logWhere[] = 'number LIKE :nu'; $logParams[':nu'] = '%' . $fNumber . '%'; }
if ($fDate   !== '') { $logWhere[] = 'DATE(created_at) = :dt'; $logParams[':dt'] = $fDate; }
$logSql = $logWhere ? ' WHERE ' . implode(' AND ', $logWhere) : '';

$logs = []; $logTotal = 0; $templates = []; $plans = [];
try {
    $logTotal = (int) scalar("SELECT COUNT(*) FROM " . tbl('whatsapp_logs') . $logSql, $logParams);
    $logs = all("SELECT * FROM " . tbl('whatsapp_logs') . $logSql . "
                 ORDER BY id DESC LIMIT $perPage OFFSET $offset", $logParams);
    $templates = all("SELECT * FROM " . tbl('whatsapp_templates') . " ORDER BY id ASC");
    $plans = all("SELECT * FROM " . tbl('plans') . " ORDER BY price ASC");
} catch (Throwable $e) {
    kt_error_log('admin whatsapp data: ' . $e->getMessage());
}

admin_head('વ્હોટ્સએપ');
?>
<?php if ($flash): ?>
  <div class="kt-card p-3 mb-4 text-sm" style="border-color:var(--gold)"><?= e($flash) ?></div>
<?php endif; ?>

<!-- (a) Status + test send -->
<div class="grid gap-4 lg:grid-cols-2 mb-5">
  <div class="kt-card p-5">
    <h2 class="section-title text-base mb-3">કનેક્શન સ્ટેટસ</h2>
    <?php if ($configured): ?>
      <div class="flex items-center gap-2 text-sm" style="color:var(--morpankh-green)">
        <i data-lucide="check-circle-2" class="w-5 h-5"></i> WhatsApp API કન્ફિગર થયેલ છે.
      </div>
    <?php else: ?>
      <div class="flex items-center gap-2 text-sm" style="color:#DC2626">
        <i data-lucide="x-circle" class="w-5 h-5"></i> કન્ફિગર નથી. <a href="settings.php" class="underline">સેટિંગ્સમાં</a> API કી ઉમેરો.
      </div>
    <?php endif; ?>
    <?php if ($testResult !== null): ?>
      <div class="mt-3 text-xs">
        <div class="kt-label !mb-1">API પ્રતિસાદ:</div>
        <pre class="p-2 rounded-lg overflow-auto text-xs" style="background:rgba(0,0,0,.05);max-height:150px"><?= e(is_array($testResult) ? json_encode($testResult, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : (string) $testResult) ?></pre>
      </div>
    <?php endif; ?>
  </div>
  <div class="kt-card p-5">
    <h2 class="section-title text-base mb-3">ટેસ્ટ મેસેજ મોકલો</h2>
    <form method="post" class="space-y-3">
      <?= csrf_field() ?>
      <input type="hidden" name="form" value="test">
      <div><label class="kt-label text-xs">નંબર (91XXXXXXXXXX)</label><input name="number" class="kt-input !py-1.5" placeholder="919978123146" required></div>
      <div><label class="kt-label text-xs">મેસેજ</label><textarea name="message" rows="3" class="kt-input !py-1.5" required></textarea></div>
      <button class="btn btn-wa w-full justify-center"><i data-lucide="send" class="w-4 h-4"></i> મોકલો</button>
    </form>
  </div>
</div>

<!-- (d) Broadcast composer -->
<div class="kt-card p-5 mb-5">
  <h2 class="section-title text-base mb-1">બ્રોડકાસ્ટ</h2>
  <p class="text-xs opacity-70 mb-3">દરેક મેસેજ વચ્ચે 3 સેકન્ડનો વિરામ. એક વખતમાં મહત્તમ 200 નંબર (વધારાના છોડી દેવાય).</p>
  <?php if ($broadcastSummary): ?>
    <div class="text-sm mb-3 p-3 rounded-lg" style="background:rgba(5,150,105,.1)">
      કુલ <?= (int) $broadcastSummary['total'] ?> | મોકલાયા <?= (int) $broadcastSummary['sent'] ?> | નિષ્ફળ <?= (int) $broadcastSummary['failed'] ?> | છોડ્યા <?= (int) $broadcastSummary['skipped'] ?>
    </div>
  <?php endif; ?>
  <form method="post" class="space-y-3" onsubmit="return confirm('બ્રોડકાસ્ટ મોકલવાની ખાતરી છે? આ સમય લેશે.');">
    <?= csrf_field() ?>
    <input type="hidden" name="form" value="broadcast">
    <div><label class="kt-label text-xs">મેસેજ</label><textarea name="bmessage" rows="4" class="kt-input !py-1.5" required></textarea></div>
    <div class="grid sm:grid-cols-2 gap-3">
      <div>
        <label class="kt-label text-xs">ઓડિયન્સ</label>
        <select name="audience" class="kt-select !py-1.5" onchange="document.getElementById('planWrap').style.display=this.value==='plan'?'block':'none';document.getElementById('pasteWrap').style.display=this.value==='paste'?'block':'none';">
          <option value="all">બધા એક્ટિવ યુઝર્સ</option>
          <option value="plan">પ્લાન પ્રમાણે</option>
          <option value="paste">નંબર પેસ્ટ કરો</option>
        </select>
      </div>
      <div id="planWrap" style="display:none">
        <label class="kt-label text-xs">પ્લાન</label>
        <select name="plan_id" class="kt-select !py-1.5">
          <?php foreach ($plans as $p): ?><option value="<?= (int) $p['id'] ?>"><?= e(loc($p, 'name')) ?></option><?php endforeach; ?>
        </select>
      </div>
    </div>
    <div id="pasteWrap" style="display:none">
      <label class="kt-label text-xs">નંબર (સ્પેસ / કોમા / લાઇનથી અલગ)</label>
      <textarea name="numbers" rows="3" class="kt-input !py-1.5" placeholder="919978123146, 919876543210"></textarea>
    </div>
    <button class="btn btn-cta"><i data-lucide="megaphone" class="w-4 h-4"></i> બ્રોડકાસ્ટ મોકલો</button>
  </form>
</div>

<!-- (c) Template editor -->
<div class="kt-card p-5 mb-5">
  <h2 class="section-title text-base mb-3">ટેમ્પ્લેટ એડિટર</h2>
  <?php if (!$templates): ?>
    <p class="opacity-60 text-sm">કોઈ ટેમ્પ્લેટ નથી.</p>
  <?php else: ?>
    <div class="space-y-3">
      <?php foreach ($templates as $tpl): ?>
        <details class="border rounded-xl p-3" style="border-color:var(--border)">
          <summary class="cursor-pointer font-semibold flex items-center justify-between list-none">
            <span><?= e($tpl['name_gu'] ?: $tpl['key_name']) ?> <span class="text-xs opacity-50">(<?= e($tpl['key_name']) ?>)</span></span>
            <span class="text-xs" style="color:<?= (int) $tpl['is_active'] === 1 ? 'var(--morpankh-green)' : '#DC2626' ?>"><?= (int) $tpl['is_active'] === 1 ? 'એક્ટિવ' : 'નિષ્ક્રિય' ?></span>
          </summary>
          <form method="post" class="mt-3 space-y-2">
            <?= csrf_field() ?>
            <input type="hidden" name="form" value="template">
            <input type="hidden" name="template_id" value="<?= (int) $tpl['id'] ?>">
            <?php if ($tpl['variables']): ?><p class="text-xs opacity-60">વેરિએબલ્સ: <?= e($tpl['variables']) ?> (<code>:નામ</code> તરીકે વાપરો)</p><?php endif; ?>
            <div><label class="kt-label text-xs">બોડી (ગુ)</label><textarea name="body_gu" rows="2" class="kt-input !py-1.5 text-sm"><?= e($tpl['body_gu'] ?? '') ?></textarea></div>
            <div><label class="kt-label text-xs">Body (EN)</label><textarea name="body_en" rows="2" class="kt-input !py-1.5 text-sm"><?= e($tpl['body_en'] ?? '') ?></textarea></div>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" <?= (int) $tpl['is_active'] === 1 ? 'checked' : '' ?>> એક્ટિવ</label>
            <button class="btn btn-primary !py-1 text-sm"><i data-lucide="save" class="w-3.5 h-3.5"></i> સેવ કરો</button>
          </form>
        </details>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<!-- (b) Message logs -->
<div class="kt-card p-5">
  <h2 class="section-title text-base mb-3">મેસેજ લોગ્સ</h2>
  <form method="get" class="flex flex-wrap gap-2 items-end mb-3">
    <div><label class="kt-label text-xs">સ્ટેટસ</label>
      <select name="status" class="kt-select !py-1.5">
        <?php foreach (['', 'sent', 'failed', 'not_configured'] as $s): ?>
          <option value="<?= e($s) ?>" <?= $fStatus === $s ? 'selected' : '' ?>><?= $s === '' ? 'બધા' : e($s) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div><label class="kt-label text-xs">નંબર</label><input name="number" value="<?= e($fNumber) ?>" class="kt-input !py-1.5"></div>
    <div><label class="kt-label text-xs">તારીખ</label><input type="date" name="date" value="<?= e($fDate) ?>" class="kt-input !py-1.5"></div>
    <button class="btn btn-primary">ફિલ્ટર</button>
    <a href="whatsapp.php" class="btn btn-ghost">રિસેટ</a>
    <span class="opacity-60 text-sm ms-auto">કુલ: <?= number_format($logTotal) ?></span>
  </form>
  <div class="overflow-x-auto">
    <table class="kt-table">
      <thead><tr><th>#</th><th>નંબર</th><th>પ્રકાર</th><th>મેસેજ</th><th>સ્ટેટસ</th><th>સમય</th></tr></thead>
      <tbody>
      <?php if (!$logs): ?>
        <tr><td colspan="6" class="text-center opacity-60 py-6">કોઈ લોગ નથી.</td></tr>
      <?php else: foreach ($logs as $l): ?>
        <tr>
          <td class="opacity-60"><?= (int) $l['id'] ?></td>
          <td class="text-xs"><?= e($l['number'] ?? '') ?></td>
          <td class="text-xs opacity-70"><?= e($l['type'] ?? '') ?></td>
          <td class="text-xs opacity-70 max-w-[240px] truncate"><?= e($l['message'] ?? '') ?></td>
          <td><span class="text-xs font-semibold" style="color:<?= ($l['status'] ?? '') === 'sent' ? 'var(--morpankh-green)' : '#DC2626' ?>"><?= e($l['status'] ?? '') ?></span></td>
          <td class="text-xs opacity-60 whitespace-nowrap"><?= e($l['created_at'] ?? '') ?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <?= admin_pager($page, $logTotal, $perPage) ?>
</div>
<?php admin_foot(); ?>
