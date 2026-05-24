<?php
$page_title = 'Mark Attendance';
$root = '..';

require_once __DIR__ . '/../includes/auth.php';
require_teacher();

$pdo = db();
$teacherId = get_teacher_id();
$error = '';
$date = $_GET['date'] ?? date('Y-m-d');
$course_id = (int) ($_GET['course_id'] ?? $_POST['course_id'] ?? 0);

$stmt = $pdo->prepare('SELECT id, course_code, course_name FROM courses WHERE teacher_id = ? AND status = ? ORDER BY course_name');
$stmt->execute([$teacherId, 'active']);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($courses)) {
    require_once __DIR__ . '/../includes/header_teacher.php';
    echo '<div class="glass-panel"><p>No courses assigned to you. Contact the administrator.</p></div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

if ($course_id <= 0) {
    $course_id = (int) $courses[0]['id'];
}

$stmt = $pdo->prepare(
    "SELECT s.* FROM students s
     INNER JOIN enrollments e ON e.student_id = s.id AND e.course_id = ? AND e.status = 'enrolled'
     WHERE s.status = 'active'
     ORDER BY s.first_name, s.last_name"
);
$stmt->execute([$course_id]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($students)) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE status = 'active' ORDER BY first_name, last_name");
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$attendance_data = [];
if ($students) {
    $ids = array_column($students, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $params = array_merge($ids, [$course_id, $date]);
    $stmt = $pdo->prepare("SELECT * FROM attendance WHERE student_id IN ($placeholders) AND course_id = ? AND attendance_date = ?");
    $stmt->execute($params);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $attendance_data[$row['student_id']] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $date = $_POST['date'] ?? $date;
    $course_id = (int) ($_POST['course_id'] ?? $course_id);
    try {
        $pdo->beginTransaction();
        foreach ($_POST['attendance'] ?? [] as $sid => $status) {
            if ($status === '') continue;
            $sid = (int) $sid;
            $remarks = trim($_POST['remarks'][$sid] ?? '');
            $check = $pdo->prepare('SELECT id FROM attendance WHERE student_id=? AND course_id=? AND attendance_date=?');
            $check->execute([$sid, $course_id, $date]);
            if ($check->fetch()) {
                $pdo->prepare('UPDATE attendance SET status=?, remarks=?, marked_by=? WHERE student_id=? AND course_id=? AND attendance_date=?')
                    ->execute([$status, $remarks, $teacherId, $sid, $course_id, $date]);
            } else {
                $pdo->prepare('INSERT INTO attendance (student_id, course_id, attendance_date, status, remarks, marked_by) VALUES (?,?,?,?,?,?)')
                    ->execute([$sid, $course_id, $date, $status, $remarks, $teacherId]);
            }
        }
        $pdo->commit();
        flash_set('success', 'Attendance saved.');
        redirect('attendance.php?date=' . urlencode($date) . '&course_id=' . $course_id);
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}

require_once __DIR__ . '/../includes/header_teacher.php';
?>

<div class="glass-panel">
    <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <form method="GET" class="filter-bar">
        <input type="date" name="date" class="neon-input" value="<?php echo htmlspecialchars($date); ?>">
        <select name="course_id" class="neon-input">
            <?php foreach ($courses as $c): ?>
                <option value="<?php echo (int) $c['id']; ?>" <?php echo $course_id === (int) $c['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($c['course_code'] . ' - ' . $c['course_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="neon-btn">Load</button>
    </form>

    <form method="POST">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="date" value="<?php echo htmlspecialchars($date); ?>">
        <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
        <table class="data-table">
            <thead><tr><th>Student</th><th>Status</th><th>Remarks</th></tr></thead>
            <tbody>
                <?php foreach ($students as $s):
                    $cur = $attendance_data[$s['id']]['status'] ?? '';
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name'] . ' (' . $s['student_id'] . ')'); ?></td>
                        <td>
                            <select name="attendance[<?php echo (int) $s['id']; ?>]" class="neon-input">
                                <option value="">--</option>
                                <?php foreach (['present','absent','late','excused'] as $st): ?>
                                    <option value="<?php echo $st; ?>" <?php echo $cur === $st ? 'selected' : ''; ?>><?php echo ucfirst($st); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="text" name="remarks[<?php echo (int) $s['id']; ?>]" class="neon-input" value="<?php echo htmlspecialchars($attendance_data[$s['id']]['remarks'] ?? ''); ?>"></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit" class="neon-btn" style="margin-top:15px;">Save Attendance</button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
