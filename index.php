<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config/db.php';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($username) || empty($password)) {
        $error = 'กรุณากรอก username และ password';
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($user && ($password === $user['password'] || password_verify($password, $user['password']))) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login</title>
<link rel="stylesheet" href="assets/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body class="auth-page">
<div class="auth-wrapper">
    <div class="auth-brand">
        <div class="brand-content">
            <div class="brand-icon">✦</div>
            <h1 class="brand-title">Smart<br>Task<br>Manager</h1>
            <p class="brand-sub">Organize your work.<br>Ship what matters.</p>
            <div class="brand-dots"><span></span><span></span><span></span></div>
        </div>
    </div>
    <div class="auth-form-panel">
        <div class="auth-form-inner">
            <h2 class="form-heading">Welcome back</h2>
            <p class="form-subheading">Sign in to your workspace</p>
            <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST" action="index.php">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Sign In →</button>
            </form>
            <p class="demo-hint">🔑 Login: <strong>admin</strong> / <strong>1234</strong></p>
        </div>
    </div>
</div>
</body>
</html>