<?php
// public/admin/events.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Event.php';
require_once __DIR__ . '/../../classes/Notification.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['admin', 'officer']);

$eventModel = new Event();
$pageTitle  = 'Events';
$action     = $_GET['action'] ?? 'list';
$eventId    = $_GET['id'] ?? null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['csrf_token'])) {
        validateCSRFToken($_POST['csrf_token']);
    }

    if (isset($_POST['create_event'])) {
        $data = [
            'event_name'  => sanitizeInput($_POST['event_name']),
            'event_type'  => $_POST['event_type'],
            'event_date'  => $_POST['event_date'],
            'event_time'  => $_POST['event_time'] ?: null,
            'location'    => sanitizeInput($_POST['location']),
            'description' => sanitizeInput($_POST['description']),
            'status'      => $_POST['status'],
            'created_by'  => $_SESSION['user_id']
        ];
        try {
            $newEventId = $eventModel->create($data);
            logAudit($_SESSION['user_id'], 'create_event', 'events', $newEventId, null, $data);
            setFlashMessage('success', 'Event created successfully.');
            redirect(APP_URL . '/admin/events.php');
        } catch (Exception $e) {
            $error = 'Failed to create event: ' . $e->getMessage();
        }

    } elseif (isset($_POST['update_event'])) {
        $data = [
            'event_name'  => sanitizeInput($_POST['event_name']),
            'event_type'  => $_POST['event_type'],
            'event_date'  => $_POST['event_date'],
            'event_time'  => $_POST['event_time'] ?: null,
            'location'    => sanitizeInput($_POST['location']),
            'description' => sanitizeInput($_POST['description']),
            'status'      => $_POST['status']
        ];
        try {
            $eventModel->update($_POST['event_id'], $data);
            logAudit($_SESSION['user_id'], 'update_event', 'events', $_POST['event_id'], null, $data);
            setFlashMessage('success', 'Event updated successfully.');
            redirect(APP_URL . '/admin/events.php');
        } catch (Exception $e) {
            $error = 'Failed to update event: ' . $e->getMessage();
        }

    } elseif (isset($_POST['mark_completed'])) {
        try {
            $eventModel->update($_POST['event_id'], ['status' => 'completed']);
            logAudit($_SESSION['user_id'], 'complete_event', 'events', $_POST['event_id']);
            setFlashMessage('success', 'Event marked as completed.');
            redirect(APP_URL . '/admin/events.php');
        } catch (Exception $e) {
            setFlashMessage('danger', 'Failed to update event.');
            redirect(APP_URL . '/admin/events.php');
        }

    } elseif (isset($_POST['mark_cancelled'])) {
        try {
            $eventModel->update($_POST['event_id'], ['status' => 'cancelled']);
            logAudit($_SESSION['user_id'], 'cancel_event', 'events', $_POST['event_id']);
            setFlashMessage('success', 'Event cancelled.');
            redirect(APP_URL . '/admin/events.php');
        } catch (Exception $e) {
            setFlashMessage('danger', 'Failed to cancel event.');
            redirect(APP_URL . '/admin/events.php');
        }

    } elseif (isset($_POST['delete_event'])) {
        try {
            $eventModel->delete($_POST['event_id']);
            logAudit($_SESSION['user_id'], 'delete_event', 'events', $_POST['event_id']);
            setFlashMessage('success', 'Event deleted successfully.');
            redirect(APP_URL . '/admin/events.php');
        } catch (Exception $e) {
            setFlashMessage('danger', 'Failed to delete event: ' . $e->getMessage());
            redirect(APP_URL . '/admin/events.php');
        }
    }
}

// Get event data for editing
$event = null;
if (($action === 'edit' || $action === 'view') && $eventId) {
    $event = $eventModel->getById($eventId);
    if (!$event) {
        setFlashMessage('danger', 'Event not found.');
        redirect(APP_URL . '/admin/events.php');
    }
}

