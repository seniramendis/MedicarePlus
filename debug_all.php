<?php
// Turn on all error reporting so we can see the hidden error
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Diagnostic Diagnostic Started...</h1>";

// 1. Check if files exist
$files = ['header.php', 'functions.php', 'chat_engine.php', 'db_connect.php'];
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<p style='color:green;'>SUCCESS: $file found.</p>";
    } else {
        echo "<p style='color:red;'>ERROR: $file NOT found!</p>";
    }
}

// 2. Check Database Connection
require_once 'functions.php';
$conn = get_db_connection();
if ($conn) {
    echo "<p style='color:green;'>SUCCESS: Database connected.</p>";

    // 3. Check if messages table exists
    $res = $conn->query("SHOW TABLES LIKE 'messages'");
    if ($res->num_rows > 0) {
        echo "<p style='color:green;'>SUCCESS: 'messages' table found.</p>";
    } else {
        echo "<p style='color:red;'>ERROR: 'messages' table MISSING. Run the SQL query!</p>";
    }
} else {
    echo "<p style='color:red;'>ERROR: Could not connect to DB.</p>";
}
echo "<p><em>If everything here is green, your issue is inside the header.php or chat_engine.php logic.</em></p>";
