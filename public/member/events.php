<?php
// public/member/events.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Event.php';
require_once __DIR__ . '/../../classes/Notification.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['member', 'officer', 'admin']);

$db         = Database::getInstance();
$eventModel = new Event();
$pageTitle  = 'Events';

// Filters
$filters = [];
if (!empty($_GET['status']))     $filters['status']     = $_GET['status'];
if (!empty($_GET['event_type'])) $filters['event_type'] = $_GET['event_type'];

$events = $eventModel->getAll($filters, 50, 0);

include __DIR__ . '/../includes/header.php';
?>

<div class="content-area">
    <div class="mb-4">
        <h2 class="text-primary-blue mb-1">Events & Activities</h2>
        <p class="text-muted">All brigade events and activities</p>
    </div>

    <!-- Filters -->
    <div class="glass-card mb-4">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <select class="form-select" name="status">
                    <option value="">All Status</option>
                    <option value="scheduled"
                        <?php echo ($_GET['status'] ?? '') === 'scheduled' ? 'selected' : ''; ?>>
                        Upcoming
                    </option>
                    <option value="completed"
                        <?php echo ($_GET['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>
                        Completed
                    </option>
                    <option value="cancelled"
                        <?php echo ($_GET['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>
                        Cancelled
                    </option>
                </select>
            </div>
            <div class="col-md-4">
                <select class="form-select" name="event_type">
                    <option value="">All Types</option>
                    <option value="meeting"
                        <?php echo ($_GET['event_type'] ?? '') === 'meeting' ? 'selected' : ''; ?>>
                        Meeting
                    </option>
                    <option value="training"
                        <?php echo ($_GET['event_type'] ?? '') === 'training' ? 'selected' : ''; ?>>
                        Training
                    </option>
                    <option value="service"
                        <?php echo ($_GET['event_type'] ?? '') === 'service' ? 'selected' : ''; ?>>
                        Service
                    </option>
                    <option value="competition"
                        <?php echo ($_GET['event_type'] ?? '') === 'competition' ? 'selected' : ''; ?>>
                        Competition
                    </option>
                    <option value="other"
                        <?php echo ($_GET['event_type'] ?? '') === 'other' ? 'selected' : ''; ?>>
                        Other
                    </option>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="events.php" class="btn btn-secondary">Clear</a>
            </div>
        </form>
    </div>

    <!-- Events Grid -->
    <?php if (empty($events)): ?>
        <div class="empty-state glass-card">
            <div class="empty-state-icon">📅</div>
            <div class="empty-state-title">No Events Found</div>
            <div class="empty-state-text">No events match your search.</div>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($events as $event): ?>
                <?php
                $isToday = date('Y-m-d', strtotime($event['event_date'])) === date('Y-m-d');
                $isPast  = strtotime($event['event_date']) < strtotime('today');

                if ($isToday) {
                    $topColor = 'var(--accent-red)';
                } elseif ($isPast) {
                    $topColor = '#aaa';
                } else {
                    $topColor = 'var(--primary-blue)';
                }
                ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm"
                         style="border-radius:10px;">
                        <!-- Top color bar -->
                        <div style="height:5px;
                                    background:<?php echo $topColor; ?>;
                                    border-radius:10px 10px 0 0;">
                        </div>

                        <div class="card-body">
                            <!-- Badges -->
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge badge-secondary">
                                    <?php echo ucfirst($event['event_type']); ?>
                                </span>
                                <?php echo getStatusBadge($event['status']); ?>
                                <?php if ($isToday): ?>
                                    <span class="badge badge-danger">Today!</span>
                                <?php endif; ?>
                            </div>

                            <!-- Title -->
                            <h5 class="card-title text-primary-blue">
                                <?php echo e($event['event_name']); ?>
                            </h5>

                            <!-- Date & Time -->
                            <p class="card-text small text-muted mb-1">
                                📅 <strong><?php echo formatDate($event['event_date']); ?></strong>
                                <?php if ($event['event_time']): ?>
                                    at <?php echo date('g:i A', strtotime($event['event_time'])); ?>
                                <?php endif; ?>
                            </p>

                            <!-- Location -->
                            <?php if ($event['location']): ?>
                                <p class="card-text small text-muted mb-1">
                                    📍 <?php echo e($event['location']); ?>
                                </p>
                            <?php endif; ?>

                            <!-- Description -->
                            <?php if ($event['description']): ?>
                                <p class="card-text small text-muted mt-2">
                                    <?php
                                    $desc = substr($event['description'], 0, 100);
                                    echo e($desc);
                                    echo strlen($event['description']) > 100 ? '...' : '';
                                    ?>
                                </p>
                            <?php endif; ?>

                            <!-- Attendance count -->
                            <?php if ($event['total_attendance'] > 0): ?>
                                <p class="card-text small mt-2">
                                    <span class="badge badge-info">
                                        👥 <?php echo $event['total_attendance']; ?> attended
                                    </span>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>