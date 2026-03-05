/**
 * Smart Task Manager – app.js
 * All AJAX communication with the PHP API lives here.
 * Requires jQuery (loaded in dashboard.php via CDN)
 */

// ============================================================
// CONFIG – update if your project lives in a subfolder
// e.g. if URL is localhost/smart-task-manager/ keep as is
// ============================================================
const API_BASE = 'api/';

// Track current filter and the task pending deletion
let currentFilter  = 'all';
let deleteTaskId   = null;

// ============================================================
// DOCUMENT READY
// ============================================================
$(document).ready(function () {
    loadTasks(); // fetch tasks immediately on page load

    // ── Filter buttons ──────────────────────────────────────
    $('.filter-btn').on('click', function () {
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        currentFilter = $(this).data('filter');
        loadTasks();
    });

    // ── Open "Add Task" modal ────────────────────────────────
    $('#btn-open-add-modal').on('click', function () {
        clearAddForm();
        openModal('modal-add');
    });

    // ── Save new task ────────────────────────────────────────
    $('#btn-save-task').on('click', function () {
        const title       = $('#add-title').val().trim();
        const description = $('#add-description').val().trim();
        const priority    = $('#add-priority').val();

        if (!title) {
            showToast('Please enter a task title.', 'error');
            $('#add-title').focus();
            return;
        }

        $(this).prop('disabled', true).text('Saving…');

        $.ajax({
            url    : API_BASE + 'add_task.php',
            method : 'POST',
            data   : { title, description, priority },
            success: function (res) {
                if (res.success) {
                    closeModal('modal-add');
                    showToast('Task added!', 'success');
                    loadTasks();
                } else {
                    showToast(res.message || 'Could not add task.', 'error');
                }
            },
            error  : function () { showToast('Server error. Try again.', 'error'); },
            complete: function () { $('#btn-save-task').prop('disabled', false).text('Save Task'); }
        });
    });

    // ── Update existing task ─────────────────────────────────
    $('#btn-update-task').on('click', function () {
        const id          = $('#edit-task-id').val();
        const title       = $('#edit-title').val().trim();
        const description = $('#edit-description').val().trim();
        const priority    = $('#edit-priority').val();

        if (!title) {
            showToast('Task title cannot be empty.', 'error');
            $('#edit-title').focus();
            return;
        }

        $(this).prop('disabled', true).text('Updating…');

        $.ajax({
            url    : API_BASE + 'update_task.php',
            method : 'POST',
            data   : { id, title, description, priority },
            success: function (res) {
                if (res.success) {
                    closeModal('modal-edit');
                    showToast('Task updated!', 'success');
                    loadTasks();
                } else {
                    showToast(res.message || 'Update failed.', 'error');
                }
            },
            error  : function () { showToast('Server error. Try again.', 'error'); },
            complete: function () { $('#btn-update-task').prop('disabled', false).text('Update Task'); }
        });
    });

    // ── Confirm delete ───────────────────────────────────────
    $('#btn-confirm-delete').on('click', function () {
        if (!deleteTaskId) return;

        $(this).prop('disabled', true).text('Deleting…');

        $.ajax({
            url    : API_BASE + 'delete_task.php',
            method : 'POST',
            data   : { id: deleteTaskId },
            success: function (res) {
                if (res.success) {
                    closeModal('modal-delete');
                    showToast('Task deleted.', 'info');
                    loadTasks();
                } else {
                    showToast(res.message || 'Delete failed.', 'error');
                }
            },
            error  : function () { showToast('Server error. Try again.', 'error'); },
            complete: function () {
                $('#btn-confirm-delete').prop('disabled', false).text('Yes, Delete');
                deleteTaskId = null;
            }
        });
    });

    // ── Close modals (× button or Cancel) ───────────────────
    $(document).on('click', '.modal-close, [data-modal]', function () {
        const target = $(this).data('modal') || $(this).attr('id');
        closeModal(target);
    });

    // ── Close modal when clicking outside the box ────────────
    $(document).on('click', '.modal-overlay', function (e) {
        if ($(e.target).hasClass('modal-overlay')) {
            $(this).hide();
        }
    });

    // ── Keyboard: Enter submits focused modal ────────────────
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') { $('.modal-overlay').hide(); }
    });
});

