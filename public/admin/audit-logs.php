<?php
// public/admin/audit-logs.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Notification.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole('admin');

$db        = Database::getInstance();
$pageTitle = 'Audit Logs';

// ── POST HANDLERS ───────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['csrf_token'])) {
        validateCSRFToken($_POST['csrf_token']);
    }

    // Delete single log
    if (isset($_POST['delete_log'])) {
        $db->delete('audit_logs', 'id = :id', ['id' => intval($_POST['log_id'])]);
        setFlashMessage('success', 'Log entry deleted.');
        redirect(APP_URL . '/admin/audit-logs.php?' . http_build_query(array_filter([
            'search'     => $_POST['search_keep']     ?? '',
            'action'     => $_POST['action_keep']     ?? '',
            'table_name' => $_POST['table_keep']      ?? '',
            'date_from'  => $_POST['date_from_keep']  ?? '',
            'date_to'    => $_POST['date_to_keep']    ?? '',
            'page'       => $_POST['page_keep']       ?? 1,
        ])));
    }

    // Clear logs older than X days
    if (isset($_POST['clear_old_logs'])) {
        $days       = intval($_POST['days']);
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        $result     = $db->query(
            "DELETE FROM audit_logs WHERE created_at < :cutoff",
            ['cutoff' => $cutoffDate]
        );
        setFlashMessage('success', "Deleted {$result->rowCount()} log entries older than {$days} days.");
        redirect(APP_URL . '/admin/audit-logs.php');
    }

    // Clear all logs
    if (isset($_POST['clear_all_logs'])) {
        $result = $db->query("DELETE FROM audit_logs");
        setFlashMessage('success', "All {$result->rowCount()} log entries deleted.");
        redirect(APP_URL . '/admin/audit-logs.php');
    }

    // Export CSV
    if (isset($_POST['export_csv'])) {
        // Build same filters for export
        $exportWhere  = "WHERE 1=1";
        $exportParams = [];

        if (!empty($_POST['search'])) {
            $exportWhere .= " AND (al.action LIKE :search OR al.table_name LIKE :search2)";
            $exportParams['search']  = '%' . $_POST['search'] . '%';
            $exportParams['search2'] = '%' . $_POST['search'] . '%';
        }
        if (!empty($_POST['action_filter'])) {
            $exportWhere .= " AND al.action = :action";
            $exportParams['action'] = $_POST['action_filter'];
        }
        if (!empty($_POST['table_filter'])) {
            $exportWhere .= " AND al.table_name = :table_name";
            $exportParams['table_name'] = $_POST['table_filter'];
        }
        if (!empty($_POST['date_from'])) {
            $exportWhere .= " AND DATE(al.created_at) >= :date_from";
            $exportParams['date_from'] = $_POST['date_from'];
        }
        if (!empty($_POST['date_to'])) {
            $exportWhere .= " AND DATE(al.created_at) <= :date_to";
            $exportParams['date_to'] = $_POST['date_to'];
        }

        $exportLogs = $db->fetchAll(
            "SELECT al.*, u.full_name as user_name, u.username
             FROM audit_logs al
             LEFT JOIN users u ON al.user_id = u.id
             {$exportWhere}
             ORDER BY al.created_at DESC",
            $exportParams
        );

        $csvData = [];
        foreach ($exportLogs as $log) {
            $csvData[] = [
                'ID'         => $log['id'],
                'User'       => $log['user_name'] ?? 'System',
                'Username'   => $log['username']  ?? 'N/A',
                'Action'     => $log['action'],
                'Table'      => $log['table_name'],
                'Record ID'  => $log['record_id']  ?? 'N/A',
                'Old Values' => $log['old_values']  ?? '',
                'New Values' => $log['new_values']  ?? '',
                'IP Address' => $log['ip_address']  ?? 'N/A',
                'Date/Time'  => $log['created_at'],
            ];
        }

        exportToCSV($csvData, 'audit_logs_' . date('Y-m-d') . '.csv');
    }
}

// ── FILTERS ─────────────────────────────────────────────────────────────────
$search      = $_GET['search']      ?? '';
$actionFilter = $_GET['action']     ?? '';
$tableFilter  = $_GET['table_name'] ?? '';
$dateFrom     = $_GET['date_from']  ?? '';
$dateTo       = $_GET['date_to']    ?? '';

