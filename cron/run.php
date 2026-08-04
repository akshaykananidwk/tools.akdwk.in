<?php
/**
 * KRISHNA TOOLS — MASTER CRON (the only server cron you need).
 *
 * Install ONE line on the server:
 *   * * * * * php /home/USER/tools.akdwk.in/cron/run.php >/dev/null 2>&1
 *
 * Or, if your host only offers URL crons (no CLI), hit it over HTTP with the
 * secret key from Admin → Settings (cron_key):
 *   * * * * * curl -s "https://tools.akdwk.in/cron/run.php?key=SECRET"
 *
 * Every minute this runner asks the Scheduler to execute all ENABLED + DUE
 * background jobs (message queue, reminders, cleanup, backups, …). A flock
 * master lock prevents overlapping runs; each job also takes its own DB lock.
 */

$cliMode = (PHP_SAPI === 'cli');

$config = __DIR__ . '/../config/config.php';
if (!is_file($config)) {
    if (!$cliMode) http_response_code(503);
    fwrite(STDERR, "config missing — not installed\n");
    exit(1);
}
require_once $config;
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/scheduler.php';

// HTTP access needs the secret key; CLI is always allowed.
if (!$cliMode) {
    $expected = setting('cron_key', '');
    if ($expected === '' || !hash_equals($expected, (string) ($_GET['key'] ?? ''))) {
        http_response_code(403);
        exit('forbidden');
    }
    header('Content-Type: text/plain; charset=utf-8');
}

@set_time_limit(0);
$scheduler = new Scheduler();

// Non-blocking master lock: if a previous minute's run is still going, skip.
if (!$scheduler->acquireMasterLock()) {
    echo "[" . date('Y-m-d H:i:s') . "] previous run still in progress — skipped\n";
    exit(0);
}

try {
    $summary = $scheduler->runDue('cron');
    echo "[" . date('Y-m-d H:i:s') . "] master cron ran\n";
    foreach ($summary as $key => $status) {
        if ($status !== 'not-due' && $status !== 'disabled') echo "  - $key: $status\n";
    }
} catch (Throwable $e) {
    kt_error_log('master cron error: ' . $e->getMessage());
    echo "error: " . $e->getMessage() . "\n";
} finally {
    $scheduler->releaseMasterLock();
}
