<?php
/**
 * SIMS - Email notifications (PHP mail; configure SMTP in production)
 */

function sims_mail(string $to, string $subject, string $body): bool
{
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $from = 'noreply@sims.local';
    try {
        $pdo = db();
        $stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'system_email' LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && filter_var($row['setting_value'], FILTER_VALIDATE_EMAIL)) {
            $from = $row['setting_value'];
        }
    } catch (PDOException $e) {
        // use default
    }

    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: SIMS <' . $from . '>',
    ];

    $html = '<html><body style="font-family:sans-serif;">' . nl2br(htmlspecialchars($body)) . '</body></html>';
    return @mail($to, $subject, $html, implode("\r\n", $headers));
}

function notify_student_registered(string $email, string $studentName, string $studentId): void
{
    if (!$email) {
        return;
    }
    $subject = 'SIMS - Student Registration Confirmation';
    $body = "Hello,\n\nStudent {$studentName} (ID: {$studentId}) has been registered in SIMS.\n\n— SIMS Administration";
    sims_mail($email, $subject, $body);
}

function notify_attendance_alert(string $email, string $studentName, string $status, string $date): void
{
    if (!$email) {
        return;
    }
    $subject = 'SIMS - Attendance Alert';
    $body = "Attendance for {$studentName} on {$date} was marked as: {$status}.";
    sims_mail($email, $subject, $body);
}
