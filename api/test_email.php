<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../includes/config.php';
require_once 'C:/xampp/htdocs/attendancesystem/vendor/autoload.php';
/** @var mysqli $conn */
header('Content-Type: application/json');

// 1. Fetch the latest record to get the student's name and latest time
$query = "SELECT s.fullname, s.email, a.time_in, a.time_out, a.date 
          FROM attendance a 
          JOIN students s ON a.student_id = s.id 
          ORDER BY a.time_in DESC LIMIT 1";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

if ($data) {
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
        $mail->addAddress($data['email']);
        
        $studentName = $data['fullname'];
        $date = date('M j, Y', strtotime($data['date']));
        $timeIn = $data['time_in'] ? date('h:i:s A', strtotime($data['time_in'])) : 'N/A';
        $timeOut = $data['time_out'] ? date('h:i:s A', strtotime($data['time_out'])) : '--';

        $mail->isHTML(true);
        $mail->Subject = 'Attendance System: Test Notification';
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: auto; border: 1px solid #e2e8f0; padding: 20px; border-radius: 8px;'>
                <h2 style='color: #2563eb; margin-top: 0;'>System Notification</h2>
                <p>Hi,</p>
                
                <p>This is a formal test attendance notification from the RFID Attendance System to verify that your account email notifications are configured correctly.</p>
                
                <div style='background: #f8fafc; padding: 15px; border-radius: 6px; border-left: 4px solid #2563eb;'>
                    <p style='margin: 0;'><strong>Date:</strong> " . htmlspecialchars($date) . "</p>
                    <p style='margin: 5px 0 0 0;'><strong>Time In:</strong> " . htmlspecialchars($timeIn) . "</p>
                    <p style='margin: 5px 0 0 0;'><strong>Time Out:</strong> " . htmlspecialchars($timeOut) . "</p>
                </div>
                
                <p style='margin-top: 20px;'>If you have received this message, the email integration is fully operational.</p>
                
                <hr style='border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;'>
                <p style='font-size: 0.85rem; color: #64748b;'>
                    RFID Attendance System Support
                </p>
            </div>";
        
        $mail->send();
        echo json_encode(['success' => true, 'message' => 'Test email sent successfully.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Email failed: ' . $mail->ErrorInfo]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No attendance records found to test.']);
}
?>