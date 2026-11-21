<?php
/**
 * KRISHNA TOOLS — AK Earn command-line helper (CLI only).
 *
 *   php cron/ak_earn.php ping            → checks keys + connection (must print "status": "ok")
 *   php cron/ak_earn.php sync            → sends the plan list to AK Earn (also runs after Admin → Plans save)
 *   php cron/ak_earn.php products        → shows the plan list that would be sent (nothing is sent)
 *   php cron/ak_earn.php flush           → retries queued events now
 *   php cron/ak_earn.php test ORDER_ID   → staging check: sends order_created, payment_success and refund
 *                                          for a ₹1 test order with that id (use a TEST- id; never on live data)
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$config = __DIR__ . '/../config/config.php';
if (!is_file($config)) { fwrite(STDERR, "config missing — not installed\n"); exit(1); }
require_once $config;
require_once __DIR__ . '/../includes/functions.php';

$cmd = $argv[1] ?? 'ping';
$print = fn($r) => print(json_encode($r, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n");
if ($cmd !== 'products' && !ak_enabled()) { fwrite(STDERR, "AK_SITE_KEY / AK_SITE_SECRET are not set in config/config.php\n"); exit(1); }

switch ($cmd) {
    case 'ping': $print(ak_ping()); break;
    case 'products': $print(ak_site_products()); break;
    case 'sync': $print(ak_sync_site_products(true)); break;
    case 'flush': echo ak_flush_queue(200) . " queued event(s) sent\n"; break;
    case 'test':
        $id = $argv[2] ?? ('TEST-' . date('YmdHis'));
        if (!str_starts_with($id, 'TEST-')) { fwrite(STDERR, "Use an order id starting with TEST-\n"); exit(1); }
        $print(['order_created' => ak_track_order($id, 1, 'kt-plan-test', 'Test Customer', '9999999999', 'created', ['test' => true])]);
        $print(['payment_success' => ak_track_order($id, 1, 'kt-plan-test', 'Test Customer', '9999999999', 'paid', ['test' => true])]);
        $print(['refund' => ak_track_refund($id, 'staging test')]);
        break;
    default: fwrite(STDERR, "Unknown command. Use: ping | products | sync | flush | test TEST-ID\n"); exit(1);
}
