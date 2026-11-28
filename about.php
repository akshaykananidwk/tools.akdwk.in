<?php
/** KRISHNA TOOLS — About AK Computer, Dwarka and Krishna Tools. Shop photos: put JPG/PNG/WebP files in /assets/img/about/. */
require_once __DIR__ . '/includes/legal.php';

$shop = kt_shop();
$nTools = kt_tool_count_label();
$photos = glob(__DIR__ . '/assets/img/about/*.{jpg,jpeg,png,webp}', GLOB_BRACE) ?: [];
sort($photos);

$page_title = 'About Us — Krishna Tools by AK Computer, Dwarka';
$page_desc = "Krishna Tools is a free collection of {$nTools}+ online tools and unit converters built by AK Computer, a computer and CCTV shop in Dwarka, Gujarat.";
$breadcrumb = [['label' => 'Home', 'url' => SITE_URL . '/'], ['label' => 'About us']];
$extra_head = '<script type="application/ld+json">' . json_encode(['@context' => 'https://schema.org', '@type' => 'AboutPage',
    'name' => 'About Krishna Tools', 'url' => SITE_URL . '/about.php',
    'mainEntity' => ['@type' => 'LocalBusiness', 'name' => $shop['name'], 'telephone' => '+91' . substr(preg_replace('/\D/', '', $shop['phone']), -10),
        'address' => ['@type' => 'PostalAddress', 'streetAddress' => $shop['address'], 'addressLocality' => 'Dwarka', 'addressRegion' => 'Gujarat', 'postalCode' => '361335', 'addressCountry' => 'IN'],
        'url' => SITE_URL . '/']], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
require __DIR__ . '/includes/header.php';
?>
<div class="max-w-3xl mx-auto my-6">
  <h1 class="section-title text-3xl mb-1">About Krishna Tools</h1>
  <div class="divider-gold mb-5" style="max-width:120px"></div>

  <article class="kt-card p-6 leading-relaxed space-y-5">
    <section>
      <h2 class="font-bold text-xl mb-2" style="color:var(--peacock-teal)">Who we are</h2>
      <p>Krishna Tools (tools.akdwk.in) is built and run by <b><?= e($shop['name']) ?></b>, a computer and CCTV shop in <?= e($shop['city']) ?>. In the shop we sell and repair computers and laptops, install CCTV camera systems for homes, shops and offices, set up networks and Wi-Fi, and help customers every day with printing, online forms and digital paperwork.</p>
      <p class="mt-2">Many of those daily jobs need a small calculation or a quick file change: how big a hard disk a 4-camera CCTV needs, converting quintal rates to price per kg, merging documents into one PDF for a government form, or making a quotation for a customer. We built these tools for our own counter first, then put them online, free, for everyone.</p>
    </section>

    <?php if ($photos): ?>
    <section class="grid grid-cols-2 sm:grid-cols-3 gap-3" aria-label="Photos of AK Computer, Dwarka">
      <?php foreach (array_slice($photos, 0, 6) as $ph): $u = SITE_URL . '/assets/img/about/' . rawurlencode(basename($ph)); ?>
        <img src="<?= e($u) ?>" alt="<?= e($shop['name']) ?>, Dwarka — <?= e(ucwords(str_replace(['-', '_'], ' ', pathinfo($ph, PATHINFO_FILENAME)))) ?>" loading="lazy" class="rounded-lg w-full h-32 object-cover">
      <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <section>
      <h2 class="font-bold text-xl mb-2" style="color:var(--peacock-teal)">What Krishna Tools offers</h2>
      <ul class="list-disc ml-6 space-y-1">
        <li><b><?= e($nTools) ?>+ free online tools</b> — image and PDF tools, CCTV and computer calculators, business documents and quotations, tax and salary calculators, health, student and farming tools.</li>
        <li><b>Thousands of unit converters</b> with the answer, formula, worked examples and a table on every page — including Indian units such as quintal, tola, maund, bigha (vigha), guntha, gaj, lakh and crore, with a choice of region where the size differs by state.</li>
        <li>No sign-up for almost every tool, and it works on any phone or computer.</li>
      </ul>
    </section>

    <section>
      <h2 class="font-bold text-xl mb-2" style="color:var(--peacock-teal)">How we keep your files private</h2>
      <p>Most tools run entirely in your browser, so your photos, PDFs and documents never leave your device. The few tools that need our server delete the uploaded file automatically within 2 hours. Read the <a class="underline" href="<?= SITE_URL ?>/privacy.php">privacy policy</a> for the details.</p>
    </section>

    <section>
      <h2 class="font-bold text-xl mb-2" style="color:var(--peacock-teal)">How we check accuracy</h2>
      <p>Formulas are tested against worked examples before a tool goes live. Where a rule comes from an authority we name it on the page with a "last updated" date — for example the official UPSC and SSC notifications for exam age limits, and the Standards of Weights and Measures Act, 1956 for traditional Indian units. When something changes, or a visitor reports a mistake, we correct the page. Results are still estimates; see the <a class="underline" href="<?= SITE_URL ?>/disclaimer.php">disclaimer</a>.</p>
    </section>

    <section>
      <h2 class="font-bold text-xl mb-2" style="color:var(--peacock-teal)">Visit or contact the shop</h2>
      <p><b><?= e($shop['name']) ?></b><br><?= e($shop['address']) ?><br><?= e($shop['city']) ?><br>
        Phone / WhatsApp: <a class="underline" href="https://wa.me/<?= e($shop['wa']) ?>"><?= e($shop['phone']) ?></a>
        <?php if ($shop['email']): ?><br>Email: <a class="underline" href="mailto:<?= e($shop['email']) ?>"><?= e($shop['email']) ?></a><?php endif; ?>
        <?php if ($shop['hours']): ?><br>Hours: <?= e($shop['hours']) ?><?php endif; ?></p>
      <div class="flex gap-2 flex-wrap mt-3">
        <a href="https://wa.me/<?= e($shop['wa']) ?>" target="_blank" rel="noopener" class="btn btn-wa"><i data-lucide="message-circle" class="w-4 h-4"></i> WhatsApp</a>
        <a href="<?= SITE_URL ?>/contact.php" class="btn btn-ghost"><i data-lucide="mail" class="w-4 h-4"></i> Contact form</a>
        <a href="https://maps.google.com/?q=<?= urlencode($shop['name'] . ', ' . $shop['address']) ?>" target="_blank" rel="noopener" class="btn btn-ghost"><i data-lucide="map-pin" class="w-4 h-4"></i> Google Maps</a>
      </div>
    </section>
  </article>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
