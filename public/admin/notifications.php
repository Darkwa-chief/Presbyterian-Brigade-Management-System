<?php
// public/admin/notifications.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Notification.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole('admin');

$notificationModel = new Notification();
$pageTitle = 'Notifications';
$action = $_GET['action'] ?? 'list';
$notificationId = $_GET['id'] ?? null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['csrf_token'])) {
        validateCSRFToken($_POST['csrf_token']);
    }
    
    if (isset($_POST['create_notification'])) {
        $data = [
            'title' => sanitizeInput($_POST['title']),
            'message' => sanitizeInput($_POST['message']),
            'notification_type' => $_POST['notification_type'],
            'target_role' => $_POST['target_role'],
            'target_unit' => sanitizeInput($_POST['target_unit']) ?: null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'created_by' => $_SESSION['user_id']
        ];
        
        try {
            $emailSent = 0;
            $inAppCreated = false;
            
            // Create in-app notification if checked
            if (isset($_POST['send_in_app'])) {
                $newNotificationId = $notificationModel->create($data);
                logAudit($_SESSION['user_id'], 'create_notification', 'notifications', $newNotificationId, null, $data);
                $inAppCreated = true;
            }
            
            // Send email if checked
            if (isset($_POST['send_email'])) {
                require_once __DIR__ . '/../../classes/Email.php';
                require_once __DIR__ . '/../../classes/Brigader.php';
                
                $emailClass = new Email();
                $brigaderModel = new Brigader();
                $userModel = new User();
                
                // Get recipient emails based on target role
                $recipients = [];
                
                if ($data['target_role'] === 'all') {
                    // Get all users
                    $users = $userModel->getAll(['status' => 'active']);
                    foreach ($users as $u) {
                        if (!empty($u['email']) && filter_var($u['email'], FILTER_VALIDATE_EMAIL)) {
                            $recipients[] = $u['email'];
                        }
                    }
                    
                    // Get all brigaders
                    $brigaders = $brigaderModel->getAll(['status' => 'active']);
                    foreach ($brigaders as $b) {
                        if (!empty($b['email']) && filter_var($b['email'], FILTER_VALIDATE_EMAIL)) {
                            $recipients[] = $b['email'];
                        }
                    }
                } elseif ($data['target_role'] === 'member') {
                    // Get brigader emails
                    $brigaders = $brigaderModel->getAll(['status' => 'active']);
                    foreach ($brigaders as $b) {
                        if (!empty($b['email']) && filter_var($b['email'], FILTER_VALIDATE_EMAIL)) {
                            $recipients[] = $b['email'];
                        }
                    }
                } else {
                    // Get users with specific role (admin/officer)
                    $users = $userModel->getAll(['role' => $data['target_role'], 'status' => 'active']);
                    foreach ($users as $u) {
                        if (!empty($u['email']) && filter_var($u['email'], FILTER_VALIDATE_EMAIL)) {
                            $recipients[] = $u['email'];
                        }
                    }
                }
                
                // Remove duplicates
                $recipients = array_unique($recipients);
                
                if (!empty($recipients)) {
                    $emailSent = $emailClass->sendCustomNotification(
                        $data['title'],
                        $data['message'],
                        $recipients
                    );
                }
            }
            
            // Set appropriate success message
            if ($inAppCreated && $emailSent > 0) {
                setFlashMessage('success', 
                    "Notification created and sent to {$emailSent} email recipient(s).");
            } elseif ($inAppCreated) {
                setFlashMessage('success', 'Notification created successfully.');
            } elseif ($emailSent > 0) {
                setFlashMessage('success', 
                    "Email sent to {$emailSent} recipient(s).");
            } else {
                setFlashMessage('warning', 
                    'Notification processed but no recipients found with valid email addresses.');
            }
            
            redirect('notifications.php');
            
        } catch (Exception $e) {
            $error = 'Failed to create notification: ' . $e->getMessage();
        }
        
    } elseif (isset($_POST['update_notification'])) {
        $data = [
            'title' => sanitizeInput($_POST['title']),
            'message' => sanitizeInput($_POST['message']),
            'notification_type' => $_POST['notification_type'],
            'target_role' => $_POST['target_role'],
            'target_unit' => sanitizeInput($_POST['target_unit']) ?: null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        try {
            $notificationModel->update($_POST['notification_id'], $data);
            logAudit($_SESSION['user_id'], 'update_notification', 'notifications', $_POST['notification_id'], null, $data);
            setFlashMessage('success', 'Notification updated successfully.');
            redirect('notifications.php');
        } catch (Exception $e) {
            $error = 'Failed to update notification: ' . $e->getMessage();
        }
        
    } elseif (isset($_POST['delete_notification'])) {
        try {
            $notificationModel->delete($_POST['notification_id']);
            logAudit($_SESSION['user_id'], 'delete_notification', 'notifications', $_POST['notification_id']);
            setFlashMessage('success', 'Notification deleted successfully.');
            redirect('notifications.php');
        } catch (Exception $e) {
            setFlashMessage('danger', 'Failed to delete notification: ' . $e->getMessage());
            redirect('notifications.php');
        }
    }
}

