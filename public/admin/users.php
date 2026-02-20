<?php
// public/admin/users.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Notification.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole('admin');

$userModel = new User();
$pageTitle = 'System Users';
$action = $_GET['action'] ?? 'list';
$userId = $_GET['id'] ?? null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['csrf_token'])) {
        validateCSRFToken($_POST['csrf_token']);
    }
    
    if (isset($_POST['create_user'])) {
        $data = [
            'username' => sanitizeInput($_POST['username']),
            'email' => sanitizeInput($_POST['email']),
            'full_name' => sanitizeInput($_POST['full_name']),
            'role' => sanitizeInput($_POST['role']),
            'status' => sanitizeInput($_POST['status']),
            'password' => $_POST['password']
        ];
        
        try {
            $newUserId = $userModel->create($data);
            logAudit($_SESSION['user_id'], 'create_user', 'users', $newUserId, null, $data);
            setFlashMessage('success', 'User created successfully.');
            redirect('users.php');
        } catch (Exception $e) {
            $error = 'Failed to create user: ' . $e->getMessage();
        }
    } elseif (isset($_POST['update_user'])) {
        $data = [
            'email' => sanitizeInput($_POST['email']),
            'full_name' => sanitizeInput($_POST['full_name']),
            'role' => sanitizeInput($_POST['role']),
            'status' => sanitizeInput($_POST['status'])
        ];
        
        if (!empty($_POST['password'])) {
            $data['password'] = $_POST['password'];
        }
        
        try {
            $userModel->update($_POST['user_id'], $data);
            logAudit($_SESSION['user_id'], 'update_user', 'users', $_POST['user_id'], null, $data);
            setFlashMessage('success', 'User updated successfully.');
            redirect('users.php');
        } catch (Exception $e) {
            $error = 'Failed to update user: ' . $e->getMessage();
        }
    } elseif (isset($_POST['delete_user'])) {
        try {
            $userModel->delete($_POST['user_id']);
            logAudit($_SESSION['user_id'], 'delete_user', 'users', $_POST['user_id']);
            setFlashMessage('success', 'User deleted successfully.');
            redirect('users.php');
        } catch (Exception $e) {
            setFlashMessage('danger', 'Failed to delete user: ' . $e->getMessage());
            redirect('users.php');
        }
    }
}

// Get user data for editing
$user = null;
if ($action === 'edit' && $userId) {
    $user = $userModel->getById($userId);
    if (!$user) {
        setFlashMessage('danger', 'User not found.');
        redirect('users.php');
    }
}

// Get all users
$filters = [];
if (!empty($_GET['role'])) {
    $filters['role'] = $_GET['role'];
}
if (!empty($_GET['status'])) {
    $filters['status'] = $_GET['status'];
}
$users = $userModel->getAll($filters);

include __DIR__ . '/../includes/header.php';
?>

<div class="content-area">
    <?php if ($action === 'list'): ?>
        <!-- User List -->
        <div class="data-table-wrapper">
            <div class="table-header">
                <h4 class="table-title">System Users</h4>
                <div class="table-actions">
                    <a href="?action=create" class="btn btn-primary">
                        <span>➕</span> Add New User
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <select class="form-select" onchange="window.location.href='?role=' + this.value">
                        <option value="">All Roles</option>
                        <option value="admin" <?php echo ($_GET['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="officer" <?php echo ($_GET['role'] ?? '') === 'officer' ? 'selected' : ''; ?>>Officer</option>
                        <option value="member" <?php echo ($_GET['role'] ?? '') === 'member' ? 'selected' : ''; ?>>Member</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select class="form-select" onchange="window.location.href='?status=' + this.value">
                        <option value="">All Status</option>
                        <option value="active" <?php echo ($_GET['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($_GET['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        <option value="suspended" <?php echo ($_GET['status'] ?? '') === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                    </select>
                </div>
            </div>

            <?php if (empty($users)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">👤</div>
                    <div class="empty-state-title">No Users Found</div>
                    <div class="empty-state-text">Start by adding your first system user.</div>
                    <a href="?action=create" class="btn btn-primary">Add User</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><strong><?php echo e($u['username']); ?></strong></td>
                                    <td><?php echo e($u['full_name']); ?></td>
                                    <td><?php echo e($u['email']); ?></td>
                                    <td><span class="badge badge-primary"><?php echo ucfirst($u['role']); ?></span></td>
                                    <td><?php echo getStatusBadge($u['status']); ?></td>
                                    <td><?php echo formatDate($u['created_at']); ?></td>
                                    <td>
                                        <a href="?action=edit&id=<?php echo $u['id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                <button type="submit" name="delete_user" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    <?php elseif ($action === 'create' || $action === 'edit'): ?>
        <!-- User Form -->
        <div class="glass-card" style="max-width: 600px;">
            <h4 class="text-primary-blue mb-4"><?php echo $action === 'create' ? 'Create New User' : 'Edit User'; ?></h4>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo e($error); ?></div>
            <?php endif; ?>

            <form method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" 
                           value="<?php echo e($user['username'] ?? ''); ?>" 
                           <?php echo $action === 'edit' ? 'readonly' : 'required'; ?>>
                    <div class="form-text">Username cannot be changed after creation.</div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo e($user['email'] ?? ''); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" 
                           value="<?php echo e($user['full_name'] ?? ''); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" 
                           <?php echo $action === 'create' ? 'required' : ''; ?>>
                    <?php if ($action === 'edit'): ?>
                        <div class="form-text">Leave blank to keep current password.</div>
                    <?php else: ?>
                        <div class="form-text">Minimum 8 characters recommended.</div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="">Select Role</option>
                        <option value="admin" <?php echo ($user['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="officer" <?php echo ($user['role'] ?? '') === 'officer' ? 'selected' : ''; ?>>Officer</option>
                        <option value="member" <?php echo ($user['role'] ?? '') === 'member' ? 'selected' : ''; ?>>Member</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="active" <?php echo ($user['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($user['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        <option value="suspended" <?php echo ($user['status'] ?? '') === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" name="<?php echo $action === 'create' ? 'create_user' : 'update_user'; ?>" class="btn btn-primary">
                        <?php echo $action === 'create' ? 'Create User' : 'Update User'; ?>
                    </button>
                    <a href="users.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>