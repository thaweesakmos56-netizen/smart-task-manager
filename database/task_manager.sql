-- ============================================================
-- Smart Task Manager - Database Setup
-- Import this file into phpMyAdmin or run via MySQL CLI
-- ============================================================

CREATE DATABASE IF NOT EXISTS task_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE task_manager;

-- -------------------------------------------------------
-- Table: users
-- Stores login credentials for each user
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50)  NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,          -- stored as bcrypt hash
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- -------------------------------------------------------
-- Table: tasks
-- Stores tasks belonging to each user
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS tasks (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT          NOT NULL,
    title       VARCHAR(150) NOT NULL,
    description TEXT,
    priority    ENUM('low','medium','high') DEFAULT 'medium',
    status      ENUM('pending','completed')  DEFAULT 'pending',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- -------------------------------------------------------
-- Demo user  (password: demo123)
-- -------------------------------------------------------
INSERT INTO users (username, password) VALUES
('demo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Demo tasks for the demo user (id = 1)
INSERT INTO tasks (user_id, title, description, priority, status) VALUES
(1, 'Set up XAMPP environment',    'Install XAMPP and configure Apache + MySQL',     'high',   'completed'),
(1, 'Build login page',            'Create index.php with session handling',          'high',   'completed'),
(1, 'Design dashboard UI',         'Build responsive dashboard with task list',       'medium', 'pending'),
(1, 'Implement AJAX task CRUD',    'Add / Edit / Delete tasks without page reload',   'high',   'pending'),
(1, 'Write project documentation', 'Document setup steps and API endpoints',          'low',    'pending');
