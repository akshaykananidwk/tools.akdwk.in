<?php
/** KRISHNA TOOLS — About AK Computer, Dwarka (computer + CCTV shop). */
require_once __DIR__ . '/includes/bootstrap.php';

$en = current_lang() === 'en';
$shopName    = setting('shop_name', 'AK Computer');
$shopAddress = setting('shop_address', 'દ્વારકા, ગુજરાત');
$shopWaLink  = normalize_phone(setting('shop_whatsapp', setting('wa_sender', '919978123146')));

$page_title = t('about') . ' — ' . setting('site_name', 'કૃષ્ણા ટૂલ્સ');
$page_desc  = $en
    ? 'About AK Computer, Dwarka — a computer and CCTV shop building free online tools for Gujarat.'
    : 'AK Computer, દ્વારકા વિશે — કમ્પ્યુટર અને CCTV શોપ, ગુજરાત માટે ફ્રી ઓનલાઇન ટૂલ્સ.';
$breadcrumb = [
    ['label' => t('home'), 'url' => SITE_URL . '/'],
    ['label' => t('about')],
];
require __DIR__ . '/includes/header.php';
?>
<div class="max-w-3xl mx-auto my-6">
  <div class="text-center mb-6">
    <div class="text-5xl mb-2">🦚</div>
    <h1 class="section-title text-3xl"><?= $en ? 'About Us' : 'અમારા વિશે' ?></h1>
    <div class="divider-gold mx-auto mt-3" style="max-width:120px"></div>
  </div>

  <div class="kt-card p-6 mb-5">
    <h2 class="font-bold text-xl mb-3" style="color:var(--peacock-teal)"><?= e($shopName) ?>, દ્વારકા</h2>
    <?php if ($en): ?>
      <p class="opacity-80 leading-relaxed">
        <?= e($shopName) ?> is a trusted computer and CCTV shop based in Dwarka, Gujarat. For years we have
        served homes, shops and businesses with computer sales &amp; service, CCTV camera installation,
        networking, printing and digital paperwork.
      </p>
      <p class="opacity-80 leading-relaxed mt-3">
        Krishna Tools is our gift to the community — a collection of 120+ genuinely useful online tools
        (image, PDF, CCTV calculators, business utilities and more), built Gujarati-first so everyone can
        use them with ease. Most tools run right inside your browser, so your files stay private.
      </p>
    <?php else: ?>
      <p class="opacity-80 leading-relaxed">
        <?= e($shopName) ?> એ દ્વારકા, ગુજરાત સ્થિત વિશ્વસનીય કમ્પ્યુટર અને CCTV શોપ છે. વર્ષોથી અમે ઘર,
        દુકાન અને બિઝનેસ માટે કમ્પ્યુટર વેચાણ અને સર્વિસ, CCTV કેમેરા ઇન્સ્ટોલેશન, નેટવર્કિંગ, પ્રિન્ટિંગ
        અને ડિજિટલ કામકાજની સેવા આપીએ છીએ.
      </p>
      <p class="opacity-80 leading-relaxed mt-3">
        કૃષ્ણા ટૂલ્સ એ સમાજ માટે અમારી ભેટ છે — 120+ ખરેખર ઉપયોગી ઓનલાઇન ટૂલ્સ (ઇમેજ, PDF, CCTV
        કેલ્ક્યુલેટર, બિઝનેસ યુટિલિટી અને વધુ), જે ગુજરાતી-પ્રથમ બનાવ્યાં છે જેથી દરેક સરળતાથી વાપરી શકે.
        મોટાભાગનાં ટૂલ્સ તમારા બ્રાઉઝરમાં જ ચાલે છે, એટલે તમારી ફાઇલો ખાનગી રહે છે.
      </p>
    <?php endif; ?>
  </div>

  <div class="grid sm:grid-cols-3 gap-4 mb-6">
    <div class="kt-card p-4 text-center">
      <i data-lucide="monitor" class="w-7 h-7 mx-auto mb-2" style="color:var(--peacock-teal)"></i>
      <div class="font-semibold"><?= $en ? 'Computer Sales & Service' : 'કમ્પ્યુટર વેચાણ અને સર્વિસ' ?></div>
    </div>
    <div class="kt-card p-4 text-center">
      <i data-lucide="cctv" class="w-7 h-7 mx-auto mb-2" style="color:var(--peacock-teal)"></i>
      <div class="font-semibold"><?= $en ? 'CCTV Installation' : 'CCTV ઇન્સ્ટોલેશન' ?></div>
    </div>
    <div class="kt-card p-4 text-center">
      <i data-lucide="wrench" class="w-7 h-7 mx-auto mb-2" style="color:var(--peacock-teal)"></i>
      <div class="font-semibold"><?= $en ? 'Networking & Repair' : 'નેટવર્કિંગ અને રિપેર' ?></div>
    </div>
  </div>

  <div class="kt-card p-6 text-center" style="background:linear-gradient(135deg,rgba(30,58,138,.07),rgba(245,158,11,.07))">
    <h3 class="font-bold text-lg mb-1"><?= $en ? 'Need help or a quote?' : 'મદદ કે કોટેશન જોઈએ છે?' ?></h3>
    <p class="opacity-70 text-sm mb-4"><?= e($shopAddress) ?></p>
    <div class="flex gap-2 justify-center flex-wrap">
      <a href="https://wa.me/<?= e($shopWaLink) ?>" target="_blank" rel="noopener" class="btn btn-wa">
        <i data-lucide="message-circle" class="w-4 h-4"></i> <?= t('send_whatsapp') ?>
      </a>
      <a href="<?= SITE_URL ?>/contact.php" class="btn btn-ghost">
        <i data-lucide="mail" class="w-4 h-4"></i> <?= t('contact') ?>
      </a>
    </div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