// Get all events with filters
$filters = [];
if (!empty($_GET['status']))     $filters['status']     = $_GET['status'];
if (!empty($_GET['event_type'])) $filters['event_type'] = $_GET['event_type'];
if (!empty($_GET['date_from']))  $filters['date_from']  = $_GET['date_from'];
if (!empty($_GET['date_to']))    $filters['date_to']    = $_GET['date_to'];

$currentPage = intval($_GET['page'] ?? 1);
$totalEvents = count($eventModel->getAll($filters));
$pagination  = paginate($totalEvents, $currentPage, ITEMS_PER_PAGE);
$events      = $eventModel->getAll($filters, $pagination['items_per_page'], $pagination['offset']);

include __DIR__ . '/../includes/header.php';
?>

<div class="content-area">
    <?php if ($action === 'list'): ?>

        <div class="data-table-wrapper">
            <div class="table-header">
                <h4 class="table-title">Events & Activities</h4>
                <div class="table-actions">
                    <a href="?action=create" class="btn btn-primary">
                        <span>➕</span> Create Event
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <form method="GET" class="row g-3 mb-3">
                <div class="col-md-3">
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
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="scheduled"
                            <?php echo ($_GET['status'] ?? '') === 'scheduled' ? 'selected' : ''; ?>>
                            Scheduled
                        </option>
                        <option value="ongoing"
                            <?php echo ($_GET['status'] ?? '') === 'ongoing' ? 'selected' : ''; ?>>
                            Ongoing
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
                <div class="col-md-2">
                    <input type="date" class="form-control" name="date_from"
                           value="<?php echo e($_GET['date_from'] ?? ''); ?>">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="date_to"
                           value="<?php echo e($_GET['date_to'] ?? ''); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>

            <?php if (empty($events)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📅</div>
                    <div class="empty-state-title">No Events Found</div>
                    <div class="empty-state-text">Start by creating your first event.</div>
                    <a href="?action=create" class="btn btn-primary">Create Event</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Event Name</th>
                                <th>Type</th>
                                <th>Date & Time</th>
                                <th>Location</th>
                                <th>Attendance</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $e): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo e($e['event_name']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            <?php echo ucfirst($e['event_type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo formatDate($e['event_date']); ?>
                                        <?php if ($e['event_time']): ?>
                                            <br>
                                            <small class="text-muted">
                                                <?php echo date('g:i A', strtotime($e['event_time'])); ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($e['location']); ?></td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?php echo $e['total_attendance']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo getStatusBadge($e['status']); ?></td>

                                    <!-- ====== ACTIONS COLUMN ====== -->
                                    <td>
                                        <div class="d-flex gap-1 flex-wrap">

                                            <!-- Attendance -->
                                            <a href="attendance.php?event_id=<?php echo $e['id']; ?>"
                                               class="btn btn-sm btn-primary">
                                                Attendance
                                            </a>

                                            <!-- Mark Complete -->
                                            <?php if ($e['status'] === 'scheduled' || $e['status'] === 'ongoing'): ?>
                                                <form method="POST" class="d-inline"
                                                      onsubmit="return confirm('Mark this event as completed?');">
                                                    <input type="hidden" name="csrf_token"
                                                           value="<?php echo generateCSRFToken(); ?>">
                                                    <input type="hidden" name="event_id"
                                                           value="<?php echo $e['id']; ?>">
                                                    <button type="submit"
                                                            name="mark_completed"
                                                            class="btn btn-sm btn-success">
                                                        ✓ Complete
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <!-- Cancel -->
                                            <?php if ($e['status'] === 'scheduled'): ?>
                                                <form method="POST" class="d-inline"
                                                      onsubmit="return confirm('Cancel this event?');">
                                                    <input type="hidden" name="csrf_token"
                                                           value="<?php echo generateCSRFToken(); ?>">
                                                    <input type="hidden" name="event_id"
                                                           value="<?php echo $e['id']; ?>">
                                                    <button type="submit"
                                                            name="mark_cancelled"
                                                            class="btn btn-sm btn-warning">
                                                        ✗ Cancel
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <!-- Edit -->
                                            <a href="?action=edit&id=<?php echo $e['id']; ?>"
                                               class="btn btn-sm btn-secondary">
                                                Edit
                                            </a>

                                            <!-- Delete -->
                                            <form method="POST" class="d-inline"
                                                  onsubmit="return confirm('Permanently delete this event and all its attendance records?');">
                                                <input type="hidden" name="csrf_token"
                                                       value="<?php echo generateCSRFToken(); ?>">
                                                <input type="hidden" name="event_id"
                                                       value="<?php echo $e['id']; ?>">
                                                <button type="submit"
                                                        name="delete_event"
                                                        class="btn btn-sm btn-danger">
                                                    🗑️ Delete
                                                </button>
                                            </form>

                                        </div>
                                    </td>
                                    <!-- ====== END ACTIONS COLUMN ====== -->

                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php echo renderPagination(
                    $pagination,
                    'events.php?' . http_build_query($filters)
                ); ?>

            <?php endif; ?>
        </div>

    <?php elseif ($action === 'create' || $action === 'edit'): ?>

        <!-- Event Form -->
        <div class="glass-card" style="max-width:700px;">
            <h4 class="text-primary-blue mb-4">
                <?php echo $action === 'create' ? 'Create New Event' : 'Edit Event'; ?>
            </h4>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo e($error); ?></div>
            <?php endif; ?>

            <form method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="csrf_token"
                       value="<?php echo generateCSRFToken(); ?>">
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="event_id"
                           value="<?php echo $event['id']; ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="event_name" class="form-label">Event Name</label>
                    <input type="text" class="form-control" id="event_name"
                           name="event_name"
                           value="<?php echo e($event['event_name'] ?? ''); ?>"
                           required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="event_type" class="form-label">Event Type</label>
                        <select class="form-select" id="event_type"
                                name="event_type" required>
                            <option value="">Select Type</option>
                            <option value="meeting"
                                <?php echo ($event['event_type'] ?? '') === 'meeting' ? 'selected' : ''; ?>>
                                Meeting
                            </option>
                            <option value="training"
                                <?php echo ($event['event_type'] ?? '') === 'training' ? 'selected' : ''; ?>>
                                Training
                            </option>
                            <option value="service"
                                <?php echo ($event['event_type'] ?? '') === 'service' ? 'selected' : ''; ?>>
                                Service
                            </option>
                            <option value="competition"
                                <?php echo ($event['event_type'] ?? '') === 'competition' ? 'selected' : ''; ?>>
                                Competition
                            </option>
                            <option value="other"
                                <?php echo ($event['event_type'] ?? '') === 'other' ? 'selected' : ''; ?>>
                                Other
                            </option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status"
                                name="status" required>
                            <option value="scheduled"
                                <?php echo ($event['status'] ?? 'scheduled') === 'scheduled' ? 'selected' : ''; ?>>
                                Scheduled
                            </option>
                            <option value="ongoing"
                                <?php echo ($event['status'] ?? '') === 'ongoing' ? 'selected' : ''; ?>>
                                Ongoing
                            </option>
                            <option value="completed"
                                <?php echo ($event['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>
                                Completed
                            </option>
                            <option value="cancelled"
                                <?php echo ($event['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>
                                Cancelled
                            </option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="event_date" class="form-label">Event Date</label>
                        <input type="date" class="form-control" id="event_date"
                               name="event_date"
                               value="<?php echo e($event['event_date'] ?? ''); ?>"
                               required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="event_time" class="form-label">
                            Event Time (Optional)
                        </label>
                        <input type="time" class="form-control" id="event_time"
                               name="event_time"
                               value="<?php echo e($event['event_time'] ?? ''); ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="location" class="form-label">Location</label>
                    <input type="text" class="form-control" id="location"
                           name="location"
                           value="<?php echo e($event['location'] ?? ''); ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">
                        Description (Optional)
                    </label>
                    <textarea class="form-control" id="description"
                              name="description"
                              rows="4"><?php echo e($event['description'] ?? ''); ?></textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit"
                            name="<?php echo $action === 'create' ? 'create_event' : 'update_event'; ?>"
                            class="btn btn-primary">
                        <?php echo $action === 'create' ? 'Create Event' : 'Update Event'; ?>
                    </button>
                    <a href="events.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>

    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>