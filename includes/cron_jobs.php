<?php
/**
 * KRISHNA TOOLS — modular background-job registry.
 *
 * Every scheduled/background task lives here as one entry. The master cron
 * (cron/run.php → Scheduler) executes whichever are enabled + due. To add a new
 * task in future: append one entry — NO new server cron is ever needed.
 *
 * Each entry:
 *   key            unique id (also the cron_jobs.job_key)
 *   name           human label for the admin panel
 *   group          UI grouping
 *   schedule       'interval:<sec>' | 'cron:<m h dom mon dow>' | 'manual'
 *   default_enabled whether it is on by default on first install
 *   handler        fn(CronContext $ctx): string  — returns a short summary
 */
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/whatsapp.php';

/* ================= Queue helpers (used by any feature) ================= */

/** Enqueue a WhatsApp message for reliable, retried delivery. */
function kt_queue_whatsapp(string $number, string $message, ?string $mediaUrl = null, ?string $runAfter = null, ?int $userId = null): int {
    return insert('job_queue', [
        'channel' => 'whatsapp', 'recipient' => normalize_phone($number),
        'body' => $message, 'media_url' => $mediaUrl, 'user_id' => $userId,
        'run_after' => $runAfter ?: date('Y-m-d H:i:s'), 'status' => 'pending',
        'attempts' => 0, 'max_attempts' => 3,
        'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
    ]);
}

/** Enqueue an email for reliable, retried delivery. */
function kt_queue_email(string $to, string $subject, string $html, ?string $runAfter = null): int {
    return insert('job_queue', [
        'channel' => 'email', 'recipient' => $to, 'subject' => $subject, 'body' => $html,
        'run_after' => $runAfter ?: date('Y-m-d H:i:s'), 'status' => 'pending',
        'attempts' => 0, 'max_attempts' => 3,
        'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
    ]);
}

/** Render a WhatsApp template body (without sending). Returns null if missing/inactive. */
function kt_render_wa_template(string $key, array $vars = []): ?string {
    try {
        $tpl = one("SELECT * FROM " . tbl('whatsapp_templates') . " WHERE key_name = :k", [':k' => $key]);
        if (!$tpl || (int) $tpl['is_active'] !== 1) return null;
        $body = current_lang() === 'en' ? ($tpl['body_en'] ?: $tpl['body_gu']) : ($tpl['body_gu'] ?: $tpl['body_en']);
        foreach ($vars as $k => $v) $body = str_replace(':' . $k, (string) $v, $body);
        return $body;
    } catch (Throwable $e) { return null; }
}

/* ================= The job registry ================= */

function kt_cron_jobs(): array {
    return [

    // ── Message queue (WhatsApp + Email): the delivery engine ──────────
    [
        'key' => 'message_queue', 'name' => 'Message Queue (WhatsApp + Email)',
        'group' => 'delivery', 'schedule' => 'interval:60', 'default_enabled' => true,
        'handler' => 'kt_job_message_queue',
    ],

    // ── Subscription / plan expiry reminders + downgrade ───────────────
    [
        'key' => 'plan_expiry', 'name' => 'Plan Expiry Reminders & Downgrade',
        'group' => 'reminders', 'schedule' => 'cron:0 9 * * *', 'default_enabled' => true,
        'handler' => 'kt_job_plan_expiry',
    ],

    // ── AMC / warranty renewal reminders ───────────────────────────────
    [
        'key' => 'amc_reminder', 'name' => 'AMC / Warranty Renewal Reminders',
        'group' => 'reminders', 'schedule' => 'cron:0 10 * * *', 'default_enabled' => true,
        'handler' => 'kt_job_amc_reminder',
    ],

    // ── Cleanup: temp files, expired shares, guest limits, webhook bins ─
    [
        'key' => 'cleanup', 'name' => 'Cleanup (temp files, expired data)',
        'group' => 'maintenance', 'schedule' => 'interval:1800', 'default_enabled' => true,
        'handler' => 'kt_job_cleanup',
    ],

    // ── Nightly database backup ────────────────────────────────────────
    [
        'key' => 'db_backup', 'name' => 'Database Backup (nightly)',
        'group' => 'maintenance', 'schedule' => 'cron:30 2 * * *', 'default_enabled' => true,
        'handler' => 'kt_job_db_backup',
    ],

    // ── Daily admin report (off by default) ────────────────────────────
    [
        'key' => 'auto_report', 'name' => 'Daily Admin Report',
        'group' => 'reports', 'schedule' => 'cron:0 8 * * *', 'default_enabled' => false,
        'handler' => 'kt_job_auto_report',
    ],
    ];
}

/* ================= Handlers ================= */

