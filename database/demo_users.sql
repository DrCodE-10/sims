-- SIMS - Demo teacher & student accounts (run after main schema)
-- Student login: username = student_id (STU001), password = last name (Doe)

USE sims_db;

-- Teacher user (login: teacher1 / Teacher@123)
INSERT INTO users (username, password, email, role, full_name, is_active) VALUES
('teacher1', '$2y$10$yfaNy6rgiXIoWRGNsBmDXODoyXbs.b1w6OAydEpNRnN4VeVboP2dS', 'teacher1@sims.com', 'teacher', 'Jane Smith', 1)
ON DUPLICATE KEY UPDATE full_name = VALUES(full_name);

SET @teacher_user_id = (SELECT id FROM users WHERE username = 'teacher1' LIMIT 1);

INSERT INTO teachers (user_id, teacher_id, first_name, last_name, email, hire_date, qualification, specialization, status) VALUES
(@teacher_user_id, 'TCH001', 'Jane', 'Smith', 'teacher1@sims.com', CURDATE(), 'M.Ed', 'Mathematics', 'active')
ON DUPLICATE KEY UPDATE user_id = @teacher_user_id, first_name = 'Jane', last_name = 'Smith';

SET @teacher_id = (SELECT id FROM teachers WHERE teacher_id = 'TCH001' LIMIT 1);

UPDATE courses SET teacher_id = @teacher_id WHERE course_code = 'GEN101';

-- Remove legacy demo login (student1) if present
DELETE FROM users WHERE username = 'student1' AND role = 'student';

-- Student: username STU001 = student_id, password Doe = last_name
INSERT INTO users (username, password, email, role, full_name, is_active) VALUES
('STU001', '$2y$10$xDh6fTRwDIS2Q3GxUXN0AO2dnioDPEStrYrg.66egSQJY12U9KUyS', 'student1@sims.com', 'student', 'John Doe', 1)
ON DUPLICATE KEY UPDATE password = VALUES(password), full_name = VALUES(full_name);

SET @student_user_id = (SELECT id FROM users WHERE username = 'STU001' LIMIT 1);

INSERT INTO students (user_id, student_id, first_name, last_name, email, enrollment_date, class_grade, section, gender, status) VALUES
(@student_user_id, 'STU001', 'John', 'Doe', 'student1@sims.com', CURDATE(), '10', 'A', 'male', 'active')
ON DUPLICATE KEY UPDATE user_id = @student_user_id, first_name = 'John', last_name = 'Doe';

SET @student_id = (SELECT id FROM students WHERE student_id = 'STU001' LIMIT 1);
SET @course_id = (SELECT id FROM courses WHERE course_code = 'GEN101' LIMIT 1);

INSERT IGNORE INTO enrollments (student_id, course_id, enrollment_date, status)
VALUES (@student_id, @course_id, CURDATE(), 'enrolled');
