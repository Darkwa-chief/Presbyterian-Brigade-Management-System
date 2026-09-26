<?php
// public/admin/brigaders.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Brigader.php';
require_once __DIR__ . '/../../classes/Rank.php';
require_once __DIR__ . '/../../classes/Notification.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole('admin');

$brigaderModel = new Brigader();
$rankModel     = new Rank();
$db            = Database::getInstance();
$pageTitle     = 'Brigaders';
$action        = $_GET['action'] ?? 'list';
$brigaderId    = $_GET['id'] ?? null;

// ── POST HANDLERS ───────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['csrf_token'])) {
        validateCSRFToken($_POST['csrf_token']);
    }

    if (isset($_POST['create_brigader'])) {
        $data = [
            'first_name'    => sanitizeInput($_POST['first_name']),
            'last_name'     => sanitizeInput($_POST['last_name']),
            'date_of_birth' => $_POST['date_of_birth'],
            'gender'        => $_POST['gender'],
            'email'         => sanitizeInput($_POST['email']),
            'phone'         => sanitizeInput($_POST['phone']),
            'address'       => sanitizeInput($_POST['address']),
            'rank_id'       => $_POST['rank_id'] ?: null,
            'unit'          => sanitizeInput($_POST['unit']),
            'join_date'     => $_POST['join_date'],
            'status'        => $_POST['status'],
            'created_by'    => $_SESSION['user_id'],
        ];

        try {
            $newBrigaderId = $brigaderModel->create($data);

            if (isset($_FILES['passport_photo']) && $_FILES['passport_photo']['error'] === UPLOAD_ERR_OK) {
                try {
                    $brigaderModel->uploadPassport($newBrigaderId, $_FILES['passport_photo']);
                } catch (Exception $e) { /* silent */ }
            }

            logAudit($_SESSION['user_id'], 'create_brigader', 'brigaders', $newBrigaderId, null, $data);
            setFlashMessage('success', 'Brigader created successfully.');
            redirect('brigaders.php');
        } catch (Exception $e) {
            $error = 'Failed to create brigader: ' . $e->getMessage();
        }

    } elseif (isset($_POST['update_brigader'])) {
        $data = [
            'first_name'    => sanitizeInput($_POST['first_name']),
            'last_name'     => sanitizeInput($_POST['last_name']),
            'date_of_birth' => $_POST['date_of_birth'],
            'gender'        => $_POST['gender'],
            'email'         => sanitizeInput($_POST['email']),
            'phone'         => sanitizeInput($_POST['phone']),
            'address'       => sanitizeInput($_POST['address']),
            'rank_id'       => $_POST['rank_id'] ?: null,
            'unit'          => sanitizeInput($_POST['unit']),
            'join_date'     => $_POST['join_date'],
            'status'        => $_POST['status'],
        ];

        try {
            $brigaderModel->update($_POST['brigader_id'], $data);

            if (isset($_FILES['passport_photo']) && $_FILES['passport_photo']['error'] === UPLOAD_ERR_OK) {
                try {
                    $brigaderModel->uploadPassport($_POST['brigader_id'], $_FILES['passport_photo']);
                } catch (Exception $e) { /* silent */ }
            }

            logAudit($_SESSION['user_id'], 'update_brigader', 'brigaders', $_POST['brigader_id'], null, $data);
            setFlashMessage('success', 'Brigader updated successfully.');
            redirect('brigaders.php');
        } catch (Exception $e) {
            $error = 'Failed to update brigader: ' . $e->getMessage();
        }

    } elseif (isset($_POST['delete_brigader'])) {
        try {
            $brigaderModel->delete($_POST['brigader_id']);
            logAudit($_SESSION['user_id'], 'delete_brigader', 'brigaders', $_POST['brigader_id']);
            setFlashMessage('success', 'Brigader deleted successfully.');
            redirect('brigaders.php');
        } catch (Exception $e) {
            setFlashMessage('danger', 'Failed to delete brigader: ' . $e->getMessage());
            redirect('brigaders.php');
        }

    } elseif (isset($_POST['export_csv'])) {
        $exportFilters = [];
        if (!empty($_POST['search']))  $exportFilters['search']  = $_POST['search'];
        if (!empty($_POST['status']))  $exportFilters['status']  = $_POST['status'];
        if (!empty($_POST['rank_id'])) $exportFilters['rank_id'] = $_POST['rank_id'];
        if (!empty($_POST['unit']))    $exportFilters['unit']    = $_POST['unit'];
        if (!empty($_POST['gender']))  $exportFilters['gender']  = $_POST['gender'];

        $brigaders = $brigaderModel->getAll($exportFilters);

        $genderLabel = !empty($exportFilters['gender']) ? '_' . $exportFilters['gender'] : '';
        $filename    = 'brigaders' . $genderLabel . '_' . date('Y-m-d') . '.csv';

        $csvData = [];
        foreach ($brigaders as $b) {
            $csvData[] = [
                'Brigade ID'    => $b['brigade_id'],
                'First Name'    => $b['first_name'],
                'Last Name'     => $b['last_name'],
                'Gender'        => ucfirst($b['gender']),
                'Date of Birth' => $b['date_of_birth'],
                'Email'         => $b['email'],
                'Phone'         => $b['phone'],
                'Rank'          => $b['rank_name'] ?? 'N/A',
                'Unit'          => $b['unit'],
                'Join Date'     => $b['join_date'],
                'Status'        => ucfirst($b['status']),
            ];
        }

        exportToCSV($csvData, $filename);
    }
}

