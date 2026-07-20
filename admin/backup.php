<?php
/**
 * KRISHNA TOOLS — one-click SQL backup of all prefixed tables (PDO only).
 * GET shows a confirmation page; POST streams a downloadable .sql dump.
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    kt_session_start();
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419);
        exit('CSRF');
    }
    admin_stream_backup();
    exit;
}

/** Stream a full SQL dump of every DB_PREFIX table. */
function admin_stream_backup(): void {
    $pdo = db();
    $prefix = DB_PREFIX;

    // Discover our tables (parametrised against information_schema).
    $tables = [];
    try {
        $rows = all("SELECT table_name AS t FROM information_schema.tables
                     WHERE table_schema = DATABASE() AND table_name LIKE :p
                     ORDER BY table_name", [':p' => $prefix . '%']);
        foreach ($rows as $r) {
            $name = $r['t'] ?? ($r['table_name'] ?? '');
            // Identifier safety: only our own prefixed, word-char names.
            if ($name !== '' && preg_match('/^[A-Za-z0-9_]+$/', $name) && str_starts_with($name, $prefix)) {
                $tables[] = $name;
            }
        }
    } catch (Throwable $e) {
        http_response_code(500);
        exit('Backup failed: unable to list tables.');
    }

    $fname = 'krishnatools-backup-' . date('Ymd') . '.sql';
    header('Content-Type: application/sql; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $fname . '"');
    header('Cache-Control: no-store');
    while (ob_get_level() > 0) { ob_end_clean(); }

    $out = function (string $s) { echo $s; flush(); };

    $out("-- KRISHNA TOOLS SQL backup\n");
    $out("-- Generated: " . date('c') . "\n");
    $out("-- Tables: " . count($tables) . "\n");
    $out("SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS = 0;\n\n");

    foreach ($tables as $tbl) {
        $q = '`' . str_replace('`', '``', $tbl) . '`';
        $out("-- ----------------------------\n-- Table: $tbl\n-- ----------------------------\n");
        $out("DROP TABLE IF EXISTS $q;\n");

        // CREATE statement.
        try {
            $create = $pdo->query("SHOW CREATE TABLE $q")->fetch(PDO::FETCH_ASSOC);
            $ddl = $create['Create Table'] ?? ($create['Create View'] ?? '');
            if ($ddl) $out($ddl . ";\n\n");
        } catch (Throwable $e) {
            $out("-- (could not read structure: " . str_replace("\n", ' ', $e->getMessage()) . ")\n\n");
            continue;
        }

        // Data in batches to guard against large tables.
        try {
            $total = (int) scalar("SELECT COUNT(*) FROM $q");
            $batch = 500;
            for ($off = 0; $off < $total; $off += $batch) {
                $rows = $pdo->query("SELECT * FROM $q LIMIT $batch OFFSET $off")->fetchAll(PDO::FETCH_ASSOC);
                if (!$rows) break;
                $cols = array_keys($rows[0]);
                $colList = implode(',', array_map(fn($c) => '`' . str_replace('`', '``', $c) . '`', $cols));
                foreach ($rows as $row) {
                    $vals = [];
                    foreach ($row as $v) {
                        if ($v === null) $vals[] = 'NULL';
                        elseif (is_int($v) || is_float($v)) $vals[] = (string) $v;
                        else $vals[] = $pdo->quote((string) $v);
                    }
                    $out("INSERT INTO $q ($colList) VALUES (" . implode(',', $vals) . ");\n");
                }
            }
            $out("\n");
        } catch (Throwable $e) {
            $out("-- (could not dump data: " . str_replace("\n", ' ', $e->getMessage()) . ")\n\n");
        }
    }

    $out("SET FOREIGN_KEY_CHECKS = 1;\n-- End of backup\n");
}

require_once __DIR__ . '/layout.php';

// Preview list of tables for the confirmation page.
$tables = [];
try {
    $rows = all("SELECT table_name AS t, table_rows AS r FROM information_schema.tables
                 WHERE table_schema = DATABASE() AND table_name LIKE :p ORDER BY table_name",
                [':p' => DB_PREFIX . '%']);
    foreach ($rows as $r) $tables[] = $r;
} catch (Throwable $e) { kt_error_log('admin backup list: ' . $e->getMessage()); }

admin_head('બેકઅપ');
?>
<div class="kt-card p-6 max-w-2xl">
  <div class="flex items-center gap-3 mb-3">
    <span class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background:rgba(30,58,138,.12);color:var(--krishna-blue)"><i data-lucide="database-backup" class="w-6 h-6"></i></span>
    <div>
      <h2 class="section-title text-base m-0">ડેટાબેઝ બેકઅપ</h2>
      <p class="text-sm opacity-70 m-0">બધા <code><?= e(DB_PREFIX) ?>*</code> ટેબલનો <code>.sql</code> ડમ્પ ડાઉનલોડ કરો.</p>
    </div>
  </div>

  <?php if ($tables): ?>
    <div class="text-sm mb-4">
      <div class="kt-label !mb-1">ટેબલ્સ (<?= count($tables) ?>):</div>
      <div class="flex flex-wrap gap-1.5">
        <?php foreach ($tables as $t): ?>
          <span class="badge-premium text-xs"><?= e($t['t']) ?> (<?= number_format((int) ($t['r'] ?? 0)) ?>)</span>
        <?php endforeach; ?>
      </div>
      <p class="text-xs opacity-50 mt-2">રો સંખ્યા અંદાજિત છે (information_schema).</p>
    </div>
  <?php else: ?>
    <p class="text-sm opacity-60 mb-4">કોઈ ટેબલ મળ્યું નહીં.</p>
  <?php endif; ?>

  <form method="post">
    <?= csrf_field() ?>
    <button class="btn btn-primary"><i data-lucide="download" class="w-4 h-4"></i> બેકઅપ ડાઉનલોડ કરો</button>
  </form>
  <p class="text-xs opacity-60 mt-3">મોટા ડેટાબેઝ માટે ડાઉનલોડ થોડો સમય લઈ શકે. ડમ્પ 500 રોના બેચમાં લખાય છે.</p>
</div>
<?php admin_foot(); ?>
