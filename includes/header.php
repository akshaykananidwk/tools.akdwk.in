<?php
/**
 * KRISHNA TOOLS — global HTML head + top navigation.
 * Expects optional: $page_title, $page_desc, $page_og, $extra_head, $breadcrumb.
 */
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
kt_session_start();

$lang     = current_lang();
$title    = $page_title ?? (brand_name() . ' — ' . t('tagline'));
$desc     = $page_desc  ?? t('tagline');
$og       = $page_og    ?? (SITE_URL . '/assets/img/logo/og.png');
$fest     = festival_theme();
$user     = current_user();
$gaId     = setting('ga_id', defined('GA_ID') ? GA_ID : '');
?><!DOCTYPE html>
<html lang="<?= e($lang) ?>" data-theme="light" class="<?= $lang === 'gu' ? 'gu' : '' ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<?php
// Canonical + hreflang alternates: strip any ?lang= so both languages point at
// one canonical URL, and advertise the en/gu variants for international SEO.
$reqUri = $_SERVER['REQUEST_URI'] ?? '/';
$cleanUri = preg_replace('/([?&])lang=(en|gu)(&|$)/', '$1', $reqUri);
$cleanUri = rtrim(preg_replace('/[?&]$/', '', $cleanUri), '');
// Pages may force a clean canonical URL (e.g. generated converter pages).
$canonical = $page_canonical ?? (SITE_URL . $cleanUri);
$sep = str_contains($cleanUri, '?') ? '&' : '?';
$siteName = brand_name();
?>
<meta name="robots" content="<?= e($page_robots ?? 'index, follow, max-image-preview:large') ?>">
<?php if (!empty($page_keywords)): ?><meta name="keywords" content="<?= e($page_keywords) ?>"><?php endif; ?>
<meta name="author" content="AK Computer, Dwarka">
<link rel="canonical" href="<?= e($canonical) ?>">
<link rel="alternate" hreflang="en" href="<?= e($canonical . $sep . 'lang=en') ?>">
<link rel="alternate" hreflang="gu" href="<?= e($canonical . $sep . 'lang=gu') ?>">
<link rel="alternate" hreflang="x-default" href="<?= e($canonical) ?>">
<!-- Open Graph -->
<meta property="og:site_name" content="<?= e($siteName) ?>">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:image" content="<?= e($og) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:type" content="website">
<meta property="og:locale" content="<?= $lang === 'gu' ? 'gu_IN' : 'en_IN' ?>">
<meta property="og:locale:alternate" content="<?= $lang === 'gu' ? 'en_IN' : 'gu_IN' ?>">
<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($title) ?>">
<meta name="twitter:description" content="<?= e($desc) ?>">
<meta name="twitter:image" content="<?= e($og) ?>">
<meta name="theme-color" content="#1E3A8A">
<link rel="icon" href="<?= SITE_URL ?>/assets/img/logo/favicon.svg" type="image/svg+xml">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Baloo+Bhai+2:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/krishna.css">
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js" defer></script>
<script>window.KT = { url: <?= json_encode(SITE_URL) ?>, lang: <?= json_encode($lang) ?>, csrf: <?= json_encode(csrf_token()) ?>, loggedIn: <?= is_logged_in() ? 'true' : 'false' ?> };</script>
<?php
// Site-wide structured data: Organization + WebSite (Sitelinks Search box).
$orgLd = json_encode(['@context' => 'https://schema.org', '@graph' => [
    ['@type' => 'Organization', '@id' => SITE_URL . '/#org', 'name' => 'AK Computer',
     'url' => SITE_URL, 'logo' => SITE_URL . '/assets/img/logo/logo.svg',
     'description' => 'Computer & CCTV shop in Dwarka, Gujarat — makers of Krishna Tools.',
     'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Dwarka', 'addressRegion' => 'Gujarat', 'addressCountry' => 'IN'],
     'contactPoint' => ['@type' => 'ContactPoint', 'telephone' => setting('shop_phone', ''), 'contactType' => 'customer service']],
    ['@type' => 'WebSite', '@id' => SITE_URL . '/#website', 'url' => SITE_URL,
     'name' => $siteName, 'publisher' => ['@id' => SITE_URL . '/#org'],
     'inLanguage' => ['en', 'gu'],
     'potentialAction' => ['@type' => 'SearchAction',
         'target' => ['@type' => 'EntryPoint', 'urlTemplate' => SITE_URL . '/search.php?q={search_term_string}'],
         'query-input' => 'required name=search_term_string']],
]], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
<script type="application/ld+json"><?= $orgLd ?></script>
<?php if ($gaId): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($gaId) ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?= e($gaId) ?>');</script>
<?php endif; ?>
<?= $extra_head ?? '' ?>
</head>
<body class="morpankh-bg">
<script>
  // Apply saved theme before paint to avoid flash.
  (function(){var t=localStorage.getItem('kt-theme');if(t)document.documentElement.setAttribute('data-theme',t);})();
