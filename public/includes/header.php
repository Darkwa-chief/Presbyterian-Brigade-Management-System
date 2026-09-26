<?php
// public/includes/header.php

if (!defined('APP_NAME')) {
    die('Direct access not permitted');
}

$notificationModel = new Notification();
$unreadCount       = $notificationModel->getUnreadCount($_SESSION['user_id'], $_SESSION['user_role']);
$pageTitle         = $pageTitle ?? 'Dashboard';
$currentPage       = basename($_SERVER['PHP_SELF']);
$userRole          = $_SESSION['user_role'];
$userInitial       = strtoupper(substr($_SESSION['full_name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?> &mdash; <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
    <style>
        /* ── Hamburger Button ─────────────────────────────────── */
        .hamburger-btn {
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 40px;
            height: 40px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 6px;
            border-radius: 8px;
            transition: background 0.2s ease;
            margin-right: 0.75rem;
            gap: 5px;
        }

        .hamburger-btn:hover {
            background: rgba(13, 42, 82, 0.07);
        }

        /* Each line of the hamburger */
        .hamburger-btn .bar {
            display: block;
            width: 22px;
            height: 2px;
            background-color: var(--primary-blue);
            border-radius: 4px;
            transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
            transform-origin: center;
        }

        /* Active (X) state */
        .hamburger-btn.active .bar:nth-child(1) {
            transform: translateY(7px) rotate(45deg);
        }

        .hamburger-btn.active .bar:nth-child(2) {
            opacity: 0;
            transform: scaleX(0);
        }

        .hamburger-btn.active .bar:nth-child(3) {
            transform: translateY(-7px) rotate(-45deg);
        }

        @media (max-width: 992px) {
            .hamburger-btn { display: flex; }
        }

        /* ── Sidebar Overlay ──────────────────────────────────── */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 999;
            backdrop-filter: blur(2px);
            animation: fadeOverlay 0.2s ease;
        }

        .sidebar-overlay.show {
            display: block;
        }

        @keyframes fadeOverlay {
            from { opacity: 0; }
            to   { opacity: 1; }
        }

        /* ── Sidebar Nav Icons (SVG based) ────────────────────── */
        .nav-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            margin-right: 0.75rem;
            flex-shrink: 0;
            opacity: 0.75;
        }

        .sidebar-nav .nav-link:hover .nav-icon,
        .sidebar-nav .nav-link.active .nav-icon {
            opacity: 1;
        }

        .nav-icon svg {
            width: 16px;
            height: 16px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        /* ── Notification bell SVG ────────────────────────────── */
        .notification-bell svg {
            width: 22px;
            height: 22px;
            stroke: var(--primary-blue);
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        /* ── User Dropdown cleanup ────────────────────────────── */
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.88rem;
        }

        .dropdown-item svg {
            width: 15px;
            height: 15px;
            stroke: var(--text-muted);
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            flex-shrink: 0;
        }

        .dropdown-divider { margin: 4px 0; }
    </style>
</head>
<body>
<div class="dashboard-wrapper">

    <!-- ── Sidebar Overlay (mobile) ───────────────────────────── -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- ── Sidebar ────────────────────────────────────────────── -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img
                src="<?php echo APP_URL; ?>/assets/images/pcg image.png"
                alt="Logo"
                class="sidebar-logo"
                onerror="this.style.display='none'"
            >
            <h5 class="sidebar-title">Presbyterian<br>Brigade</h5>
        </div>

        <nav class="sidebar-nav">

            <?php if ($userRole === 'admin'): ?>
                <!-- ── Admin Navigation ──────────────────────── -->
                <div class="nav-section">Main</div>

                <a href="<?php echo APP_URL; ?>/admin/dashboard.php"
                   class="nav-link <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                    </span>
                    Dashboard
                </a>

                <div class="nav-section">Management</div>

                <a href="<?php echo APP_URL; ?>/admin/users.php"
                   class="nav-link <?php echo $currentPage === 'users.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </span>
                    System Users
                </a>

                <a href="<?php echo APP_URL; ?>/admin/brigaders.php"
                   class="nav-link <?php echo $currentPage === 'brigaders.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </span>
                    Brigaders
                </a>

                <a href="<?php echo APP_URL; ?>/admin/ranks.php"
                   class="nav-link <?php echo $currentPage === 'ranks.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    </span>
                    Ranks &amp; Promotions
                </a>

                <a href="<?php echo APP_URL; ?>/admin/events.php"
                   class="nav-link <?php echo $currentPage === 'events.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </span>
                    Events
                </a>

                <a href="<?php echo APP_URL; ?>/admin/attendance.php"
                   class="nav-link <?php echo $currentPage === 'attendance.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    </span>
                    Attendance
                </a>

                <a href="<?php echo APP_URL; ?>/admin/recitals.php"
                   class="nav-link <?php echo $currentPage === 'recitals.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    </span>
                    Recitals
                </a>

                <a href="<?php echo APP_URL; ?>/admin/notifications.php"
                   class="nav-link <?php echo $currentPage === 'notifications.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    </span>
                    Notifications
                </a>

                <div class="nav-section">System</div>

                <a href="<?php echo APP_URL; ?>/admin/audit-logs.php"
                   class="nav-link <?php echo $currentPage === 'audit-logs.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    </span>
                    Audit Logs
                </a>

            <?php elseif ($userRole === 'officer'): ?>
                <!-- ── Officer Navigation ────────────────────── -->
                <div class="nav-section">Main</div>

                <a href="<?php echo APP_URL; ?>/officer/dashboard.php"
                   class="nav-link <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                    </span>
                    Dashboard
                </a>

                <a href="<?php echo APP_URL; ?>/officer/brigaders.php"
                   class="nav-link <?php echo $currentPage === 'brigaders.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </span>
                    Brigaders
                </a>

                <a href="<?php echo APP_URL; ?>/officer/events.php"
                   class="nav-link <?php echo $currentPage === 'events.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </span>
                    Events
                </a>

                <a href="<?php echo APP_URL; ?>/officer/attendance.php"
                   class="nav-link <?php echo $currentPage === 'attendance.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    </span>
                    Attendance
                </a>

                <a href="<?php echo APP_URL; ?>/officer/recitals.php"
                   class="nav-link <?php echo $currentPage === 'recitals.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    </span>
                    Recitals
                </a>

            <?php else: ?>
                <!-- ── Member Navigation ─────────────────────── -->
                <div class="nav-section">Main</div>

                <a href="<?php echo APP_URL; ?>/member/dashboard.php"
                   class="nav-link <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                    </span>
                    Dashboard
                </a>

                <a href="<?php echo APP_URL; ?>/member/profile.php"
                   class="nav-link <?php echo $currentPage === 'profile.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </span>
                    My Profile
                </a>

                <a href="<?php echo APP_URL; ?>/member/events.php"
                   class="nav-link <?php echo $currentPage === 'events.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </span>
                    Events
                </a>

                <a href="<?php echo APP_URL; ?>/member/recitals.php"
                   class="nav-link <?php echo $currentPage === 'recitals.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    </span>
                    Recitals
                </a>

            <?php endif; ?>

            <!-- ── Account Section (all roles) ──────────────── -->
            <div class="nav-section">Account</div>

            <a href="<?php echo APP_URL; ?>/profile-settings.php"
               class="nav-link <?php echo $currentPage === 'profile-settings.php' ? 'active' : ''; ?>">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                </span>
                Settings
            </a>

            <a href="<?php echo APP_URL; ?>/logout.php" class="nav-link">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </span>
                Logout
            </a>

        </nav>
    </aside>

    <!-- ── Main Content ────────────────────────────────────────── -->
    <main class="main-content">

        <!-- Top Header -->
        <header class="top-header">
            <div class="d-flex align-items-center">

                <!-- ── Modern Hamburger Button ──────────────── -->
                <button
                    class="hamburger-btn"
                    id="hamburgerBtn"
                    aria-label="Toggle navigation"
                    aria-expanded="false"
                >
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </button>

                <h1 class="header-title"><?php echo e($pageTitle); ?></h1>
            </div>

            <div class="header-actions">

                <!-- ── Notification Bell ────────────────────── -->
                <div class="notification-wrapper">
                    <div class="notification-bell" id="notificationBell">
                        <svg viewBox="0 0 24 24">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                        <?php if ($unreadCount > 0): ?>
                            <span class="notification-badge"><?php echo $unreadCount; ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Notification Dropdown -->
                    <div class="notification-dropdown" id="notificationDropdown">
                        <div class="notification-dropdown-header">
                            <h6 class="mb-0">Notifications</h6>
                            <button
                                type="button"
                                class="btn btn-link btn-sm p-0"
                                id="markAllRead"
                                style="font-size:0.78rem; color:var(--primary-blue); text-decoration:none;"
                            >
                                Mark all read
                            </button>
                        </div>
                        <div class="notification-dropdown-body" id="notificationList">
                            <div class="text-center py-3">
                                <div class="spinner-border spinner-border-sm" role="status"
                                     style="color:var(--primary-blue);">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                        <div class="notification-dropdown-footer">
                            <a href="#" class="btn btn-link btn-sm w-100"
                               style="font-size:0.82rem; color:var(--primary-blue); text-decoration:none;">
                                View all notifications
                            </a>
                        </div>
                    </div>
                </div>

                <!-- ── User Dropdown ─────────────────────────── -->
                <div class="dropdown">
                    <div class="user-dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-avatar">
                            <?php echo $userInitial; ?>
                        </div>
                        <div class="user-info d-none d-md-block">
                            <span class="user-name"><?php echo e($_SESSION['full_name']); ?></span>
                            <span class="user-role"><?php echo ucfirst($userRole); ?></span>
                        </div>
                    </div>

                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="<?php echo APP_URL; ?>/profile-settings.php">
                                <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                Profile Settings
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?php echo APP_URL; ?>/change-password.php">
                                <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                Change Password
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="<?php echo APP_URL; ?>/logout.php"
                               style="color: var(--danger);">
                                <svg viewBox="0 0 24 24" style="stroke:var(--danger);"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>

            </div>
        </header>

        <!-- Flash Messages -->
        <?php
        $flash = getFlashMessage();
        if ($flash):
        ?>
            <div class="content-area pb-0">
                <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show mb-0" role="alert">
                    <?php echo e($flash['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        <?php endif; ?>

        <script>
        // ── Hamburger toggle ─────────────────────────────────────
        (function () {
            const btn      = document.getElementById('hamburgerBtn');
            const sidebar  = document.getElementById('sidebar');
            const overlay  = document.getElementById('sidebarOverlay');

            function openSidebar() {
                sidebar.classList.add('show');
                overlay.classList.add('show');
                btn.classList.add('active');
                btn.setAttribute('aria-expanded', 'true');
                document.body.style.overflow = 'hidden';
            }

            function closeSidebar() {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
                btn.classList.remove('active');
                btn.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            }

            btn.addEventListener('click', function () {
                sidebar.classList.contains('show') ? closeSidebar() : openSidebar();
            });

            overlay.addEventListener('click', closeSidebar);

            // Close on ESC
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeSidebar();
            });

            // Close sidebar when a nav link is clicked on mobile
            document.querySelectorAll('.sidebar-nav .nav-link').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (window.innerWidth < 992) closeSidebar();
                });
            });
        })();
        </script>