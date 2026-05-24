<?php
$page_title = 'Edit Student';
$root = '..';

require_once __DIR__ . '/../includes/auth.php';
require_admin();

$pdo = db();
$error = '';
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM students WHERE id = ?');
$stmt->execute([$id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    flash_set('error', 'Student not found.');
    redirect('students.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Invalid security token.';
    } else {
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
        $status = $_POST['status'] ?? 'active';
        $profile_image = $student['profile_image'];

        try {
            $newImage = handle_profile_upload();
            if ($newImage !== null) {
                delete_profile_image($student['profile_image']);
                $profile_image = $newImage;
            }
        } catch (RuntimeException $e) {
            $error = $e->getMessage();
        }

        if ($error === '' && (empty($first_name) || empty($last_name) || empty($date_of_birth) || empty($class_grade))) {
            $error = 'Please fill in all required fields.';
        } elseif ($error === '') {
            try {
                $stmt = $pdo->prepare(
                    'UPDATE students SET first_name=?, last_name=?, date_of_birth=?, gender=?, address=?, phone=?, email=?, class_grade=?, section=?, guardian_name=?, guardian_phone=?, guardian_email=?, profile_image=?, status=? WHERE id=?'
                );
                $stmt->execute([
                    $first_name, $last_name, $date_of_birth, $gender, $address, $phone, $email,
                    $class_grade, $section, $guardian_name, $guardian_phone, $guardian_email,
                    $profile_image, $status, $id,
                ]);
                flash_set('success', 'Student updated successfully.');
                redirect('students.php');
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="glass-panel">
    <?php if ($error): ?>
        <div class="alert alert-error" style="margin-bottom:15px;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="id" value="<?php echo (int) $id; ?>">

        <div class="form-group" style="margin-bottom:20px;">
            <?php if ($student['profile_image']): ?>
                <img src="<?php echo $root; ?>/<?php echo htmlspecialchars($student['profile_image']); ?>" alt="" style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:2px solid var(--primary-neon);">
            <?php endif; ?>
            <label style="display:block;margin-top:10px;">Change Photo</label>
            <input type="file" name="profile_image" accept="image/*" class="neon-input">
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label>Student ID</label>
                <input type="text" class="neon-input" value="<?php echo htmlspecialchars($student['student_id']); ?>" disabled>
            </div>
            <div class="form-group">
                <label>First Name *</label>
                <input type="text" name="first_name" class="neon-input" required value="<?php echo htmlspecialchars($student['first_name']); ?>">
            </div>
            <div class="form-group">
                <label>Last Name *</label>
                <input type="text" name="last_name" class="neon-input" required value="<?php echo htmlspecialchars($student['last_name']); ?>">
            </div>
            <div class="form-group">
                <label>Date of Birth *</label>
                <input type="date" name="date_of_birth" class="neon-input" required value="<?php echo htmlspecialchars($student['date_of_birth']); ?>">
            </div>
            <div class="form-group">
                <label>Gender</label>
                <select name="gender" class="neon-input">
                    <?php foreach (['male', 'female', 'other'] as $g): ?>
                        <option value="<?php echo $g; ?>" <?php echo $student['gender'] === $g ? 'selected' : ''; ?>><?php echo ucfirst($g); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Class Grade *</label>
                <input type="text" name="class_grade" class="neon-input" required value="<?php echo htmlspecialchars($student['class_grade']); ?>">
            </div>
            <div class="form-group">
                <label>Section</label>
                <input type="text" name="section" class="neon-input" value="<?php echo htmlspecialchars($student['section'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="neon-input">
                    <?php foreach (['active', 'inactive', 'graduated', 'suspended'] as $st): ?>
                        <option value="<?php echo $st; ?>" <?php echo $student['status'] === $st ? 'selected' : ''; ?>><?php echo ucfirst($st); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="tel" name="phone" class="neon-input" value="<?php echo htmlspecialchars($student['phone'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="neon-input" value="<?php echo htmlspecialchars($student['email'] ?? ''); ?>">
            </div>
            <div class="form-group form-full">
                <label>Address</label>
                <textarea name="address" class="neon-input" rows="2"><?php echo htmlspecialchars($student['address'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label>Guardian Name</label>
                <input type="text" name="guardian_name" class="neon-input" value="<?php echo htmlspecialchars($student['guardian_name'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Guardian Phone</label>
                <input type="tel" name="guardian_phone" class="neon-input" value="<?php echo htmlspecialchars($student['guardian_phone'] ?? ''); ?>">
            </div>
            <div class="form-group form-full">
                <label>Guardian Email</label>
                <input type="email" name="guardian_email" class="neon-input" value="<?php echo htmlspecialchars($student['guardian_email'] ?? ''); ?>">
            </div>
        </div>

        <div style="margin-top:20px;display:flex;gap:10px;">
            <button type="submit" class="neon-btn">Update Student</button>
            <a href="students.php" class="neon-btn" style="text-decoration:none;">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
