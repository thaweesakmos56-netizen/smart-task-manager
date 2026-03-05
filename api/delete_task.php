<?php
// ============================================================
// api/delete_task.php  – Remove a task permanently
// Method : POST
// Expects: id (task id)
// Returns: JSON success/error
// ============================================================

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../config/db.php';

$user_id = $_SESSION['user_id'];
$task_id = intval($_POST['id'] ?? 0);

if ($task_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid task ID.']);
    exit;
}

// Delete only if the task belongs to this user (prevents deleting other users' tasks)
$stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
$stmt->bind_param('ii', $task_id, $user_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Task deleted.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Task not found.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Delete failed.']);
}

$stmt->close();
$conn->close();
?>
