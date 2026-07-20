<?php
/** KRISHNA TOOLS — home page (hero + category grid + tool grid + live search). */
require_once __DIR__ . '/includes/bootstrap.php';

$cats  = kt_categories();
$tools = kt_tools();

// Merge DB flags (active/premium/views) so admin toggles reflect on the grid.
$dbTools = [];
try { foreach (all("SELECT slug, is_active, is_premium, views FROM " . tbl('tools')) as $r) $dbTools[$r['slug']] = $r; } catch (Throwable $e) {}

$en = current_lang() === 'en';
$page_title = $en
    ? brand_name() . ' — 120+ Free Online Tools (PDF, Image, CCTV, GST)'
    : setting('site_name', 'કૃષ્ણા ટૂલ્સ') . ' — 120+ ફ્રી ઓનલાઇન ટૂલ્સ';
$page_desc  = $en
    ? 'Krishna Tools by AK Computer, Dwarka — 120+ free online tools: PDF & image converters, CCTV storage/bandwidth calculators, GST & invoice generators, QR codes and more. Fast, secure, works in your browser.'
    : 'AK Computer, દ્વારકા દ્વારા 120+ ફ્રી ઓનલાઇન ટૂલ્સ — PDF, ઇમેજ, CCTV કેલ્ક્યુલેટર, GST, QR અને ઘણું બધું.';
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
    <span>🆓 <?= T_bilingual('ફ્રી શરૂ કરો', 'Start free') ?></span>
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
    $catTools = array_filter($tools, fn($t) => $t['cat'] === $c['slug']); ?>
  <section class="cat-section mb-8" data-cat="<?= e($c['slug']) ?>">
    <div class="flex items-center gap-3 mb-4">
      <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white" style="background:<?= e($c['color']) ?>">
        <i data-lucide="<?= e($c['icon']) ?>" class="w-5 h-5"></i></div>
      <div>
        <h2 class="section-title text-xl"><?= e(loc($c, 'name')) ?></h2>
        <div class="text-xs opacity-60"><?= count($catTools) ?> <?= t('all_tools') ?></div>
      </div>
      <a href="<?= SITE_URL ?>/category/<?= e($c['slug']) ?>" class="ml-auto text-sm text-[color:var(--peacock-teal)] hover:underline"><?= T_bilingual('બધા જુઓ', 'View all') ?> →</a>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
      <?php foreach ($catTools as $t):
        $db = $dbTools[$t['slug']] ?? null;
        if ($db && (int) $db['is_active'] === 0) continue;
        $prem = $t['premium'] || ($db && (int) $db['is_premium'] === 1); ?>
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

<!-- Pricing teaser -->
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

<script>
// Category filter + hero search (client-side over the rendered grid).
document.querySelectorAll('#categories .cat-chip').forEach(chip => chip.onclick = () => {
  document.querySelectorAll('#categories .cat-chip').forEach(c => c.classList.remove('active'));
  chip.classList.add('active');
  const cat = chip.dataset.cat;
  document.querySelectorAll('.cat-section').forEach(s => s.style.display = (cat === 'all' || s.dataset.cat === cat) ? '' : 'none');
});
(function () {
  const inp = document.getElementById('heroSearch'), box = document.getElementById('heroResults');
  const items = Array.from(document.querySelectorAll('.tool-item'));
  inp.addEventListener('input', () => {
    const term = inp.value.trim().toLowerCase();
    if (!term) { box.classList.add('hidden'); return; }
    const hits = items.filter(a => a.dataset.name.includes(term)).slice(0, 10);
    box.innerHTML = hits.length ? hits.map(a => `<a href="${a.href}" class="flex items-center gap-2 p-2 rounded-lg hover:bg-[color:var(--gold)]/10"><span class="text-sm font-medium">${a.querySelector('.font-semibold').textContent}</span></a>`).join('') : `<div class="p-2 text-sm opacity-60">કંઈ મળ્યું નહીં</div>`;
    box.classList.remove('hidden');
  });
  document.addEventListener('click', e => { if (!box.contains(e.target) && e.target !== inp) box.classList.add('hidden'); });
})();
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
<?php
/** Tiny inline bilingual helper for one-off strings on this page. */
function T_bilingual($gu, $en) { return current_lang() === 'en' ? $en : $gu; }
