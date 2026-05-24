<?php
$page_title = 'My Attendance';
$root = '..';

require_once __DIR__ . '/../includes/auth.php';
require_student();

$pdo = db();
$studentId = get_student_id();
$rate = student_attendance_rate($pdo, $studentId);

$stmt = $pdo->prepare(
    "SELECT a.*, c.course_name, c.course_code FROM attendance a
     JOIN courses c ON c.id = a.course_id
     WHERE a.student_id = ? ORDER BY a.attendance_date DESC"
);
$stmt->execute([$studentId]);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

$present = $absent = $late = 0;
foreach ($records as $r) {
    if ($r['status'] === 'present') $present++;
    elseif ($r['status'] === 'absent') $absent++;
    elseif ($r['status'] === 'late') $late++;
}

require_once __DIR__ . '/../includes/header_student.php';
?>

<div class="stats-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:20px;">
    <div class="glass-panel" style="text-align:center;"><strong style="font-size:24px;color:var(--accent-neon);"><?php echo $rate; ?>%</strong><br>Overall</div>
    <div class="glass-panel" style="text-align:center;"><strong style="font-size:24px;"><?php echo $present; ?></strong><br>Present</div>
    <div class="glass-panel" style="text-align:center;"><strong style="font-size:24px;color:var(--danger-neon);"><?php echo $absent; ?></strong><br>Absent</div>
    <div class="glass-panel" style="text-align:center;"><strong style="font-size:24px;"><?php echo $late; ?></strong><br>Late</div>
</div>

<div class="glass-panel">
    <table class="data-table">
        <thead><tr><th>Date</th><th>Course</th><th>Status</th><th>Remarks</th></tr></thead>
        <tbody>
            <?php if (empty($records)): ?>
                <tr><td colspan="4" style="text-align:center;color:var(--text-muted);">No attendance records.</td></tr>
            <?php else: ?>
                <?php foreach ($records as $r): ?>
                    <tr>
                        <td><?php echo htmlspecialchars(date('M d, Y', strtotime($r['attendance_date']))); ?></td>
                        <td><?php echo htmlspecialchars($r['course_code'] . ' - ' . $r['course_name']); ?></td>
                        <td><span class="badge badge-<?php echo htmlspecialchars($r['status']); ?>"><?php echo ucfirst($r['status']); ?></span></td>
                        <td><?php echo htmlspecialchars($r['remarks'] ?? '-'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
