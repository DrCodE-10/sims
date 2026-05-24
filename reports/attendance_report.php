<?php
$page_title = 'Attendance Report';
$root = '..';

require_once __DIR__ . '/../includes/auth.php';
require_admin();

$pdo = db();
$start = $_GET['start_date'] ?? date('Y-m-01');
$end = $_GET['end_date'] ?? date('Y-m-t');
$course_id = (int) ($_GET['course_id'] ?? 0);

$query = "SELECT a.*, s.student_id AS sid, s.first_name, s.last_name, c.course_name
          FROM attendance a
          JOIN students s ON s.id = a.student_id
          JOIN courses c ON c.id = a.course_id
          WHERE a.attendance_date BETWEEN ? AND ?";
$params = [$start, $end];

if ($course_id > 0) {
    $query .= ' AND a.course_id = ?';
    $params[] = $course_id;
}
$query .= ' ORDER BY a.attendance_date DESC';

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

$courses = $pdo->query("SELECT id, course_name FROM courses ORDER BY course_name")->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="glass-panel">
    <div class="page-actions">
        <button type="button" class="neon-btn" onclick="window.print()">Print / Save PDF</button>
        <a href="index.php" class="neon-btn" style="text-decoration:none;">Back</a>
    </div>

    <form method="GET" class="filter-bar no-print">
        <input type="date" name="start_date" class="neon-input" value="<?php echo htmlspecialchars($start); ?>">
        <input type="date" name="end_date" class="neon-input" value="<?php echo htmlspecialchars($end); ?>">
        <select name="course_id" class="neon-input">
            <option value="0">All Courses</option>
            <?php foreach ($courses as $c): ?>
                <option value="<?php echo (int) $c['id']; ?>" <?php echo $course_id === (int) $c['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['course_name']); ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="neon-btn">Filter</button>
    </form>

    <div class="report-header">
        <h2>Attendance Report</h2>
        <p><?php echo htmlspecialchars($start); ?> to <?php echo htmlspecialchars($end); ?></p>
    </div>

    <table class="data-table">
        <thead>
            <tr><th>Date</th><th>Student</th><th>Course</th><th>Status</th><th>Remarks</th></tr>
        </thead>
        <tbody>
            <?php if (empty($records)): ?>
                <tr><td colspan="5" style="text-align:center;">No records.</td></tr>
            <?php else: ?>
                <?php foreach ($records as $r): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($r['attendance_date']); ?></td>
                        <td><?php echo htmlspecialchars($r['first_name'] . ' ' . $r['last_name'] . ' (' . $r['sid'] . ')'); ?></td>
                        <td><?php echo htmlspecialchars($r['course_name']); ?></td>
                        <td><?php echo ucfirst($r['status']); ?></td>
                        <td><?php echo htmlspecialchars($r['remarks'] ?? '-'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
