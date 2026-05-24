<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

check_remember_me();

if (isset($_SESSION['user_id'])) {
    redirect_after_login();
}

$error = '';
$selected_role = $_GET['role'] ?? $_POST['login_role'] ?? 'admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Invalid security token. Please refresh and try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        $login_role = $_POST['login_role'] ?? 'admin';

        if ($username === '' || $password === '') {
            $error = 'Please enter both username and password.';
        } else {
            try {
                $pdo = db();
                $stmt = $pdo->prepare(
                    'SELECT id, username, password, role, full_name FROM users WHERE username = ? AND is_active = 1 LIMIT 1'
                );
                $stmt->execute([$username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['password'])) {
                    if ($user['role'] !== $login_role) {
                        $error = 'This account is not a ' . htmlspecialchars($login_role) . ' account. Select the correct role tab.';
                    } else {
                        establish_user_session($pdo, $user, $remember);

                        if ($user['role'] === 'teacher' && empty($_SESSION['teacher_record_id'])) {
                            $error = 'Teacher account is not linked to a teacher profile. Contact admin.';
                            session_destroy();
                            session_start();
                        } elseif ($user['role'] === 'student' && empty($_SESSION['student_record_id'])) {
                            $error = 'Student account is not linked to a student record. Contact admin.';
                            session_destroy();
                            session_start();
                        } else {
                            redirect_after_login();
                        }
                    }
                } else {
                    $error = 'Invalid username or password.';
                }
            } catch (PDOException $e) {
                $error = 'Login failed. Please ensure the database is configured.';
            }
        }
        $selected_role = $login_role;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#000000">
    <title>SIMS - Login</title>
    <link rel="stylesheet" href="assets/css/futuristic.css">
    <link rel="stylesheet" href="assets/css/animations.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
        .role-tabs { display:flex; gap:8px; margin-bottom:25px; }
        .role-tab {
            flex:1; padding:12px 8px; text-align:center; border-radius:10px;
            border:1px solid var(--glass-border); background:rgba(15,15,25,0.5);
            color:var(--text-secondary); cursor:pointer; text-decoration:none; font-size:13px;
        }
        .role-tab.active {
            border-color:var(--primary-neon);
            background:linear-gradient(135deg,rgba(0,240,255,0.2),rgba(0,240,255,0.05));
            color:var(--primary-neon);
        }
        .demo-hint { font-size:11px; color:var(--text-muted); margin-top:20px; line-height:1.6; }
    </style>
</head>
<body>
    <div class="matrix-bg" aria-hidden="true"></div>

    <div class="login-container" style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;position:relative;z-index:1;">
        <div class="login-card glass-card fade-in-up" style="width:100%;max-width:480px;padding:40px;">
            <div style="text-align:center;margin-bottom:25px;">
                <h1 class="neon-text">SIMS</h1>
                <p style="color:var(--text-secondary);font-size:14px;">Student Information Management System</p>
            </div>

            <div class="role-tabs">
                <a href="?role=admin" class="role-tab <?php echo $selected_role === 'admin' ? 'active' : ''; ?>">🎓 Admin</a>
                <!--<a href="?role=teacher" class="role-tab <?php //echo $selected_role === 'teacher' ? 'active' : ''; ?>">👨‍🏫 Teacher</a> -->
                <a href="?role=student" class="role-tab <?php echo $selected_role === 'student' ? 'active' : ''; ?>">👨‍🎓 Student</a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error" style="margin-bottom:20px;"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="login_role" value="<?php echo htmlspecialchars($selected_role); ?>">

                <div class="form-group" style="margin-bottom:20px;">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="neon-input" required autocomplete="username"
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>
                <div class="form-group" style="margin-bottom:20px;">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="neon-input" required autocomplete="current-password">
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:25px;">
                    <label><input type="checkbox" name="remember"> Remember me</label>
                    <a href="forgot_password.php" style="color:var(--primary-neon);font-size:14px;">Forgot password?</a>
                </div>
                <button type="submit" class="neon-btn" style="width:100%;">
                    Login as <?php echo ucfirst(htmlspecialchars($selected_role)); ?>
                </button>
            </form>

            <!--<div class="demo-hint" style="text-align:center;">
                <strong>Demo accounts</strong><br>
                Admin: <code>admin</code> / <code>Mwita@0104</code><br>
                Teacher: <code>teacher1</code> / <code>Teacher@123</code><br>
                Student: <code>STU001</code> / <code>Doe</code> (ID + last name)
            </div>-->
        </div>
    </div>

    <script src="assets/js/app.js"></script>
    <script src="assets/js/animations.js"></script>
</body>
</html>
