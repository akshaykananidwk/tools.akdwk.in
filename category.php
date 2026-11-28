<?php
/** KRISHNA TOOLS — category listing page (/category/<slug>). */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/category_intros.php';

$slug = preg_replace('/[^a-z0-9-]/', '', $_GET['slug'] ?? '');
$cat = null;
foreach (kt_categories() as $c) if ($c['slug'] === $slug) $cat = $c;
if (!$cat) {
    // Unknown category: a real 404 (a redirect to the home page looks like a soft 404 to Google).
    http_response_code(404);
    $page_title = 'Category not found — ' . brand_name();
    $page_robots = 'noindex, follow';
    require __DIR__ . '/includes/header.php';
    echo '<div class="kt-card p-10 text-center mt-10 max-w-lg mx-auto"><div class="text-6xl mb-3">🦚</div><h1 class="text-2xl font-bold mb-2">Category not found</h1>'
       . '<a href="' . SITE_URL . '/" class="btn btn-primary mt-3">All tools</a></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$tools = kt_tools_by_cat($slug);
$dbTools = [];
try { foreach (all("SELECT slug, is_active, is_premium FROM " . tbl('tools')) as $r) $dbTools[$r['slug']] = $r; } catch (Throwable $e) {}

$en = current_lang() === 'en';
$n = count($tools);
$cn = loc($cat, 'name');
$cnTools = stripos($cn, 'tools') !== false || stripos($cn, 'ટૂલ્સ') !== false ? $cn : $cn . ($en ? ' Tools' : ' ટૂલ્સ');
$sample = implode(', ', array_map(fn($t) => loc($t, 'name'), array_slice($tools, 0, 4)));
$page_title = $en
    ? "{$cnTools} — {$n} Free Online Tools | " . brand_name()
    : "{$cnTools} — {$n} ફ્રી ઓનલાઇન ટૂલ્સ | " . brand_name();
$page_desc = $en
    ? "{$n} free {$cnTools}: {$sample} and more. No login, works on mobile, nothing is uploaded."
    : "{$n} ફ્રી {$cnTools} ગુજરાતી અને English માં: {$sample} અને વધુ. લોગિન વગર, મોબાઇલ પર ચાલે.";
// ItemList structured data so Google understands the category page.
$listLd = ['@context' => 'https://schema.org', '@type' => 'ItemList', 'name' => loc($cat, 'name'), 'numberOfItems' => $n, 'itemListElement' => []];
foreach ($tools as $i => $t) $listLd['itemListElement'][] = ['@type' => 'ListItem', 'position' => $i + 1, 'url' => SITE_URL . '/tool/' . $t['slug'], 'name' => loc($t, 'name')];
$extra_head = '<script type="application/ld+json">' . json_encode($listLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
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
<?php
$intro = kt_category_intros()[$slug] ?? null;
// Most-used tools in this category first (views from the DB), otherwise the registry order.
$ranked = $tools;
usort($ranked, fn($a, $b) => (int) ($dbTools[$b['slug']]['views'] ?? 0) <=> (int) ($dbTools[$a['slug']]['views'] ?? 0));
$top = array_slice($ranked, 0, 6);
$hasBulk = function_exists('kt_is_bulk') ? (bool) array_filter($tools, 'kt_is_bulk') : false;
$cnLow = strtolower($cnTools);
if ($intro): ?>
<p class="opacity-80 -mt-3 mb-5 max-w-3xl"><?= e($intro['lead']) ?> All <?= $n ?> tools are free, work on mobile and need no login.</p>
<?php endif; ?>
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
  <?php foreach ($tools as $t):
    $db = $dbTools[$t['slug']] ?? null;
    if ($db && (int) $db['is_active'] === 0) continue;
    $prem = !kt_all_free() && ($t['premium'] || ($db && (int) $db['is_premium'] === 1)); ?>
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
<?php if ($intro): ?>
<!-- Category guide: who it is for, what it does, the most used tools, how it works -->
<article class="kt-card p-5 md:p-6 mt-8 leading-relaxed space-y-3">
  <h2 class="section-title text-xl">About these <?= e($cnTools) ?></h2>
  <p><?= e($intro['lead']) ?> They are used by <?= e($intro['who']) ?>. Every tool opens instantly, works on Android, iPhone and computers, and does the work inside your browser — your files and numbers are not uploaded to our server.</p>
  <h3 class="font-bold">What you can do here</h3>
  <ul class="list-disc ml-6 space-y-0.5">
    <?php foreach ($intro['uses'] as $u): ?><li><?= e(ucfirst($u)) ?></li><?php endforeach; ?>
  </ul>
  <h3 class="font-bold">Most used <?= e($cnLow) ?></h3>
  <p>
    <?php foreach ($top as $i => $t): ?><a class="underline hover:text-[color:var(--saffron)]" href="<?= SITE_URL ?>/tool/<?= e($t['slug']) ?>"><?= e(loc($t, 'name')) ?></a><?= $i < count($top) - 1 ? ', ' : '.' ?><?php endforeach; ?>
    Each page explains the formula or steps, shows an example and answers common questions, so you can check the result yourself.
  </p>
  <h3 class="font-bold">How to use them</h3>
  <p>Open a tool, type your numbers or choose your file, and the result appears straight away. You can copy it, download it<?= $hasBulk ? ' (many files at once as a ZIP)' : '' ?>, print it or share it on WhatsApp. Nothing needs to be installed, and there is no daily limit or watermark.</p>
  <h3 class="font-bold">Finding the right tool quickly</h3>
  <p>This page lists all <?= $n ?> <?= e($cnLow) ?> in one place. You can also type what you need in the search box at the top of any page — for example a tool name, a job like “<?= e($intro['uses'][0]) ?>”, or a conversion such as “30 qtl to kg” — and matching tools and converters appear as you type. Results you calculate stay on your device; you can bookmark any tool or add Krishna Tools to your phone’s home screen to open it like an app.</p>
  <p><b>Tip:</b> <?= e($intro['tip']) ?></p>
  <p class="text-sm opacity-70">Krishna Tools is made by AK Computer, Dwarka (Gujarat). If a tool you need is missing, <a class="underline" href="<?= SITE_URL ?>/contact.php">tell us</a> and we will add it.</p>
</article>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
