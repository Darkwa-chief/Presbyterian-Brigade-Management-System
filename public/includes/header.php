<?php
// public/includes/header.php

if (!defined('APP_NAME')) {
    die('Direct access not permitted');
}

$notificationModel = new Notification();
$unreadCount = $notificationModel->getUnreadCount($_SESSION['user_id'], $_SESSION['user_role']);
$pageTitle = $pageTitle ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?> - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <!-- Logo: Replace with your church logo -->
                <img src="<?php echo APP_URL; ?>/assets/images/logo-placeholder.png" alt="Logo" class="sidebar-logo" onerror="this.style.display='none'">
                <h5 class="sidebar-title">Presbyterian<br>Brigade</h5>
            </div>

            <nav class="sidebar-nav">
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <!-- Admin Navigation -->
                    <div class="nav-section">Main Menu</div>
                    <a href="<?php echo APP_URL; ?>/admin/dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">📊</span>
                        Dashboard
                    </a>
                    
                    <div class="nav-section">Management</div>
                    <a href="<?php echo APP_URL; ?>/admin/users.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">👤</span>
                        System Users
                    </a>
                    <a href="<?php echo APP_URL; ?>/admin/brigaders.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'brigaders.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">👥</span>
                        Brigaders
                    </a>
                    <a href="<?php echo APP_URL; ?>/admin/ranks.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'ranks.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">🎖️</span>
                        Ranks & Promotions
                    </a>
                    <a href="<?php echo APP_URL; ?>/admin/events.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'events.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">📅</span>
                        Events
                    </a>
                    <a href="<?php echo APP_URL; ?>/admin/attendance.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'attendance.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">✓</span>
                        Attendance
                    </a>
                    <a href="<?php echo APP_URL; ?>/admin/recitals.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'recitals.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">📖</span>
                        Recitals
                    </a>
                    <a href="<?php echo APP_URL; ?>/admin/notifications.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'notifications.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">🔔</span>
                        Notifications
                    </a>

                <?php elseif ($_SESSION['user_role'] === 'officer'): ?>
                    <!-- Officer Navigation -->
                    <div class="nav-section">Main Menu</div>
                    <a href="<?php echo APP_URL; ?>/officer/dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">📊</span>
                        Dashboard
                    </a>
                    <a href="<?php echo APP_URL; ?>/officer/brigaders.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'brigaders.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">👥</span>
                        Brigaders
                    </a>
                    <a href="<?php echo APP_URL; ?>/officer/events.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'events.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">📅</span>
                        Events
                    </a>
                    <a href="<?php echo APP_URL; ?>/officer/attendance.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'attendance.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">✓</span>
                        Attendance
                    </a>
                    <a href="<?php echo APP_URL; ?>/officer/recitals.php" 
                      class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'recitals.php' ? 'active' : ''; ?>">
                      <span class="nav-icon">📖</span>
                     Recitals
                    </a>

                <?php else: ?>
                    <!-- Member Navigation -->
                    <div class="nav-section">Main Menu</div>
                    <a href="<?php echo APP_URL; ?>/member/dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">📊</span>
                        Dashboard
                    </a>
                    <a href="<?php echo APP_URL; ?>/member/profile.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">👤</span>
                        My Profile
                    </a>
                    <a href="<?php echo APP_URL; ?>/member/events.php"
                        class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'events.php' ? 'active' : ''; ?>">
                        <span class="nav-icon">📅</span>
                         Events
                    </a>
                    <a href="<?php echo APP_URL; ?>/member/recitals.php" 
                      class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'recitals.php' ? 'active' : ''; ?>">
                      <span class="nav-icon">📖</span>
                       Recitals
                    </a>
                <?php endif; ?>

                <div class="nav-section">Account</div>
                <a href="<?php echo APP_URL; ?>/logout.php" class="nav-link">
                    <span class="nav-icon">🚪</span>
                    Logout
                </a>

            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Header -->
            <header class="top-header">
                <div class="d-flex align-items-center">
                    <button class="btn btn-link d-lg-none me-2" id="sidebarToggle">
                        ☰
                    </button>
                    <h1 class="header-title"><?php echo e($pageTitle); ?></h1>
                </div>

                <div class="header-actions">
                    <!-- Notifications -->
                    <div class="notification-wrapper position-relative">
    <div class="notification-bell" id="notificationBell" style="cursor: pointer;">
        <span class="notification-icon">🔔</span>
        <?php if ($unreadCount > 0): ?>
            <span class="notification-badge"><?php echo $unreadCount; ?></span>
        <?php endif; ?>
    </div>
    
    <!-- Notification Dropdown -->
    <div class="notification-dropdown" id="notificationDropdown" style="display: none;">
        <div class="notification-dropdown-header">
            <h6 class="mb-0">Notifications</h6>
            <button type="button" class="btn btn-link btn-sm text-primary" id="markAllRead">Mark all read</button>
        </div>
        <div class="notification-dropdown-body" id="notificationList">
            <div class="text-center py-3">
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
        <div class="notification-dropdown-footer">
            <a href="#" class="btn btn-link btn-sm w-100">View all notifications</a>
        </div>
    </div>
</div>

                    <!-- User Dropdown -->
                    <div class="dropdown">
                        <div class="user-dropdown" data-bs-toggle="dropdown">
                            <div class="user-avatar">
                                <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
                            </div>
                            <div class="user-info">
                                <span class="user-name"><?php echo e($_SESSION['full_name']); ?></span>
                                <span class="user-role"><?php echo ucfirst($_SESSION['user_role']); ?></span>
                            </div>
                        </div>
                           <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/profile-settings.php">👤 Profile Settings</a></li>
                            <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/change-password.php">🔒 Change Password</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/logout.php">🚪 Logout</a></li>
                           </ul>
                    </div>
                </div>
            </header>

            <?php
            $flash = getFlashMessage();
            if ($flash):
            ?>
                <div class="content-area">
                    <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
                        <?php echo e($flash['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            <?php endif; ?>