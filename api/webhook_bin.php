<?php
/**
 * KRISHNA TOOLS — Webhook Tester backend.
 *   ?catch=<token>        (ANY method, no CSRF) — records an incoming webhook.
 *   action=create         (POST + CSRF)        — mints a new bin token/URL.
 *   action=list&token=..  (GET)                — returns recorded requests.
 *
 * Bins auto-expire after 24 hours (cleaned by cron/on-demand). This is a
 * public catch endpoint by design, so it is rate-limited and size-capped.
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/ratelimit.php';

$catch = $_GET['catch'] ?? '';
$action = $_POST['action'] ?? ($_GET['action'] ?? '');

/* ── 1) Catch an incoming webhook (external callers) ── */
if ($catch !== '') {
    $token = preg_replace('/[^a-z0-9]/', '', $catch);
    $bin = one("SELECT * FROM " . tbl('webhook_bins') . " WHERE token = :t AND expires_at > NOW()", [':t' => $token]);
    if (!$bin) { http_response_code(404); echo 'Bin not found or expired'; exit; }
    if (!rate_limit('wh_catch_' . $token, 120, 60)) { http_response_code(429); echo 'rate'; exit; }

    // Collect request headers.
    $headers = [];
    foreach ($_SERVER as $k => $v) {
        if (str_starts_with($k, 'HTTP_')) {
            $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($k, 5)))));
            $headers[$name] = $v;
        }
    }
    if (!empty($_SERVER['CONTENT_TYPE'])) $headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];

    $rawBody = file_get_contents('php://input');
    if (strlen($rawBody) > 256 * 1024) $rawBody = substr($rawBody, 0, 256 * 1024) . "\n… (truncated)";

    try {
        insert('webhook_requests', [
            'bin_token'    => $token,
            'method'       => substr($_SERVER['REQUEST_METHOD'] ?? 'GET', 0, 10),
            'headers'      => json_encode($headers, JSON_UNESCAPED_UNICODE),
            'query_string' => mb_substr($_SERVER['QUERY_STRING'] ?? '', 0, 2000),
            'body'         => $rawBody,
            'ip'           => client_ip(),
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
        // Keep only the latest 100 requests per bin.
        q("DELETE FROM " . tbl('webhook_requests') . " WHERE bin_token = :t AND id NOT IN
           (SELECT id FROM (SELECT id FROM " . tbl('webhook_requests') . " WHERE bin_token = :t2 ORDER BY id DESC LIMIT 100) x)",
           [':t' => $token, ':t2' => $token]);
    } catch (Throwable $e) { /* best-effort */ }

    header('Content-Type: application/json');
    echo json_encode(['ok' => true, 'message' => 'Webhook received by Krishna Tools']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

/* ── 2) Create a new bin ── */
if ($action === 'create') {
    require_once __DIR__ . '/../includes/csrf.php';
    csrf_verify();
    if (!rate_limit('wh_create', 10, 300)) json_out(['ok' => false, 'error' => 'Too many bins created. Try again later.'], 429);
    $token = strtolower(rand_token(10));
    insert('webhook_bins', ['token' => $token, 'ip' => client_ip(),
        'created_at' => date('Y-m-d H:i:s'), 'expires_at' => date('Y-m-d H:i:s', time() + 86400)]);
    json_out(['ok' => true, 'token' => $token,
        'url' => SITE_URL . '/api/webhook_bin.php?catch=' . $token,
        'expires_hours' => 24]);
}

/* ── 3) List recorded requests for a bin ── */
if ($action === 'list') {
    $token = preg_replace('/[^a-z0-9]/', '', $_GET['token'] ?? '');
    if ($token === '') json_out(['ok' => false, 'error' => 'token required'], 400);
    $sinceId = (int) ($_GET['since'] ?? 0);
    $rows = all("SELECT id, method, headers, query_string, body, ip, created_at
                 FROM " . tbl('webhook_requests') . "
                 WHERE bin_token = :t AND id > :since ORDER BY id DESC LIMIT 100",
                 [':t' => $token, ':since' => $sinceId]);
    foreach ($rows as &$r) { $r['headers'] = json_decode($r['headers'], true) ?: []; }
    json_out(['ok' => true, 'requests' => $rows]);
}

json_out(['ok' => false, 'error' => 'Unknown action'], 400);
