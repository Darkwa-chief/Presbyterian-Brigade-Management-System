<?php
// public/admin/recitals.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Recital.php';
require_once __DIR__ . '/../../classes/Notification.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole('admin');

$recitalModel = new Recital();
$pageTitle = 'Recitals';
$action = $_GET['action'] ?? 'list';
$recitalId = $_GET['id'] ?? null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['csrf_token'])) {
        validateCSRFToken($_POST['csrf_token']);
    }
    
    if (isset($_POST['create_recital'])) {
        $data = [
            'title' => sanitizeInput($_POST['title']),
            'content' => sanitizeInput($_POST['content']),
            'category' => $_POST['category'],
            'reference' => sanitizeInput($_POST['reference']),
            'difficulty_level' => $_POST['difficulty_level'],
            'visibility' => $_POST['visibility'],
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'created_by' => $_SESSION['user_id']
        ];
        
        try {
            $newRecitalId = $recitalModel->create($data);
            logAudit($_SESSION['user_id'], 'create_recital', 'recitals', $newRecitalId, null, $data);
            setFlashMessage('success', 'Recital created successfully.');
            redirect('recitals.php');
        } catch (Exception $e) {
            $error = 'Failed to create recital: ' . $e->getMessage();
        }
    } elseif (isset($_POST['update_recital'])) {
        $data = [
            'title' => sanitizeInput($_POST['title']),
            'content' => sanitizeInput($_POST['content']),
            'category' => $_POST['category'],
            'reference' => sanitizeInput($_POST['reference']),
            'difficulty_level' => $_POST['difficulty_level'],
            'visibility' => $_POST['visibility'],
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        try {
            $recitalModel->update($_POST['recital_id'], $data);
            logAudit($_SESSION['user_id'], 'update_recital', 'recitals', $_POST['recital_id'], null, $data);
            setFlashMessage('success', 'Recital updated successfully.');
            redirect('recitals.php');
        } catch (Exception $e) {
            $error = 'Failed to update recital: ' . $e->getMessage();
        }
    } elseif (isset($_POST['delete_recital'])) {
        try {
            $recitalModel->delete($_POST['recital_id']);
            logAudit($_SESSION['user_id'], 'delete_recital', 'recitals', $_POST['recital_id']);
            setFlashMessage('success', 'Recital deleted successfully.');
            redirect('recitals.php');
        } catch (Exception $e) {
            setFlashMessage('danger', 'Failed to delete recital: ' . $e->getMessage());
            redirect('recitals.php');
        }
    }
}

// Get recital data for editing
$recital = null;
if (($action === 'edit' || $action === 'view') && $recitalId) {
    $recital = $recitalModel->getById($recitalId);
    if (!$recital) {
        setFlashMessage('danger', 'Recital not found.');
        redirect('recitals.php');
    }
}

// Get all recitals with filters
$filters = [];
if (!empty($_GET['category'])) {
    $filters['category'] = $_GET['category'];
}
if (isset($_GET['is_active'])) {
    $filters['is_active'] = $_GET['is_active'];
}
if (!empty($_GET['difficulty_level'])) {
    $filters['difficulty_level'] = $_GET['difficulty_level'];
}

$recitals = $recitalModel->getAll($filters);
$categoryCounts = $recitalModel->getCategoryCounts();

include __DIR__ . '/../includes/header.php';
?>

