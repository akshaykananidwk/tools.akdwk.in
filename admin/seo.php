<?php
/**
 * KRISHNA TOOLS — SEO controls.
 * Converter number pages: which ones Google should index beyond the built-in rule
 * (popular pairs × 1–100, 150, 200, 250, 500, 1000). Paste pages that already get
 * impressions in Search Console; they stay indexed and are added to the sitemap.
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/convert_content.php';
require_once __DIR__ . '/../includes/seo_report.php';
require_admin();
require_once __DIR__ . '/layout.php';

$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_report'])) {
    admin_csrf_check();
    $r = kt_seo_report_run(false, 240);
    activity_log((int) current_user()['id'], 'admin_seo_report', $r['checked'] . ' pages');
    $flash = "SEO check finished: {$r['checked']} of {$r['planned']} tool, category and site pages in {$r['seconds']} s. Converter pages are checked by the nightly job.";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_check();
    $raw = (string) ($_POST['keep'] ?? '');
    $ok = []; $bad = 0;
    foreach (preg_split('/[\s,]+/', $raw) as $line) {
        $line = strtolower(trim($line));
        if ($line === '' || str_starts_with($line, 'top') || str_starts_with($line, 'url')) continue;
        if (preg_match('#/convert/([a-z0-9.\-]+)#', $line, $m)) $line = $m[1];
        $p = gen_parse_slug($line);
        if ($p && $p['value'] !== null) $ok[$p['canonical_slug']] = true; else $bad++;
    }
    set_setting('seo_keep_convert', implode("\n", array_keys($ok)));
    activity_log((int) current_user()['id'], 'admin_seo_keep', count($ok) . ' converter pages');
    $flash = count($ok) . ' number pages saved' . ($bad ? " · $bad lines skipped (not a converter number page)" : '') . '.';
}

$keep = (string) setting('seo_keep_convert', '');
$keepCount = $keep === '' ? 0 : count(array_filter(explode("\n", $keep)));
$promotedPairs = 0;
foreach (gen_all_pairs() as $pr) if (gen_is_promoted_pair($pr['from'], $pr['to'])) $promotedPairs++;
$inSitemap = count(gen_sitemap_convert_urls());
$all = gen_total_count();

admin_head('SEO');
?>
<?php if ($flash): ?><div class="kt-card p-3 mb-4 text-sm" style="border-color:var(--gold)"><?= e($flash) ?></div><?php endif; ?>

<div class="grid gap-4 md:grid-cols-4 mb-4">
  <div class="kt-card p-4"><div class="text-xs opacity-60">Converter pages (all)</div><div class="text-2xl font-extrabold"><?= number_format($all['total']) ?></div></div>
  <div class="kt-card p-4"><div class="text-xs opacity-60">In sitemap / indexable</div><div class="text-2xl font-extrabold" style="color:#059669"><?= number_format($inSitemap) ?></div></div>
  <div class="kt-card p-4"><div class="text-xs opacity-60">Pairs with number pages</div><div class="text-2xl font-extrabold"><?= $promotedPairs ?></div></div>
  <div class="kt-card p-4"><div class="text-xs opacity-60">Kept from Search Console</div><div class="text-2xl font-extrabold"><?= $keepCount ?></div></div>
</div>

<?php $report = kt_seo_report_load(); $issues = $report ? kt_seo_issues($report) : []; ?>
<div class="kt-card p-5 mb-4">
  <div class="flex flex-wrap items-center justify-between gap-3">
    <div>
      <h2 class="section-title text-lg">SEO report</h2>
      <p class="text-sm opacity-70"><?php if ($report): ?>Last check: <b><?= e($report['at']) ?></b> — <?= number_format($report['checked']) ?> of <?= number_format($report['planned']) ?> pages<?= $report['full'] ? ' (full: every sitemap URL)' : ' (tools, categories and site pages)' ?>, <?= (int) $report['seconds'] ?> s.<?php else: ?>No check has run yet.<?php endif; ?> The full check runs every night (Admin → Cron jobs → "SEO Report").</p>
    </div>
    <form method="post"><?= csrf_field() ?><input type="hidden" name="run_report" value="1"><button class="btn btn-primary text-sm"><i data-lucide="search-check" class="w-4 h-4"></i> Check tools &amp; pages now</button></form>
  </div>
  <?php if ($report): ?>
  <div class="grid gap-2 md:grid-cols-5 mt-4 text-sm">
    <?php foreach ($issues as $k => [$label, $list]): ?>
      <a href="#iss-<?= e($k) ?>" class="kt-card p-3 block"><div class="text-xs opacity-60"><?= e($label) ?></div><div class="text-xl font-extrabold" style="color:<?= $list ? '#DC2626' : '#059669' ?>"><?= number_format(count($list)) ?></div></a>
    <?php endforeach; ?>
  </div>
  <?php foreach ($issues as $k => [$label, $list]): if (!$list) continue; ?>
    <details class="mt-3" id="iss-<?= e($k) ?>"><summary class="font-semibold cursor-pointer"><?= e($label) ?> (<?= count($list) ?>)</summary>
      <div class="overflow-x-auto"><table class="w-full text-xs mt-2"><?php foreach (array_slice($list, 0, 300, true) as $u => $d): ?>
        <tr class="border-t" style="border-color:var(--border)"><td class="py-1 pr-2"><a class="underline" href="<?= e($u) ?>" target="_blank"><?= e(str_replace(SITE_URL, '', $u)) ?></a></td><td class="opacity-70"><?= e($d) ?></td></tr>
      <?php endforeach; ?></table><?php if (count($list) > 300): ?><p class="text-xs opacity-60 mt-1">… and <?= count($list) - 300 ?> more.</p><?php endif; ?></div>
    </details>
  <?php endforeach; ?>
  <p class="text-xs opacity-60 mt-3">Converter number pages outside the popular list (any number typed into a URL) are <code>noindex</code> on purpose and are not in the sitemap, so they are not listed here.</p>
  <?php endif; ?>
</div>

<form method="post" class="kt-card p-5 space-y-3">
  <?= csrf_field() ?>
  <h2 class="section-title text-lg">Converter number pages to keep in Google</h2>
  <p class="text-sm opacity-70">Number pages are indexed automatically for popular pairs (quintal, tola, mg, mm, kg, gram, Mbps, ton → their usual partner units) for 1–100, 150, 200, 250, 500 and 1000. Every other number page still works for visitors but has <code>noindex</code> and is not in the sitemap.</p>
  <p class="text-sm opacity-70">To keep more: Search Console → Performance → <b>Pages</b> → Export, then paste the page URLs below (one per line, full URLs or just the part after <code>/convert/</code>). These stay indexed and are added to the sitemap.</p>
  <textarea name="keep" class="kt-input font-mono text-xs" rows="12" placeholder="https://tools.akdwk.in/convert/45-quintal-to-kilogram&#10;2.5-kilogram-to-pound"><?= e($keep) ?></textarea>
  <button class="btn btn-primary"><i data-lucide="save" class="w-4 h-4"></i> Save</button>
</form>
<?php admin_foot(); ?>
