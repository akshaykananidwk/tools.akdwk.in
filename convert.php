<?php
/**
 * KRISHNA TOOLS — generated converter page (/convert/<slug>).
 * Renders a real, live converter for a unit pair (optionally a specific value),
 * with a conversion table, formula, FAQ and full structured data.
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/generated_tools.php';
require_once __DIR__ . '/includes/convert_content.php';
require_once __DIR__ . '/includes/local_units.php';
require_once __DIR__ . '/includes/convert_widget.php';

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
// Gujarati / Hindi versions (only for indexable pages that have one).
if ($indexable) $page_alternates = gen_alternates($parsed['canonical_slug']);
// Region-dependent units: say which value the page uses.
$regionNotes = [];
foreach (array_unique([$from, $to]) as $u) {
    $d = gen_region_default($u);
    if ($d && count(gen_regions()[$u]['list']) > 1) $regionNotes[] = kt_region_note($u, $cat, 'en', fn($k) => $k === $u ? gen_unit_label($cat, $u)['name'] : gen_unit_label($cat, $k)['symbol']) . ' The size of this unit changes by region — pick yours in the converter below.';
    elseif ($d) $regionNotes[] = 'This page uses the ' . gen_regions()[$u]['list'][gen_regions()[$u]['default']]['en'] . ' value: 1 ' . gen_unit_label($cat, $u)['name'] . ' = ' . gen_fmt($d['f']) . ' kg.';
    elseif (isset(gen_fixed_regional_notes()[$u])) $regionNotes[] = gen_fixed_regional_notes()[$u]['en'];
}
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
      <?php foreach ($regionNotes as $rn): ?><p class="mt-3 text-sm p-2 rounded-lg" style="background:rgba(245,158,11,.10)">📍 <?= e($rn) ?></p><?php endforeach; ?>
    </div>
    <?= kt_lang_switch($parsed['canonical_slug'], 'en') ?>

    <?php
    // Nearby numbers (29, 31, 35, 40 for 30) and the same amount in this unit's other usual partners — only pages Google indexes.
    $near = []; $same = [];
    if ($value !== null) {
        // The closest indexed values (29, 31, 35, 40 for 30; 250, 1000, 100 for 500).
        foreach ([-1, 1, 5, 10, -5, 20] as $d) { $nv = $value + $d; if ($nv > 0 && gen_value_indexable($from, $to, $nv)) $near[] = $nv; }
        if (count($near) < 3) {
            $cand = array_filter(gen_index_values(), fn($nv) => abs($nv - $value) > 1e-9 && !in_array($nv, $near) && gen_value_indexable($from, $to, (float) $nv));
            usort($cand, fn($a, $b) => abs($a - $value) <=> abs($b - $value));
            $near = array_merge($near, array_slice($cand, 0, 4 - count($near)));
            sort($near);
        }
        $near = array_slice(array_values(array_unique($near)), 0, 5);
        foreach (array_keys(gen_unit_categories()[$cat]['units']) as $u2) {
            if ($u2 !== $from && $u2 !== $to && gen_value_indexable($from, $u2, (float) $value)) $same[] = $u2;
        }
        $same = array_slice($same, 0, 4);
    }
    if ($near || $same): ?>
    <div class="flex flex-wrap items-center gap-1.5 text-xs mb-5 -mt-2">
      <?php if ($near): ?><span class="opacity-60">Nearby:</span>
        <?php foreach ($near as $nv): ?><a class="px-2 py-1 rounded-md border hover:border-[color:var(--gold)]" style="border-color:var(--border)" href="<?= SITE_URL ?>/convert/<?= e(gen_canonical_slug($nv, $from, $to)) ?>"><?= gen_plain($nv) ?> <?= e($C['fs']) ?> to <?= e($C['ts']) ?></a><?php endforeach; ?>
      <?php endif; ?>
      <?php if ($same): ?><span class="opacity-60 <?= $near ? 'ml-2' : '' ?>"><?= gen_plain($value) ?> <?= e($C['fs']) ?> in:</span>
        <?php foreach ($same as $u2): ?><a class="px-2 py-1 rounded-md border hover:border-[color:var(--gold)]" style="border-color:var(--border)" href="<?= SITE_URL ?>/convert/<?= e(gen_canonical_slug((float) $value, $from, $u2)) ?>"><?= e(gen_short($u2, $cat)) ?></a><?php endforeach; ?>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Live converter -->
    <?php $__names = []; $__sym = []; foreach ($catMeta['units'] as $k => $u) { $__names[$k] = $u[1]; $__sym[$k] = $u[2]; }
    kt_convert_widget(['cat' => $cat, 'from' => $from, 'to' => $to, 'val' => $defaultVal, 'lang' => 'en', 'names' => $__names, 'sym' => $__sym]); ?>

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
            <td><span data-cv="<?= e($v) ?>"><?= e($r) ?></span> <?= e($toL['symbol']) ?></td>
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

<?php require __DIR__ . '/includes/footer.php'; ?>
