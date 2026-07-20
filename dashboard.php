<?php
/** KRISHNA TOOLS — user dashboard (plan, usage graph, favourites, shlok). */
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$user = current_user();
$plan = user_plan($user);
$left = days_left($user);
$shlok = daily_shlok();

// Usage over the last 7 days for the Chart.js graph.
$usage = [];
for ($i = 6; $i >= 0; $i--) $usage[date('Y-m-d', strtotime("-$i days"))] = 0;
try {
    foreach (all("SELECT DATE(created_at) d, COUNT(*) c FROM " . tbl('tool_usage') . "
                  WHERE user_id = :u AND created_at >= :since GROUP BY DATE(created_at)",
                  [':u' => $user['id'], ':since' => date('Y-m-d', strtotime('-6 days'))]) as $r) {
        $usage[$r['d']] = (int) $r['c'];
    }
} catch (Throwable $e) {}

// Recent + favourite tools.
$recent = [];
try { foreach (all("SELECT DISTINCT t.slug, t.name_gu, t.name_en, t.icon FROM " . tbl('tool_usage') . " tu
                    JOIN " . tbl('tools') . " t ON t.id = tu.tool_id WHERE tu.user_id = :u ORDER BY tu.id DESC LIMIT 6",
                    [':u' => $user['id']]) as $r) $recent[] = $r; } catch (Throwable $e) {}
$favs = [];
try { foreach (all("SELECT t.slug, t.name_gu, t.name_en, t.icon FROM " . tbl('favourites') . " f
                    JOIN " . tbl('tools') . " t ON t.id = f.tool_id WHERE f.user_id = :u LIMIT 8",
                    [':u' => $user['id']]) as $r) $favs[] = $r; } catch (Throwable $e) {}

$page_title = t('dashboard') . ' — ' . setting('site_name', 'કૃષ્ણા ટૂલ્સ');
$extra_head = '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>';
require __DIR__ . '/includes/header.php';
?>
<div class="my-6">
  <h1 class="section-title text-2xl"><?= current_lang() === 'en' ? 'Hare Krishna' : 'હરે કૃષ્ણ' ?>, <?= e($user['name']) ?> 🦚</h1>
</div>

<div class="grid lg:grid-cols-3 gap-5">
  <!-- Plan card -->
  <div class="kt-card p-5">
    <div class="text-xs opacity-60"><?= t('my_plan') ?></div>
    <div class="text-2xl font-extrabold mt-1" style="color:var(--peacock-teal)"><?= e(loc($plan, 'name')) ?></div>
    <?php if ((float) $plan['price'] > 0): ?>
      <div class="text-sm mt-1"><?= t('days_left', ['n' => $left]) ?></div>
      <?php if ($left <= 7): ?><a href="<?= SITE_URL ?>/pricing.php" class="btn btn-cta text-sm mt-3"><?= t('upgrade') ?></a><?php endif; ?>
    <?php else: ?>
      <div class="text-sm opacity-70 mt-1"><?= current_lang() === 'en' ? '3 uses/day · 5MB files' : 'દરરોજ 3 વખત · 5MB' ?></div>
      <a href="<?= SITE_URL ?>/pricing.php" class="btn btn-cta text-sm mt-3"><i data-lucide="gem" class="w-4 h-4"></i> <?= t('upgrade') ?></a>
    <?php endif; ?>
  </div>

  <!-- Credits + referral -->
  <div class="kt-card p-5">
    <div class="text-xs opacity-60"><?= current_lang() === 'en' ? 'Referral' : 'રેફરલ' ?></div>
    <div class="text-2xl font-extrabold mt-1">₹<?= number_format((float) $user['credits'], 0) ?> <span class="text-sm opacity-60"><?= current_lang() === 'en' ? 'credits' : 'ક્રેડિટ' ?></span></div>
    <div class="text-sm mt-2"><?= current_lang() === 'en' ? 'Your code:' : 'તમારો કોડ:' ?> <b class="font-mono"><?= e($user['referral_code']) ?></b></div>
    <div class="text-xs opacity-60 mt-1"><?= current_lang() === 'en' ? '₹50 credit per signup' : 'દરેક સાઇનઅપ પર ₹50' ?></div>
  </div>

  <!-- Daily shlok -->
  <div class="kt-card p-5" style="background:linear-gradient(135deg,rgba(30,58,138,.07),rgba(245,158,11,.07))">
    <div class="text-xs opacity-60"><?= t('daily_shlok') ?></div>
    <div class="mt-2 font-semibold" style="font-family:'Baloo Bhai 2'"><?= e($shlok['sanskrit']) ?></div>
    <div class="text-sm opacity-70 mt-1"><?= e($shlok['gu']) ?></div>
  </div>
