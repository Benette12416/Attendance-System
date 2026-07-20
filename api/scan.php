<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../includes/config.php';
// Update line 6 to this:
require_once 'C:/xampp/htdocs/attendancesystem/vendor/autoload.php';
header('Content-Type: application/json');

/** @var mysqli $conn */
$data = json_decode(file_get_contents('php://input'), true);
$rfid = $data['rfid'] ?? '';

if (empty($rfid)) {
    echo json_encode(['success' => false, 'message' => 'No RFID UID detected']);
    exit;
}

// 1. Fetch student info
$stmt = $conn->prepare("SELECT id, fullname, email FROM students WHERE rfid_uid = ?");
$stmt->bind_param("s", $rfid);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if ($student) {
    $student_id = $student['id'];
    $date = date('Y-m-d');
    
    // Check for Time In or Time Out
    $checkAttendance = $conn->prepare("SELECT id FROM attendance WHERE student_id = ? AND date = ?");
    $checkAttendance->bind_param("is", $student_id, $date);
    $checkAttendance->execute();
    $isTimeOut = $checkAttendance->get_result()->num_rows > 0;

    if (!$isTimeOut) {
        $stmt_ins = $conn->prepare("INSERT INTO attendance (student_id, date, time_in, status) VALUES (?, ?, NOW(), 'Present')");
        $stmt_ins->bind_param("is", $student_id, $date);
        $stmt_ins->execute();
        $action = "Time In";
    } else {
        $stmt_upd = $conn->prepare("UPDATE attendance SET time_out = NOW() WHERE student_id = ? AND date = ?");
        $stmt_upd->bind_param("is", $student_id, $date);
        $stmt_upd->execute();
        $action = "Time Out";
    }

    // 2. Trigger Formal Email Notification & Log to Database
    if (!empty($student['email'])) {
        $mailStatus = 'Success';
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'attendancerfid2026@gmail.com';
            $mail->Password   = 'xxekngdbtruexvkz';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('attendancerfid2026@gmail.com', 'Attendance System');
            $mail->addAddress($student['email']);
            $mail->isHTML(true);
            
            $mail->Subject = 'Official Attendance Notification: ' . $action;
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; border: 1px solid #e2e8f0; padding: 20px; border-radius: 8px;'>
                    <h2 style='color: #2563eb;'>Attendance System Notification</h2>
                    <p>Dear <strong>" . htmlspecialchars($student['fullname']) . "</strong>,</p>
                    <p>This is a formal notification that your <strong>" . $action . "</strong> has been successfully recorded in our system.</p>
                    <div style='background: #f8fafc; padding: 15px; border-radius: 6px; border-left: 4px solid #2563eb;'>
                        <p style='margin: 0;'><strong>Date:</strong> " . date('F j, Y') . "</p>
                        <p style='margin: 5px 0 0 0;'><strong>Action Time:</strong> " . date('h:i:s A') . "</p>
                    </div>
                    <p style='margin-top: 20px;'>Thank you.</p>
                </div>";
            
            $mail->send();
        } catch (Exception $e) {
            $mailStatus = 'Failed: ' . $mail->ErrorInfo;
        }

        // Record the log to email_logs table
        $log_stmt = $conn->prepare("INSERT INTO email_logs (student_id, subject, status, created_at) VALUES (?, ?, ?, NOW())");
        $log_stmt->bind_param("iss", $student_id, $mail->Subject, $mailStatus);
        $log_stmt->execute();
    }
    
    echo json_encode(['success' => true, 'message' => "Attendance ($action) recorded successfully"]);
} else {
    echo json_encode(['success' => false, 'message' => 'RFID card not registered']);
}
?>