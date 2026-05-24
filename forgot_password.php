<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/mailer.php';

if (isset($_SESSION['user_id'])) {
    redirect('index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Invalid security token.';
    } else {
        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            try {
                $pdo = db();
                $stmt = $pdo->prepare('SELECT id, username, full_name FROM users WHERE email = ? AND is_active = 1 LIMIT 1');
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    $token = bin2hex(random_bytes(32));
                    $hash = hash('sha256', $token);
                    $expires = date('Y-m-d H:i:s', time() + 3600);
                    $pdo->prepare('DELETE FROM password_resets WHERE user_id = ?')->execute([$user['id']]);
                    $pdo->prepare('INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, ?)')
                        ->execute([$user['id'], $hash, $expires]);

                    $link = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']
                        . dirname($_SERVER['PHP_SELF']) . '/reset_password.php?token=' . urlencode($token);
                    sims_mail($email, 'SIMS - Password Reset', "Hello {$user['full_name']},\n\nReset your password:\n{$link}\n\nLink expires in 1 hour.");
                }
                $success = 'If that email is registered, a reset link has been sent.';
            } catch (PDOException $e) {
                $error = 'Unable to process request. Ensure database patches are applied.';
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
    <title>Forgot Password - SIMS</title>
    <link rel="stylesheet" href="assets/css/futuristic.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
    <div class="login-container" style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;">
        <div class="glass-card" style="max-width:420px;width:100%;padding:40px;">
            <h1 class="neon-text" style="text-align:center;margin-bottom:20px;">Reset Password</h1>
            <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
            <form method="POST">
                <?php echo csrf_field(); ?>
                <div class="form-group">
                    <label>Email address</label>
                    <input type="email" name="email" class="neon-input" required>
                </div>
                <button type="submit" class="neon-btn" style="width:100%;margin-top:15px;">Send Reset Link</button>
            </form>
            <p style="text-align:center;margin-top:20px;"><a href="login.php" style="color:var(--primary-neon);">Back to Login</a></p>
        </div>
    </div>
</body>
</html>
