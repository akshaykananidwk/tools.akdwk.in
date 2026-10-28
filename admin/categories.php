<?php
/**
 * KRISHNA TOOLS — category editor (name_gu/name_en/sort_order/color).
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_admin();
require_once __DIR__ . '/layout.php';

$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_check();
    $cid = (int) ($_POST['category_id'] ?? 0);
    try {
        if ($cid && one("SELECT id FROM " . tbl('categories') . " WHERE id = :id", [':id' => $cid])) {
            $color = trim($_POST['color'] ?? '');
            if ($color !== '' && !preg_match('/^#[0-9A-Fa-f]{3,8}$/', $color)) $color = '';
            update('categories', [
                'name_gu'    => trim($_POST['name_gu'] ?? ''),
                'name_en'    => trim($_POST['name_en'] ?? ''),
                'sort_order' => (int) ($_POST['sort_order'] ?? 0),
                'color'      => $color,
            ], ['id' => $cid]);
            activity_log((int) current_user()['id'], 'admin_category_update', 'category #' . $cid);
            $flash = 'Category saved.';
        } else {
            $flash = 'Category not found.';
        }
    } catch (Throwable $e) {
        kt_error_log('admin categories: ' . $e->getMessage());
        $flash = 'Could not save. Please try again.';
    }
}

$cats = [];
try { $cats = all("SELECT * FROM " . tbl('categories') . " ORDER BY sort_order ASC, id ASC"); }
catch (Throwable $e) { kt_error_log('admin categories list: ' . $e->getMessage()); }

admin_head('Categories');
?>
<?php if ($flash): ?>
  <div class="kt-card p-3 mb-4 text-sm" style="border-color:var(--gold)"><?= e($flash) ?></div>
<?php endif; ?>

<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
<?php if (!$cats): ?>
  <div class="kt-card p-6 text-center opacity-60">No categories yet.</div>
<?php else: foreach ($cats as $c): ?>
  <form method="post" class="kt-card p-5 space-y-3">
    <?= csrf_field() ?>
    <input type="hidden" name="category_id" value="<?= (int) $c['id'] ?>">
    <div class="flex items-center gap-2">
      <span class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:<?= e($c['color'] ?: '#1E3A8A') ?>1a;color:<?= e($c['color'] ?: '#1E3A8A') ?>">
        <i data-lucide="<?= e($c['icon'] ?: 'folder') ?>" class="w-5 h-5"></i>
      </span>
      <h2 class="section-title text-base"><?= e(loc($c, 'name')) ?></h2>
      <span class="text-xs opacity-60 ms-auto">#<?= (int) $c['id'] ?></span>
    </div>
    <input type="hidden" name="name_gu" value="<?= e($c['name_gu']) ?>">
    <div><label class="kt-label text-xs">Name (EN)</label><input name="name_en" class="kt-input !py-1.5" value="<?= e($c['name_en']) ?>"></div>
    <div class="grid grid-cols-2 gap-2">
      <div><label class="kt-label text-xs">Order</label><input name="sort_order" type="number" class="kt-input !py-1.5" value="<?= (int) $c['sort_order'] ?>"></div>
      <div><label class="kt-label text-xs">Color (hex)</label><input name="color" class="kt-input !py-1.5" value="<?= e($c['color'] ?? '') ?>" placeholder="#1E3A8A"></div>
    </div>
    <button class="btn btn-primary w-full justify-center"><i data-lucide="save" class="w-4 h-4"></i> Save</button>
  </form>
<?php endforeach; endif; ?>
</div>
<?php admin_foot(); ?>
