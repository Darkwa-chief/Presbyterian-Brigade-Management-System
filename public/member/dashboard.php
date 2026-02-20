<?php
// public/member/dashboard.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Event.php';
require_once __DIR__ . '/../../classes/Notification.php';
require_once __DIR__ . '/../../classes/Recital.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole('member');

$db                = Database::getInstance();
$eventModel        = new Event();
$notificationModel = new Notification();
$recitalModel      = new Recital();
$pageTitle         = 'Member Dashboard';

$upcomingEvents = $eventModel->getUpcoming(5);
$pastEvents     = $db->fetchAll(
    "SELECT * FROM events
     WHERE status = 'completed'
     ORDER BY event_date DESC
     LIMIT 5"
);
$notifications  = $notificationModel->getUserNotifications(
    $_SESSION['user_id'],
    $_SESSION['user_role']
);
$unreadCount    = $notificationModel->getUnreadCount(
    $_SESSION['user_id'],
    $_SESSION['user_role']
);
$recitals       = $recitalModel->getAll(['is_active' => 1]);

include __DIR__ . '/../includes/header.php';
?>

<div class="content-area">
    <div class="mb-4">
        <h2 class="text-primary-blue mb-1">Member Dashboard</h2>
        <p class="text-muted">
            Welcome, <?php echo e($_SESSION['full_name']); ?>!
            &nbsp;|&nbsp; <?php echo date('l, F j, Y'); ?>
        </p>
    </div>

    <!-- Stats -->
    <div class="stats-grid mb-4">
        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Upcoming Events</div>
                    <div class="stat-value"><?php echo count($upcomingEvents); ?></div>
                    <div class="stat-subtitle">Scheduled for you</div>
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
                    <div class="stat-title">Recitals</div>
                    <div class="stat-value"><?php echo count($recitals); ?></div>
                    <div class="stat-subtitle">Available to learn</div>
                </div>
                <div class="stat-icon">📖</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Events -->
        <div class="col-lg-6 mb-4">
            <div class="glass-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="text-primary-blue mb-0">Events</h5>
                    <div class="d-flex gap-2">
                        <button id="btnUpcoming"
                                class="btn btn-sm btn-primary"
                                onclick="showTab('upcoming', this)">
                            Upcoming
                        </button>
                        <button id="btnPast"
                                class="btn btn-sm btn-secondary"
                                onclick="showTab('past', this)">
                            Past
                        </button>
                    </div>
                </div>

                <!-- Upcoming Tab -->
                <div id="tab-upcoming">
                    <?php if (empty($upcomingEvents)): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📅</div>
                            <div class="empty-state-title">No Upcoming Events</div>
                            <div class="empty-state-text">Check back later.</div>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($upcomingEvents as $event): ?>
                                <div class="list-group-item border-0 px-0 py-2">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="mb-1">
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
                                        <span class="badge badge-primary align-self-start">
                                            <?php echo ucfirst($event['event_type']); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="text-center mt-2">
                            <a href="<?php echo APP_URL; ?>/member/events.php"
                               class="btn btn-secondary btn-sm">
                                View All Events
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Past Tab -->
                <div id="tab-past" style="display:none;">
                    <?php if (empty($pastEvents)): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📅</div>
                            <div class="empty-state-title">No Past Events</div>
                            <div class="empty-state-text">No completed events yet.</div>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($pastEvents as $event): ?>
                                <div class="list-group-item border-0 px-0 py-2">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="mb-1">
                                                <?php echo e($event['event_name']); ?>
                                            </h6>
                                            <small class="text-muted">
                                                📅 <?php echo formatDate($event['event_date']); ?>
                                            </small>
                                        </div>
                                        <span class="badge badge-success align-self-start">
                                            Completed
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <!-- Notifications -->
        <div class="col-lg-6 mb-4">
            <div class="glass-card h-100">
                <h5 class="text-primary-blue mb-3">
                    Notifications
                    <?php if ($unreadCount > 0): ?>
                        <span class="badge badge-danger ms-2">
                            <?php echo $unreadCount; ?> new
                        </span>
                    <?php endif; ?>
                </h5>

                <?php if (empty($notifications)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">🔔</div>
                        <div class="empty-state-title">No Notifications</div>
                        <div class="empty-state-text">
                            You have no notifications at this time.
                        </div>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach (array_slice($notifications, 0, 5) as $n): ?>
                            <?php
                            $typeColors = [
                                'general'   => 'secondary',
                                'event'     => 'primary',
                                'promotion' => 'success',
                                'urgent'    => 'danger'
                            ];
                            $color = $typeColors[$n['notification_type']] ?? 'secondary';
                            ?>
                            <div class="list-group-item border-0 px-0 py-2
                                <?php echo !$n['is_read'] ? 'bg-light rounded' : ''; ?>">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <span class="badge badge-<?php echo $color; ?> me-1">
                                            <?php echo ucfirst($n['notification_type']); ?>
                                        </span>
                                        <?php if (!$n['is_read']): ?>
                                            <span class="badge badge-danger">New</span>
                                        <?php endif; ?>
                                        <strong class="d-block mt-1">
                                            <?php echo e($n['title']); ?>
                                        </strong>
                                        <p class="mb-0 text-muted small">
                                            <?php echo e($n['message']); ?>
                                        </p>
                                    </div>
                                    <small class="text-muted text-nowrap ms-2">
                                        <?php echo timeAgo($n['created_at']); ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recitals -->
    <div class="glass-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="text-primary-blue mb-0">📖 Recent Recitals</h5>
            <a href="<?php echo APP_URL; ?>/member/recitals.php"
               class="btn btn-secondary btn-sm">
                View All
            </a>
        </div>

        <?php if (empty($recitals)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📖</div>
                <div class="empty-state-title">No Recitals Available</div>
                <div class="empty-state-text">No recitals have been added yet.</div>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach (array_slice($recitals, 0, 3) as $recital): ?>
                    <?php
                    $diffColors = [
                        'beginner'     => 'success',
                        'intermediate' => 'warning',
                        'advanced'     => 'danger'
                    ];
                    $diffColor = $diffColors[$recital['difficulty_level']] ?? 'secondary';
                    ?>
                    <div class="col-md-4 mb-3">
                        <div class="card border-0 shadow-sm h-100"
                             style="border-radius:10px;">
                            <div style="height:4px;
                                        background:var(--primary-blue);
                                        border-radius:10px 10px 0 0;">
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="badge badge-secondary">
                                        <?php echo ucfirst($recital['category']); ?>
                                    </span>
                                    <span class="badge badge-<?php echo $diffColor; ?>">
                                        <?php echo ucfirst($recital['difficulty_level']); ?>
                                    </span>
                                </div>
                                <h6 class="text-primary-blue">
                                    <?php echo e($recital['title']); ?>
                                </h6>
                                <?php if ($recital['reference']): ?>
                                    <p class="text-muted small mb-2">
                                        📚 <?php echo e($recital['reference']); ?>
                                    </p>
                                <?php endif; ?>
                                <p class="small text-muted">
                                    <?php echo e(substr($recital['content'], 0, 80)); ?>...
                                </p>
                                <button class="btn btn-primary btn-sm w-100"
                                        onclick="viewRecital(<?php echo $recital['id']; ?>)">
                                    Read
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Recital Modal -->
<div class="modal fade" id="recitalModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="recitalModalTitle">Recital</h5>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="recitalModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
const recitals = <?php echo json_encode($recitals); ?>;

function showTab(tab, btn) {
    document.getElementById('tab-upcoming').style.display = 'none';
    document.getElementById('tab-past').style.display     = 'none';
    document.getElementById('tab-' + tab).style.display   = 'block';

    document.getElementById('btnUpcoming').className = 'btn btn-sm btn-secondary';
    document.getElementById('btnPast').className     = 'btn btn-sm btn-secondary';
    btn.className = 'btn btn-sm btn-primary';
}

function viewRecital(id) {
    const recital = recitals.find(r => parseInt(r.id) === id);
    if (!recital) return;

    document.getElementById('recitalModalTitle').textContent =
        recital.title + (recital.reference ? ' — ' + recital.reference : '');

    document.getElementById('recitalModalBody').innerHTML = `
        <div class="mb-3">
            <span class="badge bg-secondary me-2">${recital.category}</span>
            <span class="badge bg-info me-2">${recital.difficulty_level}</span>
            ${recital.reference
                ? '<span class="text-muted small">📚 ' + escapeHtml(recital.reference) + '</span>'
                : ''}
        </div>
        <hr>
        <div style="white-space:pre-wrap;font-size:1.1rem;
                    line-height:2.2;font-family:Georgia,serif;">
            ${escapeHtml(recital.content)}
        </div>
    `;
    new bootstrap.Modal(document.getElementById('recitalModal')).show();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>