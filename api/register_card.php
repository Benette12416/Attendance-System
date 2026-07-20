<?php
/**
 * api/register_card.php
 * Assigns a scanned RFID UID to a student.
 * Called from admin/register_rfid.php after the admin selects a student
 * and taps a blank card on the reader.
 */
/** @var mysqli $conn */
header('Content-Type: application/json');
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authorized.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$student_id = (int)($input['student_id'] ?? 0);
$rfid_uid   = trim($input['rfid_uid'] ?? '');

if ($student_id <= 0 || $rfid_uid === '') {
    echo json_encode(['success' => false, 'message' => 'Missing student or card UID.']);
    exit;
}

// Check the UID is not already used by someone else
$stmt = mysqli_prepare($conn, "SELECT id, fullname FROM students WHERE rfid_uid = ? AND id != ?");
mysqli_stmt_bind_param($stmt, 'si', $rfid_uid, $student_id);
mysqli_stmt_execute($stmt);
$existing = mysqli_stmt_get_result($stmt)->fetch_assoc();

if ($existing) {
    echo json_encode(['success' => false, 'message' => "This card is already registered to {$existing['fullname']}."]);
    exit;
}

$stmt = mysqli_prepare($conn, "UPDATE students SET rfid_uid = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'si', $rfid_uid, $student_id);
mysqli_stmt_execute($stmt);

$stmt = mysqli_prepare($conn, "SELECT fullname FROM students WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $student_id);
mysqli_stmt_execute($stmt);
$student = mysqli_stmt_get_result($stmt)->fetch_assoc();

echo json_encode([
    'success' => true,
    'message' => "Card UID $rfid_uid registered to {$student['fullname']}."
]);
