<?php
/** KRISHNA TOOLS — home page (hero + category grid + tool grid + live search). */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/generated_tools.php';

$cats  = kt_categories();
$tools = kt_tools();
$gc = gen_total_count()['total'];

// Merge DB flags (active/premium/views) so admin toggles reflect on the grid.
$dbTools = [];
try { foreach (all("SELECT slug, is_active, is_premium, views FROM " . tbl('tools')) as $r) $dbTools[$r['slug']] = $r; } catch (Throwable $e) {}

$en = current_lang() === 'en';
$nTools = (int) (floor(count($tools) / 50) * 50); // "700+" style rounded count
$page_title = $en
    ? brand_name() . " — {$nTools}+ Free Online Tools in Gujarati & English"
    : brand_name() . " — {$nTools}+ ફ્રી ઓનલાઇન ટૂલ્સ ગુજરાતીમાં";
$page_desc  = $en
    ? "Krishna Tools by AK Computer, Dwarka — {$nTools}+ free online tools for everyone: PDF & image, restaurant, hotel, salon, doctor, contractor, electrician, printing, student, farmer, finance, birthday wishes and more. No login, works in your browser."
    : "AK Computer, દ્વારકા દ્વારા {$nTools}+ ફ્રી ઓનલાઇન ટૂલ્સ — PDF, ઇમેજ, રેસ્ટોરન્ટ, હોટલ, સલૂન, ડૉક્ટર, કોન્ટ્રાક્ટર, ઇલેક્ટ્રિશિયન, વિદ્યાર્થી, ખેડૂત, શુભેચ્છા અને ઘણું બધું. લોગિન વગર, સંપૂર્ણ ફ્રી.";
$perCat = 8; // home shows the first 8 tools of each category; the rest live on the category page
$page_keywords = 'online tools, free tools, pdf converter, image compressor, cctv storage calculator, gst calculator, qr code generator, invoice generator, AK Computer Dwarka';
require __DIR__ . '/includes/header.php';
?>
<!-- Hero -->
<section class="hero-krishna rounded-3xl p-8 md:p-12 mt-4 text-center relative">
  <div class="text-5xl mb-3">🦚</div>
  <h1 class="brand-title text-3xl md:text-5xl mb-3"><?= e(brand_name()) ?></h1>
  <p class="text-lg opacity-80 max-w-2xl mx-auto"><?= t('tagline') ?></p>
  <div class="max-w-xl mx-auto mt-6 relative">
    <input id="heroSearch" type="text" placeholder="<?= t('search_tools') ?>" class="kt-input !py-3 pl-11 text-lg" autocomplete="off">
    <i data-lucide="search" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 opacity-60"></i>
    <div id="heroResults" class="absolute top-14 left-0 right-0 kt-card p-2 hidden max-h-96 overflow-auto z-30 text-left"></div>
  </div>
  <div class="flex items-center justify-center gap-4 mt-5 text-sm opacity-70 flex-wrap">
    <span>✅ <?= count($tools) ?>+ <?= t('all_tools') ?></span>
    <span>🔒 <?= T_bilingual('બ્રાઉઝરમાં સુરક્ષિત', 'Secure in browser') ?></span>
    <span>🆓 <?= T_bilingual('100% ફ્રી, લોગિન વગર', '100% free, no login') ?></span>
  </div>
</section>

<!-- Category filter chips -->
<div id="categories" class="flex gap-2 overflow-x-auto py-6 -mx-1 px-1 scrollbar-none">
  <button class="cat-chip active" data-cat="all"><?= t('all_categories') ?></button>
  <?php foreach ($cats as $c): ?>
    <button class="cat-chip" data-cat="<?= e($c['slug']) ?>"><?= e(loc($c, 'name')) ?></button>
  <?php endforeach; ?>
</div>

<!-- Tool grid grouped by category -->
<?php foreach ($cats as $c):
    $catTools = array_values(array_filter($tools, fn($t) => $t['cat'] === $c['slug']));
    if (!$catTools) continue;
    $catCount = count($catTools);
    $catTools = array_slice($catTools, 0, $perCat); ?>
  <section class="cat-section mb-8" data-cat="<?= e($c['slug']) ?>">
    <div class="flex items-center gap-3 mb-4">
      <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white" style="background:<?= e($c['color']) ?>">
        <i data-lucide="<?= e($c['icon']) ?>" class="w-5 h-5"></i></div>
      <div>
        <h2 class="section-title text-xl"><?= e(loc($c, 'name')) ?></h2>
        <div class="text-xs opacity-60"><?= $catCount ?> <?= t('all_tools') ?></div>
      </div>
      <a href="<?= SITE_URL ?>/category/<?= e($c['slug']) ?>" class="ml-auto text-sm text-[color:var(--peacock-teal)] hover:underline"><?= T_bilingual('બધા ' . $catCount . ' જુઓ', 'View all ' . $catCount) ?> →</a>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
      <?php foreach ($catTools as $t):
        $db = $dbTools[$t['slug']] ?? null;
        if ($db && (int) $db['is_active'] === 0) continue;
        $prem = !kt_all_free() && ($t['premium'] || ($db && (int) $db['is_premium'] === 1)); ?>
        <a href="<?= SITE_URL ?>/tool/<?= e($t['slug']) ?>" class="kt-card tool-card p-4 block tool-item"
           data-name="<?= e(strtolower($t['name_gu'] . ' ' . $t['name_en'] . ' ' . $t['slug'])) ?>">
          <div class="flex items-start justify-between">
            <div class="icon-wrap w-10 h-10 rounded-xl flex items-center justify-center">
              <i data-lucide="<?= e($t['icon']) ?>" class="w-5 h-5"></i></div>
            <?php if ($prem): ?><span class="badge-premium">PRO</span><?php endif; ?>
          </div>
          <div class="font-semibold mt-3 leading-tight"><?= e(loc($t, 'name')) ?></div>
          <div class="text-xs opacity-60 mt-1 line-clamp-2"><?= e(loc($t, 'desc')) ?></div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
