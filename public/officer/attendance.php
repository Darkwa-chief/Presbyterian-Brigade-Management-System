<?php
// public/officer/attendance.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Event.php';
require_once __DIR__ . '/../../classes/Attendance.php';
require_once __DIR__ . '/../../classes/Brigader.php';
require_once __DIR__ . '/../../classes/Notification.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['admin', 'officer']);

$attendanceModel = new Attendance();
$eventModel      = new Event();
$brigaderModel   = new Brigader();
$db              = Database::getInstance();
$pageTitle       = 'Attendance';
$eventId         = $_GET['event_id'] ?? null;
$genderView      = $_GET['gender']   ?? 'all';

// ── POST HANDLERS ───────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['csrf_token'])) {
        validateCSRFToken($_POST['csrf_token']);
    }

    if (isset($_POST['mark_attendance'])) {
        $eventId        = $_POST['event_id'];
        $attendanceData = $_POST['attendance'] ?? [];

        try {
            foreach ($attendanceData as $brigaderId => $status) {
                $notes = $_POST['notes'][$brigaderId] ?? '';
                $attendanceModel->markAttendance(
                    $eventId, $brigaderId, $status, $_SESSION['user_id'], $notes
                );
            }
            logAudit($_SESSION['user_id'], 'mark_attendance', 'attendance', $eventId);
            setFlashMessage('success', 'Attendance saved successfully.');
            redirect(APP_URL . '/officer/attendance.php?event_id=' . $eventId
                   . '&gender=' . ($_POST['gender_view'] ?? 'all'));
        } catch (Exception $e) {
            $error = 'Failed to save attendance: ' . $e->getMessage();
        }
    }
}

// ── LOAD EVENT DATA ─────────────────────────────────────────────────────────
$event           = null;
$attendanceMap   = [];
$stats           = null;
$allBrigaders    = [];
$maleBrigaders   = [];
$femaleBrigaders = [];
$malePresent     = 0;
$femalePresent   = 0;

if ($eventId) {
    $event = $eventModel->getById($eventId);
    if (!$event) {
        setFlashMessage('danger', 'Event not found.');
        redirect(APP_URL . '/officer/events.php');
    }

    $attendanceRecords = $attendanceModel->getEventAttendance($eventId);
    $stats             = $attendanceModel->getAttendanceStats($eventId);
    $allBrigaders      = $brigaderModel->getAll(['status' => 'active']);

    foreach ($allBrigaders as $b) {
        if ($b['gender'] === 'male') $maleBrigaders[] = $b;
        else                          $femaleBrigaders[] = $b;
    }

    foreach ($attendanceRecords as $record) {
        $attendanceMap[$record['brigader_id']] = $record;
        if ($record['status'] === 'present') {
            if ($record['gender'] === 'male') $malePresent++;
            else                               $femalePresent++;
        }
    }
}

$recentEvents = $eventModel->getAll([], 20, 0);

include __DIR__ . '/../includes/header.php';
?>

