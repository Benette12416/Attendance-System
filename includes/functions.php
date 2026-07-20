<?php
/**
 * Shared helper functions used across the system.
 * Requires $conn from includes/config.php
 */

// ---------------------------------------------------------
// Creates a default admin (admin / admin123) the very first
// time the app is used, so login.php always works out of the box.
// ---------------------------------------------------------
function ensureDefaultAdmin(mysqli $conn): void {
    $result = mysqli_query($conn, "SELECT id FROM admins LIMIT 1");
    if ($result && mysqli_num_rows($result) === 0) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "INSERT INTO admins (username, password, fullname) VALUES ('admin', ?, 'Administrator')");
        mysqli_stmt_bind_param($stmt, 's', $hash);
        mysqli_stmt_execute($stmt);
    }
}

// ---------------------------------------------------------
// Settings (single row table)
// ---------------------------------------------------------
function getSettings(mysqli $conn): array {
    $result = mysqli_query($conn, "SELECT * FROM settings LIMIT 1");
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    // fallback defaults if settings row somehow missing
    return [
        'school_name' => 'My School',
        'late_time' => '08:00:00',
        'email_enabled' => 0,
        'email_from_name' => ''
    ];
}

// ---------------------------------------------------------
// Send an email notification (tap-in / tap-out) via Gmail SMTP
// (using PHPMailer), and log it.
//
// Works for any student as long as email is set and Email
// Sending is enabled in Settings, and GMAIL_ADDRESS /
// GMAIL_APP_PASSWORD are defined in config.php. If email is
// disabled, has no address, the address is invalid, or Gmail
// isn't configured, the message is still logged with status
// "Skipped" so you can see what WOULD have been sent.
//
// Settings used (from `settings` table):
//   email_enabled   -> 1 to actually send, 0 to just log
//   email_from_name -> the "From" display name shown to recipients (optional)
//
// Config constants used (from includes/config.php):
//   GMAIL_ADDRESS       -> the Gmail account used to send
//   GMAIL_APP_PASSWORD  -> the 16-character Gmail App Password
// ---------------------------------------------------------
function sendEmail(mysqli $conn, int $studentId, ?string $email, string $subject, string $message) {
    $settings = getSettings($conn);
    $status = 'Sent';

    $emailEnabled    = (int)($settings['email_enabled'] ?? 0) === 1;
    $hasEmail        = !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL);
    $gmailAddress    = defined('GMAIL_ADDRESS') ? GMAIL_ADDRESS : '';
    $gmailPassword   = defined('GMAIL_APP_PASSWORD') ? GMAIL_APP_PASSWORD : '';
    $gmailConfigured = $gmailAddress !== '' && $gmailPassword !== '';

    if ($emailEnabled && $hasEmail && $gmailConfigured) {
        $fromName = !empty($settings['email_from_name']) ? $settings['email_from_name'] : 'School Attendance';

        $result = sendEmailViaGmail($fromName, $email, $subject, $message);

        if (!$result['success']) {
            // --- TEMPORARY DEBUG LOGGING ---
            // Writes to PHP's error log so you can see why the send failed.
            // Remove once your Gmail setup is confirmed working.
            error_log('[Email Debug] Gmail send failed to: ' . $email . ' | Subject: ' . $subject . ' | Error: ' . $result['error']);
            // --- END DEBUG LOGGING ---
            $status = 'Failed';
        }
    } else {
        // Not configured -> just record it, don't attempt to send
        $status = 'Skipped';
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO email_logs (student_id, subject, message, status) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'isss', $studentId, $subject, $message, $status);
    mysqli_stmt_execute($stmt);

    return $status;
}

// ---------------------------------------------------------
// Low-level Gmail SMTP send via PHPMailer.
// Requires: composer require phpmailer/phpmailer  (run from project root)
// Returns ['success' => bool, 'error' => string|null]
// ---------------------------------------------------------
function sendEmailViaGmail(string $fromName, string $toEmail, string $subject, string $message): array {
    require_once __DIR__ . '/../vendor/autoload.php';

    $gmailAddress  = defined('GMAIL_ADDRESS') ? GMAIL_ADDRESS : '';
    $gmailPassword = defined('GMAIL_APP_PASSWORD') ? GMAIL_APP_PASSWORD : '';

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $gmailAddress;
        $mail->Password   = $gmailPassword;
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom($gmailAddress, $fromName);
        $mail->addAddress($toEmail);

        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        $mail->send();
        return ['success' => true, 'error' => null];
    } catch (\PHPMailer\PHPMailer\Exception $e) {
        return ['success' => false, 'error' => $mail->ErrorInfo];
    }
}

// ---------------------------------------------------------
// Small utility formatters
// ---------------------------------------------------------
function h(mixed $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function formatTime(?string $time): string {
    if (empty($time)) return '--';
    return date('h:i A', strtotime($time));
}

function formatDate(?string $date): string {
    if (empty($date)) return '--';
    return date('M d, Y', strtotime($date));
}

function statusBadgeClass(string $status): string {
    switch ($status) {
        case 'Present': return 'badge-success';
        case 'Late': return 'badge-warning';
        default: return 'badge-secondary';
    }
}