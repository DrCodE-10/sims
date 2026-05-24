-- SIMS - Database Schema
-- Student Information Management System
-- MySQL/MariaDB Database Schema

-- Create database
CREATE DATABASE IF NOT EXISTS sims_db  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sims_db;

-- Drop existing tables (for clean installation)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS grades;
DROP TABLE IF EXISTS attendance;
DROP TABLE IF EXISTS enrollments;
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS teachers;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS settings;
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- USERS TABLE (Authentication & Authorization)
-- ============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('admin', 'teacher', 'student') NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    profile_image VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    last_login DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- STUDENTS TABLE
-- ============================================
-- Portal rule: users.username = students.student_id, initial password = students.last_name (bcrypt in users.password)
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE COMMENT 'Links to users.id for student portal login',
    student_id VARCHAR(20) UNIQUE NOT NULL COMMENT 'Also used as portal username (users.username)',
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other') DEFAULT 'other',
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    enrollment_date DATE NOT NULL,
    class_grade VARCHAR(10),
    section VARCHAR(10),
    guardian_name VARCHAR(100),
    guardian_phone VARCHAR(20),
    guardian_email VARCHAR(100),
    profile_image VARCHAR(255) DEFAULT NULL,
    status ENUM('active', 'inactive', 'graduated', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_student_id (student_id),
    INDEX idx_class_grade (class_grade),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TEACHERS TABLE
-- ============================================
CREATE TABLE teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE,
    teacher_id VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other') DEFAULT 'other',
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    hire_date DATE NOT NULL,
    qualification VARCHAR(100),
    specialization VARCHAR(100),
    subject_expertise TEXT,
    profile_image VARCHAR(255) DEFAULT NULL,
    status ENUM('active', 'inactive', 'on_leave') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_teacher_id (teacher_id),
    INDEX idx_specialization (specialization),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- COURSES TABLE
-- ============================================
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(20) UNIQUE NOT NULL,
    course_name VARCHAR(100) NOT NULL,
    description TEXT,
    credits DECIMAL(3,1) DEFAULT 3.0,
    class_grade VARCHAR(10),
    teacher_id INT,
    academic_year VARCHAR(10),
    semester TINYINT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL,
    INDEX idx_course_code (course_code),
    INDEX idx_class_grade (class_grade),
    INDEX idx_academic_year (academic_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ENROLLMENTS TABLE (Student-Course Relationship)
-- ============================================
CREATE TABLE enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    enrollment_date DATE NOT NULL,
    status ENUM('enrolled', 'dropped', 'completed') DEFAULT 'enrolled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (student_id, course_id),
    INDEX idx_student_id (student_id),
    INDEX idx_course_id (course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ATTENDANCE TABLE
-- ============================================
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('present', 'absent', 'late', 'excused') NOT NULL,
    remarks TEXT,
    marked_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by) REFERENCES teachers(id) ON DELETE SET NULL,
    UNIQUE KEY unique_attendance (student_id, course_id, attendance_date),
    INDEX idx_student_id (student_id),
    INDEX idx_course_id (course_id),
    INDEX idx_attendance_date (attendance_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- GRADES TABLE
-- ============================================
CREATE TABLE grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    exam_type VARCHAR(50) NOT NULL,
    exam_date DATE,
    marks_obtained DECIMAL(5,2),
    total_marks DECIMAL(5,2) DEFAULT 100.00,
    percentage DECIMAL(5,2),
    grade VARCHAR(2),
    remarks TEXT,
    graded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (graded_by) REFERENCES teachers(id) ON DELETE SET NULL,
    INDEX idx_student_id (student_id),
    INDEX idx_course_id (course_id),
    INDEX idx_exam_type (exam_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- NOTIFICATIONS TABLE
-- ============================================
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error', 'alert') DEFAULT 'info',
    is_read TINYINT(1) DEFAULT 0,
    link VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ACTIVITY LOGS TABLE
-- ============================================
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    entity_type VARCHAR(50),
    entity_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SETTINGS TABLE
-- ============================================
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INSERT DEFAULT DATA
-- ============================================

-- Default Admin User (Password: Mwita@0104 - bcrypt hashed)
INSERT INTO users (username, password, email, role, full_name, is_active) VALUES
('admin', '$2y$10$BK2ZNH96nztV5WcnwnHkrOOL841d0A5Gu7nmxjyjB08q9O9p4pV6q', 'admin@sims.com', 'admin', 'Dr.code', 1);

-- Default Settings
INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES
('school_name', 'SIMS Academy', 'string', 'Name of the educational institution'),
('academic_year', '2024-2025', 'string', 'Current academic year'),
('attendance_threshold', '75', 'integer', 'Minimum attendance percentage required'),
('grading_scale', '{"A": 90, "B": 80, "C": 70, "D": 60, "F": 0}', 'json', 'Grading scale for GPA calculation'),
('system_email', 'noreply@sims.com', 'string', 'System email for notifications'),
('enable_notifications', '1', 'boolean', 'Enable email notifications'),
('max_file_size', '5242880', 'integer', 'Maximum file upload size in bytes (5MB)'),
('allowed_file_types', '["jpg","jpeg","png","pdf","doc","docx"]', 'json', 'Allowed file types for uploads'),
('student_portal_username', 'student_id', 'string', 'Student portal username field (maps to students.student_id)'),
('student_portal_password', 'last_name', 'string', 'Initial student portal password field (students.last_name, stored hashed)'),
('student_portal_auto_create', '1', 'boolean', 'Automatically create portal user when admin adds a student');

-- ============================================
-- CREATE VIEWS FOR COMMON QUERIES
-- ============================================

-- Student portal accounts (username should equal student_id)
CREATE VIEW v_student_portal_logins AS
SELECT
    s.id AS internal_id,
    s.student_id,
    s.first_name,
    s.last_name,
    s.user_id,
    u.username AS login_username,
    u.email AS login_email,
    u.is_active AS login_active,
    CASE
        WHEN s.user_id IS NULL THEN 'no_account'
        WHEN u.username = s.student_id THEN 'linked_ok'
        ELSE 'username_mismatch'
    END AS link_status
FROM students s
LEFT JOIN users u ON u.id = s.user_id AND u.role = 'student';

-- View: Student Attendance Summary
CREATE VIEW student_attendance_summary AS
SELECT 
    s.id AS student_id,
    s.student_id,
    CONCAT(s.first_name, ' ', s.last_name) AS student_name,
    c.course_code,
    c.course_name,
    COUNT(CASE WHEN a.status = 'present' THEN 1 END) AS present_count,
    COUNT(CASE WHEN a.status = 'absent' THEN 1 END) AS absent_count,
    COUNT(CASE WHEN a.status = 'late' THEN 1 END) AS late_count,
    COUNT(*) AS total_classes,
    ROUND((COUNT(CASE WHEN a.status = 'present' THEN 1 END) / COUNT(*)) * 100, 2) AS attendance_percentage
FROM students s
JOIN attendance a ON s.id = a.student_id
JOIN courses c ON a.course_id = c.id
GROUP BY s.id, s.student_id, s.first_name, s.last_name, c.course_code, c.course_name;

-- View: Student GPA Summary
CREATE VIEW student_gpa_summary AS
SELECT 
    s.id AS student_id,
    s.student_id,
    CONCAT(s.first_name, ' ', s.last_name) AS student_name,
    c.course_code,
    c.course_name,
    AVG(g.percentage) AS average_percentage,
    ROUND(AVG(g.percentage), 2) AS gpa
FROM students s
JOIN grades g ON s.id = g.student_id
JOIN courses c ON g.course_id = c.id
GROUP BY s.id, s.student_id, s.first_name, s.last_name, c.course_code, c.course_name;

-- ============================================
-- CREATE STORED PROCEDURES
-- ============================================

DELIMITER //

-- Procedure: Calculate Student Attendance Percentage
CREATE PROCEDURE CalculateAttendancePercentage(IN student_id_param INT, IN course_id_param INT)
BEGIN
    SELECT 
        COUNT(CASE WHEN status = 'present' THEN 1 END) AS present,
        COUNT(CASE WHEN status = 'absent' THEN 1 END) AS absent,
        COUNT(*) AS total,
        ROUND((COUNT(CASE WHEN status = 'present' THEN 1 END) / COUNT(*)) * 100, 2) AS percentage
    FROM attendance
    WHERE student_id = student_id_param AND course_id = course_id_param;
END //

-- Procedure: Get Student Report Card
CREATE PROCEDURE GetStudentReportCard(IN student_id_param INT)
BEGIN
    SELECT 
        s.student_id,
        CONCAT(s.first_name, ' ', s.last_name) AS student_name,
        s.class_grade,
        c.course_code,
        c.course_name,
        c.credits,
        g.exam_type,
        g.marks_obtained,
        g.total_marks,
        g.percentage,
        g.grade,
        g.remarks
    FROM students s
    JOIN grades g ON s.id = g.student_id
    JOIN courses c ON g.course_id = c.id
    WHERE s.id = student_id_param
    ORDER BY c.course_name, g.exam_date;
END //

-- Procedure: Mark Attendance
CREATE PROCEDURE MarkAttendance(
    IN p_student_id INT,
    IN p_course_id INT,
    IN p_attendance_date DATE,
    IN p_status VARCHAR(20),
    IN p_remarks TEXT,
    IN p_marked_by INT
)
BEGIN
    INSERT INTO attendance (student_id, course_id, attendance_date, status, remarks, marked_by)
    VALUES (p_student_id, p_course_id, p_attendance_date, p_status, p_remarks, p_marked_by)
    ON DUPLICATE KEY UPDATE 
        status = p_status,
        remarks = p_remarks,
        marked_by = p_marked_by,
        updated_at = CURRENT_TIMESTAMP;
END //

DELIMITER ;

-- ============================================
-- CREATE TRIGGERS
-- ============================================

DELIMITER //

-- Trigger: Calculate Grade Percentage Before Insert
CREATE TRIGGER before_grade_insert
BEFORE INSERT ON grades
FOR EACH ROW
BEGIN
    IF NEW.marks_obtained IS NOT NULL AND NEW.total_marks IS NOT NULL AND NEW.total_marks > 0 THEN
        SET NEW.percentage = (NEW.marks_obtained / NEW.total_marks) * 100;
        
        -- Auto-calculate grade based on percentage
        IF NEW.percentage >= 90 THEN
            SET NEW.grade = 'A';
        ELSEIF NEW.percentage >= 80 THEN
            SET NEW.grade = 'B';
        ELSEIF NEW.percentage >= 70 THEN
            SET NEW.grade = 'C';
        ELSEIF NEW.percentage >= 60 THEN
            SET NEW.grade = 'D';
        ELSE
            SET NEW.grade = 'F';
        END IF;
    END IF;
END //

-- Trigger: Calculate Grade Percentage Before Update
CREATE TRIGGER before_grade_update
BEFORE UPDATE ON grades
FOR EACH ROW
BEGIN
    IF NEW.marks_obtained IS NOT NULL AND NEW.total_marks IS NOT NULL AND NEW.total_marks > 0 THEN
        SET NEW.percentage = (NEW.marks_obtained / NEW.total_marks) * 100;
        
        -- Auto-calculate grade based on percentage
        IF NEW.percentage >= 90 THEN
            SET NEW.grade = 'A';
        ELSEIF NEW.percentage >= 80 THEN
            SET NEW.grade = 'B';
        ELSEIF NEW.percentage >= 70 THEN
            SET NEW.grade = 'C';
        ELSEIF NEW.percentage >= 60 THEN
            SET NEW.grade = 'D';
        ELSE
            SET NEW.grade = 'F';
        END IF;
    END IF;
END //

DELIMITER ;

-- ============================================
-- ADDITIONAL TABLES (auth extras)
-- ============================================
CREATE TABLE IF NOT EXISTS remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token_hash (token_hash),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_reset_token (token_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO courses (course_code, course_name, class_grade, academic_year, semester, status)
VALUES ('GEN101', 'General Attendance', 'All', '2024-2025', 1, 'active');

-- Demo teacher: username teacher1 / password Teacher@123
INSERT INTO users (username, password, email, role, full_name, is_active) VALUES
('teacher1', '$2y$10$yfaNy6rgiXIoWRGNsBmDXODoyXbs.b1w6OAydEpNRnN4VeVboP2dS', 'teacher1@sims.com', 'teacher', 'Jane Smith', 1);

SET @demo_teacher_user_id = LAST_INSERT_ID();

INSERT INTO teachers (user_id, teacher_id, first_name, last_name, email, hire_date, qualification, specialization, status) VALUES
(@demo_teacher_user_id, 'TCH001', 'Jane', 'Smith', 'teacher1@sims.com', CURDATE(), 'M.Ed', 'Mathematics', 'active');

UPDATE courses SET teacher_id = (SELECT id FROM teachers WHERE teacher_id = 'TCH001' LIMIT 1)
WHERE course_code = 'GEN101';

-- Demo student: username STU001 (= student_id) / password Doe (= last_name, bcrypt below)
INSERT INTO users (username, password, email, role, full_name, is_active) VALUES
('STU001', '$2y$10$xDh6fTRwDIS2Q3GxUXN0AO2dnioDPEStrYrg.66egSQJY12U9KUyS', 'student1@sims.com', 'student', 'John Doe', 1);

SET @demo_student_user_id = LAST_INSERT_ID();

INSERT INTO students (user_id, student_id, first_name, last_name, email, enrollment_date, class_grade, section, gender, status) VALUES
(@demo_student_user_id, 'STU001', 'John', 'Doe', 'student1@sims.com', CURDATE(), '10', 'A', 'male', 'active');

INSERT IGNORE INTO enrollments (student_id, course_id, enrollment_date, status)
SELECT s.id, c.id, CURDATE(), 'enrolled'
FROM students s, courses c
WHERE s.student_id = 'STU001' AND c.course_code = 'GEN101';

-- ============================================
-- END OF SCHEMA
-- ============================================
