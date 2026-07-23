<?php
/**
 * KRISHNA TOOLS — shared helpers: bootstrap, settings, i18n, escaping, logging.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/tools_registry.php';
require_once __DIR__ . '/seed_data.php'; // shloks (daily_shlok), plans/templates/blog seeds

if (defined('APP_TIMEZONE')) {
    date_default_timezone_set(APP_TIMEZONE);
}

// Production error posture: never print warnings/deprecations to visitors
// (PHP 8.5 deprecates several old cURL/date calls); log them to a file instead.
// (E_STRICT was removed in PHP 8.4 — referencing it would itself warn.)
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
@ini_set('display_errors', '0');
@ini_set('log_errors', '1');
if (defined('KT_LOGS')) @ini_set('error_log', KT_LOGS . '/php_error.log');

/* ── Session ─────────────────────────────────────────────── */
function kt_session_start(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/* ── Settings (cached key/value table) ───────────────────── */
function settings(): array {
    static $cache = null;
    if ($cache !== null) return $cache;
    $cache = [];
    try {
        foreach (all("SELECT k, v FROM " . tbl('settings')) as $r) {
            $cache[$r['k']] = $r['v'];
        }
    } catch (Throwable $e) { /* table may not exist pre-install */ }
    return $cache;
}

function setting(string $key, $default = null) {
    $s = settings();
    return array_key_exists($key, $s) ? $s[$key] : $default;
}

function set_setting(string $key, $value): void {
    q("INSERT INTO " . tbl('settings') . " (k, v) VALUES (:k, :v)
       ON DUPLICATE KEY UPDATE v = :v2", [':k' => $key, ':v' => $value, ':v2' => $value]);
}

/* ── i18n ────────────────────────────────────────────────── */
function current_lang(): string {
    kt_session_start();
    if (isset($_GET['lang']) && in_array($_GET['lang'], ['gu', 'en'], true)) {
        $_SESSION['lang'] = $_GET['lang'];
    }
    // Default language is admin-configurable (Settings → default_lang); English
    // by default. A visitor's toggle choice (session) always wins.
    return $_SESSION['lang'] ?? setting('default_lang', (defined('DEFAULT_LANG') ? DEFAULT_LANG : 'en'));
}

function lang_pack(): array {
    static $pack = null;
    if ($pack !== null) return $pack;
    $lang = current_lang();
    $file = __DIR__ . '/lang/' . ($lang === 'en' ? 'en' : 'gu') . '.php';
    $pack = is_file($file) ? require $file : [];
    return $pack;
}

/** Translate a key; falls back to the key itself. Supports :placeholders. */
function t(string $key, array $vars = []): string {
    $pack = lang_pack();
    $str  = $pack[$key] ?? $key;
    foreach ($vars as $k => $v) $str = str_replace(':' . $k, (string) $v, $str);
    return $str;
}

/**
 * Asset cache-busting version. Changes on every GitHub self-update (so browsers
 * fetch fresh CSS/JS instead of a stale 7-day-cached copy), falling back to the
 * app.js file modification time for manual installs.
 */
function asset_ver(): string {
    static $v = null;
    if ($v !== null) return $v;
    $gh = setting('gh_current_version', '');
    if ($gh !== '') return $v = substr($gh, 0, 8);
    return $v = (string) (@filemtime(KT_ROOT . '/assets/js/app.js') ?: '1');
}

/** The brand/site name in the current language (English default: "Krishna Tools"). */
function brand_name(): string {
    if (current_lang() === 'en') return setting('site_name_en', 'Krishna Tools');
    return setting('site_name', 'કૃષ્ણા ટૂલ્સ');
}

/** Pick the language-appropriate field from a row with _gu/_en variants. */
function loc(array $row, string $base): string {
    $suffix = current_lang() === 'en' ? '_en' : '_gu';
    return $row[$base . $suffix] ?? $row[$base . '_gu'] ?? $row[$base . '_en'] ?? '';
}

/* ── Escaping / output ───────────────────────────────────── */
function e(?string $s): string {
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function json_out($data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

/* ── Client info ─────────────────────────────────────────── */
function client_ip(): string {
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $k) {
        if (!empty($_SERVER[$k])) {
            $ip = trim(explode(',', $_SERVER[$k])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return '0.0.0.0';
}

/* ── Logging ─────────────────────────────────────────────── */
function activity_log(?int $userId, string $action, string $details = ''): void {
    try {
        insert('activity_logs', [
            'user_id' => $userId,
            'action'  => $action,
            'details' => mb_substr($details, 0, 2000),
            'ip'      => client_ip(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    } catch (Throwable $e) { /* logging must never break flow */ }
}

function kt_error_log(string $msg): void {
    if (defined('KT_LOGS')) {
        @error_log('[' . date('c') . '] ' . $msg . "\n", 3, KT_LOGS . '/error.log');
    }
}

/* ── Misc ────────────────────────────────────────────────── */
function money(float $n): string {
    // Note: CURRENCY_SYMBOL is a predefined PHP constant, so we use KT_CURRENCY_SYMBOL.
    return (defined('KT_CURRENCY_SYMBOL') ? KT_CURRENCY_SYMBOL : '₹') . number_format($n, 2);
}

function rand_token(int $len = 24): string {
    return bin2hex(random_bytes(max(8, (int) ($len / 2))));
}

/** Normalise an Indian phone to 91XXXXXXXXXX. */
function normalize_phone(string $num): string {
    $d = preg_replace('/\D+/', '', $num);
    if (strlen($d) === 10) $d = '91' . $d;
    if (strlen($d) === 12 && str_starts_with($d, '91')) return $d;
    // strip leading zeros / country code variants best-effort
    if (strlen($d) > 12 && str_starts_with($d, '91')) return substr($d, 0, 12);
    return $d;
}

/** Is the current visitor on maintenance-exempt path? */
function maintenance_active(): bool {
    return setting('maintenance_mode', '0') === '1';
}

/**
 * Render the daily Bhagavad Gita shlok (rotates by day-of-year).
 * Returns ['sanskrit'=>..., 'gu'=>...].
 */
function daily_shlok(): array {
    $shloks = kt_shloks();
    $idx = ((int) date('z')) % count($shloks);
    return $shloks[$idx];
}

/** Is a festival theme currently active? Returns theme key or ''. */
function festival_theme(): string {
    $m = (int) date('n');
    if ($m === 8) return 'janmashtami'; // August
    return '';
}
