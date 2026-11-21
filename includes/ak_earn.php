<?php
/**
 * KRISHNA TOOLS — glue for the central AK Earn partner/commission system (https://earn.akdwk.in).
 *
 * Credentials are server-side only. Put them in /config/config.php (not in git, blocked by .htaccess):
 *   define('AK_MASTER_URL', 'https://earn.akdwk.in');
 *   define('AK_SITE_KEY', 'sk_...');
 *   define('AK_SITE_SECRET', '...');        // never in JS, logs or git
 * or set the same names as environment variables. Until both key and secret are set, every call
 * below is a silent no-op, so the site works exactly as before.
 *
 * All tracking is non-blocking: calls are queued and sent after the response has been delivered
 * to the visitor (fastcgi_finish_request), and any error is swallowed (logged without the secret).
 * The SDK itself retries failed sends from a local queue file.
 */

foreach (['AK_MASTER_URL', 'AK_SITE_KEY', 'AK_SITE_SECRET'] as $__k) {
    if (!defined($__k)) {
        $__v = getenv($__k);
        if (is_string($__v) && $__v !== '') define($__k, $__v);
    }
}
unset($__k, $__v);
if (!defined('AK_MASTER_URL')) define('AK_MASTER_URL', 'https://earn.akdwk.in');
// Retry queue holds customer names/mobiles: keep it in /logs (web access denied), not the shared /tmp.
if (!defined('AK_QUEUE_DIR') && defined('KT_LOGS') && is_dir(KT_LOGS) && is_writable(KT_LOGS)) define('AK_QUEUE_DIR', KT_LOGS);

require_once __DIR__ . '/vendor/ak-connect.php';

/** True once a real site key and secret are configured. */
function ak_enabled(): bool {
    $key = defined('AK_SITE_KEY') ? (string) AK_SITE_KEY : '';
    $sec = defined('AK_SITE_SECRET') ? (string) AK_SITE_SECRET : '';
    return $key !== '' && $sec !== '' && $key !== 'SITE_KEY_HERE' && $sec !== 'SITE_SECRET_HERE' && function_exists('curl_init');
}

/** Public site key for the browser script (the key is public; the secret never leaves the server). */
function ak_public_key(): string {
    return ak_enabled() ? (string) AK_SITE_KEY : '';
}

/** Customer mobile as the 10-digit Indian number AK Earn expects. */
function ak_mobile10(?string $phone): string {
    $d = preg_replace('/\D+/', '', (string) $phone);
    return strlen($d) >= 10 ? substr($d, -10) : '';
}

/** Stable product code for a plan row (ids never change; names may). */
function ak_plan_code($planId): string {
    return 'kt-plan-' . (int) $planId;
}

/**
 * Run an AK Earn call after the response is sent. Never throws, never delays the visitor.
 * Example: ak_later('ak_track_signup', $name, $mobile, $email);
 */
function ak_later(string $fn, ...$args): void {
    if (!ak_enabled() || !function_exists($fn)) return;
    static $queue = null;
    if ($queue === null) {
        $queue = new ArrayObject();
        $q = $queue;
        register_shutdown_function(static function () use ($q) {
            if (!count($q)) return;
            if (function_exists('fastcgi_finish_request')) @fastcgi_finish_request();
            foreach ($q as [$f, $a]) {
                try {
                    $r = $f(...$a);
                    if (is_array($r) && ($r['status'] ?? '') === 'error' && empty($r['queued'])) {
                        kt_ak_log($f . ' failed: ' . (string) ($r['message'] ?? 'unknown'));
                    }
                } catch (Throwable $e) {
                    kt_ak_log($f . ' exception: ' . $e->getMessage());
                }
            }
        });
    }
    $queue->append([$fn, $args]);
}

function kt_ak_log(string $msg): void {
    if (defined('AK_SITE_SECRET') && (string) AK_SITE_SECRET !== '') $msg = str_replace((string) AK_SITE_SECRET, '***', $msg);
    if (function_exists('kt_error_log')) kt_error_log('AK Earn: ' . $msg);
}

/** This site's plans in the AK Earn product format. */
function ak_site_products(): array {
    $out = [];
    try {
        foreach (all("SELECT * FROM " . tbl('plans') . " ORDER BY price ASC, id ASC") as $p) {
            $days = (int) $p['duration_days'];
            $billing = $days <= 0 ? 'one_time' : ($days <= 31 ? 'monthly' : ($days <= 100 ? 'quarterly' : 'yearly'));
            $out[] = [
                'code' => ak_plan_code($p['id']),
                'name' => 'Krishna Tools ' . ($p['name_en'] ?: $p['name_gu']),
                'price' => round((float) $p['price'], 2),
                'renewal_price' => round((float) $p['price'], 2),
                'billing' => $billing,
                'description' => mb_substr(trim(preg_replace('/\s+/', ' ', (string) ($p['features'] ?? ''))), 0, 500),
                'image' => (str_starts_with(SITE_URL, 'https://') ? SITE_URL : 'https://tools.akdwk.in') . '/assets/img/logo/og.png',
                'url' => SITE_URL . '/pricing.php',
                'status' => ((int) $p['is_active'] === 1 && (float) $p['price'] > 0) ? 'active' : 'inactive',
            ];
        }
    } catch (Throwable $e) {
        kt_ak_log('product list: ' . $e->getMessage());
    }
    return $out;
}

/** Push the plan list to AK Earn (after admin saves a plan, and from `php cron/ak_earn.php sync`). */
function ak_sync_site_products(bool $now = false): array {
    if (!ak_enabled()) return ['status' => 'error', 'message' => 'AK Earn is not configured'];
    $p = ak_site_products();
    if (!$p) return ['status' => 'error', 'message' => 'no plans found'];
    if (!$now) { ak_later('ak_sync_products', $p); return ['status' => 'ok', 'deferred' => true]; }
    try { return ak_sync_products($p); } catch (Throwable $e) { kt_ak_log('sync: ' . $e->getMessage()); return ['status' => 'error', 'message' => $e->getMessage()]; }
}

/**
 * Track a confirmed payment row (status just became 'paid'). Decides between a first order and a
 * renewal: a renewal is a paid order by a user who already had an earlier paid payment.
 */
function ak_track_paid_payment(array $pay, ?array $sub, ?array $user, float $amountPaid): void {
    if (!ak_enabled()) return;
    try {
        $orderId = (string) ($pay['razorpay_order_id'] ?? '');
        if ($orderId === '' || $amountPaid <= 0) return;
        $code = $sub ? ak_plan_code($sub['plan_id']) : null;
        $name = (string) ($user['name'] ?? '');
        $mobile = ak_mobile10($user['phone'] ?? '');
        $earlier = (int) scalar("SELECT COUNT(*) FROM " . tbl('payments') . " WHERE user_id = :u AND id <> :id AND status IN ('paid','refunded')",
            [':u' => (int) $pay['user_id'], ':id' => (int) $pay['id']]);
        if ($earlier > 0) ak_later('ak_track_renewal', $orderId, $amountPaid, $code, $name, $mobile);
        else ak_later('ak_track_order', $orderId, $amountPaid, $code, $name, $mobile);
    } catch (Throwable $e) {
        kt_ak_log('paid payment: ' . $e->getMessage());
    }
}
