<?php
$page_title = 'Enter Grades';
$root = '..';

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/mailer.php';
require_admin();

$pdo = db();
$error = '';
$student_id = (int) ($_GET['student_id'] ?? $_POST['student_id'] ?? 0);

$students = $pdo->query("SELECT id, student_id, first_name, last_name FROM students WHERE status = 'active' ORDER BY first_name")->fetchAll(PDO::FETCH_ASSOC);
$courses = $pdo->query("SELECT id, course_code, course_name FROM courses WHERE status = 'active' ORDER BY course_name")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $student_id = (int) ($_POST['student_id'] ?? 0);
    $course_id = (int) ($_POST['course_id'] ?? 0);
    $exam_type = trim($_POST['exam_type'] ?? '');
    $exam_date = $_POST['exam_date'] ?? date('Y-m-d');
    $marks = $_POST['marks_obtained'] ?? '';
    $total = $_POST['total_marks'] ?? '100';
    $remarks = trim($_POST['remarks'] ?? '');

    if ($student_id <= 0 || $course_id <= 0 || $exam_type === '' || $marks === '') {
        $error = 'Please fill in all required fields.';
    } else {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO grades (student_id, course_id, exam_type, exam_date, marks_obtained, total_marks, remarks)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$student_id, $course_id, $exam_type, $exam_date, $marks, $total, $remarks]);

            $pct = ((float) $marks / (float) $total) * 100;
            $stu = $pdo->prepare('SELECT first_name, last_name, email, guardian_email FROM students WHERE id = ?');
            $stu->execute([$student_id]);
            $s = $stu->fetch(PDO::FETCH_ASSOC);
            if ($s) {
                $to = $s['email'] ?: $s['guardian_email'];
                if ($to) {
                    sims_mail($to, 'SIMS - Grade Posted', "A new grade ({$exam_type}) was recorded: {$marks}/{$total} (" . round($pct, 1) . "%).");
                }
            }

            flash_set('success', 'Grade saved successfully.');
            redirect('enter.php?student_id=' . $student_id);
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $error = 'Invalid security token.';
}

$grades = [];
if ($student_id > 0) {
    $stmt = $pdo->prepare(
        "SELECT g.*, c.course_name FROM grades g
         JOIN courses c ON c.id = g.course_id
         WHERE g.student_id = ? ORDER BY g.exam_date DESC"
    );
    $stmt->execute([$student_id]);
    $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="glass-panel">
    <div class="page-actions">
        <a href="index.php" class="neon-btn" style="text-decoration:none;">Back to GPA List</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" class="form-grid" style="margin-bottom:30px;">
        <?php echo csrf_field(); ?>
        <div class="form-group">
            <label>Student *</label>
            <select name="student_id" class="neon-input" required onchange="location='enter.php?student_id='+this.value">
                <option value="">Select student</option>
                <?php foreach ($students as $s): ?>
                    <option value="<?php echo (int) $s['id']; ?>" <?php echo $student_id === (int) $s['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name'] . ' (' . $s['student_id'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Course *</label>
            <select name="course_id" class="neon-input" required>
                <option value="">Select course</option>
                <?php foreach ($courses as $c): ?>
                    <option value="<?php echo (int) $c['id']; ?>"><?php echo htmlspecialchars($c['course_code'] . ' - ' . $c['course_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Exam Type *</label>
            <input type="text" name="exam_type" class="neon-input" placeholder="e.g. Midterm" required>
        </div>
        <div class="form-group">
            <label>Exam Date</label>
            <input type="date" name="exam_date" class="neon-input" value="<?php echo date('Y-m-d'); ?>">
        </div>
        <div class="form-group">
            <label>Marks Obtained *</label>
            <input type="number" step="0.01" name="marks_obtained" class="neon-input" required>
        </div>
        <div class="form-group">
            <label>Total Marks</label>
            <input type="number" step="0.01" name="total_marks" class="neon-input" value="100">
        </div>
        <div class="form-group form-full">
            <label>Remarks</label>
            <input type="text" name="remarks" class="neon-input">
        </div>
        <div class="form-group form-full">
            <button type="submit" class="neon-btn">Save Grade</button>
        </div>
    </form>

    <?php if ($student_id > 0): ?>
        <h3 class="chart-title" style="margin-bottom:15px;">Grade History</h3>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Exam</th>
                        <th>Date</th>
                        <th>Score</th>
                        <th>%</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($grades)): ?>
                        <tr><td colspan="6" style="text-align:center;color:var(--text-muted);">No grades yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($grades as $g): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($g['course_name']); ?></td>
                                <td><?php echo htmlspecialchars($g['exam_type']); ?></td>
                                <td><?php echo htmlspecialchars($g['exam_date']); ?></td>
                                <td><?php echo htmlspecialchars($g['marks_obtained'] . '/' . $g['total_marks']); ?></td>
                                <td><?php echo htmlspecialchars($g['percentage']); ?>%</td>
                                <td><?php echo htmlspecialchars($g['grade']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
