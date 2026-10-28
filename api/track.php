<?php
/**
 * KRISHNA TOOLS — analytics beacon (see includes/analytics.php).
 * Body (JSON, sent with navigator.sendBeacon or fetch keepalive):
 *   { t: "view" | "ping", v: "<16-hex visitor id>", p: "/path", s: "tool-slug"?, r: "referrer"?, l: "gu"|"en" }
 * Always answers 204 so a tracking problem never shows up for the visitor.
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/analytics.php';

header('Cache-Control: no-store');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $in = json_decode((string) file_get_contents('php://input', false, null, 0, 4096), true);
    if (is_array($in) && in_array($in['t'] ?? '', ['view', 'ping'], true)) {
        try {
            kt_track($in['t'], (string) ($in['v'] ?? ''), (string) ($in['p'] ?? '/'),
                isset($in['s']) ? (string) $in['s'] : null, (string) ($in['r'] ?? ''), (string) ($in['l'] ?? ''));
        } catch (Throwable $e) { /* never break the page */ }
    }
}
http_response_code(204);
