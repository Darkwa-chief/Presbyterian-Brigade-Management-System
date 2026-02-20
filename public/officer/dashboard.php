<?php
// public/officer/dashboard.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Brigader.php';
require_once __DIR__ . '/../../classes/Event.php';
require_once __DIR__ . '/../../classes/Attendance.php';
require_once __DIR__ . '/../../classes/Notification.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['admin', 'officer']);

$db = Database::getInstance();
$brigaderModel = new Brigader();
$eventModel = new Event();
$notificationModel = new Notification();
$pageTitle = 'Officer Dashboard';

// Statistics
$totalBrigaders   = $brigaderModel->count(['status' => 'active']);
$upcomingEvents   = $eventModel->getUpcoming(5);
$recentBrigaders  = $brigaderModel->getAll(['status' => 'active'], 5, 0);
$notifications    = $notificationModel->getUserNotifications(
                        $_SESSION['user_id'], 
                        $_SESSION['user_role']
                    );
$unreadCount      = $notificationModel->getUnreadCount(
                        $_SESSION['user_id'], 
                        $_SESSION['user_role']
                    );

// Recent events
$recentEvents = $eventModel->getAll([], 5, 0);

// Get attendance stats for recent events
$attendanceModel = new Attendance();

include __DIR__ . '/../includes/header.php';
?>

<div class="content-area">
    <div class="mb-4">
        <h2 class="text-primary-blue mb-1">Officer Dashboard</h2>
        <p class="text-muted">
            Welcome back, <?php echo e($_SESSION['full_name']); ?>!
            &nbsp;|&nbsp;
            <?php echo date('l, F j, Y'); ?>
        </p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Active Brigaders</div>
                    <div class="stat-value"><?php echo $totalBrigaders; ?></div>
                    <div class="stat-subtitle">Total active members</div>
                </div>
                <div class="stat-icon">👥</div>
            </div>
        </div>

        <div class="stat-card accent-success">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Upcoming Events</div>
                    <div class="stat-value"><?php echo count($upcomingEvents); ?></div>
                    <div class="stat-subtitle">Scheduled events</div>
                </div>
                <div class="stat-icon">📅</div>
            </div>
        </div>

        <div class="stat-card accent-red">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Notifications</div>
                    <div class="stat-value"><?php echo $unreadCount; ?></div>
                    <div class="stat-subtitle">Unread messages</div>
                </div>
                <div class="stat-icon">🔔</div>
            </div>
        </div>

        <div class="stat-card accent-info">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Recent Events</div>
                    <div class="stat-value"><?php echo count($recentEvents); ?></div>
                    <div class="stat-subtitle">Last recorded events</div>
                </div>
                <div class="stat-icon">📋</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Upcoming Events -->
        <div class="col-lg-6 mb-4">
            <div class="glass-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="text-primary-blue mb-0">Upcoming Events</h5>
                    <a href="<?php echo APP_URL; ?>/officer/events.php" class="btn btn-sm btn-secondary">
                        View All
                    </a>
                </div>

                <?php if (empty($upcomingEvents)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📅</div>
                        <div class="empty-state-title">No Upcoming Events</div>
                        <div class="empty-state-text">No events scheduled at this time.</div>
                        <a href="<?php echo APP_URL; ?>/officer/events.php?action=create" 
                           class="btn btn-primary btn-sm">Create Event</a>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($upcomingEvents as $event): ?>
                            <div class="list-group-item border-0 px-0 py-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 text-dark">
                                            <?php echo e($event['event_name']); ?>
                                        </h6>
                                        <small class="text-muted">
                                            📅 <?php echo formatDate($event['event_date']); ?>
                                            <?php if ($event['event_time']): ?>
                                                at <?php echo date('g:i A', strtotime($event['event_time'])); ?>
                                            <?php endif; ?>
                                            <?php if ($event['location']): ?>
                                                <br>📍 <?php echo e($event['location']); ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <div class="ms-2">
                                        <span class="badge badge-primary mb-1 d-block">
                                            <?php echo ucfirst($event['event_type']); ?>
                                        </span>
                                        <a href="<?php echo APP_URL; ?>/officer/attendance.php?event_id=<?php echo $event['id']; ?>" 
                                           class="btn btn-sm btn-primary">
                                            Attendance
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Brigaders -->
        <div class="col-lg-6 mb-4">
            <div class="glass-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="text-primary-blue mb-0">Recent Brigaders</h5>
                    <a href="<?php echo APP_URL; ?>/officer/brigaders.php" 
                       class="btn btn-sm btn-secondary">View All</a>
                </div>

                <?php if (empty($recentBrigaders)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">👥</div>
                        <div class="empty-state-title">No Brigaders Found</div>
                        <div class="empty-state-text">No brigaders in the system yet.</div>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Brigade ID</th>
                                    <th>Rank</th>
                                    <th>Unit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentBrigaders as $b): ?>
                                    <tr>
                                        <td>
                                            <?php echo e($b['first_name'] . ' ' . $b['last_name']); ?>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo e($b['brigade_id']); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php echo e($b['rank_name'] ?? 'N/A'); ?>
                                        </td>
                                        <td><?php echo e($b['unit']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Notifications -->
    <div class="glass-card mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="text-primary-blue mb-0">
                Recent Notifications
                <?php if ($unreadCount > 0): ?>
                    <span class="badge badge-danger ms-2"><?php echo $unreadCount; ?> new</span>
                <?php endif; ?>
            </h5>
        </div>

        <?php if (empty($notifications)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">🔔</div>
                <div class="empty-state-title">No Notifications</div>
                <div class="empty-state-text">You have no notifications at this time.</div>
            </div>
        <?php else: ?>
            <div class="list-group list-group-flush">
                <?php foreach (array_slice($notifications, 0, 5) as $n): ?>
                    <div class="list-group-item border-0 px-0 
                        <?php echo !$n['is_read'] ? 'bg-light' : ''; ?>">
                        <div class="d-flex justify-content-between">
                            <div>
                                <?php
                                $typeColors = [
                                    'general'   => 'secondary',
                                    'event'     => 'primary',
                                    'promotion' => 'success',
                                    'urgent'    => 'danger'
                                ];
                                $color = $typeColors[$n['notification_type']] ?? 'secondary';
                                ?>
                                <span class="badge badge-<?php echo $color; ?> me-2">
                                    <?php echo ucfirst($n['notification_type']); ?>
                                </span>
                                <strong><?php echo e($n['title']); ?></strong>
                                <?php if (!$n['is_read']): ?>
                                    <span class="badge badge-danger ms-1">New</span>
                                <?php endif; ?>
                                <p class="mb-0 mt-1 text-muted small">
                                    <?php echo e($n['message']); ?>
                                </p>
                            </div>
                            <small class="text-muted text-nowrap ms-3">
                                <?php echo timeAgo($n['created_at']); ?>
                            </small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>