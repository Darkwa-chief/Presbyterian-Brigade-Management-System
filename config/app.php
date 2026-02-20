<?php
// config/app.php

define('APP_NAME', 'Presbyterian Brigade Management System');
define('APP_VERSION', '1.0.0');

//  IMPORTANT: Match your actual XAMPP path
define('APP_URL', 'http://localhost/presbyterian-brigade/public');

// Session configuration - XAMPP compatible
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);      // Must be 0 for localhost
ini_set('session.cookie_samesite', 'Lax'); // Changed from Strict to Lax for XAMPP

//  IMPORTANT: Match the session name in auth.php
session_name('BRIGADE_SESSION');

// File upload settings
define('UPLOAD_MAX_SIZE', 5242880);
define('UPLOAD_ALLOWED_TYPES', ['image/jpeg', 'image/jpg', 'image/png']);
define('UPLOAD_PATH', __DIR__ . '/../public/uploads/');

// Pagination
define('ITEMS_PER_PAGE', 20);

// Timezone
date_default_timezone_set('UTC');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0); // Keep OFF so errors don't break JSON