<?php endforeach; ?>

<!-- Converters hub promo -->
<section class="kt-card p-6 md:p-8 mt-6 flex flex-col md:flex-row items-center gap-6" style="background:linear-gradient(135deg,rgba(14,116,144,.08),rgba(245,158,11,.08))">
  <div class="flex-1">
    <h2 class="section-title text-2xl mb-2"><?= $en ? number_format($gc) . '+ Instant Unit Converters' : number_format($gc) . '+ યુનિટ કન્વર્ટર' ?></h2>
    <p class="opacity-75"><?= $en ? 'Length, weight, temperature, volume, speed, digital storage, data rate and more — with formulas and conversion tables.' : 'લંબાઈ, વજન, તાપમાન, વોલ્યુમ, સ્પીડ, ડિજિટલ સ્ટોરેજ અને વધુ — ફોર્મ્યુલા અને ટેબલ સાથે.' ?></p>
    <div class="flex flex-wrap gap-2 mt-4">
      <?php foreach (['kilometer-to-mile' => 'km → miles', 'kilogram-to-pound' => 'kg → lb', 'celsius-to-fahrenheit' => '°C → °F', 'megabyte-to-gigabyte' => 'MB → GB', 'megabit-per-second-to-megabyte-per-second' => 'Mbps → MB/s'] as $slug => $label): ?>
        <a href="<?= SITE_URL ?>/convert/<?= $slug ?>" class="cat-chip"><?= $label ?></a>
      <?php endforeach; ?>
    </div>
  </div>
  <a href="<?= SITE_URL ?>/converters.php" class="btn btn-primary"><i data-lucide="arrow-left-right" class="w-4 h-4"></i> <?= $en ? 'All Converters' : 'બધા કન્વર્ટર' ?></a>
</section>

<!-- Pricing teaser (hidden while every tool is free) -->
<?php if (!kt_all_free()): ?>
<section class="kt-card p-6 md:p-8 text-center mt-6">
  <h2 class="section-title text-2xl mb-2"><?= t('pricing') ?></h2>
  <p class="opacity-70 mb-5"><?= T_bilingual('ફ્રી શરૂ કરો, જરૂર પડે અપગ્રેડ કરો', 'Start free, upgrade when you need') ?></p>
  <div class="grid sm:grid-cols-3 gap-4 max-w-3xl mx-auto text-left">
    <div class="kt-card p-5"><div class="font-bold"><?= t('plan_free') ?></div><div class="text-2xl font-extrabold my-2">₹0</div><div class="text-xs opacity-60"><?= T_bilingual('દરરોજ 3 વખત · 5MB', '3/day · 5MB') ?></div></div>
    <div class="kt-card p-5"><div class="font-bold"><?= t('plan_monthly') ?></div><div class="text-2xl font-extrabold my-2">₹99<span class="text-sm opacity-60"><?= t('per_month') ?></span></div><div class="text-xs opacity-60"><?= T_bilingual('અમર્યાદિત · 100MB', 'Unlimited · 100MB') ?></div></div>
    <div class="kt-card p-5 border-2" style="border-color:var(--gold)"><div class="flex items-center gap-2"><span class="font-bold"><?= t('plan_yearly') ?></span><span class="badge-premium"><?= t('best_value') ?></span></div><div class="text-2xl font-extrabold my-2">₹499<span class="text-sm opacity-60"><?= t('per_year') ?></span></div><div class="text-xs opacity-60"><?= T_bilingual('API · બલ્ક ZIP · સેવ્ડ ફાઇલ્સ', 'API · Bulk ZIP · Saved files') ?></div></div>
  </div>
  <a href="<?= SITE_URL ?>/pricing.php" class="btn btn-cta mt-6"><i data-lucide="gem" class="w-4 h-4"></i> <?= t('pricing') ?></a>
</section>
<?php endif; ?>

<script>
// Category filter + hero search (client-side over the rendered grid).
document.querySelectorAll('#categories .cat-chip').forEach(chip => chip.onclick = () => {
  document.querySelectorAll('#categories .cat-chip').forEach(c => c.classList.remove('active'));
  chip.classList.add('active');
  const cat = chip.dataset.cat;
  document.querySelectorAll('.cat-section').forEach(s => s.style.display = (cat === 'all' || s.dataset.cat === cat) ? '' : 'none');
});
// Hero search is wired by app.js (searches the full tool index).
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
<?php
/** Tiny inline bilingual helper for one-off strings on this page. */
function T_bilingual($gu, $en) { return current_lang() === 'en' ? $en : $gu; }
