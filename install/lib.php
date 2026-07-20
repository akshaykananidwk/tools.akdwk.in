<?php
/**
 * KRISHNA TOOLS — installer library.
 * Self-contained: works BEFORE /config/config.php exists. Builds the schema
 * and seeds all data from the PHP registries so the DB always matches the app.
 */
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

define('KT_ROOT_DIR', dirname(__DIR__));
require_once KT_ROOT_DIR . '/includes/tools_registry.php';
require_once KT_ROOT_DIR . '/includes/seed_data.php';

/** Detect the site's base URL (scheme + host, no trailing slash). */
function install_site_url(): string {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') == 443
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $host = $_SERVER['HTTP_HOST'] ?? 'tools.akdwk.in';
    return ($https ? 'https' : 'http') . '://' . $host;
}

/** Connect using session-stored DB creds; throws PDOException on failure. */
function install_pdo(array $db): PDO {
    $dsn = 'mysql:host=' . $db['host'] . ';dbname=' . $db['name'] . ';charset=utf8mb4';
    $pdo = new PDO($dsn, $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec("SET NAMES utf8mb4");
    return $pdo;
}

/** Create all tables from sql/krishnatools.sql with the chosen prefix. */
function install_schema(PDO $pdo, string $prefix): void {
    $sql = file_get_contents(KT_ROOT_DIR . '/sql/krishnatools.sql');
    $sql = str_replace('{{PREFIX}}', $prefix, $sql);
    // Split on semicolons at line ends; execute each statement.
    foreach (array_filter(array_map('trim', preg_split('/;\s*[\r\n]/', $sql))) as $stmt) {
        if ($stmt === '' || str_starts_with($stmt, '--')) continue;
        $pdo->exec($stmt);
    }
}

/** Seed categories + tools from the registry. Safe to re-run (repair). */
function install_seed_tools(PDO $pdo, string $prefix): array {
    $catIds = [];
    $stmt = $pdo->prepare("INSERT INTO `{$prefix}categories` (name_en,name_gu,slug,icon,color,sort_order)
        VALUES (:en,:gu,:slug,:icon,:color,:so)
        ON DUPLICATE KEY UPDATE name_en=VALUES(name_en),name_gu=VALUES(name_gu),icon=VALUES(icon),color=VALUES(color),sort_order=VALUES(sort_order)");
    foreach (kt_categories() as $c) {
        $stmt->execute([':en'=>$c['name_en'],':gu'=>$c['name_gu'],':slug'=>$c['slug'],':icon'=>$c['icon'],':color'=>$c['color'],':so'=>$c['sort_order']]);
    }
    foreach ($pdo->query("SELECT id,slug FROM `{$prefix}categories`") as $r) $catIds[$r['slug']] = $r['id'];

    $ts = $pdo->prepare("INSERT INTO `{$prefix}tools` (name_en,name_gu,slug,category_id,icon,description_gu,description_en,is_premium,is_active,sort_order)
        VALUES (:en,:gu,:slug,:cat,:icon,:dg,:de,:prem,1,:so)
        ON DUPLICATE KEY UPDATE name_en=VALUES(name_en),name_gu=VALUES(name_gu),category_id=VALUES(category_id),icon=VALUES(icon),description_gu=VALUES(description_gu),description_en=VALUES(description_en),is_premium=VALUES(is_premium),sort_order=VALUES(sort_order)");
    $i = 0;
    foreach (kt_tools() as $t) {
        $ts->execute([
            ':en'=>$t['name_en'], ':gu'=>$t['name_gu'], ':slug'=>$t['slug'],
            ':cat'=>$catIds[$t['cat']] ?? null, ':icon'=>$t['icon'],
            ':dg'=>$t['desc_gu'], ':de'=>$t['desc_en'], ':prem'=>$t['premium'] ?? 0, ':so'=>$i++,
        ]);
    }
    return ['categories' => count(kt_categories()), 'tools' => count(kt_tools())];
}

/** Seed plans. Safe to re-run. */
function install_seed_plans(PDO $pdo, string $prefix): int {
    $has = (int) $pdo->query("SELECT COUNT(*) FROM `{$prefix}plans`")->fetchColumn();
    if ($has > 0) return $has; // don't overwrite admin-edited pricing on repair
    $st = $pdo->prepare("INSERT INTO `{$prefix}plans` (name_en,name_gu,price,duration_days,daily_limit,max_file_mb,features,is_active)
        VALUES (:en,:gu,:p,:d,:dl,:mb,:f,:a)");
    foreach (kt_plans() as $p) {
        $st->execute([':en'=>$p['name_en'],':gu'=>$p['name_gu'],':p'=>$p['price'],':d'=>$p['duration_days'],':dl'=>$p['daily_limit'],':mb'=>$p['max_file_mb'],':f'=>$p['features'],':a'=>$p['is_active']]);
    }
    return count(kt_plans());
}

/** Seed WhatsApp templates. Safe to re-run. */
function install_seed_templates(PDO $pdo, string $prefix): int {
    $st = $pdo->prepare("INSERT INTO `{$prefix}whatsapp_templates` (key_name,name_gu,body_gu,body_en,variables,is_active)
        VALUES (:k,:n,:bg,:be,:v,1)
        ON DUPLICATE KEY UPDATE name_gu=VALUES(name_gu),body_gu=VALUES(body_gu),body_en=VALUES(body_en),variables=VALUES(variables)");
    foreach (kt_wa_templates() as $t) {
        $st->execute([':k'=>$t['key_name'],':n'=>$t['name_gu'],':bg'=>$t['body_gu'],':be'=>$t['body_en'],':v'=>$t['variables']]);
    }
    return count(kt_wa_templates());
}

/** Seed blog posts. Safe to re-run. */
function install_seed_blog(PDO $pdo, string $prefix): int {
    $st = $pdo->prepare("INSERT INTO `{$prefix}blog_posts` (title_gu,title_en,slug,content_gu,content_en,meta_desc,is_published,created_at)
        VALUES (:tg,:te,:slug,:cg,:ce,:md,1,NOW())
        ON DUPLICATE KEY UPDATE title_gu=VALUES(title_gu),title_en=VALUES(title_en),content_gu=VALUES(content_gu),content_en=VALUES(content_en),meta_desc=VALUES(meta_desc)");
    foreach (kt_blog_posts() as $b) {
        $st->execute([':tg'=>$b['tg'],':te'=>$b['te'],':slug'=>$b['slug'],':cg'=>$b['cg'],':ce'=>$b['ce'],':md'=>$b['md']]);
    }
    return count(kt_blog_posts());
}

/** Create the admin user (idempotent by email). */
function install_admin(PDO $pdo, string $prefix, array $admin): int {
    $st = $pdo->prepare("INSERT INTO `{$prefix}users` (name,email,phone,password_hash,role,status,email_verified,created_at,referral_code)
        VALUES (:n,:e,:ph,:pw,'admin','active',1,NOW(),:rc)
        ON DUPLICATE KEY UPDATE name=VALUES(name),password_hash=VALUES(password_hash),role='admin',status='active'");
    $st->execute([
        ':n'=>$admin['name'], ':e'=>strtolower($admin['email']), ':ph'=>$admin['phone'] ?? '',
        ':pw'=>password_hash($admin['password'], PASSWORD_DEFAULT),
        ':rc'=>strtoupper(substr(md5($admin['email']), 0, 8)),
    ]);
    return (int) $pdo->lastInsertId();
}

/** Persist settings key/values. */
function install_settings(PDO $pdo, string $prefix, array $kv): void {
    $st = $pdo->prepare("INSERT INTO `{$prefix}settings` (k,v) VALUES (:k,:v) ON DUPLICATE KEY UPDATE v=VALUES(v)");
    foreach ($kv as $k => $v) $st->execute([':k'=>$k, ':v'=>$v]);
}

/** Write /config/config.php from the wizard data. */
function install_write_config(array $c): void {
    $esc = fn($s) => addslashes((string) $s);
    $secret = bin2hex(random_bytes(24));
    $php = "<?php\n/** KRISHNA TOOLS — auto-generated by the installer. Do not commit real secrets. */\n";
    // Idempotent guard: safe even if included twice via different path strings
    // (e.g. cron scripts using /cron/../config vs db.php's canonical path).
    $php .= "if (defined('DB_HOST')) { return; }\n";
    $lines = [
        'DB_HOST'=>$c['db']['host'], 'DB_NAME'=>$c['db']['name'], 'DB_USER'=>$c['db']['user'],
        'DB_PASS'=>$c['db']['pass'], 'DB_PREFIX'=>$c['db']['prefix'], 'DB_CHARSET'=>'utf8mb4',
        'SITE_NAME'=>$c['site']['name'], 'SITE_URL'=>$c['site']['url'], 'DEFAULT_LANG'=>$c['site']['lang'],
        'APP_TIMEZONE'=>$c['site']['tz'], 'CURRENCY'=>'INR', 'KT_CURRENCY_SYMBOL'=>'₹', 'APP_SECRET'=>$secret,
        'RAZORPAY_KEY_ID'=>$c['razorpay']['key'] ?? '', 'RAZORPAY_KEY_SECRET'=>$c['razorpay']['secret'] ?? '',
        'SMTP_HOST'=>$c['smtp']['host'] ?? '', 'SMTP_PORT'=>$c['smtp']['port'] ?? '587',
        'SMTP_USER'=>$c['smtp']['user'] ?? '', 'SMTP_PASS'=>$c['smtp']['pass'] ?? '',
        'SMTP_FROM_NAME'=>$c['smtp']['from'] ?? 'Krishna Tools',
        'WA_BASE_URL'=>$c['wa']['base'] ?? 'https://bulk.akdwk.in/api.php',
        'WA_INBOUND_URL'=>$c['wa']['inbound'] ?? 'https://bulk.akdwk.in/api/webhook_inbound.php',
        'WA_API_KEY'=>$c['wa']['key'] ?? '', 'WA_SESSION_ID'=>$c['wa']['session'] ?? '',
        'WA_SENDER'=>$c['wa']['sender'] ?? '', 'GA_ID'=>$c['ga'] ?? '',
    ];
    foreach ($lines as $k => $v) $php .= "define('$k', '" . $esc($v) . "');\n";
    $php .= "define('KT_ROOT', dirname(__DIR__));\n";
    $php .= "define('KT_UPLOADS', KT_ROOT . '/uploads');\n";
    $php .= "define('KT_LOGS', KT_ROOT . '/logs');\n";
    if (!is_dir(KT_ROOT_DIR . '/config')) @mkdir(KT_ROOT_DIR . '/config', 0755, true);
    file_put_contents(KT_ROOT_DIR . '/config/config.php', $php);
}

/** Write a hardened root .htaccess (clean URLs + security). */
function install_write_htaccess(): void {
    $ht = <<<'HT'
# KRISHNA TOOLS — clean URLs + security (auto-generated by installer)
Options -Indexes
<IfModule mod_rewrite.c>
  RewriteEngine On
  # Block the installer once locked
  RewriteCond %{DOCUMENT_ROOT}/config/install.lock -f
  RewriteRule ^install/ /?installed=1 [L,R=302]

  # Clean URLs: /tool/<slug>  /category/<slug>  /blog/<slug>
  RewriteRule ^tool/([a-z0-9-]+)/?$ tool.php?slug=$1 [L,QSA]
  RewriteRule ^category/([a-z0-9-]+)/?$ category.php?slug=$1 [L,QSA]
  RewriteRule ^blog/([a-z0-9-]+)/?$ blog/post.php?slug=$1 [L,QSA]
</IfModule>

# Security headers
<IfModule mod_headers.c>
  Header always set X-Frame-Options "SAMEORIGIN"
  Header always set X-Content-Type-Options "nosniff"
  Header always set Referrer-Policy "strict-origin-when-cross-origin"
  Header always set X-XSS-Protection "1; mode=block"
</IfModule>

# Protect sensitive files
<FilesMatch "^(config\.php|\.env|.*\.log|.*\.sql)$">
  Require all denied
</FilesMatch>

# Gzip + cache
<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE text/html text/css application/javascript application/json image/svg+xml
</IfModule>
<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresByType text/css "access plus 7 days"
  ExpiresByType application/javascript "access plus 7 days"
  ExpiresByType image/svg+xml "access plus 30 days"
</IfModule>
HT;
    file_put_contents(KT_ROOT_DIR . '/.htaccess', $ht);

    // Deny PHP execution inside /uploads.
    if (!is_dir(KT_ROOT_DIR . '/uploads')) @mkdir(KT_ROOT_DIR . '/uploads', 0755, true);
    file_put_contents(KT_ROOT_DIR . '/uploads/.htaccess',
        "php_flag engine off\n<FilesMatch \"\\.(php|phtml|phar)$\">\n  Require all denied\n</FilesMatch>\n");
}

/** Ensure runtime folders exist and are writable. */
function install_make_dirs(): void {
    foreach (['uploads', 'uploads/temp', 'uploads/share', 'logs', 'assets/img/logo', 'config'] as $d) {
        $p = KT_ROOT_DIR . '/' . $d;
        if (!is_dir($p)) @mkdir($p, 0755, true);
    }
}

/** Create the install.lock marker. */
function install_lock(): void {
    file_put_contents(KT_ROOT_DIR . '/config/install.lock', 'installed ' . date('c'));
}

function install_is_locked(): bool {
    return is_file(KT_ROOT_DIR . '/config/install.lock');
}

/** Requirement checks for step 1. Returns [key => [status, label, fix]]. */
function install_requirements(): array {
    $r = [];
    $ok = fn($c) => $c ? 'ok' : 'fail';
    $r['php'] = [version_compare(PHP_VERSION, '8.1.0', '>=') ? 'ok' : 'fail', 'PHP ≥ 8.1 (' . PHP_VERSION . ')', 'cPanel > Select PHP Version > 8.1+'];
    $r['pdo'] = [$ok(extension_loaded('pdo') && extension_loaded('pdo_mysql')), 'PDO + pdo_mysql', 'cPanel PHP extensions માં pdo_mysql ચાલુ કરો'];
    $r['mbstring'] = [$ok(extension_loaded('mbstring')), 'mbstring', 'PHP extensions માં mbstring ચાલુ કરો'];
    $r['json'] = [$ok(extension_loaded('json')), 'json', 'PHP json extension'];
    $r['fileinfo'] = [$ok(extension_loaded('fileinfo')), 'fileinfo', 'PHP fileinfo extension'];
    $r['curl'] = [$ok(extension_loaded('curl')), 'cURL (WhatsApp/Razorpay)', 'PHP curl extension ચાલુ કરો'];
    $r['gd'] = [(extension_loaded('gd') || extension_loaded('imagick')) ? 'ok' : 'warn', 'GD અથવા Imagick', 'ઇમેજ ટૂલ્સ માટે ભલામણ'];
    $r['zip'] = [extension_loaded('zip') ? 'ok' : 'warn', 'zip (bulk ZIP)', 'PHP zip extension'];
    $r['openssl'] = [$ok(extension_loaded('openssl')), 'openssl (પેમેન્ટ સિગ્નેચર)', 'PHP openssl extension'];
    $r['session'] = [$ok(function_exists('session_start')), 'session support', '-'];
    $r['fopen'] = [ini_get('allow_url_fopen') ? 'ok' : 'warn', 'allow_url_fopen', 'php.ini માં ચાલુ કરો'];
    $r['memory'] = [install_bytes(ini_get('memory_limit')) >= 256*1024*1024 ? 'ok' : 'warn', 'memory_limit ≥ 256M (' . ini_get('memory_limit') . ')', 'cPanel MultiPHP INI Editor'];
    $r['upload'] = [install_bytes(ini_get('upload_max_filesize')) >= 64*1024*1024 ? 'ok' : 'warn', 'upload_max_filesize ≥ 64M (' . ini_get('upload_max_filesize') . ')', 'cPanel MultiPHP INI Editor > upload_max_filesize = 64M'];
    $r['post'] = [install_bytes(ini_get('post_max_size')) >= 64*1024*1024 ? 'ok' : 'warn', 'post_max_size ≥ 64M (' . ini_get('post_max_size') . ')', 'cPanel MultiPHP INI Editor > post_max_size = 64M'];
    $r['exec'] = [((int) ini_get('max_execution_time') >= 120 || (int) ini_get('max_execution_time') === 0) ? 'ok' : 'warn', 'max_execution_time ≥ 120', 'cPanel MultiPHP INI Editor'];
    $r['https'] = [(!empty($_SERVER['HTTPS']) || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') ? 'ok' : 'warn', 'HTTPS (WhatsApp webhook)', 'cPanel > SSL/TLS Status > AutoSSL ચલાવો'];

    // Write tests.
    foreach (['config', 'uploads', 'uploads/temp', 'assets/img/logo', 'logs'] as $d) {
        $p = KT_ROOT_DIR . '/' . $d;
        if (!is_dir($p)) @mkdir($p, 0755, true);
        $test = $p . '/.kt_write_test';
        $wok = @file_put_contents($test, 'x') !== false;
        if ($wok) @unlink($test);
        $r['write_' . str_replace('/', '_', $d)] = [$wok ? 'ok' : 'fail', "લખવાની પરવાનગી: /$d/", "cPanel File Manager > $d ફોલ્ડર પર Right Click > Permissions > 755 સેટ કરો"];
    }

    // Optional binaries (never blocking).
    foreach (['gs' => 'Ghostscript (PDF compress)', 'convert' => 'ImageMagick', 'ffmpeg' => 'FFmpeg (video)'] as $bin => $label) {
        $found = install_has_binary($bin);
        $r['bin_' . $bin] = [$found ? 'ok' : 'warn', $label, $found ? '' : 'વૈકલ્પિક — JS fallback વપરાશે'];
    }
    return $r;
}

function install_bytes(string $val): int {
    $val = trim($val); if ($val === '' || $val === '-1') return PHP_INT_MAX;
    $n = (int) $val; $u = strtolower(substr($val, -1));
    return $n * ['k'=>1024, 'm'=>1048576, 'g'=>1073741824][$u] ?? $n;
}

function install_has_binary(string $bin): bool {
    if (!function_exists('shell_exec') || !is_callable('shell_exec')) return false;
    $disabled = explode(',', (string) ini_get('disable_functions'));
    if (in_array('shell_exec', array_map('trim', $disabled), true)) return false;
    $out = @shell_exec('command -v ' . escapeshellarg($bin) . ' 2>/dev/null');
    return !empty(trim((string) $out));
}
