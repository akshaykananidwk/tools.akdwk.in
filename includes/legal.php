<?php
/**
 * KRISHNA TOOLS — shared layout for the trust pages (About, Contact, Privacy, Terms, Disclaimer).
 * Each page passes its sections as [heading, html]; the page shows a contents list,
 * the "last updated" date and the shop's contact details from Admin → Settings.
 */
require_once __DIR__ . '/bootstrap.php';

/** Contact details of the business, from settings (with the shop's known defaults). */
function kt_shop(): array {
    $phone = setting('shop_phone', '9978123146');
    return [
        'name' => setting('shop_name', 'AK Computer'),
        'address' => kt_en_text(setting('shop_address', 'Dwarka, Gujarat'), 'Dwarka, Gujarat'),
        'city' => 'Dwarka, Devbhumi Dwarka district, Gujarat 361335, India',
        'phone' => $phone,
        'wa' => normalize_phone(setting('shop_whatsapp', setting('wa_sender', '919978123146'))),
        'email' => setting('shop_email', setting('contact_email', '')),
        'hours' => setting('shop_hours', ''),
    ];
}

/** The pages in the footer's "Legal & trust" column. */
function kt_trust_links(): array {
    return ['About us' => '/about.php', 'Contact' => '/contact.php', 'Privacy policy' => '/privacy.php', 'Terms of use' => '/terms.php', 'Disclaimer' => '/disclaimer.php'];
}

/** Print a whole trust page. */
function kt_legal_page(string $title, string $desc, string $updated, array $sections, string $intro = ''): void {
    $page_title = $title . ' — ' . brand_name();
    $page_desc = $desc;
    $breadcrumb = [['label' => 'Home', 'url' => SITE_URL . '/'], ['label' => $title]];
    $shop = kt_shop();
    require __DIR__ . '/header.php';
    ?>
    <div class="max-w-3xl mx-auto my-6">
      <h1 class="section-title text-3xl mb-1"><?= e($title) ?></h1>
      <p class="text-sm opacity-70 mb-4">Last updated: <time datetime="<?= e($updated) ?>"><?= e(date('j F Y', strtotime($updated))) ?></time></p>
      <?php if ($intro): ?><p class="leading-relaxed mb-5"><?= $intro ?></p><?php endif; ?>
      <?php if (count($sections) > 3): ?>
      <nav class="kt-card p-4 mb-5 text-sm" aria-label="Contents"><b>On this page</b>
        <ol class="list-decimal ml-5 mt-2 space-y-0.5"><?php foreach ($sections as $i => [$h]): ?><li><a class="underline" href="#s<?= $i + 1 ?>"><?= e($h) ?></a></li><?php endforeach; ?></ol>
      </nav>
      <?php endif; ?>
      <article class="kt-card p-6 leading-relaxed space-y-5">
        <?php foreach ($sections as $i => [$h, $html]): ?>
          <section id="s<?= $i + 1 ?>"><h2 class="font-bold text-xl mb-2" style="color:var(--peacock-teal)"><?= e($h) ?></h2><?= $html ?></section>
        <?php endforeach; ?>
      </article>
      <div class="kt-card p-5 mt-5 text-sm">
        <b><?= e($shop['name']) ?></b> · <?= e($shop['address']) ?><br>
        Phone / WhatsApp: <a class="underline" href="https://wa.me/<?= e($shop['wa']) ?>"><?= e($shop['phone']) ?></a><?php if ($shop['email']): ?> · Email: <a class="underline" href="mailto:<?= e($shop['email']) ?>"><?= e($shop['email']) ?></a><?php endif; ?>
        <div class="mt-2 opacity-80"><?php foreach (kt_trust_links() as $l => $u): ?><a class="underline mr-3" href="<?= SITE_URL . $u ?>"><?= e($l) ?></a><?php endforeach; ?></div>
      </div>
    </div>
    <?php
    require __DIR__ . '/footer.php';
}
