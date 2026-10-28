<?php
/**
 * KRISHNA TOOLS — plan editor (price, duration, limits, features, active).
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_admin();
require_once __DIR__ . '/layout.php';

$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_check();
    $pid = (int) ($_POST['plan_id'] ?? 0);
    try {
        if ($pid && one("SELECT id FROM " . tbl('plans') . " WHERE id = :id", [':id' => $pid])) {
            update('plans', [
                'name_gu'       => trim($_POST['name_gu'] ?? ''),
                'name_en'       => trim($_POST['name_en'] ?? ''),
                'price'         => (float) ($_POST['price'] ?? 0),
                'duration_days' => (int) ($_POST['duration_days'] ?? 0),
                'daily_limit'   => (int) ($_POST['daily_limit'] ?? 0),
                'max_file_mb'   => (int) ($_POST['max_file_mb'] ?? 5),
                'features'      => trim($_POST['features'] ?? ''),
                'is_active'     => isset($_POST['is_active']) ? 1 : 0,
            ], ['id' => $pid]);
            activity_log((int) current_user()['id'], 'admin_plan_update', 'plan #' . $pid);
            $flash = 'Plan saved.';
        } else {
            $flash = 'Plan not found.';
        }
    } catch (Throwable $e) {
        kt_error_log('admin plans: ' . $e->getMessage());
        $flash = 'Could not save. Please try again.';
    }
}

$plans = [];
try { $plans = all("SELECT * FROM " . tbl('plans') . " ORDER BY price ASC, id ASC"); }
catch (Throwable $e) { kt_error_log('admin plans list: ' . $e->getMessage()); }

admin_head('Plans');
?>
<?php if ($flash): ?>
  <div class="kt-card p-3 mb-4 text-sm" style="border-color:var(--gold)"><?= e($flash) ?></div>
<?php endif; ?>

<p class="text-sm opacity-70 mb-4">Separate features with a pipe (<code>|</code>). <code>daily_limit = 0</code> means unlimited.</p>

<div class="grid gap-4 lg:grid-cols-3">
<?php if (!$plans): ?>
  <div class="kt-card p-6 text-center opacity-60">No plans yet.</div>
<?php else: foreach ($plans as $p): ?>
  <form method="post" class="kt-card p-5 space-y-3">
    <?= csrf_field() ?>
    <input type="hidden" name="plan_id" value="<?= (int) $p['id'] ?>">
    <div class="flex items-center justify-between">
      <h2 class="section-title text-base"><?= e(loc($p, 'name')) ?></h2>
      <span class="text-xs opacity-60">#<?= (int) $p['id'] ?></span>
    </div>
    <div class="grid grid-cols-2 gap-2">
      <input type="hidden" name="name_gu" value="<?= e($p['name_gu']) ?>">
      <div><label class="kt-label text-xs">Name (EN)</label><input name="name_en" class="kt-input !py-1.5" value="<?= e($p['name_en']) ?>"></div>
      <div><label class="kt-label text-xs">Price (₹)</label><input name="price" type="number" step="0.01" class="kt-input !py-1.5" value="<?= e((string) $p['price']) ?>"></div>
      <div><label class="kt-label text-xs">Days</label><input name="duration_days" type="number" class="kt-input !py-1.5" value="<?= (int) $p['duration_days'] ?>"></div>
      <div><label class="kt-label text-xs">Daily limit</label><input name="daily_limit" type="number" class="kt-input !py-1.5" value="<?= (int) $p['daily_limit'] ?>"></div>
      <div><label class="kt-label text-xs">Max MB</label><input name="max_file_mb" type="number" class="kt-input !py-1.5" value="<?= (int) $p['max_file_mb'] ?>"></div>
    </div>
    <div>
      <label class="kt-label text-xs">Features (|)</label>
      <textarea name="features" rows="4" class="kt-input !py-1.5 text-sm"><?= e($p['features'] ?? '') ?></textarea>
    </div>
    <label class="flex items-center gap-2 text-sm">
      <input type="checkbox" name="is_active" <?= (int) $p['is_active'] === 1 ? 'checked' : '' ?>> Active
    </label>
    <button class="btn btn-primary w-full justify-center"><i data-lucide="save" class="w-4 h-4"></i> Save</button>
  </form>
<?php endforeach; endif; ?>
</div>
<?php admin_foot(); ?>
