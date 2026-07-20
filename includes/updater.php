<?php
/**
 * KRISHNA TOOLS — GitHub self-updater ("Check for Update" → "Update Now").
 *
 * Set repo/branch/token once in Admin → Update. "Check for Update" queries the
 * GitHub API for the latest commit on the branch; "Update Now" downloads that
 * commit's zipball, overlays the app files, and runs the idempotent DB
 * re-seed — so code AND database update together.
 *
 * NEVER touched: /config/config.php, /config/install.lock, /uploads, /logs,
 * /.git — your database password, bills and photos are always preserved.
 *
 * Security: admin-only + CSRF gated. This is an intentional self-update
 * mechanism that fetches YOUR own repository; treat the GitHub token as a
 * secret (stored in the settings table, readable only via the admin panel).
 */
require_once __DIR__ . '/functions.php';

class GitHubUpdater {
    private string $repo;
    private string $branch;
    private string $token;

    /** Files/dirs (repo-relative) that must never be overwritten or removed. */
    private array $protected = [
        'config/config.php',
        'config/install.lock',
        'uploads',
        'logs',
        '.git',
    ];

    public function __construct() {
        $this->repo   = trim(setting('gh_repo', ''));
        $this->branch = trim(setting('gh_branch', 'main'));
        $this->token  = trim(setting('gh_token', ''));
    }

    public function isConfigured(): bool {
        return $this->repo !== '' && str_contains($this->repo, '/');
    }

    public function currentVersion(): string {
        return setting('gh_current_version', '');
    }

    /** Query the latest commit on the branch. */
    public function check(): array {
        if (!$this->isConfigured()) return ['ok' => false, 'error' => 'Repo not set. Enter owner/repo first.'];
        $url = 'https://api.github.com/repos/' . $this->repo . '/commits/' . rawurlencode($this->branch);
        [$code, $body] = $this->api($url);
        if ($code === 404) return ['ok' => false, 'error' => 'Repo or branch not found (or token lacks access).'];
        if ($code === 401 || $code === 403) return ['ok' => false, 'error' => 'GitHub auth failed — check the token / rate limit.'];
        if ($code !== 200) return ['ok' => false, 'error' => "GitHub API error (HTTP $code)."];
        $data = json_decode($body, true);
        $sha  = $data['sha'] ?? '';
        $date = $data['commit']['committer']['date'] ?? ($data['commit']['author']['date'] ?? '');
        $msg  = $data['commit']['message'] ?? '';
        $cur  = $this->currentVersion();
        return [
            'ok' => true,
            'current' => $cur,
            'latest' => $sha,
            'short' => substr($sha, 0, 7),
            'date' => $date ? date('Y-m-d H:i', strtotime($date)) : '',
            'message' => mb_substr(strtok($msg, "\n"), 0, 120),
            'update_available' => $sha !== '' && $sha !== $cur,
        ];
    }

    /**
     * Download the latest zipball and overlay it, then re-seed the DB.
     * Returns ['ok', 'files', 'version', 'log'=>[...], 'error'].
     */
    public function update(?string $adminName = null): array {
        $log = [];
        if (!$this->isConfigured()) return ['ok' => false, 'error' => 'Repo not set.'];
        if (!class_exists('ZipArchive')) return ['ok' => false, 'error' => 'PHP zip extension is required for updates.'];

        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        // 1) Resolve the latest commit SHA.
        $chk = $this->check();
        if (!$chk['ok']) return $chk;
        $sha = $chk['latest'];
        $log[] = 'Latest commit: ' . substr($sha, 0, 7) . ' (' . $chk['date'] . ')';

        // 2) Download the zipball.
        $tmp = KT_UPLOADS . '/temp';
        @mkdir($tmp, 0755, true);
        $zipPath = $tmp . '/kt_update_' . rand_token(8) . '.zip';
        $url = 'https://api.github.com/repos/' . $this->repo . '/zipball/' . rawurlencode($this->branch);
        $ok = $this->download($url, $zipPath);
        if (!$ok || !is_file($zipPath) || filesize($zipPath) < 100) {
            @unlink($zipPath);
            return ['ok' => false, 'error' => 'Failed to download the update archive from GitHub.'];
        }
        $log[] = 'Downloaded ' . number_format(filesize($zipPath) / 1024, 0) . ' KB archive';

        // 3) Extract.
        $extractDir = $tmp . '/kt_extract_' . rand_token(8);
        @mkdir($extractDir, 0755, true);
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) { @unlink($zipPath); return ['ok' => false, 'error' => 'Could not open the downloaded archive.']; }
        $zip->extractTo($extractDir);
        $zip->close();
        @unlink($zipPath);

