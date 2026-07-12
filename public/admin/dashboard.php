<?php
// public/admin/dashboard.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Brigader.php';
require_once __DIR__ . '/../../classes/Event.php';
require_once __DIR__ . '/../../classes/Notification.php';
require_once __DIR__ . '/../../classes/Recital.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole('admin');

$db                = Database::getInstance();
$brigaderModel     = new Brigader();
$eventModel        = new Event();
$notificationModel = new Notification();
$recitalModel      = new Recital();
$userModel         = new User();

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['csrf_token'])) {
        validateCSRFToken($_POST['csrf_token']);
    }

    if (isset($_POST['delete_log'])) {
        $db->delete('audit_logs', 'id = :id', ['id' => intval($_POST['log_id'])]);
        setFlashMessage('success', 'Log deleted.');
        redirect(APP_URL . '/admin/dashboard.php');
    }

    if (isset($_POST['clear_old_logs'])) {
        $days       = intval($_POST['days']);
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        $result     = $db->query(
            "DELETE FROM audit_logs WHERE created_at < :cutoff",
            ['cutoff' => $cutoffDate]
        );
        setFlashMessage('success', "Deleted {$result->rowCount()} old log entries.");
        redirect(APP_URL . '/admin/dashboard.php');
    }

    if (isset($_POST['clear_all_logs'])) {
        $result = $db->query("DELETE FROM audit_logs");
        setFlashMessage('success', "Deleted all {$result->rowCount()} log entries.");
        redirect(APP_URL . '/admin/dashboard.php');
    }

    if (isset($_POST['update_event_status'])) {
        $eventModel->update(intval($_POST['event_id']), ['status' => $_POST['new_status']]);
        logAudit($_SESSION['user_id'], 'update_event_status', 'events', $_POST['event_id']);
        setFlashMessage('success', 'Event status updated.');
        redirect(APP_URL . '/admin/dashboard.php');
    }

    if (isset($_POST['delete_event'])) {
        $eventModel->delete(intval($_POST['event_id']));
        logAudit($_SESSION['user_id'], 'delete_event', 'events', $_POST['event_id']);
        setFlashMessage('success', 'Event deleted.');
        redirect(APP_URL . '/admin/dashboard.php');
    }
}

// ── STATISTICS ─────────────────────────────────────────────────────────────

// Total active brigaders
$totalBrigaders = $brigaderModel->count(['status' => 'active']);

// Male brigaders (active)
$maleBrigaders = $db->fetchOne(
    "SELECT COUNT(*) as count FROM brigaders
     WHERE status = 'active' AND gender = 'male'"
)['count'];

// Female brigaders (active)
$femaleBrigaders = $db->fetchOne(
    "SELECT COUNT(*) as count FROM brigaders
     WHERE status = 'active' AND gender = 'female'"
)['count'];

// Number of officers registered (users with role = officer)
$totalOfficers = $db->fetchOne(
    "SELECT COUNT(*) as count FROM users
     WHERE role = 'officer' AND status = 'active'"
)['count'];

// Total active users (all roles)
$totalUsers = count($userModel->getAll(['status' => 'active']));

// Event counts
$scheduledEvents = $db->fetchOne(
    "SELECT COUNT(*) as count FROM events WHERE status = 'scheduled'"
)['count'];

$completedEvents = $db->fetchOne(
    "SELECT COUNT(*) as count FROM events WHERE status = 'completed'"
)['count'];

$totalEvents = $db->fetchOne(
    "SELECT COUNT(*) as count FROM events"
)['count'];

$totalRecitals = $db->fetchOne(
    "SELECT COUNT(*) as count FROM recitals WHERE is_active = 1"
)['count'];

// Data for tables
$allRecentEvents  = $eventModel->getAll([], 10, 0);
$recentActivity   = $db->fetchAll(
    "SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 10"
);
$rankDistribution = $db->fetchAll(
    "SELECT r.rank_name,
            COUNT(b.id) as count,
            SUM(CASE WHEN b.gender = 'male'   THEN 1 ELSE 0 END) as male_count,
            SUM(CASE WHEN b.gender = 'female' THEN 1 ELSE 0 END) as female_count
     FROM ranks r
     LEFT JOIN brigaders b ON r.id = b.rank_id AND b.status = 'active'
     GROUP BY r.id, r.rank_name
     ORDER BY r.rank_level"
);

$pageTitle = 'Admin Dashboard';

include __DIR__ . '/../includes/header.php';
?>

