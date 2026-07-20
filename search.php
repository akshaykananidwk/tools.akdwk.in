<?php
/** KRISHNA TOOLS — tool search results page (also the Sitelinks SearchAction target). */
require_once __DIR__ . '/includes/bootstrap.php';

$q = trim($_GET['q'] ?? '');
$results = [];
if ($q !== '') {
    $needle = mb_strtolower($q);
    foreach (kt_tools() as $t) {
        $hay = mb_strtolower($t['name_en'] . ' ' . $t['name_gu'] . ' ' . $t['slug'] . ' ' . $t['desc_en']);
        if (str_contains($hay, $needle)) $results[] = $t;
    }
}

$en = current_lang() === 'en';
$page_title = ($q !== '' ? ($en ? "Search: $q" : "શોધ: $q") : ($en ? 'Search Tools' : 'ટૂલ્સ શોધો')) . ' — ' . setting('site_name', 'Krishna Tools');
$page_desc = $en ? "Search results for \"$q\" across 120+ online tools at Krishna Tools." : "\"$q\" માટે 120+ ટૂલ્સમાં શોધ પરિણામ.";
$page_robots = 'noindex, follow'; // search result pages should not be indexed
$breadcrumb = [['label' => t('home'), 'url' => SITE_URL . '/'], ['label' => $en ? 'Search' : 'શોધ']];
require __DIR__ . '/includes/header.php';
?>
<div class="my-6">
  <h1 class="section-title text-2xl mb-3"><?= $en ? 'Search Tools' : 'ટૂલ્સ શોધો' ?></h1>
  <form method="get" class="relative max-w-xl">
    <input name="q" value="<?= e($q) ?>" class="kt-input !py-3 pl-11 text-lg" placeholder="<?= t('search_tools') ?>" autofocus>
    <i data-lucide="search" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 opacity-60"></i>
  </form>
</div>

<?php if ($q !== ''): ?>
  <p class="opacity-70 mb-4"><?= count($results) ?> <?= $en ? 'results for' : 'પરિણામ' ?> "<b><?= e($q) ?></b>"</p>
  <?php if ($results): ?>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
      <?php foreach ($results as $t): ?>
        <a href="<?= SITE_URL ?>/tool/<?= e($t['slug']) ?>" class="kt-card tool-card p-4 block">
          <div class="icon-wrap w-10 h-10 rounded-xl flex items-center justify-center"><i data-lucide="<?= e($t['icon']) ?>" class="w-5 h-5"></i></div>
          <div class="font-semibold mt-3 leading-tight"><?= e(loc($t, 'name')) ?></div>
          <div class="text-xs opacity-60 mt-1 line-clamp-2"><?= e(loc($t, 'desc')) ?></div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="kt-card p-8 text-center"><div class="text-4xl mb-2">🦚</div>
      <p class="opacity-70"><?= $en ? 'No tools found. Try a different keyword.' : 'કોઈ ટૂલ મળ્યું નહીં. બીજો શબ્દ અજમાવો.' ?></p></div>
  <?php endif; ?>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
