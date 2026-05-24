<?php
$page_title = 'Add Teacher';
$root = '..';

require_once __DIR__ . '/../includes/auth.php';
require_admin();

$pdo = db();
$error = '';
$post = $_POST;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $teacher_id = trim($_POST['teacher_id'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $qualification = trim($_POST['qualification'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? 'Teacher@123';

    if ($teacher_id === '' || $first_name === '' || $last_name === '' || $username === '' || $email === '') {
        $error = 'Please fill required fields.';
    } else {
        try {
            $pdo->beginTransaction();
            $check = $pdo->prepare('SELECT id FROM teachers WHERE teacher_id = ?');
            $check->execute([$teacher_id]);
            if ($check->fetch()) {
                throw new RuntimeException('Teacher ID already exists.');
            }

            $uid = create_user_account($pdo, $username, $password, $email, 'teacher', $first_name . ' ' . $last_name);

            $pdo->prepare(
                'INSERT INTO teachers (user_id, teacher_id, first_name, last_name, email, phone, hire_date, qualification, specialization, status)
                 VALUES (?, ?, ?, ?, ?, ?, CURDATE(), ?, ?, ?)'
            )->execute([$uid, $teacher_id, $first_name, $last_name, $email, $phone, $qualification, $specialization, 'active']);

            $pdo->commit();
            flash_set('success', "Teacher added. Login: {$username} / (password you set)");
            redirect('teachers.php');
        } catch (Throwable $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="glass-panel">
    <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <form method="POST" class="form-grid">
        <?php echo csrf_field(); ?>
        <div class="form-group"><label>Teacher ID *</label><input name="teacher_id" class="neon-input" required value="<?php echo htmlspecialchars($post['teacher_id'] ?? ''); ?>"></div>
        <div class="form-group"><label>First Name *</label><input name="first_name" class="neon-input" required value="<?php echo htmlspecialchars($post['first_name'] ?? ''); ?>"></div>
        <div class="form-group"><label>Last Name *</label><input name="last_name" class="neon-input" required value="<?php echo htmlspecialchars($post['last_name'] ?? ''); ?>"></div>
        <div class="form-group"><label>Email *</label><input type="email" name="email" class="neon-input" required value="<?php echo htmlspecialchars($post['email'] ?? ''); ?>"></div>
        <div class="form-group"><label>Phone</label><input name="phone" class="neon-input" value="<?php echo htmlspecialchars($post['phone'] ?? ''); ?>"></div>
        <div class="form-group"><label>Qualification</label><input name="qualification" class="neon-input" value="<?php echo htmlspecialchars($post['qualification'] ?? ''); ?>"></div>
        <div class="form-group"><label>Specialization</label><input name="specialization" class="neon-input" value="<?php echo htmlspecialchars($post['specialization'] ?? ''); ?>"></div>
        <div class="form-group"><label>Login Username *</label><input name="username" class="neon-input" required value="<?php echo htmlspecialchars($post['username'] ?? ''); ?>"></div>
        <div class="form-group"><label>Login Password *</label><input name="password" class="neon-input" value="<?php echo htmlspecialchars($post['password'] ?? 'Teacher@123'); ?>"></div>
        <div class="form-group form-full"><button type="submit" class="neon-btn">Add Teacher</button></div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
