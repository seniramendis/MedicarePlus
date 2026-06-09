<?php
// ─────────────────────────────────────────────────────────────
// MedicarePlus – db_connect.php
// Single-instance MySQLi connection using .env or constants
// ─────────────────────────────────────────────────────────────

define('DB_HOST', '127.0.0.1');
define('DB_PORT', 3307);        // XAMPP running on port 3307
define('DB_NAME', 'medicare_plus_db');
define('DB_USER', 'root');
define('DB_PASS', '');

function get_db_connection(): mysqli {
    static $conn = null;
    if ($conn instanceof mysqli && $conn->ping()) {
        return $conn;
    }
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    if ($conn->connect_error) {
        error_log('MedicarePlus DB error: ' . $conn->connect_error);
        http_response_code(503);
        die(json_encode(['error' => 'Database unavailable. Please try again later.']));
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}
