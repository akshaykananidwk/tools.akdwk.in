<?php
/**
 * KRISHNA TOOLS — front-controller bootstrap.
 * Redirects to the installer until /config/config.php exists, then loads the
 * app. Include this at the very top of every public page.
 */
if (!is_file(dirname(__DIR__) . '/config/config.php')) {
    header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/') . '/install/');
    // Fallback absolute path if the above is ambiguous.
    if (!headers_sent()) header('Location: /install/');
    exit;
}
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

// Maintenance mode (admins bypass).
if (maintenance_active() && !is_admin() && !str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin')) {
    http_response_code(503);
    $lang = current_lang();
    echo '<!DOCTYPE html><meta charset="utf-8"><title>Maintenance</title>'
        . '<div style="font-family:sans-serif;text-align:center;padding:4rem">'
        . '<div style="font-size:3rem">🦚</div><h1>' . e(t('maintenance')) . '</h1></div>';
    exit;
}
