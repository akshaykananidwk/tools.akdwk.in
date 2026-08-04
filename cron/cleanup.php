<?php
/**
 * KRISHNA TOOLS — DEPRECATED shim.
 *
 * Cleanup is now a job in the centralized scheduler. This file remains only so
 * an old `* * * * * php cron/cleanup.php` cron keeps working — it simply runs
 * the 'cleanup' job through the scheduler. Prefer the single master cron:
 *   * * * * * php cron/run.php
 */
$cliMode = (PHP_SAPI === 'cli');
$config = __DIR__ . '/../config/config.php';
if (!is_file($config)) { if (!$cliMode) http_response_code(503); exit(1); }
require_once $config;
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/scheduler.php';
if (!$cliMode) {
    $expected = setting('cron_key', '');
    if ($expected === '' || !hash_equals($expected, (string) ($_GET['key'] ?? ''))) { http_response_code(403); exit('forbidden'); }
    header('Content-Type: text/plain; charset=utf-8');
}
$res = (new Scheduler())->runJob('cleanup', true, $cliMode ? 'cli' : 'http');
echo "[" . date('Y-m-d H:i:s') . "] cleanup: {$res['status']} — {$res['message']}\n";
