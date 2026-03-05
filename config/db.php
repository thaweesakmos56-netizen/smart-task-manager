<?php
// ============================================================
// config/db.php  – Database connection
// ============================================================
// Change these values to match your XAMPP setup.
// Default XAMPP credentials: root / (no password)
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');           // leave empty for default XAMPP
define('DB_NAME', 'smart_task_db');
// Create a MySQLi connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Stop and show error if connection fails
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]));
}

// Use UTF-8 for all queries
$conn->set_charset('utf8mb4');
?>
