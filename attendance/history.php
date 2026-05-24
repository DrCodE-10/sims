<?php
$page_title = 'Attendance History';
$root = '..';

require_once __DIR__ . '/../includes/auth.php';
require_admin();

$pdo = db();
$student_id = (int) ($_GET['student_id'] ?? 0);
$course_id = (int) ($_GET['course_id'] ?? 0);
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

$students = $pdo->query("SELECT id, student_id, first_name, last_name FROM students ORDER BY first_name")->fetchAll(PDO::FETCH_ASSOC);
$courses = $pdo->query("SELECT id, course_code, course_name FROM courses ORDER BY course_name")->fetchAll(PDO::FETCH_ASSOC);

$query = "SELECT a.*, s.student_id AS sid_code, s.first_name, s.last_name, c.course_name
          FROM attendance a
          JOIN students s ON s.id = a.student_id
          JOIN courses c ON c.id = a.course_id
          WHERE 1=1";
$params = [];

if ($student_id > 0) {
    $query .= ' AND a.student_id = ?';
    $params[] = $student_id;
}
if ($course_id > 0) {
    $query .= ' AND a.course_id = ?';
    $params[] = $course_id;
}
if ($start_date) {
    $query .= ' AND a.attendance_date >= ?';
    $params[] = $start_date;
}
if ($end_date) {
    $query .= ' AND a.attendance_date <= ?';
    $params[] = $end_date;
}
$query .= ' ORDER BY a.attendance_date DESC, s.first_name';

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="glass-panel">
    <div class="page-actions">
        <a href="index.php" class="neon-btn" style="text-decoration:none;">Mark Attendance</a>
    </div>

    <form method="GET" class="filter-bar">
        <div class="form-group">
            <label>Student</label>
            <select name="student_id" class="neon-input">
                <option value="0">All Students</option>
                <?php foreach ($students as $s): ?>
                    <option value="<?php echo (int) $s['id']; ?>" <?php echo $student_id === (int) $s['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name'] . ' (' . $s['student_id'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Course</label>
            <select name="course_id" class="neon-input">
                <option value="0">All Courses</option>
                <?php foreach ($courses as $c): ?>
                    <option value="<?php echo (int) $c['id']; ?>" <?php echo $course_id === (int) $c['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($c['course_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>From</label>
            <input type="date" name="start_date" class="neon-input" value="<?php echo htmlspecialchars($start_date); ?>">
        </div>
        <div class="form-group">
            <label>To</label>
            <input type="date" name="end_date" class="neon-input" value="<?php echo htmlspecialchars($end_date); ?>">
        </div>
        <button type="submit" class="neon-btn">Filter</button>
        <a href="history.php" class="neon-btn" style="text-decoration:none;">Reset</a>
    </form>

    <div style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Student</th>
                    <th>Course</th>
                    <th>Status</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($records)): ?>
                    <tr><td colspan="5" style="text-align:center;color:var(--text-muted);">No records found.</td></tr>
                <?php else: ?>
                    <?php foreach ($records as $r): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(date('M d, Y', strtotime($r['attendance_date']))); ?></td>
                            <td><?php echo htmlspecialchars($r['first_name'] . ' ' . $r['last_name'] . ' (' . $r['sid_code'] . ')'); ?></td>
                            <td><?php echo htmlspecialchars($r['course_name']); ?></td>
                            <td><span class="badge badge-<?php echo htmlspecialchars($r['status']); ?>"><?php echo ucfirst($r['status']); ?></span></td>
                            <td><?php echo htmlspecialchars($r['remarks'] ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
