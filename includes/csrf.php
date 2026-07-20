<?php
/**
 * KRISHNA TOOLS — CSRF protection.
 * A per-session token is embedded in every form and verified on POST.
 */
require_once __DIR__ . '/functions.php';

function csrf_token(): string {
    kt_session_start();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = rand_token(40);
    }
    return $_SESSION['csrf'];
}

/** Hidden input for forms. */
function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

/** Verify a submitted token (POST body or X-CSRF header). Exits on failure. */
function csrf_verify(): void {
    kt_session_start();
    $sent = $_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF'] ?? '');
    if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], (string) $sent)) {
        json_out(['ok' => false, 'error' => t('csrf_failed')], 419);
    }
}

/** Honeypot check for public forms: a hidden field that must stay empty. */
function honeypot_ok(string $field = 'website'): bool {
    return empty($_POST[$field]);
}
