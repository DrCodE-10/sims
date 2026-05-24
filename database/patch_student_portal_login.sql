-- SIMS - Student portal login convention
-- Run on existing databases after sims_2050_schema.sql / patches.sql
-- Rule: users.username = students.student_id | initial password = students.last_name (bcrypt)

USE sims_db;

-- Policy settings
INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES
('student_portal_username', 'student_id', 'string', 'Student portal username field (maps to students.student_id)'),
('student_portal_password', 'last_name', 'string', 'Initial student portal password field (students.last_name, stored hashed)'),
('student_portal_auto_create', '1', 'boolean', 'Automatically create portal user when admin adds a student')
ON DUPLICATE KEY UPDATE
    setting_value = VALUES(setting_value),
    setting_type = VALUES(setting_type),
    description = VALUES(description);

-- Recreate view (safe on re-run)
DROP VIEW IF EXISTS v_student_portal_logins;
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

-- Fix demo account: login username must be STU001 (not student1)
DELETE FROM users WHERE username = 'student1' AND role = 'student';

-- Align usernames with student_id where already linked
UPDATE users u
INNER JOIN students s ON s.user_id = u.id
SET u.username = s.student_id
WHERE u.role = 'student' AND u.username <> s.student_id;

-- Ensure demo student STU001 exists with password "Doe" (last name)
-- Hash: bcrypt of "Doe"
SET @doe_hash = '$2y$10$xDh6fTRwDIS2Q3GxUXN0AO2dnioDPEStrYrg.66egSQJY12U9KUyS';

INSERT INTO users (username, password, email, role, full_name, is_active)
SELECT 'STU001', @doe_hash, 'student1@sims.com', 'student', 'John Doe', 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'STU001');

UPDATE users SET password = @doe_hash, full_name = 'John Doe', role = 'student', is_active = 1
WHERE username = 'STU001';

SET @stu_user_id = (SELECT id FROM users WHERE username = 'STU001' LIMIT 1);

INSERT INTO students (user_id, student_id, first_name, last_name, email, enrollment_date, class_grade, section, gender, status)
SELECT @stu_user_id, 'STU001', 'John', 'Doe', 'student1@sims.com', CURDATE(), '10', 'A', 'male', 'active'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM students WHERE student_id = 'STU001');

UPDATE students SET user_id = @stu_user_id, first_name = 'John', last_name = 'Doe'
WHERE student_id = 'STU001';

INSERT IGNORE INTO courses (course_code, course_name, class_grade, academic_year, semester, status)
VALUES ('GEN101', 'General Attendance', 'All', '2024-2025', 1, 'active');

INSERT IGNORE INTO enrollments (student_id, course_id, enrollment_date, status)
SELECT s.id, c.id, CURDATE(), 'enrolled'
FROM students s
CROSS JOIN courses c
WHERE s.student_id = 'STU001' AND c.course_code = 'GEN101';

-- Students without portal accounts: run database/migrate_student_portal_accounts.php from browser or CLI