<div class="content-area">
    <?php if (!$eventId): ?>
        <!-- ── Select Event ──────────────────────────────────────────────── -->
        <div class="glass-card" style="max-width:700px;">
            <h4 class="text-primary-blue mb-4">Select Event for Attendance</h4>

            <?php if (empty($recentEvents)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📅</div>
                    <div class="empty-state-title">No Events Available</div>
                    <div class="empty-state-text">Create an event first.</div>
                    <a href="events.php?action=create" class="btn btn-primary">Create Event</a>
                </div>
            <?php else: ?>
                <div class="list-group">
                    <?php foreach ($recentEvents as $e): ?>
                        <a href="?event_id=<?php echo $e['id']; ?>"
                           class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><?php echo e($e['event_name']); ?></h6>
                                    <small class="text-muted">
                                        <?php echo formatDate($e['event_date']); ?>
                                        <?php if ($e['event_time']): ?>
                                            at <?php echo date('g:i A', strtotime($e['event_time'])); ?>
                                        <?php endif; ?>
                                        — <?php echo e($e['location']); ?>
                                    </small>
                                </div>
                                <div>
                                    <span class="badge badge-primary">
                                        <?php echo ucfirst($e['event_type']); ?>
                                    </span>
                                    <span class="badge badge-info ms-1">
                                        <?php echo $e['total_attendance']; ?> marked
                                    </span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <!-- ── Attendance Header ──────────────────────────────────────── -->
        <div class="mb-3 d-flex justify-content-between align-items-start">
            <div>
                <h3 class="text-primary-blue mb-1"><?php echo e($event['event_name']); ?></h3>
                <p class="text-muted mb-0">
                    <?php echo formatDate($event['event_date']); ?>
                    <?php if ($event['event_time']): ?>
                        at <?php echo date('g:i A', strtotime($event['event_time'])); ?>
                    <?php endif; ?>
                    — <?php echo e($event['location']); ?>
                </p>
            </div>
            <a href="attendance.php" class="btn btn-secondary">Change Event</a>
        </div>

        <!-- ── Summary Stats ──────────────────────────────────────────── -->
        <?php if ($stats && $stats['total'] > 0): ?>
            <div class="stats-grid mb-4">
                <div class="stat-card accent-success">
                    <div class="stat-header">
                        <div>
                            <div class="stat-title">Present</div>
                            <div class="stat-value"><?php echo $stats['present']; ?></div>
                        </div>
                        <div class="stat-icon">✓</div>
                    </div>
                </div>
                <div class="stat-card accent-red">
                    <div class="stat-header">
                        <div>
                            <div class="stat-title">Absent</div>
                            <div class="stat-value"><?php echo $stats['absent']; ?></div>
                        </div>
                        <div class="stat-icon">✗</div>
                    </div>
                </div>
                <div class="stat-card accent-warning">
                    <div class="stat-header">
                        <div>
                            <div class="stat-title">Excused</div>
                            <div class="stat-value"><?php echo $stats['excused']; ?></div>
                        </div>
                        <div class="stat-icon">📝</div>
                    </div>
                </div>
                <div class="stat-card accent-info">
                    <div class="stat-header">
                        <div>
                            <div class="stat-title">Late</div>
                            <div class="stat-value"><?php echo $stats['late']; ?></div>
                        </div>
                        <div class="stat-icon">⏰</div>
                    </div>
                </div>
            </div>

            <!-- Gender Mini Stats -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="stat-card" style="border-left:4px solid #1565c0;">
                        <div class="stat-header">
                            <div>
                                <div class="stat-title">Boys Present</div>
                                <div class="stat-value"><?php echo $malePresent; ?></div>
                                <div class="stat-subtitle">of <?php echo count($maleBrigaders); ?> boys</div>
                            </div>
                            <div class="stat-icon">👦</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-card" style="border-left:4px solid #e91e8c;">
                        <div class="stat-header">
                            <div>
                                <div class="stat-title">Girls Present</div>
                                <div class="stat-value"><?php echo $femalePresent; ?></div>
                                <div class="stat-subtitle">of <?php echo count($femaleBrigaders); ?> girls</div>
                            </div>
                            <div class="stat-icon">👧</div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- ── Gender Tabs ─────────────────────────────────────────────── -->
        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link <?php echo $genderView === 'all' ? 'active' : ''; ?>"
                   href="?event_id=<?php echo $eventId; ?>&gender=all">
                    👥 All (<?php echo count($allBrigaders); ?>)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $genderView === 'male' ? 'active' : ''; ?>"
                   href="?event_id=<?php echo $eventId; ?>&gender=male">
                    👦 Boys (<?php echo count($maleBrigaders); ?>)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $genderView === 'female' ? 'active' : ''; ?>"
                   href="?event_id=<?php echo $eventId; ?>&gender=female">
                    👧 Girls (<?php echo count($femaleBrigaders); ?>)
                </a>
            </li>
        </ul>

        <!-- ── Attendance Table ───────────────────────────────────────── -->
        <div class="data-table-wrapper">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo e($error); ?></div>
            <?php endif; ?>

            <?php
            if ($genderView === 'male')        { $brigadersToShow = $maleBrigaders;   $genderLabel = 'Boys'; }
            elseif ($genderView === 'female')   { $brigadersToShow = $femaleBrigaders; $genderLabel = 'Girls'; }
            else                                { $brigadersToShow = $allBrigaders;    $genderLabel = 'All Brigaders'; }
            ?>

            <?php if (empty($brigadersToShow)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">👥</div>
                    <div class="empty-state-title">No <?php echo $genderLabel; ?></div>
                    <div class="empty-state-text">No active <?php echo strtolower($genderLabel); ?> found.</div>
                </div>
            <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="event_id" value="<?php echo $eventId; ?>">
                    <input type="hidden" name="gender_view" value="<?php echo $genderView; ?>">

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Brigade ID</th>
                                    <th>Name</th>
                                    <th>Gender</th>
                                    <th>Unit</th>
                                    <th>Rank</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($brigadersToShow as $brigader): ?>
                                    <?php
                                    $existing      = $attendanceMap[$brigader['id']] ?? null;
                                    $currentStatus = $existing ? $existing['status'] : 'present';
                                    $currentNotes  = $existing ? $existing['notes']  : '';
                                    ?>
                                    <tr>
                                        <td><strong><?php echo e($brigader['brigade_id']); ?></strong></td>
                                        <td><?php echo e($brigader['first_name'] . ' ' . $brigader['last_name']); ?></td>
                                        <td>
                                            <?php if ($brigader['gender'] === 'male'): ?>
                                                <span style="color:#1565c0;">👦</span>
                                            <?php else: ?>
                                                <span style="color:#e91e8c;">👧</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e($brigader['unit']); ?></td>
                                        <td><?php echo e($brigader['rank_name'] ?? 'N/A'); ?></td>
                                        <td>
                                            <select name="attendance[<?php echo $brigader['id']; ?>]"
                                                    class="form-select form-select-sm"
                                                    style="width:130px;">
                                                <option value="present" <?php echo $currentStatus === 'present' ? 'selected' : ''; ?>>Present</option>
                                                <option value="absent"  <?php echo $currentStatus === 'absent'  ? 'selected' : ''; ?>>Absent</option>
                                                <option value="excused" <?php echo $currentStatus === 'excused' ? 'selected' : ''; ?>>Excused</option>
                                                <option value="late"    <?php echo $currentStatus === 'late'    ? 'selected' : ''; ?>>Late</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text"
                                                   name="notes[<?php echo $brigader['id']; ?>]"
                                                   class="form-control form-control-sm"
                                                   placeholder="Optional"
                                                   value="<?php echo e($currentNotes); ?>">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="submit" name="mark_attendance" class="btn btn-primary">
                            Save Attendance
                        </button>
                        <a href="events.php" class="btn btn-secondary">Back to Events</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>