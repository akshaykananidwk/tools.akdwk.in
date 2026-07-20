<?php
/**
 * KRISHNA TOOLS — tools manager. Grouped by category (collapsible), search,
 * inline edit of name_gu / sort_order and toggles for active / premium.
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_admin();
require_once __DIR__ . '/layout.php';

$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_check();
    $tid = (int) ($_POST['tool_id'] ?? 0);
    try {
        if ($tid && one("SELECT id FROM " . tbl('tools') . " WHERE id = :id", [':id' => $tid])) {
            update('tools', [
                'name_gu'    => trim($_POST['name_gu'] ?? ''),
                'sort_order' => (int) ($_POST['sort_order'] ?? 0),
                'is_active'  => isset($_POST['is_active']) ? 1 : 0,
                'is_premium' => isset($_POST['is_premium']) ? 1 : 0,
            ], ['id' => $tid]);
            activity_log((int) current_user()['id'], 'admin_tool_update', 'tool #' . $tid);
            $flash = 'ટૂલ સેવ થયું.';
        } else {
            $flash = 'ટૂલ મળ્યું નહીં.';
        }
    } catch (Throwable $e) {
        kt_error_log('admin tools: ' . $e->getMessage());
        $flash = 'સેવ કરવામાં ભૂલ આવી.';
    }
}

$search = trim($_GET['q'] ?? '');

$cats = []; $tools = [];
try {
    $cats = all("SELECT * FROM " . tbl('categories') . " ORDER BY sort_order ASC, id ASC");
    if ($search !== '') {
        $tools = all("SELECT * FROM " . tbl('tools') . "
                      WHERE name_gu LIKE :s OR name_en LIKE :s OR slug LIKE :s
                      ORDER BY category_id ASC, sort_order ASC", [':s' => '%' . $search . '%']);
    } else {
        $tools = all("SELECT * FROM " . tbl('tools') . " ORDER BY category_id ASC, sort_order ASC, id ASC");
    }
} catch (Throwable $e) {
    kt_error_log('admin tools list: ' . $e->getMessage());
}

// Group tools by category id.
$byCat = [];
foreach ($tools as $t) { $byCat[(int) $t['category_id']][] = $t; }
$catName = [];
foreach ($cats as $c) { $catName[(int) $c['id']] = loc($c, 'name'); }

/** Render one editable tool row form. */
function admin_tool_row(array $t): void {
    ?>
    <form method="post" class="flex flex-wrap items-center gap-2 py-2 border-b" style="border-color:var(--border)">
      <?= csrf_field() ?>
      <input type="hidden" name="tool_id" value="<?= (int) $t['id'] ?>">
      <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background:rgba(14,116,144,.12);color:var(--peacock-teal)"><i data-lucide="<?= e($t['icon'] ?: 'wrench') ?>" class="w-4 h-4"></i></span>
      <input name="name_gu" value="<?= e($t['name_gu']) ?>" class="kt-input !py-1.5 flex-1 min-w-[160px] text-sm">
      <span class="text-xs opacity-50 hidden md:inline w-40 truncate"><?= e($t['slug']) ?></span>
      <label class="text-xs flex items-center gap-1">ક્રમ <input name="sort_order" type="number" value="<?= (int) $t['sort_order'] ?>" class="kt-input !py-1 w-16 text-sm"></label>
      <label class="text-xs flex items-center gap-1"><input type="checkbox" name="is_active" <?= (int) $t['is_active'] === 1 ? 'checked' : '' ?>> એક્ટિવ</label>
      <label class="text-xs flex items-center gap-1"><input type="checkbox" name="is_premium" <?= (int) $t['is_premium'] === 1 ? 'checked' : '' ?>> પ્રીમિયમ</label>
      <button class="btn btn-primary !py-1 !px-3 text-sm"><i data-lucide="save" class="w-3.5 h-3.5"></i></button>
    </form>
    <?php
}

admin_head('ટૂલ્સ');
?>
<?php if ($flash): ?>
  <div class="kt-card p-3 mb-4 text-sm" style="border-color:var(--gold)"><?= e($flash) ?></div>
<?php endif; ?>

<div class="kt-card p-4 mb-4">
  <form method="get" class="flex flex-wrap gap-2 items-center">
    <div class="relative flex-1 min-w-[220px]">
      <input type="text" name="q" value="<?= e($search) ?>" placeholder="ટૂલ શોધો (નામ કે slug)" class="kt-input pl-9">
      <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 opacity-60"></i>
    </div>
    <button class="btn btn-primary">શોધો</button>
    <?php if ($search !== ''): ?><a href="tools.php" class="btn btn-ghost">રિસેટ</a><?php endif; ?>
    <span class="opacity-60 text-sm ms-auto">કુલ ટૂલ્સ: <?= number_format(count($tools)) ?></span>
  </form>
</div>

<?php if (!$tools): ?>
  <div class="kt-card p-6 text-center opacity-60">કોઈ ટૂલ મળ્યું નહીં.</div>
<?php elseif ($search !== ''): ?>
  <div class="kt-card p-4">
    <?php foreach ($tools as $t) admin_tool_row($t); ?>
  </div>
<?php else: ?>
  <?php foreach ($byCat as $cid => $list): ?>
    <details class="kt-card p-4 mb-3" open>
      <summary class="cursor-pointer font-bold flex items-center justify-between list-none">
        <span class="section-title text-base"><?= e($catName[$cid] ?? 'અન્ય') ?></span>
        <span class="badge-premium text-xs"><?= count($list) ?></span>
      </summary>
      <div class="mt-3">
        <?php foreach ($list as $t) admin_tool_row($t); ?>
      </div>
    </details>
  <?php endforeach; ?>
<?php endif; ?>
<?php admin_foot(); ?>
