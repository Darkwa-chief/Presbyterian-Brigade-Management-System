<?php
// public/api/delete-audit-log.php

ob_start();

// Load config FIRST before anything else
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

ob_end_clean();

header('Content-Type: application/json');

// Use the same session start from auth.php
startSession();

// Debug: uncomment these lines if still having issues
// error_log("Session data: " . print_r($_SESSION, true));
// error_log("Session name: " . session_name());
// error_log("Session ID: " . session_id());

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Not logged in - session: ' . session_id(),
        'debug' => [
            'session_id' => session_id(),
            'session_name' => session_name(),
            'session_data' => $_SESSION
        ]
    ]);
    exit;
}

if ($_SESSION['user_role'] !== 'admin') {
    echo json_encode([
        'success' => false, 
        'message' => 'Not an admin - role is: ' . $_SESSION['user_role']
    ]);
    exit;
}

try {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }
    
    $logId = isset($data['log_id']) ? intval($data['log_id']) : 0;
    
    if (!$logId) {
        throw new Exception('Log ID required');
    }
    
    $db = Database::getInstance();
    $deleted = $db->delete('audit_logs', 'id = :id', ['id' => $logId]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Log deleted successfully',
        'deleted' => $deleted
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
exit;