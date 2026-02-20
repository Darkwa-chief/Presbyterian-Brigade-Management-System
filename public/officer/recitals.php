<?php
// public/officer/recitals.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Recital.php';
require_once __DIR__ . '/../../classes/Notification.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['admin', 'officer']);

$recitalModel   = new Recital();
$pageTitle      = 'Recital Library';

// Filters
$filters = ['is_active' => 1];
if (!empty($_GET['category']))         $filters['category']         = $_GET['category'];
if (!empty($_GET['difficulty_level'])) $filters['difficulty_level'] = $_GET['difficulty_level'];

$recitals       = $recitalModel->getAll($filters);
$categoryCounts = $recitalModel->getCategoryCounts();

include __DIR__ . '/../includes/header.php';
?>

<div class="content-area">
    <div class="mb-4">
        <h2 class="text-primary-blue mb-1">📖 Recital Library</h2>
        <p class="text-muted">All active recitals available for members</p>
    </div>

    <!-- Category Stats -->
    <div class="stats-grid mb-4">
        <?php foreach ($categoryCounts as $cat): ?>
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-title"><?php echo ucfirst($cat['category']); ?></div>
                        <div class="stat-value"><?php echo $cat['count']; ?></div>
                        <div class="stat-subtitle">Recitals</div>
                    </div>
                    <div class="stat-icon">📖</div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Filters -->
    <div class="glass-card mb-4">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Category</label>
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
            <div class="col-md-4">
                <label class="form-label">Difficulty</label>
                <select class="form-select" name="difficulty_level">
                    <option value="">All Levels</option>
                    <option value="beginner" <?php echo ($_GET['difficulty_level'] ?? '') === 'beginner' ? 'selected' : ''; ?>>Beginner</option>
                    <option value="intermediate" <?php echo ($_GET['difficulty_level'] ?? '') === 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                    <option value="advanced" <?php echo ($_GET['difficulty_level'] ?? '') === 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="recitals.php" class="btn btn-secondary">Clear</a>
            </div>
        </form>
    </div>

    <!-- Recitals Grid -->
    <?php if (empty($recitals)): ?>
        <div class="empty-state glass-card">
            <div class="empty-state-icon">📖</div>
            <div class="empty-state-title">No Recitals Found</div>
            <div class="empty-state-text">No recitals have been created yet.</div>
            <a href="<?php echo APP_URL; ?>/admin/recitals.php?action=create" class="btn btn-primary">
                Create Recital (Admin)
            </a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($recitals as $recital): ?>
                <?php
                $diffColors = [
                    'beginner'     => 'success',
                    'intermediate' => 'warning',
                    'advanced'     => 'danger'
                ];
                $diffColor = $diffColors[$recital['difficulty_level']] ?? 'secondary';
                ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm" style="border-radius:12px;">
                        <div style="height:5px; background:var(--primary-blue); border-radius:12px 12px 0 0;"></div>
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="badge badge-secondary">
                                    <?php echo ucfirst($recital['category']); ?>
                                </span>
                                <span class="badge badge-<?php echo $diffColor; ?>">
                                    <?php echo ucfirst($recital['difficulty_level']); ?>
                                </span>
                            </div>

                            <h5 class="card-title text-primary-blue">
                                <?php echo e($recital['title']); ?>
                            </h5>

                            <?php if ($recital['reference']): ?>
                                <p class="text-muted small mb-2">
                                    📚 <?php echo e($recital['reference']); ?>
                                </p>
                            <?php endif; ?>

                            <p class="card-text text-muted small flex-grow-1" style="line-height:1.7;">
                                <?php
                                $preview = substr($recital['content'], 0, 150);
                                echo e($preview) . (strlen($recital['content']) > 150 ? '...' : '');
                                ?>
                            </p>

                            <button class="btn btn-primary btn-sm mt-3"
                                    onclick="viewRecital(<?php echo $recital['id']; ?>)">
                                📖 Read Full Recital
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Recital Modal -->
<div class="modal fade" id="recitalModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="recitalModalTitle">Recital</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="recitalModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
const recitals = <?php echo json_encode($recitals); ?>;

function viewRecital(id) {
    const recital = recitals.find(r => parseInt(r.id) === id);
    if (!recital) return;

    document.getElementById('recitalModalTitle').textContent =
        recital.title + (recital.reference ? ' — ' + recital.reference : '');

    document.getElementById('recitalModalBody').innerHTML = `
        <div class="mb-3">
            <span class="badge bg-secondary me-2">${recital.category}</span>
            <span class="badge bg-info me-2">${recital.difficulty_level}</span>
            ${recital.reference ? '<span class="text-muted small">📚 ' + escapeHtml(recital.reference) + '</span>' : ''}
        </div>
        <hr>
        <div style="white-space:pre-wrap; font-size:1.1rem; line-height:2.2; font-family:Georgia,serif;">
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