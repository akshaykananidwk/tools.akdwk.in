<?php
/**
 * KRISHNA TOOLS — fallback endpoints for the Internet Speed Test.
 * The test normally runs against Cloudflare's public speed servers; these
 * small endpoints are only used if those cannot be reached.
 *   ?a=ping            — tiny uncached response (latency)
 *   ?a=down&bytes=N    — N bytes of filler (max 10 MB)
 *   ?a=up  (POST)      — reads and discards the body (max 10 MB)
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/ratelimit.php';

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Access-Control-Allow-Origin: ' . SITE_URL);
$a = $_GET['a'] ?? 'ping';
const KT_SPEED_MAX = 10 * 1024 * 1024;

if ($a === 'ping') {
    header('Content-Type: text/plain');
    echo 'ok';
    exit;
}

if (!rate_limit('speedtest_' . $a, 60, 300)) { http_response_code(429); exit; }

if ($a === 'down') {
    $bytes = max(1, min(KT_SPEED_MAX, (int) ($_GET['bytes'] ?? 1048576)));
    @ini_set('zlib.output_compression', '0');
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: application/octet-stream');
    header('Content-Encoding: identity');
    header('Content-Length: ' . $bytes);
    $chunk = random_bytes(65536); // incompressible, so proxies cannot shrink it
    for ($sent = 0; $sent < $bytes; $sent += strlen($chunk)) {
        echo $sent + 65536 <= $bytes ? $chunk : substr($chunk, 0, $bytes - $sent);
        flush();
    }
    exit;
}

if ($a === 'up' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $in = fopen('php://input', 'rb');
    $n = 0;
    while (!feof($in) && $n < KT_SPEED_MAX) $n += strlen(fread($in, 65536));
    fclose($in);
    json_out(['ok' => true, 'bytes' => $n]);
}

http_response_code(400);
