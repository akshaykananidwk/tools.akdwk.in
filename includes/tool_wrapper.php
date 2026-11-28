<?php
/**
 * KRISHNA TOOLS — generic tool page renderer.
 *
 * One router (tool.php) + this wrapper turns any registry slug into a full,
 * SEO-complete, working tool page. The interactive UI + logic is supplied by
 * the matching engine in /assets/js/app.js (keyed by the tool's `engine`),
 * so every tool shares the same chrome while remaining fully functional.
 */
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/ratelimit.php';
require_once __DIR__ . '/tools_i18n.php';
require_once __DIR__ . '/seo.php';

/**
 * Which engines file registers an engine name (so a tool page loads one JS file, not all five).
 * Built by scanning reg('name' / regCalc('name' in assets/js/engines*.js; cached per request.
 */
function kt_engine_file(string $engine): string {
    static $map = null;
    if ($map === null) {
        $map = [];
        foreach (glob(dirname(__DIR__) . '/assets/js/engines*.js') ?: [] as $f) {
            if (preg_match_all("/(?:^|[^A-Za-z_])(?:reg|regCalc)\('([a-z0-9-]+)'/m", (string) @file_get_contents($f), $m)) {
                foreach ($m[1] as $name) $map[$name] = $map[$name] ?? basename($f);
            }
        }
    }
    return $map[$engine] ?? '';
}

/** Look up the DB row for a tool slug (for id + admin toggles). */
function tool_db_row(string $slug): ?array {
    try {
        return one("SELECT * FROM " . tbl('tools') . " WHERE slug = :s", [':s' => $slug]);
    } catch (Throwable $e) { return null; }
}

/**
 * Tools added to the registry after install (e.g. the industry packs) have no
 * DB row yet. Create it on first visit so usage stats and admin toggles work
 * without re-running the installer. INSERT IGNORE keeps concurrent hits safe.
 */
function kt_ensure_tool_row(array $tool): ?array {
    try {
        $cat = one("SELECT id FROM " . tbl('categories') . " WHERE slug = :s", [':s' => $tool['cat']]);
        if (!$cat) {
            foreach (kt_categories() as $c) {
                if ($c['slug'] !== $tool['cat']) continue;
                q("INSERT IGNORE INTO " . tbl('categories') . " (name_en, name_gu, slug, icon, color, sort_order)
                   VALUES (:en, :gu, :slug, :icon, :color, :so)",
                  [':en' => $c['name_en'], ':gu' => $c['name_gu'], ':slug' => $c['slug'], ':icon' => $c['icon'],
                   ':color' => $c['color'], ':so' => $c['sort_order']]);
            }
            $cat = one("SELECT id FROM " . tbl('categories') . " WHERE slug = :s", [':s' => $tool['cat']]);
        }
        q("INSERT IGNORE INTO " . tbl('tools') . " (name_en, name_gu, slug, category_id, icon, description_gu, description_en, is_premium, is_active, sort_order)
           VALUES (:en, :gu, :slug, :cat, :icon, :dg, :de, 0, 1, 1000)",
          [':en' => $tool['name_en'], ':gu' => $tool['name_gu'], ':slug' => $tool['slug'], ':cat' => $cat['id'] ?? null,
           ':icon' => $tool['icon'], ':dg' => $tool['desc_gu'], ':de' => $tool['desc_en']]);
        return tool_db_row($tool['slug']);
    } catch (Throwable $e) { return null; }
}

/**
 * Render a complete tool page for $slug. Handles access, chrome, JSON-LD and
 * usage logging. Dies with a 404 view if the slug is unknown.
 */
/**
 * Space to reserve for the tool UI before its JavaScript draws it, so the guide/FAQ below don't jump
 * (CLS). Estimated from the number of fields/results (fitted on real mobile + desktop heights) and
 * kept at ~90% so a page never shows extra empty space. Returns [mobilePx, desktopPx].
 */
function kt_tool_reserve(array $tool): array {
    $in = count($tool['opt']['in'] ?? []); $out = count($tool['opt']['out'] ?? []);
    switch ($tool['engine']) {
        case 'formula': return [(int) (0.9 * (22 + 89 * $in + 95 * $out)), (int) (0.9 * (121 + 40 * $in + 52 * $out))];
        case 'textgen': return [(int) (0.85 * (899 + 132 * $in)), (int) (0.85 * (725 + 64 * $in))];
        case 'card':    return [850, 550];
        case 'doc':     return [1500, 900];
        default:        return [140, 140];
    }
}

