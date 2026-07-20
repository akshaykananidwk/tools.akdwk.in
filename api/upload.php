<?php
/**
 * KRISHNA TOOLS — generic temp upload endpoint (MIME + size guarded).
 * Files land in /uploads/temp and are auto-purged after 2 hours.
 * Most tools work fully client-side; this is for the few that need a round-trip.
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/ratelimit.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['ok' => false], 405);
csrf_verify();
if (!rate_limit('upload', 30, 60)) json_out(['ok' => false, 'error' => 'Too many requests'], 429);
if (empty($_FILES['file']['tmp_name'])) json_out(['ok' => false, 'error' => 'No file']);

$f = $_FILES['file'];
$maxMb = (int) (user_plan()['max_file_mb'] ?? 5);
if ($f['size'] > $maxMb * 1024 * 1024) json_out(['ok' => false, 'error' => "ફાઇલ {$maxMb}MB થી નાની હોવી જોઈએ"]);

$allowedExt = ['png', 'jpg', 'jpeg', 'webp', 'gif', 'pdf', 'zip', 'csv', 'txt', 'json', 'xlsx', 'xls', 'docx', 'pptx', 'heic'];
$ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowedExt, true)) json_out(['ok' => false, 'error' => 'આ ફાઇલ પ્રકાર માન્ય નથી']);

$allowedMime = ['image/png', 'image/jpeg', 'image/webp', 'image/gif', 'image/heic', 'application/pdf',
    'application/zip', 'text/csv', 'text/plain', 'application/json',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'application/octet-stream'];
$mime = mime_content_type($f['tmp_name']) ?: 'application/octet-stream';
if (!in_array($mime, $allowedMime, true)) json_out(['ok' => false, 'error' => 'MIME માન્ય નથી']);

$dir = KT_UPLOADS . '/temp';
@mkdir($dir, 0755, true);
$name = rand_token(12) . '.' . preg_replace('/[^a-z0-9]/', '', $ext);
$path = $dir . '/' . $name;
if (!move_uploaded_file($f['tmp_name'], $path)) json_out(['ok' => false, 'error' => 'અપલોડ નિષ્ફળ']);

json_out(['ok' => true, 'url' => SITE_URL . '/uploads/temp/' . $name, 'name' => $name, 'expires' => 7200]);
