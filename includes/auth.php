<?php
/**
 * KRISHNA TOOLS — authentication, plans & access control.
 */
require_once __DIR__ . '/functions.php';

/* ── Current user ─────────────────────────────────────────── */
function current_user(): ?array {
    static $cached = false;
    static $user = null;
    if ($cached) return $user;
    $cached = true;
    kt_session_start();
    if (empty($_SESSION['uid'])) return $user = null;
    $user = one("SELECT * FROM " . tbl('users') . " WHERE id = :id AND status = 'active'",
                [':id' => $_SESSION['uid']]);
    return $user;
}

function is_logged_in(): bool { return current_user() !== null; }

function is_admin(): bool {
    $u = current_user();
    return $u && $u['role'] === 'admin';
}

/* ── Login / logout ───────────────────────────────────────── */
function login_user(array $user): void {
    kt_session_start();
    session_regenerate_id(true);          // prevent fixation
    $_SESSION['uid'] = (int) $user['id'];
    update('users', [
        'last_login' => date('Y-m-d H:i:s'),
        'last_ip'    => client_ip(),
    ], ['id' => $user['id']]);
    activity_log((int) $user['id'], 'login', 'IP ' . client_ip());
}

function logout_user(): void {
    kt_session_start();
    $uid = $_SESSION['uid'] ?? null;
    $_SESSION = [];
    session_destroy();
    if ($uid) activity_log((int) $uid, 'logout');
}

function attempt_login(string $email, string $password): ?array {
    $user = one("SELECT * FROM " . tbl('users') . " WHERE email = :e", [':e' => strtolower(trim($email))]);
    if ($user && password_verify($password, $user['password_hash'])) {
        if ($user['status'] !== 'active') return null;
        login_user($user);
        return $user;
    }
    return null;
}

/* ── Plans ────────────────────────────────────────────────── */
function user_plan(?array $user = null): array {
    $user = $user ?? current_user();
    if (!$user || empty($user['plan_id'])) {
        return plan_free();
    }
    $active = $user['plan_expiry'] && strtotime($user['plan_expiry']) > time();
    if (!$active) return plan_free();
    $plan = one("SELECT * FROM " . tbl('plans') . " WHERE id = :id", [':id' => $user['plan_id']]);
    return $plan ?: plan_free();
}

function plan_free(): array {
    $p = one("SELECT * FROM " . tbl('plans') . " WHERE price = 0 ORDER BY id LIMIT 1");
    return $p ?: [
        'id' => 0, 'name_en' => 'Free', 'name_gu' => 'ફ્રી', 'price' => 0,
        'duration_days' => 0, 'daily_limit' => 3, 'max_file_mb' => 5, 'features' => '',
    ];
}

function days_left(?array $user = null): int {
    $user = $user ?? current_user();
    if (!$user || empty($user['plan_expiry'])) return 0;
    $diff = strtotime($user['plan_expiry']) - time();
    return $diff > 0 ? (int) ceil($diff / 86400) : 0;
}

/* ── Access control middleware ────────────────────────────── */
/**
 * Decide whether the current visitor may USE a tool (i.e. run a conversion).
 * Guests get a free-tier daily quota; paid users bypass it. Premium tools
 * require an active paid plan.
 *
 * Returns ['allowed'=>bool, 'reason'=>string, 'upgrade'=>bool,
 *          'watermark'=>bool, 'max_mb'=>int].
 */
function tool_access(array $tool): array {
    $user = current_user();
    $plan = user_plan($user);
    $isPaid = ((float) ($plan['price'] ?? 0)) > 0;

    // Premium tool gate.
    if (!empty($tool['is_premium']) && !$isPaid) {
        return ['allowed' => false, 'reason' => t('premium_only'), 'upgrade' => true,
                'watermark' => true, 'max_mb' => 5];
    }

    if ($isPaid) {
        return ['allowed' => true, 'reason' => '', 'upgrade' => false,
                'watermark' => false, 'max_mb' => (int) ($plan['max_file_mb'] ?? 100)];
    }

    // Free / guest: enforce daily limit per tool.
    $toolId = (int) ($tool['db_id'] ?? 0);
    if ($toolId > 0) {
        $u = guest_tool_usage($toolId, (int) ($plan['daily_limit'] ?? 3));
        if (!$u['allowed']) {
            return ['allowed' => false,
                    'reason' => t('daily_limit_reached', ['n' => $u['limit']]),
                    'upgrade' => true, 'watermark' => true, 'max_mb' => 5];
        }
    }
    return ['allowed' => true, 'reason' => '', 'upgrade' => false,
            'watermark' => true, 'max_mb' => (int) ($plan['max_file_mb'] ?? 5)];
}

/** Require login for a page (redirect to login). */
function require_login(): void {
    if (!is_logged_in()) {
        redirect(SITE_URL . '/login.php?next=' . urlencode($_SERVER['REQUEST_URI'] ?? '/'));
    }
}

/** Require admin for /admin pages. */
function require_admin(): void {
    if (!is_admin()) {
        redirect(SITE_URL . '/admin/login.php');
    }
}
