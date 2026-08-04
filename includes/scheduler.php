<?php
/**
 * KRISHNA TOOLS — centralized scheduler.
 *
 * One server cron (`* * * * * php cron/run.php`) calls Scheduler::runDue()
 * every minute. That runner evaluates every registered job, and executes the
 * ones that are enabled + due — so ALL background work flows through a single
 * cron. Jobs are defined modularly in includes/cron_jobs.php; adding a new one
 * never needs a new server cron.
 *
 * Safety:
 *  - Master lock (flock, non-blocking) prevents overlapping master runs.
 *  - Per-job DB lock (atomic conditional UPDATE + stale timeout) prevents the
 *    same job running twice at once (e.g. cron + admin "Run now").
 *  - Every execution is recorded in cron_runs with status, duration and output.
 */
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/cron_jobs.php';

class Scheduler {
    /** A job is considered dead (lock auto-released) after this many seconds. */
    const STALE_LOCK_SECONDS = 900;      // 15 min
    /** Keep this many days of run history. */
    const HISTORY_DAYS = 14;

    private $masterFp = null;

    /* ── Registry ───────────────────────────────────────────── */

    /** All registered job definitions keyed by job_key. */
    public function jobs(): array {
        $out = [];
        foreach (kt_cron_jobs() as $job) $out[$job['key']] = $job;
        return $out;
    }

    /** Ensure a cron_jobs state row exists for every registered job. */
    public function syncRegistry(): void {
        foreach ($this->jobs() as $key => $job) {
            $exists = one("SELECT id, schedule FROM " . tbl('cron_jobs') . " WHERE job_key = :k", [':k' => $key]);
            if (!$exists) {
                insert('cron_jobs', [
                    'job_key' => $key, 'name' => $job['name'], 'schedule' => $job['schedule'],
                    'is_enabled' => !empty($job['default_enabled']) ? 1 : 0,
                    'run_count' => 0, 'fail_count' => 0, 'updated_at' => date('Y-m-d H:i:s'),
                ]);
            } elseif ($exists['schedule'] !== $job['schedule'] || true) {
                // Keep the human-facing name/schedule in sync with code (not enabled/state).
                update('cron_jobs', ['name' => $job['name'], 'schedule' => $job['schedule']], ['job_key' => $key]);
            }
        }
    }

    /** Merge code definition + DB state for the admin UI. */
    public function jobsWithState(): array {
        $this->syncRegistry();
        $defs = $this->jobs();
        $rows = [];
        foreach (all("SELECT * FROM " . tbl('cron_jobs')) as $r) $rows[$r['job_key']] = $r;
        $out = [];
        foreach ($defs as $key => $def) {
            $state = $rows[$key] ?? [];
            $out[] = array_merge($def, $state, [
                'group' => $def['group'] ?? 'general',
                'is_enabled' => (int) ($state['is_enabled'] ?? (!empty($def['default_enabled']) ? 1 : 0)),
                'due' => $this->isDue($def, $state),
            ]);
        }
        return $out;
    }

    /* ── Scheduling ─────────────────────────────────────────── */

    /**
     * Is a job due to run now? Schedule formats:
     *   'interval:<seconds>'   — run when now-last_run >= seconds
     *   'cron:<m h dom mon dow>'— run when the current minute matches the cron
     *   'manual'               — never auto-runs (admin "Run now" only)
     */
    public function isDue(array $def, array $state): bool {
        $schedule = $def['schedule'] ?? 'manual';
        $lastRun = !empty($state['last_run']) ? strtotime($state['last_run']) : 0;
        $now = time();

        if ($schedule === 'manual') return false;

        if (str_starts_with($schedule, 'interval:')) {
            $sec = max(30, (int) substr($schedule, 9));
            return ($now - $lastRun) >= $sec;
        }
        if (str_starts_with($schedule, 'cron:')) {
            $expr = trim(substr($schedule, 5));
            // Match the current minute, and only once per minute.
            if (!$this->cronMatches($expr, $now)) return false;
            return $lastRun < strtotime(date('Y-m-d H:i:00', $now)); // not already run this minute
        }
        return false;
    }

    /** Standard 5-field cron matcher (minute hour dom month dow). */
    public function cronMatches(string $expr, int $ts): bool {
        $parts = preg_split('/\s+/', trim($expr));
        if (count($parts) !== 5) return false;
        [$min, $hour, $dom, $mon, $dow] = $parts;
        $t = getdate($ts);
        return $this->fieldMatches($min, $t['minutes'], 0, 59)
            && $this->fieldMatches($hour, $t['hours'], 0, 23)
            && $this->fieldMatches($dom, $t['mday'], 1, 31)
            && $this->fieldMatches($mon, $t['mon'], 1, 12)
            && $this->fieldMatches($dow, $t['wday'], 0, 6);
    }

