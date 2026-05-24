<?php
require_login();
if (($_SESSION['role'] ?? '') !== 'student') {
    flash_set('error', 'Student access only.');
    redirect(home_url_for_role());
}

$root = $root ?? '..';
$page_title = $page_title ?? 'Student';
$current_script = basename($_SERVER['PHP_SELF']);

function student_nav_active(string $file): string
{
    global $current_script;
    return $current_script === $file ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#000000">
    <title><?php echo htmlspecialchars($page_title); ?> - SIMS Student</title>
    <link rel="stylesheet" href="<?php echo $root; ?>/assets/css/futuristic.css">
    <link rel="stylesheet" href="<?php echo $root; ?>/assets/css/animations.css">
    <link rel="stylesheet" href="<?php echo $root; ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?php echo $root; ?>/assets/css/admin.css">
</head>
<body>
    <div class="matrix-bg" aria-hidden="true"></div>
    <div class="dashboard-container">
        <aside class="sidebar fade-in-left">
            <div class="sidebar-header">
                <h1 class="sidebar-logo">SIMS</h1>
                <p class="sidebar-subtitle">Student Portal</p>
            </div>
            <nav>
                <ul class="nav-menu">
                    <li class="nav-item <?php echo student_nav_active('index.php'); ?>">
                        <a href="<?php echo $root; ?>/student/index.php" class="nav-link"><span class="nav-icon">📊</span><span>Dashboard</span></a>
                    </li>
                    <li class="nav-item <?php echo student_nav_active('attendance.php'); ?>">
                        <a href="<?php echo $root; ?>/student/attendance.php" class="nav-link"><span class="nav-icon">📋</span><span>My Attendance</span></a>
                    </li>
                    <li class="nav-item <?php echo student_nav_active('grades.php'); ?>">
                        <a href="<?php echo $root; ?>/student/grades.php" class="nav-link"><span class="nav-icon">📝</span><span>My Grades</span></a>
                    </li>
                    <li class="nav-item <?php echo student_nav_active('profile.php'); ?>">
                        <a href="<?php echo $root; ?>/student/profile.php" class="nav-link"><span class="nav-icon">👤</span><span>Profile</span></a>
                    </li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <a href="<?php echo $root; ?>/logout.php" class="nav-item nav-link" style="color:var(--danger-neon);">
                    <span class="nav-icon">🚪</span><span>Logout</span>
                </a>
            </div>
        </aside>
        <main class="main-content">
            <header class="dashboard-header fade-in-down">
                <div>
                    <h1 class="header-title"><?php echo htmlspecialchars($page_title); ?></h1>
                    <p class="header-subtitle"><?php echo htmlspecialchars($_SESSION['full_name'] ?? ''); ?> · <?php echo htmlspecialchars($_SESSION['student_code'] ?? ''); ?></p>
                </div>
            </header>
            <?php $flash = flash_get(); if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type'] === 'error' ? 'error' : 'success'; ?> glass-card"><?php echo htmlspecialchars($flash['message']); ?></div>
            <?php endif; ?>
            <div class="admin-page-content">
