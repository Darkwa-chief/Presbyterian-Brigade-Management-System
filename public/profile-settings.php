<?php
// public/profile-settings.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Notification.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin(); // Any logged-in user can access their profile

$userModel = new User();
$pageTitle = 'Profile Settings';
$user = getCurrentUser();
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['csrf_token'])) {
        validateCSRFToken($_POST['csrf_token']);
    }
    
    if (isset($_POST['update_profile'])) {
        $data = [
            'full_name' => sanitizeInput($_POST['full_name']),
            'email' => sanitizeInput($_POST['email'])
        ];
        
        // Validate email
        if (!validateEmail($data['email'])) {
            $error = 'Invalid email address.';
        } else {
            try {
                $userModel->update($_SESSION['user_id'], $data);
                
                // Update session data
                $_SESSION['full_name'] = $data['full_name'];
                $_SESSION['email'] = $data['email'];
                
                logAudit($_SESSION['user_id'], 'update_profile', 'users', $_SESSION['user_id'], null, $data);
                $success = true;
                
                // Reload user data
                $user = getCurrentUser();
            } catch (Exception $e) {
                $error = 'Failed to update profile: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['change_password'])) {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validation
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = 'All password fields are required.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New passwords do not match.';
        } elseif (strlen($newPassword) < 8) {
            $error = 'New password must be at least 8 characters long.';
        } else {
            // Verify current password
            if (!password_verify($currentPassword, $user['password'])) {
                $error = 'Current password is incorrect.';
            } else {
                try {
                    $userModel->changePassword($_SESSION['user_id'], $newPassword);
                    logAudit($_SESSION['user_id'], 'change_password', 'users', $_SESSION['user_id']);
                    setFlashMessage('success', 'Password changed successfully!');
                    redirect('profile-settings.php');
                } catch (Exception $e) {
                    $error = 'Failed to change password: ' . $e->getMessage();
                }
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="content-area">
    <div class="row">
        <!-- Profile Information -->
        <div class="col-lg-4 mb-4">
            <div class="glass-card text-center">
                <div class="user-avatar" style="width: 120px; height: 120px; font-size: 3rem; margin: 0 auto 1rem;">
                    <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                </div>
                
                <h4 class="text-primary-blue mb-1"><?php echo e($user['full_name']); ?></h4>
                <p class="text-muted mb-2"><?php echo e($user['email']); ?></p>
                <span class="badge badge-primary"><?php echo ucfirst($user['role']); ?></span>
                
                <hr class="my-3">
                
                <div class="text-start">
                    <p class="mb-2">
                        <strong>Username:</strong><br>
                        <span class="text-muted"><?php echo e($user['username']); ?></span>
                    </p>
                    <p class="mb-2">
                        <strong>Account Status:</strong><br>
                        <?php echo getStatusBadge($user['status']); ?>
                    </p>
                    <p class="mb-2">
                        <strong>Member Since:</strong><br>
                        <span class="text-muted"><?php echo formatDate($user['created_at'], 'M d, Y'); ?></span>
                    </p>
                    <p class="mb-0">
                        <strong>Last Updated:</strong><br>
                        <span class="text-muted"><?php echo formatDateTime($user['updated_at']); ?></span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Settings Forms -->
        <div class="col-lg-8">
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <strong>Success!</strong> Your profile has been updated.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo e($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Update Profile Information -->
            <div class="glass-card mb-4">
                <h5 class="text-primary-blue mb-3">Profile Information</h5>
                
                <form method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" value="<?php echo e($user['username']); ?>" disabled>
                        <div class="form-text">Username cannot be changed.</div>
                    </div>

                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" 
                               value="<?php echo e($user['full_name']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo e($user['email']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <input type="text" class="form-control" id="role" value="<?php echo ucfirst($user['role']); ?>" disabled>
                        <div class="form-text">Contact an administrator to change your role.</div>
                    </div>

                    <button type="submit" name="update_profile" class="btn btn-primary">
                        💾 Save Changes
                    </button>
                </form>
            </div>

            <!-- Change Password -->
            <div class="glass-card">
                <h5 class="text-primary-blue mb-3">Change Password</h5>
                
                <form method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8">
                            <div class="form-text">Minimum 8 characters.</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <strong>Password Tips:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Use at least 8 characters</li>
                            <li>Mix uppercase and lowercase letters</li>
                            <li>Include numbers and special characters</li>
                            <li>Avoid common words or phrases</li>
                        </ul>
                    </div>

                    <button type="submit" name="change_password" class="btn btn-primary">
                        🔒 Change Password
                    </button>
                </form>
            </div>

            <!-- Account Information (Read-Only) -->
            <div class="glass-card mt-4">
                <h5 class="text-primary-blue mb-3">Account Information</h5>
                
                <div class="table-responsive">
                    <table class="table">
                        <tbody>
                            <tr>
                                <th width="40%">User ID</th>
                                <td><?php echo $user['id']; ?></td>
                            </tr>
                            <tr>
                                <th>Account Created</th>
                                <td><?php echo formatDateTime($user['created_at']); ?></td>
                            </tr>
                            <tr>
                                <th>Last Login</th>
                                <td>
                                    <?php if (isset($_SESSION['login_time'])): ?>
                                        <?php echo date('M d, Y h:i A', $_SESSION['login_time']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Account Status</th>
                                <td><?php echo getStatusBadge($user['status']); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <?php if ($user['status'] !== 'active'): ?>
                    <div class="alert alert-warning mb-0">
                        <strong>Notice:</strong> Your account status is "<?php echo $user['status']; ?>". 
                        Please contact an administrator if you believe this is an error.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Password match validation
document.getElementById('confirm_password').addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = this.value;
    
    if (newPassword !== confirmPassword) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>