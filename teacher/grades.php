<?php
$page_title = 'Enter Grades';
$root = '..';

require_once __DIR__ . '/../includes/auth.php';
require_teacher();

$pdo = db();
$teacherId = get_teacher_id();
$error = '';

$stmt = $pdo->prepare('SELECT id, course_code, course_name FROM courses WHERE teacher_id = ? AND status = ? ORDER BY course_name');
$stmt->execute([$teacherId, 'active']);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare(
    "SELECT DISTINCT s.id, s.student_id, s.first_name, s.last_name
     FROM students s
     INNER JOIN enrollments e ON e.student_id = s.id
     INNER JOIN courses c ON c.id = e.course_id AND c.teacher_id = ?
     WHERE s.status = 'active' ORDER BY s.first_name"
);
$stmt->execute([$teacherId]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $student_id = (int) ($_POST['student_id'] ?? 0);
    $course_id = (int) ($_POST['course_id'] ?? 0);
    $exam_type = trim($_POST['exam_type'] ?? '');
    $exam_date = $_POST['exam_date'] ?? date('Y-m-d');
    $marks = $_POST['marks_obtained'] ?? '';
    $total = $_POST['total_marks'] ?? '100';

    $owns = $pdo->prepare('SELECT id FROM courses WHERE id = ? AND teacher_id = ?');
    $owns->execute([$course_id, $teacherId]);
    if (!$owns->fetch()) {
        $error = 'Invalid course selection.';
    } elseif ($student_id <= 0 || $course_id <= 0 || $exam_type === '' || $marks === '') {
        $error = 'Please fill all required fields.';
    } else {
        try {
            $pdo->prepare(
                'INSERT INTO grades (student_id, course_id, exam_type, exam_date, marks_obtained, total_marks, graded_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            )->execute([$student_id, $course_id, $exam_type, $exam_date, $marks, $total, $teacherId]);
            flash_set('success', 'Grade recorded.');
            redirect('grades.php');
        } catch (PDOException $e) {
            $error = $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header_teacher.php';
?>

<div class="glass-panel">
    <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <?php if (empty($courses)): ?>
        <p style="color:var(--text-muted);">No courses assigned. Contact admin.</p>
    <?php else: ?>
        <form method="POST" class="form-grid">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label>Student *</label>
                <select name="student_id" class="neon-input" required>
                    <option value="">Select</option>
                    <?php foreach ($students as $s): ?>
                        <option value="<?php echo (int) $s['id']; ?>"><?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Course *</label>
                <select name="course_id" class="neon-input" required>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?php echo (int) $c['id']; ?>"><?php echo htmlspecialchars($c['course_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>Exam Type *</label><input type="text" name="exam_type" class="neon-input" required></div>
            <div class="form-group"><label>Exam Date</label><input type="date" name="exam_date" class="neon-input" value="<?php echo date('Y-m-d'); ?>"></div>
            <div class="form-group"><label>Marks *</label><input type="number" step="0.01" name="marks_obtained" class="neon-input" required></div>
            <div class="form-group"><label>Total</label><input type="number" step="0.01" name="total_marks" class="neon-input" value="100"></div>
            <div class="form-group form-full"><button type="submit" class="neon-btn">Save Grade</button></div>
        </form>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
