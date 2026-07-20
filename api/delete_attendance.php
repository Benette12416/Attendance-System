<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
// Add security check: requireAdmin(); 
/** @var mysqli $conn */
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';
$id = (int)($data['id'] ?? 0);

if ($action === 'delete' && $id > 0) {
    $stmt = $conn->prepare("DELETE FROM attendance WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo json_encode(['success' => true]);
} elseif ($action === 'delete_all') {
    // Optional: Add date range filters here if needed
    $conn->query("DELETE FROM attendance");
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>