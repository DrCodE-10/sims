<?php
$page_title = 'Student Dashboard';
$root = '..';

require_once __DIR__ . '/../includes/auth.php';
require_student();

$pdo = db();
$studentId = get_student_id();

$stmt = $pdo->prepare('SELECT * FROM students WHERE id = ?');
$stmt->execute([$studentId]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

$attendanceRate = student_attendance_rate($pdo, $studentId);
$avgGrade = student_average_grade($pdo, $studentId);
$gpa = $avgGrade !== null ? round(($avgGrade / 100) * 4, 2) : null;

$stmt = $pdo->prepare(
    "SELECT g.*, c.course_name FROM grades g JOIN courses c ON c.id = g.course_id
     WHERE g.student_id = ? ORDER BY g.exam_date DESC LIMIT 5"
);
$stmt->execute([$studentId]);
$recentGrades = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare(
    "SELECT a.attendance_date, a.status, c.course_name FROM attendance a
     JOIN courses c ON c.id = a.course_id WHERE a.student_id = ?
     ORDER BY a.attendance_date DESC LIMIT 5"
);
$stmt->execute([$studentId]);
$recentAttendance = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/header_student.php';
?>

<div class="glass-panel" style="margin-bottom:20px;">
    <h3 style="margin-bottom:10px;">Hello, <?php echo htmlspecialchars($student['first_name']); ?> 👋</h3>
    <p style="color:var(--text-secondary);">Class <?php echo htmlspecialchars($student['class_grade'] . ($student['section'] ? ' - ' . $student['section'] : '')); ?> · ID: <?php echo htmlspecialchars($student['student_id']); ?></p>
</div>

<div class="stats-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:15px;">
    <div class="glass-panel" style="text-align:center;">
        <div style="font-size:28px;color:var(--accent-neon);"><?php echo $attendanceRate; ?>%</div>
        <div class="stat-label">Attendance</div>
    </div>
    <div class="glass-panel" style="text-align:center;">
        <div style="font-size:28px;color:var(--primary-neon);"><?php echo $avgGrade !== null ? $avgGrade . '%' : '—'; ?></div>
        <div class="stat-label">Average Grade</div>
    </div>
    <div class="glass-panel" style="text-align:center;">
        <div style="font-size:28px;color:var(--secondary-neon);"><?php echo $gpa !== null ? $gpa : '—'; ?></div>
        <div class="stat-label">GPA (4.0)</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:20px;margin-top:20px;">
    <div class="glass-panel">
        <h3 class="chart-title" style="margin-bottom:15px;">Recent Grades</h3>
        <?php if (empty($recentGrades)): ?>
            <p style="color:var(--text-muted);">No grades recorded yet.</p>
        <?php else: ?>
            <table class="data-table">
                <thead><tr><th>Course</th><th>Exam</th><th>Score</th><th>Grade</th></tr></thead>
                <tbody>
                    <?php foreach ($recentGrades as $g): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($g['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($g['exam_type']); ?></td>
                            <td><?php echo htmlspecialchars($g['marks_obtained'] . '/' . $g['total_marks']); ?></td>
                            <td><?php echo htmlspecialchars($g['grade']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <a href="grades.php" class="neon-btn" style="display:inline-block;margin-top:15px;text-decoration:none;">View All Grades</a>
    </div>

    <div class="glass-panel">
        <h3 class="chart-title" style="margin-bottom:15px;">Recent Attendance</h3>
        <?php if (empty($recentAttendance)): ?>
            <p style="color:var(--text-muted);">No attendance records yet.</p>
        <?php else: ?>
            <table class="data-table">
                <thead><tr><th>Date</th><th>Course</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach ($recentAttendance as $a): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(date('M d, Y', strtotime($a['attendance_date']))); ?></td>
                            <td><?php echo htmlspecialchars($a['course_name']); ?></td>
                            <td><span class="badge badge-<?php echo htmlspecialchars($a['status']); ?>"><?php echo ucfirst($a['status']); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <a href="attendance.php" class="neon-btn" style="display:inline-block;margin-top:15px;text-decoration:none;">View All Attendance</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
