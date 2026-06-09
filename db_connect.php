<?php
// db_connect.php — MedicarePlus
// Port 3307 — your XAMPP MySQL runs on 3307 (confirmed from error log)

define('DB_HOST', '127.0.0.1');
define('DB_PORT', 3307);
define('DB_NAME', 'medicare_plus_db');
define('DB_USER', 'root');
define('DB_PASS', '');

function get_db_connection(): mysqli {
    static $conn = null;
    if ($conn instanceof mysqli && $conn->ping()) {
        return $conn;
    }
    mysqli_report(MYSQLI_REPORT_OFF); // prevent fatal exception, handle manually
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    if ($conn->connect_error) {
        error_log('MedicarePlus DB error: ' . $conn->connect_error);
        http_response_code(503);
        die('<div style="font-family:sans-serif;padding:40px;max-width:600px;margin:60px auto;border:1px solid #e0e0e0;border-radius:12px">
            <h2 style="color:#c0392b">&#9888; Database Connection Failed</h2>
            <p>Could not connect to <strong>' . DB_NAME . '</strong> on port <strong>' . DB_PORT . '</strong>.</p>
            <p><strong>To fix this:</strong></p>
            <ol style="line-height:2">
              <li>Open <strong>XAMPP Control Panel</strong> and make sure <strong>MySQL is running</strong></li>
              <li>Open <strong>phpMyAdmin</strong> (<a href="http://localhost/phpmyadmin">localhost/phpmyadmin</a>)</li>
              <li>Create database named <strong>medicare_plus_db</strong></li>
              <li>Import the <strong>database.sql</strong> file from the MedicarePlus folder</li>
            </ol>
            <p style="color:#888;font-size:.85rem">Error: ' . htmlspecialchars($conn->connect_error) . '</p>
        </div>');
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

// Global $conn for files that use it directly
$conn = get_db_connection();
