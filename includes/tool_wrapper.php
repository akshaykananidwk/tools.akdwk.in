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

/** Look up the DB row for a tool slug (for id + admin toggles). */
function tool_db_row(string $slug): ?array {
    try {
        return one("SELECT * FROM " . tbl('tools') . " WHERE slug = :s", [':s' => $slug]);
    } catch (Throwable $e) { return null; }
}

/**
 * Render a complete tool page for $slug. Handles access, chrome, JSON-LD and
 * usage logging. Dies with a 404 view if the slug is unknown.
 */
function render_tool_page(string $slug): void {
    $tool = kt_tool($slug);
    if (!$tool) { render_tool_404(); return; }

    $db = tool_db_row($slug);
    if ($db && (int) $db['is_active'] === 0) { render_tool_404(); return; }
    $tool['db_id'] = $db['id'] ?? 0;

    $cat = null;
    foreach (kt_categories() as $c) if ($c['slug'] === $tool['cat']) $cat = $c;

    // Log usage (best-effort) + bump view count.
    try {
        if (!empty($tool['db_id'])) {
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

    $page_title = $name . ' — ' . setting('site_name', 'કૃષ્ણા ટૂલ્સ');
    $page_desc  = $desc;
    $breadcrumb = [
        ['label' => t('home'), 'url' => SITE_URL . '/'],
        ['label' => loc($cat ?? [], 'name'), 'url' => SITE_URL . '/category/' . $tool['cat']],
        ['label' => $name],
    ];

    // Schema.org SoftwareApplication + FAQ JSON-LD.
    $faqLd = ['@type' => 'FAQPage', 'mainEntity' => []];
    foreach (($tool['faq'] ?? []) as $qa) {
        $faqLd['mainEntity'][] = ['@type' => 'Question', 'name' => $qa[0],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $qa[1]]];
    }
    $ld = json_encode(['@context' => 'https://schema.org', '@graph' => [
        ['@type' => 'SoftwareApplication', 'name' => $name, 'applicationCategory' => 'UtilitiesApplication',
         'operatingSystem' => 'Web', 'description' => $desc,
         'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'INR']],
        $faqLd,
    ]], JSON_UNESCAPED_UNICODE);
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
                <?php if (!empty($tool['is_premium'])): ?><span class="badge-premium align-middle">PRO</span><?php endif; ?></h1>
              <p class="opacity-70 mt-1"><?= e(loc($tool, 'name') === $name ? ($tool['name_en'] ?? '') : '') ?><?= e($desc) ?></p>
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
          <div id="toolMount"
               data-engine="<?= e($tool['engine']) ?>"
               data-slug="<?= e($tool['slug']) ?>"
               data-toolid="<?= (int) ($tool['db_id'] ?? 0) ?>"
               data-premium="<?= (int) !empty($tool['is_premium']) ?>"
               data-allowed="<?= $access['allowed'] ? 1 : 0 ?>"
               data-watermark="<?= $access['watermark'] ? 1 : 0 ?>"
               data-maxmb="<?= (int) $access['max_mb'] ?>"
               data-opt='<?= e(json_encode($tool['opt'] ?? new stdClass, JSON_UNESCAPED_UNICODE)) ?>'>
            <div class="text-center py-8"><div class="chakra-loader mx-auto"></div>
              <p class="opacity-60 mt-3"><?= t('processing') ?></p></div>
          </div>

          <!-- Action bar (engines wire these up) -->
          <div id="toolActions" class="flex flex-wrap gap-2 mt-4"></div>
        </div>

        <!-- How to use -->
        <div class="kt-card p-5 mb-6">
          <h2 class="section-title text-xl mb-1"><?= t('how_to_use') ?></h2>
          <div class="divider-gold mb-4"></div>
          <ol class="space-y-2">
            <?php foreach (($tool['guide'] ?? []) as $i => $step): ?>
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
          <?php foreach (($tool['faq'] ?? []) as $qa): ?>
            <div class="faq-item border-b" style="border-color:var(--border)">
              <div class="faq-q" onclick="this.parentElement.classList.toggle('open')">
                <span><?= e($qa[0]) ?></span><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
              <div class="faq-a"><p class="pb-3"><?= e($qa[1]) ?></p></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Related tools sidebar -->
      <aside>
        <div class="kt-card p-5 sticky top-20">
          <h2 class="font-bold mb-3"><?= t('related_tools') ?></h2>
          <div class="space-y-2">
            <?php foreach (kt_related($slug, 5) as $r): ?>
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

function render_tool_404(): void {
    http_response_code(404);
    $page_title = '404 — ' . setting('site_name', 'કૃષ્ણા ટૂલ્સ');
    require __DIR__ . '/header.php';
    echo '<div class="kt-card p-10 text-center mt-10 max-w-lg mx-auto">
      <div class="text-6xl mb-3">🦚</div>
      <h1 class="text-2xl font-bold mb-2">ટૂલ મળ્યું નહીં</h1>
      <p class="opacity-70 mb-5">This tool was not found.</p>
      <a href="' . SITE_URL . '/" class="btn btn-primary">' . t('home') . '</a></div>';
    require __DIR__ . '/footer.php';
}