</div>

<!-- Usage chart -->
<div class="kt-card p-5 mt-5">
  <h2 class="font-bold mb-3"><?= t('usage') ?> (7 <?= current_lang() === 'en' ? 'days' : 'દિવસ' ?>)</h2>
  <canvas id="usageChart" height="90"></canvas>
</div>

<div class="grid md:grid-cols-2 gap-5 mt-5">
  <div class="kt-card p-5">
    <h2 class="font-bold mb-3"><?= t('recent_tools') ?></h2>
    <?php if ($recent): ?>
      <div class="grid grid-cols-2 gap-2">
        <?php foreach ($recent as $r): ?><a href="<?= SITE_URL ?>/tool/<?= e($r['slug']) ?>" class="flex items-center gap-2 p-2 rounded-lg hover:bg-[color:var(--gold)]/10"><i data-lucide="<?= e($r['icon']) ?>" class="w-4 h-4 opacity-70"></i><span class="text-sm"><?= e(loc($r, 'name')) ?></span></a><?php endforeach; ?>
      </div>
    <?php else: ?><p class="opacity-60 text-sm"><?= current_lang() === 'en' ? 'No recent tools yet.' : 'હજુ કોઈ ટૂલ વાપર્યું નથી.' ?></p><?php endif; ?>
  </div>
  <div class="kt-card p-5">
    <h2 class="font-bold mb-3"><?= t('favourite_tools') ?></h2>
    <?php if ($favs): ?>
      <div class="grid grid-cols-2 gap-2">
        <?php foreach ($favs as $r): ?><a href="<?= SITE_URL ?>/tool/<?= e($r['slug']) ?>" class="flex items-center gap-2 p-2 rounded-lg hover:bg-[color:var(--gold)]/10"><i data-lucide="<?= e($r['icon']) ?>" class="w-4 h-4 opacity-70"></i><span class="text-sm"><?= e(loc($r, 'name')) ?></span></a><?php endforeach; ?>
      </div>
    <?php else: ?><p class="opacity-60 text-sm"><?= current_lang() === 'en' ? 'Star tools to see them here.' : 'ટૂલ્સ મનપસંદ કરો.' ?></p><?php endif; ?>
  </div>
</div>

<div class="mt-5 flex gap-2 flex-wrap">
  <a href="<?= SITE_URL ?>/profile.php" class="btn btn-ghost"><i data-lucide="user" class="w-4 h-4"></i> <?= t('profile') ?></a>
  <a href="<?= SITE_URL ?>/billing.php" class="btn btn-ghost"><i data-lucide="receipt" class="w-4 h-4"></i> <?= current_lang() === 'en' ? 'Billing' : 'બિલિંગ' ?></a>
  <a href="<?= SITE_URL ?>/logout.php" class="btn btn-ghost"><i data-lucide="log-out" class="w-4 h-4"></i> <?= t('logout') ?></a>
</div>

<script>
new Chart(document.getElementById('usageChart'), {
  type: 'bar',
  data: { labels: <?= json_encode(array_map(fn($d) => date('d/m', strtotime($d)), array_keys($usage))) ?>,
    datasets: [{ label: '<?= t('usage') ?>', data: <?= json_encode(array_values($usage)) ?>,
      backgroundColor: 'rgba(14,116,144,.6)', borderRadius: 6 }] },
  options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
});
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
