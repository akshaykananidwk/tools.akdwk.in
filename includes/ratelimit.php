<?php
/**
 * KRISHNA TOOLS — per-IP rate limiting and guest daily tool limits.
 */
require_once __DIR__ . '/functions.php';

/**
 * Generic sliding-window limiter using the guest_limits table as a counter
 * store keyed by "action:ip". Returns true if the request is allowed.
 */
function rate_limit(string $action, int $max, int $windowSeconds = 60): bool {
    kt_session_start();
    $key = $action . ':' . client_ip();
    $now = time();
    $bucket = $_SESSION['rl'][$key] ?? ['count' => 0, 'reset' => $now + $windowSeconds];
    if ($now > $bucket['reset']) {
        $bucket = ['count' => 0, 'reset' => $now + $windowSeconds];
    }
    $bucket['count']++;
    $_SESSION['rl'][$key] = $bucket;
    return $bucket['count'] <= $max;
}

/**
 * Guest daily usage for a specific tool (free tier = 3/day/IP).
 * Returns ['allowed'=>bool, 'used'=>int, 'limit'=>int].
 */
function guest_tool_usage(int $toolId, int $limit = 3): array {
    $ip  = client_ip();
    $day = date('Y-m-d');
    $row = one("SELECT id, uses FROM " . tbl('guest_limits') . "
                WHERE ip = :ip AND tool_id = :t AND day = :d",
               [':ip' => $ip, ':t' => $toolId, ':d' => $day]);
    $used = $row ? (int) $row['uses'] : 0;
    return ['allowed' => $used < $limit, 'used' => $used, 'limit' => $limit];
}

/** Increment guest tool usage for today. */
function guest_tool_increment(int $toolId): void {
    $ip  = client_ip();
    $day = date('Y-m-d');
    $row = one("SELECT id FROM " . tbl('guest_limits') . "
                WHERE ip = :ip AND tool_id = :t AND day = :d",
               [':ip' => $ip, ':t' => $toolId, ':d' => $day]);
    if ($row) {
        q("UPDATE " . tbl('guest_limits') . " SET uses = uses + 1 WHERE id = :id", [':id' => $row['id']]);
    } else {
        insert('guest_limits', ['ip' => $ip, 'tool_id' => $toolId, 'uses' => 1, 'day' => $day]);
    }
}
