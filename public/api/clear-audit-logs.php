<?php
// public/api/clear-audit-logs.php

ob_start();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../includes/auth.php';

ob_end_clean();

header('Content-Type: application/json');

// Use the same session start from auth.php
startSession();

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if ($_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);
    
    $db = Database::getInstance();
    
    if (isset($data['clear_all']) && $data['clear_all'] === true) {
        $result = $db->query("DELETE FROM audit_logs");
        $deleted = $result->rowCount();
    } else {
        $days = isset($data['days']) ? intval($data['days']) : 30;
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        $result = $db->query(
            "DELETE FROM audit_logs WHERE created_at < :cutoff_date",
            ['cutoff_date' => $cutoffDate]
        );
        $deleted = $result->rowCount();
    }
    
    echo json_encode([
        'success' => true,
        'deleted' => $deleted,
        'message' => "Deleted {$deleted} log entries"
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit;