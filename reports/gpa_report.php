<?php
$page_title = 'GPA Report';
$root = '..';

require_once __DIR__ . '/../includes/auth.php';
require_admin();

$pdo = db();
$class_grade = $_GET['class_grade'] ?? '';

$query = "SELECT s.student_id, s.first_name, s.last_name, s.class_grade,
          ROUND(AVG(g.percentage), 2) AS avg_pct, COUNT(g.id) AS cnt
          FROM students s
          LEFT JOIN grades g ON g.student_id = s.id
          WHERE s.status = 'active'";
$params = [];
if ($class_grade !== '') {
    $query .= ' AND s.class_grade = ?';
    $params[] = $class_grade;
}
$query .= ' GROUP BY s.id ORDER BY avg_pct DESC';

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$classes = $pdo->query("SELECT DISTINCT class_grade FROM students WHERE class_grade IS NOT NULL ORDER BY class_grade")->fetchAll(PDO::FETCH_COLUMN);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="glass-panel">
    <div class="page-actions">
        <button type="button" class="neon-btn" onclick="window.print()">Print / Save PDF</button>
        <a href="index.php" class="neon-btn" style="text-decoration:none;">Back</a>
    </div>

    <form method="GET" class="filter-bar no-print">
        <select name="class_grade" class="neon-input">
            <option value="">All Classes</option>
            <?php foreach ($classes as $c): ?>
                <option value="<?php echo htmlspecialchars($c); ?>" <?php echo $class_grade === $c ? 'selected' : ''; ?>>Class <?php echo htmlspecialchars($c); ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="neon-btn">Filter</button>
    </form>

    <div class="report-header">
        <h2>GPA / Academic Performance Report</h2>
        <p>Generated: <?php echo date('F d, Y'); ?></p>
    </div>

    <table class="data-table">
        <thead>
            <tr><th>Student ID</th><th>Name</th><th>Class</th><th>Avg %</th><th>GPA (4.0)</th><th>Letter</th><th>Exams</th></tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r):
                $avg = $r['avg_pct'] !== null ? (float) $r['avg_pct'] : null;
                $gpa = $avg !== null ? round(($avg / 100) * 4, 2) : null;
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['student_id']); ?></td>
                    <td><?php echo htmlspecialchars($r['first_name'] . ' ' . $r['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($r['class_grade'] ?? '-'); ?></td>
                    <td><?php echo $avg !== null ? $avg . '%' : '—'; ?></td>
                    <td><?php echo $gpa !== null ? $gpa : '—'; ?></td>
                    <td><?php echo $avg !== null ? gpa_letter($avg) : '—'; ?></td>
                    <td><?php echo (int) $r['cnt']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
