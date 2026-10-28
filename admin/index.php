<?php
/**
 * KRISHNA TOOLS — admin dashboard (KPIs + 14-day usage chart + activity feed).
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_admin();
require_once __DIR__ . '/layout.php';

/** Safe scalar with a fallback when the table is missing/empty. */
function kpi_scalar(string $sql, array $params = [], $default = 0) {
    try { $v = scalar($sql, $params); return $v === null ? $default : $v; }
    catch (Throwable $e) { return $default; }
}

$today = date('Y-m-d');

$totalUsers = (int) kpi_scalar("SELECT COUNT(*) FROM " . tbl('users'));

$activePaid = (int) kpi_scalar(
    "SELECT COUNT(*) FROM " . tbl('users') . " u
     JOIN " . tbl('plans') . " p ON p.id = u.plan_id
     WHERE u.plan_expiry > NOW() AND p.price > 0");

$revenueToday = (float) kpi_scalar(
    "SELECT COALESCE(SUM(amount),0) FROM " . tbl('payments') . "
     WHERE status = 'paid' AND DATE(created_at) = :d", [':d' => $today]);

// Visitor numbers come from the same real-visitor data as Live Analytics
// (browsers only: no bots, no admin), so both pages always agree.
require_once __DIR__ . '/../includes/analytics.php';
try { kt_analytics_ensure(); } catch (Throwable $e) {}
$usagesToday = (int) kpi_scalar(
    "SELECT COUNT(*) FROM " . tbl('page_views') . " WHERE kind = 'use' AND created_at BETWEEN :a AND :b",
    [':a' => "$today 00:00:00", ':b' => "$today 23:59:59"]);
$visitorsToday = (int) kpi_scalar(
    "SELECT COUNT(DISTINCT vid) FROM " . tbl('page_views') . " WHERE created_at BETWEEN :a AND :b",
    [':a' => "$today 00:00:00", ':b' => "$today 23:59:59"]);
$liveVisitors = (int) kpi_scalar(
    "SELECT COUNT(*) FROM " . tbl('live_visitors') . " WHERE last_seen >= :t",
    [':t' => date('Y-m-d H:i:s', time() - KT_LIVE_WINDOW)]);

$totalTools = (int) kpi_scalar("SELECT COUNT(*) FROM " . tbl('tools'));

// Top 5 tools by views.
$topTools = [];
try {
    $topTools = all("SELECT name_gu, name_en, views FROM " . tbl('tools') . "
                     ORDER BY views DESC, id ASC LIMIT 5");
} catch (Throwable $e) { $topTools = []; }

// 14-day usage series (fill gaps with zero).
$chartLabels = [];
$chartData   = [];
$series = [];
try {
    foreach (all("SELECT DATE(created_at) d, COUNT(DISTINCT vid) c FROM " . tbl('page_views') . "
                  WHERE created_at >= :since GROUP BY DATE(created_at)", [':since' => date('Y-m-d', strtotime('-13 days')) . ' 00:00:00']) as $r) {
        $series[$r['d']] = (int) $r['c'];
    }
} catch (Throwable $e) { $series = []; }
for ($i = 13; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i day"));
    $chartLabels[] = date('d/m', strtotime($d));
    $chartData[]   = $series[$d] ?? 0;
}

// Recent activity (last 15).
$activity = [];
try {
    $activity = all("SELECT a.action, a.details, a.ip, a.created_at, u.name AS uname
                     FROM " . tbl('activity_logs') . " a
                     LEFT JOIN " . tbl('users') . " u ON u.id = a.user_id
                     ORDER BY a.id DESC LIMIT 15");
} catch (Throwable $e) { $activity = []; }

$cards = [
    ['કુલ યુઝર્સ',         number_format($totalUsers),  'users',              'var(--krishna-blue)'],
    ['એક્ટિવ પેઇડ પ્લાન',  number_format($activePaid),  'badge-check',        'var(--morpankh-green)'],
    ['આજની આવક',          money($revenueToday),        'badge-indian-rupee', 'var(--saffron)'],
    ['આજના મુલાકાતીઓ',     number_format($visitorsToday), 'users-round',      'var(--peacock-teal)'],
    ['આજે ટૂલ વપરાશ',     number_format($usagesToday), 'activity',           'var(--peacock-teal)'],
    ['અત્યારે લાઇવ',       number_format($liveVisitors), 'radio',            'var(--gold)'],
    ['કુલ ટૂલ્સ',          number_format($totalTools),  'wrench',             'var(--krishna-blue)'],
];

admin_head('ડેશબોર્ડ');
?>
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
  <?php foreach ($cards as [$label, $value, $icon, $color]): ?>
    <div class="kt-card kpi p-5 flex items-center justify-between">
      <div>
        <div class="text-sm opacity-70 mb-1"><?= e($label) ?></div>
        <div class="text-2xl font-extrabold"><?= e($value) ?></div>
      </div>
      <div class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background:<?= e($color) ?>1a;color:<?= e($color) ?>">
        <i data-lucide="<?= e($icon) ?>" class="w-6 h-6"></i>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="grid gap-4 lg:grid-cols-3 mt-5">
  <div class="kt-card p-5 lg:col-span-2">
    <h2 class="section-title text-base mb-3">છેલ્લા 14 દિવસના મુલાકાતીઓ <a href="<?= SITE_URL ?>/admin/analytics.php" class="text-xs font-normal underline ml-2">પૂરો રિપોર્ટ →</a></h2>
    <canvas id="usageChart" height="110"></canvas>
  </div>
  <div class="kt-card p-5">
    <h2 class="section-title text-base mb-3">ટોપ 5 ટૂલ્સ (વ્યૂઝ)</h2>
    <?php if ($topTools): ?>
      <ol class="space-y-2">
        <?php foreach ($topTools as $i => $trow): ?>
          <li class="flex items-center justify-between text-sm">
            <span class="flex items-center gap-2">
              <span class="w-6 h-6 rounded-lg flex items-center justify-center text-xs font-bold" style="background:rgba(245,158,11,.15);color:var(--saffron)"><?= $i + 1 ?></span>
              <?= e(loc($trow, 'name')) ?>
            </span>
            <span class="badge-premium text-xs font-semibold"><?= number_format((int) $trow['views']) ?></span>
          </li>
        <?php endforeach; ?>
      </ol>
    <?php else: ?>
      <p class="text-sm opacity-60">કોઈ ડેટા નથી.</p>
    <?php endif; ?>
  </div>
</div>

<div class="kt-card p-5 mt-5">
  <h2 class="section-title text-base mb-3">તાજેતરની પ્રવૃત્તિ</h2>
  <?php if ($activity): ?>
    <div class="overflow-x-auto">
      <table class="kt-table">
        <thead><tr><th>યુઝર</th><th>ક્રિયા</th><th>વિગત</th><th>IP</th><th>સમય</th></tr></thead>
        <tbody>
        <?php foreach ($activity as $a): ?>
          <tr>
            <td><?= e($a['uname'] ?? 'ગેસ્ટ') ?></td>
            <td><span class="badge-premium text-xs"><?= e($a['action'] ?? '') ?></span></td>
            <td class="opacity-70 max-w-[280px] truncate"><?= e($a['details'] ?? '') ?></td>
            <td class="opacity-60 text-xs"><?= e($a['ip'] ?? '') ?></td>
            <td class="opacity-60 text-xs whitespace-nowrap"><?= e($a['created_at'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p class="text-sm opacity-60">કોઈ પ્રવૃત્તિ નથી.</p>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
(function(){
  var el = document.getElementById('usageChart');
  if(!el || !window.Chart) return;
  new Chart(el.getContext('2d'), {
    type: 'line',
    data: {
      labels: <?= json_encode($chartLabels, JSON_UNESCAPED_UNICODE) ?>,
      datasets: [{
        label: 'મુલાકાતીઓ',
        data: <?= json_encode($chartData) ?>,
        borderColor: '#F59E0B',
        backgroundColor: 'rgba(245,158,11,.15)',
        fill: true, tension: .35, pointRadius: 3, borderWidth: 2
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: true,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
    }
  });
})();
</script>
<?php admin_foot(); ?>