<div class="content-area">
    <?php if ($action === 'list'): ?>
        <!-- Recitals List -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h3 class="text-primary-blue">Recital Library</h3>
                <p class="text-muted">Manage scriptures, creeds, catechisms, and other recitals.</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="?action=create" class="btn btn-primary">
                    <span>➕</span> Add Recital
                </a>
            </div>
        </div>

        <!-- Category Overview -->
        <div class="stats-grid mb-4">
            <?php foreach ($categoryCounts as $cat): ?>
                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-title"><?php echo ucfirst($cat['category']); ?></div>
                            <div class="stat-value"><?php echo $cat['count']; ?></div>
                            <div class="stat-subtitle">Active recitals</div>
                        </div>
                        <div class="stat-icon">📖</div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Filters -->
        <div class="glass-card mb-3">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <select class="form-select" name="category">
                        <option value="">All Categories</option>
                        <option value="scripture" <?php echo ($_GET['category'] ?? '') === 'scripture' ? 'selected' : ''; ?>>Scripture</option>
                        <option value="creed" <?php echo ($_GET['category'] ?? '') === 'creed' ? 'selected' : ''; ?>>Creed</option>
                        <option value="catechism" <?php echo ($_GET['category'] ?? '') === 'catechism' ? 'selected' : ''; ?>>Catechism</option>
                        <option value="hymn" <?php echo ($_GET['category'] ?? '') === 'hymn' ? 'selected' : ''; ?>>Hymn</option>
                        <option value="prayer" <?php echo ($_GET['category'] ?? '') === 'prayer' ? 'selected' : ''; ?>>Prayer</option>
                        <option value="other" <?php echo ($_GET['category'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="difficulty_level">
                        <option value="">All Levels</option>
                        <option value="beginner" <?php echo ($_GET['difficulty_level'] ?? '') === 'beginner' ? 'selected' : ''; ?>>Beginner</option>
                        <option value="intermediate" <?php echo ($_GET['difficulty_level'] ?? '') === 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                        <option value="advanced" <?php echo ($_GET['difficulty_level'] ?? '') === 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="is_active">
                        <option value="">All Status</option>
                        <option value="1" <?php echo ($_GET['is_active'] ?? '') === '1' ? 'selected' : ''; ?>>Active</option>
                        <option value="0" <?php echo ($_GET['is_active'] ?? '') === '0' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>

        <!-- Recitals Table -->
        <div class="data-table-wrapper">
            <?php if (empty($recitals)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📖</div>
                    <div class="empty-state-title">No Recitals Found</div>
                    <div class="empty-state-text">Start building your recital library.</div>
                    <a href="?action=create" class="btn btn-primary">Add First Recital</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Reference</th>
                                <th>Difficulty</th>
                                <th>Visibility</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recitals as $r): ?>
                                <tr>
                                    <td><strong><?php echo e($r['title']); ?></strong></td>
                                    <td><span class="badge badge-secondary"><?php echo ucfirst($r['category']); ?></span></td>
                                    <td><?php echo e($r['reference']); ?></td>
                                    <td><span class="badge badge-info"><?php echo ucfirst($r['difficulty_level']); ?></span></td>
                                    <td><?php echo ucfirst($r['visibility']); ?></td>
                                    <td>
                                        <?php if ($r['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="?action=view&id=<?php echo $r['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                        <a href="?action=edit&id=<?php echo $r['id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this recital?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                            <input type="hidden" name="recital_id" value="<?php echo $r['id']; ?>">
                                            <button type="submit" name="delete_recital" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    <?php elseif ($action === 'create' || $action === 'edit'): ?>
        <!-- Recital Form -->
        <div class="glass-card" style="max-width: 800px;">
            <h4 class="text-primary-blue mb-4"><?php echo $action === 'create' ? 'Add New Recital' : 'Edit Recital'; ?></h4>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo e($error); ?></div>
            <?php endif; ?>

            <form method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="recital_id" value="<?php echo $recital['id']; ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" class="form-control" id="title" name="title" 
                           value="<?php echo e($recital['title'] ?? ''); ?>" required>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="scripture" <?php echo ($recital['category'] ?? '') === 'scripture' ? 'selected' : ''; ?>>Scripture</option>
                            <option value="creed" <?php echo ($recital['category'] ?? '') === 'creed' ? 'selected' : ''; ?>>Creed</option>
                            <option value="catechism" <?php echo ($recital['category'] ?? '') === 'catechism' ? 'selected' : ''; ?>>Catechism</option>
                            <option value="hymn" <?php echo ($recital['category'] ?? '') === 'hymn' ? 'selected' : ''; ?>>Hymn</option>
                            <option value="prayer" <?php echo ($recital['category'] ?? '') === 'prayer' ? 'selected' : ''; ?>>Prayer</option>
                            <option value="other" <?php echo ($recital['category'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="difficulty_level" class="form-label">Difficulty Level</label>
                        <select class="form-select" id="difficulty_level" name="difficulty_level" required>
                            <option value="beginner" <?php echo ($recital['difficulty_level'] ?? 'beginner') === 'beginner' ? 'selected' : ''; ?>>Beginner</option>
                            <option value="intermediate" <?php echo ($recital['difficulty_level'] ?? '') === 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                            <option value="advanced" <?php echo ($recital['difficulty_level'] ?? '') === 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="reference" class="form-label">Reference</label>
                        <input type="text" class="form-control" id="reference" name="reference" 
                               value="<?php echo e($recital['reference'] ?? ''); ?>" placeholder="e.g., John 3:16">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label">Content</label>
                    <textarea class="form-control" id="content" name="content" rows="8" required><?php echo e($recital['content'] ?? ''); ?></textarea>
                    <div class="form-text">Enter the full text of the recital.</div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="visibility" class="form-label">Visibility</label>
                        <select class="form-select" id="visibility" name="visibility" required>
                            <option value="all" <?php echo ($recital['visibility'] ?? 'all') === 'all' ? 'selected' : ''; ?>>All Members</option>
                            <option value="officers" <?php echo ($recital['visibility'] ?? '') === 'officers' ? 'selected' : ''; ?>>Officers Only</option>
                            <option value="specific_ranks" <?php echo ($recital['visibility'] ?? '') === 'specific_ranks' ? 'selected' : ''; ?>>Specific Ranks</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label d-block">Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                   <?php echo ($recital['is_active'] ?? 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" name="<?php echo $action === 'create' ? 'create_recital' : 'update_recital'; ?>" class="btn btn-primary">
                        <?php echo $action === 'create' ? 'Add Recital' : 'Update Recital'; ?>
                    </button>
                    <a href="recitals.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>

    <?php elseif ($action === 'view'): ?>
        <!-- View Recital -->
        <div class="glass-card" style="max-width: 800px;">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h4 class="text-primary-blue mb-2"><?php echo e($recital['title']); ?></h4>
                    <div class="mb-2">
                        <span class="badge badge-secondary"><?php echo ucfirst($recital['category']); ?></span>
                        <span class="badge badge-info"><?php echo ucfirst($recital['difficulty_level']); ?></span>
                        <?php if ($recital['is_active']): ?>
                            <span class="badge badge-success">Active</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">Inactive</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($recital['reference']): ?>
                        <p class="text-muted mb-0"><strong>Reference:</strong> <?php echo e($recital['reference']); ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <a href="?action=edit&id=<?php echo $recital['id']; ?>" class="btn btn-secondary">Edit</a>
                    <a href="recitals.php" class="btn btn-primary">Back</a>
                </div>
            </div>

            <hr>

            <div class="recital-content" style="line-height: 1.8; font-size: 1.1rem; white-space: pre-wrap;">
                <?php echo e($recital['content']); ?>
            </div>

            <hr>

            <div class="text-muted">
                <small>
                    <strong>Created by:</strong> <?php echo e($recital['created_by_name'] ?? 'Unknown'); ?><br>
                    <strong>Created:</strong> <?php echo formatDateTime($recital['created_at']); ?><br>
                    <strong>Last Updated:</strong> <?php echo formatDateTime($recital['updated_at']); ?>
                </small>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>