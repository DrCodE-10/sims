<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf()) {
    flash_set('error', 'Invalid delete request.');
    redirect('students.php');
}

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    flash_set('error', 'Invalid student.');
    redirect('students.php');
}

$pdo = db();

try {
    $stmt = $pdo->prepare('SELECT profile_image FROM students WHERE id = ?');
    $stmt->execute([$id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($student) {
        delete_profile_image($student['profile_image']);
        $pdo->prepare('DELETE FROM students WHERE id = ?')->execute([$id]);
        flash_set('success', 'Student deleted successfully.');
    } else {
        flash_set('error', 'Student not found.');
    }
} catch (PDOException $e) {
    flash_set('error', 'Could not delete student.');
}

redirect('students.php');
