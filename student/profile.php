<?php
$page_title = 'My Profile';
$root = '..';

require_once __DIR__ . '/../includes/auth.php';
require_student();

$pdo = db();
$stmt = $pdo->prepare('SELECT s.*, u.username, u.email AS login_email FROM students s JOIN users u ON u.id = s.user_id WHERE s.id = ?');
$stmt->execute([get_student_id()]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/header_student.php';
?>

<div class="glass-panel">
    <?php if ($student): ?>
        <div style="display:flex;gap:20px;align-items:center;margin-bottom:25px;">
            <?php if ($student['profile_image']): ?>
                <img src="<?php echo $root; ?>/<?php echo htmlspecialchars($student['profile_image']); ?>" alt="" style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:2px solid var(--primary-neon);">
            <?php endif; ?>
            <div>
                <h2 style="margin:0;"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h2>
                <p style="color:var(--text-muted);"><?php echo htmlspecialchars($student['student_id']); ?></p>
            </div>
        </div>
        <div class="form-grid">
            <div class="form-group"><label>Username</label><p><?php echo htmlspecialchars($student['username']); ?></p></div>
            <div class="form-group"><label>Class</label><p><?php echo htmlspecialchars($student['class_grade'] . ($student['section'] ? ' - ' . $student['section'] : '')); ?></p></div>
            <div class="form-group"><label>Email</label><p><?php echo htmlspecialchars($student['email'] ?? $student['login_email']); ?></p></div>
            <div class="form-group"><label>Phone</label><p><?php echo htmlspecialchars($student['phone'] ?? '-'); ?></p></div>
            <div class="form-group"><label>Date of Birth</label><p><?php echo htmlspecialchars($student['date_of_birth'] ?? '-'); ?></p></div>
            <div class="form-group"><label>Gender</label><p><?php echo ucfirst(htmlspecialchars($student['gender'])); ?></p></div>
            <div class="form-group form-full"><label>Address</label><p><?php echo htmlspecialchars($student['address'] ?? '-'); ?></p></div>
            <div class="form-group"><label>Guardian</label><p><?php echo htmlspecialchars($student['guardian_name'] ?? '-'); ?></p></div>
            <div class="form-group"><label>Guardian Phone</label><p><?php echo htmlspecialchars($student['guardian_phone'] ?? '-'); ?></p></div>
            <div class="form-group"><label>Status</label><p><span class="badge badge-present"><?php echo ucfirst($student['status']); ?></span></p></div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
