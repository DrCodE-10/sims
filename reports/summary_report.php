<?php
$page_title = 'Summary Report';
$root = '..';

require_once __DIR__ . '/../includes/auth.php';
require_admin();

$pdo = db();

$totalStudents = (int) $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$activeStudents = (int) $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'active'")->fetchColumn();
$totalTeachers = (int) $pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
$totalCourses = (int) $pdo->query("SELECT COUNT(*) FROM courses WHERE status = 'active'")->fetchColumn();
$attendanceRate = attendance_rate_overall($pdo);

$stmt = $pdo->query(
    "SELECT ROUND(AVG(percentage), 2) AS avg_grade FROM grades WHERE percentage IS NOT NULL"
);
$avgGrade = $stmt->fetchColumn();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="glass-panel">
    <div class="page-actions">
        <button type="button" class="neon-btn" onclick="window.print()">Print / Save PDF</button>
        <a href="index.php" class="neon-btn" style="text-decoration:none;">Back</a>
    </div>

    <div class="report-header">
        <h2>SIMS Summary Report</h2>
        <p><?php echo date('F d, Y'); ?> — Academic overview</p>
    </div>

    <div class="stats-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:15px;">
        <div class="stat-card" style="padding:15px;text-align:center;border:1px solid var(--glass-border);border-radius:12px;">
            <strong style="font-size:28px;color:var(--primary-neon);"><?php echo $totalStudents; ?></strong>
            <p>Total Students</p>
        </div>
        <div class="stat-card" style="padding:15px;text-align:center;border:1px solid var(--glass-border);border-radius:12px;">
            <strong style="font-size:28px;color:var(--accent-neon);"><?php echo $activeStudents; ?></strong>
            <p>Active Students</p>
        </div>
        <div class="stat-card" style="padding:15px;text-align:center;border:1px solid var(--glass-border);border-radius:12px;">
            <strong style="font-size:28px;color:var(--primary-neon);"><?php echo $totalTeachers; ?></strong>
            <p>Teachers</p>
        </div>
        <div class="stat-card" style="padding:15px;text-align:center;border:1px solid var(--glass-border);border-radius:12px;">
            <strong style="font-size:28px;color:var(--secondary-neon);"><?php echo $totalCourses; ?></strong>
            <p>Active Courses</p>
        </div>
        <div class="stat-card" style="padding:15px;text-align:center;border:1px solid var(--glass-border);border-radius:12px;">
            <strong style="font-size:28px;color:var(--accent-neon);"><?php echo $attendanceRate; ?>%</strong>
            <p>Attendance Rate</p>
        </div>
        <div class="stat-card" style="padding:15px;text-align:center;border:1px solid var(--glass-border);border-radius:12px;">
            <strong style="font-size:28px;color:var(--primary-neon);"><?php echo $avgGrade ?: '—'; ?></strong>
            <p>Avg Grade %</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
