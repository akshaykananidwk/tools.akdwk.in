<?php
/**
 * KRISHNA TOOLS — Admin → GitHub Update.
 * Set repo/branch/token once, then "Check for Update" → "Update Now".
 * Code comes straight from GitHub to the server and the database updates
 * automatically. config.php and uploads/ are never touched.
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_admin();
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/../includes/updater.php';

$flash = ''; $flashType = 'ok';
$check = null; $updateResult = null;
$updater = new GitHubUpdater();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        set_setting('gh_repo', trim($_POST['gh_repo'] ?? ''));
        set_setting('gh_branch', trim($_POST['gh_branch'] ?? 'main') ?: 'main');
        // Keep the existing token if the field is left blank.
        $tok = trim($_POST['gh_token'] ?? '');
        if ($tok !== '') set_setting('gh_token', $tok);
        $flash = 'Repository settings saved.';
        $updater = new GitHubUpdater();
    } elseif ($action === 'check') {
        $check = $updater->check();
        if (!$check['ok']) { $flash = $check['error']; $flashType = 'err'; }
    } elseif ($action === 'update') {
        $updateResult = $updater->update(current_user()['name'] ?? 'Admin');
        if ($updateResult['ok']) { $flash = 'Updated to gh:' . $updateResult['version'] . ' — ' . $updateResult['files'] . ' files.'; }
        else { $flash = $updateResult['error']; $flashType = 'err'; }
        $updater = new GitHubUpdater();
    }
}

$repo = setting('gh_repo', '');
$branch = setting('gh_branch', 'main');
$hasToken = setting('gh_token', '') !== '';
$current = $updater->currentVersion();
$history = $updater->history();

admin_head('GitHub Update');
?>
<div class="max-w-3xl">
  <div class="flex items-center gap-2 mb-1">
    <i data-lucide="github" class="w-6 h-6"></i>
    <h1 class="text-2xl font-bold">GitHub Update</h1>
    <?php if ($current): ?><span class="px-2 py-0.5 rounded-full text-xs font-mono" style="background:rgba(30,58,138,.1);color:var(--krishna-blue)">v<?= e(substr($current, 0, 7)) ?></span><?php endif; ?>
  </div>
  <p class="opacity-70 text-sm mb-4">Set the repo/branch once, then just <b>Check for Update</b> → <b>Update Now</b> — code comes straight from GitHub to the server, and the database updates automatically too. <code>config.php</code> (database password etc.) and <code>uploads/</code> (bills, photos) are never touched.</p>

  <?php if ($flash): ?>
    <div class="p-3 rounded-lg mb-4 text-sm" style="background:<?= $flashType === 'ok' ? 'rgba(5,150,105,.12)' : 'rgba(220,38,38,.1)' ?>;color:<?= $flashType === 'ok' ? 'var(--morpankh-green)' : '#DC2626' ?>"><?= e($flash) ?></div>
  <?php endif; ?>

  <!-- Repo settings -->
  <form method="post" class="kt-card p-5 space-y-4">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <div>
      <label class="kt-label">GitHub repo (owner/repo)</label>
      <input class="kt-input" name="gh_repo" value="<?= e($repo) ?>" placeholder="akshaykananidwk/tools.akdwk.in">
    </div>
    <div>
      <label class="kt-label">Branch</label>
      <input class="kt-input" name="gh_branch" value="<?= e($branch) ?>" placeholder="main">
    </div>
    <div>
      <label class="kt-label">GitHub Token <span class="opacity-60">(required for a private repo, leave blank to keep the existing one)</span></label>
      <input class="kt-input" name="gh_token" placeholder="<?= $hasToken ? '•••••••• (saved)' : 'ghp_xxxxxxxxxxxx' ?>" autocomplete="off">
    </div>
    <button class="btn btn-ghost"><i data-lucide="save" class="w-4 h-4"></i> Save Repo Settings</button>
  </form>

  <!-- Actions -->
  <div class="flex flex-wrap gap-2 mt-4">
    <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="check">
      <button class="btn btn-primary"><i data-lucide="search" class="w-4 h-4"></i> Check for Update</button></form>
    <?php if ($check && $check['ok'] && $check['update_available']): ?>
      <form method="post" onsubmit="return confirm('Pull the latest code from GitHub and update the database now? config.php and uploads/ will be preserved.');">
        <?= csrf_field() ?><input type="hidden" name="action" value="update">
        <button class="btn btn-cta"><i data-lucide="download" class="w-4 h-4"></i> Update Now</button></form>
    <?php endif; ?>
  </div>

  <!-- Check result -->
  <?php if ($check && $check['ok']): ?>
    <div class="kt-card p-4 mt-4">
      <?php if ($check['update_available']): ?>
        <div class="font-bold" style="color:var(--saffron)">🔔 Update available</div>
        <div class="text-sm mt-1">Latest: <b class="font-mono"><?= e($check['short']) ?></b> · <?= e($check['date']) ?></div>
        <?php if ($check['message']): ?><div class="text-sm opacity-70 mt-1"><?= e($check['message']) ?></div><?php endif; ?>
        <div class="text-xs opacity-60 mt-1">Installed: <?= $current ? e(substr($current, 0, 7)) : 'unknown' ?></div>
      <?php else: ?>
        <div class="font-bold" style="color:var(--morpankh-green)">✅ You are up to date (<?= e($check['short']) ?>)</div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- Update run log -->
  <?php if ($updateResult && $updateResult['ok']): ?>
    <div class="kt-card p-4 mt-4">
      <div class="font-bold mb-2" style="color:var(--morpankh-green)">🦚 Update complete</div>
      <ul class="text-sm space-y-1 font-mono">
        <?php foreach ($updateResult['log'] as $line): ?><li>✓ <?= e($line) ?></li><?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <!-- History -->
  <h2 class="text-lg font-bold mt-8 mb-3">Update history</h2>
  <?php if ($history): ?>
    <div class="kt-card overflow-hidden">
      <table class="w-full text-sm">
        <thead><tr class="opacity-60 text-left"><th class="p-3">Version</th><th class="p-3">Date</th><th class="p-3">Files</th><th class="p-3">By</th></tr></thead>
        <tbody>
        <?php foreach ($history as $h): ?>
          <tr class="border-t" style="border-color:var(--border)">
            <td class="p-3 font-mono font-semibold"><?= e($h['version']) ?></td>
            <td class="p-3"><?= e($h['date']) ?></td>
            <td class="p-3"><?= (int) $h['files'] ?> files</td>
            <td class="p-3"><?= e($h['by']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p class="opacity-60 text-sm">No updates applied yet. Configure the repo above and click Check for Update.</p>
  <?php endif; ?>

  <div class="text-xs opacity-60 mt-6">
    ⚠️ This pulls and runs code from the repository you configure. Only use repositories you control. The GitHub token is stored securely and only admins can access this page.
  </div>
</div>
<?php admin_foot();