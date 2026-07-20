<?php
/**
 * KRISHNA TOOLS — AMC / warranty reminder cron (CLI only).
 *
 * NOTE: This project has NO dedicated AMC table. AMC/warranty records are a
 * light, shop-side concept generated from the CCTV AMC tool usage. Until a
 * structured store exists, this script optionally reads an admin-maintained
 * JSON list from the settings key `amc_records` and sends the 'amc_renewal'
 * WhatsApp template for entries due soon. With no records it simply prints
 * "no AMC records" and exits cleanly — it never invents a table or errors.
 *
 * Expected settings('amc_records') shape (JSON array):
 *   [{"client":"Name","phone":"9978123146","renew_on":"2026-08-01"}, ...]
 *
 * Usage:  php cron/amc_reminder.php
 */

if (PHP_SAPI !== 'cli') { http_response_code(403); exit("CLI only\n"); }

$config = __DIR__ . '/../config/config.php';
if (!is_file($config)) { fwrite(STDERR, "config missing — not installed\n"); exit(1); }
require_once $config;
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/whatsapp.php';

// Load the optional settings-driven AMC list (no dedicated table exists).
$raw = setting('amc_records', '');
$records = [];
if (is_string($raw) && trim($raw) !== '') {
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) $records = $decoded;
}

if (empty($records)) {
    echo "[" . date('Y-m-d H:i:s') . "] no AMC records\n";
    exit(0);
}

// Remind for AMCs due within the next 7 days.
$sent = 0; $checked = 0;
$today = new DateTimeImmutable('today');

foreach ($records as $rec) {
    $checked++;
    $client = trim((string) ($rec['client'] ?? ''));
    $phone  = trim((string) ($rec['phone'] ?? ''));
    $renew  = trim((string) ($rec['renew_on'] ?? ''));
    if ($client === '' || $phone === '' || $renew === '') continue;

    try { $due = new DateTimeImmutable($renew); } catch (Throwable $e) { continue; }
    $days = (int) $today->diff($due)->format('%r%a');   // signed days until renewal
    if ($days < 0 || $days > 7) continue;               // only the 0-7 day window

    try {
        $res = wa()->sendTemplate($phone, 'amc_renewal', ['days' => $days, 'client' => $client]);
        if (!empty($res['ok'])) $sent++;
    } catch (Throwable $e) {
        fwrite(STDERR, "AMC send error for {$client}: " . $e->getMessage() . "\n");
    }
}

printf("[%s] amc_reminder done — checked:%d sent:%d\n", date('Y-m-d H:i:s'), $checked, $sent);
