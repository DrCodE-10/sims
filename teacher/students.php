<?php
$page_title = 'My Students';
$root = '..';

require_once __DIR__ . '/../includes/auth.php';
require_teacher();

$pdo = db();
$teacherId = get_teacher_id();

$stmt = $pdo->prepare(
    "SELECT DISTINCT s.*, c.course_name
     FROM students s
     INNER JOIN enrollments e ON e.student_id = s.id
     INNER JOIN courses c ON c.id = e.course_id AND c.teacher_id = ?
     WHERE s.status = 'active'
     ORDER BY s.first_name, s.last_name"
);
$stmt->execute([$teacherId]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/header_teacher.php';
?>

<div class="glass-panel">
    <div style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr><th>Student ID</th><th>Name</th><th>Class</th><th>Course</th><th>Email</th></tr>
            </thead>
            <tbody>
                <?php if (empty($students)): ?>
                    <tr><td colspan="5" style="text-align:center;color:var(--text-muted);">No enrolled students. Enroll students via admin or mark attendance for all active students.</td></tr>
                <?php else: ?>
                    <?php foreach ($students as $s): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($s['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($s['class_grade'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($s['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($s['email'] ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
