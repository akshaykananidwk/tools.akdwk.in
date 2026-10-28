<?php
/**
 * KRISHNA TOOLS — Live Analytics report (admin).
 * Live visitors, daily/monthly totals, date-range filter, cities, tools,
 * pages, sources, devices, top users and a recent activity feed.
 * Data: page_views / live_visitors / ip_geo (see includes/analytics.php).
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/analytics.php';
require_admin();
require_once __DIR__ . '/layout.php';

kt_analytics_ensure();
$PV = tbl('page_views'); $LV = tbl('live_visitors'); $GEO = tbl('ip_geo'); $U = tbl('users');

/** Display name for a slug (tool name, else the slug itself). */
function an_tool_name(?string $slug): string {
    if (!$slug) return '—';
    $t = kt_tool($slug);
    return $t ? loc($t, 'name') : $slug;
}
function an_place(array $r): string {
    $p = array_unique(array_filter([$r['city'] ?? '', $r['country'] ?? ''], fn($x) => $x !== '' && $x !== null));
    if (in_array('Local', $p, true)) return 'લોકલ નેટવર્ક';
    return $p ? implode(', ', $p) : 'શોધાઈ રહ્યું છે…';
}
function an_ago(string $dt): string {
    $s = max(0, time() - strtotime($dt));
    if ($s < 60) return $s . ' સેકન્ડ';
    if ($s < 3600) return floor($s / 60) . ' મિનિટ';
    if ($s < 86400) return floor($s / 3600) . ' કલાક';
    return floor($s / 86400) . ' દિવસ';
}

/* ---------- Live JSON (polled every 15 s by the page) ---------- */
function an_live(): array {
    global $LV, $GEO, $U;
    $rows = all("SELECT lv.*, g.city, g.country, u.name AS uname
                 FROM $LV lv LEFT JOIN $GEO g ON g.ip = lv.ip LEFT JOIN $U u ON u.id = lv.user_id
                 WHERE lv.last_seen >= :t
                 ORDER BY lv.last_seen DESC LIMIT 200", [':t' => date('Y-m-d H:i:s', time() - KT_LIVE_WINDOW)]);
    $out = [];
    foreach ($rows as $r) {
        $out[] = ['who' => $r['uname'] ?: ('મુલાકાતી #' . substr($r['vid'], 0, 6)), 'page' => $r['path'],
            'tool' => $r['slug'] ? an_tool_name($r['slug']) : '', 'place' => an_place($r), 'device' => $r['device'] ?: '',
            'since' => an_ago($r['first_seen']), 'seen' => an_ago($r['last_seen'])];
    }
    return $out;
}
if (isset($_GET['live'])) { json_out(['ok' => true, 'count' => count($l = an_live()), 'rows' => $l, 'at' => date('H:i:s')]); }

/* ---------- Date range ---------- */
$today = date('Y-m-d');
$presets = [
    'today'     => ['આજે', $today, $today],
    'yesterday' => ['ગઈકાલે', date('Y-m-d', strtotime('-1 day')), date('Y-m-d', strtotime('-1 day'))],
    '7d'        => ['છેલ્લા 7 દિવસ', date('Y-m-d', strtotime('-6 days')), $today],
    '30d'       => ['છેલ્લા 30 દિવસ', date('Y-m-d', strtotime('-29 days')), $today],
    'month'     => ['આ મહિનો', date('Y-m-01'), $today],
    'lastmonth' => ['ગયો મહિનો', date('Y-m-01', strtotime('first day of last month')), date('Y-m-t', strtotime('last day of last month'))],
];
$preset = $_GET['range'] ?? (isset($_GET['from']) ? 'custom' : '7d');
if (isset($presets[$preset])) { [, $from, $to] = $presets[$preset]; }
else {
    $from = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['from'] ?? '') ? $_GET['from'] : date('Y-m-d', strtotime('-6 days'));
    $to   = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['to'] ?? '') ? $_GET['to'] : $today;
    if ($from > $to) [$from, $to] = [$to, $from];
    $preset = 'custom';
}
$R = [':a' => $from . ' 00:00:00', ':b' => $to . ' 23:59:59'];
$W = "pv.created_at BETWEEN :a AND :b";

