<?php
/**
 * SIMS - Main Dashboard
 * Student Information Management System
 * 
 * Smart dashboard with real-time analytics
 */

session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';


check_remember_me();
require_login();

if (($_SESSION['role'] ?? '') !== 'admin') {
    redirect(home_url_for_role());
}

// Get user data
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];
$full_name = $_SESSION['full_name'];

$db = db();

// Get dashboard statistics
$stats = [
    'total_students' => 0,
    'total_teachers' => 0,
    'total_courses' => 0,
    'attendance_rate' => 0
];

try {
    // Get total students
    $stmt = $db->query("SELECT COUNT(*) as count FROM students");
    $stats['total_students'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get total teachers
    $stmt = $db->query("SELECT COUNT(*) as count FROM teachers");
    $stats['total_teachers'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get total courses
    $stmt = $db->query("SELECT COUNT(*) as count FROM courses");
    $stats['total_courses'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    $stats['attendance_rate'] = attendance_rate_overall($db);
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#000000">
    <title>SIMS  - Dashboard</title>
    <link rel="stylesheet" href="assets/css/futuristic.css">
    <link rel="stylesheet" href="assets/css/animations.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="matrix-bg" aria-hidden="true"></div>

    <!-- Dashboard Container -->
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar fade-in-left">
            <div class="sidebar-header">
                <h1 class="sidebar-logo">SIMS</h1>
                <p class="sidebar-subtitle">Student Information</p>
            </div>
            
            <nav>
                <ul class="nav-menu">
                    <li class="nav-item active">
                        <a href="index.php" class="nav-link" style="text-decoration:none;color:inherit;display:flex;align-items:center;gap:12px;">
                            <span class="nav-icon">📊</span><span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="admin/students.php" class="nav-link" style="text-decoration:none;color:inherit;display:flex;align-items:center;gap:12px;">
                            <span class="nav-icon">👥</span><span>Students</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="attendance/index.php" class="nav-link" style="text-decoration:none;color:inherit;display:flex;align-items:center;gap:12px;">
                            <span class="nav-icon">📋</span><span>Attendance</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="grades/index.php" class="nav-link" style="text-decoration:none;color:inherit;display:flex;align-items:center;gap:12px;">
                            <span class="nav-icon">📝</span><span>Grades & GPA</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="reports/index.php" class="nav-link" style="text-decoration:none;color:inherit;display:flex;align-items:center;gap:12px;">
                            <span class="nav-icon">📈</span><span>Reports</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <a href="logout.php" class="nav-item nav-link" style="color: var(--danger-neon); text-decoration:none; display:flex; align-items:center; gap:12px;">
                    <span class="nav-icon">🚪</span><span>Logout</span>
                </a>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="dashboard-header fade-in-down">
                <div>
                    <h1 class="header-title">Welcome back, <?php echo htmlspecialchars($full_name); ?></h1>
                    <p class="header-subtitle">Academic Management System</p>
                </div>
                
                <div class="header-actions">
                    <!-- Search Bar -->
                    <div class="search-bar">
                        <input type="text" placeholder="Search students, courses...">
                        <span class="search-icon">🔍</span>
                    </div>
                    
                    <!-- Notification Bell -->
                    <div class="notification-bell">
                        <span>🔔</span>
                        <span class="notification-badge">3</span>
                    </div>
                    
                    <!-- User Profile -->
                    <div class="user-profile">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($full_name, 0, 1)); ?>
                        </div>
                        <span class="user-name"><?php echo htmlspecialchars($full_name); ?></span>
                    </div>
                </div>
            </header>
            
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card glass-card fade-in-up stagger-1">
                    <div class="stat-icon">👥</div>
                    <div class="stat-value"><?php echo $stats['total_students']; ?></div>
                    <div class="stat-label">Total Students</div>
                    <div class="stat-change positive">+12% this month</div>
                </div>
                
                <div class="stat-card glass-card fade-in-up stagger-2">
                    <div class="stat-icon">👨‍🏫</div>
                    <div class="stat-value"><?php echo $stats['total_teachers']; ?></div>
                    <div class="stat-label">Total Teachers</div>
                    <div class="stat-change positive">+5% this month</div>
                </div>
                
                <div class="stat-card glass-card fade-in-up stagger-3">
                    <div class="stat-icon">📚</div>
                    <div class="stat-value"><?php echo $stats['total_courses']; ?></div>
                    <div class="stat-label">Active Courses</div>
                    <div class="stat-change positive">+8% this month</div>
                </div>
                
                <div class="stat-card glass-card fade-in-up stagger-4">
                    <div class="stat-icon">📊</div>
                    <div class="stat-value"><?php echo $stats['attendance_rate']; ?>%</div>
                    <div class="stat-label">Attendance Rate</div>
                    <div class="stat-change negative">-2% this month</div>
                </div>
            </div>
            
            <!-- Charts Section -->
            <div class="charts-section">
                <div class="chart-card glass-card fade-in-up stagger-5">
                    <div class="chart-header">
                        <h3 class="chart-title">Student Performance Trends</h3>
                        <select class="neon-input" style="width: auto; padding: 8px 15px;">
                            <option>This Semester</option>
                            <option>Last Semester</option>
                            <option>This Year</option>
                        </select>
                    </div>
                    <div class="chart-container">
                        <canvas id="studentPerformanceChart"></canvas>
                    </div>
                </div>
                
                <div class="chart-card glass-card fade-in-up stagger-6">
                    <div class="chart-header">
                        <h3 class="chart-title">Attendance Distribution</h3>
                    </div>
                    <div class="chart-container">
                        <canvas id="attendanceChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Activity Feed & Quick Actions -->
            <div class="charts-section">
                <div class="activity-feed glass-card fade-in-up stagger-7">
                    <div class="chart-header">
                        <h3 class="chart-title">Recent Activity</h3>
                    </div>
                    
                    <div class="activity-item">
                        <div class="activity-icon">👥</div>
                        <div class="activity-content">
                            <div class="activity-title">New student registered</div>
                            <div class="activity-time">5 minutes ago</div>
                        </div>
                    </div>
                    
                    <div class="activity-item">
                        <div class="activity-icon">📋</div>
                        <div class="activity-content">
                            <div class="activity-title">Attendance marked for Class 10A</div>
                            <div class="activity-time">15 minutes ago</div>
                        </div>
                    </div>
                    
                    <div class="activity-item">
                        <div class="activity-icon">📝</div>
                        <div class="activity-content">
                            <div class="activity-title">Grades updated for Mathematics</div>
                            <div class="activity-time">1 hour ago</div>
                        </div>
                    </div>
                    
                    <div class="activity-item">
                        <div class="activity-icon">📚</div>
                        <div class="activity-content">
                            <div class="activity-title">New course added: Advanced Physics</div>
                            <div class="activity-time">2 hours ago</div>
                        </div>
                    </div>
                </div>
                
                <div class="glass-card fade-in-up stagger-8">
                    <div class="chart-header">
                        <h3 class="chart-title">Quick Actions</h3>
                    </div>
                    
                    <div class="quick-actions">
                        <a href="admin/add_student.php" class="action-card" style="text-decoration:none;color:inherit;">
                            <div class="action-icon">➕</div>
                            <div class="action-label">Add Student</div>
                        </a>
                        <a href="attendance/index.php" class="action-card" style="text-decoration:none;color:inherit;">
                            <div class="action-icon">📋</div>
                            <div class="action-label">Mark Attendance</div>
                        </a>
                        <a href="grades/enter.php" class="action-card" style="text-decoration:none;color:inherit;">
                            <div class="action-icon">📝</div>
                            <div class="action-label">Enter Grades</div>
                        </a>
                        <a href="reports/index.php" class="action-card" style="text-decoration:none;color:inherit;">
                            <div class="action-icon">📊</div>
                            <div class="action-label">Generate Report</div>
                        </a>
                    </div>
                    
                    <!-- Insights -->
                    <div class="insights" style="margin-top: 20px;">
                        <div class="chart-header">
                            <h3 class="chart-title">Insights</h3>
                        </div>
                        <div id="insights">
                            <div class="insight-card glass-card" style="padding: 15px; margin-bottom: 10px;">
                                <div class="insight-icon">📈</div>
                                <div class="insight-content">
                                    <h4>Performance Trend</h4>
                                    <p style="font-size: 12px; color: var(--text-secondary);">Student GPA is trending upward with 87% confidence.</p>
                                </div>
                            </div>
                            <div class="insight-card glass-card" style="padding: 15px;">
                                <div class="insight-icon">⚠️</div>
                                <div class="insight-content">
                                    <h4>Attendance Alert</h4>
                                    <p style="font-size: 12px; color: var(--text-secondary);">3 students at risk of falling below 80% attendance.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Scripts -->
    <script src="assets/js/app.js"></script>
    <script src="assets/js/charts.js"></script>
    <script src="assets/js/ai.js"></script>
    <script src="assets/js/animations.js"></script>
    
    <script>
        window.SIMS.AppState.currentUser = <?php echo json_encode([
            'id' => (int) $user_id,
            'username' => $username,
            'role' => $role,
            'name' => $full_name,
        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    </script>
</body>
</html>
