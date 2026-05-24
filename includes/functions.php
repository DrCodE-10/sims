<?php
/**
 * SIMS - Shared helper functions
 */

require_once __DIR__ . '/../config/database.php';

define('SIMS_MAX_UPLOAD_BYTES', 5242880); // 5MB

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $database = new Database();
        $pdo = $database->getConnection();
    }
    return $pdo;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

function verify_csrf(): bool
{
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], (string) $_POST['csrf_token']);
}

function flash_set(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_get(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit();
}

function ensure_upload_dir(string $relativePath): string
{
    $full = dirname(__DIR__) . '/' . trim($relativePath, '/');
    if (!is_dir($full)) {
        mkdir($full, 0755, true);
    }
    return $full . '/';
}

function handle_profile_upload(string $field = 'profile_image'): ?string
{
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed. Please try again.');
    }

    if ($_FILES[$field]['size'] > SIMS_MAX_UPLOAD_BYTES) {
        throw new RuntimeException('File too large. Maximum size is 5MB.');
    }

    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed, true)) {
        throw new RuntimeException('Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.');
    }

    $fileName = bin2hex(random_bytes(16)) . '.' . $ext;
    $dir = ensure_upload_dir('uploads/students');
    $target = $dir . $fileName;

    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $target)) {
        throw new RuntimeException('Failed to save uploaded file.');
    }

    return 'uploads/students/' . $fileName;
}

function delete_profile_image(?string $path): void
{
    if (!$path) {
        return;
    }
    $full = dirname(__DIR__) . '/' . ltrim($path, '/');
    if (is_file($full)) {
        unlink($full);
    }
}

function ensure_default_course(PDO $pdo): int
{
    $stmt = $pdo->query("SELECT id FROM courses WHERE course_code = 'GEN101' LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        return (int) $row['id'];
    }

    $stmt = $pdo->prepare(
        "INSERT INTO courses (course_code, course_name, class_grade, academic_year, semester, status)
         VALUES ('GEN101', 'General Attendance', 'All', '2024-2025', 1, 'active')"
    );
    $stmt->execute();
    return (int) $pdo->lastInsertId();
}

function attendance_rate_overall(PDO $pdo): float
{
    $stmt = $pdo->query(
        "SELECT ROUND(
            (COUNT(CASE WHEN status = 'present' THEN 1 END) / NULLIF(COUNT(*), 0)) * 100, 1
        ) AS rate FROM attendance"
    );
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row && $row['rate'] !== null ? (float) $row['rate'] : 0.0;
}

function student_attendance_rate(PDO $pdo, int $studentId): float
{
    $stmt = $pdo->prepare(
        "SELECT ROUND(
            (COUNT(CASE WHEN status = 'present' THEN 1 END) / NULLIF(COUNT(*), 0)) * 100, 1
        ) AS rate FROM attendance WHERE student_id = ?"
    );
    $stmt->execute([$studentId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row && $row['rate'] !== null ? (float) $row['rate'] : 0.0;
}

function student_average_grade(PDO $pdo, int $studentId): ?float
{
    $stmt = $pdo->prepare('SELECT ROUND(AVG(percentage), 2) FROM grades WHERE student_id = ?');
    $stmt->execute([$studentId]);
    $val = $stmt->fetchColumn();
    return $val !== false && $val !== null ? (float) $val : null;
}

function gpa_letter(float $percentage): string
{
    if ($percentage >= 90) return 'A';
    if ($percentage >= 80) return 'B';
    if ($percentage >= 70) return 'C';
    if ($percentage >= 60) return 'D';
    return 'F';
}

function json_attr($value): string
{
    return htmlspecialchars(json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8');
}
