```php
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
     <style>
        .pw-wrap { position: relative; }
        .pw-wrap .form-control { padding-right: 2.5rem; }
        .pw-wrap button {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #6c757d;
            padding: 0;
            line-height: 1;
        }
        .pw-wrap button:hover { color: #0d2a52; }
    </style>
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
                    <div class="pw-wrap">
                        <input type="password" class="form-control" id="password" name="password" required>
                        <button type="button" id="pwBtn" title="Show / hide password">
                            <svg id="eyeOn" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg id="eyeOff" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
            
            <div class="text-center mt-4">
                <small class="text-muted">© <?php echo date('Y'); ?> Presbyterian Brigade. All rights reserved.</small>
            </div>
        </div>
    </div>
        <script>
        document.getElementById('pwBtn').addEventListener('click', function() {
            var p = document.getElementById('password');
            var on = document.getElementById('eyeOn');
            var off = document.getElementById('eyeOff');
            if (p.type === 'password') {
                p.type = 'text';
                on.style.display = 'none';
                off.style.display = 'inline';
            } else {
                p.type = 'password';
                on.style.display = 'inline';
                off.style.display = 'none';
            }
        });
    </script>
</body>
</body>
</html>