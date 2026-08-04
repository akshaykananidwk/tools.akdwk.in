<?php
/**
 * KRISHNA TOOLS — Admin → Cron / Scheduler.
 * One place to manage every background task: status, health, per-job enable/
 * disable, manual run, retry failed queue items, and execution history.
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_admin();
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/../includes/scheduler.php';

$scheduler = new Scheduler();
$flash = ''; $flashType = 'ok';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_check();
    $action = $_POST['action'] ?? '';
    $key = preg_replace('/[^a-z0-9_]/', '', $_POST['job'] ?? '');
    try {
        if ($action === 'toggle') {
            $scheduler->setEnabled($key, ($_POST['enable'] ?? '0') === '1');
            $flash = "Job '$key' " . (($_POST['enable'] ?? '0') === '1' ? 'enabled' : 'disabled') . '.';
        } elseif ($action === 'run') {
            $res = $scheduler->runJob($key, true, 'admin');
            $flash = "Ran '$key': {$res['status']} — {$res['message']}";
            $flashType = $res['ok'] ? 'ok' : 'err';
        } elseif ($action === 'run_master') {
            if ($scheduler->acquireMasterLock()) {
                $summary = $scheduler->runDue('admin');
                $scheduler->releaseMasterLock();
                $ran = array_filter($summary, fn($s) => $s !== 'not-due' && $s !== 'disabled');
                $flash = 'Master run complete. ' . ($ran ? implode(', ', array_map(fn($k, $v) => "$k:$v", array_keys($ran), $ran)) : 'nothing was due');
            } else { $flash = 'A cron run is already in progress.'; $flashType = 'err'; }
        } elseif ($action === 'retry_queue') {
            $n = q("UPDATE " . tbl('job_queue') . " SET status='pending', attempts=0, claimed_by=NULL,
                    run_after=NOW(), updated_at=NOW() WHERE status='failed'")->rowCount();
            $flash = "$n failed message(s) re-queued.";
        } elseif ($action === 'clear_history') {
            q("DELETE FROM " . tbl('cron_runs'));
            $flash = 'Execution history cleared.';
        }
    } catch (Throwable $e) { $flash = 'Error: ' . $e->getMessage(); $flashType = 'err'; }
}

$health = $scheduler->health();
$jobs = $scheduler->jobsWithState();
$cronKey = setting('cron_key', '');
if ($cronKey === '') { $cronKey = rand_token(16); set_setting('cron_key', $cronKey); }
$cronCmd = '* * * * * php ' . KT_ROOT . '/cron/run.php >/dev/null 2>&1';
$cronUrl = SITE_URL . '/cron/run.php?key=' . $cronKey;

// Queue stats.
$q = ['pending' => 0, 'processing' => 0, 'sent' => 0, 'failed' => 0];
try { foreach (all("SELECT status, COUNT(*) c FROM " . tbl('job_queue') . " GROUP BY status") as $r) $q[$r['status']] = (int) $r['c']; } catch (Throwable $e) {}

$history = [];
try { $history = all("SELECT * FROM " . tbl('cron_runs') . " ORDER BY id DESC LIMIT 40"); } catch (Throwable $e) {}

function badge($status) {
    $map = ['success' => 'var(--morpankh-green)', 'running' => 'var(--peacock-teal)', 'failed' => '#DC2626',
            'locked' => 'var(--gold)', 'skipped' => '#9CA3AF'];
    $c = $map[$status] ?? '#9CA3AF';
    return '<span style="background:' . $c . '22;color:' . $c . ';padding:.1rem .5rem;border-radius:6px;font-size:.72rem;font-weight:600">' . e($status ?: '—') . '</span>';
}

admin_head('Cron / Scheduler');
?>
<div class="max-w-5xl">
  <h1 class="text-2xl font-bold mb-1 flex items-center gap-2"><i data-lucide="clock" class="w-6 h-6"></i> Cron / Scheduler</h1>
  <p class="opacity-70 text-sm mb-4">One master cron runs every minute and executes all background jobs. Configure exactly one server cron below.</p>

  <?php if ($flash): ?>
    <div class="p-3 rounded-lg mb-4 text-sm" style="background:<?= $flashType === 'ok' ? 'rgba(5,150,105,.12)' : 'rgba(220,38,38,.1)' ?>;color:<?= $flashType === 'ok' ? 'var(--morpankh-green)' : '#DC2626' ?>"><?= e($flash) ?></div>
  <?php endif; ?>

  <!-- Health + server cron command -->
  <div class="grid md:grid-cols-2 gap-4 mb-5">
    <div class="kt-card p-4">
      <div class="text-xs opacity-60">Scheduler Health</div>
      <?php
        $hc = $health['status'] === 'running' ? 'var(--morpankh-green)' : ($health['status'] === 'never' ? '#9CA3AF' : '#DC2626');
        $hlabel = $health['status'] === 'running' ? '✅ Running' : ($health['status'] === 'never' ? '⚠️ Never run yet' : '❌ Stalled');
      ?>
      <div class="text-xl font-extrabold mt-1" style="color:<?= $hc ?>"><?= $hlabel ?></div>
      <div class="text-sm mt-1">Last master run: <b><?= $health['last_run'] ? e($health['last_run']) : '—' ?></b>
        <?= $health['age_seconds'] !== null ? '<span class="opacity-60">(' . (int) $health['age_seconds'] . 's ago)</span>' : '' ?></div>
      <?php if ($health['failing_jobs'] > 0): ?><div class="text-sm mt-1" style="color:#DC2626"><?= $health['failing_jobs'] ?> job(s) failing</div><?php endif; ?>
      <form method="post" class="mt-3"><?= csrf_field() ?><input type="hidden" name="action" value="run_master">
        <button class="btn btn-primary text-sm"><i data-lucide="play" class="w-4 h-4"></i> Run master now</button></form>
    </div>
    <div class="kt-card p-4">
      <div class="text-xs opacity-60">Install ONE server cron (every minute)</div>
      <pre class="text-xs mt-1 p-2 rounded overflow-x-auto" style="background:rgba(0,0,0,.06);white-space:pre-wrap"><?= e($cronCmd) ?></pre>
      <div class="text-xs opacity-60 mt-2">No CLI cron? Use a URL cron instead:</div>
      <pre class="text-xs mt-1 p-2 rounded overflow-x-auto" style="background:rgba(0,0,0,.06);white-space:pre-wrap">* * * * * curl -s "<?= e($cronUrl) ?>"</pre>
    </div>
  </div>

  <!-- Message queue -->
  <div class="kt-card p-4 mb-5">
    <div class="flex items-center justify-between flex-wrap gap-2">
      <div class="font-bold">Message Queue</div>
      <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="retry_queue">
        <button class="btn btn-ghost text-sm" <?= $q['failed'] ? '' : 'disabled' ?>><i data-lucide="rotate-ccw" class="w-4 h-4"></i> Retry failed (<?= $q['failed'] ?>)</button></form>
    </div>
    <div class="grid grid-cols-4 gap-3 mt-3 text-center">
      <?php foreach (['pending' => 'var(--gold)', 'processing' => 'var(--peacock-teal)', 'sent' => 'var(--morpankh-green)', 'failed' => '#DC2626'] as $st => $c): ?>
        <div class="p-2 rounded-lg" style="background:<?= $c ?>15"><div class="text-xl font-bold" style="color:<?= $c ?>"><?= $q[$st] ?></div><div class="text-xs opacity-60"><?= ucfirst($st) ?></div></div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Jobs -->
  <h2 class="text-lg font-bold mb-2">Registered Jobs</h2>
  <div class="kt-card overflow-x-auto mb-6">
    <table class="w-full text-sm">
      <thead><tr class="opacity-60 text-left"><th class="p-2">Job</th><th class="p-2">Schedule</th><th class="p-2">Enabled</th><th class="p-2">Last run</th><th class="p-2">Status</th><th class="p-2">Runs/Fails</th><th class="p-2">Next</th><th class="p-2"></th></tr></thead>
      <tbody>
      <?php foreach ($jobs as $j): ?>
        <tr class="border-t" style="border-color:var(--border)">
          <td class="p-2"><div class="font-semibold"><?= e($j['name']) ?></div><div class="text-xs opacity-50 font-mono"><?= e($j['key']) ?> · <?= e($j['group']) ?></div></td>
          <td class="p-2 font-mono text-xs"><?= e($j['schedule']) ?></td>
          <td class="p-2">
            <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="job" value="<?= e($j['key']) ?>"><input type="hidden" name="enable" value="<?= $j['is_enabled'] ? '0' : '1' ?>">
              <button class="text-xs px-2 py-1 rounded-full" style="background:<?= $j['is_enabled'] ? 'rgba(5,150,105,.15);color:var(--morpankh-green)' : 'rgba(0,0,0,.08);color:#6B7280' ?>"><?= $j['is_enabled'] ? 'ON' : 'OFF' ?></button>
            </form>
          </td>
          <td class="p-2 text-xs"><?= $j['last_run'] ? e($j['last_run']) : '—' ?><?= !empty($j['last_duration_ms']) ? '<div class="opacity-50">' . (int) $j['last_duration_ms'] . 'ms</div>' : '' ?></td>
          <td class="p-2"><?= badge($j['last_status'] ?? '') ?><?php if (!empty($j['last_message'])): ?><div class="text-xs opacity-50 max-w-[180px] truncate" title="<?= e($j['last_message']) ?>"><?= e($j['last_message']) ?></div><?php endif; ?></td>
          <td class="p-2 text-xs"><?= (int) ($j['run_count'] ?? 0) ?> / <span style="color:<?= (int)($j['fail_count'] ?? 0) ? '#DC2626' : 'inherit' ?>"><?= (int) ($j['fail_count'] ?? 0) ?></span></td>
          <td class="p-2 text-xs opacity-60"><?= $j['schedule'] === 'manual' ? 'manual' : e($scheduler->nextRunEstimate($j, $j) ?? '—') ?></td>
          <td class="p-2">
            <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="run"><input type="hidden" name="job" value="<?= e($j['key']) ?>">
              <button class="btn btn-ghost !px-2 !py-1 text-xs" title="Run now"><i data-lucide="play" class="w-3.5 h-3.5"></i></button></form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- History -->
  <div class="flex items-center justify-between mb-2">
    <h2 class="text-lg font-bold">Execution History</h2>
    <form method="post" onsubmit="return confirm('Clear all execution history?')"><?= csrf_field() ?><input type="hidden" name="action" value="clear_history">
      <button class="btn btn-ghost text-xs">Clear</button></form>
  </div>
  <div class="kt-card overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="opacity-60 text-left"><th class="p-2">Job</th><th class="p-2">Status</th><th class="p-2">By</th><th class="p-2">Started</th><th class="p-2">Duration</th><th class="p-2">Message</th></tr></thead>
      <tbody>
      <?php if (!$history): ?><tr><td colspan="6" class="p-4 text-center opacity-60">No runs recorded yet.</td></tr><?php endif; ?>
      <?php foreach ($history as $r): ?>
        <tr class="border-t" style="border-color:var(--border)">
          <td class="p-2 font-mono text-xs"><?= e($r['job_key']) ?></td>
          <td class="p-2"><?= badge($r['status']) ?></td>
          <td class="p-2 text-xs opacity-60"><?= e($r['trigger_by']) ?></td>
          <td class="p-2 text-xs"><?= e($r['started_at']) ?></td>
          <td class="p-2 text-xs"><?= (int) $r['duration_ms'] ?>ms</td>
          <td class="p-2 text-xs opacity-70 max-w-[260px] truncate" title="<?= e($r['message']) ?>"><?= e($r['message']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php admin_foot();