</script>

<?php if ($fest === 'janmashtami'): ?>
<div class="festival-ribbon text-sm">🦚 જન્માષ્ટમી ઓફર — વાર્ષિક પ્લાન પર ખાસ છૂટ! 🎉</div>
<?php endif; ?>

<header class="sticky top-0 z-40 backdrop-blur bg-[color:var(--card)]/90 border-b" style="border-color:var(--border)">
  <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between gap-4">
    <a href="<?= SITE_URL ?>/" class="flex items-center gap-2 shrink-0">
      <img src="<?= SITE_URL ?>/assets/img/logo/logo.svg" alt="Krishna Tools" class="w-9 h-9" onerror="this.style.display='none'">
      <span class="brand-title text-xl md:text-2xl brand"><?= e(brand_name()) ?></span>
    </a>

    <div class="hidden md:flex flex-1 max-w-md relative">
      <input id="globalSearch" type="text" placeholder="<?= t('search_tools') ?>"
             class="kt-input pl-10" autocomplete="off">
      <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 opacity-60"></i>
      <div id="searchResults" class="absolute top-12 left-0 right-0 kt-card p-2 hidden max-h-80 overflow-auto z-50"></div>
    </div>

    <nav class="flex items-center gap-1 md:gap-2">
      <a href="<?= SITE_URL ?>/converters.php" class="hidden md:inline-block btn btn-ghost text-sm"><?= current_lang() === 'en' ? 'Converters' : 'કન્વર્ટર' ?></a>
      <a href="<?= SITE_URL ?>/pricing.php" class="hidden md:inline-block btn btn-ghost text-sm"><?= t('pricing') ?></a>
      <button onclick="ktToggleTheme()" class="btn btn-ghost !px-2" title="Theme" aria-label="Theme">
        <i data-lucide="moon-star" class="w-4 h-4"></i>
      </button>
      <a href="?lang=<?= $lang === 'gu' ? 'en' : 'gu' ?>" class="btn btn-ghost !px-2 text-sm font-bold" title="Language">
        <?= $lang === 'gu' ? 'EN' : 'ગુ' ?>
      </a>
      <?php if ($user): ?>
        <a href="<?= SITE_URL ?>/dashboard.php" class="btn btn-ghost !px-2" title="<?= t('dashboard') ?>"><i data-lucide="layout-dashboard" class="w-4 h-4"></i></a>
        <?php if (is_admin()): ?><a href="<?= SITE_URL ?>/admin/" class="btn btn-ghost !px-2" title="Admin"><i data-lucide="shield" class="w-4 h-4"></i></a><?php endif; ?>
        <a href="<?= SITE_URL ?>/logout.php" class="btn btn-ghost text-sm hidden md:inline-flex"><?= t('logout') ?></a>
      <?php else: ?>
        <a href="<?= SITE_URL ?>/login.php" class="btn btn-primary text-sm"><?= t('login') ?></a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<?php if (!empty($breadcrumb)): ?>
<nav class="max-w-7xl mx-auto px-4 py-2 text-xs opacity-70 flex items-center gap-2 flex-wrap">
  <?php foreach ($breadcrumb as $i => $bc): ?>
    <?php if ($i) echo '<span>/</span>'; ?>
    <?php if (!empty($bc['url'])): ?><a href="<?= e($bc['url']) ?>" class="hover:text-[color:var(--saffron)]"><?= e($bc['label']) ?></a>
    <?php else: ?><span><?= e($bc['label']) ?></span><?php endif; ?>
  <?php endforeach; ?>
</nav>
<?php endif; ?>

<main class="max-w-7xl mx-auto px-4 pb-24 md:pb-10">
