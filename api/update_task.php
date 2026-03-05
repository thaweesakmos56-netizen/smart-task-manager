<?php
// ============================================================
// api/update_task.php  – Edit a task OR toggle its status
// Method : POST
// Expects: id, and any of: title, description, priority, status
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

// Confirm this task belongs to the current user (ownership check)
$check = $conn->prepare("SELECT id FROM tasks WHERE id = ? AND user_id = ?");
$check->bind_param('ii', $task_id, $user_id);
$check->execute();
if ($check->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Task not found.']);
    exit;
}
$check->close();

// -------------------------------------------------------
// Determine what we are updating
// -------------------------------------------------------

// Toggle-status shortcut (Mark as complete / pending)
if (isset($_POST['status'])) {
    $status = $_POST['status'] === 'completed' ? 'completed' : 'pending';
    $stmt = $conn->prepare("UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param('sii', $status, $task_id, $user_id);

// Full edit (title, description, priority)
} else {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority    = $_POST['priority'] ?? 'medium';

    if (empty($title)) {
        echo json_encode(['success' => false, 'message' => 'Task title is required.']);
        exit;
    }

    $allowed = ['low', 'medium', 'high'];
    if (!in_array($priority, $allowed)) $priority = 'medium';

    $stmt = $conn->prepare(
        "UPDATE tasks SET title = ?, description = ?, priority = ?
         WHERE id = ? AND user_id = ?"
    );
    $stmt->bind_param('sssii', $title, $description, $priority, $task_id, $user_id);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Task updated.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Update failed.']);
}

$stmt->close();
$conn->close();
?>
