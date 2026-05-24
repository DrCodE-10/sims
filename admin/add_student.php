<?php
$page_title = 'Add Student';
$root = '..';

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/mailer.php';
require_admin();

$pdo = db();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $student_id = trim($_POST['student_id'] ?? '');
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $date_of_birth = $_POST['date_of_birth'] ?? '';
        $gender = $_POST['gender'] ?? 'other';
        $address = trim($_POST['address'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $class_grade = trim($_POST['class_grade'] ?? '');
        $section = trim($_POST['section'] ?? '');
        $guardian_name = trim($_POST['guardian_name'] ?? '');
        $guardian_phone = trim($_POST['guardian_phone'] ?? '');
        $guardian_email = trim($_POST['guardian_email'] ?? '');

        try {
            $profile_image = handle_profile_upload();
        } catch (RuntimeException $e) {
            $error = $e->getMessage();
        }

        if ($error === '' && (empty($student_id) || empty($first_name) || empty($last_name) || empty($date_of_birth) || empty($class_grade))) {
            $error = 'Please fill in all required fields.';
        } elseif ($error === '') {
            try {
                $stmt = $pdo->prepare('SELECT id FROM students WHERE student_id = ?');
                $stmt->execute([$student_id]);
                if ($stmt->fetch()) {
                    $error = 'Student ID already exists.';
                } else {
                    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
                    $stmt->execute([$student_id]);
                    if ($stmt->fetch()) {
                        $error = 'This Student ID is already used as a login username.';
                    } else {
                        $loginUsername = $student_id;
                        $loginPassword = $last_name;
                        $loginEmail = $email !== '' ? $email : ($student_id . '@sims.local');

                        $pdo->beginTransaction();

                        $stmt = $pdo->prepare(
                            'INSERT INTO students (student_id, first_name, last_name, date_of_birth, gender, address, phone, email, enrollment_date, class_grade, section, guardian_name, guardian_phone, guardian_email, profile_image)
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), ?, ?, ?, ?, ?, ?)'
                        );
                        $stmt->execute([
                            $student_id, $first_name, $last_name, $date_of_birth, $gender,
                            $address, $phone, $email, $class_grade, $section,
                            $guardian_name, $guardian_phone, $guardian_email, $profile_image,
                        ]);

                        $newStudentPk = (int) $pdo->lastInsertId();

                        $uid = create_user_account(
                            $pdo,
                            $loginUsername,
                            $loginPassword,
                            $loginEmail,
                            'student',
                            $first_name . ' ' . $last_name
                        );

                        $pdo->prepare('UPDATE students SET user_id = ? WHERE id = ?')->execute([$uid, $newStudentPk]);

                        $courseId = ensure_default_course($pdo);
                        $pdo->prepare(
                            'INSERT IGNORE INTO enrollments (student_id, course_id, enrollment_date, status) VALUES (?, ?, CURDATE(), ?)'
                        )->execute([$newStudentPk, $courseId, 'enrolled']);

                        $pdo->commit();

                        $notifyEmail = $email ?: $guardian_email;
                        notify_student_registered($notifyEmail, $first_name . ' ' . $last_name, $student_id);

                        flash_set(
                            'success',
                            'Student added. Portal login — Username: ' . $loginUsername
                            . ' | Password: ' . $loginPassword . ' (last name, case-sensitive)'
                        );
                        redirect('students.php');
                    }
                }
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $code = (int) $e->errorInfo[1];
                if ($code === 1062) {
                    $error = 'Student ID or login username already exists.';
                } else {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        }
    }
}

$post = $_POST;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="glass-panel">
    <?php if ($error): ?>
        <div class="alert alert-error" style="margin-bottom:15px;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>

        <div class="form-group" style="margin-bottom:20px;">
            <label>Profile Picture</label>
            <input type="file" name="profile_image" accept="image/*" class="neon-input">
            <p style="font-size:12px;color:var(--text-muted);">JPG, PNG, GIF — max 5MB</p>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label>Student ID *</label>
                <input type="text" name="student_id" class="neon-input" required value="<?php echo htmlspecialchars($post['student_id'] ?? ''); ?>">
                <p style="font-size:12px;color:var(--text-muted);margin-top:5px;">Used as the student portal <strong>username</strong> when they log in.</p>
            </div>
            <div class="form-group">
                <label>First Name *</label>
                <input type="text" name="first_name" class="neon-input" required value="<?php echo htmlspecialchars($post['first_name'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Last Name *</label>
                <input type="text" name="last_name" class="neon-input" required value="<?php echo htmlspecialchars($post['last_name'] ?? ''); ?>">
                <p style="font-size:12px;color:var(--text-muted);margin-top:5px;">Used as the initial portal <strong>password</strong> (stored securely with hashing).</p>
            </div>
            <div class="form-group">
                <label>Date of Birth *</label>
                <input type="date" name="date_of_birth" class="neon-input" required value="<?php echo htmlspecialchars($post['date_of_birth'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Gender</label>
                <select name="gender" class="neon-input">
                    <?php foreach (['male', 'female', 'other'] as $g): ?>
                        <option value="<?php echo $g; ?>" <?php echo (($post['gender'] ?? 'other') === $g) ? 'selected' : ''; ?>><?php echo ucfirst($g); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Class Grade *</label>
                <input type="text" name="class_grade" class="neon-input" required value="<?php echo htmlspecialchars($post['class_grade'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Section</label>
                <input type="text" name="section" class="neon-input" value="<?php echo htmlspecialchars($post['section'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="tel" name="phone" class="neon-input" value="<?php echo htmlspecialchars($post['phone'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="neon-input" value="<?php echo htmlspecialchars($post['email'] ?? ''); ?>">
            </div>
            <div class="form-group form-full">
                <label>Address</label>
                <textarea name="address" class="neon-input" rows="2"><?php echo htmlspecialchars($post['address'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label>Guardian Name</label>
                <input type="text" name="guardian_name" class="neon-input" value="<?php echo htmlspecialchars($post['guardian_name'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Guardian Phone</label>
                <input type="tel" name="guardian_phone" class="neon-input" value="<?php echo htmlspecialchars($post['guardian_phone'] ?? ''); ?>">
            </div>
            <div class="form-group form-full">
                <label>Guardian Email</label>
                <input type="email" name="guardian_email" class="neon-input" value="<?php echo htmlspecialchars($post['guardian_email'] ?? ''); ?>">
            </div>
            <div class="form-group form-full" style="border-top:1px solid var(--glass-border);padding-top:15px;">
                <p style="font-size:13px;color:var(--text-secondary);margin:0;">
                    A student portal account is created automatically:<br>
                    <strong>Username</strong> = Student ID &nbsp;|&nbsp; <strong>Password</strong> = Last name
                </p>
            </div>
        </div>

        <div style="margin-top:20px;display:flex;gap:10px;">
            <button type="submit" class="neon-btn">Add Student</button>
            <a href="students.php" class="neon-btn" style="text-decoration:none;">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