// ============================================================
// LOAD TASKS via AJAX (GET)
// ============================================================
function loadTasks() {
    $('#loading-spinner').show();
    $('#task-list').empty();
    $('#empty-state').hide();

    const params = {};
    if (currentFilter !== 'all') params.status = currentFilter;

    $.ajax({
        url    : API_BASE + 'get_tasks.php',
        method : 'GET',
        data   : params,
        success: function (res) {
            $('#loading-spinner').hide();

            if (!res.success) {
                showToast(res.message || 'Failed to load tasks.', 'error');
                return;
            }

            const tasks = res.tasks;
            updateStats(tasks);

            if (tasks.length === 0) {
                $('#empty-state').show();
                $('#task-count-label').text('No tasks found.');
                return;
            }

            const label = tasks.length === 1 ? '1 task' : `${tasks.length} tasks`;
            $('#task-count-label').text(label);

            // Render each task card
            tasks.forEach(task => {
                $('#task-list').append(buildTaskCard(task));
            });
        },
        error: function () {
            $('#loading-spinner').hide();
            showToast('Could not reach the server.', 'error');
        }
    });
}

// ============================================================
// BUILD A TASK CARD (returns jQuery element)
// ============================================================
function buildTaskCard(task) {
    const isCompleted = task.status === 'completed';
    const checkClass  = isCompleted ? 'done' : '';
    const checkIcon   = isCompleted ? '✓' : '';
    const cardClass   = isCompleted ? 'task-card completed' : 'task-card';
    const descHtml    = task.description
        ? `<div class="task-desc">${escapeHtml(task.description)}</div>`
        : '';

    return $(`
        <div class="${cardClass}" data-id="${task.id}">
            <button class="task-check ${checkClass}" title="Toggle complete">${checkIcon}</button>
            <div class="task-priority-bar ${task.priority}"></div>
            <div class="task-body">
                <div class="task-title">${escapeHtml(task.title)}</div>
                ${descHtml}
            </div>
            <div class="task-meta">
                <span class="task-badge ${task.priority}">${task.priority}</span>
            </div>
            <div class="task-actions">
                <button class="task-btn edit"   title="Edit">Edit</button>
                <button class="task-btn delete" title="Delete">Delete</button>
            </div>
        </div>
    `)
    .on('click', '.task-check', function () {
        toggleComplete(task.id, isCompleted ? 'pending' : 'completed');
    })
    .on('click', '.task-btn.edit', function () {
        openEditModal(task);
    })
    .on('click', '.task-btn.delete', function () {
        openDeleteModal(task);
    });
}

// ============================================================
// TOGGLE TASK COMPLETE / PENDING
// ============================================================
function toggleComplete(taskId, newStatus) {
    $.ajax({
        url    : API_BASE + 'update_task.php',
        method : 'POST',
        data   : { id: taskId, status: newStatus },
        success: function (res) {
            if (res.success) {
                loadTasks();
            } else {
                showToast(res.message || 'Could not update status.', 'error');
            }
        },
        error: function () { showToast('Server error.', 'error'); }
    });
}

// ============================================================
// EDIT MODAL – pre-fill fields with existing task data
// ============================================================
function openEditModal(task) {
    $('#edit-task-id').val(task.id);
    $('#edit-title').val(task.title);
    $('#edit-description').val(task.description);
    $('#edit-priority').val(task.priority);
    openModal('modal-edit');
}

// ============================================================
// DELETE MODAL
// ============================================================
function openDeleteModal(task) {
    deleteTaskId = task.id;
    $('#delete-task-title').text(task.title);
    openModal('modal-delete');
}

// ============================================================
// UPDATE STATS PANEL
// ============================================================
function updateStats(tasks) {
    const total    = tasks.length;  // note: this reflects current filter
    // For accurate global stats we count from all tasks regardless of filter;
    // these numbers apply to the loaded set.
    const pending   = tasks.filter(t => t.status === 'pending').length;
    const completed = tasks.filter(t => t.status === 'completed').length;

    $('#stat-total').text(total);
    $('#stat-pending').text(pending);
    $('#stat-done').text(completed);
}

// ============================================================
// MODAL HELPERS
// ============================================================
function openModal(id)  { $('#' + id).fadeIn(150); }
function closeModal(id) { $('#' + id).fadeOut(150); }

function clearAddForm() {
    $('#add-title').val('');
    $('#add-description').val('');
    $('#add-priority').val('medium');
}

// ============================================================
// TOAST NOTIFICATION
// ============================================================
function showToast(message, type = 'info') {
    const $toast = $('#toast');
    $toast.text(message)
          .removeClass('success error info')
          .addClass(type)
          .addClass('show');

    setTimeout(() => $toast.removeClass('show'), 3000);
}

// ============================================================
// XSS PREVENTION – escape HTML before inserting into DOM
// ============================================================
function escapeHtml(str) {
    return String(str)
        .replace(/&/g,  '&amp;')
        .replace(/</g,  '&lt;')
        .replace(/>/g,  '&gt;')
        .replace(/"/g,  '&quot;')
        .replace(/'/g,  '&#039;');
}