/** Visitors / views / uses between two datetimes. */
function an_totals(string $a, string $b): array {
    global $PV;
    $r = one("SELECT COUNT(DISTINCT vid) AS visitors, SUM(kind = 'view') AS views, SUM(kind = 'use') AS uses
              FROM $PV WHERE created_at BETWEEN :a AND :b", [':a' => $a, ':b' => $b]);
    return ['visitors' => (int) ($r['visitors'] ?? 0), 'views' => (int) ($r['views'] ?? 0), 'uses' => (int) ($r['uses'] ?? 0)];
}
$kToday = an_totals("$today 00:00:00", "$today 23:59:59");
$y = date('Y-m-d', strtotime('-1 day'));
$kYest  = an_totals("$y 00:00:00", "$y 23:59:59");
$kMonth = an_totals(date('Y-m-01') . ' 00:00:00', "$today 23:59:59");
$kRange = an_totals($R[':a'], $R[':b']);
$kAll   = (int) (scalar("SELECT COUNT(DISTINCT vid) FROM $PV") ?? 0);
$live   = an_live();
$newVis = (int) (scalar("SELECT COUNT(*) FROM (SELECT vid, MIN(created_at) f FROM $PV GROUP BY vid HAVING f BETWEEN :a AND :b) x", $R) ?? 0);

/* ---------- Series ---------- */
$daily = []; $d = strtotime($from);
while ($d <= strtotime($to)) { $daily[date('Y-m-d', $d)] = ['v' => 0, 'p' => 0, 'u' => 0]; $d = strtotime('+1 day', $d); }
foreach (all("SELECT DATE(created_at) d, COUNT(DISTINCT vid) v, SUM(kind='view') p, SUM(kind='use') u FROM $PV pv WHERE $W GROUP BY DATE(created_at)", $R) as $r) {
    $daily[$r['d']] = ['v' => (int) $r['v'], 'p' => (int) $r['p'], 'u' => (int) $r['u']];
}
$hourly = array_fill(0, 24, 0);
foreach (all("SELECT HOUR(created_at) h, COUNT(*) c FROM $PV pv WHERE $W AND kind = 'view' GROUP BY HOUR(created_at)", $R) as $r) $hourly[(int) $r['h']] = (int) $r['c'];

/* ---------- Breakdowns ---------- */
$cities = all("SELECT COALESCE(g.city, '') city, COALESCE(g.region, '') region, COALESCE(g.country, '') country,
                      COUNT(DISTINCT pv.vid) visitors, SUM(pv.kind='view') views
               FROM $PV pv LEFT JOIN $GEO g ON g.ip = pv.ip WHERE $W
               GROUP BY g.city, g.region, g.country ORDER BY visitors DESC LIMIT 50", $R);
$countries = all("SELECT COALESCE(g.country, '') country, COUNT(DISTINCT pv.vid) visitors
                  FROM $PV pv LEFT JOIN $GEO g ON g.ip = pv.ip WHERE $W GROUP BY g.country ORDER BY visitors DESC LIMIT 15", $R);
$toolViews = all("SELECT slug, COUNT(*) views, COUNT(DISTINCT vid) visitors FROM $PV pv WHERE $W AND kind='view' AND slug IS NOT NULL AND path LIKE '/tool/%'
                  GROUP BY slug ORDER BY views DESC LIMIT 25", $R);
$toolUses = all("SELECT slug, COUNT(*) uses, COUNT(DISTINCT vid) users FROM $PV pv WHERE $W AND kind='use' AND slug IS NOT NULL
                 GROUP BY slug ORDER BY uses DESC LIMIT 25", $R);
$pages = all("SELECT path, COUNT(*) views, COUNT(DISTINCT vid) visitors FROM $PV pv WHERE $W AND kind='view' GROUP BY path ORDER BY views DESC LIMIT 25", $R);
$refs = all("SELECT COALESCE(ref_host, '') host, COUNT(*) views FROM $PV pv WHERE $W AND kind='view' GROUP BY ref_host ORDER BY views DESC LIMIT 15", $R);
$devices = all("SELECT COALESCE(device, '') device, COUNT(DISTINCT vid) visitors FROM $PV pv WHERE $W GROUP BY device ORDER BY visitors DESC", $R);
$langs = all("SELECT COALESCE(lang, '') lang, COUNT(*) views FROM $PV pv WHERE $W AND kind='view' GROUP BY lang ORDER BY views DESC", $R);
$topUsers = all("SELECT u.id, u.name, u.email, SUM(pv.kind='use') uses, SUM(pv.kind='view') views, MAX(pv.created_at) last
                 FROM $PV pv JOIN $U u ON u.id = pv.user_id WHERE $W GROUP BY u.id, u.name, u.email ORDER BY uses DESC, views DESC LIMIT 20", $R);
$topVisitors = all("SELECT pv.vid, SUM(pv.kind='use') uses, SUM(pv.kind='view') views, COUNT(DISTINCT DATE(pv.created_at)) days, MAX(pv.created_at) last,
                           MAX(g.city) city, MAX(g.country) country, MAX(pv.device) device
                    FROM $PV pv LEFT JOIN $GEO g ON g.ip = pv.ip WHERE $W GROUP BY pv.vid ORDER BY uses DESC, views DESC LIMIT 20", $R);
$recent = all("SELECT pv.*, g.city, g.country, u.name uname FROM $PV pv LEFT JOIN $GEO g ON g.ip = pv.ip LEFT JOIN $U u ON u.id = pv.user_id
               WHERE $W ORDER BY pv.id DESC LIMIT 60", $R);
$lastErr = (string) setting('analytics_last_error', '');
$firstRow = (string) (scalar("SELECT MIN(created_at) FROM $PV") ?? '');
$lastRow  = (string) (scalar("SELECT MAX(created_at) FROM $PV") ?? '');
$geoPending = (int) (scalar("SELECT COUNT(DISTINCT pv.ip) FROM $PV pv LEFT JOIN $GEO g ON g.ip = pv.ip WHERE g.ip IS NULL AND pv.ip IS NOT NULL") ?? 0);

$fmt = fn($n) => number_format((int) $n);
$deviceGu = ['mobile' => '📱 મોબાઇલ', 'desktop' => '💻 કમ્પ્યુટર', 'tablet' => '📲 ટેબ્લેટ', '' => '—'];
$langGu = ['gu' => 'ગુજરાતી', 'en' => 'English', '' => '—'];
$pct = fn($a, $b) => $b ? round(($a - $b) / $b * 100) : null;

admin_head('Live Analytics');
?>
<style>
  .an-kpi b { font-size: 1.9rem; line-height: 1.1; font-variant-numeric: tabular-nums; }
  .an-live-dot { width: .6rem; height: .6rem; border-radius: 50%; background: #16a34a; display: inline-block; box-shadow: 0 0 0 0 rgba(22,163,74,.6); animation: anPulse 1.6s infinite; }
  @keyframes anPulse { 70% { box-shadow: 0 0 0 10px rgba(22,163,74,0); } 100% { box-shadow: 0 0 0 0 rgba(22,163,74,0); } }
  @media (prefers-reduced-motion: reduce) { .an-live-dot { animation: none; } }
  .an-bar { height: .45rem; border-radius: 9px; background: linear-gradient(90deg, var(--peacock-teal), var(--saffron)); }
  .an-scroll { max-height: 460px; overflow: auto; }
  .num { text-align: end; font-variant-numeric: tabular-nums; white-space: nowrap; }
</style>

<!-- Tracking status -->
<div class="kt-card p-4 mb-5 text-sm flex flex-wrap gap-x-6 gap-y-1 items-center">
  <?php if ($firstRow): ?>
    <span>✅ ટ્રેકિંગ ચાલુ છે. ડેટા <b><?= e(date('d M Y, H:i', strtotime($firstRow))) ?></b> થી ગણાય છે.</span>
    <span class="opacity-70">છેલ્લી મુલાકાત: <?= e(an_ago($lastRow)) ?> પહેલાં</span>
  <?php else: ?>
    <span>⏳ હજી કોઈ મુલાકાત નોંધાઈ નથી. ચકાસવા માટે, admin તરીકે લોગિન <b>ન</b> હોય એવા browser કે ફોનમાં (દા.ત. Incognito) વેબસાઈટ ખોલો. 15 સેકન્ડમાં અહીં "અત્યારે લાઇવ" માં દેખાશે.</span>
  <?php endif; ?>
  <?php if ($lastErr): ?><span class="text-red-600">⚠️ છેલ્લી ટ્રેકિંગ ભૂલ: <span class="font-mono text-xs"><?= e($lastErr) ?></span></span><?php endif; ?>
</div>

<!-- Filter -->
<form method="get" class="kt-card p-4 mb-5 flex flex-wrap items-end gap-2">
  <?php foreach ($presets as $k => [$label]): ?>
    <a href="?range=<?= $k ?>" class="btn <?= $preset === $k ? 'btn-primary' : 'btn-ghost' ?> !py-1.5 text-sm"><?= e($label) ?></a>
  <?php endforeach; ?>
  <div class="flex items-end gap-2 ml-auto flex-wrap">
    <label class="text-xs">થી<input type="date" name="from" value="<?= e($from) ?>" class="kt-input !py-1.5"></label>
    <label class="text-xs">સુધી<input type="date" name="to" value="<?= e($to) ?>" class="kt-input !py-1.5"></label>
    <button class="btn btn-cta !py-1.5 text-sm">ફિલ્ટર</button>
  </div>
</form>

<!-- KPIs -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
  <div class="kt-card kpi p-5 an-kpi border-2" style="border-color:#16a34a">
    <div class="text-sm opacity-70 flex items-center gap-2"><span class="an-live-dot"></span> અત્યારે લાઇવ</div>
    <b id="anLiveCount"><?= count($live) ?></b><div class="text-xs opacity-60">છેલ્લી <?= KT_LIVE_WINDOW ?> સેકન્ડમાં સક્રિય</div>
  </div>
  <div class="kt-card kpi p-5 an-kpi"><div class="text-sm opacity-70">આજના મુલાકાતીઓ</div><b><?= $fmt($kToday['visitors']) ?></b>
    <div class="text-xs opacity-60"><?= $fmt($kToday['views']) ?> વ્યૂ · <?= $fmt($kToday['uses']) ?> ટૂલ વપરાશ
      <?php if (($p = $pct($kToday['visitors'], $kYest['visitors'])) !== null): ?> · <span style="color:<?= $p >= 0 ? '#16a34a' : '#dc2626' ?>"><?= $p >= 0 ? '▲' : '▼' ?><?= abs($p) ?>%</span><?php endif; ?></div></div>
  <div class="kt-card kpi p-5 an-kpi"><div class="text-sm opacity-70">ગઈકાલે</div><b><?= $fmt($kYest['visitors']) ?></b>
    <div class="text-xs opacity-60"><?= $fmt($kYest['views']) ?> વ્યૂ · <?= $fmt($kYest['uses']) ?> ટૂલ વપરાશ</div></div>
  <div class="kt-card kpi p-5 an-kpi"><div class="text-sm opacity-70">આ મહિને</div><b><?= $fmt($kMonth['visitors']) ?></b>
    <div class="text-xs opacity-60"><?= $fmt($kMonth['views']) ?> વ્યૂ · <?= $fmt($kMonth['uses']) ?> ટૂલ વપરાશ</div></div>
</div>

<div class="kt-card p-4 mb-5 flex flex-wrap gap-x-8 gap-y-2 text-sm">
  <div><span class="opacity-60">પસંદ કરેલ સમય (<?= e(date('d M Y', strtotime($from))) ?> – <?= e(date('d M Y', strtotime($to))) ?>):</span></div>
  <div>મુલાકાતીઓ <b><?= $fmt($kRange['visitors']) ?></b></div>
  <div>નવા મુલાકાતીઓ <b><?= $fmt($newVis) ?></b></div>
  <div>પેજ વ્યૂ <b><?= $fmt($kRange['views']) ?></b></div>
  <div>ટૂલ વપરાશ <b><?= $fmt($kRange['uses']) ?></b></div>
  <div>વ્યૂ / મુલાકાતી <b><?= $kRange['visitors'] ? number_format($kRange['views'] / $kRange['visitors'], 1) : '0' ?></b></div>
  <div class="opacity-60">શરૂઆતથી કુલ મુલાકાતીઓ <b><?= $fmt($kAll) ?></b></div>
</div>

<!-- Live list -->
<div class="kt-card p-5 mb-5">
  <div class="flex items-center gap-2 mb-3 flex-wrap">
    <h2 class="section-title text-base m-0"><span class="an-live-dot"></span> અત્યારે વેબસાઈટ પર કોણ છે</h2>
    <span class="text-xs opacity-60 ml-auto" id="anLiveAt">દર 15 સેકન્ડે આપોઆપ અપડેટ</span>
  </div>
  <div class="overflow-x-auto an-scroll">
    <table class="kt-table"><thead><tr><th>મુલાકાતી</th><th>અત્યારનું પેજ / ટૂલ</th><th>શહેર</th><th>ડિવાઇસ</th><th>સાઇટ પર</th><th>છેલ્લે સક્રિય</th></tr></thead>
      <tbody id="anLiveBody"></tbody></table>
  </div>
</div>

<!-- Charts -->
<div class="grid lg:grid-cols-3 gap-5 mb-5">
  <div class="kt-card p-5 lg:col-span-2 min-w-0">
    <h2 class="section-title text-base mb-3">દિવસ પ્રમાણે મુલાકાતીઓ અને વ્યૂ</h2>
    <canvas id="anDaily" height="120"></canvas>
  </div>
  <div class="kt-card p-5 min-w-0">
    <h2 class="section-title text-base mb-3">કલાક પ્રમાણે વ્યૂ</h2>
    <canvas id="anHourly" height="180"></canvas>
  </div>
</div>

<?php
/** Render a ranked table with a proportional bar in the first numeric column. */
function an_table(string $title, array $heads, array $rows, callable $cells, string $note = ''): void {
    $num = fn($s) => (int) preg_replace('/\D/', '', strip_tags((string) $s));
    $max = 1; foreach ($rows as $r) $max = max($max, $num(array_values($cells($r))[1]));
    echo '<div class="kt-card p-5 min-w-0"><h2 class="section-title text-base mb-1">' . e($title) . '</h2>';
    if ($note) echo '<p class="text-xs opacity-60 mb-2">' . $note . '</p>';
    echo '<div class="overflow-x-auto an-scroll"><table class="kt-table"><thead><tr>';
    foreach ($heads as $i => $h) echo '<th class="' . ($i ? 'num' : '') . '">' . e($h) . '</th>';
    echo '</tr></thead><tbody>';
    if (!$rows) echo '<tr><td colspan="' . count($heads) . '" class="opacity-60">આ સમયમાં હજી ડેટા નથી.</td></tr>';
    foreach ($rows as $r) {
        $c = array_values($cells($r));
        echo '<tr><td><div>' . $c[0] . '</div><div class="an-bar mt-1" style="width:' . max(2, round($num($c[1]) / $max * 100)) . '%"></div></td>';
        for ($i = 1; $i < count($c); $i++) echo '<td class="num">' . $c[$i] . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table></div></div>';
}
?>

<div class="grid lg:grid-cols-2 gap-5 mb-5">
  <?php an_table('શહેર પ્રમાણે મુલાકાતીઓ', ['શહેર', 'મુલાકાતીઓ', 'વ્યૂ'], $cities,
      fn($r) => [e($r['city'] !== '' ? $r['city'] . ($r['region'] ? ', ' . $r['region'] : '') : 'શોધાઈ રહ્યું છે…') . ' <span class="opacity-50 text-xs">' . e($r['country']) . '</span>', $fmt($r['visitors']), $fmt($r['views'])],
      $geoPending ? "{$geoPending} નવા IP નું શહેર હજી શોધવાનું બાકી છે (દર 5 મિનિટે આપોઆપ શોધાય છે)." : ''); ?>
  <?php an_table('દેશ', ['દેશ', 'મુલાકાતીઓ'], $countries, fn($r) => [e($r['country'] ?: 'શોધાઈ રહ્યું છે…'), $fmt($r['visitors'])]); ?>
</div>

<div class="grid lg:grid-cols-2 gap-5 mb-5">
  <?php an_table('સૌથી વધુ વપરાતાં ટૂલ્સ (ખરેખર વાપર્યાં)', ['ટૂલ', 'વપરાશ', 'યુઝર'], $toolUses,
      fn($r) => ['<a class="hover:underline" target="_blank" href="' . SITE_URL . '/tool/' . e($r['slug']) . '">' . e(an_tool_name($r['slug'])) . '</a>', $fmt($r['uses']), $fmt($r['users'])],
      'ગણતરી કરી, ફાઇલ ડાઉનલોડ કરી કે મેસેજ બનાવ્યો હોય ત્યારે ગણાય.'); ?>
  <?php an_table('સૌથી વધુ ખોલાયેલાં ટૂલ પેજ', ['ટૂલ', 'વ્યૂ', 'મુલાકાતીઓ'], $toolViews,
      fn($r) => ['<a class="hover:underline" target="_blank" href="' . SITE_URL . '/tool/' . e($r['slug']) . '">' . e(an_tool_name($r['slug'])) . '</a>', $fmt($r['views']), $fmt($r['visitors'])]); ?>
</div>

<div class="grid lg:grid-cols-2 gap-5 mb-5">
  <?php an_table('સૌથી વધુ જોવાયેલાં પેજ', ['પેજ', 'વ્યૂ', 'મુલાકાતીઓ'], $pages,
      fn($r) => ['<a class="hover:underline font-mono text-xs break-all" target="_blank" href="' . SITE_URL . e($r['path']) . '">' . e($r['path']) . '</a>', $fmt($r['views']), $fmt($r['visitors'])]); ?>
  <?php an_table('મુલાકાતીઓ ક્યાંથી આવ્યા', ['સ્રોત', 'વ્યૂ'], $refs,
      fn($r) => [e($r['host'] ?: 'સીધા / બુકમાર્ક / એપ'), $fmt($r['views'])]); ?>
</div>

<div class="grid lg:grid-cols-2 gap-5 mb-5">
  <?php an_table('ડિવાઇસ', ['ડિવાઇસ', 'મુલાકાતીઓ'], $devices, fn($r) => [$deviceGu[$r['device']] ?? e($r['device']), $fmt($r['visitors'])]); ?>
  <?php an_table('ભાષા', ['ભાષા', 'વ્યૂ'], $langs, fn($r) => [$langGu[$r['lang']] ?? e($r['lang']), $fmt($r['views'])]); ?>
</div>

<div class="grid lg:grid-cols-2 gap-5 mb-5">
  <?php an_table('સૌથી વધુ ટૂલ વાપરતા લોગિન યુઝર', ['યુઝર', 'ટૂલ વપરાશ', 'વ્યૂ', 'છેલ્લે'], $topUsers,
      fn($r) => ['<a class="hover:underline" href="' . SITE_URL . '/admin/users.php?q=' . urlencode($r['email']) . '">' . e($r['name']) . '</a> <span class="opacity-50 text-xs">' . e($r['email']) . '</span>', $fmt($r['uses']), $fmt($r['views']), e(an_ago($r['last'])) . ' પહેલાં'],
      'ફક્ત લોગિન કરેલા યુઝર. બાકીના મુલાકાતીઓ બાજુના ટેબલમાં છે.'); ?>
  <?php an_table('સૌથી સક્રિય મુલાકાતીઓ (લોગિન વગર પણ)', ['મુલાકાતી', 'ટૂલ વપરાશ', 'વ્યૂ', 'દિવસ'], $topVisitors,
      fn($r) => ['#' . e(substr($r['vid'], 0, 6)) . ' <span class="opacity-60 text-xs">' . e(an_place($r)) . ' · ' . ($deviceGu[$r['device'] ?? ''] ?? '') . '</span>', $fmt($r['uses']), $fmt($r['views']), $fmt($r['days'])]); ?>
</div>

<div class="kt-card p-5 mb-5">
  <h2 class="section-title text-base mb-3">તાજેતરની પ્રવૃત્તિ</h2>
  <div class="overflow-x-auto an-scroll">
    <table class="kt-table"><thead><tr><th>સમય</th><th>મુલાકાતી</th><th>શું કર્યું</th><th>શહેર</th><th>ડિવાઇસ</th></tr></thead><tbody>
    <?php if (!$recent): ?><tr><td colspan="5" class="opacity-60">આ સમયમાં હજી ડેટા નથી.</td></tr><?php endif; ?>
    <?php foreach ($recent as $r): ?>
      <tr>
        <td class="whitespace-nowrap text-xs"><?= e(date('d M, H:i:s', strtotime($r['created_at']))) ?></td>
        <td><?= e($r['uname'] ?: '#' . substr($r['vid'], 0, 6)) ?></td>
        <td><?= $r['kind'] === 'use' ? '🛠️ વાપર્યું: <b>' . e(an_tool_name($r['slug'])) . '</b>' : '👁️ ' . ($r['slug'] && str_starts_with($r['path'], '/tool/') ? e(an_tool_name($r['slug'])) : '<span class="font-mono text-xs">' . e($r['path']) . '</span>') ?></td>
        <td><?= e(an_place($r)) ?></td>
        <td><?= $deviceGu[$r['device'] ?? ''] ?? '' ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody></table>
  </div>
</div>

<p class="text-xs opacity-60 mb-6">નોંધ: મુલાકાતીને તેના browser માં રાખેલા random ID થી ઓળખાય છે. એક વ્યક્તિ બે ડિવાઇસ કે browser વાપરે તો બે મુલાકાતી ગણાય. Admin તરીકે લોગિન હો ત્યારે તમારી પોતાની મુલાકાત ગણાતી નથી. Search engine bots ગણાતા નથી. શહેર IP પરથી અંદાજે શોધાય છે; મોબાઇલ નેટવર્ક પર ક્યારેક નજીકનું મોટું શહેર બતાવે.</p>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
  var daily = <?= json_encode($daily) ?>, hourly = <?= json_encode($hourly) ?>;
  var css = getComputedStyle(document.documentElement), teal = '#0E7490', saffron = '#EA580C';
  if (window.Chart) {
    var labels = Object.keys(daily).map(function (d) { var p = d.split('-'); return p[2] + '/' + p[1]; });
    new Chart(document.getElementById('anDaily'), { type: 'line', data: { labels: labels, datasets: [
      { label: 'મુલાકાતીઓ', data: Object.values(daily).map(function (x) { return x.v; }), borderColor: teal, backgroundColor: 'rgba(14,116,144,.12)', fill: true, tension: .3, borderWidth: 2, pointRadius: 2 },
      { label: 'પેજ વ્યૂ', data: Object.values(daily).map(function (x) { return x.p; }), borderColor: saffron, backgroundColor: 'transparent', tension: .3, borderWidth: 2, pointRadius: 2 },
      { label: 'ટૂલ વપરાશ', data: Object.values(daily).map(function (x) { return x.u; }), borderColor: '#7C3AED', backgroundColor: 'transparent', borderDash: [4, 4], tension: .3, borderWidth: 2, pointRadius: 0 }
    ] }, options: { responsive: true, interaction: { mode: 'index', intersect: false }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } });
    new Chart(document.getElementById('anHourly'), { type: 'bar', data: { labels: hourly.map(function (_, h) { return h; }), datasets: [
      { label: 'વ્યૂ', data: hourly, backgroundColor: 'rgba(14,116,144,.7)', borderRadius: 4 } ] },
      options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } });
  }

  // Live panel
  var body = document.getElementById('anLiveBody'), cnt = document.getElementById('anLiveCount'), at = document.getElementById('anLiveAt');
  var dev = { mobile: '📱', desktop: '💻', tablet: '📲' };
  function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
  function paint(rows) {
    cnt.textContent = rows.length;
    body.innerHTML = rows.length ? rows.map(function (r) {
      return '<tr><td>' + esc(r.who) + '</td><td>' + (r.tool ? '<b>' + esc(r.tool) + '</b><br>' : '') + '<span class="font-mono text-xs opacity-70 break-all">' + esc(r.page) + '</span></td><td>' +
        esc(r.place) + '</td><td>' + (dev[r.device] || '') + '</td><td class="whitespace-nowrap">' + esc(r.since) + '</td><td class="whitespace-nowrap">' + esc(r.seen) + ' પહેલાં</td></tr>';
    }).join('') : '<tr><td colspan="6" class="opacity-60">અત્યારે કોઈ લાઇવ નથી.</td></tr>';
  }
  paint(<?= json_encode($live, JSON_UNESCAPED_UNICODE) ?>);
  setInterval(function () {
    if (document.hidden) return;
    fetch('?live=1', { cache: 'no-store', credentials: 'same-origin' }).then(function (r) { return r.json(); })
      .then(function (j) { if (j.ok) { paint(j.rows); at.textContent = 'છેલ્લું અપડેટ ' + j.at; } }).catch(function () {});
  }, 15000);
})();
</script>
<?php admin_foot(); ?>