// Build WHERE clause
$where  = "WHERE 1=1";
$params = [];

if (!empty($search)) {
    $where .= " AND (al.action LIKE :search OR al.table_name LIKE :search2 OR u.full_name LIKE :search3)";
    $params['search']  = '%' . $search . '%';
    $params['search2'] = '%' . $search . '%';
    $params['search3'] = '%' . $search . '%';
}

if (!empty($actionFilter)) {
    $where .= " AND al.action = :action";
    $params['action'] = $actionFilter;
}

if (!empty($tableFilter)) {
    $where .= " AND al.table_name = :table_name";
    $params['table_name'] = $tableFilter;
}

if (!empty($dateFrom)) {
    $where .= " AND DATE(al.created_at) >= :date_from";
    $params['date_from'] = $dateFrom;
}

if (!empty($dateTo)) {
    $where .= " AND DATE(al.created_at) <= :date_to";
    $params['date_to'] = $dateTo;
}

// ── PAGINATION ──────────────────────────────────────────────────────────────
$totalResult = $db->fetchOne(
    "SELECT COUNT(*) as count FROM audit_logs al
     LEFT JOIN users u ON al.user_id = u.id
     {$where}",
    $params
);
$totalLogs  = $totalResult['count'];
$currentPage = intval($_GET['page'] ?? 1);
$pagination  = paginate($totalLogs, $currentPage, ITEMS_PER_PAGE);

// ── FETCH LOGS ───────────────────────────────────────────────────────────────
$logs = $db->fetchAll(
    "SELECT al.*,
            u.full_name as user_name,
            u.username,
            u.role as user_role
     FROM audit_logs al
     LEFT JOIN users u ON al.user_id = u.id
     {$where}
     ORDER BY al.created_at DESC
     LIMIT :limit OFFSET :offset",
    array_merge($params, [
        'limit'  => $pagination['items_per_page'],
        'offset' => $pagination['offset'],
    ])
);

// ── GET DISTINCT ACTIONS & TABLES FOR FILTER DROPDOWNS ──────────────────────
$distinctActions = $db->fetchAll(
    "SELECT DISTINCT action FROM audit_logs ORDER BY action ASC"
);
$distinctTables = $db->fetchAll(
    "SELECT DISTINCT table_name FROM audit_logs ORDER BY table_name ASC"
);

// ── SUMMARY STATS ─────────────────────────────────────────────────────────
$totalAllLogs = $db->fetchOne("SELECT COUNT(*) as count FROM audit_logs")['count'];

$todayLogs = $db->fetchOne(
    "SELECT COUNT(*) as count FROM audit_logs WHERE DATE(created_at) = CURDATE()"
)['count'];

$thisWeekLogs = $db->fetchOne(
    "SELECT COUNT(*) as count FROM audit_logs
     WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
)['count'];

$uniqueUsers = $db->fetchOne(
    "SELECT COUNT(DISTINCT user_id) as count FROM audit_logs"
)['count'];

// Action color map
function getActionColor($action) {
    if (str_contains($action, 'create') || str_contains($action, 'add')) {
        return 'success';
    } elseif (str_contains($action, 'update') || str_contains($action, 'edit')) {
        return 'warning';
    } elseif (str_contains($action, 'delete') || str_contains($action, 'remove')) {
        return 'danger';
    } elseif (str_contains($action, 'login') || str_contains($action, 'logout')) {
        return 'info';
    }
    return 'secondary';
}

include __DIR__ . '/../includes/header.php';
?>

