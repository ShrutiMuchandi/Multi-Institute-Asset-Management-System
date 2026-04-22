<?php
// config/db.php  —  Edit these values before deployment
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'institute_mgmt');
define('DB_PORT', 3306);

define('APP_NAME',    'Institute Management System');
define('APP_VERSION', '1.0');
define('BASE_URL',    'http://localhost/ims');   // no trailing slash
define('TIMEZONE',    'Asia/Kolkata');

date_default_timezone_set(TIMEZONE);
session_name('IMS_SESSION');
session_start();

function db(): mysqli {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        if ($conn->connect_error) {
            die(json_encode(['error' => 'DB connection failed: ' . $conn->connect_error]));
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

// Convenience: run a prepared statement, return result
function query(string $sql, string $types = '', ...$params): mysqli_result|bool {
    $db  = db();
    $stmt = $db->prepare($sql);
    if (!$stmt) die("Prepare error: " . $db->error);
    if ($types && $params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result !== false ? $result : true;
}

function row(string $sql, string $types = '', ...$params): ?array {
    $r = query($sql, $types, ...$params);
    return ($r instanceof mysqli_result) ? ($r->fetch_assoc() ?: null) : null;
}

function rows(string $sql, string $types = '', ...$params): array {
    $r = query($sql, $types, ...$params);
    if (!($r instanceof mysqli_result)) return [];
    $out = [];
    while ($row = $r->fetch_assoc()) $out[] = $row;
    return $out;
}

function insert(string $sql, string $types, ...$params): int {
    $db   = db();
    $stmt = $db->prepare($sql);
    if (!$stmt) die("Prepare error: " . $db->error);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $id = $db->insert_id;
    $stmt->close();
    return $id;
}

function esc(string $val): string {
    return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
}

function money(float $v): string {
    return '₹' . number_format($v, 2);
}

function audit(string $action, string $module, int $recordId = 0, string $details = ''): void {
    if (empty($_SESSION['user_id'])) return;
    insert(
        "INSERT INTO audit_log (inst_id,user_id,action,module,record_id,details,ip_address) VALUES (?,?,?,?,?,?,?)",
        'iissiis',
        (int)($_SESSION['inst_id'] ?? 0),
        (int)$_SESSION['user_id'],
        $action, $module, $recordId, $details,
        $_SERVER['REMOTE_ADDR'] ?? ''
    );
}
