<?php
/**
 * KRISHNA TOOLS — first-party visitor analytics.
 *
 * Every public page sends a small beacon (assets/js/app.js → api/track.php):
 *   - "view" once per page load  → page_views (kind = view)
 *   - "ping" every 30 s while the tab is visible → live_visitors (who is on now)
 *   - a tool being used (api/tool_log.php)       → page_views (kind = use)
 * Visitors are identified by a random id kept in their browser (no login needed).
 * Cities come from Cloudflare's visitor-location headers when present, otherwise
 * the "analytics_geo" cron job resolves new IPs in batches via ip-api.com.
 * The admin report lives at /admin/analytics.php.
 */
require_once __DIR__ . '/functions.php';

const KT_LIVE_WINDOW = 90; // seconds since the last ping that still counts as "live"

/** Create the analytics tables on first use (also in sql/krishnatools.sql). */
function kt_analytics_ensure(): void {
    static $done = false;
    if ($done) return;
    $done = true;
    if (setting('analytics_schema', '') === '1') return;
    $p = DB_PREFIX;
    q("CREATE TABLE IF NOT EXISTS `{$p}page_views` (
        `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `kind` ENUM('view','use') NOT NULL DEFAULT 'view',
        `vid` CHAR(16) NOT NULL,
        `user_id` INT UNSIGNED DEFAULT NULL,
        `path` VARCHAR(255) NOT NULL DEFAULT '/',
        `slug` VARCHAR(120) DEFAULT NULL,
        `ref_host` VARCHAR(120) DEFAULT NULL,
        `ip` VARCHAR(45) DEFAULT NULL,
        `device` VARCHAR(10) DEFAULT NULL,
        `lang` VARCHAR(5) DEFAULT NULL,
        `created_at` DATETIME NOT NULL,
        INDEX `pv_time` (`created_at`), INDEX `pv_vid` (`vid`), INDEX `pv_slug` (`slug`), INDEX `pv_ip` (`ip`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    q("CREATE TABLE IF NOT EXISTS `{$p}live_visitors` (
        `vid` CHAR(16) NOT NULL PRIMARY KEY,
        `user_id` INT UNSIGNED DEFAULT NULL,
        `path` VARCHAR(255) NOT NULL DEFAULT '/',
        `slug` VARCHAR(120) DEFAULT NULL,
        `ip` VARCHAR(45) DEFAULT NULL,
        `device` VARCHAR(10) DEFAULT NULL,
        `first_seen` DATETIME NOT NULL,
        `last_seen` DATETIME NOT NULL,
        INDEX `lv_seen` (`last_seen`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    q("CREATE TABLE IF NOT EXISTS `{$p}ip_geo` (
        `ip` VARCHAR(45) NOT NULL PRIMARY KEY,
        `city` VARCHAR(80) DEFAULT NULL,
        `region` VARCHAR(80) DEFAULT NULL,
        `country` VARCHAR(80) DEFAULT NULL,
        `isp` VARCHAR(120) DEFAULT NULL,
        `resolved_at` DATETIME NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    set_setting('analytics_schema', '1');
}

/** Remember the latest tracking failure (shown on the Live Analytics page) and log it. */
function kt_analytics_error(Throwable $e): void {
    $msg = date('Y-m-d H:i:s') . ' — ' . mb_substr($e->getMessage(), 0, 400);
    error_log('[analytics] ' . $msg);
    try { set_setting('analytics_last_error', $msg); } catch (Throwable $e2) {}
}

/** Crawlers and preview bots are not visitors. */
function kt_is_bot_ua(string $ua): bool {
    return $ua === '' || (bool) preg_match('/bot|crawl|spider|slurp|preview|headless|lighthouse|facebookexternalhit|whatsapp|curl|wget|python|java\//i', $ua);
}

function kt_device(string $ua): string {
    if (preg_match('/ipad|tablet/i', $ua)) return 'tablet';
    return preg_match('/mobile|android|iphone/i', $ua) ? 'mobile' : 'desktop';
}

/** Cache the visitor's location straight from Cloudflare headers when the site is behind Cloudflare. */
function kt_geo_from_headers(string $ip): void {
    $city = $_SERVER['HTTP_CF_IPCITY'] ?? '';
    $country = $_SERVER['HTTP_CF_IPCOUNTRY'] ?? '';
    if ($city === '' && $country === '') return;
    q("INSERT IGNORE INTO " . tbl('ip_geo') . " (ip, city, region, country, resolved_at) VALUES (:ip, :c, :r, :co, :t)",
      [':ip' => $ip, ':c' => mb_substr($city, 0, 80), ':r' => mb_substr($_SERVER['HTTP_CF_REGION'] ?? '', 0, 80),
       ':co' => $country, ':t' => date('Y-m-d H:i:s')]);
}

/**
 * Record a beacon. $kind: view | ping | use. Returns false when ignored.
 * Admins are not counted so the owner's own browsing does not inflate the report.
 */
function kt_track(string $kind, string $vid, string $path, ?string $slug = null, string $ref = '', string $lang = ''): bool {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (!preg_match('/^[a-f0-9]{16}$/', $vid) || kt_is_bot_ua($ua)) return false;
    if (function_exists('is_admin') && is_admin()) return false;
    kt_analytics_ensure();
    $now = date('Y-m-d H:i:s');
    $ip = client_ip();
    $uid = function_exists('current_user') ? (current_user()['id'] ?? null) : null;
    $path = mb_substr('/' . ltrim(parse_url($path, PHP_URL_PATH) ?: '/', '/'), 0, 255);
    $slug = $slug !== null && preg_match('/^[a-z0-9.-]{1,120}$/', $slug) ? $slug : null;
    $device = kt_device($ua);

    if ($kind === 'view' || $kind === 'use') {
        $refHost = $ref !== '' ? (parse_url($ref, PHP_URL_HOST) ?: null) : null;
        if ($refHost && $refHost === parse_url(SITE_URL, PHP_URL_HOST)) $refHost = null; // internal navigation
        q("INSERT INTO " . tbl('page_views') . " (kind, vid, user_id, path, slug, ref_host, ip, device, lang, created_at)
           VALUES (:k, :v, :u, :p, :s, :r, :ip, :d, :l, :t)",
          [':k' => $kind, ':v' => $vid, ':u' => $uid, ':p' => $path, ':s' => $slug,
           ':r' => $refHost ? mb_substr($refHost, 0, 120) : null, ':ip' => $ip, ':d' => $device,
           ':l' => in_array($lang, ['gu', 'en'], true) ? $lang : null, ':t' => $now]);
        kt_geo_from_headers($ip);
    }
    if ($kind === 'view' || $kind === 'ping') {
        q("INSERT INTO " . tbl('live_visitors') . " (vid, user_id, path, slug, ip, device, first_seen, last_seen)
           VALUES (:v, :u, :p, :s, :ip, :d, :t, :t2)
           ON DUPLICATE KEY UPDATE first_seen = IF(last_seen < :gap, VALUES(first_seen), first_seen),
             user_id = VALUES(user_id), path = VALUES(path), slug = VALUES(slug),
             ip = VALUES(ip), device = VALUES(device), last_seen = VALUES(last_seen)",
          [':v' => $vid, ':u' => $uid, ':p' => $path, ':s' => $slug, ':ip' => $ip, ':d' => $device, ':t' => $now, ':t2' => $now,
           ':gap' => date('Y-m-d H:i:s', time() - 1800)]);
    }
    return true;
}

/**
 * Cron: resolve up to 100 unknown visitor IPs per run via ip-api.com's batch API
 * (free, no key; 15 batch requests/minute). Private/local IPs are skipped.
 */
function kt_job_analytics_geo(): string {
    kt_analytics_ensure();
    $rows = all("SELECT DISTINCT pv.ip FROM " . tbl('page_views') . " pv
                 LEFT JOIN " . tbl('ip_geo') . " g ON g.ip = pv.ip
                 WHERE pv.ip IS NOT NULL AND g.ip IS NULL AND pv.created_at > :since
                 LIMIT 100", [':since' => date('Y-m-d H:i:s', strtotime('-7 days'))]);
    $ips = [];
    foreach ($rows as $r) {
        if (filter_var($r['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) $ips[] = $r['ip'];
        else q("INSERT IGNORE INTO " . tbl('ip_geo') . " (ip, city, country, resolved_at) VALUES (:ip, 'Local', 'Local', NOW())", [':ip' => $r['ip']]);
    }
    if (!$ips) return 'No new IPs.';
    $body = json_encode(array_map(fn($ip) => ['query' => $ip, 'fields' => 'status,city,regionName,country,isp,query'], $ips));
    $ch = curl_init('http://ip-api.com/batch');
    curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $body, CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15, CURLOPT_HTTPHEADER => ['Content-Type: application/json']]);
    $res = json_decode((string) curl_exec($ch), true);
    if (!is_array($res)) $res = kt_geo_ipwhois(array_slice($ips, 0, 25)); // HTTPS fallback when plain HTTP is blocked
    if (!$res) return 'Geo lookup failed (will retry).';
    $n = 0;
    foreach ($res as $g) {
        if (empty($g['query'])) continue;
        $ok = ($g['status'] ?? '') === 'success';
        q("INSERT INTO " . tbl('ip_geo') . " (ip, city, region, country, isp, resolved_at) VALUES (:ip, :c, :r, :co, :isp, NOW())
           ON DUPLICATE KEY UPDATE city = VALUES(city), region = VALUES(region), country = VALUES(country), isp = VALUES(isp), resolved_at = NOW()",
          [':ip' => $g['query'], ':c' => $ok ? mb_substr($g['city'] ?? '', 0, 80) : 'Unknown', ':r' => $ok ? mb_substr($g['regionName'] ?? '', 0, 80) : null,
           ':co' => $ok ? mb_substr($g['country'] ?? '', 0, 80) : 'Unknown', ':isp' => $ok ? mb_substr($g['isp'] ?? '', 0, 120) : null]);
        $n++;
    }
    return "Resolved $n IPs.";
}

/** Fallback geo provider (ipwho.is, HTTPS, one request per IP). Returns ip-api shaped rows. */
function kt_geo_ipwhois(array $ips): array {
    $out = [];
    foreach ($ips as $ip) {
        $ch = curl_init('https://ipwho.is/' . rawurlencode($ip) . '?fields=success,city,region,country,connection');
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 8]);
        $g = json_decode((string) curl_exec($ch), true);
        if (!is_array($g)) break; // provider unreachable: retry next run
        $out[] = ['query' => $ip, 'status' => !empty($g['success']) ? 'success' : 'fail', 'city' => $g['city'] ?? '',
                  'regionName' => $g['region'] ?? '', 'country' => $g['country'] ?? '', 'isp' => $g['connection']['isp'] ?? ''];
    }
    return $out;
}

/** Cron: keep tables small — 13 months of views and 1 day of live rows (the geo cache is kept). */
function kt_job_analytics_prune(): string {
    kt_analytics_ensure();
    $a = q("DELETE FROM " . tbl('page_views') . " WHERE created_at < :t", [':t' => date('Y-m-d H:i:s', strtotime('-395 days'))])->rowCount();
    $b = q("DELETE FROM " . tbl('live_visitors') . " WHERE last_seen < :t", [':t' => date('Y-m-d H:i:s', strtotime('-1 day'))])->rowCount();
    return "Pruned views:$a live:$b";
}
