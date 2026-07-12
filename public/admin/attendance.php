<?php
// public/admin/attendance.php

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
$pageTitle       = 'Attendance Management';
$eventId         = $_GET['event_id'] ?? null;

// Current gender view (boys / girls / all)
$genderView = $_GET['gender'] ?? 'all';

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
            setFlashMessage('success', 'Attendance marked successfully.');
            redirect('attendance.php?event_id=' . $eventId . '&gender=' . $_POST['gender_view']);
        } catch (Exception $e) {
            $error = 'Failed to mark attendance: ' . $e->getMessage();
        }

    } elseif (isset($_POST['export_attendance'])) {
        $eventId      = $_POST['event_id'];
        $exportGender = $_POST['export_gender'] ?? 'all';
        $event        = $eventModel->getById($eventId);

        // Get attendance records filtered by gender
        $query  = "SELECT a.*, b.brigade_id, b.first_name, b.last_name,
                          b.unit, b.gender, r.rank_name,
                          u.full_name as marked_by_name
                   FROM attendance a
                   JOIN brigaders b ON a.brigader_id = b.id
                   LEFT JOIN ranks r ON b.rank_id = r.id
                   LEFT JOIN users u ON a.marked_by = u.id
                   WHERE a.event_id = :event_id";
        $params = ['event_id' => $eventId];

        if ($exportGender !== 'all') {
            $query  .= " AND b.gender = :gender";
            $params['gender'] = $exportGender;
        }
        $query .= " ORDER BY b.gender, b.last_name, b.first_name";

        $attendanceRecords = $db->fetchAll($query, $params);

        $genderLabel = $exportGender !== 'all' ? '_' . $exportGender : '';
        $filename    = 'attendance_' . str_replace(' ', '_', $event['event_name'])
                     . $genderLabel . '_' . date('Y-m-d') . '.csv';

        $csvData = [];
        foreach ($attendanceRecords as $record) {
            $csvData[] = [
                'Brigade ID' => $record['brigade_id'],
                'First Name' => $record['first_name'],
                'Last Name'  => $record['last_name'],
                'Gender'     => ucfirst($record['gender']),
                'Unit'       => $record['unit'],
                'Rank'       => $record['rank_name'] ?? 'N/A',
                'Status'     => ucfirst($record['status']),
                'Notes'      => $record['notes'],
                'Marked By'  => $record['marked_by_name'] ?? 'N/A',
                'Marked At'  => $record['marked_at'],
            ];
        }

        exportToCSV($csvData, $filename);
    }
}

// ── LOAD EVENT DATA ─────────────────────────────────────────────────────────
$event             = null;
$attendanceRecords = [];
$stats             = null;
$allBrigaders      = [];
$maleBrigaders     = [];
$femaleBrigaders   = [];

if ($eventId) {
    $event = $eventModel->getById($eventId);
    if (!$event) {
        setFlashMessage('danger', 'Event not found.');
        redirect('events.php');
    }

    $attendanceRecords = $attendanceModel->getEventAttendance($eventId);
    $stats             = $attendanceModel->getAttendanceStats($eventId);

    // Load ALL active brigaders
    $allBrigaders = $brigaderModel->getAll(['status' => 'active']);

    // Split by gender
    foreach ($allBrigaders as $b) {
        if ($b['gender'] === 'male') {
            $maleBrigaders[] = $b;
        } else {
            $femaleBrigaders[] = $b;
        }
    }

    // Build attendance map
    $attendanceMap = [];
    foreach ($attendanceRecords as $record) {
        $attendanceMap[$record['brigader_id']] = $record;
    }

    // Gender-specific stats
    $malePresent   = 0;
    $femalePresent = 0;
    foreach ($attendanceRecords as $r) {
        if ($r['status'] === 'present') {
            if ($r['gender'] === 'male')   $malePresent++;
            else                            $femalePresent++;
        }
    }
}

