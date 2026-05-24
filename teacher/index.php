<?php
$page_title = 'Teacher Dashboard';
$root = '..';

require_once __DIR__ . '/../includes/auth.php';
require_teacher();

$pdo = db();
$teacherId = get_teacher_id();
$today = date('Y-m-d');

$stmt = $pdo->prepare('SELECT COUNT(*) FROM courses WHERE teacher_id = ? AND status = ?');
$stmt->execute([$teacherId, 'active']);
$courseCount = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare(
    "SELECT COUNT(DISTINCT e.student_id) FROM enrollments e
     JOIN courses c ON c.id = e.course_id WHERE c.teacher_id = ? AND e.status = 'enrolled'"
);
$stmt->execute([$teacherId]);
$studentCount = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare(
    "SELECT COUNT(*) FROM attendance a JOIN courses c ON c.id = a.course_id
     WHERE c.teacher_id = ? AND a.attendance_date = ?"
);
$stmt->execute([$teacherId, $today]);
$markedToday = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare(
    "SELECT c.course_name, c.course_code,
            COUNT(a.id) AS total,
            SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) AS present
     FROM courses c
     LEFT JOIN attendance a ON a.course_id = c.id AND a.attendance_date = ?
     WHERE c.teacher_id = ? AND c.status = 'active'
     GROUP BY c.id ORDER BY c.course_name LIMIT 5"
);
$stmt->execute([$today, $teacherId]);
$coursesToday = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/header_teacher.php';
?>

<div class="stats-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:15px;">
    <div class="glass-panel" style="text-align:center;">
        <div class="stat-value" style="font-size:32px;color:var(--primary-neon);"><?php echo $courseCount; ?></div>
        <div class="stat-label">My Courses</div>
    </div>
    <div class="glass-panel" style="text-align:center;">
        <div class="stat-value" style="font-size:32px;color:var(--accent-neon);"><?php echo $studentCount; ?></div>
        <div class="stat-label">Enrolled Students</div>
    </div>
    <div class="glass-panel" style="text-align:center;">
        <div class="stat-value" style="font-size:32px;color:var(--secondary-neon);"><?php echo $markedToday; ?></div>
        <div class="stat-label">Attendance Marked Today</div>
    </div>
</div>

<div class="glass-panel" style="margin-top:20px;">
    <h3 class="chart-title" style="margin-bottom:15px;">Today's Classes</h3>
    <?php if (empty($coursesToday)): ?>
        <p style="color:var(--text-muted);">No courses assigned yet. Contact the administrator.</p>
    <?php else: ?>
        <table class="data-table">
            <thead><tr><th>Course</th><th>Marked Today</th><th>Present</th></tr></thead>
            <tbody>
                <?php foreach ($coursesToday as $c): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($c['course_code'] . ' - ' . $c['course_name']); ?></td>
                        <td><?php echo (int) $c['total']; ?></td>
                        <td><?php echo (int) $c['present']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <div style="margin-top:20px;display:flex;gap:10px;flex-wrap:wrap;">
        <a href="attendance.php" class="neon-btn" style="text-decoration:none;">Mark Attendance</a>
        <a href="grades.php" class="neon-btn" style="text-decoration:none;">Enter Grades</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
