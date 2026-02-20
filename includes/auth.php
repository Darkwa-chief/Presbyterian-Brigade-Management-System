<?php
// includes/auth.php

function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // ✅ These must match app.php settings
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', 0);
        ini_set('session.cookie_samesite', 'Lax');
        
        session_name('BRIGADE_SESSION');
        session_start();
    }
}

function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
}

function requireRole($allowedRoles) {
    requireLogin();
    
    if (!is_array($allowedRoles)) {
        $allowedRoles = [$allowedRoles];
    }
    
    if (!in_array($_SESSION['user_role'], $allowedRoles)) {
        http_response_code(403);
        die('Access denied. Insufficient permissions.');
    }
}

function login($user) {
    startSession();
    
    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['login_time'] = time();
    
    // Log login
    logAudit($user['id'], 'login', 'users', $user['id']);
}

function logout() {
    startSession();
    
    if (isset($_SESSION['user_id'])) {
        logAudit($_SESSION['user_id'], 'logout', 'users', $_SESSION['user_id']);
    }
    
    session_unset();
    session_destroy();
    
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $userModel = new User();
    return $userModel->getById($_SESSION['user_id']);
}

function generateCSRFToken() {
    startSession();
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    startSession();
    
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        die('CSRF token validation failed.');
    }
    
    return true;
}

function logAudit($userId, $action, $tableName = null, $recordId = null, $oldValues = null, $newValues = null) {
    try {
        $db = Database::getInstance();
        
        $data = [
            'user_id' => $userId,
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ];
        
        $db->insert('audit_logs', $data);
    } catch (Exception $e) {
        // Log error silently, don't disrupt application flow
        error_log("Audit log failed: " . $e->getMessage());
    }
}