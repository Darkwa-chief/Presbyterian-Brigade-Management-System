<?php
// includes/middleware.php

function adminOnly() {
    requireRole('admin');
}

function officerOrAdmin() {
    requireRole(['admin', 'officer']);
}

function memberOnly() {
    requireRole('member');
}

function checkSessionTimeout($timeout = 3600) {
    startSession();
    
    if (isset($_SESSION['login_time'])) {
        $elapsed = time() - $_SESSION['login_time'];
        
        if ($elapsed > $timeout) {
            logout();
        }
    }
}

function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

function redirect($url, $statusCode = 302) {
    header("Location: $url", true, $statusCode);
    exit;
}

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function setFlashMessage($type, $message) {
    startSession();
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    startSession();
    
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    
    return null;
}