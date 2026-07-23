<?php
/**
 * KRISHNA TOOLS — cleanup cron.
 *
 * Deletes stale temp/share uploads (>2h), expired saved_files rows + their
 * files, and old guest_limits rows (>2 days). Run from CLI or over HTTP with
 * ?key=<cron_key> where cron_key matches the settings value.
 *
 * Usage:  php cron/cleanup.php
 *         curl "https://tools.akdwk.in/cron/cleanup.php?key=SECRET"
 */

$cliMode = (PHP_SAPI === 'cli');

// Bootstrap config + functions (guard if config missing).
$config = __DIR__ . '/../config/config.php';
if (!is_file($config)) {
    if (!$cliMode) http_response_code(503);
    fwrite(STDERR, "config missing — not installed\n");
    exit(1);
}
require_once $config;
require_once __DIR__ . '/../includes/functions.php';

// Access guard: CLI is always allowed; HTTP needs a matching secret key.
if (!$cliMode) {
    $key = $_GET['key'] ?? '';
    $expected = setting('cron_key', '');
    if ($expected === '' || !hash_equals($expected, (string) $key)) {
        http_response_code(403);
        exit('forbidden');
    }
    header('Content-Type: text/plain; charset=utf-8');
}

$now = time();
$maxAge = 2 * 3600;                 // 2 hours
$uploads = defined('KT_UPLOADS') ? KT_UPLOADS : (dirname(__DIR__) . '/uploads');

$stats = ['temp_files' => 0, 'share_files' => 0, 'share_dirs' => 0, 'saved_rows' => 0, 'saved_files' => 0, 'guest_rows' => 0];

/** Delete files older than $maxAge directly under $dir (recursively). */
function kt_purge_old_files(string $dir, int $maxAge, int $now, int &$fileCount): void {
    if (!is_dir($dir)) return;
    foreach (scandir($dir) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..') continue;
        $path = $dir . '/' . $entry;
        if (is_dir($path)) {
            kt_purge_old_files($path, $maxAge, $now, $fileCount);
        } elseif (is_file($path) && ($now - @filemtime($path)) > $maxAge) {
            if (@unlink($path)) $fileCount++;
        }
    }
}

/** Remove now-empty subdirectories under $dir. */
function kt_remove_empty_dirs(string $dir, int &$dirCount): void {
    if (!is_dir($dir)) return;
    foreach (scandir($dir) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..') continue;
        $path = $dir . '/' . $entry;
        if (is_dir($path)) {
            kt_remove_empty_dirs($path, $dirCount);
            $remaining = array_diff(scandir($path) ?: [], ['.', '..']);
            if (empty($remaining) && @rmdir($path)) $dirCount++;
        }
    }
}

// 1) Temp uploads.
kt_purge_old_files($uploads . '/temp', $maxAge, $now, $stats['temp_files']);

// 2) Share uploads + empty token dirs.
kt_purge_old_files($uploads . '/share', $maxAge, $now, $stats['share_files']);
kt_remove_empty_dirs($uploads . '/share', $stats['share_dirs']);

// 3) Expired saved_files rows and their underlying files.
try {
    $expired = all("SELECT id, path FROM " . tbl('saved_files') . " WHERE expires_at IS NOT NULL AND expires_at < NOW()");
    foreach ($expired as $row) {
        if (!empty($row['path'])) {
            $abs = (defined('KT_ROOT') ? KT_ROOT : dirname(__DIR__)) . '/' . ltrim($row['path'], '/');
            if (is_file($abs) && @unlink($abs)) $stats['saved_files']++;
        }
        q("DELETE FROM " . tbl('saved_files') . " WHERE id = :id", [':id' => $row['id']]);
        $stats['saved_rows']++;
    }
} catch (Throwable $e) {
    fwrite(STDERR, 'saved_files cleanup error: ' . $e->getMessage() . "\n");
}

// 4) Old guest_limits rows (>2 days).
try {
    $stats['guest_rows'] = q("DELETE FROM " . tbl('guest_limits') . " WHERE day < :d",
        [':d' => date('Y-m-d', $now - 2 * 86400)])->rowCount();
} catch (Throwable $e) {
    fwrite(STDERR, 'guest_limits cleanup error: ' . $e->getMessage() . "\n");
}

// 5) Expired webhook bins + their requests (>24h).
try {
    $expiredBins = all("SELECT token FROM " . tbl('webhook_bins') . " WHERE expires_at < NOW()");
    foreach ($expiredBins as $b) {
        q("DELETE FROM " . tbl('webhook_requests') . " WHERE bin_token = :t", [':t' => $b['token']]);
    }
    $stats['webhook_bins'] = q("DELETE FROM " . tbl('webhook_bins') . " WHERE expires_at < NOW()")->rowCount();
} catch (Throwable $e) {
    // Table may not exist on older installs — ignore.
    $stats['webhook_bins'] = 0;
}

// Summary.
$summary = sprintf(
    "[%s] cleanup done — temp:%d share:%d dirs:%d saved_rows:%d saved_files:%d guest_rows:%d webhook_bins:%d\n",
    date('Y-m-d H:i:s'),
    $stats['temp_files'], $stats['share_files'], $stats['share_dirs'],
    $stats['saved_rows'], $stats['saved_files'], $stats['guest_rows'], $stats['webhook_bins'] ?? 0
);
echo $summary;
