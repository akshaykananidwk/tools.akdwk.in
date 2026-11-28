<?php
/**
 * KRISHNA TOOLS — generated converter page (/convert/<slug>).
 * Renders a real, live converter for a unit pair (optionally a specific value),
 * with a conversion table, formula, FAQ and full structured data.
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/generated_tools.php';
require_once __DIR__ . '/includes/convert_content.php';

$rawSlug = (string) ($_GET['slug'] ?? '');
$slug = preg_replace('/[^a-z0-9.\-]/', '', strtolower(str_replace(',', '', rawurldecode($rawSlug))));
$parsed = gen_parse_slug($slug);
if (!$parsed) {
    http_response_code(404);
    $page_title = '404 — ' . brand_name();
    require __DIR__ . '/includes/header.php';
    echo '<div class="kt-card p-10 text-center mt-10 max-w-lg mx-auto"><div class="text-6xl mb-3">🦚</div>'
       . '<h1 class="text-2xl font-bold mb-2">Converter not found</h1>'
       . '<a href="' . SITE_URL . '/converters.php" class="btn btn-primary mt-3">All converters</a></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

// Consolidate aliases/format variants to one canonical URL (301) for SEO.
if (empty($parsed['is_canonical']) || $rawSlug !== $parsed['canonical_slug']) {
    header('Location: ' . SITE_URL . '/convert/' . $parsed['canonical_slug'], true, 301);
    exit;
}

$cat = $parsed['cat'];
$catMeta = gen_unit_categories()[$cat];
$from = $parsed['from'];
$to = $parsed['to'];
$value = $parsed['value'];              // null for a pair page
$defaultVal = $value ?? 1;

$fromL = gen_unit_label($cat, $from);
$toL   = gen_unit_label($cat, $to);
$answer = gen_convert($cat, $defaultVal, $from, $to);

// Unique page text (Phase 1) and the SEO copy built from it.
$C = gen_page_content($parsed);
$pairName = "{$fromL['name']} to {$toL['name']}";
if (gen_word_pair($from, $to)) {
    // "500 Hours to Days (500 Hours in Days)" / "Hours to Days Converter (hour to day)"
    $A = gen_title_unit($from, $cat, (float) ($value ?? 2)); $B = gen_title_unit($to, $cat, 2);
    $h1 = $value !== null ? gen_plain($defaultVal) . " $A to $B (" . gen_plain($defaultVal) . " $A in $B)" : "$A to $B Converter ({$C['fs']} to {$C['ts']})";
} else {
    $h1 = $value !== null
        ? gen_fmt($defaultVal) . " {$fromL['name']} to {$toL['name']} (" . gen_plain($defaultVal) . " {$C['fs']} in {$C['ts']})"
        : "{$fromL['name']} to {$toL['name']} Converter ({$C['fs']} to {$C['ts']})";
}
$page_title = gen_seo_title($parsed, $C);
$page_desc = gen_seo_desc($parsed, $C);
$forms = gen_search_forms($parsed, $C);
$page_keywords = implode(', ', $forms);
// Number pages: only popular pairs (1–100, 150, 200, 250, 500, 1000) and owner-listed pages stay in Google.
$indexable = $value === null || gen_value_indexable($from, $to, (float) $value);
if (!$indexable) $page_robots = 'noindex, follow';

$breadcrumb = [
    ['label' => 'Home', 'url' => SITE_URL . '/'],
    ['label' => 'Converters', 'url' => SITE_URL . '/converters.php'],
    ['label' => $catMeta['name'], 'url' => SITE_URL . '/converters.php#' . $cat],
    ['label' => $value !== null ? $h1 : $pairName],
];

$table = [];
foreach ($C['table'] as $v) $table[(string) $v] = gen_fmt(gen_convert($cat, (float) $v, $from, $to));
$linkable = array_flip(array_map('strval', gen_value_pages_for($from, $to)));
$faq = $C['faq'];

// FAQ + Breadcrumb + WebPage JSON-LD.
$faqLd = ['@type' => 'FAQPage', 'mainEntity' => array_map(fn($qa) => [
    '@type' => 'Question', 'name' => $qa[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $qa[1]]], $faq)];
$crumbLd = ['@type' => 'BreadcrumbList', 'itemListElement' => []];
foreach ($breadcrumb as $i => $bc) {
    $it = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $bc['label']];
    if (!empty($bc['url'])) $it['item'] = $bc['url'];
    $crumbLd['itemListElement'][] = $it;
}
$extra_head = '<script type="application/ld+json">' . json_encode(['@context' => 'https://schema.org',
    '@graph' => [
        ['@type' => 'WebApplication', 'name' => $h1, 'url' => SITE_URL . '/convert/' . $parsed['canonical_slug'], 'description' => $page_desc,
         'applicationCategory' => 'UtilitiesApplication', 'operatingSystem' => 'Web, Android, iOS', 'browserRequirements' => 'Requires JavaScript for the live converter; the answer works without it.',
         'isAccessibleForFree' => true, 'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'INR']],
        $faqLd, $crumbLd]], JSON_UNESCAPED_SLASHES) . '</script>';

// Units JSON for the live client-side engine (this category only).
$unitsJson = [];
foreach ($catMeta['units'] as $k => $u) $unitsJson[$k] = ['f' => $u[0], 'n' => $u[1], 's' => $u[2]];

$page_canonical = SITE_URL . '/convert/' . $parsed['canonical_slug'];
require __DIR__ . '/includes/header.php';
?>
<div class="grid lg:grid-cols-[1fr,300px] gap-6 mt-4">
  <div>
    <!-- Direct answer (server-side HTML, visible without JavaScript) -->
    <div class="kt-card p-5 mb-5">
      <div class="flex items-start gap-3">
        <div class="icon-wrap w-12 h-12 rounded-xl flex items-center justify-center shrink-0"><i data-lucide="<?= e($catMeta['icon']) ?>" class="w-6 h-6"></i></div>
        <div class="min-w-0">
          <h1 class="text-2xl md:text-3xl font-extrabold" style="color:var(--krishna-blue)"><?= e($h1) ?></h1>
          <p class="opacity-70 mt-1 text-sm"><?= e($catMeta['name']) ?> conversion · <?= e($fromL['symbol']) ?> → <?= e($toL['symbol']) ?></p>
        </div>
      </div>
      <div class="mt-4 p-4 rounded-xl text-center" style="background:linear-gradient(135deg,rgba(30,58,138,.08),rgba(5,150,105,.08))">
        <p class="text-3xl md:text-4xl font-extrabold leading-tight" style="color:var(--peacock-teal)"><?= e($C['answer']) ?></p>
        <p class="mt-2 text-sm opacity-80"><?= e($C['answer_sub']) ?></p>
      </div>
    </div>

    <!-- Live converter -->
    <div class="kt-card p-5 mb-6" id="genConvert"
         data-cat="<?= e($cat) ?>" data-special="<?= e($catMeta['special'] ?? '') ?>"
         data-from="<?= e($from) ?>" data-to="<?= e($to) ?>" data-val="<?= e($defaultVal) ?>"
         data-units='<?= e(json_encode($unitsJson, JSON_UNESCAPED_UNICODE)) ?>'>
      <div class="grid sm:grid-cols-[1fr,auto,1fr] gap-3 items-end">
        <div>
          <label class="kt-label">From</label>
          <input type="number" class="kt-input gc-val" value="<?= e($defaultVal) ?>" step="any">
          <select class="kt-select gc-from mt-2"><?php foreach ($catMeta['units'] as $k => $u): ?><option value="<?= e($k) ?>" <?= $k === $from ? 'selected' : '' ?>><?= e($u[1]) ?> (<?= e($u[2]) ?>)</option><?php endforeach; ?></select>
        </div>
        <button class="btn btn-ghost gc-swap mb-1" title="Swap"><i data-lucide="arrow-left-right" class="w-4 h-4"></i></button>
        <div>
          <label class="kt-label">To</label>
          <input type="text" class="kt-input gc-out" readonly>
          <select class="kt-select gc-to mt-2"><?php foreach ($catMeta['units'] as $k => $u): ?><option value="<?= e($k) ?>" <?= $k === $to ? 'selected' : '' ?>><?= e($u[1]) ?> (<?= e($u[2]) ?>)</option><?php endforeach; ?></select>
        </div>
      </div>
      <div class="mt-4 p-4 rounded-xl text-center" style="background:linear-gradient(135deg,rgba(30,58,138,.08),rgba(5,150,105,.08))">
        <div class="text-2xl font-extrabold gc-result" style="color:var(--peacock-teal)"><?= gen_fmt($defaultVal) ?> <?= e($fromL['symbol']) ?> = <?= gen_fmt($answer) ?> <?= e($toL['symbol']) ?></div>
      </div>
      <div class="flex flex-wrap gap-2 mt-4">
        <button class="btn btn-ghost text-sm gc-copy"><i data-lucide="copy" class="w-4 h-4"></i> Copy</button>
        <button class="btn btn-wa text-sm gc-wa"><i data-lucide="send" class="w-4 h-4"></i> WhatsApp</button>
      </div>
    </div>

    <!-- Guide: formula, examples, units, usage, reverse -->
    <article class="kt-card p-5 mb-6 leading-relaxed space-y-4 conv-guide">
      <section>
        <h2 class="section-title text-xl mb-2">How to convert <?= e($C['fs']) ?> to <?= e($C['ts']) ?></h2>
        <p><?= $C['intro'] ?></p>
        <p class="mt-2 text-sm opacity-80">Searching “<?= e($forms[0]) ?>”, “<?= e($forms[1]) ?>” or “<?= e($forms[2]) ?>”? It is the same conversion — the answer is above.<?php if (isset($forms[3]) && (gen_word_pair($from, $to))): ?> And if you are asking “<?= e(ucfirst($forms[3])) ?>?” — it is <b><?= e(gen_title_num(gen_convert($cat, (float) $defaultVal, $from, $to))) ?> <?= e(strtolower(gen_title_unit($to, $cat, gen_convert($cat, (float) $defaultVal, $from, $to)))) ?></b>.<?php endif; ?></p>
      </section>
      <section>
        <h2 class="section-title text-xl mb-2"><?= e($fromL['name']) ?> to <?= e($toL['name']) ?> formula</h2>
        <p><?= $C['formula'] ?></p>
        <p class="mt-2 p-3 rounded-lg font-mono text-center" style="background:rgba(0,0,0,.04)"><?= e($C['formula_text']) ?></p>
      </section>
      <section>
        <h2 class="section-title text-xl mb-2">Examples: <?= e($C['fs']) ?> to <?= e($C['ts']) ?> step by step</h2>
        <p class="mb-2"><?= e($C['ex_intro']) ?></p>
        <?php foreach ($C['examples'] as $n => $ex): ?>
          <h3 class="font-bold mt-3">Example <?= $n + 1 ?>: <?= gen_fmt($ex['v']) ?> <?= e($C['fs']) ?> to <?= e($C['ts']) ?></h3>
          <ol class="list-decimal ml-6 mt-1 space-y-0.5"><?php foreach ($ex['steps'] as $st): ?><li><?= $st ?></li><?php endforeach; ?></ol>
        <?php endforeach; ?>
      </section>
      <section>
        <h2 class="section-title text-xl mb-2"><?= e($C['what_from']) ?> (<?= e($C['fs']) ?>)?</h2>
        <p><?= e($C['about_from']) ?></p>
        <h2 class="section-title text-xl mb-2 mt-4"><?= e($C['what_to']) ?> (<?= e($C['ts']) ?>)?</h2>
        <p><?= e($C['about_to']) ?></p>
      </section>
      <section>
        <h2 class="section-title text-xl mb-2">Where is <?= e($C['fs']) ?> to <?= e($C['ts']) ?> used?</h2>
        <p><?= e($C['where']) ?></p>
      </section>
      <section>
        <h2 class="section-title text-xl mb-2"><?= e($toL['name']) ?> to <?= e($fromL['name']) ?> (reverse)</h2>
        <p><?= $C['reverse'] ?></p>
      </section>
      <section>
        <h2 class="section-title text-xl mb-2"><?= e($C['vs']) ?> <?= e($C['fs']) ?> in other units</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-sm">
          <?php foreach ($C['others'] as $o): ?>
            <div class="p-2 rounded-lg border" style="border-color:var(--border)"><span class="opacity-60"><?= e($o['name']) ?></span><br><b><?= e($o['val']) ?> <?= e($o['sym']) ?></b></div>
          <?php endforeach; ?>
        </div>
      </section>
      <section>
        <h2 class="section-title text-xl mb-2">Good to know</h2>
        <?php if ($C['compare']): ?><p><?= e($C['compare']) ?></p><?php endif; ?>
        <p class="<?= $C['compare'] ? 'mt-2' : '' ?>"><?= e($C['tip']) ?></p>
        <p class="mt-2"><?= e($C['quick']) ?></p>
      </section>
    </article>

    <!-- Conversion table (30–40 rows) -->
    <div class="kt-card p-5 mb-6">
      <h2 class="section-title text-xl mb-1"><?= e($fromL['name']) ?> to <?= e($toL['name']) ?> table (<?= e($C['fs']) ?> to <?= e($C['ts']) ?>)</h2>
      <div class="divider-gold mb-4"></div>
      <div class="overflow-x-auto"><table class="w-full text-sm">
        <thead><tr class="opacity-60 text-left"><th class="py-2"><?= e($fromL['name']) ?> (<?= e($fromL['symbol']) ?>)</th><th><?= e($toL['name']) ?> (<?= e($toL['symbol']) ?>)</th></tr></thead>
        <tbody>
        <?php foreach ($table as $v => $r): $isCur = $value !== null && abs((float) $v - $value) < 1e-9; ?>
          <tr class="border-t" style="border-color:var(--border)<?= $isCur ? ';background:rgba(5,150,105,.08);font-weight:700' : '' ?>">
            <td class="py-2"><?php if (!$isCur && isset($linkable[(string) $v])): ?><a class="hover:text-[color:var(--saffron)]" href="<?= SITE_URL ?>/convert/<?= e(gen_slug_num((float) $v)) ?>-<?= e($from) ?>-to-<?= e($to) ?>"><?= gen_fmt((float) $v) ?> <?= e($fromL['symbol']) ?></a><?php else: ?><?= gen_fmt((float) $v) ?> <?= e($fromL['symbol']) ?><?php endif; ?></td>
            <td><?= e($r) ?> <?= e($toL['symbol']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table></div>
    </div>

    <?php if (gen_is_promoted_pair($from, $to)): ?>
    <!-- Every number page of this pair (internal links for Google + quick picks for visitors) -->
    <div class="kt-card p-5 mb-6">
      <h2 class="section-title text-xl mb-1"><?= $value !== null ? 'More ' . e($C['fs']) . ' to ' . e($C['ts']) . ' values' : 'Common ' . e($C['fs']) . ' to ' . e($C['ts']) . ' conversions' ?></h2>
      <div class="divider-gold mb-3"></div>
      <div class="flex flex-wrap gap-1.5 text-xs">
        <?php foreach (gen_index_values() as $nv): if ($value !== null && abs($nv - $value) < 1e-9) continue; ?>
          <a class="px-2 py-1 rounded-md border hover:border-[color:var(--gold)]" style="border-color:var(--border)" href="<?= SITE_URL ?>/convert/<?= e(gen_canonical_slug((float) $nv, $from, $to)) ?>"><?= $nv ?> <?= e($C['fs']) ?> to <?= e($C['ts']) ?></a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- FAQ -->
    <div class="kt-card p-5 mb-6">
      <h2 class="section-title text-xl mb-1"><?= e($C['fs']) ?> to <?= e($C['ts']) ?> — frequently asked questions</h2>
      <div class="divider-gold mb-2"></div>
      <?php foreach ($faq as $qa): ?>
        <div class="faq-item border-b" style="border-color:var(--border)">
          <div class="faq-q" onclick="this.parentElement.classList.toggle('open')"><span><?= e($qa[0]) ?></span><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
          <div class="faq-a"><p class="pb-3"><?= e($qa[1]) ?></p></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Related conversions -->
  <aside>
    <div class="kt-card p-5 sticky top-20">
      <h2 class="font-bold mb-3">Related conversions</h2>
      <div class="space-y-2 text-sm">
        <a href="<?= SITE_URL ?>/convert/<?= e($to) ?>-to-<?= e($from) ?>" class="block p-2 rounded-lg hover:bg-[color:var(--gold)]/10"><?= e($toL['name']) ?> to <?= e($fromL['name']) ?></a>
        <?php $i = 0; foreach (array_keys($catMeta['units']) as $u): if ($u === $from || $u === $to || $i >= 6) continue; $i++;
          $ul = gen_unit_label($cat, $u); ?>
          <a href="<?= SITE_URL ?>/convert/<?= e($from) ?>-to-<?= e($u) ?>" class="block p-2 rounded-lg hover:bg-[color:var(--gold)]/10"><?= e($fromL['name']) ?> to <?= e($ul['name']) ?></a>
        <?php endforeach; ?>
        <a href="<?= SITE_URL ?>/converters.php#<?= e($cat) ?>" class="block p-2 rounded-lg text-[color:var(--peacock-teal)]">All <?= e($catMeta['name']) ?> converters →</a>
      </div>
    </div>
  </aside>
</div>

<script>
(function () {
  const box = document.getElementById('genConvert');
  const units = JSON.parse(box.dataset.units);
  const special = box.dataset.special;
  const valI = box.querySelector('.gc-val'), fromS = box.querySelector('.gc-from'),
        toS = box.querySelector('.gc-to'), outI = box.querySelector('.gc-out'),
        res = box.querySelector('.gc-result');
  function convert(v, from, to) {
    if (special === 'temp') {
      let c = from === 'celsius' ? v : from === 'fahrenheit' ? (v - 32) * 5 / 9 : from === 'kelvin' ? v - 273.15 : (v - 491.67) * 5 / 9;
      return to === 'celsius' ? c : to === 'fahrenheit' ? c * 9 / 5 + 32 : to === 'kelvin' ? c + 273.15 : (c + 273.15) * 9 / 5;
    }
    if (special === 'fuel') {
      const toKmL = (u, x) => u === 'liter-per-100km' ? (x === 0 ? 0 : 100 / x) : x * units[u].f;
      const kmL = toKmL(from, v);
      return to === 'liter-per-100km' ? (kmL === 0 ? 0 : 100 / kmL) : kmL / units[to].f;
    }
    return v * units[from].f / units[to].f;
  }
  function fmt(n) {
    if (!isFinite(n)) return '—';
    if (n === 0) return '0';
    const a = Math.abs(n);
    if (a >= 1e15 || (a < 1e-6)) return n.toExponential(6).replace(/\.?0+e/, 'e');
    const d = a >= 100 ? 2 : a >= 1 ? 4 : 8;
    return parseFloat(n.toFixed(d)).toLocaleString('en-US', { maximumFractionDigits: d });
  }
  let last = '';
  function run() {
    const v = parseFloat(valI.value) || 0, f = fromS.value, t = toS.value;
    const r = convert(v, f, t);
    outI.value = fmt(r);
    last = `${fmt(v)} ${units[f].s} = ${fmt(r)} ${units[t].s}`;
    res.textContent = last;
  }
  valI.addEventListener('input', run); fromS.addEventListener('change', run); toS.addEventListener('change', run);
  box.querySelector('.gc-swap').addEventListener('click', () => { const a = fromS.value; fromS.value = toS.value; toS.value = a; run(); });
  box.querySelector('.gc-copy').addEventListener('click', () => window.ktCopy && window.ktCopy(last));
  box.querySelector('.gc-wa').addEventListener('click', () => window.ktWaShareText && window.ktWaShareText(last + '\n' + location.href));
  run();
})();
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
