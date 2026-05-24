<?php
/**
 * One-time migration: create portal users for students missing user_id
 * Username = student_id | Password = last_name (hashed)
 *
 * Run from CLI: php database/migrate_student_portal_accounts.php
 * Or browser (admin only): http://localhost/SIMS/database/migrate_student_portal_accounts.php
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

$isCli = PHP_SAPI === 'cli';

if (!$isCli) {
    session_start();
    if (($_SESSION['role'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('Admin login required.');
    }
    header('Content-Type: text/plain; charset=utf-8');
}

$pdo = db();
$created = 0;
$skipped = 0;
$errors = [];

$stmt = $pdo->query(
    "SELECT s.id, s.student_id, s.first_name, s.last_name, s.email
     FROM students s
     WHERE s.user_id IS NULL
     ORDER BY s.id"
);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $studentId = trim($row['student_id']);
    $lastName = trim($row['last_name']);

    if ($studentId === '' || $lastName === '') {
        $errors[] = "Skip internal id {$row['id']}: missing student_id or last_name";
        $skipped++;
        continue;
    }

    $check = $pdo->prepare('SELECT id FROM users WHERE username = ?');
    $check->execute([$studentId]);
    if ($check->fetch()) {
        $errors[] = "Skip {$studentId}: username already exists in users";
        $skipped++;
        continue;
    }

    try {
        $pdo->beginTransaction();
        $email = trim($row['email'] ?? '') !== '' ? trim($row['email']) : ($studentId . '@sims.local');
        $uid = create_user_account(
            $pdo,
            $studentId,
            $lastName,
            $email,
            'student',
            trim($row['first_name'] . ' ' . $row['last_name'])
        );
        $pdo->prepare('UPDATE students SET user_id = ? WHERE id = ?')->execute([$uid, $row['id']]);
        $pdo->commit();
        $created++;
    } catch (Throwable $e) {
        $pdo->rollBack();
        $errors[] = "Failed {$studentId}: " . $e->getMessage();
    }
}

echo "Student portal migration complete.\n";
echo "Created: {$created}\n";
echo "Skipped: {$skipped}\n";
if ($errors) {
    echo "\nDetails:\n" . implode("\n", $errors) . "\n";
}
