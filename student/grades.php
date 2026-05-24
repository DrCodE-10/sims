<?php
$page_title = 'My Grades';
$root = '..';

require_once __DIR__ . '/../includes/auth.php';
require_student();

$pdo = db();
$studentId = get_student_id();
$avg = student_average_grade($pdo, $studentId);
$gpa = $avg !== null ? round(($avg / 100) * 4, 2) : null;

$stmt = $pdo->prepare(
    "SELECT g.*, c.course_name, c.course_code FROM grades g
     JOIN courses c ON c.id = g.course_id
     WHERE g.student_id = ? ORDER BY g.exam_date DESC"
);
$stmt->execute([$studentId]);
$grades = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/header_student.php';
?>

<div class="glass-panel" style="margin-bottom:20px;display:flex;gap:30px;flex-wrap:wrap;">
    <div><span style="color:var(--text-muted);">Average</span><br><strong style="font-size:24px;color:var(--primary-neon);"><?php echo $avg !== null ? $avg . '%' : '—'; ?></strong></div>
    <div><span style="color:var(--text-muted);">GPA (4.0 scale)</span><br><strong style="font-size:24px;color:var(--secondary-neon);"><?php echo $gpa !== null ? $gpa : '—'; ?></strong></div>
    <div><span style="color:var(--text-muted);">Letter (avg)</span><br><strong style="font-size:24px;"><?php echo $avg !== null ? gpa_letter($avg) : '—'; ?></strong></div>
</div>

<div class="glass-panel">
    <table class="data-table">
        <thead>
            <tr><th>Course</th><th>Exam</th><th>Date</th><th>Score</th><th>%</th><th>Grade</th><th>Remarks</th></tr>
        </thead>
        <tbody>
            <?php if (empty($grades)): ?>
                <tr><td colspan="7" style="text-align:center;color:var(--text-muted);">No grades recorded yet.</td></tr>
            <?php else: ?>
                <?php foreach ($grades as $g): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($g['course_name']); ?></td>
                        <td><?php echo htmlspecialchars($g['exam_type']); ?></td>
                        <td><?php echo htmlspecialchars($g['exam_date']); ?></td>
                        <td><?php echo htmlspecialchars($g['marks_obtained'] . '/' . $g['total_marks']); ?></td>
                        <td><?php echo htmlspecialchars($g['percentage']); ?>%</td>
                        <td><strong><?php echo htmlspecialchars($g['grade']); ?></strong></td>
                        <td><?php echo htmlspecialchars($g['remarks'] ?? '-'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
