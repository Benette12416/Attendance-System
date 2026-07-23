<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../includes/config.php';
require_once '../vendor/autoload.php';

header('Content-Type: application/json');
/** @var mysqli $conn */

/*
|--------------------------------------------------------------------------
| ESP32 API KEY
|--------------------------------------------------------------------------
*/

$API_KEY = "ESP32RFID2026";

/*
|--------------------------------------------------------------------------
| Check API Key
|--------------------------------------------------------------------------
*/

$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';

if ($apiKey !== $API_KEY) {
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized API Key"
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Receive ESP32 Data
|--------------------------------------------------------------------------
*/

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode([
        "status" => "error",
        "message" => "No data received"
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Get RFID UID & Capture Registration Payload
|--------------------------------------------------------------------------
*/

$uid = $data['uid'] ?? '';
$uid = trim($uid);

// Capture the student_id if it was sent from the register_rfid.php frontend
$register_student_id = $data['student_id'] ?? null; 

if(empty($uid)){
    echo json_encode([
        "success" => false, 
        "message" => "RFID UID missing"
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| REGISTRATION LOGIC INTERCEPT
|--------------------------------------------------------------------------
*/

// If a student_id is present, this is a registration request, NOT attendance.
if (!empty($register_student_id)) {
    
    // Check if this card is already registered to someone else
    $check_card = $conn->prepare("SELECT id FROM students WHERE rfid_uid = ? LIMIT 1");
    $check_card->bind_param("s", $uid);
    $check_card->execute();
    if ($check_card->get_result()->num_rows > 0) {
        echo json_encode([
            "success" => false,
            "message" => "This RFID card is already registered to another student."
        ]);
        exit;
    }

    // Update the student's record with the new RFID UID
    $update_stmt = $conn->prepare("UPDATE students SET rfid_uid = ? WHERE id = ?");
    if (!$update_stmt) {
        echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
        exit;
    }
    
    $update_stmt->bind_param("si", $uid, $register_student_id);
    
    if ($update_stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Card successfully registered!"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Failed to update database."
        ]);
    }
    
    exit; // Stop execution here so it DOES NOT run the attendance logic below
}

/*
|--------------------------------------------------------------------------
| Check RFID UID Exists (Attendance Logic)
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        id,
        student_id,
        fullname,
        email
    FROM students
    WHERE rfid_uid = ?
    LIMIT 1
");

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("s", $uid);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    echo json_encode([
        "status" => "error",
        "message" => "RFID card not registered",
        "uid" => $uid
    ]);
    exit;
}

$student = $result->fetch_assoc();
$student_db_id = $student['id'];
$student_id = $student['student_id'];
$name = $student['fullname'];
$email = $student['email'];

/*
|--------------------------------------------------------------------------
| Check Today's Attendance
|--------------------------------------------------------------------------
*/

$date_today = date("Y-m-d");

$check = $conn->prepare("
    SELECT id, time_in, time_out
    FROM attendance
    WHERE student_id = ?
    AND date = ?
    LIMIT 1
");

if (!$check) {
    die("Prepare failed: " . $conn->error);
}

$check->bind_param(
    "is",
    $student_db_id,
    $date_today
);

$check->execute();
$attendance_result = $check->get_result();

/*
|--------------------------------------------------------------------------
| Determine Attendance Action
|--------------------------------------------------------------------------
*/

$attendance_id = null;
$action = "";
$current_time = date("H:i:s");

if($attendance_result->num_rows == 0){
    /*
    First scan = TIME IN
    */
    $action = "TIME IN";
    
    // 1. Fetch the Late Cutoff Time from Settings
    $settings_query = $conn->query("SELECT late_time FROM settings LIMIT 1");
    $settings_row = $settings_query->fetch_assoc();
    $late_time_setting = $settings_row['late_time'] ?? '08:00:00';
    
    // 2. Compare the current time to the late cutoff time
    $current_timestamp = strtotime($current_time);
    $late_timestamp = strtotime($late_time_setting);
    
    $status = ($current_timestamp > $late_timestamp) ? 'Late' : 'Present';

    // 3. Insert into database using the dynamic $status
    $insert = $conn->prepare("
        INSERT INTO attendance
        (
            student_id,
            rfid_uid,
            date,
            time_in,
            status
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?
        )
    ");

    // Note: Bind param changed to "issss" because $status is a string
    $insert->bind_param(
        "issss",
        $student_db_id,
        $uid,
        $date_today,
        $current_time,
        $status
    );

    $insert->execute();

    $attendance_id = $conn->insert_id;
}
else{
    $attendance = $attendance_result->fetch_assoc();

    if(empty($attendance['time_out'])){
        
        // ==========================================
        // NEW: 5-MINUTE COOLDOWN CHECK 
        // ==========================================
        $time_in_timestamp = strtotime($attendance['time_in']);
        $current_timestamp = strtotime($current_time);
        
        // Get the difference in minutes
        $minutes_diff = ($current_timestamp - $time_in_timestamp) / 60;

        if ($minutes_diff < 5) {
            // Trigger the "cooldown" status so the ESP32 prints "PLEASE WAIT"
            echo json_encode([
                "status" => "cooldown",
                "message" => "Please wait 5 minutes between Time In and Time Out."
            ]);
            exit;
        }
        // ==========================================

        /*
        Second scan = TIME OUT
        */
        $action = "TIME OUT";
        $attendance_id = $attendance['id'];

        $update = $conn->prepare("
            UPDATE attendance
            SET time_out = ?
            WHERE id = ?
        ");

        $update->bind_param(
            "si",
            $current_time,
            $attendance_id
        );

        $update->execute();
    }
    else{
        echo json_encode([
            "status" => "success",
            "message" => "Attendance already completed today",
            "student" => $name,
            "action" => "DONE"
        ]);
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| Trigger Formal Email Notification & Log to Database
|--------------------------------------------------------------------------
*/

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
    $log_stmt = $conn->prepare("
    INSERT INTO email_logs 
    (student_id, subject, status, created_at)
    VALUES (?, ?, ?, NOW())
    ");
    $log_stmt->bind_param(
        "iss",
        $student_db_id, // <--- Use the primary key (id) from the students table
        $mail->Subject,
        $mailStatus
    );
    $log_stmt->execute();
    
    echo json_encode([
        'success' => true,
        'status' => 'success', // Added to ensure compatibility with ESP32 indexOf checks
        'message' => "Attendance ($action) recorded successfully"
    ]);
} else {
    echo json_encode([
        'success' => true,
        'status' => 'success', // Added to ensure compatibility with ESP32 indexOf checks
        'message' => "Attendance ($action) recorded, but no email sent (email not registered)"
    ]);
}
?>