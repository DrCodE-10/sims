<?php
$page_title = 'Reports';
$root = '..';

require_once __DIR__ . '/../includes/auth.php';
require_admin();

$pdo = db();

$totalStudents = (int) $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'active'")->fetchColumn();
$today = date('Y-m-d');

$stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE attendance_date = ?");
$stmt->execute([$today]);
$attendanceToday = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE attendance_date = ? AND status = 'present'");
$stmt->execute([$today]);
$presentToday = (int) $stmt->fetchColumn();

$rate = attendance_rate_overall($pdo);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="stats-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px;margin-bottom:20px;">
    <div class="stat-card glass-card" style="padding:20px;text-align:center;">
        <div class="stat-value"><?php echo $totalStudents; ?></div>
        <div class="stat-label">Active Students</div>
    </div>
    <div class="stat-card glass-card" style="padding:20px;text-align:center;">
        <div class="stat-value"><?php echo $attendanceToday; ?></div>
        <div class="stat-label">Marked Today</div>
    </div>
    <div class="stat-card glass-card" style="padding:20px;text-align:center;">
        <div class="stat-value"><?php echo $presentToday; ?></div>
        <div class="stat-label">Present Today</div>
    </div>
    <div class="stat-card glass-card" style="padding:20px;text-align:center;">
        <div class="stat-value"><?php echo $rate; ?>%</div>
        <div class="stat-label">Overall Attendance</div>
    </div>
</div>

<div class="glass-panel">
    <h3 class="chart-title" style="margin-bottom:20px;">Generate Reports</h3>
    <div class="page-actions" style="justify-content:flex-start;">
        <a href="students_report.php" class="neon-btn" style="text-decoration:none;">Student Report</a>
        <a href="attendance_report.php" class="neon-btn" style="text-decoration:none;">Attendance Report</a>
        <a href="gpa_report.php" class="neon-btn" style="text-decoration:none;">GPA Report</a>
        <a href="summary_report.php" class="neon-btn" style="text-decoration:none;">Summary Report</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
