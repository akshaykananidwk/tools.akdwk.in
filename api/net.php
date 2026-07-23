<?php
/**
 * KRISHNA TOOLS — network utilities backend.
 *   action=ip                      — caller's public IP + request headers.
 *   action=dns&domain=&type=       — DNS records (A/AAAA/MX/TXT/NS/CNAME/SOA).
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/ratelimit.php';
header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? 'ip';

if ($action === 'ip') {
    $headers = [];
    foreach ($_SERVER as $k => $v) {
        if (str_starts_with($k, 'HTTP_')) {
            $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($k, 5)))));
            $headers[$name] = $v;
        }
    }
    json_out(['ok' => true, 'ip' => client_ip(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'headers' => $headers]);
}

if ($action === 'dns') {
    if (!rate_limit('dns', 30, 60)) json_out(['ok' => false, 'error' => 'Too many lookups. Slow down.'], 429);
    $domain = strtolower(trim($_GET['domain'] ?? ''));
    // Strip scheme/path if a URL was pasted.
    $domain = preg_replace('#^https?://#', '', $domain);
    $domain = explode('/', $domain)[0];
    if (!preg_match('/^([a-z0-9](-?[a-z0-9])*\.)+[a-z]{2,}$/i', $domain)) {
        json_out(['ok' => false, 'error' => 'Enter a valid domain (e.g. example.com).']);
    }
    $typeMap = ['A' => DNS_A, 'AAAA' => DNS_AAAA, 'MX' => DNS_MX, 'TXT' => DNS_TXT,
                'NS' => DNS_NS, 'CNAME' => DNS_CNAME, 'SOA' => DNS_SOA, 'ANY' => DNS_ALL ?? DNS_ANY];
    $type = strtoupper($_GET['type'] ?? 'A');
    if (!isset($typeMap[$type])) $type = 'A';

    $records = @dns_get_record($domain, $typeMap[$type]);
    if ($records === false) json_out(['ok' => false, 'error' => 'Lookup failed for that domain.']);
    // Trim to the useful fields.
    $out = array_map(function ($r) {
        return array_intersect_key($r, array_flip(['host', 'type', 'ip', 'ipv6', 'target', 'ttl',
            'pri', 'txt', 'mname', 'rname', 'ns', 'serial', 'refresh', 'retry', 'expire', 'minimum-ttl']));
    }, $records);
    json_out(['ok' => true, 'domain' => $domain, 'type' => $type, 'records' => $out]);
}

json_out(['ok' => false, 'error' => 'Unknown action'], 400);
