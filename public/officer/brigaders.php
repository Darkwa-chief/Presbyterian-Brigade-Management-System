<?php
// public/officer/brigaders.php

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

requireRole(['admin', 'officer']);

$brigaderModel  = new Brigader();
$rankModel      = new Rank();
$pageTitle      = 'Brigaders';
$action         = $_GET['action'] ?? 'list';
$brigaderId     = $_GET['id'] ?? null;

// Get filters
$filters = [];
if (!empty($_GET['search']))    $filters['search']  = $_GET['search'];
if (!empty($_GET['status']))    $filters['status']  = $_GET['status'];
if (!empty($_GET['unit']))      $filters['unit']    = $_GET['unit'];
if (!empty($_GET['rank_id']))   $filters['rank_id'] = $_GET['rank_id'];

$currentPage = intval($_GET['page'] ?? 1);
$pagination  = paginate($brigaderModel->count($filters), $currentPage, ITEMS_PER_PAGE);
$brigaders   = $brigaderModel->getAll($filters, $pagination['items_per_page'], $pagination['offset']);
$ranks       = $rankModel->getAll();

// View single brigader
$brigader         = null;
$promotionHistory = [];
$attendanceHistory = [];

if ($action === 'view' && $brigaderId) {
    $brigader = $brigaderModel->getById($brigaderId);
    if (!$brigader) {
        setFlashMessage('danger', 'Brigader not found.');
        redirect(APP_URL . '/officer/brigaders.php');
    }
    $promotionHistory  = $brigaderModel->getPromotionHistory($brigaderId);
    $attendanceHistory = $brigaderModel->getAttendanceHistory($brigaderId);
}

include __DIR__ . '/../includes/header.php';
?>

<div class="content-area">
    <?php if ($action === 'list'): ?>
        <div class="data-table-wrapper">
            <div class="table-header">
                <h4 class="table-title">Brigaders</h4>
            </div>

            <!-- Filters -->
            <form method="GET" class="row g-3 mb-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" 
                           placeholder="Search name or ID" 
                           value="<?php echo e($_GET['search'] ?? ''); ?>">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="active" <?php echo ($_GET['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($_GET['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        <option value="transferred" <?php echo ($_GET['status'] ?? '') === 'transferred' ? 'selected' : ''; ?>>Transferred</option>
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
                    <a href="brigaders.php" class="btn btn-secondary">Clear</a>
                </div>
            </form>

            <?php if (empty($brigaders)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">👥</div>
                    <div class="empty-state-title">No Brigaders Found</div>
                    <div class="empty-state-text">No brigaders match your search.</div>
                </div>
            <?php else: ?>
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
                                    <td><?php echo ucfirst($b['gender']); ?></td>
                                    <td><?php echo e($b['rank_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo e($b['unit']); ?></td>
                                    <td><?php echo formatDate($b['join_date']); ?></td>
                                    <td><?php echo getStatusBadge($b['status']); ?></td>
                                    <td>
                                        <a href="?action=view&id=<?php echo $b['id']; ?>" 
                                           class="btn btn-sm btn-primary">View</a>
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

    <?php elseif ($action === 'view' && $brigader): ?>
        <!-- Profile View -->
        <div class="row">
            <div class="col-lg-4">
                <div class="glass-card text-center">
                    <?php if ($brigader['passport_photo']): ?>
                        <img src="<?php echo APP_URL . '/' . $brigader['passport_photo']; ?>" 
                             alt="Passport Photo" 
                             class="img-fluid rounded mb-3" 
                             style="max-width:180px;">
                    <?php else: ?>
                        <div style="font-size:80px;" class="mb-3">👤</div>
                    <?php endif; ?>

                    <h4 class="text-primary-blue">
                        <?php echo e($brigader['first_name'] . ' ' . $brigader['last_name']); ?>
                    </h4>
                    <p class="text-muted mb-1"><?php echo e($brigader['brigade_id']); ?></p>
                    <?php echo getStatusBadge($brigader['status']); ?>

                    <hr>

                    <div class="text-start small">
                        <p><strong>Rank:</strong> <?php echo e($brigader['rank_name'] ?? 'N/A'); ?></p>
                        <p><strong>Unit:</strong> <?php echo e($brigader['unit']); ?></p>
                        <p><strong>Gender:</strong> <?php echo ucfirst($brigader['gender']); ?></p>
                        <p><strong>Date of Birth:</strong> <?php echo formatDate($brigader['date_of_birth']); ?></p>
                        <p><strong>Join Date:</strong> <?php echo formatDate($brigader['join_date']); ?></p>
                        <?php if ($brigader['email']): ?>
                            <p><strong>Email:</strong> <?php echo e($brigader['email']); ?></p>
                        <?php endif; ?>
                        <?php if ($brigader['phone']): ?>
                            <p><strong>Phone:</strong> <?php echo e($brigader['phone']); ?></p>
                        <?php endif; ?>
                        <?php if ($brigader['address']): ?>
                            <p><strong>Address:</strong> <?php echo e($brigader['address']); ?></p>
                        <?php endif; ?>
                    </div>

                    <a href="brigaders.php" class="btn btn-secondary w-100 mt-2">
                        Back to List
                    </a>
                </div>
            </div>

            <div class="col-lg-8">
                <!-- Promotion History -->
                <div class="glass-card mb-3">
                    <h5 class="text-primary-blue mb-3">Promotion History</h5>
                    <?php if (empty($promotionHistory)): ?>
                        <p class="text-muted">No promotion history available.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
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

                <!-- Attendance History -->
                <div class="glass-card">
                    <h5 class="text-primary-blue mb-3">Recent Attendance</h5>
                    <?php if (empty($attendanceHistory)): ?>
                        <p class="text-muted">No attendance records available.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
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