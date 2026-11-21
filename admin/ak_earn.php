<?php
/**
 * KRISHNA TOOLS — AK Earn connection (partner / commission system at earn.akdwk.in).
 * The site key and secret are stored server-side (settings table) and never shown again or sent
 * to the browser. Values defined in config/config.php override this page.
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_admin();
require_once __DIR__ . '/layout.php';

$flash = $_SESSION['ak_flash'] ?? ''; unset($_SESSION['ak_flash']);
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_check();
    $act = $_POST['act'] ?? 'save';
    try {
        if ($act === 'save') {
            $url = trim((string) ($_POST['ak_master_url'] ?? ''));
            $key = trim((string) ($_POST['ak_site_key'] ?? ''));
            $sec = trim((string) ($_POST['ak_site_secret'] ?? ''));
            if ($url !== '' && !preg_match('#^https://[a-z0-9.-]+(/.*)?$#i', $url) && !preg_match('#^http://(localhost|127\.0\.0\.1)(:\d+)?(/.*)?$#', $url)) {
                $flash = 'Master URL must start with https://';
            } elseif ($key !== '' && !preg_match('/^[A-Za-z0-9_\-]{6,100}$/', $key)) {
                $flash = 'The site key looks wrong (letters, numbers, - and _ only).';
            } else {
                set_setting('ak_master_url', rtrim($url ?: 'https://earn.akdwk.in', '/'));
                set_setting('ak_site_key', $key);
                // The secret is write-only: an empty box keeps the saved one; the remove box clears it.
                if (!empty($_POST['ak_clear_secret'])) set_setting('ak_site_secret', '');
                elseif ($sec !== '') set_setting('ak_site_secret', $sec);
                set_setting('ak_on', isset($_POST['ak_on']) ? '1' : '0');
                activity_log((int) current_user()['id'], 'admin_ak_earn', 'AK Earn settings saved');
                $_SESSION['ak_flash'] = 'Saved. Now press "Test connection".';
                redirect(SITE_URL . '/admin/ak_earn.php');
            }
        } elseif (!ak_enabled()) {
            $flash = 'Enter the site key and secret (and keep "Tracking on" ticked) first.';
        } elseif ($act === 'ping') {
            $result = ['Test connection' => ak_ping()];
        } elseif ($act === 'sync') {
            $result = ['Sync plans' => ak_sync_site_products(true), 'Plans sent' => ak_site_products()];
        } elseif ($act === 'flush') {
            $result = ['Retry queue' => ['sent' => ak_flush_queue(200)]];
        } elseif ($act === 'test') {
            $id = 'TEST-' . date('YmdHis');
            $result = [
                'Test order id' => $id,
                'order_created' => ak_track_order($id, 1, 'kt-plan-test', 'Test Customer', '9999999999', 'created', ['test' => true]),
                'payment_success' => ak_track_order($id, 1, 'kt-plan-test', 'Test Customer', '9999999999', 'paid', ['test' => true]),
                'refund' => ak_track_refund($id, 'admin test'),
            ];
        }
        if ($result !== null) activity_log((int) current_user()['id'], 'admin_ak_earn', 'AK Earn ' . $act);
    } catch (Throwable $e) {
        kt_ak_log('admin: ' . $e->getMessage());
        $flash = 'Something went wrong: ' . $e->getMessage();
    }
}

ak_boot();
$savedKey = (string) setting('ak_site_key', '');
$hasSecret = (string) setting('ak_site_secret', '') !== '';
$on = setting('ak_on', '1') === '1';
$fromConfig = ['AK_SITE_KEY' => ak_source('AK_SITE_KEY') === 'config', 'AK_SITE_SECRET' => ak_source('AK_SITE_SECRET') === 'config'];
$queueFile = function_exists('ak_queue_file') && ak_enabled() ? ak_queue_file() : '';
$queued = $queueFile && is_file($queueFile) ? count(file($queueFile, FILE_SKIP_EMPTY_LINES) ?: []) : 0;
$mask = fn(string $s) => $s === '' ? '' : substr($s, 0, 4) . str_repeat('•', max(0, min(12, strlen($s) - 6))) . substr($s, -2);

admin_head('AK Earn');
?>
<?php if ($flash): ?>
  <div class="kt-card p-3 mb-4 text-sm" style="border-color:var(--gold)"><?= e($flash) ?></div>
<?php endif; ?>

<div class="grid gap-4 lg:grid-cols-[1fr,360px]">
  <form method="post" class="kt-card p-5 space-y-4" autocomplete="off">
    <?= csrf_field() ?><input type="hidden" name="act" value="save">
    <div class="flex items-center justify-between gap-3">
      <h2 class="section-title text-lg">AK Earn connection</h2>
      <span class="text-xs font-bold px-3 py-1 rounded-full" style="background:<?= ak_enabled() ? 'rgba(5,150,105,.12);color:#059669' : 'rgba(220,38,38,.1);color:#DC2626' ?>"><?= ak_enabled() ? '● Connected (keys set)' : '● Off – keys missing' ?></span>
    </div>
    <p class="text-sm opacity-70">Copy the <b>Site key</b> and <b>Secret</b> from AK Earn admin → Sites → Krishna Tools → API credentials. The secret is kept on the server only: it is never shown again, never sent to the browser and never written to logs.</p>

    <div>
      <label class="kt-label" for="ak_master_url">Master URL</label>
      <input class="kt-input" id="ak_master_url" name="ak_master_url" value="<?= e(setting('ak_master_url', 'https://earn.akdwk.in')) ?>" placeholder="https://earn.akdwk.in">
    </div>
    <div>
      <label class="kt-label" for="ak_site_key">Site key</label>
      <?php if ($fromConfig['AK_SITE_KEY']): ?>
        <div class="kt-input opacity-70"><?= e($mask((string) AK_SITE_KEY)) ?> <span class="text-xs">(set in config/config.php — edit it there)</span></div>
      <?php else: ?>
        <input class="kt-input font-mono" id="ak_site_key" name="ak_site_key" value="<?= e($savedKey) ?>" placeholder="sk_xxxxxxxx" spellcheck="false">
      <?php endif; ?>
    </div>
    <div>
      <label class="kt-label" for="ak_site_secret">Secret</label>
      <?php if ($fromConfig['AK_SITE_SECRET']): ?>
        <div class="kt-input opacity-70">•••••••• <span class="text-xs">(set in config/config.php — edit it there)</span></div>
      <?php else: ?>
        <input class="kt-input font-mono" type="password" id="ak_site_secret" name="ak_site_secret" value="" autocomplete="new-password" spellcheck="false"
               placeholder="<?= $hasSecret ? 'Saved ✓ — leave empty to keep it, or paste a new one' : 'Paste the secret here' ?>">
        <?php if ($hasSecret): ?><label class="flex items-center gap-2 text-xs mt-2"><input type="checkbox" name="ak_clear_secret" value="1"> Remove the saved secret</label><?php endif; ?>
      <?php endif; ?>
    </div>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="ak_on" <?= $on ? 'checked' : '' ?>> Tracking on (untick to pause sending events without deleting the keys)</label>
    <button class="btn btn-primary"><i data-lucide="save" class="w-4 h-4"></i> Save</button>
  </form>

  <div class="space-y-4">
    <div class="kt-card p-5 space-y-2">
      <h2 class="section-title text-base">Check & tools</h2>
      <?php foreach ([['ping', 'plug-zap', 'Test connection (ak_ping)', 'btn-primary'], ['sync', 'refresh-cw', 'Send plans to AK Earn', 'btn-ghost'], ['test', 'flask-conical', 'Send ₹1 test order + refund', 'btn-ghost'], ['flush', 'send', 'Retry queued events (' . $queued . ')', 'btn-ghost']] as [$a, $ic, $lb, $cls]): ?>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="act" value="<?= $a ?>">
          <button class="btn <?= $cls ?> w-full justify-center text-sm" <?= ak_enabled() ? '' : 'disabled' ?> <?= $a === 'test' ? 'onclick="return confirm(\'Send a ₹1 TEST order, payment and refund to AK Earn? Use this on staging or right after setup.\')"' : '' ?>><i data-lucide="<?= $ic ?>" class="w-4 h-4"></i> <?= e($lb) ?></button></form>
      <?php endforeach; ?>
      <p class="text-xs opacity-60">After a test, open AK Earn admin → Sites → this site → Live event log.</p>
    </div>
    <div class="kt-card p-5 text-sm space-y-1">
      <h2 class="section-title text-base mb-1">What is sent</h2>
      <div>• Page views + referral code (<code>?ref=</code>) via ak-connect.js</div>
      <div>• New account → signup</div>
      <div>• Contact form with mobile → lead</div>
      <div>• Razorpay order → order created</div>
      <div>• Razorpay payment confirmed → order / renewal</div>
      <div>• Refund (Razorpay or "mark refunded") → refund</div>
      <div class="opacity-60 text-xs pt-1">No commission amounts are sent — AK Earn calculates them.</div>
    </div>
  </div>
</div>

<?php if ($result !== null): ?>
  <div class="kt-card p-5 mt-4">
    <?php $ok = ($result['Test connection']['status'] ?? $result['Sync plans']['status'] ?? $result['payment_success']['status'] ?? 'ok') === 'ok'; ?>
    <h2 class="section-title text-base mb-2"><?= $ok ? '✅ Result' : '⚠️ Result' ?></h2>
    <pre class="text-xs overflow-auto p-3 rounded-lg" style="background:rgba(0,0,0,.05);max-height:420px"><?= e(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre>
  </div>
<?php endif; ?>
<?php admin_foot(); ?>
