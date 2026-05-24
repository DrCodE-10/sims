<?php
$page_title = 'My Profile';
$root = '..';

require_once __DIR__ . '/../includes/auth.php';
require_teacher();

$pdo = db();
$stmt = $pdo->prepare('SELECT t.*, u.email AS login_email, u.username FROM teachers t JOIN users u ON u.id = t.user_id WHERE t.id = ?');
$stmt->execute([get_teacher_id()]);
$teacher = $stmt->fetch(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/header_teacher.php';
?>

<div class="glass-panel">
    <?php if ($teacher): ?>
        <div class="form-grid">
            <div class="form-group"><label>Teacher ID</label><p><?php echo htmlspecialchars($teacher['teacher_id']); ?></p></div>
            <div class="form-group"><label>Username</label><p><?php echo htmlspecialchars($teacher['username']); ?></p></div>
            <div class="form-group"><label>Name</label><p><?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?></p></div>
            <div class="form-group"><label>Email</label><p><?php echo htmlspecialchars($teacher['email'] ?? $teacher['login_email']); ?></p></div>
            <div class="form-group"><label>Phone</label><p><?php echo htmlspecialchars($teacher['phone'] ?? '-'); ?></p></div>
            <div class="form-group"><label>Specialization</label><p><?php echo htmlspecialchars($teacher['specialization'] ?? '-'); ?></p></div>
            <div class="form-group"><label>Qualification</label><p><?php echo htmlspecialchars($teacher['qualification'] ?? '-'); ?></p></div>
            <div class="form-group"><label>Status</label><p><span class="badge badge-present"><?php echo ucfirst($teacher['status']); ?></span></p></div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
