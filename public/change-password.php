<?php
// public/change-password.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Notification.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin(); // Any logged-in user can change their password

$userModel = new User();
$pageTitle = 'Change Password';
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['csrf_token'])) {
        validateCSRFToken($_POST['csrf_token']);
    }
    
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = 'All fields are required.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'New passwords do not match.';
    } elseif (strlen($newPassword) < 8) {
        $error = 'New password must be at least 8 characters long.';
    } else {
        // Verify current password
        $user = $userModel->getById($_SESSION['user_id']);
        
        if (!password_verify($currentPassword, $user['password'])) {
            $error = 'Current password is incorrect.';
        } else {
            // Update password
            try {
                $userModel->changePassword($_SESSION['user_id'], $newPassword);
                logAudit($_SESSION['user_id'], 'change_password', 'users', $_SESSION['user_id']);
                $success = true;
            } catch (Exception $e) {
                $error = 'Failed to update password: ' . $e->getMessage();
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="content-area">
    <div class="glass-card" style="max-width: 600px; margin: 0 auto;">
        <h4 class="text-primary-blue mb-4">Change Password</h4>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <strong>Success!</strong> Your password has been changed successfully.
                <br><a href="<?php echo APP_URL; ?>/index.php">Return to Dashboard</a>
            </div>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo e($error); ?></div>
            <?php endif; ?>

            <form method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                <div class="mb-3">
                    <label for="current_password" class="form-label">Current Password</label>
                    <input type="password" class="form-control" id="current_password" name="current_password" required autofocus>
                </div>

                <div class="mb-3">
                    <label for="new_password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8">
                    <div class="form-text">Minimum 8 characters. Use a mix of letters, numbers, and symbols.</div>
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Change Password</button>
                    <a href="<?php echo APP_URL; ?>/index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>