/** Process pending queued messages (WhatsApp + email) with retry/backoff. */
function kt_job_message_queue(CronContext $ctx): string {
    $token = rand_token(12);
    $batch = 20;
    // Atomically claim a batch of due, pending items.
    q("UPDATE " . tbl('job_queue') . " SET status = 'processing', claimed_by = :tok, updated_at = NOW()
       WHERE status = 'pending' AND (run_after IS NULL OR run_after <= NOW())
       ORDER BY id ASC LIMIT $batch", [':tok' => $token]);
    $items = all("SELECT * FROM " . tbl('job_queue') . " WHERE claimed_by = :tok AND status = 'processing'", [':tok' => $token]);

    $sent = 0; $failed = 0; $retry = 0;
    foreach ($items as $it) {
        $attempts = (int) $it['attempts'] + 1;
        $ok = false; $err = '';
        try {
            if ($it['channel'] === 'whatsapp') {
                $res = $it['media_url']
                    ? wa()->sendMedia($it['recipient'], $it['body'] ?? '', $it['media_url'], $it['user_id'] ? (int) $it['user_id'] : null)
                    : wa()->send($it['recipient'], $it['body'] ?? '', $it['user_id'] ? (int) $it['user_id'] : null);
                $ok = !empty($res['ok']); $err = $ok ? '' : (string) ($res['response'] ?? 'send failed');
                usleep(1200000); // ~1.2s between WhatsApp sends to reduce ban risk
            } elseif ($it['channel'] === 'email') {
                require_once __DIR__ . '/mailer.php';
                $res = send_mail($it['recipient'], (string) $it['subject'], (string) $it['body']);
                $ok = !empty($res['ok']); $err = $ok ? '' : (string) ($res['error'] ?? 'send failed');
            } else {
                $err = 'unknown channel';
            }
        } catch (Throwable $e) { $err = $e->getMessage(); }

        if ($ok) {
            update('job_queue', ['status' => 'sent', 'attempts' => $attempts, 'claimed_by' => null,
                'last_error' => null, 'updated_at' => date('Y-m-d H:i:s')], ['id' => $it['id']]);
            $sent++;
        } elseif ($attempts >= (int) $it['max_attempts']) {
            update('job_queue', ['status' => 'failed', 'attempts' => $attempts, 'claimed_by' => null,
                'last_error' => mb_substr($err, 0, 500), 'updated_at' => date('Y-m-d H:i:s')], ['id' => $it['id']]);
            $failed++;
            $ctx->log("FAILED #{$it['id']} ({$it['channel']} → {$it['recipient']}): $err");
        } else {
            // Exponential-ish backoff: attempts^2 minutes.
            $delay = $attempts * $attempts;
            update('job_queue', ['status' => 'pending', 'attempts' => $attempts, 'claimed_by' => null,
                'last_error' => mb_substr($err, 0, 500), 'run_after' => date('Y-m-d H:i:s', time() + $delay * 60),
                'updated_at' => date('Y-m-d H:i:s')], ['id' => $it['id']]);
            $retry++;
        }
    }
    return $ctx->done("queue processed — sent:$sent failed:$failed retry:$retry (batch " . count($items) . ")");
}

/** Plan expiry reminders (7/3/1 days) + downgrade expired paid users. */
function kt_job_plan_expiry(CronContext $ctx): string {
    require_once __DIR__ . '/auth.php';
    $link = rtrim(SITE_URL, '/') . '/pricing.php';
    $free = plan_free();
    $freeId = (int) ($free['id'] ?? 0);
    $reminders = 0; $expired = 0; $skipped = 0;

    foreach ([7, 3, 1] as $d) {
        $users = all("SELECT * FROM " . tbl('users') . "
                      WHERE plan_expiry IS NOT NULL
                        AND DATE(plan_expiry) = DATE(DATE_ADD(NOW(), INTERVAL :d DAY))
                        AND status = 'active'", [':d' => $d]);
        foreach ($users as $u) {
            $action = 'expiry_reminder_' . $d;
            if (empty($u['phone']) || kt_reminder_already_sent((int) $u['id'], $action)) { $skipped++; continue; }
            $body = kt_render_wa_template('expiry_reminder', ['days' => $d, 'link' => $link]);
            if ($body) {
                kt_queue_whatsapp($u['phone'], $body, null, null, (int) $u['id']);   // enqueue → sent by message_queue
                activity_log((int) $u['id'], $action, "queued {$d}d");
                $reminders++;
            }
        }
    }

    $expiredUsers = all("SELECT * FROM " . tbl('users') . "
                         WHERE plan_expiry IS NOT NULL AND plan_expiry < NOW()
                           AND plan_id IS NOT NULL AND plan_id <> :free AND status = 'active'", [':free' => $freeId]);
    foreach ($expiredUsers as $u) {
        if (!empty($u['phone']) && !kt_reminder_already_sent((int) $u['id'], 'plan_expired')) {
            $body = kt_render_wa_template('plan_expired', ['link' => $link]);
            if ($body) { kt_queue_whatsapp($u['phone'], $body, null, null, (int) $u['id']); activity_log((int) $u['id'], 'plan_expired', 'queued'); }
        }
        update('users', ['plan_id' => ($freeId ?: null), 'plan_expiry' => null], ['id' => $u['id']]);
        $expired++;
    }
    return $ctx->done("reminders:$reminders expired-downgraded:$expired skipped:$skipped");
}

/** AMC reminders from the admin-maintained settings('amc_records') JSON list. */
function kt_job_amc_reminder(CronContext $ctx): string {
    $raw = setting('amc_records', '');
    $records = (is_string($raw) && trim($raw) !== '') ? (json_decode($raw, true) ?: []) : [];
    if (!$records) return $ctx->done('no AMC records');
    $today = new DateTimeImmutable('today');
    $sent = 0; $checked = 0;
    foreach ($records as $rec) {
        $checked++;
        $client = trim((string) ($rec['client'] ?? '')); $phone = trim((string) ($rec['phone'] ?? ''));
        $renew = trim((string) ($rec['renew_on'] ?? ''));
        if ($client === '' || $phone === '' || $renew === '') continue;
        try { $due = new DateTimeImmutable($renew); } catch (Throwable $e) { continue; }
        $days = (int) $today->diff($due)->format('%r%a');
        if ($days < 0 || $days > 7) continue;
        $body = kt_render_wa_template('amc_renewal', ['days' => $days, 'client' => $client]);
        if ($body) { kt_queue_whatsapp($phone, $body); $sent++; }
    }
    return $ctx->done("checked:$checked queued:$sent");
}

/** Cleanup temp/share uploads, expired saved files, guest limits, webhook bins. */
function kt_job_cleanup(CronContext $ctx): string {
    $now = time();
    $maxAge = 2 * 3600;
    $uploads = defined('KT_UPLOADS') ? KT_UPLOADS : (dirname(__DIR__) . '/uploads');
    $s = ['temp' => 0, 'share' => 0, 'dirs' => 0, 'saved_rows' => 0, 'saved_files' => 0, 'guest' => 0, 'webhook' => 0];

    $purge = function (string $dir) use ($maxAge, $now, &$purge): int {
        $c = 0; if (!is_dir($dir)) return 0;
        foreach (scandir($dir) ?: [] as $e) {
            if ($e === '.' || $e === '..') continue;
            $p = $dir . '/' . $e;
            if (is_dir($p)) $c += $purge($p);
            elseif (is_file($p) && ($now - @filemtime($p)) > $maxAge && @unlink($p)) $c++;
        }
        return $c;
    };
    $s['temp'] = $purge($uploads . '/temp');
    $s['share'] = $purge($uploads . '/share');
    // Remove empty share token dirs.
    foreach (glob($uploads . '/share/*', GLOB_ONLYDIR) ?: [] as $d) {
        if (!array_diff(scandir($d) ?: [], ['.', '..']) && @rmdir($d)) $s['dirs']++;
    }
    try {
        foreach (all("SELECT id, path FROM " . tbl('saved_files') . " WHERE expires_at IS NOT NULL AND expires_at < NOW()") as $r) {
            if (!empty($r['path'])) { $abs = KT_ROOT . '/' . ltrim($r['path'], '/'); if (is_file($abs) && @unlink($abs)) $s['saved_files']++; }
            q("DELETE FROM " . tbl('saved_files') . " WHERE id = :id", [':id' => $r['id']]); $s['saved_rows']++;
        }
    } catch (Throwable $e) { $ctx->log('saved_files: ' . $e->getMessage()); }
    try {
        $s['guest'] = q("DELETE FROM " . tbl('guest_limits') . " WHERE day < :d", [':d' => date('Y-m-d', $now - 2 * 86400)])->rowCount();
    } catch (Throwable $e) { $ctx->log('guest_limits: ' . $e->getMessage()); }
    try {
        foreach (all("SELECT token FROM " . tbl('webhook_bins') . " WHERE expires_at < NOW()") as $b) {
            q("DELETE FROM " . tbl('webhook_requests') . " WHERE bin_token = :t", [':t' => $b['token']]);
        }
        $s['webhook'] = q("DELETE FROM " . tbl('webhook_bins') . " WHERE expires_at < NOW()")->rowCount();
    } catch (Throwable $e) { /* table may not exist */ }
    // Purge sent/failed queue rows older than 7 days.
    try { q("DELETE FROM " . tbl('job_queue') . " WHERE status IN ('sent','failed') AND updated_at < :d",
            [':d' => date('Y-m-d H:i:s', $now - 7 * 86400)]); } catch (Throwable $e) {}

    return $ctx->done("temp:{$s['temp']} share:{$s['share']} dirs:{$s['dirs']} saved:{$s['saved_rows']} guest:{$s['guest']} webhook:{$s['webhook']}");
}

/** Nightly database backup to /uploads/backups (gzip), keeping the last 7. */
function kt_job_db_backup(CronContext $ctx): string {
    $dir = KT_UPLOADS . '/backups';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    $file = $dir . '/backup-' . date('Y-m-d-His') . '.sql';
    $fp = fopen($file, 'w');
    if (!$fp) throw new RuntimeException('cannot write backup file');
    fwrite($fp, "-- Krishna Tools backup " . date('c') . "\nSET NAMES utf8mb4;\nSET foreign_key_checks=0;\n");
    $tables = [];
    foreach (all("SHOW TABLES") as $row) { $t = array_values($row)[0]; if (str_starts_with($t, DB_PREFIX)) $tables[] = $t; }
    $rowsTotal = 0;
    foreach ($tables as $t) {
        $create = one("SHOW CREATE TABLE `$t`");
        $ddl = $create['Create Table'] ?? array_values($create)[1] ?? '';
        fwrite($fp, "\nDROP TABLE IF EXISTS `$t`;\n$ddl;\n");
        foreach (all("SELECT * FROM `$t`") as $r) {
            $cols = implode(',', array_map(fn($c) => "`$c`", array_keys($r)));
            $vals = implode(',', array_map(fn($v) => $v === null ? 'NULL' : db()->quote((string) $v), array_values($r)));
            fwrite($fp, "INSERT INTO `$t` ($cols) VALUES ($vals);\n");
            $rowsTotal++;
        }
    }
    fwrite($fp, "\nSET foreign_key_checks=1;\n");
    fclose($fp);
    // Gzip if available to save space.
    if (function_exists('gzencode')) {
        $gz = $file . '.gz';
        if (@file_put_contents($gz, gzencode(file_get_contents($file), 6)) !== false) { @unlink($file); $file = $gz; }
    }
    // Keep only the newest 7 backups.
    $all = glob($dir . '/backup-*.sql*') ?: [];
    usort($all, fn($a, $b) => filemtime($b) <=> filemtime($a));
    foreach (array_slice($all, 7) as $old) @unlink($old);
    return $ctx->done(count($tables) . " tables, $rowsTotal rows → " . basename($file) . ' (' . round(filesize($file) / 1024) . ' KB)');
}

/** Daily admin summary via email + WhatsApp queue (off by default). */
function kt_job_auto_report(CronContext $ctx): string {
    $today = date('Y-m-d');
    $users = (int) scalar("SELECT COUNT(*) FROM " . tbl('users'));
    $newUsers = (int) scalar("SELECT COUNT(*) FROM " . tbl('users') . " WHERE DATE(created_at) = :d", [':d' => $today]);
    $activeSubs = (int) scalar("SELECT COUNT(*) FROM " . tbl('users') . " WHERE plan_expiry > NOW()");
    $revenue = (float) scalar("SELECT COALESCE(SUM(amount),0) FROM " . tbl('payments') . " WHERE status='paid' AND DATE(created_at) = :d", [':d' => $today]);
    $usages = (int) scalar("SELECT COUNT(*) FROM " . tbl('tool_usage') . " WHERE DATE(created_at) = :d", [':d' => $today]);

    $summary = "📊 Krishna Tools — daily report ($today)\n"
        . "Users: $users (+$newUsers today)\nActive subscriptions: $activeSubs\n"
        . "Revenue today: ₹" . number_format($revenue, 0) . "\nTool usages today: $usages";

    $adminEmail = (string) scalar("SELECT email FROM " . tbl('users') . " WHERE role='admin' ORDER BY id LIMIT 1");
    if ($adminEmail) kt_queue_email($adminEmail, "Krishna Tools daily report — $today", nl2br(e($summary)));
    $shop = setting('shop_whatsapp', setting('wa_sender', ''));
    if ($shop) kt_queue_whatsapp($shop, $summary);
    return $ctx->done("report queued (users:$users rev:₹" . number_format($revenue, 0) . " usages:$usages)");
}

/** True if a reminder action was already logged for a user today (dedup). */
function kt_reminder_already_sent(int $uid, string $action): bool {
    try {
        return one("SELECT id FROM " . tbl('activity_logs') . "
                    WHERE user_id = :u AND action = :a AND DATE(created_at) = CURDATE() LIMIT 1",
                   [':u' => $uid, ':a' => $action]) !== null;
    } catch (Throwable $e) { return false; }
}
