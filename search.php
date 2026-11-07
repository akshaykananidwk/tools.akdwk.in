<?php
/** KRISHNA TOOLS — tool search results page (also the Sitelinks SearchAction target). */
require_once __DIR__ . '/includes/bootstrap.php';

$q = trim($_GET['q'] ?? '');
$results = [];
if ($q !== '') {
    require_once __DIR__ . '/includes/seo.php';
    // Every word must appear somewhere (name, keywords, description); name matches rank first.
    $words = array_filter(preg_split('/\s+/u', mb_strtolower($q)), fn($w) => mb_strlen($w) > 1);
    $scored = [];
    foreach (kt_tools() as $t) {
        $name = mb_strtolower($t['name_en'] . ' ' . $t['name_gu'] . ' ' . str_replace('-', ' ', $t['slug']));
        $hay = $name . ' ' . mb_strtolower(implode(' ', kt_tool_keywords($t)) . ' ' . $t['desc_en']);
        $score = 0;
        foreach ($words as $w) {
            if (!str_contains($hay, $w)) { $score = -1; break; }
            $score += str_contains($name, $w) ? 3 : 1;
        }
        if ($words && $score > 0) $scored[] = [$score, $t];
    }
    usort($scored, fn($a, $b) => $b[0] <=> $a[0]);
    $results = array_map(fn($x) => $x[1], $scored);
}

$en = current_lang() === 'en';
$page_title = ($q !== '' ? ($en ? "Search: $q" : "શોધ: $q") : ($en ? 'Search Tools' : 'ટૂલ્સ શોધો')) . ' — ' . brand_name();
$page_desc = $en ? "Search results for \"$q\" across " . kt_tool_count_label() . "+ online tools at Krishna Tools." : "\"$q\" માટે " . kt_tool_count_label() . "+ ટૂલ્સમાં શોધ પરિણામ.";
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
