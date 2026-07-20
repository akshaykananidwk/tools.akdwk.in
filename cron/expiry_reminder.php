<?php
/**
 * KRISHNA TOOLS — plan expiry reminder cron (CLI only).
 *
 * Sends 'expiry_reminder' WhatsApp messages to users whose plan expires in
 * exactly 7, 3 or 1 days, and 'plan_expired' + downgrade for those already
 * expired. Duplicate sends within the same day are prevented via activity_logs.
 *
 * Usage:  php cron/expiry_reminder.php
 */

if (PHP_SAPI !== 'cli') { http_response_code(403); exit("CLI only\n"); }

$config = __DIR__ . '/../config/config.php';
if (!is_file($config)) { fwrite(STDERR, "config missing — not installed\n"); exit(1); }
require_once $config;
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/whatsapp.php';

/** True if a reminder with this action was already logged for the user today. */
function kt_already_sent(int $uid, string $action): bool {
    try {
        $row = one("SELECT id FROM " . tbl('activity_logs') . "
                    WHERE user_id = :u AND action = :a AND DATE(created_at) = CURDATE() LIMIT 1",
                   [':u' => $uid, ':a' => $action]);
        return $row !== null;
    } catch (Throwable $e) { return false; }
}

$link = rtrim(SITE_URL, '/') . '/pricing.php';
$free = plan_free();
$freeId = (int) ($free['id'] ?? 0);
$stats = ['reminders' => 0, 'expired' => 0, 'skipped' => 0];

// 1) Upcoming expiries at 7, 3, 1 days out.
foreach ([7, 3, 1] as $d) {
    try {
        $users = all("SELECT * FROM " . tbl('users') . "
                      WHERE plan_expiry IS NOT NULL
                        AND DATE(plan_expiry) = DATE(DATE_ADD(NOW(), INTERVAL :d DAY))
                        AND status = 'active'", [':d' => $d]);
    } catch (Throwable $e) { fwrite(STDERR, "query error: " . $e->getMessage() . "\n"); continue; }

    foreach ($users as $u) {
        $action = 'expiry_reminder_' . $d;
        if (empty($u['phone']) || kt_already_sent((int) $u['id'], $action)) { $stats['skipped']++; continue; }
        try {
            $res = wa()->sendTemplate($u['phone'], 'expiry_reminder', ['days' => $d, 'link' => $link], (int) $u['id']);
            if (!empty($res['ok'])) { activity_log((int) $u['id'], $action, "reminder {$d}d"); $stats['reminders']++; }
        } catch (Throwable $e) { fwrite(STDERR, "send error uid {$u['id']}: " . $e->getMessage() . "\n"); }
    }
}

// 2) Already-expired paid users — notify once, then downgrade to Free.
try {
    $expired = all("SELECT * FROM " . tbl('users') . "
                    WHERE plan_expiry IS NOT NULL AND plan_expiry < NOW()
                      AND plan_id IS NOT NULL AND plan_id <> :free
                      AND status = 'active'", [':free' => $freeId]);
} catch (Throwable $e) { $expired = []; fwrite(STDERR, "expired query error: " . $e->getMessage() . "\n"); }

foreach ($expired as $u) {
    if (!empty($u['phone']) && !kt_already_sent((int) $u['id'], 'plan_expired')) {
        try {
            $res = wa()->sendTemplate($u['phone'], 'plan_expired', ['link' => $link], (int) $u['id']);
            if (!empty($res['ok'])) activity_log((int) $u['id'], 'plan_expired', 'notified');
        } catch (Throwable $e) { fwrite(STDERR, "expired send error uid {$u['id']}: " . $e->getMessage() . "\n"); }
    }
    // Downgrade to Free so access control reflects the lapse.
    try {
        update('users', ['plan_id' => ($freeId ?: null), 'plan_expiry' => null], ['id' => $u['id']]);
        $stats['expired']++;
    } catch (Throwable $e) { fwrite(STDERR, "downgrade error uid {$u['id']}: " . $e->getMessage() . "\n"); }
}

printf("[%s] expiry_reminder done — reminders:%d expired:%d skipped:%d\n",
    date('Y-m-d H:i:s'), $stats['reminders'], $stats['expired'], $stats['skipped']);
