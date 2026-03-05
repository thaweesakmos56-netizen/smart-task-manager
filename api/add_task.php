<?php
// ============================================================
// api/add_task.php  – Insert a new task
// Method : POST
// Expects: title, description (optional), priority
// Returns: JSON with new task id
// ============================================================

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../config/db.php';

// Read and sanitize inputs
$user_id     = $_SESSION['user_id'];
$title       = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$priority    = $_POST['priority'] ?? 'medium';

// Basic validation
if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Task title is required.']);
    exit;
}

// Whitelist the priority value
$allowed_priorities = ['low', 'medium', 'high'];
if (!in_array($priority, $allowed_priorities)) {
    $priority = 'medium';
}

// Insert into database
$stmt = $conn->prepare(
    "INSERT INTO tasks (user_id, title, description, priority)
     VALUES (?, ?, ?, ?)"
);
$stmt->bind_param('isss', $user_id, $title, $description, $priority);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Task added successfully.',
        'task_id' => $stmt->insert_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add task.']);
}

$stmt->close();
$conn->close();
?>
