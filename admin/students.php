<?php
$page_title = 'Students';
$root = '..';

require_once __DIR__ . '/../includes/auth.php';
require_admin();

$pdo = db();
$search = trim($_GET['search'] ?? '');
$filter_class = trim($_GET['class'] ?? '');

$query = 'SELECT * FROM students WHERE 1=1';
$params = [];

if ($search !== '') {
    $query .= ' AND (student_id LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)';
    $like = '%' . $search . '%';
    $params = array_merge($params, [$like, $like, $like, $like]);
}
if ($filter_class !== '') {
    $query .= ' AND class_grade = ?';
    $params[] = $filter_class;
}
$query .= ' ORDER BY created_at DESC';

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

$classStmt = $pdo->query('SELECT DISTINCT class_grade FROM students WHERE class_grade IS NOT NULL ORDER BY class_grade');
$classes = $classStmt->fetchAll(PDO::FETCH_COLUMN);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="glass-panel">
    <div class="page-actions">
        <a href="add_student.php" class="neon-btn" style="text-decoration:none;">Add Student</a>
    </div>

    <form method="GET" class="filter-bar">
        <input type="text" name="search" class="neon-input" placeholder="Search ID, name, email..." value="<?php echo htmlspecialchars($search); ?>">
        <select name="class" class="neon-input">
            <option value="">All Classes</option>
            <?php foreach ($classes as $class): ?>
                <option value="<?php echo htmlspecialchars($class); ?>" <?php echo $filter_class === $class ? 'selected' : ''; ?>>Class <?php echo htmlspecialchars($class); ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="neon-btn">Search</button>
        <a href="students.php" class="neon-btn" style="text-decoration:none;">Clear</a>
    </form>

    <div style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>ID</th>
                    <th>Class</th>
                    <th>Gender</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($students)): ?>
                    <tr><td colspan="7" style="text-align:center;color:var(--text-muted);">No students found.</td></tr>
                <?php else: ?>
                    <?php foreach ($students as $student):
                        $fullName = $student['first_name'] . ' ' . $student['last_name'];
                    ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($fullName); ?></strong><br>
                                <small style="color:var(--text-muted);"><?php echo htmlspecialchars($student['email'] ?? 'N/A'); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($student['class_grade'] . ($student['section'] ? ' - ' . $student['section'] : '')); ?></td>
                            <td><?php echo ucfirst(htmlspecialchars($student['gender'])); ?></td>
                            <td><?php echo htmlspecialchars($student['phone'] ?? 'N/A'); ?></td>
                            <td><span class="badge badge-<?php echo $student['status'] === 'active' ? 'present' : 'absent'; ?>"><?php echo ucfirst($student['status']); ?></span></td>
                            <td style="white-space:nowrap;">
                                <a href="edit_student.php?id=<?php echo (int) $student['id']; ?>" class="neon-btn" style="text-decoration:none;padding:6px 12px;font-size:12px;">Edit</a>
                                <form method="POST" action="delete_student.php" style="display:inline;" onsubmit="return confirm('Delete <?php echo htmlspecialchars(addslashes($fullName)); ?>?');">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="id" value="<?php echo (int) $student['id']; ?>">
                                    <button type="submit" class="neon-btn" style="padding:6px 12px;font-size:12px;background:rgba(255,51,102,0.2);border-color:rgba(255,51,102,0.4);">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <p style="margin-top:15px;color:var(--text-muted);font-size:14px;">Showing <?php echo count($students); ?> student(s)</p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