// Recent events for selection (show all, not just scheduled)
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
                    <div class="empty-state-text">Create an event first to mark attendance.</div>
                    <a href="events.php?action=create" class="btn btn-primary">Create Event</a>
                </div>
            <?php else: ?>
                <div class="list-group">
                    <?php foreach ($recentEvents as $e): ?>
                        <a href="?event_id=<?php echo $e['id']; ?>"
                           class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-start">
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
        <!-- ── Attendance Marking ──────────────────────────────────────── -->
        <div class="mb-4 d-flex justify-content-between align-items-start">
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
                            <div class="stat-subtitle">
                                <?php echo round(($stats['present'] / $stats['total']) * 100); ?>%
                            </div>
                        </div>
                        <div class="stat-icon">✓</div>
                    </div>
                </div>
                <div class="stat-card accent-red">
                    <div class="stat-header">
                        <div>
                            <div class="stat-title">Absent</div>
                            <div class="stat-value"><?php echo $stats['absent']; ?></div>
                            <div class="stat-subtitle">
                                <?php echo round(($stats['absent'] / $stats['total']) * 100); ?>%
                            </div>
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

            <!-- Gender Stats Row -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="stat-card" style="border-left:4px solid #1565c0;">
                        <div class="stat-header">
                            <div>
                                <div class="stat-title">Boys Present</div>
                                <div class="stat-value"><?php echo $malePresent; ?></div>
                                <div class="stat-subtitle">
                                    out of <?php echo count($maleBrigaders); ?> boys
                                </div>
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
                                <div class="stat-subtitle">
                                    out of <?php echo count($femaleBrigaders); ?> girls
                                </div>
                            </div>
                            <div class="stat-icon">👧</div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- ── Export Buttons ─────────────────────────────────────────── -->
        <div class="d-flex gap-2 mb-4 flex-wrap">
            <form method="POST" class="d-inline">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="event_id" value="<?php echo $eventId; ?>">
                <input type="hidden" name="export_gender" value="all">
                <button type="submit" name="export_attendance" class="btn btn-secondary">
                    📥 Export All CSV
                </button>
            </form>
            <form method="POST" class="d-inline">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="event_id" value="<?php echo $eventId; ?>">
                <input type="hidden" name="export_gender" value="male">
                <button type="submit" name="export_attendance" class="btn btn-secondary"
                        style="border-color:#1565c0;color:#1565c0;">
                    📥 Export Boys CSV
                </button>
            </form>
            <form method="POST" class="d-inline">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="event_id" value="<?php echo $eventId; ?>">
                <input type="hidden" name="export_gender" value="female">
                <button type="submit" name="export_attendance" class="btn btn-secondary"
                        style="border-color:#e91e8c;color:#e91e8c;">
                    📥 Export Girls CSV
                </button>
            </form>
        </div>

        <!-- ── Gender Tabs ────────────────────────────────────────────── -->
        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link <?php echo $genderView === 'all' ? 'active' : ''; ?>"
                   href="?event_id=<?php echo $eventId; ?>&gender=all">
                    👥 All (<?php echo count($allBrigaders); ?>)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $genderView === 'male' ? 'active' : ''; ?>"
                   href="?event_id=<?php echo $eventId; ?>&gender=male"
                   style="<?php echo $genderView === 'male' ? 'color:#1565c0;border-bottom:2px solid #1565c0;' : ''; ?>">
                    👦 Boys (<?php echo count($maleBrigaders); ?>)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $genderView === 'female' ? 'active' : ''; ?>"
                   href="?event_id=<?php echo $eventId; ?>&gender=female"
                   style="<?php echo $genderView === 'female' ? 'color:#e91e8c;border-bottom:2px solid #e91e8c;' : ''; ?>">
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
            // Decide which brigaders to show
            if ($genderView === 'male') {
                $brigadersToShow = $maleBrigaders;
                $genderLabel     = 'Boys';
            } elseif ($genderView === 'female') {
                $brigadersToShow = $femaleBrigaders;
                $genderLabel     = 'Girls';
            } else {
                $brigadersToShow = $allBrigaders;
                $genderLabel     = 'All Brigaders';
            }
            ?>

            <?php if (empty($brigadersToShow)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">👥</div>
                    <div class="empty-state-title">No <?php echo $genderLabel; ?> Found</div>
                    <div class="empty-state-text">
                        No active <?php echo strtolower($genderLabel); ?> to mark attendance for.
                    </div>
                </div>
            <?php else: ?>
                <h6 class="text-muted mb-3">
                    Marking attendance for:
                    <strong><?php echo $genderLabel; ?></strong>
                    (<?php echo count($brigadersToShow); ?> brigaders)
                </h6>

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