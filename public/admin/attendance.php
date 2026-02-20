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
$eventModel = new Event();
$brigaderModel = new Brigader();
$pageTitle = 'Attendance Management';
$eventId = $_GET['event_id'] ?? null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['csrf_token'])) {
        validateCSRFToken($_POST['csrf_token']);
    }
    
    if (isset($_POST['mark_attendance'])) {
        $eventId = $_POST['event_id'];
        $attendanceData = $_POST['attendance'] ?? [];
        
        try {
            foreach ($attendanceData as $brigaderId => $status) {
                $notes = $_POST['notes'][$brigaderId] ?? '';
                $attendanceModel->markAttendance($eventId, $brigaderId, $status, $_SESSION['user_id'], $notes);
            }
            
            logAudit($_SESSION['user_id'], 'mark_attendance', 'attendance', $eventId);
            setFlashMessage('success', 'Attendance marked successfully.');
            redirect('attendance.php?event_id=' . $eventId);
        } catch (Exception $e) {
            $error = 'Failed to mark attendance: ' . $e->getMessage();
        }
    } elseif (isset($_POST['export_attendance'])) {
        $eventId = $_POST['event_id'];
        $event = $eventModel->getById($eventId);
        $attendanceRecords = $attendanceModel->getEventAttendance($eventId);
        
        $csvData = [];
        foreach ($attendanceRecords as $record) {
            $csvData[] = [
                'Brigade ID' => $record['brigade_id'],
                'First Name' => $record['first_name'],
                'Last Name' => $record['last_name'],
                'Unit' => $record['unit'],
                'Rank' => $record['rank_name'] ?? 'N/A',
                'Status' => ucfirst($record['status']),
                'Notes' => $record['notes'],
                'Marked By' => $record['marked_by_name'] ?? 'N/A',
                'Marked At' => $record['marked_at']
            ];
        }
        
        exportToCSV($csvData, 'attendance_' . $event['event_name'] . '_' . date('Y-m-d') . '.csv');
    }
}

// Get event data
$event = null;
$attendanceRecords = [];
$stats = null;
$activeBrigaders = [];

if ($eventId) {
    $event = $eventModel->getById($eventId);
    if (!$event) {
        setFlashMessage('danger', 'Event not found.');
        redirect('events.php');
    }
    
    $attendanceRecords = $attendanceModel->getEventAttendance($eventId);
    $stats = $attendanceModel->getAttendanceStats($eventId);
    $activeBrigaders = $brigaderModel->getAll(['status' => 'active']);
    
    // Create a map of existing attendance
    $attendanceMap = [];
    foreach ($attendanceRecords as $record) {
        $attendanceMap[$record['brigader_id']] = $record;
    }
}

// Get recent events for selection
$recentEvents = $eventModel->getAll(['status' => 'scheduled'], 20, 0);

include __DIR__ . '/../includes/header.php';
?>

