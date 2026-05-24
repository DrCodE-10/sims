<?php
$page_title = 'Teachers';
$root = '..';

require_once __DIR__ . '/../includes/auth.php';
require_admin();

$pdo = db();
$teachers = $pdo->query(
    "SELECT t.*, u.username, u.email AS login_email
     FROM teachers t LEFT JOIN users u ON u.id = t.user_id ORDER BY t.first_name"
)->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="glass-panel">
    <div class="page-actions">
        <a href="add_teacher.php" class="neon-btn" style="text-decoration:none;">Add Teacher</a>
    </div>
    <table class="data-table">
        <thead>
            <tr><th>ID</th><th>Name</th><th>Specialization</th><th>Login</th><th>Status</th></tr>
        </thead>
        <tbody>
            <?php if (empty($teachers)): ?>
                <tr><td colspan="5" style="text-align:center;color:var(--text-muted);">No teachers. Add one or run database/demo_users.sql</td></tr>
            <?php else: ?>
                <?php foreach ($teachers as $t): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($t['teacher_id']); ?></td>
                        <td><?php echo htmlspecialchars($t['first_name'] . ' ' . $t['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($t['specialization'] ?? '-'); ?></td>
                        <td><?php echo $t['username'] ? htmlspecialchars($t['username']) : '<span style="color:var(--text-muted);">No account</span>'; ?></td>
                        <td><?php echo ucfirst($t['status']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
