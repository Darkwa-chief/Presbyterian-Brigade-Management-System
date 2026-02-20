// public/assets/js/app.js

/* ============================================
   PRESBYTERIAN BRIGADE MANAGEMENT SYSTEM
   Main JavaScript File
   ============================================ */

(function() {
    'use strict';

    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        initSidebar();
        initNotifications();
        initTooltips();
        initConfirmDialogs();
        initFormValidation();
        initDataTables();
    });

    // Sidebar toggle for mobile
    function initSidebar() {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');
        
        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('show');
            });
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                        sidebar.classList.remove('show');
                    }
                }
            });
        }
    }

    // Notification handling
function initNotifications() {
    const notificationBell = document.getElementById('notificationBell');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const markAllReadBtn = document.getElementById('markAllRead');
    
    if (notificationBell && notificationDropdown) {
        notificationBell.addEventListener('click', function(e) {
            e.stopPropagation();
            const isVisible = notificationDropdown.style.display === 'block';
            
            if (isVisible) {
                notificationDropdown.style.display = 'none';
            } else {
                notificationDropdown.style.display = 'block';
                loadNotifications();
            }
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!notificationDropdown.contains(e.target) && !notificationBell.contains(e.target)) {
                notificationDropdown.style.display = 'none';
            }
        });
        
        // Mark all as read
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', function(e) {
                e.preventDefault();
                markAllNotificationsRead();
            });
        }
    }
    
    // Auto-refresh notification count
    refreshNotificationCount();
    setInterval(refreshNotificationCount, 60000); // Every minute
}

// Load notifications via AJAX
function loadNotifications() {
    const notificationList = document.getElementById('notificationList');
    
    if (!notificationList) return;
    
    // Show loading
    notificationList.innerHTML = `
        <div class="text-center py-3">
            <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    // Fetch notifications
    fetch('api/get-notifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.notifications.length > 0) {
                renderNotifications(data.notifications);
            } else {
                notificationList.innerHTML = `
                    <div class="notification-empty">
                        <div class="notification-empty-icon">🔔</div>
                        <div>No notifications</div>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            notificationList.innerHTML = `
                <div class="notification-empty">
                    <div class="notification-empty-icon">⚠️</div>
                    <div>Failed to load notifications</div>
                </div>
            `;
        });
}

// Render notifications
function renderNotifications(notifications) {
    const notificationList = document.getElementById('notificationList');
    
    let html = '';
    notifications.forEach(notification => {
        const isUnread = !notification.is_read;
        const typeColors = {
            'general': 'secondary',
            'event': 'primary',
            'promotion': 'success',
            'urgent': 'danger'
        };
        const badgeColor = typeColors[notification.notification_type] || 'secondary';
        
        html += `
            <div class="notification-item ${isUnread ? 'unread' : ''}" data-id="${notification.id}">
                <div class="notification-item-title">
                    <span class="badge bg-${badgeColor} notification-item-type">${notification.notification_type}</span>
                    ${notification.title}
                </div>
                <div class="notification-item-message">${notification.message}</div>
                <div class="notification-item-time">${notification.time_ago}</div>
            </div>
        `;
    });
    
    notificationList.innerHTML = html;
    
    // Add click handlers to mark as read
    document.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', function() {
            const notificationId = this.getAttribute('data-id');
            markNotificationRead(notificationId);
            this.classList.remove('unread');
        });
    });
}

// Mark single notification as read
function markNotificationRead(notificationId) {
    fetch('api/mark-notification-read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ notification_id: notificationId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            refreshNotificationCount();
        }
    })
    .catch(error => console.error('Error marking notification as read:', error));
}

// Mark all notifications as read
function markAllNotificationsRead() {
    fetch('api/mark-all-notifications-read.php', {
        method: 'POST',
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadNotifications();
            refreshNotificationCount();
        }
    })
    .catch(error => console.error('Error marking all as read:', error));
}

    // Load notifications via AJAX
    function loadNotifications() {
        // Implementation for loading notifications
        // This would make an AJAX call to get latest notifications
        console.log('Loading notifications...');
    }

    // Refresh notification count
    function refreshNotificationCount() {
        const badge = document.querySelector('.notification-badge');
        
        if (badge) {
            // AJAX call to get unread count
            fetch('api/notifications/unread-count.php')
                .then(response => response.json())
                .then(data => {
                    if (data.count > 0) {
                        badge.textContent = data.count;
                        badge.style.display = 'flex';
                    } else {
                        badge.style.display = 'none';
                    }
                })
                .catch(error => console.error('Error fetching notification count:', error));
        }
    }

    // Initialize Bootstrap tooltips
    function initTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Confirm dialogs for delete actions
    function initConfirmDialogs() {
        const confirmButtons = document.querySelectorAll('[data-confirm]');
        
        confirmButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                const message = this.getAttribute('data-confirm') || 'Are you sure you want to proceed?';
                
                if (!confirm(message)) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    }

    // Form validation
    function initFormValidation() {
        const forms = document.querySelectorAll('.needs-validation');
        
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                
                form.classList.add('was-validated');
            }, false);
        });
    }

    // Data table enhancements
    function initDataTables() {
        // Search functionality
        const searchInputs = document.querySelectorAll('.table-search');
        
        searchInputs.forEach(input => {
            input.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                const tableId = this.getAttribute('data-table');
                const table = document.getElementById(tableId);
                
                if (table) {
                    const rows = table.querySelectorAll('tbody tr');
                    
                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(searchTerm) ? '' : 'none';
                    });
                }
            });
        });
    }

    // Utility: Show toast message
    window.showToast = function(message, type = 'info') {
        const toastContainer = document.getElementById('toastContainer') || createToastContainer();
        
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        toastContainer.appendChild(toast);
        
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        toast.addEventListener('hidden.bs.toast', function() {
            toast.remove();
        });
    };

    function createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
        return container;
    }

    // Utility: Format date
    window.formatDate = function(dateString, format = 'short') {
        const date = new Date(dateString);
        
        if (format === 'short') {
            return date.toLocaleDateString();
        } else if (format === 'long') {
            return date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
        } else if (format === 'datetime') {
            return date.toLocaleString();
        }
        
        return dateString;
    };

    // Utility: Debounce function
    window.debounce = function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    };

})();

// Export to global scope if needed
window.BrigadeApp = {
    showToast: window.showToast,
    formatDate: window.formatDate,
    debounce: window.debounce
};