<div class="content-area">
    <div class="mb-4">
        <h2 class="text-primary-blue mb-1">Admin Dashboard</h2>
        <p class="text-muted">
            Welcome back, <?php echo e($_SESSION['full_name']); ?>!
            &nbsp;|&nbsp; <?php echo date('l, F j, Y'); ?>
        </p>
    </div>

    <!-- ── ROW 1: Main Stats ─────────────────────────────────────────────── -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Total Brigaders</div>
                    <div class="stat-value"><?php echo $totalBrigaders; ?></div>
                    <div class="stat-subtitle">Active members</div>
                </div>
                <div class="stat-icon">👥</div>
            </div>
        </div>

        <div class="stat-card accent-red">
            <div class="stat-header">
                <div>
                    <div class="stat-title">System Users</div>
                    <div class="stat-value"><?php echo $totalUsers; ?></div>
                    <div class="stat-subtitle">Active users</div>
                </div>
                <div class="stat-icon">👤</div>
            </div>
        </div>

        <div class="stat-card accent-success">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Scheduled Events</div>
                    <div class="stat-value"><?php echo $scheduledEvents; ?></div>
                    <div class="stat-subtitle"><?php echo $completedEvents; ?> completed</div>
                </div>
                <div class="stat-icon">📅</div>
            </div>
        </div>

        <div class="stat-card accent-info">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Active Recitals</div>
                    <div class="stat-value"><?php echo $totalRecitals; ?></div>
                    <div class="stat-subtitle">In library</div>
                </div>
                <div class="stat-icon">📖</div>
            </div>
        </div>
    </div>

    <!-- ── ROW 2: Gender + Officers Stats ───────────────────────────────── -->
    <div class="stats-grid mt-3">
        <div class="stat-card" style="border-left: 4px solid #1565c0;">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Male Brigaders</div>
                    <div class="stat-value"><?php echo $maleBrigaders; ?></div>
                    <div class="stat-subtitle">
                        <?php echo $totalBrigaders > 0
                            ? round(($maleBrigaders / $totalBrigaders) * 100)
                            : 0; ?>% of total
                    </div>
                </div>
                <div class="stat-icon">👦</div>
            </div>
        </div>

        <div class="stat-card" style="border-left: 4px solid #e91e8c;">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Female Brigaders</div>
                    <div class="stat-value"><?php echo $femaleBrigaders; ?></div>
                    <div class="stat-subtitle">
                        <?php echo $totalBrigaders > 0
                            ? round(($femaleBrigaders / $totalBrigaders) * 100)
                            : 0; ?>% of total
                    </div>
                </div>
                <div class="stat-icon">👧</div>
            </div>
        </div>

        <div class="stat-card" style="border-left: 4px solid #ff6f00;">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Registered Officers</div>
                    <div class="stat-value"><?php echo $totalOfficers; ?></div>
                    <div class="stat-subtitle">Active officer accounts</div>
                </div>
                <div class="stat-icon">🎖️</div>
            </div>
        </div>

        <div class="stat-card" style="border-left: 4px solid #2e7d32;">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Gender Ratio</div>
                    <div class="stat-value">
                        <?php echo $maleBrigaders; ?>M /
                        <?php echo $femaleBrigaders; ?>F
                    </div>
                    <div class="stat-subtitle">Male / Female</div>
                </div>
                <div class="stat-icon">⚖️</div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- ── Events Management ──────────────────────────────────────────── -->
        <div class="col-lg-7 mb-4">
            <div class="glass-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="text-primary-blue mb-0">
                        Events
                        <span class="badge badge-primary ms-2">
                            <?php echo $totalEvents; ?> total
                        </span>
                    </h5>
                    <a href="<?php echo APP_URL; ?>/admin/events.php?action=create"
                       class="btn btn-primary btn-sm">
                        ➕ New Event
                    </a>
                </div>

                <?php if (empty($allRecentEvents)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📅</div>
                        <div class="empty-state-title">No Events Yet</div>
                        <a href="<?php echo APP_URL; ?>/admin/events.php?action=create"
                           class="btn btn-primary btn-sm mt-2">
                            Create Event
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allRecentEvents as $event): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo e($event['event_name']); ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <?php echo ucfirst($event['event_type']); ?>
                                                <?php if ($event['location']): ?>
                                                    — <?php echo e($event['location']); ?>
                                                <?php endif; ?>
                                            </small>
                                        </td>
                                        <td>
                                            <small><?php echo formatDate($event['event_date']); ?></small>
                                        </td>
                                        <td><?php echo getStatusBadge($event['status']); ?></td>
                                        <td>
                                            <div class="d-flex gap-1 flex-wrap">
                                                <?php if ($event['status'] === 'scheduled' || $event['status'] === 'ongoing'): ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                        <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                                        <input type="hidden" name="new_status" value="completed">
                                                        <button type="submit" name="update_event_status"
                                                                class="btn btn-sm btn-success"
                                                                onclick="return confirm('Mark as completed?')">
                                                            ✓ Done
                                                        </button>
                                                    </form>
                                                <?php endif; ?>

                                                <?php if ($event['status'] === 'scheduled'): ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                        <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                                        <input type="hidden" name="new_status" value="cancelled">
                                                        <button type="submit" name="update_event_status"
                                                                class="btn btn-sm btn-warning"
                                                                onclick="return confirm('Cancel this event?')">
                                                            ✗ Cancel
                                                        </button>
                                                    </form>
                                                <?php endif; ?>

                                                <a href="<?php echo APP_URL; ?>/admin/events.php?action=edit&id=<?php echo $event['id']; ?>"
                                                   class="btn btn-sm btn-secondary">Edit</a>

                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                    <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                                    <button type="submit" name="delete_event"
                                                            class="btn btn-sm btn-danger"
                                                            onclick="return confirm('Delete this event permanently?')">
                                                        🗑️
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-2">
                        <a href="<?php echo APP_URL; ?>/admin/events.php"
                           class="btn btn-secondary btn-sm">View All Events</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── Rank Distribution with Gender ─────────────────────────────── -->
        <div class="col-lg-5 mb-4">
            <div class="glass-card">
                <h5 class="text-primary-blue mb-3">
                    Brigaders by Rank &amp; Gender
                </h5>
                <?php if (empty($rankDistribution)): ?>
                    <p class="text-muted">No rank data available.</p>
                <?php else: ?>
                    <?php foreach ($rankDistribution as $rank): ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <strong><?php echo e($rank['rank_name']); ?></strong>
                                <span>
                                    <span class="badge badge-primary"><?php echo $rank['count']; ?> total</span>
                                    <span class="badge ms-1" style="background:#1565c0;">
                                        👦 <?php echo $rank['male_count']; ?>
                                    </span>
                                    <span class="badge ms-1" style="background:#e91e8c;">
                                        👧 <?php echo $rank['female_count']; ?>
                                    </span>
                                </span>
                            </div>
                            <div class="progress" style="height:8px;">
                                <!-- Male bar (blue) -->
                                <div class="progress-bar"
                                     style="width:<?php echo $totalBrigaders > 0
                                         ? round(($rank['male_count'] / $totalBrigaders) * 100)
                                         : 0; ?>%;
                                            background:#1565c0;">
                                </div>
                                <!-- Female bar (pink) -->
                                <div class="progress-bar"
                                     style="width:<?php echo $totalBrigaders > 0
                                         ? round(($rank['female_count'] / $totalBrigaders) * 100)
                                         : 0; ?>%;
                                            background:#e91e8c;">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Legend -->
                    <div class="d-flex gap-3 mt-3">
                        <span><span style="display:inline-block;width:12px;height:12px;background:#1565c0;border-radius:2px;"></span> Male</span>
                        <span><span style="display:inline-block;width:12px;height:12px;background:#e91e8c;border-radius:2px;"></span> Female</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── Recent Activity / Audit Logs ──────────────────────────────────── -->
    <div class="glass-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="text-primary-blue mb-0">Recent Activity</h5>
            <div class="dropdown">
                <button class="btn btn-sm btn-secondary dropdown-toggle"
                        data-bs-toggle="dropdown">
                    Manage Logs
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <form method="POST" class="px-3 py-2"
                              onsubmit="return confirm('Clear logs older than 30 days?');">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="days" value="30">
                            <button type="submit" name="clear_old_logs"
                                    class="btn btn-sm btn-warning w-100">
                                Clear &gt; 30 days
                            </button>
                        </form>
                    </li>
                    <li>
                        <form method="POST" class="px-3 py-2"
                              onsubmit="return confirm('Clear logs older than 90 days?');">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="days" value="90">
                            <button type="submit" name="clear_old_logs"
                                    class="btn btn-sm btn-warning w-100">
                                Clear &gt; 90 days
                            </button>
                        </form>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" class="px-3 py-2"
                              onsubmit="return confirm('Delete ALL logs?');">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <button type="submit" name="clear_all_logs"
                                    class="btn btn-sm btn-danger w-100">
                                Clear All
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>

        <?php if (empty($recentActivity)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📋</div>
                <div class="empty-state-text">No recent activity</div>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>User</th>
                            <th>Table</th>
                            <th>Time</th>
                            <th>Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentActivity as $log): ?>
                            <tr>
                                <td><code><?php echo e($log['action']); ?></code></td>
                                <td><?php echo $log['user_id']; ?></td>
                                <td><?php echo e($log['table_name']); ?></td>
                                <td><?php echo timeAgo($log['created_at']); ?></td>
                                <td>
                                    <form method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete this log?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                        <input type="hidden" name="log_id" value="<?php echo $log['id']; ?>">
                                        <button type="submit" name="delete_log"
                                                class="btn btn-sm btn-danger">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>