<div class="content-area">
    <?php if (!$eventId): ?>
        <!-- Select Event -->
        <div class="glass-card" style="max-width: 700px;">
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
                        <a href="?event_id=<?php echo $e['id']; ?>" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1"><?php echo e($e['event_name']); ?></h6>
                                    <small class="text-muted">
                                        <?php echo formatDate($e['event_date']); ?>
                                        <?php if ($e['event_time']): ?>
                                            at <?php echo date('g:i A', strtotime($e['event_time'])); ?>
                                        <?php endif; ?>
                                        - <?php echo e($e['location']); ?>
                                    </small>
                                </div>
                                <div>
                                    <span class="badge badge-primary"><?php echo ucfirst($e['event_type']); ?></span>
                                    <span class="badge badge-info"><?php echo $e['total_attendance']; ?> marked</span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <!-- Attendance Marking -->
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h3 class="text-primary-blue mb-1"><?php echo e($event['event_name']); ?></h3>
                    <p class="text-muted mb-0">
                        <?php echo formatDate($event['event_date']); ?>
                        <?php if ($event['event_time']): ?>
                            at <?php echo date('g:i A', strtotime($event['event_time'])); ?>
                        <?php endif; ?>
                        - <?php echo e($event['location']); ?>
                    </p>
                </div>
                <a href="attendance.php" class="btn btn-secondary">Change Event</a>
            </div>
        </div>

        <!-- Attendance Statistics -->
        <?php if ($stats && $stats['total'] > 0): ?>
            <div class="stats-grid mb-4">
                <div class="stat-card accent-success">
                    <div class="stat-header">
                        <div>
                            <div class="stat-title">Present</div>
                            <div class="stat-value"><?php echo $stats['present']; ?></div>
                            <div class="stat-subtitle"><?php echo round(($stats['present'] / $stats['total']) * 100); ?>%</div>
                        </div>
                        <div class="stat-icon">✓</div>
                    </div>
                </div>

                <div class="stat-card accent-red">
                    <div class="stat-header">
                        <div>
                            <div class="stat-title">Absent</div>
                            <div class="stat-value"><?php echo $stats['absent']; ?></div>
                            <div class="stat-subtitle"><?php echo round(($stats['absent'] / $stats['total']) * 100); ?>%</div>
                        </div>
                        <div class="stat-icon">✗</div>
                    </div>
                </div>

                <div class="stat-card accent-warning">
                    <div class="stat-header">
                        <div>
                            <div class="stat-title">Excused</div>
                            <div class="stat-value"><?php echo $stats['excused']; ?></div>
                            <div class="stat-subtitle"><?php echo round(($stats['excused'] / $stats['total']) * 100); ?>%</div>
                        </div>
                        <div class="stat-icon">📝</div>
                    </div>
                </div>

                <div class="stat-card accent-info">
                    <div class="stat-header">
                        <div>
                            <div class="stat-title">Late</div>
                            <div class="stat-value"><?php echo $stats['late']; ?></div>
                            <div class="stat-subtitle"><?php echo round(($stats['late'] / $stats['total']) * 100); ?>%</div>
                        </div>
                        <div class="stat-icon">⏰</div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Mark Attendance Form -->
        <div class="data-table-wrapper">
            <div class="table-header">
                <h4 class="table-title">Mark Attendance</h4>
                <div class="table-actions">
                    <?php if (!empty($attendanceRecords)): ?>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="event_id" value="<?php echo $eventId; ?>">
                            <button type="submit" name="export_attendance" class="btn btn-secondary">
                                <span>📥</span> Export CSV
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo e($error); ?></div>
            <?php endif; ?>

            <?php if (empty($activeBrigaders)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">👥</div>
                    <div class="empty-state-title">No Active Brigaders</div>
                    <div class="empty-state-text">Add brigaders to the system to mark attendance.</div>
                    <a href="brigaders.php?action=create" class="btn btn-primary">Add Brigader</a>
                </div>
            <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="event_id" value="<?php echo $eventId; ?>">

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Brigade ID</th>
                                    <th>Name</th>
                                    <th>Unit</th>
                                    <th>Rank</th>
                                    <th>Attendance Status</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($activeBrigaders as $brigader): ?>
                                    <?php
                                    $existingRecord = $attendanceMap[$brigader['id']] ?? null;
                                    $currentStatus = $existingRecord ? $existingRecord['status'] : 'present';
                                    $currentNotes = $existingRecord ? $existingRecord['notes'] : '';
                                    ?>
                                    <tr>
                                        <td><strong><?php echo e($brigader['brigade_id']); ?></strong></td>
                                        <td><?php echo e($brigader['first_name'] . ' ' . $brigader['last_name']); ?></td>
                                        <td><?php echo e($brigader['unit']); ?></td>
                                        <td><?php echo e($brigader['rank_name'] ?? 'N/A'); ?></td>
                                        <td>
                                            <select name="attendance[<?php echo $brigader['id']; ?>]" class="form-select form-select-sm" style="width: 150px;">
                                                <option value="present" <?php echo $currentStatus === 'present' ? 'selected' : ''; ?>>Present</option>
                                                <option value="absent" <?php echo $currentStatus === 'absent' ? 'selected' : ''; ?>>Absent</option>
                                                <option value="excused" <?php echo $currentStatus === 'excused' ? 'selected' : ''; ?>>Excused</option>
                                                <option value="late" <?php echo $currentStatus === 'late' ? 'selected' : ''; ?>>Late</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="notes[<?php echo $brigader['id']; ?>]" 
                                                   class="form-control form-control-sm" 
                                                   placeholder="Optional notes" 
                                                   value="<?php echo e($currentNotes); ?>">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="submit" name="mark_attendance" class="btn btn-primary">Save Attendance</button>
                        <a href="events.php" class="btn btn-secondary">Back to Events</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>