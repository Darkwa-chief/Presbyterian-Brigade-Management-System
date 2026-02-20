<?php
// public/login.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/middleware.php';

startSession();

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $userModel = new User();
        $user = $userModel->authenticate($username, $password);
        
        if ($user) {
            login($user);
            header('Location: index.php');
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card glass-panel">
            <!-- Logo Placeholder -->
            <div class="login-logo text-center mb-4">
                <!-- REPLACE THIS IMAGE: Upload your church logo to public/assets/images/logo-placeholder.png -->
                <!-- Recommended size: 200x200px PNG with transparent background -->
                <img src="assets/images/logo-placeholder.png" alt="Presbyterian Brigade Logo" class="logo-image" onerror="this.style.display='none'">
                <h1 class="app-title mt-3">Presbyterian Brigade</h1>
                <p class="app-subtitle">Management System</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo e($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required autofocus>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
            
            <div class="text-center mt-4">
                <small class="text-muted">© <?php echo date('Y'); ?> Presbyterian Brigade. All rights reserved.</small>
            </div>
        </div>
    </div>
</body>
</html>