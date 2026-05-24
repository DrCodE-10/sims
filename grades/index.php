<?php
$page_title = 'Grades & GPA';
$root = '..';

require_once __DIR__ . '/../includes/auth.php';
require_admin();

$pdo = db();
$search = trim($_GET['search'] ?? '');

$query = "SELECT s.id, s.student_id, s.first_name, s.last_name, s.class_grade,
          ROUND(AVG(g.percentage), 2) AS avg_percentage,
          COUNT(g.id) AS grade_count
          FROM students s
          LEFT JOIN grades g ON g.student_id = s.id
          WHERE s.status = 'active'";
$params = [];

if ($search !== '') {
    $query .= " AND (s.student_id LIKE ? OR s.first_name LIKE ? OR s.last_name LIKE ?)";
    $like = '%' . $search . '%';
    $params = [$like, $like, $like];
}
$query .= " GROUP BY s.id ORDER BY s.first_name, s.last_name";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="glass-panel">
    <div class="page-actions">
        <a href="enter.php" class="neon-btn" style="text-decoration:none;">Enter Grades</a>
    </div>

    <form method="GET" class="filter-bar">
        <input type="text" name="search" class="neon-input" placeholder="Search students..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="neon-btn">Search</button>
    </form>

    <div style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Class</th>
                    <th>Avg %</th>
                    <th>GPA (4.0 scale)</th>
                    <th>Grades</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($students)): ?>
                    <tr><td colspan="7" style="text-align:center;color:var(--text-muted);">No students found.</td></tr>
                <?php else: ?>
                    <?php foreach ($students as $s):
                        $avg = $s['avg_percentage'] !== null ? (float) $s['avg_percentage'] : null;
                        $gpa = $avg !== null ? round(($avg / 100) * 4, 2) : null;
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($s['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($s['class_grade'] ?? '-'); ?></td>
                            <td><?php echo $avg !== null ? $avg . '%' : '—'; ?></td>
                            <td><?php echo $gpa !== null ? $gpa : '—'; ?></td>
                            <td><?php echo (int) $s['grade_count']; ?></td>
                            <td>
                                <a href="enter.php?student_id=<?php echo (int) $s['id']; ?>" class="neon-btn" style="text-decoration:none;padding:6px 12px;font-size:12px;">Manage</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
