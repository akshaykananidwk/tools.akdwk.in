<?php
/**
 * KRISHNA TOOLS — activity log viewer + error.log tail with a clear button.
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_admin();
require_once __DIR__ . '/layout.php';

$errFile = defined('KT_LOGS') ? KT_LOGS . '/error.log' : '';
$flash = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_check();
    if (($_POST['action'] ?? '') === 'clear_errors' && $errFile && is_file($errFile)) {
        if (@file_put_contents($errFile, '') !== false) {
            activity_log((int) current_user()['id'], 'admin_clear_error_log', '');
            $flash = 'Error log cleared.';
        } else {
            $flash = 'Could not clear the log (check permissions).';
        }
    }
}

$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 40;
$offset  = ($page - 1) * $perPage;

$acts = []; $actTotal = 0;
try {
    $actTotal = (int) scalar("SELECT COUNT(*) FROM " . tbl('activity_logs'));
    $acts = all("SELECT a.*, u.name AS uname FROM " . tbl('activity_logs') . " a
                 LEFT JOIN " . tbl('users') . " u ON u.id = a.user_id
                 ORDER BY a.id DESC LIMIT $perPage OFFSET $offset");
} catch (Throwable $e) {
    kt_error_log('admin logs list: ' . $e->getMessage());
}

// Tail last ~200 lines of the error log.
$errLines = [];
if ($errFile && is_file($errFile)) {
    try {
        $all = @file($errFile, FILE_IGNORE_NEW_LINES) ?: [];
        $errLines = array_slice($all, -200);
    } catch (Throwable $e) { $errLines = []; }
}

admin_head('Logs');
?>
<?php if ($flash): ?>
  <div class="kt-card p-3 mb-4 text-sm" style="border-color:var(--gold)"><?= e($flash) ?></div>
<?php endif; ?>

<div class="kt-card p-5 mb-5">
  <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
    <h2 class="section-title text-base">Error Log (last 200 lines)</h2>
    <?php if ($errLines): ?>
      <form method="post" onsubmit="return confirm('Clear the error log?');">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="clear_errors">
        <button class="btn btn-ghost text-sm" style="color:#DC2626"><i data-lucide="trash-2" class="w-4 h-4"></i> Clear</button>
      </form>
    <?php endif; ?>
  </div>
  <?php if ($errLines): ?>
    <pre class="text-xs p-3 rounded-lg overflow-auto" style="background:rgba(0,0,0,.06);max-height:400px;white-space:pre-wrap"><?php foreach ($errLines as $line) echo e($line) . "\n"; ?></pre>
  <?php else: ?>
    <p class="opacity-60 text-sm">Error log is empty or not found.</p>
  <?php endif; ?>
</div>

<div class="kt-card p-5">
  <h2 class="section-title text-base mb-3">Activity Log (total <?= number_format($actTotal) ?>)</h2>
  <div class="overflow-x-auto">
    <table class="kt-table">
      <thead><tr><th>#</th><th>User</th><th>Action</th><th>Details</th><th>IP</th><th>Time</th></tr></thead>
      <tbody>
      <?php if (!$acts): ?>
        <tr><td colspan="6" class="text-center opacity-60 py-6">No logs.</td></tr>
      <?php else: foreach ($acts as $a): ?>
        <tr>
          <td class="opacity-60"><?= (int) $a['id'] ?></td>
          <td><?= e($a['uname'] ?? 'Guest') ?></td>
          <td><span class="badge-premium text-xs"><?= e($a['action'] ?? '') ?></span></td>
          <td class="opacity-70 text-xs max-w-[320px] truncate"><?= e($a['details'] ?? '') ?></td>
          <td class="opacity-60 text-xs"><?= e($a['ip'] ?? '') ?></td>
          <td class="opacity-60 text-xs whitespace-nowrap"><?= e($a['created_at'] ?? '') ?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <?= admin_pager($page, $actTotal, $perPage) ?>
</div>
<?php admin_foot(); ?>
