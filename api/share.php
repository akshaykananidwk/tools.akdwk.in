<?php
/**
 * KRISHNA TOOLS — "Share on WhatsApp" endpoint.
 * Text → WhatsApp::send. File → saved to /uploads/share/<token>/ and sent via
 * sendMedia with a public URL (auto-purged after 2 hours). Login required for
 * API sends; guests fall back to wa.me client-side.
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/ratelimit.php';
require_once __DIR__ . '/../includes/whatsapp.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['ok' => false], 405);
csrf_verify();

if (!is_logged_in()) json_out(['ok' => false, 'error' => t('login_required')], 401);
if (!rate_limit('wa_share', 10, 60)) json_out(['ok' => false, 'error' => 'Too many requests'], 429);

$user = current_user();
$number = normalize_phone($_POST['number'] ?? (json_decode(file_get_contents('php://input'), true)['number'] ?? ''));
if (strlen($number) < 12) json_out(['ok' => false, 'error' => 'માન્ય નંબર આપો (91XXXXXXXXXX)']);

// File share (multipart) vs text share (JSON body).
if (!empty($_FILES['file']['tmp_name'])) {
    $f = $_FILES['file'];
    if ($f['size'] > 25 * 1024 * 1024) json_out(['ok' => false, 'error' => 'ફાઇલ ખૂબ મોટી (25MB max)']);

    // MIME whitelist.
    $allowed = ['image/png', 'image/jpeg', 'image/webp', 'application/pdf', 'application/zip', 'text/plain', 'text/csv', 'application/json'];
    $mime = mime_content_type($f['tmp_name']) ?: '';
    if (!in_array($mime, $allowed, true)) json_out(['ok' => false, 'error' => 'આ ફાઇલ પ્રકાર માન્ય નથી']);

    $token = rand_token(16);
    $dir = KT_UPLOADS . '/share/' . $token;
    @mkdir($dir, 0755, true);
    $name = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($f['name'])) ?: 'file';
    $path = $dir . '/' . $name;
    if (!move_uploaded_file($f['tmp_name'], $path)) json_out(['ok' => false, 'error' => 'અપલોડ નિષ્ફળ']);

    $publicUrl = SITE_URL . '/uploads/share/' . $token . '/' . rawurlencode($name);
    $msg = $_POST['message'] ?? '';
    $res = wa()->sendMedia($number, $msg ?: ('🦚 ' . setting('site_name', 'કૃષ્ણા ટૂલ્સ')), $publicUrl, (int) $user['id']);

    // Record for the 2-hour purge (cron + on-load sweep).
    try {
        insert('saved_files', ['user_id' => (int) $user['id'], 'tool_id' => null, 'filename' => $name,
            'path' => 'uploads/share/' . $token . '/' . $name, 'size' => filesize($path),
            'expires_at' => date('Y-m-d H:i:s', time() + 7200)]);
    } catch (Throwable $e) {}

    json_out(['ok' => $res['ok'], 'error' => $res['ok'] ? '' : 'WhatsApp મોકલવામાં નિષ્ફળ']);
}

// Text share.
$body = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$message = trim($body['message'] ?? '');
if ($message === '') json_out(['ok' => false, 'error' => 'સંદેશ ખાલી છે']);
$res = wa()->send($number, $message, (int) $user['id']);
json_out(['ok' => $res['ok'], 'error' => $res['ok'] ? '' : 'WhatsApp મોકલવામાં નિષ્ફળ']);