function render_tool_page(string $slug): void {
    $tool = kt_tool($slug);
    if (!$tool) { render_tool_404(); return; }

    $db = tool_db_row($slug) ?? kt_ensure_tool_row($tool);
    if ($db && (int) $db['is_active'] === 0) { render_tool_404(); return; }
    $tool['db_id'] = $db['id'] ?? 0;

    $cat = null;
    foreach (kt_categories() as $c) if ($c['slug'] === $tool['cat']) $cat = $c;

    // Log usage (best-effort) + bump view count. Crawlers are skipped so Google
    // indexing hundreds of pages does not look like visitors.
    require_once __DIR__ . '/analytics.php';
    try {
        if (!empty($tool['db_id']) && !kt_is_bot_ua($_SERVER['HTTP_USER_AGENT'] ?? '')) {
            insert('tool_usage', [
                'user_id'    => current_user()['id'] ?? null,
                'tool_id'    => $tool['db_id'],
                'ip'         => client_ip(),
                'user_agent' => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            q("UPDATE " . tbl('tools') . " SET views = views + 1 WHERE id = :id", [':id' => $tool['db_id']]);
        }
    } catch (Throwable $e) { /* ignore */ }

    $access = tool_access($tool);
    $name   = loc($tool, 'name');
    $desc   = loc($tool, 'desc');

    $en = current_lang() === 'en';
    $kw = kt_tool_keywords($tool);
    $page_title = kt_seo_title($tool);
    $page_desc  = kt_seo_desc($tool);
    $page_canonical = SITE_URL . '/tool/' . $tool['slug'];
    $page_keywords = implode(', ', $kw);
    $about = kt_tool_about($tool, $kw);
    $breadcrumb = [
        ['label' => t('home'), 'url' => SITE_URL . '/'],
        ['label' => loc($cat ?? [], 'name'), 'url' => SITE_URL . '/category/' . $tool['cat']],
        ['label' => $name],
    ];

    // Language-aware guide + FAQ (English content for en, Gujarati otherwise).
    $guide = tool_guide($tool);
    $faq   = tool_faq($tool);
    // Long guide for the most-searched tools (who it is for, example, tips, extra FAQ, last-updated date).
    require_once __DIR__ . '/tool_landing.php';
    $landing = kt_tool_landing($tool['slug']);
    if ($landing) $faq = array_merge($faq, $landing['faq']);

    // Schema.org SoftwareApplication + FAQ JSON-LD.
    $faqLd = ['@type' => 'FAQPage', 'mainEntity' => []];
    foreach ($faq as $qa) {
        $faqLd['mainEntity'][] = ['@type' => 'Question', 'name' => $qa[0],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $qa[1]]];
    }
    // BreadcrumbList from the page breadcrumb trail.
    $crumbLd = ['@type' => 'BreadcrumbList', 'itemListElement' => []];
    foreach ($breadcrumb as $i => $bc) {
        $item = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $bc['label']];
        if (!empty($bc['url'])) $item['item'] = $bc['url'];
        $crumbLd['itemListElement'][] = $item;
    }
    $ld = json_encode(['@context' => 'https://schema.org', '@graph' => [
        ['@type' => 'WebApplication', 'name' => $name, 'applicationCategory' => 'UtilitiesApplication', 'browserRequirements' => 'Requires JavaScript. Works in any modern browser.',
         'operatingSystem' => 'Web, Android, iOS', 'description' => $desc, 'keywords' => implode(', ', array_slice($kw, 0, 10)),
         'isAccessibleForFree' => true,
         'url' => SITE_URL . '/tool/' . $tool['slug'], 'inLanguage' => current_lang() === 'en' ? 'en' : 'gu',
         'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'INR']],
        $faqLd,
        $crumbLd,
    ]], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $extra_head = '<script type="application/ld+json">' . $ld . '</script>';

    require __DIR__ . '/header.php';
    ?>
    <div class="grid lg:grid-cols-[1fr,300px] gap-6 mt-4">
      <div>
        <!-- Tool header -->
        <div class="kt-card p-5 mb-5">
          <div class="flex items-start gap-3">
            <div class="tool-card"><div class="icon-wrap w-12 h-12 rounded-xl flex items-center justify-center shrink-0">
              <i data-lucide="<?= e($tool['icon']) ?>" class="w-6 h-6"></i></div></div>
            <div>
              <h1 class="text-2xl md:text-3xl font-extrabold" style="color:var(--krishna-blue)"><?= e($name) ?>
                <?php if (!empty($tool['is_premium']) && !kt_all_free()): ?><span class="badge-premium align-middle">PRO</span><?php endif; ?></h1>
              <?php if (!$en && !empty($tool['name_en']) && $tool['name_en'] !== $name): ?><p class="text-sm opacity-60"><?= e($tool['name_en']) ?></p><?php endif; ?>
              <p class="opacity-70 mt-1"><?= e($desc) ?></p>
            </div>
          </div>
        </div>

        <?php if (!$access['allowed']): ?>
          <div class="kt-card p-6 mb-5 border-2" style="border-color:var(--saffron)">
            <div class="flex items-center gap-2 text-lg font-bold" style="color:var(--saffron)">
              <i data-lucide="lock" class="w-5 h-5"></i> <?= e($access['reason']) ?></div>
            <p class="opacity-70 mt-2"><?= t('watermark_note') ?></p>
            <a href="<?= SITE_URL ?>/pricing.php" class="btn btn-cta mt-4"><i data-lucide="gem" class="w-4 h-4"></i> <?= t('upgrade') ?></a>
          </div>
        <?php endif; ?>

        <!-- Tool mount: the JS engine renders the interactive UI here -->
        <div class="kt-card p-5 mb-6">
          <?php [$__mh, $__md] = kt_tool_reserve($tool); ?>
          <div id="toolMount" style="--mh:<?= $__mh ?>px;--md:<?= $__md ?>px"
               data-engine="<?= e($tool['engine']) ?>"
               data-src="<?= e(kt_engine_file($tool['engine'])) ?>"
               data-slug="<?= e($tool['slug']) ?>"
               data-cat="<?= e($tool['cat']) ?>"
               data-toolid="<?= (int) ($tool['db_id'] ?? 0) ?>"
               data-premium="<?= (int) !empty($tool['is_premium']) ?>"
               data-allowed="<?= $access['allowed'] ? 1 : 0 ?>"
               data-watermark="<?= $access['watermark'] ? 1 : 0 ?>"
               data-maxmb="<?= (int) $access['max_mb'] ?>"
               data-opt='<?= e(json_encode(kt_strip_gu($tool['opt'] ?? []) ?: new stdClass, JSON_UNESCAPED_UNICODE)) ?>'>
            <div class="text-center py-8"><div class="chakra-loader mx-auto"></div>
              <p class="opacity-60 mt-3"><?= t('processing') ?></p></div>
          </div>

          <!-- Action bar (engines wire these up) -->
          <div id="toolActions" class="flex flex-wrap gap-2 mt-4"></div>
        </div>

        <?php if ($landing) kt_render_tool_landing($landing); ?>

        <!-- How to use -->
        <div class="kt-card p-5 mb-6">
          <h2 class="section-title text-xl mb-1"><?= t('how_to_use') ?></h2>
          <div class="divider-gold mb-4"></div>
          <ol class="space-y-2">
            <?php foreach ($guide as $i => $step): ?>
              <li class="flex gap-3 items-start">
                <span class="shrink-0 w-6 h-6 rounded-full text-white text-sm flex items-center justify-center" style="background:var(--peacock-teal)"><?= $i + 1 ?></span>
                <span><?= e($step) ?></span></li>
            <?php endforeach; ?>
          </ol>
        </div>

        <!-- FAQ -->
        <div class="kt-card p-5 mb-6">
          <h2 class="section-title text-xl mb-1"><?= t('faq') ?></h2>
          <div class="divider-gold mb-2"></div>
          <?php foreach ($faq as $qa): ?>
            <div class="faq-item border-b" style="border-color:var(--border)">
              <div class="faq-q" onclick="this.parentElement.classList.toggle('open')">
                <span><?= e($qa[0]) ?></span><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
              <div class="faq-a"><p class="pb-3"><?= e($qa[1]) ?></p></div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- About + the phrases people search for (visible text helps Google match real queries) -->
        <div class="kt-card p-5 mb-6">
          <h2 class="section-title text-xl mb-1">About <?= e($name) ?></h2>
          <div class="divider-gold mb-3"></div>
          <p class="opacity-80 leading-relaxed"><?= e($about) ?></p>
          <?php if ($kw): ?>
          <h3 class="font-bold mt-4 mb-2 text-sm">People also search for</h3>
          <div class="flex flex-wrap gap-2">
            <?php foreach ($kw as $k): ?><span class="text-xs px-2 py-1 rounded-full border" style="border-color:var(--border)"><?= e($k) ?></span><?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      <?php // Embed code: other sites can show this tool in an iframe, with a credit link back here.
        $__embed = '<iframe src="' . SITE_URL . '/embed/' . $tool['slug'] . '" title="' . $name . ' — Krishna Tools" width="100%" height="' . max(600, $__md + 260) . '" style="border:0;max-width:100%" loading="lazy"></iframe>' . "\n"
            . '<p style="font-size:13px">Free <a href="' . SITE_URL . '/tool/' . $tool['slug'] . '">' . $name . '</a> by Krishna Tools</p>'; ?>
        <details class="kt-card p-5 mb-6" id="embedTool">
          <summary class="font-bold cursor-pointer"><i data-lucide="code" class="w-4 h-4 inline"></i> Embed this tool on your website</summary>
          <p class="text-sm opacity-80 mt-3">Copy this code into any web page, blog or WordPress "Custom HTML" block. The tool works inside your page, free, with a small credit link to Krishna Tools.</p>
          <textarea readonly rows="4" class="kt-input font-mono text-xs mt-2" id="embedCode" aria-label="Embed code"><?= e($__embed) ?></textarea>
          <button type="button" class="btn btn-ghost text-sm mt-2" data-embed-copy><i data-lucide="copy" class="w-4 h-4"></i> Copy embed code</button>
          <a class="text-sm underline ml-2" href="<?= SITE_URL ?>/embed/<?= e($tool['slug']) ?>" target="_blank" rel="noopener nofollow">Preview</a>
        </details>
      </div>

      <!-- Related tools sidebar -->
      <aside>
        <div class="kt-card p-5 sticky top-20">
          <h2 class="font-bold mb-3"><?= t('related_tools') ?></h2>
          <div class="space-y-2">
            <?php foreach (kt_related_smart($slug, 8) as $r): ?>
              <a href="<?= SITE_URL ?>/tool/<?= e($r['slug']) ?>" class="flex items-center gap-2 p-2 rounded-lg hover:bg-[color:var(--gold)]/10">
                <i data-lucide="<?= e($r['icon']) ?>" class="w-4 h-4 opacity-70"></i>
                <span class="text-sm"><?= e(loc($r, 'name')) ?></span></a>
            <?php endforeach; ?>
          </div>
        </div>
      </aside>
    </div>
    <?php
    require __DIR__ . '/footer.php';
}

/**
 * The site is English-only: drop the old Gujarati copy (keys "gu", "*_gu", and the third
 * Gujarati label of select options) from a tool's settings before they go into the page.
 */
function kt_strip_gu($v) {
    if (!is_array($v)) return $v;
    $out = [];
    foreach ($v as $k => $x) {
        if (is_string($k) && ($k === 'gu' || str_ends_with($k, '_gu'))) continue;
        if ($k === 'opts' && is_array($x)) { $out[$k] = array_map(fn($o) => is_array($o) && array_is_list($o) ? array_slice($o, 0, 2) : kt_strip_gu($o), $x); continue; }
        $out[$k] = is_string($x) ? kt_strip_gu_str($x) : kt_strip_gu($x);
    }
    return $out;
}

/** In formula strings T("ગુજરાતી", "English") picks the English text, so the Gujarati half is emptied; any other Gujarati/Hindi string is emptied too. */
function kt_strip_gu_str(string $s): string {
    if (!preg_match('/[\x{0900}-\x{097F}\x{0A80}-\x{0AFF}]/u', $s)) return $s;
    $s = preg_replace('/\bT\(\s*"[^"]*"\s*,/u', 'T("",', $s);
    $s = preg_replace("/\\bT\\(\\s*'[^']*'\\s*,/u", "T('',", $s);
    return preg_match('/[\x{0900}-\x{097F}\x{0A80}-\x{0AFF}]/u', $s) ? '' : $s;
}

function render_tool_404(): void {
    http_response_code(404);
    $page_title = '404 — ' . brand_name();
    require __DIR__ . '/header.php';
    echo '<div class="kt-card p-10 text-center mt-10 max-w-lg mx-auto">
      <div class="text-6xl mb-3">🦚</div>
      <h1 class="text-2xl font-bold mb-2">Tool not found</h1>
      <p class="opacity-70 mb-5">This tool was not found.</p>
      <a href="' . SITE_URL . '/" class="btn btn-primary">' . t('home') . '</a></div>';
    require __DIR__ . '/footer.php';
}
