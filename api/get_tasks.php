<?php
// ============================================================
// api/get_tasks.php  – Fetch all tasks for the logged-in user
// Method : GET
// Returns: JSON array of task objects
// ============================================================

session_start();
header('Content-Type: application/json');

// Security: only allow logged-in users
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../config/db.php';

$user_id = $_SESSION['user_id'];

// Optional filter: ?status=pending or ?status=completed
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Build query – use prepared statement to prevent SQL injection
if ($status_filter === 'pending' || $status_filter === 'completed') {
    $stmt = $conn->prepare(
        "SELECT id, title, description, priority, status, created_at
         FROM tasks
         WHERE user_id = ? AND status = ?
         ORDER BY
           FIELD(priority,'high','medium','low'),
           created_at DESC"
    );
    $stmt->bind_param('is', $user_id, $status_filter);
} else {
    $stmt = $conn->prepare(
        "SELECT id, title, description, priority, status, created_at
         FROM tasks
         WHERE user_id = ?
         ORDER BY
           FIELD(priority,'high','medium','low'),
           created_at DESC"
    );
    $stmt->bind_param('i', $user_id);
}

$stmt->execute();
$result = $stmt->get_result();

$tasks = [];
while ($row = $result->fetch_assoc()) {
    $tasks[] = $row;
}

echo json_encode(['success' => true, 'tasks' => $tasks]);

$stmt->close();
$conn->close();
?>
