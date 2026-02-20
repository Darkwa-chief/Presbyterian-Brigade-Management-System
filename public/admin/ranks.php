<?php
// public/admin/ranks.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Rank.php';
require_once __DIR__ . '/../../classes/Brigader.php';
require_once __DIR__ . '/../../classes/Notification.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole('admin');

$rankModel = new Rank();
$brigaderModel = new Brigader();
$pageTitle = 'Ranks & Promotions';
$action = $_GET['action'] ?? 'list';
$rankId = $_GET['id'] ?? null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['csrf_token'])) {
        validateCSRFToken($_POST['csrf_token']);
    }
    
    if (isset($_POST['create_rank'])) {
        $data = [
            'rank_name' => sanitizeInput($_POST['rank_name']),
            'rank_level' => $_POST['rank_level'],
            'description' => sanitizeInput($_POST['description']),
            'requirements' => sanitizeInput($_POST['requirements'])
        ];
        
        try {
            $newRankId = $rankModel->create($data);
            logAudit($_SESSION['user_id'], 'create_rank', 'ranks', $newRankId, null, $data);
            setFlashMessage('success', 'Rank created successfully.');
            redirect('ranks.php');
        } catch (Exception $e) {
            $error = 'Failed to create rank: ' . $e->getMessage();
        }
    } elseif (isset($_POST['update_rank'])) {
        $data = [
            'rank_name' => sanitizeInput($_POST['rank_name']),
            'rank_level' => $_POST['rank_level'],
            'description' => sanitizeInput($_POST['description']),
            'requirements' => sanitizeInput($_POST['requirements'])
        ];
        
        try {
            $rankModel->update($_POST['rank_id'], $data);
            logAudit($_SESSION['user_id'], 'update_rank', 'ranks', $_POST['rank_id'], null, $data);
            setFlashMessage('success', 'Rank updated successfully.');
            redirect('ranks.php');
        } catch (Exception $e) {
            $error = 'Failed to update rank: ' . $e->getMessage();
        }
    } elseif (isset($_POST['delete_rank'])) {
        try {
            $rankModel->delete($_POST['rank_id']);
            logAudit($_SESSION['user_id'], 'delete_rank', 'ranks', $_POST['rank_id']);
            setFlashMessage('success', 'Rank deleted successfully.');
            redirect('ranks.php');
        } catch (Exception $e) {
            setFlashMessage('danger', 'Failed to delete rank: ' . $e->getMessage());
            redirect('ranks.php');
        }
    } elseif (isset($_POST['promote_brigader'])) {
        try {
            $rankModel->promoteBrigader(
                $_POST['brigader_id'],
                $_POST['to_rank_id'],
                $_SESSION['user_id'],
                sanitizeInput($_POST['notes'])
            );
            logAudit($_SESSION['user_id'], 'promote_brigader', 'promotions', $_POST['brigader_id']);
            setFlashMessage('success', 'Brigader promoted successfully.');
            redirect('ranks.php?action=promote');
        } catch (Exception $e) {
            $error = 'Promotion failed: ' . $e->getMessage();
        }
    }
}

// Get rank data for editing
$rank = null;
if ($action === 'edit' && $rankId) {
    $rank = $rankModel->getById($rankId);
    if (!$rank) {
        setFlashMessage('danger', 'Rank not found.');
        redirect('ranks.php');
    }
}

// Get all ranks and brigaders
$ranks = $rankModel->getAll();
$activeBrigaders = $brigaderModel->getAll(['status' => 'active']);

include __DIR__ . '/../includes/header.php';
?>