<div class="content-area">

    <!-- ── Page Title ────────────────────────────────────────────── -->
    <div class="mb-4">
        <h2 class="text-primary-blue mb-1">Audit Logs</h2>
        <p class="text-muted">
            Full activity history of all actions performed in the system.
        </p>
    </div>

    <!-- ── Summary Stats ─────────────────────────────────────────── -->
    <div class="stats-grid mb-4">
        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Total Logs</div>
                    <div class="stat-value"><?php echo number_format($totalAllLogs); ?></div>
                    <div class="stat-subtitle">All time</div>
                </div>
                <div class="stat-icon" style="opacity:0.15; font-size:1.8rem;">&#128196;</div>
            </div>
        </div>

        <div class="stat-card accent-success">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Today</div>
                    <div class="stat-value"><?php echo number_format($todayLogs); ?></div>
                    <div class="stat-subtitle"><?php echo date('M j, Y'); ?></div>
                </div>
                <div class="stat-icon" style="opacity:0.15; font-size:1.8rem;">&#128197;</div>
            </div>
        </div>

        <div class="stat-card accent-info">
            <div class="stat-header">
                <div>
                    <div class="stat-title">This Week</div>
                    <div class="stat-value"><?php echo number_format($thisWeekLogs); ?></div>
                    <div class="stat-subtitle">Last 7 days</div>
                </div>
                <div class="stat-icon" style="opacity:0.15; font-size:1.8rem;">&#128198;</div>
            </div>
        </div>

        <div class="stat-card accent-warning">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Active Users</div>
                    <div class="stat-value"><?php echo number_format($uniqueUsers); ?></div>
                    <div class="stat-subtitle">Unique users logged</div>
                </div>
                <div class="stat-icon" style="opacity:0.15; font-size:1.8rem;">&#128100;</div>
            </div>
        </div>
    </div>

    <!-- ── Main Table Card ───────────────────────────────────────── -->
    <div class="data-table-wrapper">

        <!-- Table Header -->
        <div class="table-header">
            <h4 class="table-title">
                Activity Log
                <span class="badge badge-secondary ms-2" style="font-size:0.75rem;">
                    <?php echo number_format($totalLogs); ?> record(s)
                </span>
            </h4>

            <div class="table-actions">
                <!-- Export CSV -->
                <form method="POST" class="d-inline">
                    <input type="hidden" name="csrf_token"
                           value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="search"        value="<?php echo e($search); ?>">
                    <input type="hidden" name="action_filter" value="<?php echo e($actionFilter); ?>">
                    <input type="hidden" name="table_filter"  value="<?php echo e($tableFilter); ?>">
                    <input type="hidden" name="date_from"     value="<?php echo e($dateFrom); ?>">
                    <input type="hidden" name="date_to"       value="<?php echo e($dateTo); ?>">
                    <button type="submit" name="export_csv" class="btn btn-secondary btn-sm">
                        Export CSV
                    </button>
                </form>

                <!-- Manage Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-secondary btn-sm dropdown-toggle"
                            data-bs-toggle="dropdown">
                        Manage Logs
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <form method="POST" class="px-3 py-2"
                                  onsubmit="return confirm('Delete logs older than 30 days?')">
                                <input type="hidden" name="csrf_token"
                                       value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="days" value="30">
                                <button type="submit" name="clear_old_logs"
                                        class="btn btn-warning btn-sm w-100">
                                    Clear older than 30 days
                                </button>
                            </form>
                        </li>
                        <li>
                            <form method="POST" class="px-3 py-2"
                                  onsubmit="return confirm('Delete logs older than 90 days?')">
                                <input type="hidden" name="csrf_token"
                                       value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="days" value="90">
                                <button type="submit" name="clear_old_logs"
                                        class="btn btn-warning btn-sm w-100">
                                    Clear older than 90 days
                                </button>
                            </form>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" class="px-3 py-2"
                                  onsubmit="return confirm('This will permanently delete ALL audit logs. Are you sure?')">
                                <input type="hidden" name="csrf_token"
                                       value="<?php echo generateCSRFToken(); ?>">
                                <button type="submit" name="clear_all_logs"
                                        class="btn btn-danger btn-sm w-100">
                                    Clear All Logs
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- ── Filters ──────────────────────────────────────────── -->
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
                <input
                    type="text"
                    class="form-control"
                    name="search"
                    placeholder="Search action, table, user..."
                    value="<?php echo e($search); ?>"
                >
            </div>

            <div class="col-md-2">
                <select class="form-select" name="action">
                    <option value="">All Actions</option>
                    <?php foreach ($distinctActions as $a): ?>
                        <option value="<?php echo e($a['action']); ?>"
                            <?php echo $actionFilter === $a['action'] ? 'selected' : ''; ?>>
                            <?php echo e($a['action']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <select class="form-select" name="table_name">
                    <option value="">All Tables</option>
                    <?php foreach ($distinctTables as $t): ?>
                        <option value="<?php echo e($t['table_name']); ?>"
                            <?php echo $tableFilter === $t['table_name'] ? 'selected' : ''; ?>>
                            <?php echo e($t['table_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <input
                    type="date"
                    class="form-control"
                    name="date_from"
                    placeholder="From"
                    value="<?php echo e($dateFrom); ?>"
                >
            </div>

            <div class="col-md-2">
                <input
                    type="date"
                    class="form-control"
                    name="date_to"
                    placeholder="To"
                    value="<?php echo e($dateTo); ?>"
                >
            </div>

            <div class="col-md-1">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        Filter
                    </button>
                    <a href="audit-logs.php" class="btn btn-secondary btn-sm">
                        Clear
                    </a>
                </div>
            </div>
        </form>

        <!-- ── Table ────────────────────────────────────────────── -->
        <?php if (empty($logs)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">&#128196;</div>
                <div class="empty-state-title">No Logs Found</div>
                <div class="empty-state-text">
                    <?php if (!empty($search) || !empty($actionFilter) || !empty($tableFilter) || !empty($dateFrom)): ?>
                        No logs match your search criteria.
                        <a href="audit-logs.php" class="d-block mt-2">Clear filters</a>
                    <?php else: ?>
                        No activity has been logged yet.
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width:50px;">#</th>
                            <th>Action</th>
                            <th>User</th>
                            <th>Table</th>
                            <th>Record ID</th>
                            <th>Date &amp; Time</th>
                            <th style="width:80px;">Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $index => $log): ?>
                            <tr>
                                <td class="text-muted" style="font-size:0.8rem;">
                                    <?php echo $pagination['offset'] + $index + 1; ?>
                                </td>

                                <td>
                                    <span class="badge badge-<?php echo getActionColor($log['action']); ?>">
                                        <?php echo e($log['action']); ?>
                                    </span>
                                </td>

                                <td>
                                    <?php if ($log['user_name']): ?>
                                        <strong><?php echo e($log['user_name']); ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            @<?php echo e($log['username']); ?>
                                            &middot; <?php echo ucfirst($log['user_role'] ?? ''); ?>
                                        </small>
                                    <?php else: ?>
                                        <span class="text-muted">System</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <code style="
                                        background: rgba(13,42,82,0.07);
                                        color: var(--primary-blue);
                                        padding: 2px 7px;
                                        border-radius: 5px;
                                        font-size: 0.82rem;
                                    ">
                                        <?php echo e($log['table_name']); ?>
                                    </code>
                                </td>

                                <td class="text-muted" style="font-size:0.85rem;">
                                    <?php echo $log['record_id'] ?? '&mdash;'; ?>
                                </td>

                                <td style="font-size:0.85rem; white-space:nowrap;">
                                    <?php echo formatDateTime($log['created_at']); ?>
                                    <br>
                                    <small class="text-muted">
                                        <?php echo timeAgo($log['created_at']); ?>
                                    </small>
                                </td>

                                <td>
                                    <form method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete this log entry?')">
                                        <input type="hidden" name="csrf_token"
                                               value="<?php echo generateCSRFToken(); ?>">
                                        <input type="hidden" name="log_id"
                                               value="<?php echo $log['id']; ?>">
                                        <!-- Preserve current filters after delete -->
                                        <input type="hidden" name="search_keep"
                                               value="<?php echo e($search); ?>">
                                        <input type="hidden" name="action_keep"
                                               value="<?php echo e($actionFilter); ?>">
                                        <input type="hidden" name="table_keep"
                                               value="<?php echo e($tableFilter); ?>">
                                        <input type="hidden" name="date_from_keep"
                                               value="<?php echo e($dateFrom); ?>">
                                        <input type="hidden" name="date_to_keep"
                                               value="<?php echo e($dateTo); ?>">
                                        <input type="hidden" name="page_keep"
                                               value="<?php echo $currentPage; ?>">
                                        <button type="submit" name="delete_log"
                                                class="btn btn-danger btn-sm">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php
            $queryString = http_build_query(array_filter([
                'search'     => $search,
                'action'     => $actionFilter,
                'table_name' => $tableFilter,
                'date_from'  => $dateFrom,
                'date_to'    => $dateTo,
            ]));
            echo renderPagination($pagination, 'audit-logs.php?' . $queryString);
            ?>

        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>