<?php
// public/api/get-notifications.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Notification.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

startSession();

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

try {
    $notificationModel = new Notification();
    $notifications = $notificationModel->getUserNotifications(
        $_SESSION['user_id'], 
        $_SESSION['user_role']
    );
    
    // Add time ago to each notification
    foreach ($notifications as &$notification) {
        $notification['time_ago'] = timeAgo($notification['created_at']);
    }
    
    echo json_encode([
        'success' => true,
        'notifications' => $notifications
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}