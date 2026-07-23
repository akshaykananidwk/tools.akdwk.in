<?php
/** KRISHNA TOOLS — global footer + mobile bottom nav + shared scripts. */
require_once __DIR__ . '/functions.php';
$waShop = normalize_phone(setting('shop_whatsapp', setting('wa_sender', '919978123146')));
?>
</main>

<footer class="mt-10 border-t" style="border-color:var(--border)">
  <div class="max-w-7xl mx-auto px-4 py-10 grid md:grid-cols-4 gap-8 text-sm">
    <div>
      <div class="brand-title text-lg mb-2"><?= e(brand_name()) ?></div>
      <p class="opacity-70"><?= t('tagline') ?></p>
      <p class="opacity-70 mt-2"><?= t('footer_note') ?></p>
    </div>
    <div>
      <div class="font-semibold mb-2"><?= t('all_tools') ?></div>
      <ul class="space-y-1 opacity-70">
        <?php foreach (array_slice(kt_categories(), 0, 6) as $c): ?>
          <li><a class="hover:text-[color:var(--saffron)]" href="<?= SITE_URL ?>/category/<?= e($c['slug']) ?>"><?= e(loc($c, 'name')) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div>
      <div class="font-semibold mb-2"><?= t('contact') ?></div>
      <ul class="space-y-1 opacity-70">
        <li><a class="hover:text-[color:var(--saffron)]" href="<?= SITE_URL ?>/pricing.php"><?= t('pricing') ?></a></li>
        <li><a class="hover:text-[color:var(--saffron)]" href="<?= SITE_URL ?>/blog/"><?= t('blog') ?></a></li>
        <li><a class="hover:text-[color:var(--saffron)]" href="<?= SITE_URL ?>/contact.php"><?= t('contact') ?></a></li>
        <li><a class="hover:text-[color:var(--saffron)]" href="<?= SITE_URL ?>/about.php"><?= t('about') ?></a></li>
      </ul>
    </div>
    <div>
      <div class="font-semibold mb-2"><?= e(setting('shop_name', 'AK Computer')) ?></div>
      <p class="opacity-70"><?= e(setting('shop_address', 'દ્વારકા, ગુજરાત')) ?></p>
      <p class="opacity-70 mt-1">📞 <?= e(setting('shop_phone', '9978123146')) ?></p>
      <a href="https://maps.google.com/?q=<?= urlencode(setting('shop_address', 'Dwarka, Gujarat')) ?>"
         target="_blank" rel="noopener" class="text-[color:var(--peacock-teal)] hover:underline mt-1 inline-block">Google Maps 📍</a>
    </div>
  </div>
  <div class="text-center py-4 hare-krishna-mark text-[color:var(--gold)]">ॐ हरे कृष्ण हरे कृष्ण कृष्ण कृष्ण हरे हरे</div>
  <div class="text-center pb-6 text-xs opacity-60">© <?= date('Y') ?> <?= e(brand_name()) ?> · <?= e(setting('shop_name', 'AK Computer')) ?>, દ્વારકા</div>
</footer>

<!-- Floating WhatsApp -->
<a href="https://wa.me/<?= e($waShop) ?>" target="_blank" rel="noopener"
   class="fixed z-40 bottom-20 md:bottom-6 right-4 btn-wa btn rounded-full !p-3 shadow-lg" aria-label="WhatsApp">
  <i data-lucide="message-circle" class="w-6 h-6"></i>
</a>

<!-- Mobile bottom nav -->
<nav class="bottom-nav md:hidden">
  <a href="<?= SITE_URL ?>/"><i data-lucide="home" class="w-5 h-5"></i><?= t('home') ?></a>
  <a href="<?= SITE_URL ?>/#categories"><i data-lucide="grid-3x3" class="w-5 h-5"></i><?= t('all_tools') ?></a>
  <a href="<?= SITE_URL ?>/pricing.php"><i data-lucide="gem" class="w-5 h-5"></i><?= t('pricing') ?></a>
  <a href="<?= SITE_URL ?>/<?= is_logged_in() ? 'dashboard.php' : 'login.php' ?>"><i data-lucide="user" class="w-5 h-5"></i><?= is_logged_in() ? t('dashboard') : t('login') ?></a>
</nav>

<div id="ktToast" class="kt-toast"></div>
<script src="<?= SITE_URL ?>/assets/js/app.js?v=<?= asset_ver() ?>" defer></script>
<script>document.addEventListener('DOMContentLoaded',()=>{if(window.lucide)lucide.createIcons();});</script>
</body>
</html>
