<?php
/** KRISHNA TOOLS — converters hub: every generated converter category, linked. */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/generated_tools.php';

$counts = gen_total_count();
$page_title = 'Unit Converters — ' . number_format($counts['total']) . '+ Free Online Conversions | ' . brand_name();
$page_desc = 'Free online unit converters: length, weight, temperature, volume, speed, digital storage, data rate and more. ' . number_format($counts['total']) . '+ instant conversions with formulas and tables.';
$page_keywords = 'unit converter, length converter, weight converter, temperature converter, online conversion, km to miles, kg to lbs';
$breadcrumb = [['label' => 'Home', 'url' => SITE_URL . '/'], ['label' => 'Converters']];
require __DIR__ . '/includes/header.php';
?>
<div class="my-6 text-center">
  <h1 class="section-title text-3xl">Unit Converters</h1>
  <p class="opacity-70 mt-2"><?= number_format($counts['total']) ?>+ free instant conversions across <?= count(gen_unit_categories()) ?> categories</p>
</div>

<?php foreach (gen_unit_categories() as $catKey => $cat):
    $units = array_keys($cat['units']); ?>
  <section id="<?= e($catKey) ?>" class="mb-8">
    <div class="flex items-center gap-3 mb-4">
      <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white" style="background:var(--peacock-teal)"><i data-lucide="<?= e($cat['icon']) ?>" class="w-5 h-5"></i></div>
      <h2 class="section-title text-xl"><?= e($cat['name']) ?> <span class="text-sm opacity-60 font-normal">(<?= count($units) ?> units)</span></h2>
    </div>
    <!-- Popular pairs: base unit → every other, plus a few common cross pairs -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2 text-sm">
      <?php
      $base = $cat['base'];
      $shown = 0;
      foreach ($units as $from) {
          foreach ($units as $tp) {
              if ($from === $tp) continue;
              // Show pairs anchored to the base unit + reverse, to keep the hub readable but well-linked.
              if ($from !== $base && $tp !== $base) continue;
              $fl = gen_unit_label($catKey, $from); $tl = gen_unit_label($catKey, $tp);
              echo '<a class="kt-card px-3 py-2 hover:border-[color:var(--gold)]" href="' . SITE_URL . '/convert/' . e($from) . '-to-' . e($tp) . '">' . e($fl['symbol']) . ' → ' . e($tl['symbol']) . ' <span class="opacity-50 text-xs">' . e($fl['name']) . ' to ' . e($tl['name']) . '</span></a>';
              $shown++;
          }
      }
      ?>
    </div>
  </section>
<?php endforeach; ?>

<div class="kt-card p-5 text-center mt-8">
  <p class="opacity-70">Looking for a specific conversion? Just type it in the search box, or visit <a class="text-[color:var(--peacock-teal)] underline" href="<?= SITE_URL ?>/">all 120 interactive tools</a>.</p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
