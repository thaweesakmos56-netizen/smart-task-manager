<?php
// ============================================================
// dashboard.php  – Main task management page
// ============================================================
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$username = htmlspecialchars($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – Smart Task Manager</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <!-- jQuery from CDN -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="dashboard-page">

    <!-- ===== TOP NAVIGATION ===== -->
    <nav class="navbar">
        <div class="nav-brand">
            <span class="nav-icon">✦</span>
            Smart Task Manager
        </div>
        <div class="nav-right">
            <span class="nav-user">👤 <?= $username ?></span>
            <a href="logout.php" class="btn btn-logout">Logout</a>
        </div>
    </nav>

    <!-- ===== MAIN LAYOUT ===== -->
    <div class="dashboard-container">

        <!-- ---- Sidebar ---- -->
        <aside class="sidebar">
            <div class="sidebar-section">
                <p class="sidebar-label">Filters</p>
                <button class="filter-btn active" data-filter="all">All Tasks</button>
                <button class="filter-btn" data-filter="pending">Pending</button>
                <button class="filter-btn" data-filter="completed">Completed</button>
            </div>

            <div class="sidebar-section">
                <p class="sidebar-label">Priority</p>
                <span class="priority-badge high">● High</span>
                <span class="priority-badge medium">● Medium</span>
                <span class="priority-badge low">● Low</span>
            </div>

            <!-- Stats block – updated via JS -->
            <div class="stats-block">
                <div class="stat-item">
                    <span class="stat-num" id="stat-total">0</span>
                    <span class="stat-label">Total</span>
                </div>
                <div class="stat-item">
                    <span class="stat-num" id="stat-pending">0</span>
                    <span class="stat-label">Pending</span>
                </div>
                <div class="stat-item">
                    <span class="stat-num" id="stat-done">0</span>
                    <span class="stat-label">Done</span>
                </div>
            </div>
        </aside>

        <!-- ---- Main content ---- -->
        <main class="main-content">

            <!-- Page header + Add button -->
            <div class="content-header">
                <div>
                    <h2 class="page-title">My Tasks</h2>
                    <p class="page-sub" id="task-count-label">Loading…</p>
                </div>
                <button class="btn btn-primary" id="btn-open-add-modal">
                    + Add Task
                </button>
            </div>

            <!-- Loading spinner -->
            <div id="loading-spinner" class="spinner-wrap">
                <div class="spinner"></div>
            </div>

            <!-- Task list injected here by app.js -->
            <div id="task-list" class="task-list"></div>

            <!-- Empty state -->
            <div id="empty-state" class="empty-state" style="display:none;">
                <div class="empty-icon">📋</div>
                <h3>No tasks yet</h3>
                <p>Click <strong>+ Add Task</strong> to get started.</p>
            </div>

        </main>
    </div>

    <!-- ===== ADD TASK MODAL ===== -->
    <div id="modal-add" class="modal-overlay" style="display:none;">
        <div class="modal-box">
            <div class="modal-header">
                <h3>New Task</h3>
                <button class="modal-close" data-modal="modal-add">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Title <span class="required">*</span></label>
                    <input type="text" id="add-title" placeholder="What needs to be done?">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea id="add-description" rows="3" placeholder="Optional details…"></textarea>
                </div>
                <div class="form-group">
                    <label>Priority</label>
                    <select id="add-priority">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-ghost" data-modal="modal-add">Cancel</button>
                <button class="btn btn-primary" id="btn-save-task">Save Task</button>
            </div>
        </div>
    </div>

    <!-- ===== EDIT TASK MODAL ===== -->
    <div id="modal-edit" class="modal-overlay" style="display:none;">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Edit Task</h3>
                <button class="modal-close" data-modal="modal-edit">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit-task-id">
                <div class="form-group">
                    <label>Title <span class="required">*</span></label>
                    <input type="text" id="edit-title" placeholder="Task title">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea id="edit-description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Priority</label>
                    <select id="edit-priority">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-ghost" data-modal="modal-edit">Cancel</button>
                <button class="btn btn-primary" id="btn-update-task">Update Task</button>
            </div>
        </div>
    </div>

    <!-- ===== DELETE CONFIRM MODAL ===== -->
    <div id="modal-delete" class="modal-overlay" style="display:none;">
        <div class="modal-box modal-box--sm">
            <div class="modal-header">
                <h3>Delete Task</h3>
                <button class="modal-close" data-modal="modal-delete">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="delete-task-title"></strong>? This cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-ghost" data-modal="modal-delete">Cancel</button>
                <button class="btn btn-danger" id="btn-confirm-delete">Yes, Delete</button>
            </div>
        </div>
    </div>

    <!-- Toast notification -->
    <div id="toast" class="toast"></div>

    <!-- Main JS -->
    <script src="assets/js/app.js"></script>
</body>
</html>
