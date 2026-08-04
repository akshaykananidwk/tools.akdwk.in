<?php
/**
 * KRISHNA TOOLS — DEPRECATED shim (see cron/run.php).
 * Runs the 'plan_expiry' job through the centralized scheduler so an old
 * `0 9 * * * php cron/expiry_reminder.php` cron keeps working.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit("CLI only\n"); }
$config = __DIR__ . '/../config/config.php';
if (!is_file($config)) { fwrite(STDERR, "config missing\n"); exit(1); }
require_once $config;
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/scheduler.php';
$res = (new Scheduler())->runJob('plan_expiry', true, 'cli');
echo "[" . date('Y-m-d H:i:s') . "] plan_expiry: {$res['status']} — {$res['message']}\n";
