<?php
$page_title = 'Mark Attendance';
$root = '..';

require_once __DIR__ . '/../includes/auth.php';
require_admin();

$pdo = db();
$error = '';
$success = '';
$date = $_GET['date'] ?? date('Y-m-d');
$course_id = (int) ($_GET['course_id'] ?? $_POST['course_id'] ?? 0);

if ($course_id <= 0) {
    $course_id = ensure_default_course($pdo);
}

$stmt = $pdo->query("SELECT id, course_code, course_name FROM courses WHERE status = 'active' ORDER BY course_name");
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT * FROM students WHERE status = 'active' ORDER BY first_name, last_name");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

$attendance_data = [];
if ($students) {
    $ids = array_column($students, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $params = array_merge($ids, [$course_id, $date]);
    $stmt = $pdo->prepare(
        "SELECT * FROM attendance WHERE student_id IN ($placeholders) AND course_id = ? AND attendance_date = ?"
    );
    $stmt->execute($params);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $attendance_data[$row['student_id']] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $date = $_POST['date'] ?? $date;
        $course_id = (int) ($_POST['course_id'] ?? $course_id);
        try {
            $pdo->beginTransaction();
            $marked_by = null;
            $teacherStmt = $pdo->prepare('SELECT id FROM teachers WHERE user_id = ? LIMIT 1');
            $teacherStmt->execute([$_SESSION['user_id']]);
            $teacher = $teacherStmt->fetch(PDO::FETCH_ASSOC);
            if ($teacher) {
                $marked_by = $teacher['id'];
            }

            foreach ($_POST['attendance'] ?? [] as $student_id => $status) {
                if ($status === '') {
                    continue;
                }
                $student_id = (int) $student_id;
                $remarks = trim($_POST['remarks'][$student_id] ?? '');

                $check = $pdo->prepare(
                    'SELECT id FROM attendance WHERE student_id = ? AND course_id = ? AND attendance_date = ?'
                );
                $check->execute([$student_id, $course_id, $date]);

                if ($check->fetch()) {
                    $upd = $pdo->prepare(
                        'UPDATE attendance SET status = ?, remarks = ?, marked_by = ? WHERE student_id = ? AND course_id = ? AND attendance_date = ?'
                    );
                    $upd->execute([$status, $remarks, $marked_by, $student_id, $course_id, $date]);
                } else {
                    $ins = $pdo->prepare(
                        'INSERT INTO attendance (student_id, course_id, attendance_date, status, remarks, marked_by) VALUES (?, ?, ?, ?, ?, ?)'
                    );
                    $ins->execute([$student_id, $course_id, $date, $status, $remarks, $marked_by]);
                }

                if ($status === 'absent') {
                    $stu = $pdo->prepare('SELECT first_name, last_name, guardian_email, email FROM students WHERE id = ?');
                    $stu->execute([$student_id]);
                    $s = $stu->fetch(PDO::FETCH_ASSOC);
                    if ($s) {
                        $notifyEmail = $s['guardian_email'] ?: $s['email'];
                        require_once __DIR__ . '/../includes/mailer.php';
                        notify_attendance_alert(
                            $notifyEmail,
                            $s['first_name'] . ' ' . $s['last_name'],
                            $status,
                            $date
                        );
                    }
                }
            }
            $pdo->commit();
            flash_set('success', 'Attendance saved successfully.');
            redirect('index.php?date=' . urlencode($date) . '&course_id=' . $course_id);
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Error saving attendance: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="glass-panel">
    <div class="page-actions">
        <a href="history.php" class="neon-btn" style="text-decoration:none;">View History</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="GET" class="filter-bar">
        <div class="form-group">
            <label for="date">Date</label>
            <input type="date" id="date" name="date" class="neon-input" value="<?php echo htmlspecialchars($date); ?>">
        </div>
        <div class="form-group">
            <label for="course_id">Course</label>
            <select id="course_id" name="course_id" class="neon-input">
                <?php foreach ($courses as $c): ?>
                    <option value="<?php echo (int) $c['id']; ?>" <?php echo $course_id === (int) $c['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($c['course_code'] . ' - ' . $c['course_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="neon-btn">Load</button>
    </form>

    <form method="POST">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="date" value="<?php echo htmlspecialchars($date); ?>">
        <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">

        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr><td colspan="4" style="text-align:center;color:var(--text-muted);">No active students.</td></tr>
                    <?php else: ?>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                <td>
                                    <select name="attendance[<?php echo (int) $student['id']; ?>]" class="neon-input">
                                        <option value="">-- Select --</option>
                                        <?php
                                        $cur = $attendance_data[$student['id']]['status'] ?? '';
                                        foreach (['present', 'absent', 'late', 'excused'] as $st):
                                        ?>
                                            <option value="<?php echo $st; ?>" <?php echo $cur === $st ? 'selected' : ''; ?>><?php echo ucfirst($st); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" class="neon-input" name="remarks[<?php echo (int) $student['id']; ?>]"
                                        value="<?php echo htmlspecialchars($attendance_data[$student['id']]['remarks'] ?? ''); ?>"
                                        placeholder="Optional">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (!empty($students)): ?>
            <div style="margin-top:20px;">
                <button type="submit" class="neon-btn">Save Attendance</button>
            </div>
        <?php endif; ?>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
