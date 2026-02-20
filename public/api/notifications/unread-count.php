<?php
// public/api/notifications/unread-count.php

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/app.php';
require_once __DIR__ . '/../../../classes/Database.php';
require_once __DIR__ . '/../../../classes/Notification.php';
require_once __DIR__ . '/../../../includes/auth.php';

header('Content-Type: application/json');

startSession();

if (!isLoggedIn()) {
    echo json_encode(['count' => 0]);
    exit;
}

try {
    $notificationModel = new Notification();
    $count = $notificationModel->getUnreadCount($_SESSION['user_id'], $_SESSION['user_role']);
    
    echo json_encode(['count' => $count]);
} catch (Exception $e) {
    echo json_encode(['count' => 0]);
}