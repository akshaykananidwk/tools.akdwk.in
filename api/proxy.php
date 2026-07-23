<?php
/**
 * KRISHNA TOOLS — HTTP proxy for the API/REST tester (bypasses browser CORS).
 *
 * Security: SSRF-guarded — only http/https, public IPs only (blocks localhost,
 * private ranges, link-local, cloud metadata), method + size + timeout caps,
 * per-IP rate limiting. Never follows redirects into blocked hosts.
 *
 * Request (JSON):  { "method":"GET", "url":"https://...", "headers":{...}, "body":"..." }
 * Response (JSON): { ok, status, statusText, headers, body, time_ms, size }
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/ratelimit.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['ok' => false, 'error' => 'POST only'], 405);
csrf_verify();
if (!rate_limit('proxy', 30, 60)) json_out(['ok' => false, 'error' => 'Too many requests — slow down.'], 429);

$in = json_decode(file_get_contents('php://input'), true) ?: [];
$method = strtoupper(preg_replace('/[^A-Z]/i', '', $in['method'] ?? 'GET'));
$url = trim($in['url'] ?? '');
$headers = is_array($in['headers'] ?? null) ? $in['headers'] : [];
$body = $in['body'] ?? '';

$allowedMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'];
if (!in_array($method, $allowedMethods, true)) json_out(['ok' => false, 'error' => 'Method not allowed']);

// ── URL + SSRF validation ──
$parts = parse_url($url);
if (!$parts || empty($parts['scheme']) || empty($parts['host'])) json_out(['ok' => false, 'error' => 'Enter a valid URL (including https://).']);
if (!in_array(strtolower($parts['scheme']), ['http', 'https'], true)) json_out(['ok' => false, 'error' => 'Only http and https are allowed.']);

$host = $parts['host'];
$port = $parts['port'] ?? (strtolower($parts['scheme']) === 'https' ? 443 : 80);
if (in_array((int) $port, [22, 25, 3306, 6379, 5432, 11211, 27017], true)) json_out(['ok' => false, 'error' => 'That port is blocked.']);

/** Reject private / loopback / link-local / reserved IPs (SSRF guard). */
function proxy_ip_is_public(string $ip): bool {
    return (bool) filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
}
// Resolve every A/AAAA record and ensure all are public (defeats DNS rebinding to private IPs).
$ips = [];
foreach (['A' => DNS_A, 'AAAA' => DNS_AAAA] as $rt => $flag) {
    $recs = @dns_get_record($host, $flag) ?: [];
    foreach ($recs as $r) { if (!empty($r['ip'])) $ips[] = $r['ip']; if (!empty($r['ipv6'])) $ips[] = $r['ipv6']; }
}
if (filter_var($host, FILTER_VALIDATE_IP)) $ips[] = $host;
$ips = array_unique($ips);
if (!$ips) json_out(['ok' => false, 'error' => 'Could not resolve that host.']);
foreach ($ips as $ip) {
    if (!proxy_ip_is_public($ip)) json_out(['ok' => false, 'error' => 'Requests to private/local addresses are blocked.']);
}

// ── Perform the request ──
$curlHeaders = [];
foreach ($headers as $k => $v) {
    $k = trim((string) $k); $v = trim((string) $v);
    if ($k === '' || stripos($k, 'host') === 0) continue; // don't let callers override Host
    $curlHeaders[] = "$k: $v";
}

$maxBytes = 3 * 1024 * 1024; // 3 MB response cap
$respBody = '';
$overflow = false;

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_CUSTOMREQUEST  => $method,
    CURLOPT_HTTPHEADER     => $curlHeaders,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER         => false,
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_FOLLOWLOCATION => false,        // never auto-follow (SSRF safety)
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_PROTOCOLS      => defined('CURLPROTO_HTTP') ? (CURLPROTO_HTTP | CURLPROTO_HTTPS) : 0,
    CURLOPT_USERAGENT      => 'KrishnaTools-APITester/1.0',
]);
if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true) && $body !== '') {
    curl_setopt($ch, CURLOPT_POSTFIELDS, (string) $body);
}
// Capture response headers.
$respHeaders = [];
curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($ch, $line) use (&$respHeaders) {
    $len = strlen($line);
    if (str_contains($line, ':')) { [$k, $v] = explode(':', $line, 2); $respHeaders[trim($k)] = trim($v); }
    return $len;
});
// Enforce the response size cap while streaming.
curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $chunk) use (&$respBody, &$overflow, $maxBytes) {
    $respBody .= $chunk;
    if (strlen($respBody) > $maxBytes) { $overflow = true; return -1; }
    return strlen($chunk);
});

$t0 = microtime(true);
$ok = curl_exec($ch);
$timeMs = round((microtime(true) - $t0) * 1000);
$status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
curl_close($ch);

if ($ok === false && !$overflow && $status === 0) {
    json_out(['ok' => false, 'error' => 'Request failed: ' . ($err ?: 'unknown error')]);
}

$statusText = [200 => 'OK', 201 => 'Created', 204 => 'No Content', 301 => 'Moved Permanently',
    302 => 'Found', 304 => 'Not Modified', 400 => 'Bad Request', 401 => 'Unauthorized',
    403 => 'Forbidden', 404 => 'Not Found', 405 => 'Method Not Allowed', 429 => 'Too Many Requests',
    500 => 'Internal Server Error', 502 => 'Bad Gateway', 503 => 'Service Unavailable'][$status] ?? '';

json_out([
    'ok' => true,
    'status' => $status,
    'statusText' => $statusText,
    'headers' => $respHeaders,
    'body' => $overflow ? (substr($respBody, 0, $maxBytes) . "\n\n… (truncated at 3 MB)") : $respBody,
    'time_ms' => $timeMs,
    'size' => strlen($respBody),
]);
