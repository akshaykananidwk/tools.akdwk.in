<?php
/**
 * KRISHNA TOOLS — Gujarati / Hindi converter page: /gu/convert/<slug>, /hi/convert/<slug>.
 * Only the slugs in gen_local_slugs() exist (Indian units, the 46 popular pairs, and number
 * pages for quintal, tola, maund and vigha). Each page is self-canonical and lists its
 * English, Gujarati and Hindi versions with hreflang.
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/generated_tools.php';
require_once __DIR__ . '/includes/convert_local_content.php';

$lang = in_array($_GET['ln'] ?? '', gen_local_langs(), true) ? $_GET['ln'] : 'gu';
$gu = $lang === 'gu';
$rawSlug = (string) ($_GET['slug'] ?? '');
$slug = preg_replace('/[^a-z0-9.\-]/', '', strtolower(str_replace(',', '', rawurldecode($rawSlug))));
$parsed = gen_parse_slug($slug);

// Short forms (qtl, kg, vigha) → the canonical slug, if that page exists.
if ($parsed && $rawSlug !== $parsed['canonical_slug'] && gen_local_exists($parsed['canonical_slug'])) {
    header('Location: ' . SITE_URL . "/$lang/convert/" . $parsed['canonical_slug'], true, 301);
    exit;
}
if (!$parsed || !gen_local_exists($parsed['canonical_slug'])) {
    http_response_code(404);
    $page_title = '404 — ' . brand_name();
    $page_robots = 'noindex, follow';
    $page_lang = $lang;
    require __DIR__ . '/includes/header.php';
    echo '<div class="kt-card p-10 text-center mt-10 max-w-lg mx-auto"><div class="text-6xl mb-3">🦚</div>'
       . '<h1 class="text-2xl font-bold mb-2">' . ($gu ? 'આ પેજ મળ્યું નથી' : 'यह पेज नहीं मिला') . '</h1>'
       . '<a href="' . SITE_URL . '/converters.php" class="btn btn-primary mt-3">' . ($gu ? 'બધા કન્વર્ટર' : 'सभी कन्वर्टर') . '</a></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$slug = $parsed['canonical_slug'];
$cat = $parsed['cat']; $from = $parsed['from']; $to = $parsed['to']; $value = $parsed['value'];
$catMeta = gen_unit_categories()[$cat];
$L = gen_local_content($parsed, $lang);
$base = SITE_URL . "/$lang/convert/";

$page_lang = $lang;
$page_title = $L['title'];
$page_desc = $L['desc'];
$page_keywords = implode(', ', $L['hing']);
$page_canonical = $base . $slug;
$page_alternates = gen_alternates($slug);

$breadcrumb = [
    ['label' => $gu ? 'હોમ' : 'होम', 'url' => SITE_URL . '/'],
    ['label' => $gu ? 'કન્વર્ટર' : 'कन्वर्टर', 'url' => SITE_URL . '/converters.php'],
    ['label' => $value !== null ? $L['h1'] : "{$L['F']} → {$L['T']}"],
];

// Names for the converter box: local name where we have one, else the English name.
$names = []; $sym = [];
foreach ($catMeta['units'] as $k => $u) {
    $n = gen_local_names()[$k] ?? null;
    $names[$k] = $n ? gen_lname($k, $lang) : $u[1];
    $sym[$k] = $n ? gen_lname($k, $lang) : $u[2];
}

$faq = $L['faq'];
$crumbLd = ['@type' => 'BreadcrumbList', 'itemListElement' => []];
foreach ($breadcrumb as $i => $bc) {
    $it = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $bc['label']];
    if (!empty($bc['url'])) $it['item'] = $bc['url'];
    $crumbLd['itemListElement'][] = $it;
}
$extra_head = '<script type="application/ld+json">' . json_encode(['@context' => 'https://schema.org', '@graph' => [
    ['@type' => 'WebApplication', 'name' => $L['h1'], 'url' => $page_canonical, 'inLanguage' => $lang, 'description' => $page_desc,
     'applicationCategory' => 'UtilitiesApplication', 'operatingSystem' => 'Web, Android, iOS', 'isAccessibleForFree' => true,
     'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'INR']],
    ['@type' => 'FAQPage', 'inLanguage' => $lang, 'mainEntity' => array_map(fn($qa) => ['@type' => 'Question', 'name' => $qa[0],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags($qa[1])]], $faq)],
    $crumbLd]], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';

// Region notes (maund, bigha…) and fixed-size notes (guntha, var).
$notes = [];
foreach (array_unique([$from, $to]) as $u) {
    if (gen_region_default($u) && count(gen_regions()[$u]['list']) > 1) {
        $notes[] = kt_region_note($u, $cat, $lang, fn($k) => $names[$k]) . ' ' . ($gu ? 'આ માપ વિસ્તાર પ્રમાણે બદલાય છે — નીચે કન્વર્ટરમાં તમારો વિસ્તાર પસંદ કરો.' : 'यह माप इलाक़े के हिसाब से बदलता है — नीचे कन्वर्टर में अपना क्षेत्र चुनें।');
    } elseif (isset(gen_fixed_regional_notes()[$u])) {
        $notes[] = gen_fixed_regional_notes()[$u][$lang];
    }
}

// Other local pages to link: reverse pair, the pair page, nearby numbers, and more pairs of these units.
$local = fn($s) => gen_local_exists($s) ? $base . $s : null;
$rev = $local("$to-to-$from");
$pairUrl = $value !== null ? $local("$from-to-$to") : null;
$numLinks = [];
foreach (gen_local_values() as $nv) { $s = gen_canonical_slug((float) $nv, $from, $to); if (gen_local_exists($s) && ($value === null || abs($nv - $value) > 1e-9)) $numLinks[$nv] = $base . $s; }
$morePairs = [];
foreach (gen_local_slugs() as $s) {
    $pp = gen_parse_slug($s);
    if (!$pp || $pp['value'] !== null || $s === "$from-to-$to" || $s === "$to-to-$from") continue;
    if ($pp['from'] === $from || $pp['to'] === $from || $pp['from'] === $to || $pp['to'] === $to) $morePairs[$s] = gen_lname($pp['from'], $lang) . ($gu ? ' થી ' : ' से ') . gen_lname($pp['to'], $lang);
    if (count($morePairs) >= 8) break;
}

require __DIR__ . '/includes/header.php';
$lang = $page_lang; // header.php sets $lang to the site language (en)
$dot = $gu ? '.' : '।';
?>
<div class="grid lg:grid-cols-[1fr,300px] gap-6 mt-4" lang="<?= e($lang) ?>">
  <div>
    <div class="kt-card p-5 mb-5">
      <div class="flex items-start gap-3">
        <div class="icon-wrap w-12 h-12 rounded-xl flex items-center justify-center shrink-0"><i data-lucide="<?= e($catMeta['icon']) ?>" class="w-6 h-6"></i></div>
        <div class="min-w-0">
          <h1 class="text-2xl md:text-3xl font-extrabold" style="color:var(--krishna-blue)"><?= e($L['h1']) ?></h1>
          <p class="opacity-70 mt-1 text-sm"><?= e(gen_unit_label($cat, $from)['name']) ?> → <?= e(gen_unit_label($cat, $to)['name']) ?></p>
        </div>
      </div>
      <div class="mt-4 p-4 rounded-xl text-center" style="background:linear-gradient(135deg,rgba(30,58,138,.08),rgba(5,150,105,.08))">
        <p class="text-3xl md:text-4xl font-extrabold leading-tight" style="color:var(--peacock-teal)"><?= e($L['answer']) ?></p>
        <p class="mt-2 text-sm opacity-80"><?= e($L['answer_sub']) ?></p>
      </div>
      <?php foreach ($notes as $n): ?><p class="mt-3 text-sm p-2 rounded-lg" style="background:rgba(245,158,11,.10)">📍 <?= e($n) ?></p><?php endforeach; ?>
    </div>
    <?= kt_lang_switch($slug, $lang) ?>

    <?php kt_convert_widget(['cat' => $cat, 'from' => $from, 'to' => $to, 'val' => $value ?? 1, 'lang' => $lang, 'names' => $names, 'sym' => $sym]); ?>

    <article class="kt-card p-5 mb-6 leading-relaxed space-y-4">
      <section>
        <h2 class="section-title text-xl mb-2"><?= e($gu ? "{$L['F']}માંથી {$L['T']} કેવી રીતે કરવું?" : "{$L['F']} से {$L['T']} कैसे बदलें?") ?></h2>
        <p><?= e($L['intro']) ?></p>
        <p class="mt-2 text-sm opacity-80"><?= $L['hing_line'] ?></p>
      </section>
      <section>
        <h2 class="section-title text-xl mb-2"><?= e($gu ? 'સૂત્ર' : 'सूत्र') ?></h2>
        <p class="p-3 rounded-lg font-mono text-center" style="background:rgba(0,0,0,.04)"><?= e($L['formula_text']) ?></p>
        <p class="mt-2"><?= e(($gu ? 'સાદી ભાષામાં: ' : 'आसान भाषा में: ') . $L['rule'] . $dot) ?></p>
      </section>
      <section>
        <h2 class="section-title text-xl mb-2"><?= e($gu ? 'ઉદાહરણ: ગણતરી પગલે પગલે' : 'उदाहरण: क़दम-दर-क़दम हिसाब') ?></h2>
        <?php foreach ($L['examples'] as $i => $ex): ?>
          <h3 class="font-bold mt-3"><?= ($gu ? 'ઉદાહરણ ' : 'उदाहरण ') . ($i + 1) ?>: <?= e($ex['title']) ?></h3>
          <ol class="list-decimal ml-6 mt-1 space-y-0.5"><?php foreach ($ex['steps'] as $st): ?><li><?= $st ?></li><?php endforeach; ?></ol>
        <?php endforeach; ?>
      </section>
      <?php if ($L['about_from']): ?><section>
        <h2 class="section-title text-xl mb-2"><?= e($gu ? "{$L['F1']} એટલે શું?" : "{$L['F']} क्या है?") ?></h2>
        <p><?= e($L['about_from']) ?></p>
      </section><?php endif; ?>
      <?php if ($L['about_to']): ?><section>
        <h2 class="section-title text-xl mb-2"><?= e($gu ? "{$L['T']} એટલે શું?" : "{$L['T']} क्या है?") ?></h2>
        <p><?= e($L['about_to']) ?></p>
      </section><?php endif; ?>
      <section>
        <h2 class="section-title text-xl mb-2"><?= e($gu ? "{$L['T']}માંથી {$L['F']} (ઊંધું)" : "{$L['T']} से {$L['F']} (उल्टा)") ?></h2>
        <p><?= e("1 {$L['T']} = {$L['back']} {$L['F']}" . ($gu ? '. એટલે કે ' : '। यानी ') . $L['rev_rule'] . ($gu ? '.' : '।')) ?>
          <?php if ($rev): ?> <a class="underline" href="<?= e($rev) ?>"><?= e($gu ? "{$L['T']} થી {$L['F']} કન્વર્ટર →" : "{$L['T']} से {$L['F']} कन्वर्टर →") ?></a><?php endif; ?></p>
      </section>
    </article>

    <div class="kt-card p-5 mb-6">
      <h2 class="section-title text-xl mb-1"><?= e($gu ? "{$L['F']} થી {$L['T']} ટેબલ" : "{$L['F']} से {$L['T']} टेबल") ?></h2>
      <div class="divider-gold mb-4"></div>
      <div class="overflow-x-auto"><table class="w-full text-sm">
        <thead><tr class="opacity-60 text-left"><th class="py-2"><?= e($L['F']) ?></th><th><?= e($L['T']) ?></th></tr></thead>
        <tbody>
        <?php foreach ($L['table'] as $tv): $isCur = $value !== null && abs($tv - $value) < 1e-9; $link = !$isCur ? ($numLinks[(string) $tv] ?? ($numLinks[(int) $tv] ?? null)) : null; if ($link && abs($tv - round($tv)) > 1e-9) $link = null; ?>
          <tr class="border-t" style="border-color:var(--border)<?= $isCur ? ';background:rgba(5,150,105,.08);font-weight:700' : '' ?>">
            <td class="py-2"><?php if ($link): ?><a class="hover:text-[color:var(--saffron)]" href="<?= e($link) ?>"><?= gen_fmt((float) $tv) ?> <?= e($L['F']) ?></a><?php else: ?><?= gen_fmt((float) $tv) ?> <?= e($L['F']) ?><?php endif; ?></td>
            <td><span data-cv="<?= e($tv) ?>"><?= gen_fmt(gen_convert($cat, (float) $tv, $from, $to)) ?></span> <?= e($L['T']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table></div>
    </div>

    <?php if ($numLinks): ?>
    <div class="kt-card p-5 mb-6">
      <h2 class="section-title text-xl mb-1"><?= e($gu ? "બીજા આંકડા: {$L['F']} થી {$L['T']}" : "और आँकड़े: {$L['F']} से {$L['T']}") ?></h2>
      <div class="divider-gold mb-3"></div>
      <div class="flex flex-wrap gap-1.5 text-xs">
        <?php foreach ($numLinks as $nv => $u): ?><a class="px-2 py-1 rounded-md border hover:border-[color:var(--gold)]" style="border-color:var(--border)" href="<?= e($u) ?>"><?= $nv ?> <?= e($L['F']) ?></a><?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="kt-card p-5 mb-6">
      <h2 class="section-title text-xl mb-1"><?= e($gu ? 'સવાલ-જવાબ' : 'सवाल-जवाब') ?></h2>
      <div class="divider-gold mb-2"></div>
      <?php foreach ($faq as $qa): ?>
        <div class="faq-item border-b" style="border-color:var(--border)">
          <div class="faq-q" onclick="this.parentElement.classList.toggle('open')"><span><?= e($qa[0]) ?></span><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
          <div class="faq-a"><p class="pb-3"><?= e($qa[1]) ?></p></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <aside>
    <div class="kt-card p-5 sticky top-20">
      <h2 class="font-bold mb-3"><?= e($gu ? 'બીજા કન્વર્ટર' : 'और कन्वर्टर') ?></h2>
      <div class="space-y-2 text-sm">
        <?php if ($pairUrl): ?><a href="<?= e($pairUrl) ?>" class="block p-2 rounded-lg hover:bg-[color:var(--gold)]/10"><?= e($gu ? "{$L['F']} થી {$L['T']} (કોઈ પણ આંકડો)" : "{$L['F']} से {$L['T']} (कोई भी संख्या)") ?></a><?php endif; ?>
        <?php if ($rev): ?><a href="<?= e($rev) ?>" class="block p-2 rounded-lg hover:bg-[color:var(--gold)]/10"><?= e($gu ? "{$L['T']} થી {$L['F']}" : "{$L['T']} से {$L['F']}") ?></a><?php endif; ?>
        <?php foreach ($morePairs as $s => $label): ?><a href="<?= e($base . $s) ?>" class="block p-2 rounded-lg hover:bg-[color:var(--gold)]/10"><?= e($label) ?></a><?php endforeach; ?>
        <a href="<?= SITE_URL ?>/converters.php#<?= e($cat) ?>" class="block p-2 rounded-lg text-[color:var(--peacock-teal)]"><?= e($gu ? 'બધા કન્વર્ટર (English) →' : 'सभी कन्वर्टर (English) →') ?></a>
      </div>
    </div>
  </aside>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