// ── LOAD SINGLE BRIGADER ────────────────────────────────────────────────────
$brigader = null;
if ($action === 'edit' && $brigaderId) {
    $brigader = $brigaderModel->getById($brigaderId);
    if (!$brigader) {
        setFlashMessage('danger', 'Brigader not found.');
        redirect('brigaders.php');
    }
} elseif ($action === 'view' && $brigaderId) {
    $brigader = $brigaderModel->getById($brigaderId);
    if (!$brigader) {
        setFlashMessage('danger', 'Brigader not found.');
        redirect('brigaders.php');
    }
    $promotionHistory  = $brigaderModel->getPromotionHistory($brigaderId);
    $attendanceHistory = $brigaderModel->getAttendanceHistory($brigaderId);
}

// ── FILTERS ─────────────────────────────────────────────────────────────────
$filters = [];
if (!empty($_GET['search']))  $filters['search']  = $_GET['search'];
if (!empty($_GET['status']))  $filters['status']  = $_GET['status'];
if (!empty($_GET['unit']))    $filters['unit']    = $_GET['unit'];
if (!empty($_GET['rank_id'])) $filters['rank_id'] = $_GET['rank_id'];
if (!empty($_GET['gender']))  $filters['gender']  = $_GET['gender'];

$currentPage = intval($_GET['page'] ?? 1);
$pagination  = paginate($brigaderModel->count($filters), $currentPage, ITEMS_PER_PAGE);
$brigaders   = $brigaderModel->getAll($filters, $pagination['items_per_page'], $pagination['offset']);

// Gender counts for tabs (always based on active only)
$totalMale   = $db->fetchOne(
    "SELECT COUNT(*) as count FROM brigaders WHERE gender = 'male' AND status = 'active'"
)['count'];
$totalFemale = $db->fetchOne(
    "SELECT COUNT(*) as count FROM brigaders WHERE gender = 'female' AND status = 'active'"
)['count'];
$totalAll    = $db->fetchOne(
    "SELECT COUNT(*) as count FROM brigaders WHERE status = 'active'"
)['count'];

// All ranks for dropdowns
$ranks = $rankModel->getAll();

// Current gender tab
$currentGender = $_GET['gender'] ?? '';

include __DIR__ . '/../includes/header.php';
?>

