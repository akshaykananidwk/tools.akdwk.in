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

<!-- Hub guide (300+ words): what is here, Indian units, how the pages work -->
<article class="kt-card p-5 md:p-6 mb-8 leading-relaxed space-y-3">
  <h2 class="section-title text-xl">Free unit converters for every day</h2>
  <p>Every converter on Krishna Tools gives the answer first, in large type, and then shows how it is worked out: the formula, three solved examples, a table of 30–40 common values and short answers to the questions people ask most. You can type any number into the live converter, swap the units, copy the result or share it on WhatsApp. Everything runs in your browser, so it is instant and works even on slow mobile data. Each page also links to the reverse conversion and to nearby amounts, so checking a second value takes one tap.</p>
  <h3 class="font-bold">Indian units, done properly</h3>
  <p>Besides metric and imperial units, the converters cover the units used in Indian markets and land records: <a class="underline" href="<?= SITE_URL ?>/convert/quintal-to-kilogram">quintal to kg</a> for APMC mandi and MSP rates, <a class="underline" href="<?= SITE_URL ?>/convert/tola-to-gram">tola to gram</a> for gold, <a class="underline" href="<?= SITE_URL ?>/convert/bigha-to-guntha">vigha (bigha) to guntha</a>, <a class="underline" href="<?= SITE_URL ?>/convert/acre-to-guntha">acre to guntha</a> and <a class="underline" href="<?= SITE_URL ?>/convert/square-yard-to-square-foot">gaj (square yard) to sq ft</a> for land, and <a class="underline" href="<?= SITE_URL ?>/convert/megabit-per-second-to-megabyte-per-second">Mbps to MB/s</a> for internet speed. Land units such as the bigha differ between states, and so does the maund (મણ): each page says which size it uses and lets you pick your state or region. There are also <a class="underline" href="<?= SITE_URL ?>/convert/maund-to-kilogram">maund to kg</a>, <a class="underline" href="<?= SITE_URL ?>/convert/ser-to-kilogram">seer to kg</a> and <a class="underline" href="<?= SITE_URL ?>/convert/crore-to-lakh">crore to lakh</a> / <a class="underline" href="<?= SITE_URL ?>/convert/lakh-to-million">lakh to million</a> converters.</p>
  <p lang="gu"><b>ગુજરાતીમાં:</b> <a class="underline" href="<?= SITE_URL ?>/gu/convert/quintal-to-kilogram">ક્વિન્ટલ થી કિલો</a> · <a class="underline" href="<?= SITE_URL ?>/gu/convert/maund-to-kilogram">મણ થી કિલો</a> · <a class="underline" href="<?= SITE_URL ?>/gu/convert/bigha-to-guntha">વીઘા થી ગુંઠા</a> · <a class="underline" href="<?= SITE_URL ?>/gu/convert/tola-to-gram">તોલા થી ગ્રામ</a> · <a class="underline" href="<?= SITE_URL ?>/gu/convert/acre-to-guntha">એકર થી ગુંઠા</a> · <a class="underline" href="<?= SITE_URL ?>/gu/convert/crore-to-lakh">કરોડ થી લાખ</a></p>
  <p lang="hi"><b>हिन्दी में:</b> <a class="underline" href="<?= SITE_URL ?>/hi/convert/quintal-to-kilogram">क्विंटल से किलो</a> · <a class="underline" href="<?= SITE_URL ?>/hi/convert/maund-to-kilogram">मन से किलो</a> · <a class="underline" href="<?= SITE_URL ?>/hi/convert/bigha-to-square-foot">बीघा से वर्ग फुट</a> · <a class="underline" href="<?= SITE_URL ?>/hi/convert/tola-to-gram">तोला से ग्राम</a> · <a class="underline" href="<?= SITE_URL ?>/hi/convert/acre-to-hectare">एकड़ से हेक्टेयर</a> · <a class="underline" href="<?= SITE_URL ?>/hi/convert/crore-to-lakh">करोड़ से लाख</a></p>
  <h3 class="font-bold">Searching for a number?</h3>
  <p>Popular conversions have their own pages for common amounts, such as <a class="underline" href="<?= SITE_URL ?>/convert/30-quintal-to-kilogram">30 quintal to kg</a>, <a class="underline" href="<?= SITE_URL ?>/convert/100-milligram-to-gram">100 mg to g</a>, <a class="underline" href="<?= SITE_URL ?>/convert/500-kilogram-to-pound">500 kg to lbs</a> or <a class="underline" href="<?= SITE_URL ?>/convert/500-hour-to-day">500 hours to days</a>. The quickest way is the search box at the top: type the whole question, like “15 qtl in kg” or “100 mm to cm”, and press Enter to open the answer directly.</p>
  <p>Below you will find every category — length, weight, temperature, area, volume, speed, time, digital storage, data rate, pressure, energy, power, angle, frequency and fuel economy — with the main conversions of each unit. For calculators that need more than a conversion (GST, EMI, income tax, CCTV storage and more) see the <a class="underline" href="<?= SITE_URL ?>/">full list of tools</a>.</p>
</article>

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
  <p class="opacity-70">Looking for a specific conversion? Just type it in the search box, or visit <a class="text-[color:var(--peacock-teal)] underline" href="<?= SITE_URL ?>/">all <?= kt_tool_count_label() ?>+ interactive tools</a>.</p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
