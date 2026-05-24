<?php
$page_title = 'Student Report';
$root = '..';

require_once __DIR__ . '/../includes/auth.php';
require_admin();

$pdo = db();
$status = $_GET['status'] ?? '';

$query = 'SELECT * FROM students WHERE 1=1';
$params = [];
if ($status !== '') {
    $query .= ' AND status = ?';
    $params[] = $status;
}
$query .= ' ORDER BY first_name, last_name';

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="glass-panel">
    <div class="page-actions">
        <button type="button" class="neon-btn" onclick="window.print()">Print / Save PDF</button>
        <a href="index.php" class="neon-btn" style="text-decoration:none;">Back</a>
    </div>

    <form method="GET" class="filter-bar no-print">
        <select name="status" class="neon-input">
            <option value="">All Status</option>
            <?php foreach (['active', 'inactive', 'graduated', 'suspended'] as $st): ?>
                <option value="<?php echo $st; ?>" <?php echo $status === $st ? 'selected' : ''; ?>><?php echo ucfirst($st); ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="neon-btn">Filter</button>
    </form>

    <div class="report-header">
        <h2>Student Information Report</h2>
        <p>Generated: <?php echo date('F d, Y g:i A'); ?></p>
    </div>

    <div style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Class</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $i => $s): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><?php echo htmlspecialchars($s['student_id']); ?></td>
                        <td><?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($s['class_grade'] . ($s['section'] ? ' - ' . $s['section'] : '')); ?></td>
                        <td><?php echo htmlspecialchars($s['email'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($s['phone'] ?? '-'); ?></td>
                        <td><?php echo ucfirst(htmlspecialchars($s['status'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
