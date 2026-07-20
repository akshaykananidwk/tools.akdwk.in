<?php
/** KRISHNA TOOLS — record a tool usage event + enforce guest daily limit. */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/ratelimit.php';
require_once __DIR__ . '/../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['ok' => false], 405);
csrf_verify();

$body = json_decode(file_get_contents('php://input'), true) ?: [];
$toolId = (int) ($body['tool_id'] ?? 0);
if ($toolId <= 0) json_out(['ok' => false, 'error' => 'bad tool'], 400);

if (!rate_limit('tool_log', 60, 60)) json_out(['ok' => false, 'error' => 'rate'], 429);

$user = current_user();
$plan = user_plan($user);
$isPaid = ((float) ($plan['price'] ?? 0)) > 0;

// Guests / free users: count against the daily per-tool quota.
if (!$isPaid) {
    $u = guest_tool_usage($toolId, (int) ($plan['daily_limit'] ?? 3));
    if (!$u['allowed']) json_out(['ok' => false, 'limit' => true, 'error' => t('daily_limit_reached', ['n' => $u['limit']])], 200);
    guest_tool_increment($toolId);
}

try {
    insert('tool_usage', [
        'user_id'    => $user['id'] ?? null,
        'tool_id'    => $toolId,
        'ip'         => client_ip(),
        'user_agent' => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250),
        'created_at' => date('Y-m-d H:i:s'),
    ]);
    q("UPDATE " . tbl('tools') . " SET views = views + 1 WHERE id = :id", [':id' => $toolId]);
} catch (Throwable $e) { /* non-fatal */ }

json_out(['ok' => true]);
