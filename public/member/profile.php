<?php
// public/member/profile.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Notification.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole('member');

$pageTitle = 'My Profile';
$user = getCurrentUser();

include __DIR__ . '/../includes/header.php';
?>

<div class="content-area">
    <div class="glass-card" style="max-width: 600px;">
        <h4 class="text-primary-blue mb-4">My Profile</h4>

        <div class="text-center mb-4">
            <div class="user-avatar" style="width: 100px; height: 100px; font-size: 2.5rem; margin: 0 auto;">
                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
            </div>
        </div>

        <table class="table">
            <tr>
                <th width="40%">Full Name</th>
                <td><?php echo e($user['full_name']); ?></td>
            </tr>
            <tr>
                <th>Username</th>
                <td><?php echo e($user['username']); ?></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?php echo e($user['email']); ?></td>
            </tr>
            <tr>
                <th>Role</th>
                <td><span class="badge badge-primary"><?php echo ucfirst($user['role']); ?></span></td>
            </tr>
            <tr>
                <th>Account Status</th>
                <td><?php echo getStatusBadge($user['status']); ?></td>
            </tr>
            <tr>
                <th>Member Since</th>
                <td><?php echo formatDate($user['created_at']); ?></td>
            </tr>
        </table>

        <div class="alert alert-info">
            <strong>Note:</strong> To update your profile information, please contact your administrator.
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
```

