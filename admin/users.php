<?php
/**
 * KRISHNA TOOLS — user management: search, paginate, block, plan grant, role.
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_admin();
require_once __DIR__ . '/layout.php';

$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_check();
    $action = $_POST['action'] ?? '';
    $uid    = (int) ($_POST['user_id'] ?? 0);
    $me     = current_user();
    try {
        $target = $uid ? one("SELECT * FROM " . tbl('users') . " WHERE id = :id", [':id' => $uid]) : null;
        if (!$target) {
            $flash = 'User not found.';
        } elseif ($action === 'block' || $action === 'unblock') {
            if ($target['id'] == ($me['id'] ?? 0)) {
                $flash = 'You cannot block your own account.';
            } else {
                $status = $action === 'block' ? 'blocked' : 'active';
                update('users', ['status' => $status], ['id' => $uid]);
                activity_log((int) $me['id'], 'admin_user_' . $action, 'user #' . $uid);
                $flash = 'Status updated.';
            }
        } elseif ($action === 'role') {
            if ($target['id'] == ($me['id'] ?? 0)) {
                $flash = 'You cannot change your own role.';
            } else {
                $role = ($_POST['role'] ?? 'user') === 'admin' ? 'admin' : 'user';
                update('users', ['role' => $role], ['id' => $uid]);
                activity_log((int) $me['id'], 'admin_user_role', 'user #' . $uid . ' -> ' . $role);
                $flash = 'Role updated.';
            }
        } elseif ($action === 'plan') {
            $planId = (int) ($_POST['plan_id'] ?? 0);
            $plan = $planId ? one("SELECT * FROM " . tbl('plans') . " WHERE id = :id", [':id' => $planId]) : null;
            if (!$plan) {
                $flash = 'Plan not found.';
            } else {
                $days = (int) $plan['duration_days'];
                // Extend from the later of now / current expiry.
                $base = ($target['plan_expiry'] && strtotime($target['plan_expiry']) > time())
                        ? strtotime($target['plan_expiry']) : time();
                $expiry = $days > 0 ? date('Y-m-d H:i:s', $base + $days * 86400) : null;
                update('users', ['plan_id' => $planId, 'plan_expiry' => $expiry], ['id' => $uid]);
                activity_log((int) $me['id'], 'admin_user_plan', 'user #' . $uid . ' -> plan ' . $planId);
                $flash = 'Plan applied.';
            }
        }
    } catch (Throwable $e) {
        kt_error_log('admin users: ' . $e->getMessage());
        $flash = 'Something went wrong. Please try again.';
    }
}

$search  = trim($_GET['q'] ?? '');
$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 25;
$offset  = ($page - 1) * $perPage;

$where = '';
$params = [];
if ($search !== '') {
    $where = " WHERE u.name LIKE :s OR u.email LIKE :s OR u.phone LIKE :s";
    $params[':s'] = '%' . $search . '%';
}

$total = 0; $users = []; $plans = [];
try {
    $total = (int) scalar("SELECT COUNT(*) FROM " . tbl('users') . " u" . $where, $params);
    $users = all("SELECT u.*, p.name_gu AS plan_gu, p.name_en AS plan_en, p.price AS plan_price
                  FROM " . tbl('users') . " u
                  LEFT JOIN " . tbl('plans') . " p ON p.id = u.plan_id" . $where . "
                  ORDER BY u.id DESC LIMIT $perPage OFFSET $offset", $params);
    $plans = all("SELECT * FROM " . tbl('plans') . " ORDER BY price ASC");
} catch (Throwable $e) {
    kt_error_log('admin users list: ' . $e->getMessage());
}

admin_head('Users');
?>
<?php if ($flash): ?>
  <div class="kt-card p-3 mb-4 text-sm" style="border-color:var(--gold)"><?= e($flash) ?></div>
<?php endif; ?>

<div class="kt-card p-4 mb-4">
  <form method="get" class="flex flex-wrap gap-2 items-center">
    <div class="relative flex-1 min-w-[220px]">
      <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search by name, email or phone" class="kt-input pl-9">
      <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 opacity-60"></i>
    </div>
    <button class="btn btn-primary">Search</button>
    <?php if ($search !== ''): ?><a href="users.php" class="btn btn-ghost">Reset</a><?php endif; ?>
    <span class="opacity-60 text-sm ms-auto">Total: <?= number_format($total) ?></span>
  </form>
</div>

<div class="kt-card p-0 overflow-x-auto">
  <table class="kt-table">
    <thead>
      <tr><th>#</th><th>Name / Email</th><th>Phone</th><th>Plan</th><th>Days left</th><th>Role</th><th>Status</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php if (!$users): ?>
      <tr><td colspan="8" class="text-center opacity-60 py-6">No users found.</td></tr>
    <?php else: foreach ($users as $u):
        $dl = days_left($u);
        $planName = $u['plan_id'] ? loc(['name_gu' => $u['plan_gu'], 'name_en' => $u['plan_en']], 'name') : 'Free';
    ?>
      <tr>
        <td class="opacity-60"><?= (int) $u['id'] ?></td>
        <td>
          <div class="font-semibold"><?= e($u['name']) ?></div>
          <div class="text-xs opacity-60"><?= e($u['email']) ?></div>
        </td>
        <td class="text-xs"><?= e($u['phone'] ?? '—') ?></td>
        <td><span class="badge-premium text-xs"><?= e($planName) ?></span></td>
        <td><?= $dl > 0 ? $dl . ($dl === 1 ? ' day' : ' days') : '<span class="opacity-50">—</span>' ?></td>
        <td>
          <span class="text-xs font-semibold <?= $u['role'] === 'admin' ? 'text-[color:var(--saffron)]' : 'opacity-70' ?>"><?= e($u['role']) ?></span>
        </td>
        <td>
          <?php $blocked = $u['status'] === 'blocked'; ?>
          <span class="text-xs font-semibold" style="color:<?= $blocked ? '#DC2626' : 'var(--morpankh-green)' ?>"><?= e($u['status']) ?></span>
        </td>
        <td>
          <details class="relative">
            <summary class="btn btn-ghost !py-1 !px-2 cursor-pointer list-none"><i data-lucide="more-horizontal" class="w-4 h-4"></i></summary>
            <div class="kt-card p-3 mt-1 absolute end-0 z-30 w-64 space-y-2 shadow-lg">
              <form method="post" class="flex gap-2">
                <?= csrf_field() ?>
                <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                <input type="hidden" name="action" value="<?= $blocked ? 'unblock' : 'block' ?>">
                <button class="btn <?= $blocked ? 'btn-primary' : 'btn-ghost' ?> !py-1 w-full justify-center text-sm"><?= $blocked ? 'Unblock' : 'Block' ?></button>
              </form>
              <form method="post" class="flex gap-2">
                <?= csrf_field() ?>
                <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                <input type="hidden" name="action" value="role">
                <input type="hidden" name="role" value="<?= $u['role'] === 'admin' ? 'user' : 'admin' ?>">
                <button class="btn btn-ghost !py-1 w-full justify-center text-sm"><?= $u['role'] === 'admin' ? 'Make user' : 'Make admin' ?></button>
              </form>
              <form method="post" class="space-y-2 border-t pt-2" style="border-color:var(--border)">
                <?= csrf_field() ?>
                <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                <input type="hidden" name="action" value="plan">
                <label class="kt-label !mb-1 text-xs">Give / extend plan</label>
                <select name="plan_id" class="kt-select !py-1 text-sm">
                  <?php foreach ($plans as $p): ?>
                    <option value="<?= (int) $p['id'] ?>"><?= e(loc($p, 'name')) ?> (<?= (int) $p['duration_days'] ?>d)</option>
                  <?php endforeach; ?>
                </select>
                <button class="btn btn-gold !py-1 w-full justify-center text-sm">Apply</button>
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
