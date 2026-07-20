<?php
/**
 * KRISHNA TOOLS — PDO database layer.
 *
 * Provides a single shared PDO connection and small helpers that keep every
 * query a prepared statement. Table names are prefix-aware via tbl().
 */

if (!defined('KT_ROOT')) {
    // Bootstrap config if not already loaded (e.g. direct include).
    $cfg = dirname(__DIR__) . '/config/config.php';
    if (is_file($cfg)) {
        require_once $cfg;
    } else {
        // No config yet — the installer handles its own connection.
        return;
    }
}

/**
 * Return the shared PDO handle, connecting on first use.
 */
function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        // Never leak credentials; log and show a friendly message.
        if (defined('KT_LOGS')) {
            @error_log('[' . date('c') . '] DB connect failed: ' . $e->getMessage() . "\n", 3, KT_LOGS . '/error.log');
        }
        http_response_code(500);
        exit('ડેટાબેઝ કનેક્શન નિષ્ફળ. કૃપા કરી પછીથી પ્રયત્ન કરો. / Database connection failed.');
    }
    return $pdo;
}

/** Prefix a bare table name, e.g. tbl('users') => 'kt_users'. */
function tbl(string $name): string {
    return DB_PREFIX . $name;
}

/** Run a prepared query and return the statement. */
function q(string $sql, array $params = []): PDOStatement {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/** Fetch a single row (or null). */
function one(string $sql, array $params = []): ?array {
    $row = q($sql, $params)->fetch();
    return $row === false ? null : $row;
}

/** Fetch all rows. */
function all(string $sql, array $params = []): array {
    return q($sql, $params)->fetchAll();
}

/** Fetch a single scalar value (or null). */
function scalar(string $sql, array $params = []) {
    $v = q($sql, $params)->fetchColumn();
    return $v === false ? null : $v;
}

/** Insert an associative array into a (prefixed) table; return last insert id. */
function insert(string $table, array $data): int {
    $cols = array_keys($data);
    $ph   = array_map(fn($c) => ':' . $c, $cols);
    $sql  = 'INSERT INTO ' . tbl($table) . ' (' . implode(',', $cols) . ') VALUES (' . implode(',', $ph) . ')';
    $params = [];
    foreach ($data as $k => $v) $params[':' . $k] = $v;
    q($sql, $params);
    return (int) db()->lastInsertId();
}

/** Update rows in a (prefixed) table. $where is an assoc array ANDed together. */
function update(string $table, array $data, array $where): int {
    $set = implode(',', array_map(fn($c) => "$c=:s_$c", array_keys($data)));
    $cond = implode(' AND ', array_map(fn($c) => "$c=:w_$c", array_keys($where)));
    $params = [];
    foreach ($data as $k => $v)  $params[":s_$k"] = $v;
    foreach ($where as $k => $v) $params[":w_$k"] = $v;
    return q("UPDATE " . tbl($table) . " SET $set WHERE $cond", $params)->rowCount();
}
