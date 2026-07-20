<?php
/**
 * KRISHNA TOOLS — self-contained admin layout.
 * admin_head($title) opens the document + sidebar; admin_foot() closes it.
 * Kept separate from the public header.php so the site nav never leaks in.
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

/** Sidebar link definitions: [file, icon, label]. */
function admin_nav_links(): array {
    return [
        ['index.php',      'layout-dashboard', 'Dashboard'],
        ['users.php',      'users',            'Users'],
        ['plans.php',      'badge-indian-rupee','Plans'],
        ['payments.php',   'receipt',          'Payments'],
        ['tools.php',      'wrench',           'Tools'],
        ['categories.php', 'folder-tree',      'Categories'],
        ['whatsapp.php',   'message-circle',   'WhatsApp'],
        ['contact.php',    'inbox',            'Contact'],
        ['update.php',     'github',           'GitHub Update'],
        ['logs.php',       'scroll-text',      'Logs'],
        ['settings.php',   'settings',         'Settings'],
        ['backup.php',     'database-backup',  'Backup'],
    ];
}

/**
 * Output the opening HTML, styles and the admin sidebar.
 * A single <main> is opened and closed by admin_foot().
 */
function admin_head(string $title): void {
    $current = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
    $siteName = setting('site_name', 'કૃષ્ણા ટૂલ્સ');
    ?><!DOCTYPE html>
<html lang="gu" data-theme="light" class="gu">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> — <?= e($siteName) ?> Admin</title>
<meta name="robots" content="noindex,nofollow">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Baloo+Bhai+2:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/krishna.css">
<link rel="icon" href="<?= SITE_URL ?>/assets/img/logo/favicon.svg" type="image/svg+xml">
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js" defer></script>
<style>
  :root { --admin-w: 250px; }
  .admin-shell { display:flex; min-height:100vh; }
  .admin-side {
    width: var(--admin-w); flex-shrink:0; position:fixed; inset:0 auto 0 0;
    background: linear-gradient(180deg, var(--krishna-blue), #0b1f5c);
    color:#eef2ff; z-index:50; overflow-y:auto; transition: transform .25s ease;
  }
  .admin-main { flex:1; margin-inline-start: var(--admin-w); min-width:0; }
  .admin-link {
    display:flex; align-items:center; gap:.7rem; padding:.6rem .95rem;
    border-radius:12px; font-weight:600; font-size:.92rem; color:#dbe4ff;
    transition:.15s; margin:.15rem .6rem;
  }
  .admin-link:hover { background:rgba(255,255,255,.10); color:#fff; }
  .admin-link.active { background: linear-gradient(135deg, var(--gold), var(--saffron)); color:#1a1a1a; box-shadow: var(--shadow-gold); }
  .admin-topbar { position:sticky; top:0; z-index:40; }
  .admin-scrim { position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:45; display:none; }
  .kt-table { width:100%; border-collapse:collapse; font-size:.9rem; }
  .kt-table th, .kt-table td { padding:.6rem .7rem; text-align:start; border-bottom:1px solid var(--border); vertical-align:middle; }
  .kt-table th { font-weight:700; opacity:.75; white-space:nowrap; }
  .kt-table tr:hover td { background: rgba(245,158,11,.05); }
  .kpi { border-radius:18px 18px 18px 6px; }
  @media (max-width: 900px) {
    .admin-side { transform: translateX(-105%); }
    .admin-side.open { transform: none; }
    .admin-main { margin-inline-start:0; }
    .admin-side.open ~ .admin-scrim, .admin-scrim.show { display:block; }
  }
</style>
</head>
<body class="morpankh-bg">
<script>(function(){var t=localStorage.getItem('kt-theme');if(t)document.documentElement.setAttribute('data-theme',t);})();</script>
<div class="admin-shell">
  <aside class="admin-side" id="adminSide">
    <div class="px-5 py-4 flex items-center gap-2 border-b border-white/10">
      <span class="text-2xl">🦚</span>
      <div>
        <div class="font-extrabold leading-tight" style="font-family:'Baloo Bhai 2',sans-serif"><?= e($siteName) ?></div>
        <div class="text-[11px] uppercase tracking-widest opacity-70">Admin Panel</div>
      </div>
    </div>
    <nav class="py-2">
      <?php foreach (admin_nav_links() as [$file, $icon, $label]): ?>
        <a href="<?= SITE_URL ?>/admin/<?= e($file) ?>" class="admin-link <?= $current === $file ? 'active' : '' ?>">
          <i data-lucide="<?= e($icon) ?>" class="w-[18px] h-[18px]"></i><span><?= e($label) ?></span>
        </a>
      <?php endforeach; ?>
      <div class="my-2 mx-4 border-t border-white/10"></div>
      <a href="<?= SITE_URL ?>/" class="admin-link"><i data-lucide="external-link" class="w-[18px] h-[18px]"></i><span>સાઇટ જુઓ</span></a>
      <a href="<?= SITE_URL ?>/logout.php" class="admin-link"><i data-lucide="log-out" class="w-[18px] h-[18px]"></i><span>લોગઆઉટ</span></a>
    </nav>
  </aside>
  <div class="admin-scrim" id="adminScrim" onclick="ktAdminMenu(false)"></div>

  <div class="admin-main">
    <header class="admin-topbar backdrop-blur bg-[color:var(--card)]/90 border-b" style="border-color:var(--border)">
      <div class="px-4 h-14 flex items-center justify-between gap-3">
        <div class="flex items-center gap-2">
          <button class="btn btn-ghost !px-2 md:!hidden" onclick="ktAdminMenu(true)" aria-label="Menu"><i data-lucide="menu" class="w-5 h-5"></i></button>
          <h1 class="section-title text-lg md:text-xl m-0" style="font-family:'Baloo Bhai 2',sans-serif"><?= e($title) ?></h1>
        </div>
        <div class="flex items-center gap-2">
          <button onclick="ktToggleAdminTheme()" class="btn btn-ghost !px-2" title="Theme" aria-label="Theme"><i data-lucide="moon-star" class="w-4 h-4"></i></button>
          <?php $au = current_user(); ?>
          <span class="hidden sm:inline text-sm opacity-70"><?= e($au['name'] ?? 'Admin') ?></span>
        </div>
      </div>
    </header>
    <main class="p-4 md:p-6 max-w-[1400px]">
<?php
}

/** Close the layout and boot Lucide icons. */
function admin_foot(): void {
    ?>
    </main>
  </div>
</div>
<script>
  function ktAdminMenu(open){
    var s=document.getElementById('adminSide'), c=document.getElementById('adminScrim');
    if(open){ s.classList.add('open'); c.classList.add('show'); }
    else { s.classList.remove('open'); c.classList.remove('show'); }
  }
  function ktToggleAdminTheme(){
    var el=document.documentElement, next=el.getAttribute('data-theme')==='dark'?'light':'dark';
    el.setAttribute('data-theme',next); localStorage.setItem('kt-theme',next);
  }
  window.addEventListener('load', function(){ if(window.lucide) lucide.createIcons(); });
  document.addEventListener('DOMContentLoaded', function(){ if(window.lucide) lucide.createIcons(); });
</script>
</body>
</html>
<?php
}

/** Small helper: verify CSRF on POST (per contract). */
function admin_csrf_check(): void {
    kt_session_start();
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419);
        exit('CSRF');
    }
}

/** Build a querystring preserving current GET params with overrides. */
function admin_qs(array $overrides = []): string {
    $q = array_merge($_GET, $overrides);
    return http_build_query($q);
}

/** Render a compact pagination bar. */
function admin_pager(int $page, int $total, int $perPage): string {
    $pages = max(1, (int) ceil($total / $perPage));
    if ($pages <= 1) return '';
    $out = '<div class="flex items-center gap-2 flex-wrap mt-4 text-sm">';
    $prev = max(1, $page - 1);
    $next = min($pages, $page + 1);
    $out .= '<a class="btn btn-ghost !py-1 !px-3" href="?' . e(admin_qs(['page' => $prev])) . '">‹</a>';
    $out .= '<span class="opacity-70">પાનું ' . $page . ' / ' . $pages . '</span>';
    $out .= '<a class="btn btn-ghost !py-1 !px-3" href="?' . e(admin_qs(['page' => $next])) . '">›</a>';
    $out .= '</div>';
    return $out;
}
