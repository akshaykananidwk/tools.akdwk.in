<?php
/**
 * KRISHNA TOOLS — global HTML head + top navigation.
 * Expects optional: $page_title, $page_desc, $page_og, $extra_head, $breadcrumb.
 */
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
kt_session_start();
if (!defined('KT_ROOT_PUBLIC')) define('KT_ROOT_PUBLIC', dirname(__DIR__));

$lang     = current_lang();
$title    = $page_title ?? (brand_name() . ' — ' . t('tagline'));
$desc     = $page_desc  ?? t('tagline');
$og       = $page_og    ?? (SITE_URL . '/assets/img/logo/og.png');
$fest     = festival_theme();
$user     = current_user();
$gaId     = setting('ga_id', defined('GA_ID') ? GA_ID : '');
?><!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<?php
// Canonical + hreflang alternates: strip any ?lang= so both languages point at
// one canonical URL, and advertise the en/gu variants for international SEO.
// Self-canonical: the path only, so ?ref= (AK Earn partner links), ?utm_… and other tracking
// parameters never create duplicate URLs. Only real pagination (?page=N) is kept. /index.php → /.
$reqUri = $_SERVER['REQUEST_URI'] ?? '/';
$cleanPath = preg_replace('#/index\.php$#', '/', (string) parse_url($reqUri, PHP_URL_PATH)) ?: '/';
parse_str((string) parse_url($reqUri, PHP_URL_QUERY), $__qs);
$cleanUri = $cleanPath . (isset($__qs['page']) && ctype_digit((string) $__qs['page']) && (int) $__qs['page'] > 1 ? '?page=' . (int) $__qs['page'] : '');
unset($__qs);
// Pages may force a clean canonical URL (e.g. generated converter pages).
$canonical = $page_canonical ?? (SITE_URL . $cleanUri);
$sep = str_contains($cleanUri, '?') ? '&' : '?';
$siteName = brand_name();
?>
<meta name="robots" content="<?= e($page_robots ?? 'index, follow, max-image-preview:large') ?>">
<?php if (!empty($page_keywords)): ?><meta name="keywords" content="<?= e($page_keywords) ?>"><?php endif; ?>
<meta name="author" content="AK Computer, Dwarka">
<link rel="canonical" href="<?= e($canonical) ?>">
<!-- Open Graph -->
<meta property="og:site_name" content="<?= e($siteName) ?>">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:image" content="<?= e($og) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:type" content="website">
<meta property="og:locale" content="en_IN">
<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($title) ?>">
<meta name="twitter:description" content="<?= e($desc) ?>">
<meta name="twitter:image" content="<?= e($og) ?>">
<meta name="theme-color" content="#1E3A8A">
<link rel="icon" href="<?= SITE_URL ?>/assets/img/logo/favicon.svg" type="image/svg+xml">
<link rel="manifest" href="<?= SITE_URL ?>/manifest.webmanifest">
<link rel="apple-touch-icon" href="<?= SITE_URL ?>/assets/img/logo/apple-touch-icon.png">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="Krishna Tools">
<!-- Self-hosted fonts (no third-party request). Loaded after the first paint: text shows at once in a
     system font and swaps to Inter / Baloo Bhai 2 when the files arrive (font-display: swap). -->
