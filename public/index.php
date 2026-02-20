<?php
// public/index.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../includes/auth.php';

startSession();

if (!isLoggedIn()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Redirect based on role
switch ($_SESSION['user_role']) {
    case 'admin':
        header('Location: ' . APP_URL . '/admin/dashboard.php');
        break;
    case 'officer':
        header('Location: ' . APP_URL . '/officer/dashboard.php');
        break;
    case 'member':
        header('Location: ' . APP_URL . '/member/dashboard.php');
        break;
    default:
        session_destroy();
        header('Location: ' . APP_URL . '/login.php');
        break;
}
exit;

