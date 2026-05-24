<?php
/**
 * SIMS - Authentication helpers
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/functions.php';

function login_path(): string
{
    $script = $_SERVER['PHP_SELF'] ?? '';
    if (preg_match('#/(admin|attendance|reports|grades|teacher|student)/#', $script)) {
        return '../login.php';
    }
    return 'login.php';
}

function require_login(): void
{
    if (!isset($_SESSION['user_id'])) {
        redirect(login_path());
    }
}

function populate_session_profile(PDO $pdo): void
{
    $_SESSION['teacher_record_id'] = null;
    $_SESSION['student_record_id'] = null;

    if (($_SESSION['role'] ?? '') === 'teacher') {
        $stmt = $pdo->prepare('SELECT id, teacher_id, first_name, last_name FROM teachers WHERE user_id = ? AND status = ? LIMIT 1');
        $stmt->execute([$_SESSION['user_id'], 'active']);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $_SESSION['teacher_record_id'] = (int) $row['id'];
            $_SESSION['teacher_code'] = $row['teacher_id'];
        }
    }

    if (($_SESSION['role'] ?? '') === 'student') {
        $stmt = $pdo->prepare('SELECT id, student_id, first_name, last_name, class_grade, section FROM students WHERE user_id = ? LIMIT 1');
        $stmt->execute([$_SESSION['user_id']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $_SESSION['student_record_id'] = (int) $row['id'];
            $_SESSION['student_code'] = $row['student_id'];
            $_SESSION['student_class'] = $row['class_grade'];
        }
    }
}

function redirect_after_login(): void
{
    switch ($_SESSION['role'] ?? '') {
        case 'admin':
            redirect('index.php');
        case 'teacher':
            redirect('teacher/index.php');
        case 'student':
            redirect('student/index.php');
        default:
            redirect('login.php');
    }
}

function portal_prefix(): string
{
    $script = $_SERVER['PHP_SELF'] ?? '';
    if (preg_match('#/(admin|attendance|reports|grades|teacher|student)/#', $script)) {
        return '../';
    }
    return '';
}

function home_url_for_role(?string $role = null): string
{
    $role = $role ?? ($_SESSION['role'] ?? '');
    $p = portal_prefix();
    switch ($role) {
        case 'admin':
            return $p . 'index.php';
        case 'teacher':
            return $p . 'teacher/index.php';
        case 'student':
            return $p . 'student/index.php';
        default:
            return $p . 'login.php';
    }
}

function require_admin(): void
{
    require_login();
    if (($_SESSION['role'] ?? '') !== 'admin') {
        flash_set('error', 'Admin access only.');
        redirect(home_url_for_role());
    }
}

function require_teacher(): void
{
    require_login();
    if (($_SESSION['role'] ?? '') !== 'teacher') {
        flash_set('error', 'Teacher access only.');
        redirect(home_url_for_role());
    }
    if (empty($_SESSION['teacher_record_id'])) {
        flash_set('error', 'Your account is not linked to a teacher profile. Contact the administrator.');
        redirect('../login.php');
    }
}

function require_student(): void
{
    require_login();
    if (($_SESSION['role'] ?? '') !== 'student') {
        flash_set('error', 'Student access only.');
        redirect(home_url_for_role());
    }
    if (empty($_SESSION['student_record_id'])) {
        flash_set('error', 'Your account is not linked to a student profile. Contact the administrator.');
        redirect('../login.php');
    }
}

function get_teacher_id(): int
{
    return (int) ($_SESSION['teacher_record_id'] ?? 0);
}

function get_student_id(): int
{
    return (int) ($_SESSION['student_record_id'] ?? 0);
}

function establish_user_session(PDO $pdo, array $user, bool $remember = false): void
{
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['full_name'] = $user['full_name'];

    $pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = ?')->execute([$user['id']]);
    populate_session_profile($pdo);

    if ($remember) {
        set_remember_me($pdo, (int) $user['id']);
    }
}

function check_remember_me(): void
{
    if (isset($_SESSION['user_id']) || !isset($_COOKIE['remember_token'])) {
        return;
    }

    try {
        $pdo = db();
        $tokenHash = hash('sha256', $_COOKIE['remember_token']);
        $stmt = $pdo->prepare(
            "SELECT u.id, u.username, u.role, u.full_name
             FROM remember_tokens rt
             JOIN users u ON u.id = rt.user_id
             WHERE rt.token_hash = ? AND rt.expires_at > NOW() AND u.is_active = 1
             LIMIT 1"
        );
        $stmt->execute([$tokenHash]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            establish_user_session($pdo, $user, false);
        } else {
            setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        }
    } catch (PDOException $e) {
        error_log('Remember me error: ' . $e->getMessage());
    }
}

function set_remember_me(PDO $pdo, int $userId): void
{
    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);
    $expires = date('Y-m-d H:i:s', time() + (86400 * 30));

    $pdo->prepare('DELETE FROM remember_tokens WHERE user_id = ?')->execute([$userId]);
    $pdo->prepare(
        'INSERT INTO remember_tokens (user_id, token_hash, expires_at) VALUES (?, ?, ?)'
    )->execute([$userId, $tokenHash, $expires]);

    setcookie('remember_token', $token, time() + (86400 * 30), '/', '', false, true);
}

function clear_remember_me(PDO $pdo, int $userId): void
{
    $pdo->prepare('DELETE FROM remember_tokens WHERE user_id = ?')->execute([$userId]);
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    }
}

function create_user_account(PDO $pdo, string $username, string $password, string $email, string $role, string $fullName): int
{
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare(
        'INSERT INTO users (username, password, email, role, full_name, is_active) VALUES (?, ?, ?, ?, ?, 1)'
    );
    $stmt->execute([$username, $hash, $email, $role, $fullName]);
    return (int) $pdo->lastInsertId();
}
