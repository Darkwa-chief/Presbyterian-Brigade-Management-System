<?php
// public/api/mark-notification-read.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Notification.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');

startSession();

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $notificationId = $data['notification_id'] ?? null;
    
    if (!$notificationId) {
        throw new Exception('Notification ID required');
    }
    
    $notificationModel = new Notification();
    $notificationModel->markAsRead($notificationId, $_SESSION['user_id']);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}