    private function fieldMatches(string $field, int $value, int $min, int $max): bool {
        foreach (explode(',', $field) as $part) {
            if ($part === '*') return true;
            if (str_contains($part, '/')) {           // */n or a-b/n
                [$range, $step] = explode('/', $part, 2);
                $step = max(1, (int) $step);
                [$lo, $hi] = $range === '*' ? [$min, $max] : (str_contains($range, '-')
                    ? array_map('intval', explode('-', $range)) : [(int) $range, $max]);
                for ($i = $lo; $i <= $hi; $i += $step) if ($i === $value) return true;
            } elseif (str_contains($part, '-')) {      // a-b
                [$lo, $hi] = array_map('intval', explode('-', $part));
                if ($value >= $lo && $value <= $hi) return true;
            } elseif ((string) $value === $part || (int) $part === $value) {
                return true;
            }
        }
        return false;
    }

    /** Human next-run estimate for display (best-effort). */
    public function nextRunEstimate(array $def, array $state): ?string {
        $schedule = $def['schedule'] ?? 'manual';
        if ($schedule === 'manual') return null;
        $lastRun = !empty($state['last_run']) ? strtotime($state['last_run']) : time();
        if (str_starts_with($schedule, 'interval:')) {
            return date('Y-m-d H:i', $lastRun + max(30, (int) substr($schedule, 9)));
        }
        if (str_starts_with($schedule, 'cron:')) {
            $expr = trim(substr($schedule, 5));
            for ($i = 1; $i <= 1440 * 8; $i++) {           // scan up to 8 days ahead
                $ts = strtotime("+$i minute", strtotime(date('Y-m-d H:i:00')));
                if ($this->cronMatches($expr, $ts)) return date('Y-m-d H:i', $ts);
            }
        }
        return null;
    }

    /* ── Locking ────────────────────────────────────────────── */

    /** Non-blocking master lock; returns false if another master run holds it. */
    public function acquireMasterLock(): bool {
        $dir = KT_UPLOADS . '/cron';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        $this->masterFp = @fopen($dir . '/master.lock', 'c');
        if (!$this->masterFp) return true; // if we can't lock, don't block the whole system
        return flock($this->masterFp, LOCK_EX | LOCK_NB);
    }

    public function releaseMasterLock(): void {
        if ($this->masterFp) { @flock($this->masterFp, LOCK_UN); @fclose($this->masterFp); $this->masterFp = null; }
    }

    /** Atomically claim a job. Returns a lock token, or null if already locked. */
    private function lockJob(string $key): ?string {
        $token = rand_token(12);
        $stale = date('Y-m-d H:i:s', time() - self::STALE_LOCK_SECONDS);
        $n = q("UPDATE " . tbl('cron_jobs') . "
                SET locked_at = NOW(), lock_token = :tok, updated_at = NOW()
                WHERE job_key = :k AND (locked_at IS NULL OR locked_at < :stale)",
               [':tok' => $token, ':k' => $key, ':stale' => $stale])->rowCount();
        return $n === 1 ? $token : null;
    }

