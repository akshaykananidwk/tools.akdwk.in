<?php
/** KRISHNA TOOLS — category listing page (/category/<slug>). */
require_once __DIR__ . '/includes/bootstrap.php';

$slug = preg_replace('/[^a-z0-9-]/', '', $_GET['slug'] ?? '');
$cat = null;
foreach (kt_categories() as $c) if ($c['slug'] === $slug) $cat = $c;
if (!$cat) { header('Location: ' . SITE_URL . '/'); exit; }

$tools = kt_tools_by_cat($slug);
$dbTools = [];
try { foreach (all("SELECT slug, is_active, is_premium FROM " . tbl('tools')) as $r) $dbTools[$r['slug']] = $r; } catch (Throwable $e) {}

$page_title = loc($cat, 'name') . ' — ' . setting('site_name', 'કૃષ્ણા ટૂલ્સ');
$page_desc  = loc($cat, 'name') . ' · ' . count($tools) . ' ' . t('all_tools');
$page_canonical = SITE_URL . '/category/' . $slug;
$breadcrumb = [['label' => t('home'), 'url' => SITE_URL . '/'], ['label' => loc($cat, 'name')]];
require __DIR__ . '/includes/header.php';
?>
<div class="flex items-center gap-3 my-6">
  <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white" style="background:<?= e($cat['color']) ?>">
    <i data-lucide="<?= e($cat['icon']) ?>" class="w-6 h-6"></i></div>
  <div><h1 class="section-title text-2xl"><?= e(loc($cat, 'name')) ?></h1>
    <div class="text-sm opacity-60"><?= count($tools) ?> <?= t('all_tools') ?></div></div>
</div>
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
  <?php foreach ($tools as $t):
    $db = $dbTools[$t['slug']] ?? null;
    if ($db && (int) $db['is_active'] === 0) continue;
    $prem = $t['premium'] || ($db && (int) $db['is_premium'] === 1); ?>
    <a href="<?= SITE_URL ?>/tool/<?= e($t['slug']) ?>" class="kt-card tool-card p-4 block">
      <div class="flex items-start justify-between">
        <div class="icon-wrap w-10 h-10 rounded-xl flex items-center justify-center"><i data-lucide="<?= e($t['icon']) ?>" class="w-5 h-5"></i></div>
        <?php if ($prem): ?><span class="badge-premium">PRO</span><?php endif; ?>
      </div>
      <div class="font-semibold mt-3 leading-tight"><?= e(loc($t, 'name')) ?></div>
      <div class="text-xs opacity-60 mt-1 line-clamp-2"><?= e(loc($t, 'desc')) ?></div>
    </a>
  <?php endforeach; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