<link rel="preload" as="style" href="<?= SITE_URL ?>/assets/fonts/fonts.css?v=<?= asset_ver() ?>" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="<?= SITE_URL ?>/assets/fonts/fonts.css?v=<?= asset_ver() ?>"></noscript>
<?php
// The site CSS is small (≈27 KB, ≈7 KB gzipped), so it is inlined: no render-blocking CSS request.
// Order matters: krishna.css first, then Tailwind utilities (the same order the old CDN script used).
$__css = '';
foreach (['css/krishna.css', 'css/tailwind.css'] as $__f) { $__p = KT_ROOT_PUBLIC . '/assets/' . $__f; if (is_file($__p)) $__css .= file_get_contents($__p) . "\n"; }
if ($__css !== ''): ?>
<style><?= $__css ?></style>
<?php else: ?>
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/krishna.css?v=<?= asset_ver() ?>">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/tailwind.css?v=<?= asset_ver() ?>">
<?php endif; unset($__css, $__f, $__p); ?>
<script src="<?= SITE_URL ?>/assets/js/icons.js?v=<?= asset_ver() ?>" defer></script>
<script>window.KT = { url: <?= json_encode(SITE_URL) ?>, lang: <?= json_encode($lang) ?>, csrf: <?= json_encode(csrf_token()) ?>, loggedIn: <?= is_logged_in() ? 'true' : 'false' ?>, ver: <?= json_encode(asset_ver()) ?> };</script>
<?php
// Site-wide structured data: Organization + WebSite (Sitelinks Search box).
$orgLd = json_encode(['@context' => 'https://schema.org', '@graph' => [
    ['@type' => 'Organization', '@id' => SITE_URL . '/#org', 'name' => 'AK Computer',
     'url' => SITE_URL, 'logo' => SITE_URL . '/assets/img/logo/logo.svg',
     'description' => 'Computer & CCTV shop in Dwarka, Gujarat — makers of Krishna Tools.',
     'address' => array_filter(['@type' => 'PostalAddress', 'streetAddress' => setting('shop_address', ''), 'addressLocality' => 'Dwarka', 'addressRegion' => 'Gujarat', 'postalCode' => '361335', 'addressCountry' => 'IN']),
     'contactPoint' => ['@type' => 'ContactPoint', 'telephone' => setting('shop_phone', ''), 'contactType' => 'customer service']],
    ['@type' => 'WebSite', '@id' => SITE_URL . '/#website', 'url' => SITE_URL,
     'name' => $siteName, 'publisher' => ['@id' => SITE_URL . '/#org'],
     'inLanguage' => 'en',
     'potentialAction' => ['@type' => 'SearchAction',
         'target' => ['@type' => 'EntryPoint', 'urlTemplate' => SITE_URL . '/search.php?q={search_term_string}'],
         'query-input' => 'required name=search_term_string']],
]], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
<?php if (($cleanPath ?? '/') === '/'): // Organization + WebSite belong on the home page only ?>
<script type="application/ld+json"><?= $orgLd ?></script>
<?php endif; ?>
<?php if ($gaId): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($gaId) ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?= e($gaId) ?>');</script>
<?php endif; ?>
<!-- Notification.AKDWK.in: analytics -->
<script src="https://notification.akdwk.in/sdk/v1.js" data-site-id="01m502jvh51tdcjbd8ndhn2fsa" defer></script>
<?= $extra_head ?? '' ?>
<?php
// BreadcrumbList for every page that shows a breadcrumb, unless the page already outputs one.
if (!empty($breadcrumb) && !str_contains((string) ($extra_head ?? ''), 'BreadcrumbList')) {
    $__bc = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => []];
    foreach (array_values($breadcrumb) as $__i => $__b) {
        $__it = ['@type' => 'ListItem', 'position' => $__i + 1, 'name' => (string) $__b['label']];
        $__it['item'] = !empty($__b['url']) ? $__b['url'] : $canonical;
        $__bc['itemListElement'][] = $__it;
    }
    echo '<script type="application/ld+json">' . json_encode($__bc, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";
    unset($__bc, $__i, $__b, $__it);
}
?>
</head>
<body class="morpankh-bg">
<script>
  // Apply saved theme before paint to avoid flash.
  (function(){var t=localStorage.getItem('kt-theme');if(t)document.documentElement.setAttribute('data-theme',t);})();
</script>


<?php if ($fest === 'janmashtami'): ?>
<div class="festival-ribbon text-sm">🦚 Happy Janmashtami from Krishna Tools! 🎉</div>
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
      <button id="mobileSearchBtn" class="btn btn-ghost !px-2 md:hidden" title="Search tools and converters" aria-label="Search"><i data-lucide="search" class="w-4 h-4"></i></button>
      <a href="<?= SITE_URL ?>/converters.php" class="hidden md:inline-block btn btn-ghost text-sm"><?= current_lang() === 'en' ? 'Converters' : 'કન્વર્ટર' ?></a>
      <?php if (!kt_all_free()): ?><a href="<?= SITE_URL ?>/pricing.php" class="hidden md:inline-block btn btn-ghost text-sm"><?= t('pricing') ?></a><?php endif; ?>
      <button onclick="ktToggleTheme()" class="btn btn-ghost !px-2" title="Theme" aria-label="Theme">
        <i data-lucide="moon-star" class="w-4 h-4"></i>
      </button>
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
<!-- Mobile search (header icon toggles it): tools + converters, suggestions while typing -->
<div id="mobileSearchBar" class="hidden md:hidden max-w-7xl mx-auto px-4 pt-3 relative">
  <input id="mobileSearch" type="search" placeholder="Search tools or type “30 qtl to kg”" class="kt-input pl-10" autocomplete="off" enterkeyhint="go">
  <i data-lucide="search" class="w-4 h-4 absolute left-7 top-[1.6rem] opacity-60"></i>
  <div id="mobileResults" class="absolute left-4 right-4 top-16 kt-card p-2 hidden max-h-96 overflow-auto z-50"></div>
</div>

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