        // GitHub zipballs contain a single top-level folder: owner-repo-<sha>/
        $tops = array_values(array_filter(glob($extractDir . '/*'), 'is_dir'));
        if (!$tops) { $this->rrmdir($extractDir); return ['ok' => false, 'error' => 'Unexpected archive layout.']; }
        $src = $tops[0];
        $log[] = 'Extracted archive';

        // 4) Overlay files, skipping protected paths.
        $count = $this->copyTree($src, KT_ROOT, '');
        $this->rrmdir($extractDir);
        $log[] = "Copied $count files (config.php & uploads/ preserved)";

        // 5) Auto-update the database from the freshly-copied installer library.
        try {
            require_once KT_ROOT . '/install/lib.php';
            $pdo = db();
            install_schema($pdo, DB_PREFIX);                 // CREATE TABLE IF NOT EXISTS — adds any new tables
            $seed = install_seed_tools($pdo, DB_PREFIX);      // upsert tools + categories
            install_seed_plans($pdo, DB_PREFIX);              // only if plans table empty
            install_seed_templates($pdo, DB_PREFIX);          // upsert WhatsApp templates
            install_seed_blog($pdo, DB_PREFIX);               // upsert blog posts
            $log[] = "Database updated (tools: {$seed['tools']}, categories: {$seed['categories']})";
        } catch (Throwable $e) {
            $log[] = 'DB update warning: ' . $e->getMessage();
        }

        // 6) Record version + history.
        set_setting('gh_current_version', $sha);
        set_setting('gh_last_update', date('Y-m-d H:i:s'));
        $hist = json_decode(setting('gh_update_history', '[]'), true) ?: [];
        array_unshift($hist, [
            'version' => 'gh:' . substr($sha, 0, 7),
            'sha' => $sha,
            'date' => date('Y-m-d H:i:s'),
            'files' => $count,
            'by' => $adminName ?: 'Admin',
        ]);
        $hist = array_slice($hist, 0, 25); // keep the last 25
        set_setting('gh_update_history', json_encode($hist, JSON_UNESCAPED_UNICODE));

        activity_log(current_user()['id'] ?? null, 'github_update', 'Updated to ' . substr($sha, 0, 7) . " ($count files)");
        return ['ok' => true, 'files' => $count, 'version' => substr($sha, 0, 7), 'log' => $log];
    }

    public function history(): array {
        return json_decode(setting('gh_update_history', '[]'), true) ?: [];
    }

    /* ── internals ── */

    /** Recursively copy $src into $dst, skipping protected paths. Returns file count. */
    private function copyTree(string $src, string $dstRoot, string $rel): int {
        $count = 0;
        foreach (scandir($src) as $entry) {
            if ($entry === '.' || $entry === '..') continue;
            $childRel = $rel === '' ? $entry : $rel . '/' . $entry;
            if ($this->isProtected($childRel)) continue;
            $from = $src . '/' . $entry;
            $to   = $dstRoot . '/' . $childRel;
            if (is_dir($from)) {
                if (!is_dir($to)) @mkdir($to, 0755, true);
                $count += $this->copyTree($from, $dstRoot, $childRel);
            } else {
                $dir = dirname($to);
                if (!is_dir($dir)) @mkdir($dir, 0755, true);
                if (@copy($from, $to)) $count++;
            }
        }
        return $count;
    }

    private function isProtected(string $rel): bool {
        foreach ($this->protected as $p) {
            if ($rel === $p || str_starts_with($rel, $p . '/')) return true;
        }
        return false;
    }

    /** GitHub API GET with auth + required User-Agent. Returns [httpCode, body]. */
    private function api(string $url): array {
        $ch = curl_init($url);
        $headers = ['User-Agent: KrishnaTools-Updater', 'Accept: application/vnd.github+json'];
        if ($this->token !== '') $headers[] = 'Authorization: Bearer ' . $this->token;
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $body = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return [$code, (string) $body];
    }

    /** Stream a URL to a file (follows GitHub's redirect to codeload). */
    private function download(string $url, string $path): bool {
        $fp = fopen($path, 'w');
        if (!$fp) return false;
        $headers = ['User-Agent: KrishnaTools-Updater', 'Accept: application/vnd.github+json'];
        if ($this->token !== '') $headers[] = 'Authorization: Bearer ' . $this->token;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_FILE => $fp,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 180,
            CURLOPT_FAILONERROR => true,
        ]);
        $ok = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);
        return $ok !== false && $code >= 200 && $code < 300;
    }

    private function rrmdir(string $dir): void {
        if (!is_dir($dir)) return;
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($it as $f) { $f->isDir() ? @rmdir($f->getPathname()) : @unlink($f->getPathname()); }
        @rmdir($dir);
    }
}