<div class="content-area">
    <?php if ($action === 'list'): ?>

        <div class="data-table-wrapper">
            <div class="table-header">
                <h4 class="table-title">Brigaders</h4>
                <div class="table-actions">
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <?php foreach ($filters as $key => $value): ?>
                            <input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e($value); ?>">
                        <?php endforeach; ?>
                        <button type="submit" name="export_csv" class="btn btn-secondary">
                            Export CSV
                        </button>
                    </form>
                    <a href="?action=create" class="btn btn-primary">Add Brigader</a>
                </div>
            </div>

            <!-- ── Gender Tabs ──────────────────────────────────────────── -->
            <ul class="nav nav-tabs mb-3">
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentGender === '' ? 'active' : ''; ?>"
                       href="brigaders.php">
                        All
                        <span class="badge badge-secondary ms-1"><?php echo $totalAll; ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentGender === 'male' ? 'active' : ''; ?>"
                       href="brigaders.php?gender=male">
                        Boys
                        <span class="badge ms-1" style="background:#1565c0; color:#fff;">
                            <?php echo $totalMale; ?>
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentGender === 'female' ? 'active' : ''; ?>"
                       href="brigaders.php?gender=female">
                        Girls
                        <span class="badge ms-1" style="background:#c2185b; color:#fff;">
                            <?php echo $totalFemale; ?>
                        </span>
                    </a>
                </li>
            </ul>

            <!-- ── Filters ─────────────────────────────────────────────── -->
            <form method="GET" class="row g-3 mb-3">
                <?php if ($currentGender !== ''): ?>
                    <input type="hidden" name="gender" value="<?php echo e($currentGender); ?>">
                <?php endif; ?>

                <div class="col-md-3">
                    <input type="text" class="form-control" name="search"
                           placeholder="Search by name or ID"
                           value="<?php echo e($_GET['search'] ?? ''); ?>">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="active"
                            <?php echo ($_GET['status'] ?? '') === 'active' ? 'selected' : ''; ?>>
                            Active
                        </option>
                        <option value="inactive"
                            <?php echo ($_GET['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>
                            Inactive
                        </option>
                        <option value="transferred"
                            <?php echo ($_GET['status'] ?? '') === 'transferred' ? 'selected' : ''; ?>>
                            Transferred
                        </option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="rank_id">
                        <option value="">All Ranks</option>
                        <?php foreach ($ranks as $rank): ?>
                            <option value="<?php echo $rank['id']; ?>"
                                <?php echo ($_GET['rank_id'] ?? '') == $rank['id'] ? 'selected' : ''; ?>>
                                <?php echo e($rank['rank_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control" name="unit"
                           placeholder="Unit"
                           value="<?php echo e($_GET['unit'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="brigaders.php<?php echo $currentGender !== '' ? '?gender=' . e($currentGender) : ''; ?>"
                       class="btn btn-secondary">
                        Clear
                    </a>
                </div>
            </form>

            <!-- ── Results ─────────────────────────────────────────────── -->
            <?php if (empty($brigaders)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">&#128100;</div>
                    <div class="empty-state-title">No Brigaders Found</div>
                    <div class="empty-state-text">
                        <?php if (!empty($filters)): ?>
                            No brigaders match your search criteria.
                        <?php else: ?>
                            Start by adding your first brigader.
                        <?php endif; ?>
                    </div>
                    <a href="?action=create" class="btn btn-primary">Add Brigader</a>
                </div>
            <?php else: ?>
                <p class="text-muted mb-2">
                    Showing
                    <strong>
                        <?php
                        if ($currentGender === 'male')        echo 'Boys';
                        elseif ($currentGender === 'female')  echo 'Girls';
                        else                                   echo 'All Brigaders';
                        ?>
                    </strong>
                    &mdash; <?php echo $pagination['total_items']; ?> record(s)
                </p>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Brigade ID</th>
                                <th>Name</th>
                                <th>Gender</th>
                                <th>Rank</th>
                                <th>Unit</th>
                                <th>Join Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($brigaders as $b): ?>
                                <tr>
                                    <td><strong><?php echo e($b['brigade_id']); ?></strong></td>
                                    <td><?php echo e($b['first_name'] . ' ' . $b['last_name']); ?></td>
                                    <td>
                                        <?php if ($b['gender'] === 'male'): ?>
                                            <span class="badge" style="background:#1565c0;">Male</span>
                                        <?php else: ?>
                                            <span class="badge" style="background:#c2185b;">Female</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($b['rank_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo e($b['unit']); ?></td>
                                    <td><?php echo formatDate($b['join_date']); ?></td>
                                    <td><?php echo getStatusBadge($b['status']); ?></td>
                                    <td>
                                        <a href="?action=view&id=<?php echo $b['id']; ?>"
                                           class="btn btn-sm btn-primary">View</a>
                                        <a href="?action=edit&id=<?php echo $b['id']; ?>"
                                           class="btn btn-sm btn-secondary">Edit</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php echo renderPagination(
                    $pagination,
                    'brigaders.php?' . http_build_query(array_merge($filters, ['action' => 'list']))
                ); ?>
            <?php endif; ?>
        </div>

    <?php elseif ($action === 'create' || $action === 'edit'): ?>

        <div class="glass-card" style="max-width:800px;">
            <h4 class="text-primary-blue mb-4">
                <?php echo $action === 'create' ? 'Add New Brigader' : 'Edit Brigader'; ?>
            </h4>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo e($error); ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="brigader_id" value="<?php echo $brigader['id']; ?>">
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">First Name</label>
                        <input type="text" class="form-control" name="first_name"
                               value="<?php echo e($brigader['first_name'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Last Name</label>
                        <input type="text" class="form-control" name="last_name"
                               value="<?php echo e($brigader['last_name'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" name="date_of_birth"
                               value="<?php echo e($brigader['date_of_birth'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Gender</label>
                        <select class="form-select" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="male"
                                <?php echo ($brigader['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>
                                Male (Boy)
                            </option>
                            <option value="female"
                                <?php echo ($brigader['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>
                                Female (Girl)
                            </option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email"
                               value="<?php echo e($brigader['email'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone</label>
                        <input type="tel" class="form-control" name="phone"
                               value="<?php echo e($brigader['phone'] ?? ''); ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea class="form-control" name="address"
                              rows="2"><?php echo e($brigader['address'] ?? ''); ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Rank</label>
                        <select class="form-select" name="rank_id">
                            <option value="">No Rank</option>
                            <?php foreach ($ranks as $rank): ?>
                                <option value="<?php echo $rank['id']; ?>"
                                    <?php echo ($brigader['rank_id'] ?? '') == $rank['id'] ? 'selected' : ''; ?>>
                                    <?php echo e($rank['rank_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Unit</label>
                        <input type="text" class="form-control" name="unit"
                               value="<?php echo e($brigader['unit'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Join Date</label>
                        <input type="date" class="form-control" name="join_date"
                               value="<?php echo e($brigader['join_date'] ?? date('Y-m-d')); ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" required>
                            <option value="active"
                                <?php echo ($brigader['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>
                                Active
                            </option>
                            <option value="inactive"
                                <?php echo ($brigader['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>
                                Inactive
                            </option>
                            <option value="transferred"
                                <?php echo ($brigader['status'] ?? '') === 'transferred' ? 'selected' : ''; ?>>
                                Transferred
                            </option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Passport Photo</label>
                        <input type="file" class="form-control" name="passport_photo"
                               accept="image/jpeg,image/jpg,image/png">
                        <div class="form-text">Max 5MB. JPG, JPEG, or PNG only.</div>
                    </div>
                </div>

                <?php if ($action === 'edit' && !empty($brigader['passport_photo'])): ?>
                    <div class="mb-3">
                        <label class="form-label">Current Passport Photo</label>
                        <div>
                            <img src="<?php echo APP_URL . '/' . $brigader['passport_photo']; ?>"
                                 alt="Passport Photo"
                                 style="max-width:150px; border-radius:8px;">
                        </div>
                    </div>
                <?php endif; ?>

                <div class="d-flex gap-2">
                    <button type="submit"
                            name="<?php echo $action === 'create' ? 'create_brigader' : 'update_brigader'; ?>"
                            class="btn btn-primary">
                        <?php echo $action === 'create' ? 'Add Brigader' : 'Update Brigader'; ?>
                    </button>
                    <a href="brigaders.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>

    <?php elseif ($action === 'view'): ?>

        <div class="row">
            <div class="col-lg-4">
                <div class="glass-card text-center">
                    <?php if ($brigader['passport_photo']): ?>
                        <img src="<?php echo APP_URL . '/' . $brigader['passport_photo']; ?>"
                             alt="Passport Photo"
                             class="img-fluid rounded mb-3"
                             style="max-width:200px;">
                    <?php else: ?>
                        <div class="mb-3"
                             style="font-size:80px; color:#ccc;">&#128100;</div>
                    <?php endif; ?>

                    <h4 class="text-primary-blue">
                        <?php echo e($brigader['first_name'] . ' ' . $brigader['last_name']); ?>
                    </h4>
                    <p class="text-muted mb-2"><?php echo e($brigader['brigade_id']); ?></p>
                    <?php echo getStatusBadge($brigader['status']); ?>

                    <hr>

                    <div class="text-start">
                        <p><strong>Rank:</strong> <?php echo e($brigader['rank_name'] ?? 'N/A'); ?></p>
                        <p><strong>Unit:</strong> <?php echo e($brigader['unit']); ?></p>
                        <p>
                            <strong>Gender:</strong>
                            <?php if ($brigader['gender'] === 'male'): ?>
                                <span class="badge" style="background:#1565c0;">Male</span>
                            <?php else: ?>
                                <span class="badge" style="background:#c2185b;">Female</span>
                            <?php endif; ?>
                        </p>
                        <p><strong>Date of Birth:</strong>
                            <?php echo formatDate($brigader['date_of_birth']); ?>
                        </p>
                        <p><strong>Join Date:</strong>
                            <?php echo formatDate($brigader['join_date']); ?>
                        </p>
                        <?php if ($brigader['email']): ?>
                            <p><strong>Email:</strong> <?php echo e($brigader['email']); ?></p>
                        <?php endif; ?>
                        <?php if ($brigader['phone']): ?>
                            <p><strong>Phone:</strong> <?php echo e($brigader['phone']); ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="mt-3">
                        <a href="?action=edit&id=<?php echo $brigader['id']; ?>"
                           class="btn btn-primary w-100 mb-2">
                            Edit Profile
                        </a>
                        <a href="brigaders.php" class="btn btn-secondary w-100">
                            Back to List
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="glass-card mb-3">
                    <h5 class="text-primary-blue mb-3">Promotion History</h5>
                    <?php if (empty($promotionHistory)): ?>
                        <p class="text-muted">No promotion history available.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>From Rank</th>
                                        <th>To Rank</th>
                                        <th>Date</th>
                                        <th>Approved By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($promotionHistory as $promo): ?>
                                        <tr>
                                            <td><?php echo e($promo['from_rank_name'] ?? 'N/A'); ?></td>
                                            <td><strong><?php echo e($promo['to_rank_name']); ?></strong></td>
                                            <td><?php echo formatDate($promo['promotion_date']); ?></td>
                                            <td><?php echo e($promo['approved_by_name'] ?? 'N/A'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="glass-card">
                    <h5 class="text-primary-blue mb-3">Recent Attendance</h5>
                    <?php if (empty($attendanceHistory)): ?>
                        <p class="text-muted">No attendance records available.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Event</th>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($attendanceHistory as $att): ?>
                                        <tr>
                                            <td><?php echo e($att['event_name']); ?></td>
                                            <td><?php echo formatDate($att['event_date']); ?></td>
                                            <td><?php echo ucfirst($att['event_type']); ?></td>
                                            <td><?php echo getStatusBadge($att['status']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>