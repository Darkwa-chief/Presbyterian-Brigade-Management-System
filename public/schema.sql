-- sql/schema.sql

CREATE DATABASE IF NOT EXISTS presbyterian_brigade CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE presbyterian_brigade;

-- Users table (Officers, Admins)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'officer', 'member') NOT NULL DEFAULT 'member',
    status ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Ranks table
CREATE TABLE ranks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rank_name VARCHAR(50) UNIQUE NOT NULL,
    rank_level INT UNIQUE NOT NULL,
    description TEXT,
    requirements TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_level (rank_level)
) ENGINE=InnoDB;

-- Brigaders table
CREATE TABLE brigaders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    brigade_id VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender ENUM('male', 'female') NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    passport_photo VARCHAR(255),
    rank_id INT,
    unit VARCHAR(50),
    join_date DATE NOT NULL,
    status ENUM('active', 'inactive', 'transferred') NOT NULL DEFAULT 'active',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (rank_id) REFERENCES ranks(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_brigade_id (brigade_id),
    INDEX idx_status (status),
    INDEX idx_unit (unit)
) ENGINE=InnoDB;

-- Promotions table
CREATE TABLE promotions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    brigader_id INT NOT NULL,
    from_rank_id INT,
    to_rank_id INT NOT NULL,
    promotion_date DATE NOT NULL,
    approved_by INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (brigader_id) REFERENCES brigaders(id) ON DELETE CASCADE,
    FOREIGN KEY (from_rank_id) REFERENCES ranks(id) ON DELETE SET NULL,
    FOREIGN KEY (to_rank_id) REFERENCES ranks(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_brigader (brigader_id),
    INDEX idx_date (promotion_date)
) ENGINE=InnoDB;

-- Events table
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(100) NOT NULL,
    event_type ENUM('meeting', 'training', 'service', 'competition', 'other') NOT NULL,
    event_date DATE NOT NULL,
    event_time TIME,
    location VARCHAR(255),
    description TEXT,
    created_by INT,
    status ENUM('scheduled', 'ongoing', 'completed', 'cancelled') NOT NULL DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_date (event_date),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Attendance table
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    brigader_id INT NOT NULL,
    status ENUM('present', 'absent', 'excused', 'late') NOT NULL DEFAULT 'present',
    notes TEXT,
    marked_by INT,
    marked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (brigader_id) REFERENCES brigaders(id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_attendance (event_id, brigader_id),
    INDEX idx_event (event_id),
    INDEX idx_brigader (brigader_id)
) ENGINE=InnoDB;

-- Recitals table
CREATE TABLE recitals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    category ENUM('scripture', 'creed', 'catechism', 'hymn', 'prayer', 'other') NOT NULL,
    reference VARCHAR(100),
    difficulty_level ENUM('beginner', 'intermediate', 'advanced') NOT NULL DEFAULT 'beginner',
    visibility ENUM('all', 'officers', 'specific_ranks') NOT NULL DEFAULT 'all',
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_category (category),
    INDEX idx_active (is_active)
) ENGINE=InnoDB;

-- Notifications table
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    notification_type ENUM('general', 'event', 'promotion', 'urgent') NOT NULL DEFAULT 'general',
    target_role ENUM('all', 'admin', 'officer', 'member') NOT NULL DEFAULT 'all',
    target_unit VARCHAR(50),
    created_by INT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_active (is_active),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- Notification reads tracking
CREATE TABLE notification_reads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    notification_id INT NOT NULL,
    user_id INT NOT NULL,
    read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_read (notification_id, user_id)
) ENGINE=InnoDB;

-- Audit logs
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_values TEXT,
    new_values TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- Seed data
INSERT INTO ranks (rank_name, rank_level, description, requirements) VALUES
('Recruit', 1, 'New member of the brigade', 'Complete orientation'),
('Chum', 2, 'Basic level brigader', '3 months attendance, basic knowledge'),
('Pal', 3, 'Intermediate level', '6 months attendance, scripture recitals'),
('Guard', 4, 'Advanced level', '1 year attendance, leadership qualities'),
('Herald', 5, 'Senior brigader', '2 years attendance, mentorship'),
('Officer-in-Training', 6, 'Leadership training', 'Recommendation from officers');

INSERT INTO users (username, password, email, full_name, role, status) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@presbyterianbrigade.org', 'System Administrator', 'admin', 'active'),
('officer1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'officer@presbyterianbrigade.org', 'Chief Officer', 'officer', 'active');
-- Default password for both: password

INSERT INTO notifications (title, message, notification_type, target_role, created_by) VALUES
('Welcome to Presbyterian Brigade System', 'Welcome to the Presbyterian Brigade Management System. Please update your profile information.', 'general', 'all', 1);