<div class="content-area">
    <?php if ($action === 'list'): ?>
        <!-- Ranks List -->
        <div class="data-table-wrapper">
            <div class="table-header">
                <h4 class="table-title">Rank Management</h4>
                <div class="table-actions">
                    <a href="?action=promote" class="btn btn-secondary">
                        <span>⬆️</span> Promote Brigader
                    </a>
                    <a href="?action=create" class="btn btn-primary">
                        <span>➕</span> Add Rank
                    </a>
                </div>
            </div>

            <?php if (empty($ranks)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">🎖️</div>
                    <div class="empty-state-title">No Ranks Defined</div>
                    <div class="empty-state-text">Create your first rank to get started.</div>
                    <a href="?action=create" class="btn btn-primary">Add Rank</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Level</th>
                                <th>Rank Name</th>
                                <th>Description</th>
                                <th>Requirements</th>
                                <th>Brigaders</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ranks as $r): ?>
                                <?php
                                $count = $brigaderModel->count(['rank_id' => $r['id'], 'status' => 'active']);
                                ?>
                                <tr>
                                    <td><strong><?php echo $r['rank_level']; ?></strong></td>
                                    <td><?php echo e($r['rank_name']); ?></td>
                                    <td><?php echo e($r['description']); ?></td>
                                    <td><?php echo e($r['requirements']); ?></td>
                                    <td><span class="badge badge-primary"><?php echo $count; ?></span></td>
                                    <td>
                                        <a href="?action=edit&id=<?php echo $r['id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                                        <?php if ($count == 0): ?>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this rank?');">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                <input type="hidden" name="rank_id" value="<?php echo $r['id']; ?>">
                                                <button type="submit" name="delete_rank" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    <?php elseif ($action === 'create' || $action === 'edit'): ?>
        <!-- Rank Form -->
        <div class="glass-card" style="max-width: 600px;">
            <h4 class="text-primary-blue mb-4"><?php echo $action === 'create' ? 'Create New Rank' : 'Edit Rank'; ?></h4>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo e($error); ?></div>
            <?php endif; ?>

            <form method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="rank_id" value="<?php echo $rank['id']; ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="rank_name" class="form-label">Rank Name</label>
                    <input type="text" class="form-control" id="rank_name" name="rank_name" 
                           value="<?php echo e($rank['rank_name'] ?? ''); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="rank_level" class="form-label">Rank Level</label>
                    <input type="number" class="form-control" id="rank_level" name="rank_level" 
                           value="<?php echo e($rank['rank_level'] ?? ''); ?>" min="1" required>
                    <div class="form-text">Lower numbers = lower ranks. Must be unique.</div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?php echo e($rank['description'] ?? ''); ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="requirements" class="form-label">Requirements</label>
                    <textarea class="form-control" id="requirements" name="requirements" rows="3"><?php echo e($rank['requirements'] ?? ''); ?></textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" name="<?php echo $action === 'create' ? 'create_rank' : 'update_rank'; ?>" class="btn btn-primary">
                        <?php echo $action === 'create' ? 'Create Rank' : 'Update Rank'; ?>
                    </button>
                    <a href="ranks.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>

    <?php elseif ($action === 'promote'): ?>
        <!-- Promote Brigader Form -->
        <div class="glass-card" style="max-width: 600px;">
            <h4 class="text-primary-blue mb-4">Promote Brigader</h4>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo e($error); ?></div>
            <?php endif; ?>

            <form method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                <div class="mb-3">
                    <label for="brigader_id" class="form-label">Select Brigader</label>
                    <select class="form-select" id="brigader_id" name="brigader_id" required>
                        <option value="">Choose brigader...</option>
                        <?php foreach ($activeBrigaders as $b): ?>
                            <option value="<?php echo $b['id']; ?>">
                                <?php echo e($b['first_name'] . ' ' . $b['last_name'] . ' (' . $b['brigade_id'] . ')'); ?>
                                - Current: <?php echo e($b['rank_name'] ?? 'No Rank'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="to_rank_id" class="form-label">Promote To</label>
                    <select class="form-select" id="to_rank_id" name="to_rank_id" required>
                        <option value="">Select new rank...</option>
                        <?php foreach ($ranks as $r): ?>
                            <option value="<?php echo $r['id']; ?>">
                                <?php echo e($r['rank_name']); ?> (Level <?php echo $r['rank_level']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Reason for promotion, achievements, etc."></textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" name="promote_brigader" class="btn btn-primary">Promote Brigader</button>
                    <a href="ranks.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>