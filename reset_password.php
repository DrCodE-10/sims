<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Invalid security token.';
    } else {
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            try {
                $pdo = db();
                $hash = hash('sha256', $token);
                $stmt = $pdo->prepare(
                    'SELECT pr.user_id FROM password_resets pr
                     WHERE pr.token_hash = ? AND pr.expires_at > NOW() AND pr.used_at IS NULL LIMIT 1'
                );
                $stmt->execute([$hash]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$row) {
                    $error = 'Invalid or expired reset link.';
                } else {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $pdo->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([$newHash, $row['user_id']]);
                    $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE token_hash = ?')->execute([$hash]);
                    $success = 'Password updated. You can now log in.';
                }
            } catch (PDOException $e) {
                $error = 'Unable to reset password.';
            }
        }
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
    <title>Reset Password - SIMS</title>
    <link rel="stylesheet" href="assets/css/futuristic.css">
</head>
<body>
    <div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;">
        <div class="glass-card" style="max-width:420px;width:100%;padding:40px;">
            <h1 class="neon-text" style="text-align:center;">New Password</h1>
            <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <p style="text-align:center;"><a href="login.php">Go to Login</a></p>
            <?php elseif ($token): ?>
                <form method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <div class="form-group"><label>New Password</label><input type="password" name="password" class="neon-input" required minlength="8"></div>
                    <div class="form-group"><label>Confirm</label><input type="password" name="confirm_password" class="neon-input" required></div>
                    <button type="submit" class="neon-btn" style="width:100%;margin-top:15px;">Update Password</button>
                </form>
            <?php else: ?>
                <p>Invalid reset link.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
