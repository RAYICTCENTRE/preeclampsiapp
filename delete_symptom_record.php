<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once __DIR__ . '/db_connect.php';

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
if ($user_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Please log in again.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$record_id = isset($input['record_id']) ? (int)$input['record_id'] : 0;

if ($record_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid record ID.']);
    exit;
}

/*
 * IMPORTANT:
 * The user_id condition prevents a patient from deleting another
 * patient's health record.
 */
$stmt = $conn->prepare(
    "DELETE FROM symptoms_records WHERE id = ? AND user_id = ? LIMIT 1"
);

if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Unable to prepare delete request.']);
    exit;
}

$stmt->bind_param('ii', $record_id, $user_id);
$ok = $stmt->execute();
$deleted = $stmt->affected_rows;
$stmt->close();
$conn->close();

if (!$ok) {
    echo json_encode(['success' => false, 'error' => 'Unable to delete the record.']);
    exit;
}

if ($deleted === 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Record not found or it does not belong to your account.'
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'deleted_id' => $record_id,
    'message' => 'Health record deleted successfully.'
]);
?>