    private function unlockJob(string $key, string $token): void {
        q("UPDATE " . tbl('cron_jobs') . " SET locked_at = NULL, lock_token = NULL, updated_at = NOW()
           WHERE job_key = :k AND lock_token = :tok", [':k' => $key, ':tok' => $token]);
    }

    /* ── Execution ──────────────────────────────────────────── */

    /**
     * Run a single job (respecting the per-job lock unless $force skips due check).
     * $force ignores the schedule (admin "Run now") but STILL takes the lock.
     * Returns ['ok'=>bool, 'status'=>..., 'message'=>..., 'skipped'=>bool].
     */
    public function runJob(string $key, bool $force = false, string $trigger = 'cron'): array {
        $defs = $this->jobs();
        if (!isset($defs[$key])) return ['ok' => false, 'status' => 'error', 'message' => 'Unknown job', 'skipped' => true];
        $def = $defs[$key];

        $token = $this->lockJob($key);
        if ($token === null) {
            return ['ok' => false, 'status' => 'locked', 'message' => 'Already running', 'skipped' => true];
        }

        // Record a running row.
        $runId = insert('cron_runs', ['job_key' => $key, 'status' => 'running', 'trigger_by' => $trigger,
            'started_at' => date('Y-m-d H:i:s')]);

        $t0 = microtime(true);
        $status = 'success'; $message = ''; $output = '';
        $ctx = new CronContext($key);
        try {
            $result = ($def['handler'])($ctx);
            $message = is_string($result) ? $result : ($ctx->summary() ?: 'done');
            $output = $ctx->getLog();
        } catch (Throwable $e) {
            $status = 'failed';
            $message = $e->getMessage();
            $output = $ctx->getLog() . "\n" . $e->getFile() . ':' . $e->getLine();
            kt_error_log("cron[$key] failed: " . $e->getMessage());
        }
        $dur = (int) round((microtime(true) - $t0) * 1000);

        // Finish the run row + update job state.
        update('cron_runs', ['status' => $status, 'message' => mb_substr($message, 0, 2000),
            'output' => mb_substr($output, 0, 20000), 'finished_at' => date('Y-m-d H:i:s'), 'duration_ms' => $dur],
            ['id' => $runId]);

        $inc = $status === 'failed' ? 'fail_count = fail_count + 1' : 'run_count = run_count + 1';
        q("UPDATE " . tbl('cron_jobs') . " SET last_run = NOW(), last_status = :s, last_message = :m,
              last_duration_ms = :d, next_run = :nr, $inc, updated_at = NOW() WHERE job_key = :k",
           [':s' => $status, ':m' => mb_substr($message, 0, 1000), ':d' => $dur,
            ':nr' => $this->nextRunEstimate($def, ['last_run' => date('Y-m-d H:i:s')]), ':k' => $key]);

        $this->unlockJob($key, $token);
        return ['ok' => $status !== 'failed', 'status' => $status, 'message' => $message, 'skipped' => false, 'duration_ms' => $dur];
    }

    /**
     * The master runner (called by cron/run.php). Runs every enabled + due job.
     * Returns a per-job summary array.
     */
    public function runDue(string $trigger = 'cron'): array {
        $this->syncRegistry();
        set_setting('cron_last_master_run', date('Y-m-d H:i:s'));

        $states = [];
        foreach (all("SELECT * FROM " . tbl('cron_jobs')) as $r) $states[$r['job_key']] = $r;

        $summary = [];
        foreach ($this->jobs() as $key => $def) {
            $state = $states[$key] ?? [];
            if ((int) ($state['is_enabled'] ?? 1) !== 1) { $summary[$key] = 'disabled'; continue; }
            if (!$this->isDue($def, $state)) { $summary[$key] = 'not-due'; continue; }
            $res = $this->runJob($key, false, $trigger);
            $summary[$key] = $res['status'] . ($res['skipped'] ? '(skipped)' : '');
        }

        // Housekeeping: trim old run history.
        try {
            q("DELETE FROM " . tbl('cron_runs') . " WHERE started_at < :d",
              [':d' => date('Y-m-d H:i:s', time() - self::HISTORY_DAYS * 86400)]);
        } catch (Throwable $e) {}

        return $summary;
    }

    /* ── Health ─────────────────────────────────────────────── */

    /** Overall scheduler health for the admin dashboard. */
    public function health(): array {
        $last = setting('cron_last_master_run', '');
        $lastTs = $last ? strtotime($last) : 0;
        $ageSec = $lastTs ? (time() - $lastTs) : null;
        // Healthy if the master ran within the last 3 minutes.
        $ok = $ageSec !== null && $ageSec <= 180;
        $failing = 0;
        try {
            $failing = (int) scalar("SELECT COUNT(*) FROM " . tbl('cron_jobs') . " WHERE last_status = 'failed'");
        } catch (Throwable $e) {}
        return ['ok' => $ok, 'last_run' => $last, 'age_seconds' => $ageSec, 'failing_jobs' => $failing,
                'status' => $lastTs === 0 ? 'never' : ($ok ? 'running' : 'stalled')];
    }

    public function setEnabled(string $key, bool $enabled): void {
        update('cron_jobs', ['is_enabled' => $enabled ? 1 : 0, 'updated_at' => date('Y-m-d H:i:s')], ['job_key' => $key]);
    }
}

/**
 * Passed to each job handler. Collects a log + summary and exposes tbl()/db()
 * helpers implicitly (they are global). Handlers call $ctx->log(...) and may
 * return a string summary (or set $ctx->summary via ->done()).
 */
class CronContext {
    private string $key;
    private array $lines = [];
    private string $summary = '';
    public function __construct(string $key) { $this->key = $key; }
    public function log(string $line): void { $this->lines[] = '[' . date('H:i:s') . '] ' . $line; }
    public function done(string $summary): string { $this->summary = $summary; return $summary; }
    public function summary(): string { return $this->summary; }
    public function getLog(): string { return implode("\n", $this->lines); }
    public function jobKey(): string { return $this->key; }
}