// Get notification data for editing
$notification = null;
if (($action === 'edit' || $action === 'view') && $notificationId) {
    $notification = $notificationModel->getById($notificationId);
    if (!$notification) {
        setFlashMessage('danger', 'Notification not found.');
        redirect('notifications.php');
    }
}

// Get all notifications with filters
$filters = [];
if (!empty($_GET['notification_type'])) {
    $filters['notification_type'] = $_GET['notification_type'];
}
if (isset($_GET['is_active'])) {
    $filters['is_active'] = $_GET['is_active'];
}

$notifications = $notificationModel->getAll($filters);

include __DIR__ . '/../includes/header.php';
?>

<div class="content-area">
    <?php if ($action === 'list'): ?>
        <!-- Notifications List -->
        <div class="data-table-wrapper">
            <div class="table-header">
                <h4 class="table-title">System Notifications</h4>
                <div class="table-actions">
                    <a href="?action=create" class="btn btn-primary">
                        <span>➕</span> Create Notification
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <form method="GET" class="row g-3 mb-3">
                <div class="col-md-4">
                    <select class="form-select" name="notification_type">
                        <option value="">All Types</option>
                        <option value="general" <?php echo ($_GET['notification_type'] ?? '') === 'general' ? 'selected' : ''; ?>>General</option>
                        <option value="event" <?php echo ($_GET['notification_type'] ?? '') === 'event' ? 'selected' : ''; ?>>Event</option>
                        <option value="promotion" <?php echo ($_GET['promotion_type'] ?? '') === 'promotion' ? 'selected' : ''; ?>>Promotion</option>
                        <option value="urgent" <?php echo ($_GET['notification_type'] ?? '') === 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select class="form-select" name="is_active">
                        <option value="">All Status</option>
                        <option value="1" <?php echo ($_GET['is_active'] ?? '') === '1' ? 'selected' : ''; ?>>Active</option>
                        <option value="0" <?php echo ($_GET['is_active'] ?? '') === '0' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="notifications.php" class="btn btn-secondary">Clear</a>
                </div>
            </form>

            <?php if (empty($notifications)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">🔔</div>
                    <div class="empty-state-title">No Notifications</div>
                    <div class="empty-state-text">Create your first notification to communicate with users.</div>
                    <a href="?action=create" class="btn btn-primary">Create Notification</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Target</th>
                                <th>Created By</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($notifications as $n): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo e($n['title']); ?></strong>
                                        <br><small class="text-muted"><?php echo e(substr($n['message'], 0, 60)) . (strlen($n['message']) > 60 ? '...' : ''); ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $typeColors = [
                                            'general' => 'secondary',
                                            'event' => 'primary',
                                            'promotion' => 'success',
                                            'urgent' => 'danger'
                                        ];
                                        $badgeColor = $typeColors[$n['notification_type']] ?? 'secondary';
                                        ?>
                                        <span class="badge badge-<?php echo $badgeColor; ?>"><?php echo ucfirst($n['notification_type']); ?></span>
                                    </td>
                                    <td>
                                        <?php echo ucfirst($n['target_role']); ?>
                                        <?php if ($n['target_unit']): ?>
                                            <br><small class="text-muted">Unit: <?php echo e($n['target_unit']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($n['created_by_name'] ?? 'System'); ?></td>
                                    <td><?php echo timeAgo($n['created_at']); ?></td>
                                    <td>
                                        <?php if ($n['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="?action=view&id=<?php echo $n['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                        <a href="?action=edit&id=<?php echo $n['id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this notification?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                            <input type="hidden" name="notification_id" value="<?php echo $n['id']; ?>">
                                            <button type="submit" name="delete_notification" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    <?php elseif ($action === 'create' || $action === 'edit'): ?>
        <!-- Notification Form -->
        <div class="glass-card" style="max-width: 700px;">
            <h4 class="text-primary-blue mb-4">
                <?php echo $action === 'create' ? 'Create New Notification' : 'Edit Notification'; ?>
            </h4>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo e($error); ?></div>
            <?php endif; ?>

            <form method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" class="form-control" id="title" name="title" 
                           value="<?php echo e($notification['title'] ?? ''); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="message" class="form-label">Message</label>
                    <textarea class="form-control" id="message" name="message" rows="5" required><?php echo e($notification['message'] ?? ''); ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="notification_type" class="form-label">Notification Type</label>
                        <select class="form-select" id="notification_type" name="notification_type" required>
                            <option value="general" <?php echo ($notification['notification_type'] ?? 'general') === 'general' ? 'selected' : ''; ?>>General</option>
                            <option value="event" <?php echo ($notification['notification_type'] ?? '') === 'event' ? 'selected' : ''; ?>>Event</option>
                            <option value="promotion" <?php echo ($notification['notification_type'] ?? '') === 'promotion' ? 'selected' : ''; ?>>Promotion</option>
                            <option value="urgent" <?php echo ($notification['notification_type'] ?? '') === 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="target_role" class="form-label">Target Audience</label>
                        <select class="form-select" id="target_role" name="target_role" required>
                            <option value="all" <?php echo ($notification['target_role'] ?? 'all') === 'all' ? 'selected' : ''; ?>>All Users</option>
                            <option value="admin" <?php echo ($notification['target_role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admins Only</option>
                            <option value="officer" <?php echo ($notification['target_role'] ?? '') === 'officer' ? 'selected' : ''; ?>>Officers Only</option>
                            <option value="member" <?php echo ($notification['target_role'] ?? '') === 'member' ? 'selected' : ''; ?>>Members Only</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="target_unit" class="form-label">Specific Unit (Optional)</label>
                    <input type="text" class="form-control" id="target_unit" name="target_unit" 
                           value="<?php echo e($notification['target_unit'] ?? ''); ?>" 
                           placeholder="Leave blank for all units">
                    <div class="form-text">Specify a unit name to target only that unit.</div>
                </div>

                <?php if ($action === 'create'): ?>
                    <!-- Delivery Methods (only show when creating) -->
                    <div class="mb-3">
                        <label class="form-label">Delivery Methods</label>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" 
                                   name="send_in_app" id="send_in_app" checked>
                            <label class="form-check-label" for="send_in_app">
                                💬 In-App Notification (shows in system)
                            </label>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" 
                                   name="send_email" id="send_email">
                            <label class="form-check-label" for="send_email">
                                📧 Send Email (to all recipients)
                            </label>
                        </div>
                        
                        <div class="form-text">
                            Select how you want to deliver this notification. 
                            Email requires recipients to have valid email addresses.
                        </div>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                               <?php echo ($notification['is_active'] ?? 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_active">
                            Active (visible to users)
                        </label>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" name="<?php echo $action === 'create' ? 'create_notification' : 'update_notification'; ?>" class="btn btn-primary">
                        <?php echo $action === 'create' ? 'Create & Send Notification' : 'Update Notification'; ?>
                    </button>
                    <a href="notifications.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>

    <?php elseif ($action === 'view'): ?>
        <!-- View Notification -->
        <div class="glass-card" style="max-width: 700px;">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div class="flex-grow-1">
                    <h4 class="text-primary-blue mb-2"><?php echo e($notification['title']); ?></h4>
                    <div class="mb-3">
                        <?php
                        $typeColors = [
                            'general' => 'secondary',
                            'event' => 'primary',
                            'promotion' => 'success',
                            'urgent' => 'danger'
                        ];
                        $badgeColor = $typeColors[$notification['notification_type']] ?? 'secondary';
                        ?>
                        <span class="badge badge-<?php echo $badgeColor; ?>"><?php echo ucfirst($notification['notification_type']); ?></span>
                        <?php if ($notification['is_active']): ?>
                            <span class="badge badge-success">Active</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">Inactive</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div>
                    <a href="?action=edit&id=<?php echo $notification['id']; ?>" class="btn btn-secondary">Edit</a>
                    <a href="notifications.php" class="btn btn-primary">Back</a>
                </div>
            </div>

            <div class="alert alert-info">
                <strong>Target Audience:</strong> <?php echo ucfirst($notification['target_role']); ?>
                <?php if ($notification['target_unit']): ?>
                    <br><strong>Specific Unit:</strong> <?php echo e($notification['target_unit']); ?>
                <?php endif; ?>
            </div>

            <div class="mb-4" style="line-height: 1.8; font-size: 1.05rem; white-space: pre-wrap;">
                <?php echo e($notification['message']); ?>
            </div>

            <hr>

            <div class="text-muted">
                <small>
                    <strong>Created by:</strong> <?php echo e($notification['created_by_name'] ?? 'System'); ?><br>
                    <strong>Created:</strong> <?php echo formatDateTime($notification['created_at']); ?>
                </small>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>