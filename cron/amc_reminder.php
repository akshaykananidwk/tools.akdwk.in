<?php
/**
 * KRISHNA TOOLS — DEPRECATED shim (see cron/run.php).
 * Runs the 'amc_reminder' job through the centralized scheduler so an old
 * `0 10 * * * php cron/amc_reminder.php` cron keeps working.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit("CLI only\n"); }
$config = __DIR__ . '/../config/config.php';
if (!is_file($config)) { fwrite(STDERR, "config missing\n"); exit(1); }
require_once $config;
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/scheduler.php';
$res = (new Scheduler())->runJob('amc_reminder', true, 'cli');
echo "[" . date('Y-m-d H:i:s') . "] amc_reminder: {$res['status']} — {$res['message']}\n";
