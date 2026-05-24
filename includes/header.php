<?php
/**
 * SIMS - Admin layout header (for admin/, attendance/, reports/, grades/)
 */
require_login();

$root = $root ?? '..';
$page_title = $page_title ?? 'SIMS';
$current_script = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

function nav_active(string $dir, string $script = ''): string
{
    global $current_dir, $current_script;
    if ($script !== '') {
        return ($current_dir === $dir && $current_script === $script) ? 'active' : '';
    }
    return $current_dir === $dir ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#000000">
    <title><?php echo htmlspecialchars($page_title); ?> - SIMS</title>
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
                <p class="sidebar-subtitle">Administration</p>
            </div>
            <nav>
                <ul class="nav-menu">
                    <li class="nav-item <?php echo $current_script === 'index.php' && $current_dir === 'SIMS' ? 'active' : ''; ?>">
                        <a href="<?php echo $root; ?>/index.php" class="nav-link">
                            <span class="nav-icon">📊</span><span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo nav_active('admin', 'students.php'); ?>">
                        <a href="<?php echo $root; ?>/admin/students.php" class="nav-link">
                            <span class="nav-icon">👥</span><span>Students</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo nav_active('admin', 'add_student.php'); ?>">
                        <a href="<?php echo $root; ?>/admin/add_student.php" class="nav-link">
                            <span class="nav-icon">➕</span><span>Add Student</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo nav_active('admin', 'teachers.php'); ?>">
                        <a href="<?php echo $root; ?>/admin/teachers.php" class="nav-link">
                            <span class="nav-icon">👨‍🏫</span><span>Teachers</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo nav_active('attendance'); ?>">
                        <a href="<?php echo $root; ?>/attendance/index.php" class="nav-link">
                            <span class="nav-icon">📋</span><span>Attendance</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo nav_active('grades'); ?>">
                        <a href="<?php echo $root; ?>/grades/index.php" class="nav-link">
                            <span class="nav-icon">📝</span><span>Grades & GPA</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo nav_active('reports'); ?>">
                        <a href="<?php echo $root; ?>/reports/index.php" class="nav-link">
                            <span class="nav-icon">📈</span><span>Reports</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <a href="<?php echo $root; ?>/logout.php" class="nav-item nav-link" style="color: var(--danger-neon);">
                    <span class="nav-icon">🚪</span><span>Logout</span>
                </a>
            </div>
        </aside>

        <main class="main-content">
            <header class="dashboard-header fade-in-down">
                <div>
                    <h1 class="header-title"><?php echo htmlspecialchars($page_title); ?></h1>
                    <p class="header-subtitle">Welcome, <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?></p>
                </div>
            </header>

            <?php $flash = flash_get(); if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type'] === 'error' ? 'error' : 'success'; ?> glass-card">
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
            <?php endif; ?>

            <div class="admin-page-content">
